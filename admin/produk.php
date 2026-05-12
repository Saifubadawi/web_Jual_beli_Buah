<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/auth_check.php';

require_role('admin');

$uploadDir = __DIR__ . '/../uploads/produk/';
if (!is_dir($uploadDir)) {
    @mkdir($uploadDir, 0777, true);
}

$title = 'Manajemen Produk';

// Ambil daftar kategori
$kategoriList = $pdo->query('SELECT id, nama_kategori, slug FROM kategori ORDER BY id ASC')->fetchAll();

// Delete
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && !empty($_POST['hapus_produk_id'])) {
    verify_csrf($_POST['csrf_token'] ?? null);
    $id = (int)($_POST['hapus_produk_id'] ?? 0);
    if ($id > 0) {
        $pdo->prepare('DELETE FROM produk WHERE id=:id')->execute([':id' => $id]);
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Produk dihapus.'];
    }
    header('Location: ' . base_url('/admin/produk.php'));
    exit;
}

// Insert / Update
$editingId = null;
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && !empty($_POST['simpan_produk'])) {
    verify_csrf($_POST['csrf_token'] ?? null);

    $id = !empty($_POST['id_produk']) ? (int)$_POST['id_produk'] : 0;
    $nama = trim((string)($_POST['nama_produk'] ?? ''));
    $idKategori = (int)($_POST['id_kategori'] ?? 0);
    $deskripsi = trim((string)($_POST['deskripsi'] ?? ''));
    $harga = (int)($_POST['harga'] ?? 0);
    $stok = (int)($_POST['stok'] ?? 0);
    $status = ($_POST['status'] ?? 'aktif') === 'nonaktif' ? 'nonaktif' : 'aktif';

    $fotoName = null;
    $uploadError = null;
    if (!empty($_FILES['foto']['name'])) {
        if ($_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo((string)$_FILES['foto']['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'webp'];
            if (in_array($ext, $allowed, true)) {
                $tempName = 'produk_' . bin2hex(random_bytes(8)) . '.' . $ext;
                if (move_uploaded_file($_FILES['foto']['tmp_name'], $uploadDir . $tempName)) {
                    $fotoName = $tempName;
                } else {
                    $uploadError = 'Gagal menyimpan file foto.';
                }
            } else {
                $uploadError = 'Format file foto tidak diizinkan.';
            }
        }
    }

    if ($uploadError) {
        $_SESSION['flash'] = ['type' => 'danger', 'message' => $uploadError];
        header('Location: ' . base_url('/admin/produk.php'));
        exit;
    }

    if ($id > 0) {
        if ($fotoName) {
            $pdo->prepare('UPDATE produk SET id_kategori=:kid,nama_produk=:nama,deskripsi=:des,harga=:harga,stok=:stok,foto=:foto,status=:status WHERE id=:id')
                ->execute([':kid' => $idKategori, ':nama' => $nama, ':des' => $deskripsi, ':harga' => $harga, ':stok' => $stok, ':foto' => $fotoName, ':status' => $status, ':id' => $id]);
        } else {
            $pdo->prepare('UPDATE produk SET id_kategori=:kid,nama_produk=:nama,deskripsi=:des,harga=:harga,stok=:stok,status=:status WHERE id=:id')
                ->execute([':kid' => $idKategori, ':nama' => $nama, ':des' => $deskripsi, ':harga' => $harga, ':stok' => $stok, ':status' => $status, ':id' => $id]);
        }
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Produk diperbarui.'];
    } else {
        $pdo->prepare('INSERT INTO produk (id_kategori,nama_produk,deskripsi,harga,stok,foto,status) VALUES (:kid,:nama,:des,:harga,:stok,:foto,:status)')
            ->execute([':kid' => $idKategori,':nama' => $nama,':des' => $deskripsi,':harga' => $harga,':stok' => $stok,':foto' => $fotoName,':status' => $status]);
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Produk ditambahkan.'];
    }

    header('Location: ' . base_url('/admin/produk.php'));
    exit;
}

// ambil produk
$produkList = $pdo->query('SELECT p.*, k.nama_kategori FROM produk p JOIN kategori k ON k.id=p.id_kategori ORDER BY p.created_at DESC')->fetchAll();

$editProduk = null;
if (isset($_GET['edit_id'])) {
    $editingId = (int)$_GET['edit_id'];
    $stmt = $pdo->prepare('SELECT * FROM produk WHERE id=:id');
    $stmt->execute([':id' => $editingId]);
    $editProduk = $stmt->fetch();
}

include __DIR__ . '/../includes/admin_header.php';
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold mb-0">Manajemen Produk</h3>
        <button class="btn btn-primary-fill d-md-none" type="button" data-bs-toggle="collapse" data-bs-target="#formProduk">
            <span class="material-symbols-outlined" style="font-size:1.2rem">add</span>
            Produk Baru
        </button>
    </div>

    <div class="row g-4">
        <!-- Form -->
        <div class="col-12 col-xl-4">
            <div class="collapse d-md-block" id="formProduk">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h5 class="fw-bold mb-4"><?php echo $editProduk ? 'Edit Produk' : 'Tambah Produk Baru'; ?></h5>
                        <form method="POST" enctype="multipart/form-data">
                            <?php echo csrf_field(); ?>
                            <input type="hidden" name="id_produk" value="<?php echo (int)($editProduk['id'] ?? 0); ?>">

                            <div class="mb-3">
                                <label class="form-label">Kategori</label>
                                <select name="id_kategori" class="form-select" required>
                                    <option value="" disabled <?php echo empty($editProduk) ? 'selected' : ''; ?>>Pilih Kategori</option>
                                    <?php foreach ($kategoriList as $k): ?>
                                        <option value="<?php echo (int)$k['id']; ?>" <?php echo !empty($editProduk) && (int)$editProduk['id_kategori'] === (int)$k['id'] ? 'selected' : ''; ?>>
                                            <?php echo e($k['nama_kategori']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Nama Produk</label>
                                <input name="nama_produk" class="form-control" value="<?php echo e($editProduk['nama_produk'] ?? ''); ?>" required placeholder="Contoh: Apel Malang">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Deskripsi</label>
                                <textarea name="deskripsi" class="form-control" rows="3" required placeholder="Jelaskan kesegaran produk Anda..."><?php echo e($editProduk['deskripsi'] ?? ''); ?></textarea>
                            </div>

                            <div class="row g-2 mb-3">
                                <div class="col-6">
                                    <label class="form-label">Harga (Rp)</label>
                                    <input name="harga" type="number" class="form-control" min="1" value="<?php echo (int)($editProduk['harga'] ?? 0); ?>" required>
                                </div>
                                <div class="col-6">
                                    <label class="form-label">Stok</label>
                                    <input name="stok" type="number" class="form-control" min="0" value="<?php echo (int)($editProduk['stok'] ?? 0); ?>" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select">
                                    <option value="aktif" <?php echo !empty($editProduk) && ($editProduk['status'] ?? '') === 'aktif' ? 'selected' : ''; ?>>Aktif</option>
                                    <option value="nonaktif" <?php echo !empty($editProduk) && ($editProduk['status'] ?? '') === 'nonaktif' ? 'selected' : ''; ?>>Nonaktif</option>
                                </select>
                            </div>

                            <div class="mb-4">
                                <label class="form-label">Foto Produk</label>
                                <input type="file" name="foto" class="form-control" accept="image/*">
                                <?php if (!empty($editProduk['foto'])): ?>
                                    <img src="<?php echo base_url('/uploads/produk/' . $editProduk['foto']); ?>" class="img-thumbnail mt-2" style="max-height:100px">
                                <?php endif; ?>
                            </div>

                            <button class="btn btn-primary-fill w-100 justify-content-center" type="submit" name="simpan_produk" value="1">
                                <?php echo $editProduk ? 'Simpan Perubahan' : 'Tambah Produk'; ?>
                            </button>
                            <?php if ($editProduk): ?>
                                <a href="<?php echo base_url('/admin/produk.php'); ?>" class="btn btn-outline-secondary w-100 mt-2">Batal</a>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- List -->
        <div class="col-12 col-xl-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">Produk</th>
                                    <th>Kategori</th>
                                    <th>Harga</th>
                                    <th>Stok</th>
                                    <th>Status</th>
                                    <th class="text-end pe-3">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($produkList as $p): ?>
                                    <tr>
                                        <td class="ps-3">
                                            <div class="d-flex align-items-center gap-3">
                                                <div class="rounded overflow-hidden flex-shrink-0" style="width:50px;height:50px;background:#f8f9fa">
                                                    <img src="<?php echo !empty($p['foto']) ? base_url('/uploads/produk/' . e($p['foto'])) : 'https://placehold.co/100x100?text=No+Foto'; ?>" class="w-100 h-100" style="object-fit:cover">
                                                </div>
                                                <div class="fw-bold" style="font-size:.875rem"><?php echo e($p['nama_produk']); ?></div>
                                            </div>
                                        </td>
                                        <td><span class="badge text-bg-light border"><?php echo e($p['nama_kategori']); ?></span></td>
                                        <td class="fw-bold text-success small"><?php echo e(format_rupiah((int)$p['harga'])); ?></td>
                                        <td class="small"><?php echo (int)$p['stok']; ?> unit</td>
                                        <td>
                                            <?php if (($p['status'] ?? '') === 'aktif'): ?>
                                                <span class="badge rounded-pill text-bg-success-subtle text-success px-3">aktif</span>
                                            <?php else: ?>
                                                <span class="badge rounded-pill text-bg-secondary-subtle text-secondary px-3">nonaktif</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end pe-3">
                                            <div class="d-flex gap-2 justify-content-end">
                                                <a href="<?php echo base_url('/admin/produk.php?edit_id=' . (int)$p['id']); ?>" class="btn btn-outline-primary btn-sm rounded-pill px-3">Edit</a>
                                                <form method="POST" onsubmit="return confirm('Hapus produk ini?')">
                                                    <?php echo csrf_field(); ?>
                                                    <input type="hidden" name="hapus_produk_id" value="<?php echo (int)$p['id']; ?>">
                                                    <button class="btn btn-outline-danger btn-sm rounded-pill px-3" type="submit">Hapus</button>
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