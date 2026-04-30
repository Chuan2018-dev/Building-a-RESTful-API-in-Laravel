<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

start_secure_session();
ensure_default_admin($pdo);

if (!empty($_SESSION['admin_id'])) {
    header('Location: ../dashboard/index.php');
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Your session expired. Please try again.';
    }

    $identifier = trim((string) ($_POST['identifier'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    if ($identifier === '' || $password === '') {
        $errors[] = 'Enter your username/email and password.';
    }

    if (!$errors) {
        $stmt = $pdo->prepare('SELECT id, username, email, password FROM admins WHERE username = :username OR email = :email LIMIT 1');
        $stmt->execute([
            ':username' => $identifier,
            ':email' => $identifier,
        ]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($password, $admin['password'])) {
            session_regenerate_id(true);
            $_SESSION['admin_id'] = (int) $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];
            flash('success', 'Welcome back, ' . $admin['username'] . '.');
            header('Location: ../dashboard/index.php');
            exit;
        }

        $errors[] = 'Invalid login credentials.';
    }
}

$flash = consume_flash();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login | Student Record System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body class="auth-page">
    <main class="auth-shell">
        <section class="auth-card glass-card">
            <div class="brand-mark">SR</div>
            <h1>Student Records</h1>
            <p class="text-muted-light">Secure administration dashboard</p>

            <?php if ($flash): ?>
                <div class="alert alert-<?= e($flash['type']) ?> alert-dismissible fade show" role="alert">
                    <?= e($flash['message']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?php foreach ($errors as $error): ?>
                <div class="alert alert-danger" role="alert"><?= e($error) ?></div>
            <?php endforeach; ?>

            <form method="post" class="mt-4" novalidate>
                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                <div class="mb-3">
                    <label for="identifier" class="form-label">Username or Email</label>
                    <input type="text" class="form-control form-control-lg" id="identifier" name="identifier" value="<?= e($_POST['identifier'] ?? '') ?>" autocomplete="username" required>
                </div>
                <div class="mb-4">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control form-control-lg" id="password" name="password" autocomplete="current-password" required>
                </div>
                <button type="submit" class="btn btn-neon w-100 btn-lg">Sign In</button>
            </form>
        </section>
    </main>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
