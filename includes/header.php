<?php

declare(strict_types=1);
require_once __DIR__ . '/helpers.php';
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$pengaturan = null;
if (!empty($pdo)) {
    // noop - placeholder (ditangani di halaman)
}

function render_nav(): void
{
    $isLogin = !empty($_SESSION['user_id']);
    $role    = $_SESSION['role'] ?? '';
    $nama    = e((string)($_SESSION['nama'] ?? ''));
    $current = basename($_SERVER['PHP_SELF'] ?? '');

    $links = [
        'index.php'         => 'Beranda',
        'katalog.php'       => 'Katalog',
        'keranjang.php'     => 'Keranjang',
        'riwayat-pesanan.php' => 'Riwayat',
    ];
    if ($isLogin && $role === 'admin') {
        $links['admin/dashboard.php'] = 'Admin';
    }

    $navItems = '';
    foreach ($links as $file => $label) {
        $isActive = ($current === basename($file)) ? 'active' : '';
        $navItems .= '<li class="nav-item">'
            . '<a class="nav-link ' . $isActive . '" href="' . base_url('/' . $file) . '">' . $label . '</a>'
            . '</li>';
    }

    if ($isLogin) {
        $right = '<div class="d-flex align-items-center gap-2">'
            . '<span class="text-muted fw-semibold me-1" style="font-size:.875rem">Hai, ' . $nama . '</span>'
            . '<a href="' . base_url('/logout.php') . '" class="btn btn-outline-danger btn-sm">Logout</a>'
            . '</div>';
    } else {
        $right = '<div class="d-flex align-items-center gap-2">'
            . '<a href="' . base_url('/login.php') . '" class="btn btn-outline-success btn-sm">Login</a>'
            . '<a href="' . base_url('/register.php') . '" class="btn btn-accent btn-sm">Daftar</a>'
            . '</div>';
    }

    echo '
    <header class="site-header">
      <nav class="navbar navbar-expand-lg py-2">
        <div class="container">
          <a class="navbar-brand" href="' . base_url('/index.php') . '">FapertaFarmShop</a>
          <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-label="Menu">
            <span class="navbar-toggler-icon"></span>
          </button>
          <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">' . $navItems . '</ul>
            <div class="d-none d-lg-flex align-items-center me-3">
              <form class="header-search" method="GET" action="' . base_url('/katalog.php') . '">
                <span class="material-symbols-outlined text-muted" style="font-size:1.1rem">search</span>
                <input type="text" name="q" placeholder="Cari produk segar...">
              </form>
            </div>
            ' . $right . '
          </div>
        </div>
      </nav>
    </header>';
}

function render_flash(): void
{
    if (empty($_SESSION['flash'])) return;
    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
    $type = $flash['type'] ?? 'success';
    $msg  = $flash['message'] ?? '';
    echo '<div class="container mt-3">'
        . '<div class="alert alert-' . e($type) . ' alert-dismissible fade show" role="alert">'
        . e((string)$msg)
        . '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>'
        . '</div>'
        . '</div>';
}

?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="FapertaFarmShop — Belanja produk segar sayur & pangan langsung dari petani.">
  <title><?php echo e($title ?? 'FapertaFarmShop'); ?> – FapertaFarmShop</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet">
  <link href="<?php echo base_url('/assets/css/style.css'); ?>" rel="stylesheet">
</head>
<body>
  <?php render_nav(); ?>
  <?php render_flash(); ?>
  <main class="pb-4">