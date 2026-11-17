<?php
require 'config.php';


$employees = $pdo->query("SELECT * FROM employees WHERE status='aktif' ORDER BY name")->fetchAll();
$criterias = $pdo->query('SELECT * FROM criterias ORDER BY id')->fetchAll();

$errors = [];
$old = ['employee_id' => '', 'period' => '', 'appraisal_date' => date('Y-m-d'), 'notes' => '', 'scores' => []];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $old['employee_id']    = (int)($_POST['employee_id'] ?? 0);
    $old['period']         = trim($_POST['period'] ?? '');
    $old['appraisal_date'] = trim($_POST['appraisal_date'] ?? '');
    $old['notes']          = trim($_POST['notes'] ?? '');
    $old['scores']         = $_POST['scores'] ?? [];

    // Validasi
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM employees WHERE id = ? AND status = 'aktif'");
    $stmt->execute([$old['employee_id']]);
    if (!$stmt->fetchColumn())        $errors[] = 'Pilih karyawan yang valid.';
    if ($old['period'] === '')         $errors[] = 'Periode wajib diisi.';
    if ($old['appraisal_date'] === '') $errors[] = 'Tanggal penilaian wajib diisi.';

    foreach ($criterias as $c) {
        $s = $old['scores'][$c['id']] ?? '';
        if (!ctype_digit((string)$s) || (int)$s > (int)$c['weight']) {
            $errors[] = 'Nilai "' . $c['name'] . '" harus angka 0 - ' . (int)$c['weight'] . '.';
        }
    }

    if (!$errors) {
        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare('INSERT INTO appraisals (employee_id, appraiser_id, period, appraisal_date, notes) VALUES (?,?,?,?,?)');
            $stmt->execute([$old['employee_id'], $_SESSION['user']['id'], $old['period'], $old['appraisal_date'], $old['notes']]);
            $appraisalId = (int)$pdo->lastInsertId();

            $ins = $pdo->prepare('INSERT INTO appraisal_details (appraisal_id, criteria_id, score) VALUES (?,?,?)');
            $totalWeight = 0;
            $weighted = 0;
            foreach ($criterias as $c) {
                $score = (int)$old['scores'][$c['id']];
                $ins->execute([$appraisalId, $c['id'], $score]);
                $weighted    += $score;
                $totalWeight += $c['weight'];
            }

            // Nilai akhir = Σ nilai / Σ bobot × 100 (nilai maksimal tiap kriteria = bobotnya)
            $final = $totalWeight > 0 ? round($weighted / $totalWeight * 100, 2) : 0;
            $upd = $pdo->prepare('UPDATE appraisals SET final_score = ?, grade = ? WHERE id = ?');
            $upd->execute([$final, grade_for($final), $appraisalId]);

            $pdo->commit();
            flash('Penilaian berhasil disimpan.');
            redirect('appraisal_show.php?id=' . $appraisalId);
        } catch (Exception $ex) {
            $pdo->rollBack();
            $errors[] = 'Gagal menyimpan: ' . $ex->getMessage();
        }
    }
}

$title = 'Form Penilaian';
require 'header.php';
?>
<h3>Form Penilaian Kinerja</h3>

<?php if ($errors): ?>
    <div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $er): ?><li><?= e($er) ?></li><?php endforeach; ?></ul></div>
<?php endif; ?>

<?php if (!$criterias): ?>
    <div class="alert alert-warning">Belum ada kriteria penilaian. Hubungi admin.</div>
<?php else: ?>
<form method="POST" class="card shadow-sm card-body" style="max-width:760px">
    <?= csrf_field() ?>
    <div class="mb-3">
        <label class="form-label">Karyawan</label>
        <select name="employee_id" class="form-select" required>
            <option value="">-- Pilih karyawan --</option>
            <?php foreach ($employees as $emp): ?>
                <option value="<?= (int)$emp['id'] ?>" <?= $old['employee_id'] == $emp['id'] ? 'selected' : '' ?>>
                    <?= e($emp['nip']) ?> - <?= e($emp['name']) ?> (<?= e($emp['position']) ?>)
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-6"><label class="form-label">Periode</label>
            <input type="text" name="period" class="form-control" placeholder="Semester 1 2026" value="<?= e($old['period']) ?>" required></div>
        <div class="col-md-6"><label class="form-label">Tanggal Penilaian</label>
            <input type="date" name="appraisal_date" class="form-control" value="<?= e($old['appraisal_date']) ?>" required></div>
    </div>

    <h5>Nilai per Kriteria (maksimal sesuai bobot)</h5>
    <?php foreach ($criterias as $c): ?>
        <div class="d-flex justify-content-between align-items-center border-bottom py-2">
            <div>
                <div class="fw-semibold"><?= e($c['name']) ?> <small class="text-muted">(bobot <?= (int)$c['weight'] ?>)</small></div>
                <small class="text-muted"><?= e($c['description']) ?></small>
            </div>
                <input type="number" name="scores[<?= (int)$c['id'] ?>]" min="0" max="<?= (int)$c['weight'] ?>" class="form-control" style="width:100px"
                value="<?= e($old['scores'][$c['id']] ?? '') ?>" required>
        </div>
    <?php endforeach; ?>

    <div class="my-3">
        <label class="form-label">Catatan / Feedback</label>
        <textarea name="notes" rows="3" class="form-control"><?= e($old['notes']) ?></textarea>
    </div>

    <div>
        <button class="btn btn-primary">Simpan Penilaian</button>
        <a href="appraisals.php" class="btn btn-outline-secondary">Batal</a>
    </div>
</form>
<?php endif; ?>
<?php require 'footer.php'; ?>
