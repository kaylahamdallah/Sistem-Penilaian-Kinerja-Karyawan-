
<?php
require 'config.php';
require_manager_or_admin();

$id = (int)($_GET['id'] ?? 0);
$data = ['name' => '', 'description' => '', 'weight' => ''];

if ($id) {
    $stmt = $pdo->prepare('SELECT * FROM criterias WHERE id = ?');
    $stmt->execute([$id]);
    $data = $stmt->fetch();
    if (!$data) { flash('Data tidak ditemukan.', 'danger'); redirect('criterias.php'); }
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $data['name']        = trim($_POST['name'] ?? '');
    $data['description'] = trim($_POST['description'] ?? '');
    $data['weight']      = trim($_POST['weight'] ?? '');

    if ($data['name'] === '') $errors[] = 'Nama kriteria wajib diisi.';
    if (!ctype_digit($data['weight']) || (int)$data['weight'] < 1 || (int)$data['weight'] > 100) {
        $errors[] = 'Bobot harus berupa angka 1 - 100.';
    }

    if (!$errors) {
        if ($id) {
            $stmt = $pdo->prepare('UPDATE criterias SET name=?, description=?, weight=? WHERE id=?');
            $stmt->execute([$data['name'], $data['description'], (int)$data['weight'], $id]);
            flash('Kriteria berhasil diperbarui.');
        } else {
            $stmt = $pdo->prepare('INSERT INTO criterias (name, description, weight) VALUES (?,?,?)');
            $stmt->execute([$data['name'], $data['description'], (int)$data['weight']]);
            flash('Kriteria berhasil ditambahkan.');
        }
        redirect('criterias.php');
    }
}

$title = $id ? 'Edit Kriteria' : 'Tambah Kriteria';
require 'header.php';
?>
<h3><?= e($title) ?></h3>
<?php if ($errors): ?>
    <div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $er): ?><li><?= e($er) ?></li><?php endforeach; ?></ul></div>
<?php endif; ?>

<form method="POST" class="card shadow-sm card-body" style="max-width:640px">
    <?= csrf_field() ?>
    <div class="mb-3"><label class="form-label">Nama Kriteria</label>
        <input type="text" name="name" class="form-control" value="<?= e($data['name']) ?>" required></div>
    <div class="mb-3"><label class="form-label">Deskripsi</label>
        <textarea name="description" rows="3" class="form-control"><?= e($data['description']) ?></textarea></div>
    <div class="mb-3"><label class="form-label">Bobot (1-100)</label>
        <input type="number" name="weight" min="1" max="100" class="form-control" value="<?= e($data['weight']) ?>" required></div>
    <div>
        <button class="btn btn-primary">Simpan</button>
        <a href="criterias.php" class="btn btn-outline-secondary">Batal</a>
    </div>
</form>
<?php require 'footer.php'; ?>
