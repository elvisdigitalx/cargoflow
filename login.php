<?php
/**
 * CargoFlow — Admin login
 */
require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/auth.php';

// Already logged in → dashboard
if (is_logged_in()) {
    redirect(base_url('admin/index.php'));
}

$error = '';
$identifier = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $identifier = trim($_POST['identifier'] ?? '');
    $password = (string) ($_POST['password'] ?? '');
    $remember = !empty($_POST['remember']);

    if ($identifier === '' || $password === '') {
        $error = 'Please enter your email/username and password.';
    } else {
        $result = attempt_login($identifier, $password, $remember);
        if ($result['success']) {
            $next = $_GET['next'] ?? '';
            if ($next && str_starts_with($next, '/')) {
                redirect(base_url(ltrim($next, '/')));
            }
            redirect(base_url('admin/index.php'));
        }
        $error = $result['message'];
    }
}

$cf_theme = current_theme();
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="<?= e($cf_theme) ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Login — <?= e(setting('site_name', 'CargoFlow')) ?></title>
    <link rel="icon" href="<?= base_url('assets/img/favicon.svg') ?>" type="image/svg+xml">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">
    <link href="<?= base_url('assets/css/style.css') ?>" rel="stylesheet">
    <style>
        .auth-wrap { min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 2rem 0;
            background: radial-gradient(900px 500px at 90% -10%, rgba(249,115,22,.15), transparent 60%),
                        radial-gradient(800px 500px at 0% 110%, rgba(232,33,39,.15), transparent 55%), var(--cf-surface); }
        .auth-card { width: 100%; max-width: 440px; }
    </style>
</head>
<body>
<div class="auth-wrap">
    <div class="auth-card">
        <div class="text-center mb-4">
            <a href="<?= base_url('index.php') ?>" class="navbar-brand d-inline-flex align-items-center gap-2 fs-3">
                <svg width="34" height="34" viewBox="0 0 32 32" fill="none"><rect x="1" y="1" width="30" height="30" rx="8" fill="#e82127"/><path d="M9 20l4-8 3 6 3-6 4 8" stroke="#fff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" fill="none"/></svg>
                CargoFlow
            </a>
        </div>
        <div class="form-card p-4 p-md-5">
            <h4 class="fw-bold mb-1">Welcome back</h4>
            <p class="text-muted-2 mb-4">Sign in to your admin dashboard.</p>

            <?php if ($error): ?>
                <div class="alert alert-danger d-flex align-items-center gap-2 py-2"><i class="bi bi-exclamation-circle"></i><?= e($error) ?></div>
            <?php endif; ?>

            <form method="post" action="<?= base_url('login.php' . (isset($_GET['next']) ? '?next=' . urlencode($_GET['next']) : '')) ?>">
                <?= csrf_field() ?>
                <div class="mb-3">
                    <label class="form-label" for="identifier">Email or username</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-person"></i></span>
                        <input type="text" class="form-control" id="identifier" name="identifier" value="<?= e($identifier) ?>" required autofocus>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="password">Password</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-lock"></i></span>
                        <input type="password" class="form-control" id="password" name="password" required>
                        <button class="btn btn-outline-secondary" type="button" id="togglePass" tabindex="-1"><i class="bi bi-eye"></i></button>
                    </div>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="remember" name="remember" value="1">
                        <label class="form-check-label small" for="remember">Remember me</label>
                    </div>
                </div>
                <button type="submit" class="btn btn-brand w-100 py-2"><i class="bi bi-box-arrow-in-right me-1"></i> Sign in</button>
            </form>
        </div>
        <p class="text-center mt-3 mb-0">
            <a href="<?= base_url('index.php') ?>" class="small"><i class="bi bi-arrow-left me-1"></i>Back to website</a>
        </p>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.getElementById('togglePass').addEventListener('click', function () {
    var p = document.getElementById('password');
    p.type = p.type === 'password' ? 'text' : 'password';
});
</script>
</body>
</html>
