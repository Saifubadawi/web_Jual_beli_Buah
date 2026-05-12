<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/auth_check.php';

require_login();

$userId = (int)$_SESSION['user_id'];

$stmt = $pdo->prepare('SELECT k.id AS id_keranjang, p.id AS id_produk, p.harga, p.stok, p.status, k.jumlah, p.foto, p.nama_produk
  FROM keranjang k
  JOIN produk p ON p.id = k.id_produk
  WHERE k.id_user=:uid');
$stmt->execute([':uid' => $userId]);
$items = $stmt->fetchAll();

$total = array_sum(array_map(fn($it) => (int)$it['harga'] * (int)$it['jumlah'], $items));

if (!$items) {
    $_SESSION['flash'] = ['type' => 'warning', 'message' => 'Keranjang masih kosong.'];
    header('Location: ' . base_url('/katalog.php'));
    exit;
}

$title = 'Checkout';

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && !empty($_POST['buat_pesanan'])) {
    verify_csrf($_POST['csrf_token'] ?? null);

    $namaPenerima = trim((string)($_POST['nama_penerima'] ?? ''));
    $alamat       = trim((string)($_POST['alamat_pengiriman'] ?? ''));
    $telepon      = trim((string)($_POST['telepon'] ?? ''));

    if ($namaPenerima === '' || $alamat === '' || $telepon === '' || $total <= 0) {
        $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Data checkout tidak valid.'];
        header('Location: ' . base_url('/checkout.php'));
        exit;
    }

    $pdo->beginTransaction();
    try {
        $pdo->prepare('INSERT INTO pesanan (id_user,total_harga,nama_penerima,alamat_pengiriman,telepon,status)
      VALUES (:uid,:total,:nama,:alamat,:tel,"menunggu")')
            ->execute([
                ':uid'    => $userId,
                ':total'  => $total,
                ':nama'   => $namaPenerima,
                ':alamat' => $alamat,
                ':tel'    => $telepon,
            ]);

        $idPesanan   = (int)$pdo->lastInsertId();
        $detailStmt  = $pdo->prepare('INSERT INTO detail_pesanan (id_pesanan,id_produk,jumlah,harga_satuan,subtotal)
      VALUES (:pid,:produk,:jml,:harga,:sub)');

        foreach ($items as $it) {
            $produkId = (int)$it['id_produk'];
            $jumlah   = (int)$it['jumlah'];

            $cek = $pdo->prepare('SELECT stok,status,harga FROM produk WHERE id=:id AND status="aktif" LIMIT 1');
            $cek->execute([':id' => $produkId]);
            $p = $cek->fetch();
            if (!$p || (int)$p['stok'] < $jumlah) {
                throw new RuntimeException('Stok produk berubah. Silakan ulangi checkout.');
            }

            $hargaSatuan = (int)$p['harga'];
            $subtotal    = $hargaSatuan * $jumlah;

            $detailStmt->execute([
                ':pid'    => $idPesanan,
                ':produk' => $produkId,
                ':jml'    => $jumlah,
                ':harga'  => $hargaSatuan,
                ':sub'    => $subtotal,
            ]);

            $pdo->prepare('UPDATE produk SET stok = stok - :jml WHERE id=:id')
                ->execute([':jml' => $jumlah, ':id' => $produkId]);
        }

        $pdo->prepare('DELETE FROM keranjang WHERE id_user=:uid')->execute([':uid' => $userId]);
        $pdo->commit();

        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Pesanan berhasil dibuat! Terima kasih.'];
        header('Location: ' . base_url('/riwayat-pesanan.php'));
        exit;
    } catch (Throwable $e) {
        $pdo->rollBack();
        $_SESSION['flash'] = ['type' => 'danger', 'message' => $e->getMessage()];
        header('Location: ' . base_url('/checkout.php'));
        exit;
    }
}

include __DIR__ . '/includes/header.php';
?>

<div class="container py-4">
  <div class="mb-4">
    <h1 class="section-title">Checkout</h1>
    <p class="section-sub mb-0">Lengkapi data pengiriman untuk menyelesaikan pesanan</p>
  </div>

  <div class="row g-4">
    <!-- Form -->
    <div class="col-12 col-lg-7">
      <div class="card">
        <div class="card-body">
          <h2 style="font-size:1.1rem;font-weight:700;color:var(--clr-primary);margin-bottom:1.5rem">
            <span class="material-symbols-outlined" style="font-size:1.2rem;vertical-align:middle;margin-right:.3rem">local_shipping</span>
            Data Pengiriman
          </h2>

          <form method="POST">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="buat_pesanan" value="1">

            <div class="mb-3">
              <label class="form-label">Nama Penerima</label>
              <input class="form-control" name="nama_penerima" placeholder="Nama lengkap penerima" required>
            </div>

            <div class="mb-3">
              <label class="form-label">Alamat Pengiriman</label>
              <textarea class="form-control" name="alamat_pengiriman" rows="3"
                        placeholder="Jalan, nomor rumah, kelurahan, kecamatan, kota" required></textarea>
            </div>

            <div class="mb-4">
              <label class="form-label">Nomor Telepon</label>
              <input class="form-control" name="telepon" placeholder="08xx-xxxx-xxxx" required>
            </div>

            <button class="btn-primary-fill w-100 justify-content-center" type="submit">
              <span class="material-symbols-outlined" style="font-size:1.1rem">check_circle</span>
              Buat Pesanan
            </button>
          </form>
        </div>
      </div>
    </div>

    <!-- Summary -->
    <div class="col-12 col-lg-5">
      <div class="card">
        <div class="card-body">
          <h2 style="font-size:1.1rem;font-weight:700;color:var(--clr-primary);margin-bottom:1.25rem">
            Ringkasan Pesanan
          </h2>

          <div class="d-flex flex-column gap-3 mb-3">
            <?php foreach ($items as $it): ?>
              <div class="d-flex gap-2 align-items-center">
                <div class="flex-shrink-0 rounded-3 overflow-hidden"
                     style="width:50px;height:50px;background:var(--clr-surface-low)">
                  <img
                    src="<?php echo $it['foto'] ? base_url('/uploads/produk/' . e($it['foto'])) : 'https://placehold.co/100x100/e8f5e9/2d5a27?text=P'; ?>"
                    alt="<?php echo e($it['nama_produk']); ?>"
                    style="width:100%;height:100%;object-fit:cover">
                </div>
                <div class="flex-grow-1" style="min-width:0">
                  <div style="font-weight:700;font-size:.85rem;color:var(--clr-primary);white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                    <?php echo e((string)$it['nama_produk']); ?>
                  </div>
                  <div style="font-size:.75rem;color:var(--clr-on-surface-var)">
                    Qty: <?php echo (int)$it['jumlah']; ?>
                  </div>
                </div>
                <div style="font-weight:700;font-size:.85rem;color:var(--clr-secondary);white-space:nowrap">
                  <?php echo e(format_rupiah((int)$it['harga'] * (int)$it['jumlah'])); ?>
                </div>
              </div>
            <?php endforeach; ?>
          </div>

          <hr style="border-color:var(--clr-outline-var)">

          <div class="d-flex justify-content-between align-items-center">
            <span style="font-weight:700;color:var(--clr-primary)">Total Pembayaran</span>
            <span style="font-weight:800;font-size:1.3rem;color:var(--clr-secondary)">
              <?php echo e(format_rupiah($total)); ?>
            </span>
          </div>

          <div class="mt-3 p-3 rounded-3" style="background:var(--clr-surface-low);font-size:.8rem;color:var(--clr-on-surface-var)">
            <span class="material-symbols-outlined" style="font-size:.95rem;vertical-align:middle;color:var(--clr-primary)">info</span>
            Pesanan akan diproses setelah konfirmasi pembayaran.
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>