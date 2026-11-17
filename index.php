<?php
require 'config.php';
require_not_karyawan();

$totalEmployees  = (int)$pdo->query("SELECT COUNT(*) FROM employees WHERE status='aktif'")->fetchColumn();
$totalAppraisals = (int)$pdo->query("SELECT COUNT(*) FROM appraisals")->fetchColumn();
$avgScore        = round((float)$pdo->query("SELECT AVG(final_score) FROM appraisals")->fetchColumn(), 2);
$gradeSummary    = $pdo->query("SELECT grade, COUNT(*) total FROM appraisals GROUP BY grade ORDER BY grade")->fetchAll();
$latest = $pdo->query("SELECT a.id, a.final_score, a.grade, e.name
                       FROM appraisals a JOIN employees e ON e.id = a.employee_id
                       ORDER BY a.id DESC LIMIT 5")->fetchAll();

$title = 'Dashboard';
require 'header.php';
?>
<div class="row g-3 mb-4">
    <div class="col-md-4"><div class="card shadow-sm"><div class="card-body">
        <div class="text-muted small">Karyawan Aktif</div><div class="fs-2 fw-bold"><?= $totalEmployees ?></div>
    </div></div></div>
    <div class="col-md-4"><div class="card shadow-sm"><div class="card-body">
        <div class="text-muted small">Total Penilaian</div><div class="fs-2 fw-bold"><?= $totalAppraisals ?></div>
    </div></div></div>
    <div class="col-md-4"><div class="card shadow-sm"><div class="card-body">
        <div class="text-muted small">Rata-rata Nilai</div><div class="fs-2 fw-bold"><?= $avgScore ?></div>
    </div></div></div>
</div>

<div class="row g-3">
    <div class="col-md-6"><div class="card shadow-sm"><div class="card-body">
        <h5>Distribusi Predikat</h5>
        <?php if (!$gradeSummary): ?><p class="text-muted">Belum ada data.</p><?php endif; ?>
        <?php foreach ($gradeSummary as $g): ?>
            <div class="d-flex justify-content-between border-bottom py-1">
                <span><?= e($g['grade']) ?> - <?= e(grade_label($g['grade'])) ?></span>
                <strong><?= (int)$g['total'] ?></strong>
            </div>
        <?php endforeach; ?>
    </div></div></div>

    <div class="col-md-6"><div class="card shadow-sm"><div class="card-body">
        <h5>Penilaian Terbaru</h5>
        <?php if (!$latest): ?><p class="text-muted">Belum ada data.</p><?php endif; ?>
        <?php foreach ($latest as $a): ?>
            <div class="d-flex justify-content-between border-bottom py-1">
                <a href="appraisal_show.php?id=<?= (int)$a['id'] ?>"><?= e($a['name']) ?></a>
                <span><?= e($a['final_score']) ?> (<?= e($a['grade']) ?>)</span>
            </div>
        <?php endforeach; ?>
    </div></div></div>
</div>
<?php require 'footer.php'; ?>
