<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/auth_check.php';

require_login();

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    http_response_code(404);
    echo 'Produk tidak ditemukan.';
    exit;
}

$stmt = $pdo->prepare(
    "SELECT p.*, k.nama_kategori, k.slug
   FROM produk p
   JOIN kategori k ON k.id = p.id_kategori
   WHERE p.id = :id AND p.status='aktif' LIMIT 1"
);
$stmt->execute([':id' => $id]);
$produk = $stmt->fetch();

if (!$produk) {
    http_response_code(404);
    echo 'Produk tidak ditemukan.';
    exit;
}

$stmt = $pdo->prepare(
    "SELECT id, nama_produk, harga, stok, foto
   FROM produk
   WHERE id_kategori = :kid AND id <> :id AND status='aktif'
   ORDER BY created_at DESC
   LIMIT 6"
);
$stmt->execute([':kid' => (int)$produk['id_kategori'], ':id' => $id]);
$related = $stmt->fetchAll();

$title = $produk['nama_produk'];
include __DIR__ . '/includes/header.php';
?>

<div class="container py-4">

  <!-- Breadcrumb -->
  <nav aria-label="breadcrumb" class="mb-4">
    <ol class="breadcrumb" style="font-size:.85rem">
      <li class="breadcrumb-item"><a href="<?= base_url('/index.php') ?>" style="color:var(--clr-primary)">Beranda</a></li>
      <li class="breadcrumb-item"><a href="<?= base_url('/katalog.php') ?>" style="color:var(--clr-primary)">Katalog</a></li>
      <li class="breadcrumb-item active" aria-current="page"><?php echo e($produk['nama_produk']); ?></li>
    </ol>
  </nav>

  <!-- Product Detail -->
  <div class="row g-4 mb-5">

    <!-- Image -->
    <div class="col-12 col-md-5">
      <div class="rounded-4 overflow-hidden shadow" style="aspect-ratio:1/1;background:var(--clr-surface-low)">
        <img
          src="<?php echo $produk['foto']
            ? base_url('/uploads/produk/' . e($produk['foto']))
            : 'https://placehold.co/800x800/e8f5e9/2d5a27?text=' . urlencode(e($produk['nama_produk'])); ?>"
          alt="<?php echo e($produk['nama_produk']); ?>"
          class="w-100 h-100" style="object-fit:cover">
      </div>
    </div>

    <!-- Info -->
    <div class="col-12 col-md-7">
      <!-- Category badge -->
      <a href="<?= base_url('/katalog.php?kategori=' . e((string)$produk['slug'])) ?>"
         class="d-inline-block mb-2 px-3 py-1 rounded-pill"
         style="background:var(--clr-surface-low);color:var(--clr-primary);font-size:.75rem;font-weight:700;text-decoration:none;border:1px solid var(--clr-outline-var)">
        <?php echo e($produk['nama_kategori']); ?>
      </a>

      <h1 style="font-size:1.75rem;font-weight:800;color:var(--clr-primary);line-height:1.2">
        <?php echo e($produk['nama_produk']); ?>
      </h1>

      <div style="font-size:1.75rem;font-weight:800;color:var(--clr-secondary);margin:.5rem 0 1rem">
        <?php echo e(format_rupiah((int)$produk['harga'])); ?>
      </div>

      <!-- Stock indicator -->
      <div class="d-inline-flex align-items-center gap-2 mb-3 px-3 py-2 rounded-pill"
           style="background:<?php echo (int)$produk['stok'] > 0 ? '#d1e7dd' : '#f8d7da'; ?>;
                  color:<?php echo (int)$produk['stok'] > 0 ? '#0f5132' : '#842029'; ?>;font-size:.85rem;font-weight:700">
        <span class="material-symbols-outlined" style="font-size:1rem">
          <?php echo (int)$produk['stok'] > 0 ? 'check_circle' : 'cancel'; ?>
        </span>
        <?php echo (int)$produk['stok'] > 0
          ? 'Stok Tersedia: ' . (int)$produk['stok']
          : 'Stok Habis'; ?>
      </div>

      <?php if ($produk['deskripsi']): ?>
        <p style="color:var(--clr-on-surface-var);line-height:1.7;margin-bottom:1.5rem">
          <?php echo e($produk['deskripsi']); ?>
        </p>
      <?php endif; ?>

      <!-- Add to cart -->
      <form method="POST" action="<?php echo base_url('/keranjang.php'); ?>">
        <input type="hidden" name="add_produk_id" value="<?php echo (int)$produk['id']; ?>">
        <?php echo csrf_field(); ?>
        <div class="d-flex gap-3 align-items-center flex-wrap">
          <div>
            <label class="form-label">Jumlah</label>
            <input class="form-control" type="number" name="qty" value="1"
                   min="1" max="<?php echo (int)$produk['stok']; ?>"
                   style="width:100px" required>
          </div>
          <div class="d-flex align-items-end">
            <button class="btn-secondary-fill"
                    type="submit"
                    <?php echo (int)$produk['stok'] <= 0 ? 'disabled style="opacity:.5;cursor:not-allowed"' : ''; ?>>
              <span class="material-symbols-outlined" style="font-size:1.1rem">shopping_cart</span>
              Tambah ke Keranjang
            </button>
          </div>
        </div>
      </form>

      <!-- Feature pills -->
      <div class="d-flex flex-wrap gap-2 mt-4">
        <span class="px-3 py-1 rounded-pill" style="background:var(--clr-surface-low);font-size:.78rem;font-weight:600;color:var(--clr-on-surface-var);border:1px solid var(--clr-outline-var)">
          <span class="material-symbols-outlined" style="font-size:.9rem;vertical-align:middle">eco</span>
          Organik
        </span>
        <span class="px-3 py-1 rounded-pill" style="background:var(--clr-surface-low);font-size:.78rem;font-weight:600;color:var(--clr-on-surface-var);border:1px solid var(--clr-outline-var)">
          <span class="material-symbols-outlined" style="font-size:.9rem;vertical-align:middle">local_shipping</span>
          Pengiriman ke Alamat
        </span>
        <span class="px-3 py-1 rounded-pill" style="background:var(--clr-surface-low);font-size:.78rem;font-weight:600;color:var(--clr-on-surface-var);border:1px solid var(--clr-outline-var)">
          <span class="material-symbols-outlined" style="font-size:.9rem;vertical-align:middle">verified</span>
          Terjamin Kualitasnya
        </span>
      </div>
    </div>
  </div>

  <!-- Related Products -->
  <?php if ($related): ?>
    <hr style="border-color:var(--clr-outline-var)">
    <div class="pt-4">
      <h2 class="section-title mb-1">Produk Terkait</h2>
      <p class="section-sub">Dari kategori yang sama</p>
      <div class="row row-cols-2 row-cols-md-3 row-cols-lg-4 g-4">
        <?php foreach ($related as $p): ?>
          <div class="col">
            <div class="card-product h-100">
              <div class="product-img-wrap">
                <img
                  src="<?php echo $p['foto'] ? base_url('/uploads/produk/' . e($p['foto'])) : 'https://placehold.co/400x400/e8f5e9/2d5a27?text=' . urlencode(e($p['nama_produk'])); ?>"
                  alt="<?php echo e($p['nama_produk']); ?>" loading="lazy">
                <span class="card-badge">Organic</span>
              </div>
              <div class="card-body-premium">
                <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                  <div class="product-title"><?php echo e($p['nama_produk']); ?></div>
                  <div class="product-price text-nowrap"><?php echo e(format_rupiah((int)$p['harga'])); ?></div>
                </div>
                
                <div class="mt-auto d-flex gap-2">
                  <a class="btn-outline-primary-pill flex-grow-1 justify-content-center py-2 px-0"
                     style="font-size: 0.8rem;"
                     href="<?php echo base_url('/detail-produk.php?id=' . (int)$p['id']); ?>">Detail</a>
                  <form method="POST" action="<?php echo base_url('/keranjang.php'); ?>" class="flex-grow-1">
                    <input type="hidden" name="add_produk_id" value="<?php echo (int)$p['id']; ?>">
                    <input type="hidden" name="qty" value="1">
                    <?php echo csrf_field(); ?>
                    <button class="btn-secondary-fill w-100 justify-content-center py-2 px-0"
                            style="font-size: 0.8rem;"
                            type="submit"
                            <?php echo (int)$p['stok'] <= 0 ? 'disabled style="opacity:.5"' : ''; ?>>
                      + Keranjang
                    </button>
                  </form>
                </div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  <?php endif; ?>

</div>

<?php include __DIR__ . '/includes/footer.php'; ?>