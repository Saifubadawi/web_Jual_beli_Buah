<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/auth_check.php';

require_login();

$userId = (int)$_SESSION['user_id'];

// ── Tambah ke keranjang ─────────────────────────────────
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && !empty($_POST['add_produk_id'])) {
    verify_csrf($_POST['csrf_token'] ?? null);

    $produkId = (int)($_POST['add_produk_id'] ?? 0);
    $qty      = (int)($_POST['qty'] ?? 1);

    if ($produkId <= 0 || $qty <= 0) {
        $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Jumlah/produk tidak valid.'];
        header('Location: ' . base_url('/keranjang.php'));
        exit;
    }

    $stmt = $pdo->prepare('SELECT id, stok, status FROM produk WHERE id=:id AND status="aktif" LIMIT 1');
    $stmt->execute([':id' => $produkId]);
    $prod = $stmt->fetch();

    if (!$prod) {
        $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Produk tidak tersedia.'];
        header('Location: ' . base_url('/keranjang.php'));
        exit;
    }

    if ((int)$prod['stok'] <= 0) {
        $_SESSION['flash'] = ['type' => 'warning', 'message' => 'Stok produk habis.'];
        header('Location: ' . base_url('/keranjang.php'));
        exit;
    }

    $stmt = $pdo->prepare('SELECT id, jumlah FROM keranjang WHERE id_user=:uid AND id_produk=:pid LIMIT 1');
    $stmt->execute([':uid' => $userId, ':pid' => $produkId]);
    $row  = $stmt->fetch();
    $stok = (int)$prod['stok'];

    if ($row) {
        $newQty = min((int)$row['jumlah'] + $qty, $stok);
        $pdo->prepare('UPDATE keranjang SET jumlah=:jml WHERE id=:id AND id_user=:uid')
            ->execute([':jml' => $newQty, ':id' => (int)$row['id'], ':uid' => $userId]);
    } else {
        $qty = min($qty, $stok);
        $pdo->prepare('INSERT INTO keranjang (id_user, id_produk, jumlah) VALUES (:uid,:pid,:jml)')
            ->execute([':uid' => $userId, ':pid' => $produkId, ':jml' => $qty]);
    }

    $_SESSION['flash'] = ['type' => 'success', 'message' => 'Produk ditambahkan ke keranjang.'];
    header('Location: ' . base_url('/keranjang.php'));
    exit;
}

// ── Hapus item ──────────────────────────────────────────
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && !empty($_POST['hapus_id_keranjang'])) {
    verify_csrf($_POST['csrf_token'] ?? null);

    $rid = (int)($_POST['hapus_id_keranjang'] ?? 0);
    if ($rid > 0) {
        $pdo->prepare('DELETE FROM keranjang WHERE id=:id AND id_user=:uid')
            ->execute([':id' => $rid, ':uid' => $userId]);
    }

    $_SESSION['flash'] = ['type' => 'success', 'message' => 'Item dihapus dari keranjang.'];
    header('Location: ' . base_url('/keranjang.php'));
    exit;
}

// ── Fetch keranjang ─────────────────────────────────────
$stmt = $pdo->prepare('SELECT k.id AS id_keranjang, p.id AS id_produk, p.nama_produk, p.harga, p.stok, p.foto, k.jumlah
  FROM keranjang k
  JOIN produk p ON p.id=k.id_produk
  WHERE k.id_user=:uid
  ORDER BY k.created_at DESC');
$stmt->execute([':uid' => $userId]);
$items = $stmt->fetchAll();

$total = array_sum(array_map(fn($it) => (int)$it['harga'] * (int)$it['jumlah'], $items));

$title = 'Keranjang Belanja';
include __DIR__ . '/includes/header.php';
?>

<div class="container py-4">
  <div class="mb-4">
    <h1 class="section-title">Keranjang Belanja</h1>
    <p class="section-sub mb-0">
      <?php echo count($items); ?> produk dalam keranjang
    </p>
  </div>

  <?php if (!$items): ?>
    <div class="text-center py-5">
      <span class="material-symbols-outlined" style="font-size:5rem;color:var(--clr-outline)">shopping_cart</span>
      <h3 style="color:var(--clr-on-surface-var);margin-top:1rem">Keranjangmu masih kosong</h3>
      <p style="color:var(--clr-outline)">Yuk, temukan produk segar untuk dimasukkan ke keranjang!</p>
      <a href="<?php echo base_url('/katalog.php'); ?>" class="btn-secondary-fill mt-2">
        <span class="material-symbols-outlined" style="font-size:1.1rem">storefront</span>
        Mulai Belanja
      </a>
    </div>

  <?php else: ?>
    <div class="row g-4">
      <!-- Cart Items -->
      <div class="col-12 col-lg-8">
        <div class="card">
          <div class="card-body p-0">
            <?php foreach ($items as $idx => $it):
              $subtotal = (int)$it['harga'] * (int)$it['jumlah'];
            ?>
              <div class="d-flex gap-3 p-3 <?php echo $idx > 0 ? 'border-top' : ''; ?>"
                   style="border-color:var(--clr-outline-var)!important">
                <!-- Image -->
                <div class="flex-shrink-0 rounded-3 overflow-hidden"
                     style="width:80px;height:80px;background:var(--clr-surface-low)">
                  <img
                    src="<?php echo $it['foto'] ? base_url('/uploads/produk/' . e($it['foto'])) : 'https://placehold.co/160x160/e8f5e9/2d5a27?text=Produk'; ?>"
                    alt="<?php echo e($it['nama_produk']); ?>"
                    style="width:100%;height:100%;object-fit:cover">
                </div>
                <!-- Info -->
                <div class="flex-grow-1 min-w-0">
                  <div style="font-weight:700;font-size:.9rem;color:var(--clr-primary)">
                    <?php echo e($it['nama_produk']); ?>
                  </div>
                  <div style="font-size:.8rem;color:var(--clr-on-surface-var);margin:.2rem 0">
                    <?php echo e(format_rupiah((int)$it['harga'])); ?> × <?php echo (int)$it['jumlah']; ?>
                  </div>
                  <div style="font-weight:700;color:var(--clr-secondary)">
                    <?php echo e(format_rupiah($subtotal)); ?>
                  </div>
                </div>
                <!-- Delete -->
                <div class="flex-shrink-0 d-flex align-items-center">
                  <form method="POST">
                    <input type="hidden" name="hapus_id_keranjang" value="<?php echo (int)$it['id_keranjang']; ?>">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="btn-icon text-danger" title="Hapus item">
                      <span class="material-symbols-outlined">delete</span>
                    </button>
                  </form>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>

      <!-- Summary -->
      <div class="col-12 col-lg-4">
        <div class="card">
          <div class="card-body">
            <h2 style="font-size:1.1rem;font-weight:700;color:var(--clr-primary);margin-bottom:1.25rem">
              Ringkasan Pesanan
            </h2>

            <div class="d-flex flex-column gap-2 mb-3">
              <?php foreach ($items as $it): ?>
                <div class="d-flex justify-content-between" style="font-size:.85rem">
                  <span style="color:var(--clr-on-surface-var)">
                    <?php echo e($it['nama_produk']); ?> (×<?php echo (int)$it['jumlah']; ?>)
                  </span>
                  <span style="font-weight:600">
                    <?php echo e(format_rupiah((int)$it['harga'] * (int)$it['jumlah'])); ?>
                  </span>
                </div>
              <?php endforeach; ?>
            </div>

            <hr style="border-color:var(--clr-outline-var)">

            <div class="d-flex justify-content-between align-items-center mb-3">
              <span style="font-weight:700;color:var(--clr-primary)">Total</span>
              <span style="font-weight:800;font-size:1.25rem;color:var(--clr-secondary)">
                <?php echo e(format_rupiah($total)); ?>
              </span>
            </div>

            <a href="<?php echo base_url('/checkout.php'); ?>" class="btn-primary-fill w-100 justify-content-center">
              <span class="material-symbols-outlined" style="font-size:1.1rem">payments</span>
              Lanjut ke Checkout
            </a>
            <a href="<?php echo base_url('/katalog.php'); ?>"
               class="btn-outline-primary-pill w-100 justify-content-center mt-2">
              Lanjut Belanja
            </a>
          </div>
        </div>
      </div>
    </div>
  <?php endif; ?>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>