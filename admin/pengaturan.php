<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/auth_check.php';

require_role('admin');


$title = 'Pengaturan Toko';

// ambil pengaturan
$peng = $pdo->query('SELECT * FROM pengaturan ORDER BY id ASC LIMIT 1')->fetch();
$peng = $peng ?: ['id' => 1, 'nama_toko' => '', 'deskripsi' => '', 'telepon' => '', 'alamat' => '', 'logo' => null];

// update pengaturan
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && (!empty($_POST['simpan_pengaturan']) || !empty($_FILES['logo']['name']))) {
    verify_csrf($_POST['csrf_token'] ?? null);

    $namaToko = trim((string)($_POST['nama_toko'] ?? ''));
    $deskripsi = trim((string)($_POST['deskripsi'] ?? ''));
    $telepon = trim((string)($_POST['telepon'] ?? ''));
    $alamat = trim((string)($_POST['alamat'] ?? ''));

    $logoName = $peng['logo'];
    if (!empty($_FILES['logo']['name']) && isset($_FILES['logo']['tmp_name'])) {
        $ext = strtolower(pathinfo((string)$_FILES['logo']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        if (in_array($ext, $allowed, true)) {
            $uploadDir = __DIR__ . '/../uploads/';
            if (!is_dir($uploadDir)) {
                @mkdir($uploadDir, 0777, true);
            }
            $logoName = 'logo_' . bin2hex(random_bytes(8)) . '.' . $ext;
            move_uploaded_file($_FILES['logo']['tmp_name'], $uploadDir . $logoName);
        }
    }

    if ($namaToko === '') {
        $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Nama toko wajib diisi.'];
        header('Location: ' . base_url('/admin/pengaturan.php'));
        exit;
    }

    $cek = $pdo->prepare('SELECT COUNT(*) FROM pengaturan WHERE id=:id');
    $cek->execute([':id' => (int)$peng['id']]);
    if ($cek->fetchColumn() > 0) {
        $pdo->prepare('UPDATE pengaturan SET nama_toko=:nama, deskripsi=:des, telepon=:tel, alamat=:al, logo=:logo WHERE id=:id')
            ->execute([':nama' => $namaToko, ':des' => $deskripsi, ':tel' => $telepon, ':al' => $alamat, ':logo' => $logoName, ':id' => (int)$peng['id']]);
    } else {
        $pdo->prepare('INSERT INTO pengaturan (id, nama_toko, deskripsi, telepon, alamat, logo) VALUES (:id, :nama, :des, :tel, :al, :logo)')
            ->execute([':id' => (int)$peng['id'], ':nama' => $namaToko, ':des' => $deskripsi, ':tel' => $telepon, ':al' => $alamat, ':logo' => $logoName]);
    }

    $_SESSION['flash'] = ['type' => 'success', 'message' => 'Pengaturan toko diperbarui.'];
    header('Location: ' . base_url('/admin/pengaturan.php'));
    exit;
}

// ganti password admin
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && !empty($_POST['ganti_password_admin'])) {
    verify_csrf($_POST['csrf_token'] ?? null);

    $old = (string)($_POST['old_password'] ?? '');
    $new = (string)($_POST['new_password'] ?? '');

    if ($new === '' || mb_strlen($new) < 6) {
        $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Password baru minimal 6 karakter.'];
        header('Location: ' . base_url('/admin/pengaturan.php'));
        exit;
    }

    $admin = $pdo->query("SELECT id, password FROM users WHERE role='admin' LIMIT 1")->fetch();
    if (!$admin || !password_verify($old, $admin['password'])) {
        $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Password lama salah.'];
        header('Location: ' . base_url('/admin/pengaturan.php'));
        exit;
    }

    $hash = password_hash($new, PASSWORD_DEFAULT);
    $pdo->prepare('UPDATE users SET password=:pw WHERE id=:id')->execute([':pw' => $hash, ':id' => (int)$admin['id']]);

    $_SESSION['flash'] = ['type' => 'success', 'message' => 'Password admin berhasil diganti.'];
    header('Location: ' . base_url('/admin/pengaturan.php'));
    exit;
}

include __DIR__ . '/../includes/admin_header.php';
?>

<div>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold mb-0">Pengaturan Toko</h3>
    </div>

    <div class="row g-4 mt-1">
        <div class="col-12 col-lg-7">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="fw-bold mb-3">Info Toko</h5>

                    <form method="POST" enctype="multipart/form-data">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="simpan_pengaturan" value="1">

                        <div class="mb-3">
                            <label class="form-label">Nama Toko</label>
                            <input class="form-control" name="nama_toko" value="<?php echo e((string)$peng['nama_toko']); ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Deskripsi</label>
                            <textarea class="form-control" name="deskripsi" rows="3"><?php echo e((string)$peng['deskripsi']); ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Nomor WA / Telepon</label>
                            <input class="form-control" name="telepon" value="<?php echo e((string)($peng['telepon'] ?? '')); ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Alamat</label>
                            <textarea class="form-control" name="alamat" rows="3"><?php echo e((string)($peng['alamat'] ?? '')); ?></textarea>
                        </div>


                        <button class="btn btn-accent" type="submit">Simpan Pengaturan</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-5">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="fw-bold mb-3">Ganti Password Admin</h5>

                    <form method="POST">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="ganti_password_admin" value="1">

                        <div class="mb-3">
                            <label class="form-label">Password Lama</label>
                            <input class="form-control" name="old_password" type="password" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Password Baru</label>
                            <input class="form-control" name="new_password" type="password" required minlength="6">
                            <div class="form-text">Minimal 6 karakter.</div>
                        </div>

                        <button class="btn btn-accent w-100" type="submit">Ganti Password</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/admin_footer.php'; ?>