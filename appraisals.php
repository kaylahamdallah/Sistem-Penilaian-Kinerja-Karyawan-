<?php
require 'config.php';
require_login();

$period = trim($_GET['period'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 10;

$conds = [];
$params = [];
if ($period !== '') {
    $conds[] = 'a.period LIKE ?';
    $params[] = "%$period%";
}

if (is_karyawan()) {
    $conds[] = 'e.email = ?';
    $params[] = $_SESSION['user']['email'];
}
$where = $conds ? 'WHERE ' . implode(' AND ', $conds) : '';

$stmt = $pdo->prepare("SELECT COUNT(*) FROM appraisals a JOIN employees e ON e.id = a.employee_id $where");
$stmt->execute($params);
$total = (int)$stmt->fetchColumn();
$pages = max(1, (int)ceil($total / $perPage));
$offset = ($page - 1) * $perPage;

$stmt = $pdo->prepare("SELECT a.*, e.name AS employee_name, u.name AS appraiser_name
                       FROM appraisals a
                       JOIN employees e ON e.id = a.employee_id
                       JOIN users u ON u.id = a.appraiser_id
                       $where ORDER BY a.id DESC LIMIT $perPage OFFSET $offset");
$stmt->execute($params);
$appraisals = $stmt->fetchAll();

$title = 'Penilaian Kinerja';
require 'header.php';
?>
<div class="d-flex justify-content-between mb-3">
    <h3>Penilaian Kinerja</h3>
    <?php if (!is_karyawan()): ?>
        <a href="appraisal_form.php" class="btn btn-primary">+ Penilaian Baru</a>
    <?php endif; ?>
</div>

<form class="row g-2 mb-3" method="GET">
    <div class="col-auto"><input type="text" name="period" value="<?= e($period) ?>" class="form-control" placeholder="Cari periode"></div>
    <div class="col-auto"><button class="btn btn-secondary">Cari</button></div>
</form>

<div class="card shadow-sm"><div class="table-responsive">
<table class="table table-hover mb-0">
    <thead class="table-light">
        <tr><th>Tanggal</th><th>Karyawan</th><th>Periode</th><th>Penilai</th><th>Nilai</th><th>Predikat</th><th>Aksi</th></tr>
    </thead>
    <tbody>
    <?php if (!$appraisals): ?><tr><td colspan="7" class="text-center text-muted">Belum ada penilaian.</td></tr><?php endif; ?>
    <?php foreach ($appraisals as $a): ?>
        <tr>
            <td><?= e(date('d/m/Y', strtotime($a['appraisal_date']))) ?></td>
            <td><?= e($a['employee_name']) ?></td>
            <td><?= e($a['period']) ?></td>
            <td><?= e($a['appraiser_name']) ?></td>
            <td class="fw-bold"><?= e($a['final_score']) ?></td>
            <td><?= e($a['grade']) ?> - <?= e(grade_label($a['grade'])) ?></td>
            <td class="d-flex gap-1">
                <a class="btn btn-sm btn-info" href="appraisal_show.php?id=<?= (int)$a['id'] ?>">Detail</a>
                <?php if (is_admin()): ?>
                <form method="POST" action="appraisal_delete.php" onsubmit="return confirm('Hapus penilaian ini?')">
                    <?= csrf_field() ?>
                    <input type="hidden" name="id" value="<?= (int)$a['id'] ?>">
                    <button class="btn btn-sm btn-danger">Hapus</button>
                </form>
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div></div>
<div class="mt-3"><?= pagination($page, $pages, ['period' => $period]) ?></div>
<?php require 'footer.php'; ?>
