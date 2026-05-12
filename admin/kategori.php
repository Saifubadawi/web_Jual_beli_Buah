<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/auth_check.php';

require_role('admin');

$uploadDir = __DIR__ . '/../uploads/kategori/';
if (!is_dir($uploadDir)) {
    @mkdir($uploadDir, 0777, true);
}

$title = 'Manajemen Kategori';

// Delete
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && !empty($_POST['hapus_kategori_id'])) {
    verify_csrf($_POST['csrf_token'] ?? null);
    $id = (int)($_POST['hapus_kategori_id'] ?? 0);
    if ($id > 0) {
        $pdo->prepare('DELETE FROM kategori WHERE id=:id')->execute([':id' => $id]);
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Kategori dihapus.'];
    }
    header('Location: ' . base_url('/admin/kategori.php'));
    exit;
}

// Insert / Update
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && !empty($_POST['simpan_kategori'])) {
    verify_csrf($_POST['csrf_token'] ?? null);

    $id = !empty($_POST['id_kategori']) ? (int)$_POST['id_kategori'] : 0;
    $nama = trim((string)($_POST['nama_kategori'] ?? ''));
    $slug = trim((string)($_POST['slug'] ?? ''));

    if ($nama === '' || $slug === '') {
        $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Nama dan Slug wajib diisi.'];
        header('Location: ' . base_url('/admin/kategori.php'));
        exit;
    }

    $fotoName = null;
    if (!empty($_FILES['foto']['name'])) {
        $ext = strtolower(pathinfo((string)$_FILES['foto']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        if (in_array($ext, $allowed, true)) {
            $tempName = 'cat_' . bin2hex(random_bytes(8)) . '.' . $ext;
            if (move_uploaded_file($_FILES['foto']['tmp_name'], $uploadDir . $tempName)) {
                $fotoName = $tempName;
            }
        }
    }

    if ($id > 0) {
        if ($fotoName) {
            $pdo->prepare('UPDATE kategori SET nama_kategori=:nama, slug=:slug, foto=:foto WHERE id=:id')
                ->execute([':nama' => $nama, ':slug' => $slug, ':foto' => $fotoName, ':id' => $id]);
        } else {
            $pdo->prepare('UPDATE kategori SET nama_kategori=:nama, slug=:slug WHERE id=:id')
                ->execute([':nama' => $nama, ':slug' => $slug, ':id' => $id]);
        }
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Kategori diperbarui.'];
    } else {
        $pdo->prepare('INSERT INTO kategori (nama_kategori, slug, foto) VALUES (:nama, :slug, :foto)')
            ->execute([':nama' => $nama, ':slug' => $slug, ':foto' => $fotoName]);
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Kategori ditambahkan.'];
    }

    header('Location: ' . base_url('/admin/kategori.php'));
    exit;
}

$kategoriList = $pdo->query('SELECT * FROM kategori ORDER BY id ASC')->fetchAll();

$editKat = null;
if (isset($_GET['edit_id'])) {
    $eid = (int)$_GET['edit_id'];
    $stmt = $pdo->prepare('SELECT * FROM kategori WHERE id=:id');
    $stmt->execute([':id' => $eid]);
    $editKat = $stmt->fetch();
}

include __DIR__ . '/../includes/admin_header.php';
?>

<div class="container-fluid">
    <h3 class="fw-bold mb-4">Manajemen Kategori</h3>

    <div class="row g-4">
        <!-- Form -->
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="fw-bold mb-3"><?php echo $editKat ? 'Edit Kategori' : 'Tambah Kategori'; ?></h5>
                    <form method="POST" enctype="multipart/form-data">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="id_kategori" value="<?php echo (int)($editKat['id'] ?? 0); ?>">

                        <div class="mb-3">
                            <label class="form-label">Nama Kategori</label>
                            <input name="nama_kategori" class="form-control" value="<?php echo e($editKat['nama_kategori'] ?? ''); ?>" required placeholder="Contoh: Sayuran Segar">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Slug</label>
                            <input name="slug" class="form-control" value="<?php echo e($editKat['slug'] ?? ''); ?>" required placeholder="sayuran-segar">
                            <div class="form-text small">Gunakan huruf kecil dan tanda hubung (-).</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Foto Kategori</label>
                            <input type="file" name="foto" class="form-control" accept="image/*">
                            <?php if (!empty($editKat['foto'])): ?>
                                <img src="<?php echo base_url('/uploads/kategori/' . $editKat['foto']); ?>" class="img-thumbnail mt-2" style="max-height:80px">
                            <?php endif; ?>
                        </div>

                        <button class="btn btn-accent w-100" type="submit" name="simpan_kategori" value="1">
                            <?php echo $editKat ? 'Simpan Perubahan' : 'Tambah Kategori'; ?>
                        </button>
                        <?php if ($editKat): ?>
                            <a href="<?php echo base_url('/admin/kategori.php'); ?>" class="btn btn-outline-secondary w-100 mt-2">Batal</a>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
        </div>

        <!-- List -->
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">Foto</th>
                                    <th>Kategori</th>
                                    <th>Slug</th>
                                    <th class="text-end pe-3">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($kategoriList as $k): ?>
                                    <tr>
                                        <td class="ps-3">
                                            <div class="rounded overflow-hidden" style="width:50px;height:50px;background:#f8f9fa">
                                                <?php if (!empty($k['foto'])): ?>
                                                    <img src="<?php echo base_url('/uploads/kategori/' . e($k['foto'])); ?>" class="w-100 h-100" style="object-fit:cover">
                                                <?php else: ?>
                                                    <div class="w-100 h-100 d-flex align-items-center justify-content-center text-muted small">No Img</div>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td><div class="fw-bold"><?php echo e($k['nama_kategori']); ?></div></td>
                                        <td><code class="text-muted"><?php echo e($k['slug']); ?></code></td>
                                        <td class="text-end pe-3">
                                            <div class="d-flex gap-2 justify-content-end">
                                                <a href="<?php echo base_url('/admin/kategori.php?edit_id=' . (int)$k['id']); ?>" class="btn btn-outline-primary btn-sm">Edit</a>
                                                <form method="POST" onsubmit="return confirm('Hapus kategori ini?')">
                                                    <?php echo csrf_field(); ?>
                                                    <input type="hidden" name="hapus_kategori_id" value="<?php echo (int)$k['id']; ?>">
                                                    <button class="btn btn-outline-danger btn-sm" type="submit">Hapus</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/admin_footer.php'; ?>
