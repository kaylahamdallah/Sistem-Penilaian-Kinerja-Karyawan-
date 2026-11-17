<?php
require 'config.php';

if (isset($_SESSION['user'])) redirect('index.php');

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        session_regenerate_id(true);
        $_SESSION['user'] = [
            'id' => $user['id'], 'name' => $user['name'],
            'email' => $user['email'], 'role' => $user['role'],
            'photo' => $user['photo'] ?? null,
        ];
        redirect('index.php');
    }
    $error = 'Email atau password salah.';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - Penilaian Kinerja</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center" style="min-height:100vh">
<div class="container" style="max-width:420px">
    <div class="card shadow-sm">
        <div class="card-body p-4">
            <div class="text-center mb-2">
                <span class="d-inline-flex align-items-center justify-content-center bg-primary text-white rounded-4"
                style="width:72px;height:72px;font-size:2.4rem"><i class="bi bi-graph-up-arrow"></i></span>
            </div>
            <h4 class="mb-1 text-center">Sistem Penilaian Kinerja</h4>
            <p class="text-center text-muted">Silakan login</p>
            <?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
            <form method="POST">
                <?= csrf_field() ?>
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" value="<?= e($_POST['email'] ?? '') ?>" required autofocus>
                </div>
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <button class="btn btn-primary w-100">Login</button>
            </form>
            <small class="text-muted d-block mt-3">Demo: admin@example.com / password</small>
            <small class="text-muted d-block mt-3">Demo: manager@example.com / password</small>
            <small class="text-muted d-block mt-3">Demo: budi@example.com / password</small>
            <small class="text-muted d-block mt-3">Demo: siti@example.com / password</small>
        </div>
    </div>
</div>
</body>
</html>
  