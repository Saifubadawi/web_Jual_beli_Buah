<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/auth_check.php';

require_role('admin');


$title = 'Manajemen Pesanan';

// update status
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && !empty($_POST['pesanan_id'])) {
    verify_csrf($_POST['csrf_token'] ?? null);

    $pid = (int)($_POST['pesanan_id'] ?? 0);
    $status = trim((string)($_POST['status'] ?? 'menunggu'));
    $allowed = ['menunggu', 'diproses', 'dikirim', 'selesai'];
    if ($pid > 0 && in_array($status, $allowed, true)) {
        $pdo->prepare('UPDATE pesanan SET status=:st WHERE id=:id')->execute([':st' => $status, ':id' => $pid]);
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Status pesanan diperbarui.'];
    }
    header('Location: ' . base_url('/admin/pesanan.php'));
    exit;
}

$orders = $pdo->query('SELECT pe.id, pe.created_at, pe.total_harga, pe.status, u.nama AS nama_user FROM pesanan pe JOIN users u ON u.id = pe.id_user ORDER BY pe.created_at DESC')->fetchAll();

include __DIR__ . '/../includes/admin_header.php';
?>

<div>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold mb-0">Manajemen Pesanan</h3>
    </div>

    <div class="table-responsive mt-3">
        <table class="table table-bordered align-middle bg-white shadow-sm">
            <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>User</th>
                    <th>Tanggal</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th style="width:220px;">Ubah Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $o): ?>
                    <tr>
                        <td class="fw-bold">#<?php echo (int)$o['id']; ?></td>
                        <td><?php echo e((string)$o['nama_user']); ?></td>
                        <td><?php echo e(date('d/m/Y H:i', strtotime((string)$o['created_at']))); ?></td>
                        <td class="fw-bold text-success"><?php echo e(format_rupiah((int)$o['total_harga'])); ?></td>
                        <td>
                            <?php
                            $mapBadge = [
                                'menunggu' => 'warning',
                                'diproses' => 'info',
                                'dikirim' => 'primary',
                                'selesai' => 'success'
                            ];
                            $cls = $mapBadge[$o['status']] ?? 'secondary';
                            ?>
                            <span class="badge text-bg-<?php echo e($cls); ?>"><?php echo e((string)$o['status']); ?></span>
                        </td>
                        <td>
                            <form method="POST" class="d-flex gap-2 flex-wrap">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="pesanan_id" value="<?php echo (int)$o['id']; ?>">
                                <select name="status" class="form-select form-select-sm" style="min-width:180px;">
                                    <?php foreach (['menunggu', 'diproses', 'dikirim', 'selesai'] as $st): ?>
                                        <option value="<?php echo e($st); ?>" <?php echo ($o['status'] === $st) ? 'selected' : ''; ?>><?php echo e($st); ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <button class="btn btn-accent btn-sm" type="submit">Simpan</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$orders): ?>
                    <tr>
                        <td colspan="6">
                            <div class="alert alert-warning mb-0">Belum ada pesanan.</div>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/../includes/admin_footer.php'; ?>