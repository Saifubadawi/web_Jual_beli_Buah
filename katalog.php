<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/auth_check.php';

require_login();

$title = 'Katalog Produk';

$kategori_slug = trim((string)($_GET['kategori'] ?? ''));
$q = trim((string)($_GET['q'] ?? ''));

$params = [];
$sql = "
  SELECT p.id, p.nama_produk, p.deskripsi, p.harga, p.stok, p.foto, k.nama_kategori, k.slug
  FROM produk p
  JOIN kategori k ON k.id = p.id_kategori
  WHERE p.status = 'aktif'
";

if ($kategori_slug !== '') {
    $sql .= " AND k.slug = :slug";
    $params[':slug'] = $kategori_slug;
}

if ($q !== '') {
    $sql .= " AND (p.nama_produk LIKE :q1 OR p.deskripsi LIKE :q2)";
    $params[':q1'] = '%' . $q . '%';
    $params[':q2'] = '%' . $q . '%';
}

$sql .= " ORDER BY p.created_at DESC LIMIT 50";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$produkList = $stmt->fetchAll();

$kategoriList = $pdo->query("SELECT id, nama_kategori, slug FROM kategori ORDER BY id ASC")->fetchAll();

include __DIR__ . '/includes/header.php';
?>

<div class="container py-4">

  <!-- Page Header -->
  <div class="mb-4">
    <h1 class="section-title">Katalog Produk</h1>
    <p class="section-sub mb-0">Temukan sayur &amp; pangan segar pilihan petani lokal</p>
  </div>

  <!-- Filter Row -->
  <div class="card mb-4">
    <div class="card-body">
      <div class="row g-3 align-items-end">
        <div class="col-12 col-md-6">
          <label class="form-label">Cari Produk</label>
          <form method="GET" class="d-flex gap-2" role="search">
            <?php if ($kategori_slug !== ''): ?>
              <input type="hidden" name="kategori" value="<?php echo e($kategori_slug); ?>">
            <?php endif; ?>
            <input class="form-control" name="q" value="<?php echo e($q); ?>" placeholder="mis. wortel, bayam…">
            <button class="btn btn-accent px-4" type="submit">
              <span class="material-symbols-outlined" style="font-size:1rem;vertical-align:middle">search</span>
            </button>
          </form>
        </div>
        <div class="col-12 col-md-4">
          <label class="form-label">Filter Kategori</label>
          <form method="GET">
            <input type="hidden" name="q" value="<?php echo e($q); ?>">
            <select name="kategori" class="form-select" onchange="this.form.submit()">
              <option value="" <?php echo $kategori_slug === '' ? 'selected' : ''; ?>>Semua Kategori</option>
              <?php foreach ($kategoriList as $k): ?>
                <option value="<?php echo e($k['slug']); ?>" <?php echo $kategori_slug === $k['slug'] ? 'selected' : ''; ?>>
                  <?php echo e($k['nama_kategori']); ?>
                </option>
              <?php endforeach; ?>
            </select>
          </form>
        </div>
        <div class="col-12 col-md-2 d-flex align-items-end">
          <?php if ($q || $kategori_slug): ?>
            <a href="<?= base_url('/katalog.php') ?>" class="btn btn-outline-success w-100">Reset</a>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  <!-- Results header -->
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h2 style="font-size:1.1rem;font-weight:700;color:var(--clr-primary);margin:0">
      <?php echo $kategori_slug ? 'Kategori: ' . e($kategori_slug) : 'Semua Produk'; ?>
    </h2>
    <span style="font-size:.85rem;color:var(--clr-on-surface-var)">
      <?php echo count($produkList); ?> produk ditemukan
    </span>
  </div>

  <!-- Product Grid -->
  <div class="row row-cols-2 row-cols-md-3 row-cols-lg-4 g-4">
    <?php if (!$produkList): ?>
      <div class="col-12 text-center py-5">
        <span class="material-symbols-outlined" style="font-size:4rem;color:var(--clr-outline)">search_off</span>
        <div class="alert alert-warning mt-3">Tidak ada produk yang cocok dengan pencarian.</div>
        <a href="<?= base_url('/katalog.php') ?>" class="btn-accent px-4 py-2">Lihat Semua Produk</a>
      </div>
    <?php endif; ?>

    <?php foreach ($produkList as $p): ?>
      <div class="col">
        <div class="card-product h-100">
          <div class="product-img-wrap">
            <img
              src="<?php echo $p['foto'] ? base_url('/uploads/produk/' . e($p['foto'])) : 'https://placehold.co/400x400/e8f5e9/2d5a27?text=' . urlencode(e($p['nama_produk'])); ?>"
              alt="<?php echo e($p['nama_produk']); ?>"
              loading="lazy">
            <?php if ((int)$p['stok'] <= 3 && (int)$p['stok'] > 0): ?>
              <span class="card-badge card-badge-orange">Sisa <?php echo (int)$p['stok']; ?></span>
            <?php elseif ((int)$p['stok'] <= 0): ?>
              <span class="card-badge" style="background:rgba(186,26,26,0.15);color:#842029">Habis</span>
            <?php else: ?>
              <span class="card-badge">Organic</span>
            <?php endif; ?>
          </div>
          <div class="card-body-premium">
            <div style="font-size:0.75rem;color:var(--clr-on-surface-var);margin-bottom:0.25rem;font-weight:600;text-transform:uppercase;letter-spacing:0.02em">
              <?php echo e($p['nama_kategori']); ?>
            </div>
            <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
              <div class="product-title"><?php echo e($p['nama_produk']); ?></div>
              <div class="product-price text-nowrap"><?php echo e(format_rupiah((int)$p['harga'])); ?></div>
            </div>
            <p class="product-desc"><?php echo e($p['deskripsi']); ?></p>
            
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
                        <?php echo (int)$p['stok'] <= 0 ? 'disabled style="opacity:0.5"' : ''; ?>>
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

<?php include __DIR__ . '/includes/footer.php'; ?>