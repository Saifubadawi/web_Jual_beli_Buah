<?php

declare(strict_types=1);
require_once __DIR__ . '/helpers.php';
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_role('admin');

function render_flash(): void
{
    if (empty($_SESSION['flash'])) {
        return;
    }
    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
    $type = $flash['type'] ?? 'success';
    $msg = $flash['message'] ?? '';
    echo '<div class="alert alert-' . e($type) . ' alert-dismissible fade show mb-4" role="alert">'
        . e((string)$msg)
        . '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>'
        . '</div>';
}

$currentPage = basename($_SERVER['SCRIPT_NAME']);
?>
<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo e($title ?? 'Admin Dashboard'); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?php echo base_url('/assets/css/style.css'); ?>" rel="stylesheet">
</head>

<body>
    <div class="admin-layout">
        <!-- Sidebar -->
        <aside class="admin-sidebar shadow-sm">
            <div class="brand text-center">
                <a href="<?php echo base_url('/admin/dashboard.php'); ?>" class="text-white text-decoration-none d-block">
                    FapertaFarmShop
                </a>
                <div class="fs-6 fw-normal mt-1 text-white-50">Admin Panel</div>
            </div>
            <nav class="nav flex-column mt-3">
                <a class="nav-link <?php echo $currentPage === 'dashboard.php' ? 'active' : ''; ?>" href="<?php echo base_url('/admin/dashboard.php'); ?>">
                    <span class="material-symbols-outlined me-2" style="font-size:1.2rem">dashboard</span>
                    Dashboard
                </a>
                <a class="nav-link <?php echo $currentPage === 'kategori.php' ? 'active' : ''; ?>" href="<?php echo base_url('/admin/kategori.php'); ?>">
                    <span class="material-symbols-outlined me-2" style="font-size:1.2rem">category</span>
                    Kategori
                </a>
                <a class="nav-link <?php echo $currentPage === 'produk.php' ? 'active' : ''; ?>" href="<?php echo base_url('/admin/produk.php'); ?>">
                    <span class="material-symbols-outlined me-2" style="font-size:1.2rem">inventory_2</span>
                    Produk
                </a>
                <a class="nav-link <?php echo $currentPage === 'pesanan.php' ? 'active' : ''; ?>" href="<?php echo base_url('/admin/pesanan.php'); ?>">
                    <span class="material-symbols-outlined me-2" style="font-size:1.2rem">receipt_long</span>
                    Pesanan
                </a>
                <a class="nav-link <?php echo $currentPage === 'pengguna.php' ? 'active' : ''; ?>" href="<?php echo base_url('/admin/pengguna.php'); ?>">
                    <span class="material-symbols-outlined me-2" style="font-size:1.2rem">group</span>
                    Pengguna
                </a>
                <a class="nav-link <?php echo $currentPage === 'pengaturan.php' ? 'active' : ''; ?>" href="<?php echo base_url('/admin/pengaturan.php'); ?>">
                    <span class="material-symbols-outlined me-2" style="font-size:1.2rem">settings</span>
                    Pengaturan
                </a>
                <hr class="border-secondary mx-3 opacity-25">
                <a class="nav-link text-warning" href="<?php echo base_url('/index.php'); ?>">
                    <span class="material-symbols-outlined me-2" style="font-size:1.2rem">storefront</span>
                    Lihat Toko
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="admin-main">
            <!-- Topbar -->
            <header class="admin-header shadow-sm">
                <div class="d-flex align-items-center gap-3">
                    <span class="text-muted fw-medium">Hai, <?php echo e($_SESSION['nama'] ?? 'Admin'); ?></span>
                    <a href="<?php echo base_url('/logout.php'); ?>" class="btn btn-outline-danger btn-sm">Logout</a>
                </div>
            </header>

            <!-- Page Content -->
            <div class="p-4 flex-grow-1">
                <?php render_flash(); ?>
