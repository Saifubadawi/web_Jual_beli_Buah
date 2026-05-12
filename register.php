<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/helpers.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (!empty($_SESSION['user_id'])) {
    header('Location: ' . base_url('/index.php'));
    exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && !empty($_POST['register'])) {
    verify_csrf($_POST['csrf_token'] ?? null);

    $nama    = trim((string)($_POST['nama'] ?? ''));
    $email   = trim((string)($_POST['email'] ?? ''));
    $pass    = (string)($_POST['password'] ?? '');
    $telepon = trim((string)($_POST['telepon'] ?? ''));
    $alamat  = trim((string)($_POST['alamat'] ?? ''));

    if ($nama === '' || $email === '' || $pass === '') {
        $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Nama, Email, dan Password wajib diisi.'];
        header('Location: ' . base_url('/register.php'));
        exit;
    }

    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
    $stmt->execute([':email' => $email]);
    if ($stmt->fetch()) {
        $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Email sudah terdaftar.'];
        header('Location: ' . base_url('/register.php'));
        exit;
    }

    $hashed = password_hash($pass, PASSWORD_DEFAULT);
    $pdo->prepare('INSERT INTO users (nama, email, password, telepon, alamat, role) VALUES (:nama, :email, :pass, :telp, :almt, "user")')
        ->execute([
            ':nama'  => $nama,
            ':email' => $email,
            ':pass'  => $hashed,
            ':telp'  => $telepon,
            ':almt'  => $alamat,
        ]);

    $_SESSION['flash'] = ['type' => 'success', 'message' => 'Registrasi berhasil! Silakan login.'];
    header('Location: ' . base_url('/login.php'));
    exit;
}

$title = 'Daftar Akun';
include __DIR__ . '/includes/header.php';
?>

<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-12 col-md-6 col-lg-5">
      <div class="auth-card shadow-lg">
        <div class="text-center mb-4">
          <span class="brand-name">FapertaFarmShop</span>
          <h2 style="font-size:1.25rem;font-weight:700;color:var(--clr-primary)">Mulai Perjalanan Organikmu</h2>
          <p class="text-muted" style="font-size:.875rem">Daftar sekarang untuk kemudahan berbelanja</p>
        </div>

        <form method="POST">
          <?php echo csrf_field(); ?>
          <input type="hidden" name="register" value="1">

          <div class="mb-3">
            <label class="form-label">Nama Lengkap</label>
            <div class="input-group">
              <span class="input-group-text bg-transparent border-end-0">
                <span class="material-symbols-outlined" style="font-size:1.2rem;color:var(--clr-outline)">person</span>
              </span>
              <input type="text" name="nama" class="form-control border-start-0" placeholder="Nama Anda" required>
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label">Email</label>
            <div class="input-group">
              <span class="input-group-text bg-transparent border-end-0">
                <span class="material-symbols-outlined" style="font-size:1.2rem;color:var(--clr-outline)">mail</span>
              </span>
              <input type="email" name="email" class="form-control border-start-0" placeholder="nama@email.com" required>
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label">Password</label>
            <div class="input-group">
              <span class="input-group-text bg-transparent border-end-0">
                <span class="material-symbols-outlined" style="font-size:1.2rem;color:var(--clr-outline)">lock</span>
              </span>
              <input type="password" name="password" class="form-control border-start-0" placeholder="Min. 8 karakter" required>
            </div>
          </div>

          <div class="row g-3 mb-3">
            <div class="col-md-12">
              <label class="form-label">Telepon</label>
              <div class="input-group">
                <span class="input-group-text bg-transparent border-end-0">
                  <span class="material-symbols-outlined" style="font-size:1.2rem;color:var(--clr-outline)">call</span>
                </span>
                <input type="text" name="telepon" class="form-control border-start-0" placeholder="08xxx">
              </div>
            </div>
          </div>

          <div class="mb-4">
            <label class="form-label">Alamat Lengkap</label>
            <textarea name="alamat" class="form-control" rows="2" placeholder="Jl. Contoh No. 123..."></textarea>
          </div>

          <button class="btn-primary-fill w-100 justify-content-center py-2 mb-3" type="submit">
            Daftar Sekarang
          </button>

          <div class="text-center">
            <p class="mb-0" style="font-size:.875rem;color:var(--clr-on-surface-var)">
              Sudah punya akun? <a href="<?php echo base_url('/login.php'); ?>" style="color:var(--clr-secondary);font-weight:700">Masuk di sini</a>
            </p>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>