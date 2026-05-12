<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/auth_check.php';

require_role('admin');


$title = 'Manajemen Pengguna';

// toggle status
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && !empty($_POST['user_id'])) {
    verify_csrf($_POST['csrf_token'] ?? null);
    $uid = (int)($_POST['user_id'] ?? 0);
    $status = trim((string)($_POST['status'] ?? 'aktif'));
    if ($uid > 0 && in_array($status, ['aktif', 'nonaktif'], true)) {
        $pdo->prepare('UPDATE users SET status=:st WHERE id=:id')->execute([':st' => $status, ':id' => $uid]);
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Status pengguna diperbarui.'];
    }
    header('Location: ' . base_url('/admin/pengguna.php'));
    exit;
}

$users = $pdo->query("SELECT id, nama, email, created_at, role, status FROM users WHERE role='user' ORDER BY created_at DESC")->fetchAll();

include __DIR__ . '/../includes/admin_header.php';
?>

<div>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold mb-0">Manajemen Pengguna</h3>
    </div>

    <div class="table-responsive mt-3">
        <table class="table table-bordered align-middle bg-white shadow-sm">
            <thead class="table-light">
                <tr>
                    <th>Nama</th>
                    <th>Email</th>
                    <th>Tanggal daftar</th>
                    <th>Status</th>
                    <th style="width:220px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $u): ?>
                    <tr>
                        <td class="fw-bold"><?php echo e((string)$u['nama']); ?></td>
                        <td><?php echo e((string)$u['email']); ?></td>
                        <td><?php echo e(date('d/m/Y H:i', strtotime((string)$u['created_at']))); ?></td>
                        <td>
                            <?php $cls = ((string)$u['status'] === 'aktif') ? 'success' : 'secondary'; ?>
                            <span class="badge text-bg-<?php echo e($cls); ?>"><?php echo e((string)$u['status']); ?></span>
                        </td>
                        <td>
                            <form method="POST" class="d-flex gap-2 flex-wrap">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="user_id" value="<?php echo (int)$u['id']; ?>">
                                <select name="status" class="form-select form-select-sm" style="min-width:160px;">
                                    <option value="aktif" <?php echo ($u['status'] === 'aktif') ? 'selected' : ''; ?>>Aktif</option>
                                    <option value="nonaktif" <?php echo ($u['status'] === 'nonaktif') ? 'selected' : ''; ?>>Nonaktif</option>
                                </select>
                                <button class="btn btn-accent btn-sm" type="submit">Simpan</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$users): ?>
                    <tr>
                        <td colspan="5">
                            <div class="alert alert-warning mb-0">Tidak ada user.</div>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/../includes/admin_footer.php'; ?>