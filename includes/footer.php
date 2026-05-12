<?php declare(strict_types=1); ?>

  </main><!-- /main -->

  <!-- Footer -->
  <footer class="site-footer mt-5">
    <div class="container">
      <div class="row g-4">
        <!-- Brand -->
        <div class="col-12 col-md-4">
          <span class="footer-brand">FapertaFarmShop</span>
          <p>Membawa hasil panen terbaik langsung dari petani ke meja makan Anda — segar, terjangkau, dan berkualitas.</p>
        </div>

        <!-- Company -->
        <div class="col-6 col-md-2">
          <h6>Perusahaan</h6>
          <a href="#">Tentang Kami</a>
          <a href="#">Keberlanjutan</a>
          <a href="#">Grosir</a>
        </div>

        <!-- Support -->
        <div class="col-6 col-md-2">
          <h6>Dukungan</h6>
          <a href="#">Kontak</a>
          <a href="#">Kebijakan Privasi</a>
          <a href="#">FAQ</a>
        </div>

        <!-- Newsletter -->
        <div class="col-12 col-md-4">
          <h6>Newsletter</h6>
          <p>Dapatkan info produk segar & penawaran spesial setiap minggu.</p>
         
        </div>
      </div>

      <hr class="footer-divider">

      <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-2">
        <p class="footer-copy mb-0">© <?php echo date('Y'); ?> FapertaFarmShop. Semua hak dilindungi.</p>
        <div class="d-flex gap-3">
          <span class="material-symbols-outlined footer-copy">payments</span>
          <span class="material-symbols-outlined footer-copy">credit_card</span>
        </div>
      </div>
    </div>
  </footer>

  <!-- Mobile Bottom Navigation -->
  <nav class="bottom-nav d-md-none">
    <a href="<?php echo base_url('/index.php'); ?>" id="bnav-home">
      <span class="material-symbols-outlined">home</span>
      Beranda
    </a>
    <a href="<?php echo base_url('/katalog.php'); ?>" id="bnav-katalog">
      <span class="material-symbols-outlined">storefront</span>
      Katalog
    </a>
    <a href="<?php echo base_url('/keranjang.php'); ?>" id="bnav-keranjang">
      <span class="material-symbols-outlined">shopping_cart</span>
      Keranjang
    </a>
    <a href="<?php echo base_url('/riwayat-pesanan.php'); ?>" id="bnav-riwayat">
      <span class="material-symbols-outlined">receipt_long</span>
      Riwayat
    </a>
  </nav>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Highlight active bottom nav link
    (function(){
      var page = location.pathname.split('/').pop() || 'index.php';
      var map = {
        'index.php':'bnav-home',
        'katalog.php':'bnav-katalog',
        'keranjang.php':'bnav-keranjang',
        'riwayat-pesanan.php':'bnav-riwayat'
      };
      if (map[page]) {
        var el = document.getElementById(map[page]);
        if (el) el.classList.add('active');
      }
    })();
  </script>
</body>
</html>