# TODO - Web Toko Sayur & Pangan (PHP Native + MySQL)

- [x] Buat struktur folder: /admin, /includes, /assets/css, /assets/js, /uploads/produk
- [x] Buat `database.sql` (skema tabel + akun admin default)
- [x] Buat `includes/db.php` (koneksi PDO)
- [x] Buat komponen layout: `includes/header.php`, `includes/footer.php`
- [x] Buat auth middleware: `includes/auth_check.php`
- [x] Buat helper: fungsi format Rupiah + escaping (+ CSRF)
- [x] Tambahkan styling Bootstrap 5 + custom hijau tua: `assets/css/style.css`
- [ ] Buat halaman user: `login.php`, `register.php`, `logout.php`, `index.php`, `katalog.php`, `detail-produk.php`, `keranjang.php`, `checkout.php`, `riwayat-pesanan.php`
- [ ] Buat halaman admin: `admin/dashboard.php`, `admin/produk.php`, `admin/pesanan.php`, `admin/pengguna.php`, `admin/pengaturan.php`
- [ ] Tambahkan JS ringan (mis. Chart.js untuk dashboard)
- [ ] Jalankan review cepat untuk memastikan alur end-to-end (register/login -> keranjang -> checkout -> riwayat; admin CRUD produk & update status)


