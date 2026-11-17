<?php
require 'config.php';
require_login();

$id = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare("SELECT a.*, e.name AS employee_name, e.email AS employee_email, e.nip, e.position, e.department, u.name AS appraiser_name
                       FROM appraisals a
                       JOIN employees e ON e.id = a.employee_id
                       JOIN users u ON u.id = a.appraiser_id
                       WHERE a.id = ?");
$stmt->execute([$id]);
$a = $stmt->fetch();

// Tidak ditemukan, atau karyawan mencoba membuka penilaian milik orang lain
if (!$a || (is_karyawan() && strcasecmp((string)$a['employee_email'], $_SESSION['user']['email']) !== 0)) {
    flash('Penilaian tidak ditemukan.', 'danger');
    redirect('appraisals.php');
}

$stmt = $pdo->prepare('SELECT d.score, c.name, c.weight
                       FROM appraisal_details d JOIN criterias c ON c.id = d.criteria_id
                       WHERE d.appraisal_id = ? ORDER BY c.id');
$stmt->execute([$id]);
$details = $stmt->fetchAll();

$title = 'Detail Penilaian';
require 'header.php';
?>
<h3>Detail Penilaian</h3>

<div class="card shadow-sm mb-3"><div class="card-body">
    <div class="row">
        <div class="col-md-6">
            <p class="mb-1"><span class="text-muted">Karyawan:</span> <strong><?= e($a['employee_name']) ?></strong> (<?= e($a['nip']) ?>)</p>
            <p class="mb-1"><span class="text-muted">Jabatan:</span> <?= e($a['position']) ?></p>
            <p class="mb-0"><span class="text-muted">Departemen:</span> <?= e($a['department']) ?></p>
        </div>
        <div class="col-md-6">
            <p class="mb-1"><span class="text-muted">Periode:</span> <?= e($a['period']) ?></p>
            <p class="mb-1"><span class="text-muted">Tanggal:</span> <?= e(date('d M Y', strtotime($a['appraisal_date']))) ?></p>
            <p class="mb-0"><span class="text-muted">Penilai:</span> <?= e($a['appraiser_name']) ?></p>
        </div>
    </div>
</div></div>

<div class="card shadow-sm mb-3"><div class="card-body">
    <table class="table">
        <thead class="table-light"><tr><th>Kriteria</th><th>Bobot</th><th>Nilai</th></tr></thead>
        <tbody>
        <?php foreach ($details as $d): ?>
            <tr><td><?= e($d['name']) ?></td><td><?= (int)$d['weight'] ?></td><td><?= (int)$d['score'] ?></td></tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <div class="text-end">
        <div class="text-muted small">Nilai Akhir</div>
        <div class="display-6 fw-bold"><?= e($a['final_score']) ?></div>
        <div class="fw-semibold">Predikat <?= e($a['grade']) ?> - <?= e(grade_label($a['grade'])) ?></div>
    </div>
</div></div>

<?php if ($a['notes']): ?>
<div class="card shadow-sm mb-3"><div class="card-body">
    <h6>Catatan</h6><p class="mb-0"><?= nl2br(e($a['notes'])) ?></p>
</div></div>
<?php endif; ?>

<a href="appraisals.php" class="btn btn-outline-secondary">Kembali</a>
<button onclick="window.print()" class="btn btn-primary">Cetak</button>
<?php require 'footer.php'; ?>
