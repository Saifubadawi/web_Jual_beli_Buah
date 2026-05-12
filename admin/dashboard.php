<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/auth_check.php';

require_role('admin');

$title = 'Dashboard Admin';

// statistik
$totalProduk = (int)$pdo->query("SELECT COUNT(*) AS j FROM produk")->fetch()['j'];
$totalPesanan = (int)$pdo->query("SELECT COUNT(*) AS j FROM pesanan")->fetch()['j'];
$totalUser = (int)$pdo->query("SELECT COUNT(*) AS j FROM users WHERE role='user'")->fetch()['j'];

$pendapatanHariIniRow = $pdo->query("SELECT COALESCE(SUM(total_harga),0) AS j FROM pesanan WHERE DATE(created_at)=CURDATE() AND status IN ('diproses','dikirim','selesai')")->fetch();
$pendapatanHariIni = (int)($pendapatanHariIniRow['j'] ?? 0);

// chart penjualan mingguan
$data = [];
$labels = [];
$start = new DateTime('monday this week');
for ($i = 0; $i < 7; $i++) {
    $d = clone $start;
    $d->modify("+$i day");
    $labels[] = $d->format('D, d/m');
    $row = $pdo->prepare("SELECT COALESCE(SUM(total_harga),0) AS j FROM pesanan WHERE DATE(created_at)=:tgl AND status IN ('diproses','dikirim','selesai')");
    $row->execute([':tgl' => $d->format('Y-m-d')]);
    $data[] = (int)($row->fetch()['j'] ?? 0);
}

$recentOrders = $pdo->query("SELECT pe.id, pe.created_at, pe.total_harga, pe.status, u.nama AS nama_user FROM pesanan pe JOIN users u ON u.id=pe.id_user ORDER BY pe.created_at DESC LIMIT 6")->fetchAll();

include __DIR__ . '/../includes/admin_header.php';
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-1">Dashboard Overview</h3>
            <p class="text-muted small mb-0">Statistik dan aktivitas terbaru toko Anda.</p>
        </div>
        <div class="d-none d-md-block">
            <span class="badge text-bg-light border px-3 py-2">
                <span class="material-symbols-outlined me-1" style="font-size:1rem;vertical-align:middle">calendar_today</span>
                <?php echo date('d F Y'); ?>
            </span>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm overflow-hidden h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" 
                         style="width:52px;height:52px;background:rgba(21,66,18,0.1);color:var(--clr-primary)">
                        <span class="material-symbols-outlined" style="font-size:2rem">inventory_2</span>
                    </div>
                    <div>
                        <div class="text-muted small fw-medium">Total Produk</div>
                        <div class="fs-4 fw-bold"><?php echo number_format($totalProduk); ?></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm overflow-hidden h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" 
                         style="width:52px;height:52px;background:rgba(145,77,0,0.1);color:var(--clr-secondary)">
                        <span class="material-symbols-outlined" style="font-size:2rem">shopping_cart</span>
                    </div>
                    <div>
                        <div class="text-muted small fw-medium">Total Pesanan</div>
                        <div class="fs-4 fw-bold"><?php echo number_format($totalPesanan); ?></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm overflow-hidden h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" 
                         style="width:52px;height:52px;background:rgba(21,66,18,0.1);color:var(--clr-primary)">
                        <span class="material-symbols-outlined" style="font-size:2rem">payments</span>
                    </div>
                    <div>
                        <div class="text-muted small fw-medium">Omzet Hari Ini</div>
                        <div class="fs-4 fw-bold text-success"><?php echo e(format_rupiah($pendapatanHariIni)); ?></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm overflow-hidden h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" 
                         style="width:52px;height:52px;background:rgba(145,77,0,0.1);color:var(--clr-secondary)">
                        <span class="material-symbols-outlined" style="font-size:2rem">group</span>
                    </div>
                    <div>
                        <div class="text-muted small fw-medium">Pelanggan</div>
                        <div class="fs-4 fw-bold"><?php echo number_format($totalUser); ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Chart -->
        <div class="col-12 col-xl-7">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h5 class="fw-bold mb-4">Grafik Penjualan Mingguan</h5>
                    <div style="height: 300px; position: relative;">
                        <canvas id="chartPenjualan"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Orders -->
        <div class="col-12 col-xl-5">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="fw-bold mb-0">Pesanan Terbaru</h5>
                        <a href="<?php echo base_url('/admin/pesanan.php'); ?>" class="small text-decoration-none">Lihat Semua</a>
                    </div>
                    
                    <?php if (!$recentOrders): ?>
                        <div class="text-center py-4">
                            <span class="material-symbols-outlined text-muted" style="font-size:3rem">receipt_long</span>
                            <p class="text-muted small mt-2">Belum ada pesanan masuk.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle small">
                                <thead>
                                    <tr class="text-muted">
                                        <th>ID</th>
                                        <th>Pelanggan</th>
                                        <th>Total</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recentOrders as $o): ?>
                                        <tr>
                                            <td class="fw-bold">#<?php echo (int)$o['id']; ?></td>
                                            <td><?php echo e((string)$o['nama_user']); ?></td>
                                            <td class="fw-bold"><?php echo e(format_rupiah((int)$o['total_harga'])); ?></td>
                                            <td>
                                                <span class="badge-status badge-<?php echo e((string)$o['status']); ?>" style="font-size: 0.65rem;">
                                                    <?php echo ucfirst((string)$o['status']); ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
    const labels = <?php echo json_encode($labels, JSON_UNESCAPED_UNICODE); ?>;
    const dataPenjualan = <?php echo json_encode($data, JSON_NUMERIC_CHECK); ?>;

    const ctx = document.getElementById('chartPenjualan');
    if (ctx) {
        new Chart(ctx, {
            type: 'line',
            data: {
                labels,
                datasets: [{
                    label: 'Pendapatan (Rp)',
                    data: dataPenjualan,
                    backgroundColor: 'rgba(21, 66, 18, 0.1)',
                    borderColor: 'rgba(21, 66, 18, 1)',
                    borderWidth: 3,
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: 'rgba(21, 66, 18, 1)',
                    pointBorderWidth: 2,
                    pointRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: 'rgba(0,0,0,0.05)' },
                        ticks: {
                            callback: function(value) {
                                if (value >= 1000000) return (value / 1000000) + 'jt';
                                if (value >= 1000) return (value / 1000) + 'k';
                                return value;
                            }
                        }
                    },
                    x: {
                        grid: { display: false }
                    }
                }
            }
        });
    }
</script>

<?php include __DIR__ . '/../includes/admin_footer.php'; ?>