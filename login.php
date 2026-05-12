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

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && !empty($_POST['login'])) {
    verify_csrf($_POST['csrf_token'] ?? null);

    $email = trim((string)($_POST['email'] ?? ''));
    $pass  = (string)($_POST['password'] ?? '');

    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch();

    if ($user && password_verify($pass, (string)$user['password'])) {
        if (($user['status'] ?? 'aktif') !== 'aktif') {
            $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Akun Anda dinonaktifkan.'];
        } else {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['nama']    = $user['nama'];
            $_SESSION['role']    = $user['role'];

            $_SESSION['flash'] = ['type' => 'success', 'message' => 'Selamat datang kembali, ' . $user['nama'] . '!'];
            header('Location: ' . base_url('/index.php'));
            exit;
        }
    } else {
        $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Email atau password salah.'];
    }
    header('Location: ' . base_url('/login.php'));
    exit;
}

$title = 'Login';
include __DIR__ . '/includes/header.php';
?>

<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-12 col-md-5">
      <div class="auth-card shadow-lg">
        <div class="text-center mb-4">
          <span class="brand-name">FapertaFarmShop</span>
          <h2 style="font-size:1.25rem;font-weight:700;color:var(--clr-primary)">Selamat Datang Kembali</h2>
          <p class="text-muted" style="font-size:.875rem">Masuk untuk mulai belanja produk segar</p>
        </div>

        <form method="POST">
          <?php echo csrf_field(); ?>
          <input type="hidden" name="login" value="1">

          <div class="mb-3">
            <label class="form-label">Email</label>
            <div class="input-group">
              <span class="input-group-text bg-transparent border-end-0">
                <span class="material-symbols-outlined" style="font-size:1.2rem;color:var(--clr-outline)">mail</span>
              </span>
              <input type="email" name="email" class="form-control border-start-0" placeholder="nama@email.com" required>
            </div>
          </div>

          <div class="mb-4">
            <label class="form-label">Password</label>
            <div class="input-group">
              <span class="input-group-text bg-transparent border-end-0">
                <span class="material-symbols-outlined" style="font-size:1.2rem;color:var(--clr-outline)">lock</span>
              </span>
              <input type="password" name="password" class="form-control border-start-0" placeholder="••••••••" required>
            </div>
          </div>

          <button class="btn-primary-fill w-100 justify-content-center py-2 mb-3" type="submit">
            Masuk Sekarang
          </button>

          <div class="text-center">
            <p class="mb-0" style="font-size:.875rem;color:var(--clr-on-surface-var)">
              Belum punya akun? <a href="<?php echo base_url('/register.php'); ?>" style="color:var(--clr-secondary);font-weight:700">Daftar di sini</a>
            </p>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>