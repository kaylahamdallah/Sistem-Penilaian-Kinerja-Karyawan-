<?php
require 'config.php';
require_manager_or_admin();

$q = trim($_GET['q'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 10;

$where = '';
$params = [];
if ($q !== '') {
    $where = 'WHERE name LIKE ? OR nip LIKE ?';
    $params = ["%$q%", "%$q%"];
}

$stmt = $pdo->prepare("SELECT COUNT(*) FROM employees $where");
$stmt->execute($params);
$total = (int)$stmt->fetchColumn();
$pages = max(1, (int)ceil($total / $perPage));
$offset = ($page - 1) * $perPage;

$stmt = $pdo->prepare("SELECT * FROM employees $where ORDER BY name LIMIT $perPage OFFSET $offset");
$stmt->execute($params);
$employees = $stmt->fetchAll();

$title = 'Data Karyawan';
require 'header.php';
?>
<div class="d-flex justify-content-between mb-3">
    <h3>Data Karyawan</h3>
    <a href="employee_form.php" class="btn btn-primary">+ Tambah Karyawan</a>
</div>

<form class="row g-2 mb-3" method="GET">
    <div class="col-auto"><input type="text" name="q" value="<?= e($q) ?>" class="form-control" placeholder="Cari nama / NIP"></div>
    <div class="col-auto"><button class="btn btn-secondary">Cari</button></div>
</form>

<div class="card shadow-sm"><div class="table-responsive">
<table class="table table-hover mb-0">
    <thead class="table-light">
        <tr><th>NIP</th><th>Nama</th><th>Jabatan</th><th>Departemen</th><th>Status</th><th>Aksi</th></tr>
    </thead>
    <tbody>
    <?php if (!$employees): ?>
        <tr><td colspan="6" class="text-center text-muted">Belum ada data.</td></tr>
    <?php endif; ?>
    <?php foreach ($employees as $emp): ?>
        <tr>
            <td><?= e($emp['nip']) ?></td>
            <td><?= e($emp['name']) ?></td>
            <td><?= e($emp['position']) ?></td>
            <td><?= e($emp['department']) ?></td>
            <td><span class="badge bg-<?= $emp['status'] === 'aktif' ? 'success' : 'secondary' ?>"><?= e($emp['status']) ?></span></td>
            <td class="d-flex gap-1">
                <a class="btn btn-sm btn-warning" href="employee_form.php?id=<?= (int)$emp['id'] ?>">Edit</a>
                <?php if (is_admin()): ?>
                <form method="POST" action="employee_delete.php" onsubmit="return confirm('Hapus karyawan ini?')">
                    <?= csrf_field() ?>
                    <input type="hidden" name="id" value="<?= (int)$emp['id'] ?>">
                    <button class="btn btn-sm btn-danger">Hapus</button>
                </form>
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div></div>
<div class="mt-3"><?= pagination($page, $pages, ['q' => $q]) ?></div>
<?php require 'footer.php'; ?>
