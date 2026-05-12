<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/helpers.php';

$title = 'Beranda';

$peng = $pdo->query('SELECT * FROM pengaturan ORDER BY id ASC LIMIT 1')->fetch() ?: null;

$produkUnggulan = $pdo->query("
    SELECT id, nama_produk, harga, foto 
    FROM produk 
    WHERE status='aktif' AND stok > 0 
    ORDER BY created_at DESC 
    LIMIT 8
")->fetchAll();

$kategoriList = $pdo->query("
    SELECT id, nama_kategori, slug 
    FROM kategori 
    ORDER BY id ASC
")->fetchAll();

include __DIR__ . '/includes/header.php';
?>

<!-- ── Hero ──────────────────────────────────────────────── -->
<section class="container my-4 my-md-5">
  <div class="hero-section">
    <div class="row align-items-center g-4">
      <div class="col-lg-6">
        <h1>
          Produk Segar Langsung dari <span>Petani</span> ke Meja Makanmu.
        </h1>
        <p class="my-3" style="font-size:1.05rem;max-width:480px">
          Belanja sayur &amp; pangan berkualitas dengan stok realtime, harga terjangkau, dan pengiriman sesuai alamat.
        </p>
        <div class="d-flex flex-wrap gap-3 mt-4">
          <a href="<?= base_url('/katalog.php') ?>" class="btn-secondary-fill">
            <span class="material-symbols-outlined" style="font-size:1.1rem">storefront</span>
            Belanja Sekarang
          </a>
          <a href="<?= base_url('/katalog.php') ?>" class="btn-outline-primary-pill">
            Lihat Katalog
          </a>
        </div>

        <!-- delivery badge -->
        <div class="delivery-badge mt-4 d-inline-flex">
          <div class="icon-wrap">
            <span class="material-symbols-outlined">local_shipping</span>
          </div>
          <div>
            <div style="font-weight:700;font-size:.875rem;color:var(--clr-primary)">Pengiriman ke Alamat</div>
            <div style="font-size:.75rem;color:var(--clr-on-surface-var)">Stok tersedia realtime</div>
          </div>
        </div>
      </div>

      <div class="col-lg-6">
        <div class="row g-3">
          <div class="col-6">
            <div class="p-4 rounded-4 text-center h-100 card-feature">
              <div class="feat-icon mx-auto" style="background:rgba(21,66,18,.08)">
                <span class="material-symbols-outlined" style="color:var(--clr-primary)">eco</span>
              </div>
              <h5>100% Organik</h5>
              <p>Tanpa pestisida sintetis</p>
            </div>
          </div>
          <div class="col-6">
            <div class="p-4 rounded-4 text-center h-100 card-feature">
              <div class="feat-icon mx-auto" style="background:rgba(145,77,0,.08)">
                <span class="material-symbols-outlined" style="color:var(--clr-secondary)">local_shipping</span>
              </div>
              <h5>Pengiriman Cepat</h5>
              <p>Dari ladang ke pintu Anda</p>
            </div>
          </div>
          <div class="col-6">
            <div class="p-4 rounded-4 text-center h-100 card-feature">
              <div class="feat-icon mx-auto" style="background:rgba(21,66,18,.08)">
                <span class="material-symbols-outlined" style="color:var(--clr-primary)">workspace_premium</span>
              </div>
              <h5>Kualitas Premium</h5>
              <p>Dipilih oleh tangan petani</p>
            </div>
          </div>
          <div class="col-6">
            <div class="p-4 rounded-4 text-center h-100 card-feature">
              <div class="feat-icon mx-auto" style="background:rgba(145,77,0,.08)">
                <span class="material-symbols-outlined" style="color:var(--clr-secondary)">volunteer_activism</span>
              </div>
              <h5>Dukung Petani</h5>
              <p>Beli langsung dari sumbernya</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ── Kategori ───────────────────────────────────────────── -->
<?php if ($kategoriList): ?>
<section class="bg-section py-5">
  <div class="container">
    <div class="d-flex justify-content-between align-items-end mb-4">
      <div>
        <h2 class="section-title">Jelajahi Kategori</h2>
        <p class="section-sub mb-0">Temukan produk favorit Anda dengan mudah</p>
      </div>
      <a href="<?= base_url('/katalog.php') ?>" class="section-link d-none d-md-flex">
        Lihat Semua
        <span class="material-symbols-outlined" style="font-size:1rem">arrow_forward</span>
      </a>
    </div>
    <div class="row row-cols-2 row-cols-md-4 g-3">
      <?php foreach ($kategoriList as $k): ?>
        <div class="col">
          <a href="<?= base_url('/katalog.php?kategori=' . e((string)$k['slug'])) ?>" class="text-decoration-none">
            <div class="card-category p-3">
              <div class="cat-img-wrap rounded-3 mb-3 overflow-hidden" style="aspect-ratio:1/1;background:var(--clr-surface-low)">
                <div class="w-100 h-100 d-flex align-items-center justify-content-center" style="min-height:120px">
                  <span class="material-symbols-outlined" style="font-size:3rem;color:var(--clr-outline)">yard</span>
                </div>
              </div>
              <h3 style="font-size:.95rem;color:var(--clr-primary);font-weight:700;margin:0;text-align:center">
                <?php echo e((string)$k['nama_kategori']); ?>
              </h3>
            </div>
          </a>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- ── Produk Unggulan ────────────────────────────────────── -->
<section class="container py-5">
  <div class="d-flex justify-content-between align-items-end mb-4">
    <div>
      <h2 class="section-title">Produk Unggulan</h2>
      <p class="section-sub mb-0">Terpopuler minggu ini, dipilih khusus untukmu</p>
    </div>
    <a href="<?= base_url('/katalog.php') ?>" class="section-link d-none d-md-flex">
      Lihat Semua
      <span class="material-symbols-outlined" style="font-size:1rem">arrow_forward</span>
    </a>
  </div>

  <div class="row row-cols-2 row-cols-md-4 g-3">
    <?php foreach ($produkUnggulan as $p): ?>
      <div class="col">
        <div class="card-product">
          <div class="product-img-wrap">
            <img
              src="<?php echo !empty($p['foto'])
                ? base_url('/uploads/produk/') . e((string)$p['foto'])
                : 'https://placehold.co/400x400/e8f5e9/2d5a27?text=' . urlencode(e((string)$p['nama_produk'])); ?>"
              alt="<?php echo e((string)$p['nama_produk']); ?>"
              loading="lazy">
            <span class="card-badge">Segar</span>
          </div>
          <div class="p-3">
            <div style="font-weight:700;font-size:.9rem;color:var(--clr-primary);min-height:38px">
              <?php echo e((string)$p['nama_produk']); ?>
            </div>
            <div style="font-weight:700;font-size:1rem;color:var(--clr-secondary);margin:.3rem 0 .75rem">
              <?php echo e(format_rupiah((int)$p['harga'])); ?>
            </div>
            <a href="<?= base_url('/detail-produk.php?id=' . (int)$p['id']) ?>"
               class="btn btn-accent btn-sm w-100">Lihat Detail</a>
          </div>
        </div>
      </div>
    <?php endforeach; ?>

    <?php if (!$produkUnggulan): ?>
      <div class="col-12">
        <div class="alert alert-warning">Belum ada produk tersedia.</div>
      </div>
    <?php endif; ?>
  </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>