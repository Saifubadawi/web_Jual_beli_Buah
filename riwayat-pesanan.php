<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/auth_check.php';

require_login();

$userId = (int)$_SESSION['user_id'];

$stmt = $pdo->prepare('SELECT * FROM pesanan WHERE id_user=:uid ORDER BY created_at DESC');
$stmt->execute([':uid' => $userId]);
$pesanan = $stmt->fetchAll();

$title = 'Riwayat Pesanan';
include __DIR__ . '/includes/header.php';
?>

<div class="container py-4">
  <div class="mb-4">
    <h1 class="section-title">Riwayat Pesanan</h1>
    <p class="section-sub mb-0">Pantau status pesanan produk segar Anda</p>
  </div>

  <?php if (!$pesanan): ?>
    <div class="text-center py-5">
      <span class="material-symbols-outlined" style="font-size:5rem;color:var(--clr-outline)">receipt_long</span>
      <h3 style="color:var(--clr-on-surface-var);margin-top:1rem">Belum ada pesanan</h3>
      <p style="color:var(--clr-outline)">Anda belum pernah melakukan pemesanan. Mulai belanja sekarang!</p>
      <a href="<?php echo base_url('/katalog.php'); ?>" class="btn-secondary-fill mt-2">
        <span class="material-symbols-outlined" style="font-size:1.1rem">storefront</span>
        Mulai Belanja
      </a>
    </div>

  <?php else: ?>
    <div class="table-responsive">
      <table class="table align-middle shadow-sm">
        <thead>
          <tr>
            <th>ID Pesanan</th>
            <th>Tanggal</th>
            <th>Total Harga</th>
            <th>Penerima</th>
            <th>Status</th>
            <th class="text-center">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($pesanan as $p):
            $statusClass = 'badge-' . ($p['status'] ?? 'menunggu');
            // override for cancelled if exists in DB logic
            if ($p['status'] === 'batal') $statusClass = 'badge-cancelled';
          ?>
            <tr>
              <td>
                <span class="fw-bold text-primary">#<?php echo (int)$p['id']; ?></span>
              </td>
              <td style="font-size:.85rem;color:var(--clr-on-surface-var)">
                <?php echo date('d M Y, H:i', strtotime((string)$p['created_at'])); ?>
              </td>
              <td class="fw-bold text-secondary">
                <?php echo e(format_rupiah((int)$p['total_harga'])); ?>
              </td>
              <td>
                <div style="font-weight:600;font-size:.875rem"><?php echo e((string)$p['nama_penerima']); ?></div>
                <div class="text-muted" style="font-size:.75rem;max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
                  <?php echo e((string)$p['alamat_pengiriman']); ?>
                </div>
              </td>
              <td>
                <span class="badge-status <?php echo $statusClass; ?>">
                  <?php echo ucfirst((string)$p['status']); ?>
                </span>
              </td>
              <td class="text-center">
                <button class="btn btn-outline-success btn-sm px-3 rounded-pill" disabled style="opacity:.6">
                  Detail
                </button>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>