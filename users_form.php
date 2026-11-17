<?php
require 'config.php';
require_admin();

const ROLES = ['admin' => 'Admin', 'manager' => 'Manager', 'karyawan' => 'Karyawan'];

$id = (int)($_GET['id'] ?? 0);
$data = ['name' => '', 'email' => '', 'role' => 'karyawan'];

if ($id) {
    $stmt = $pdo->prepare('SELECT id, name, email, role FROM users WHERE id = ?');
    $stmt->execute([$id]);
    $data = $stmt->fetch();
    if (!$data) { flash('Akun tidak ditemukan.', 'danger'); redirect('users.php'); }
}
$isSelf = $id && $id === (int)$_SESSION['user']['id'];

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $data['name']  = trim($_POST['name'] ?? '');
    $data['email'] = trim($_POST['email'] ?? '');
    $data['role']  = $isSelf ? $data['role'] : ($_POST['role'] ?? '');
    $password  = $_POST['password'] ?? '';
    $password2 = $_POST['password2'] ?? '';

    if ($data['name'] === '')  $errors[] = 'Nama wajib diisi.';
    if ($data['email'] === '') $errors[] = 'Email wajib diisi.';
    elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'Format email tidak valid.';
    if (!isset(ROLES[$data['role']])) $errors[] = 'Role tidak valid.';

    if (!$id || $password !== '') {
        if (strlen($password) < 8) $errors[] = 'Password minimal 8 karakter.';
        if ($password !== $password2) $errors[] = 'Konfirmasi password tidak sama.';
    }

    if (!$errors) {
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM users WHERE email = ? AND id <> ?');
        $stmt->execute([$data['email'], $id]);
        if ($stmt->fetchColumn() > 0) $errors[] = 'Email sudah digunakan akun lain.';
    }

    if (!$errors) {
        if ($id) {
            if ($password !== '') {
                $stmt = $pdo->prepare('UPDATE users SET name=?, email=?, role=?, password=? WHERE id=?');
                $stmt->execute([$data['name'], $data['email'], $data['role'], password_hash($password, PASSWORD_DEFAULT), $id]);
            } else {
                $stmt = $pdo->prepare('UPDATE users SET name=?, email=?, role=? WHERE id=?');
                $stmt->execute([$data['name'], $data['email'], $data['role'], $id]);
            }
            if ($isSelf) {
                $_SESSION['user']['name']  = $data['name'];
                $_SESSION['user']['email'] = $data['email'];
            }
            flash('Akun berhasil diperbarui.');
        } else {
            $stmt = $pdo->prepare('INSERT INTO users (name, email, password, role) VALUES (?,?,?,?)');
            $stmt->execute([$data['name'], $data['email'], password_hash($password, PASSWORD_DEFAULT), $data['role']]);
            flash('Akun berhasil ditambahkan.');
        }

        if ($data['role'] === 'karyawan') {
            $stmt = $pdo->prepare('SELECT COUNT(*) FROM employees WHERE email = ?');
            $stmt->execute([$data['email']]);
            if (!$stmt->fetchColumn()) {
                flash('Akun tersimpan, tetapi belum ada data karyawan dengan email ' . $data['email']
                    . '. Karyawan ini belum bisa melihat nilainya sampai emailnya disamakan di menu Data Karyawan.', 'warning');
            }
        }
        redirect('users.php');
    }
}

$title = $id ? 'Edit Akun' : 'Tambah Akun';
require 'header.php';
?>
<h3><?= e($title) ?></h3>
<?php if ($errors): ?>
    <div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $er): ?><li><?= e($er) ?></li><?php endforeach; ?></ul></div>
<?php endif; ?>

<form method="POST" class="card shadow-sm card-body" autocomplete="off">
    <?= csrf_field() ?>
    <div class="row g-3">
        <div class="col-md-6"><label class="form-label">Nama</label>
            <input type="text" name="name" class="form-control" value="<?= e($data['name']) ?>" required></div>
        <div class="col-md-6"><label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" value="<?= e($data['email']) ?>" required>
            <div class="form-text">Untuk role Karyawan, email harus sama dengan email di Data Karyawan.</div></div>
        <div class="col-md-4"><label class="form-label">Role</label>
            <select name="role" class="form-select" <?= $isSelf ? 'disabled' : '' ?>>
                <?php foreach (ROLES as $val => $label): ?>
                    <option value="<?= $val ?>" <?= $data['role'] === $val ? 'selected' : '' ?>><?= $label ?></option>
                <?php endforeach; ?>
            </select>
            <?php if ($isSelf): ?><div class="form-text">Role akun Anda sendiri tidak dapat diubah.</div><?php endif; ?></div>
        <div class="col-md-4"><label class="form-label">Password<?= $id ? ' baru' : '' ?></label>
            <input type="password" name="password" class="form-control" minlength="8" autocomplete="new-password" <?= $id ? '' : 'required' ?>>
            <?php if ($id): ?><div class="form-text">Kosongkan jika tidak ingin mengubah.</div><?php endif; ?></div>
        <div class="col-md-4"><label class="form-label">Konfirmasi Password</label>
            <input type="password" name="password2" class="form-control" minlength="8" autocomplete="new-password" <?= $id ? '' : 'required' ?>></div>
    </div>
    <div class="mt-4">
        <button class="btn btn-primary">Simpan</button>
        <a href="users.php" class="btn btn-outline-secondary">Batal</a>
    </div>
</form>
<?php require 'footer.php'; ?>