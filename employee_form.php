<?php
require 'config.php';
require_manager_or_admin();

$id = (int)($_GET['id'] ?? 0);
$data = ['nip' => '', 'name' => '', 'email' => '', 'position' => '', 'department' => '',
         'join_date' => date('Y-m-d'), 'status' => 'aktif'];

if ($id) {
    $stmt = $pdo->prepare('SELECT * FROM employees WHERE id = ?');
    $stmt->execute([$id]);
    $data = $stmt->fetch();
    if (!$data) { flash('Data tidak ditemukan.', 'danger'); redirect('employees.php'); }
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    foreach (['nip', 'name', 'email', 'position', 'department', 'join_date', 'status'] as $f) {
        $data[$f] = trim($_POST[$f] ?? '');
    }

    if ($data['nip'] === '')        $errors[] = 'NIP wajib diisi.';
    if ($data['name'] === '')       $errors[] = 'Nama wajib diisi.';
    if ($data['position'] === '')   $errors[] = 'Jabatan wajib diisi.';
    if ($data['department'] === '') $errors[] = 'Departemen wajib diisi.';
    if ($data['join_date'] === '')  $errors[] = 'Tanggal masuk wajib diisi.';
    if ($data['email'] !== '' && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'Format email tidak valid.';
    if (!in_array($data['status'], ['aktif', 'nonaktif'], true)) $errors[] = 'Status tidak valid.';

    if (!$errors) {
        // Cek NIP unik
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM employees WHERE nip = ? AND id <> ?');
        $stmt->execute([$data['nip'], $id]);
        if ($stmt->fetchColumn() > 0) $errors[] = 'NIP sudah digunakan.';
    }

    if (!$errors) {
        $vals = [$data['nip'], $data['name'], $data['email'] ?: null, $data['position'],
                 $data['department'], $data['join_date'], $data['status']];
        if ($id) {
            $stmt = $pdo->prepare('UPDATE employees SET nip=?, name=?, email=?, position=?, department=?, join_date=?, status=? WHERE id=?');
            $stmt->execute([...$vals, $id]);
            flash('Data karyawan berhasil diperbarui.');
        } else {
            $stmt = $pdo->prepare('INSERT INTO employees (nip, name, email, position, department, join_date, status) VALUES (?,?,?,?,?,?,?)');
            $stmt->execute($vals);
            flash('Karyawan berhasil ditambahkan.');
        }
        redirect('employees.php');
    }
}

$title = $id ? 'Edit Karyawan' : 'Tambah Karyawan';
require 'header.php';
?>
<h3><?= e($title) ?></h3>
<?php if ($errors): ?>
    <div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $er): ?><li><?= e($er) ?></li><?php endforeach; ?></ul></div>
<?php endif; ?>

<form method="POST" class="card shadow-sm card-body">
    <?= csrf_field() ?>
    <div class="row g-3">
        <div class="col-md-6"><label class="form-label">NIP</label>
            <input type="text" name="nip" class="form-control" value="<?= e($data['nip']) ?>" required></div>
        <div class="col-md-6"><label class="form-label">Nama Lengkap</label>
            <input type="text" name="name" class="form-control" value="<?= e($data['name']) ?>" required></div>
        <div class="col-md-6"><label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" value="<?= e($data['email']) ?>"></div>
        <div class="col-md-6"><label class="form-label">Jabatan</label>
            <input type="text" name="position" class="form-control" value="<?= e($data['position']) ?>" required></div>
        <div class="col-md-6"><label class="form-label">Departemen</label>
            <input type="text" name="department" class="form-control" value="<?= e($data['department']) ?>" required></div>
        <div class="col-md-3"><label class="form-label">Tanggal Masuk</label>
            <input type="date" name="join_date" class="form-control" value="<?= e($data['join_date']) ?>" required></div>
        <div class="col-md-3"><label class="form-label">Status</label>
            <select name="status" class="form-select">
                <?php foreach (['aktif', 'nonaktif'] as $s): ?>
                    <option value="<?= $s ?>" <?= $data['status'] === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                <?php endforeach; ?>
            </select></div>
    </div>
    <div class="mt-4">
        <button class="btn btn-primary">Simpan</button>
        <a href="employees.php" class="btn btn-outline-secondary">Batal</a>
    </div>
</form>
<?php require 'footer.php'; ?>
