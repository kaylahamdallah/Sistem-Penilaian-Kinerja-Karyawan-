<?php
require 'config.php';
require_admin();

$q = trim($_GET['q'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 10;

$where = '';
$params = [];
if ($q !== '') {
    $where = 'WHERE u.name LIKE ? OR u.email LIKE ?';
    $params = ["%$q%", "%$q%"];
}

$stmt = $pdo->prepare("SELECT COUNT(*) FROM users u $where");
$stmt->execute($params);
$total = (int)$stmt->fetchColumn();
$pages = max(1, (int)ceil($total / $perPage));
$offset = ($page - 1) * $perPage;

$stmt = $pdo->prepare("SELECT u.*, (SELECT e.id FROM employees e WHERE e.email = u.email LIMIT 1) AS emp_id
                       FROM users u $where ORDER BY u.role, u.name LIMIT $perPage OFFSET $offset");
$stmt->execute($params);
$users = $stmt->fetchAll();

$roleBadge = ['admin' => 'danger', 'manager' => 'primary', 'karyawan' => 'secondary'];

$title = 'Akun Pengguna';
require 'header.php';
?>
<div class="d-flex justify-content-between mb-3">
    <h3>Akun Pengguna</h3>
    <a href="users_form.php" class="btn btn-primary">+ Tambah Akun</a>
</div>

<form class="row g-2 mb-3" method="GET">
    <div class="col-auto"><input type="text" name="q" value="<?= e($q) ?>" class="form-control" placeholder="Cari nama / email"></div>
    <div class="col-auto"><button class="btn btn-secondary">Cari</button></div>
</form>

<div class="card shadow-sm"><div class="table-responsive">
<table class="table table-hover mb-0">
    <thead class="table-light">
        <tr><th>Nama</th><th>Email</th><th>Role</th><th>Data Karyawan</th><th>Dibuat</th><th>Aksi</th></tr>
    </thead>
    <tbody>
    <?php if (!$users): ?>
        <tr><td colspan="6" class="text-center text-muted">Belum ada data.</td></tr>
    <?php endif; ?>
    <?php foreach ($users as $u): ?>
        <?php $isSelf = (int)$u['id'] === (int)$_SESSION['user']['id']; ?>
        <tr>
            <td><?= e($u['name']) ?> <?php if ($isSelf): ?><span class="badge bg-info text-dark">Anda</span><?php endif; ?></td>
            <td><?= e($u['email']) ?></td>
            <td><span class="badge bg-<?= $roleBadge[$u['role']] ?? 'secondary' ?>"><?= e($u['role']) ?></span></td>
            <td>
                <?php if ($u['role'] !== 'karyawan'): ?>
                    <span class="text-muted">-</span>
                <?php elseif ($u['emp_id']): ?>
                    <span class="badge bg-success">Terhubung</span>
                <?php else: ?>
                    <span class="badge bg-warning text-dark" title="Tidak ada karyawan dengan email yang sama">Belum terhubung</span>
                <?php endif; ?>
            </td>
            <td><?= e(date('d/m/Y', strtotime($u['created_at']))) ?></td>
            <td class="d-flex gap-1">
                <a class="btn btn-sm btn-warning" href="users_form.php?id=<?= (int)$u['id'] ?>">Edit</a>
                <?php if (!$isSelf): ?>
                <form method="POST" action="users_delete.php" onsubmit="return confirm('Hapus akun ini?')">
                    <?= csrf_field() ?>
                    <input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
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