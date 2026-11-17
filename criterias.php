<?php
require 'config.php';
require_manager_or_admin();

$criterias = $pdo->query('SELECT * FROM criterias ORDER BY id')->fetchAll();
$totalWeight = array_sum(array_column($criterias, 'weight'));

$title = 'Kriteria Penilaian';
require 'header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3>Kriteria Penilaian</h3>
    <a href="criteria_form.php" class="btn btn-primary">+ Tambah Kriteria</a>
</div>

<p class="<?= $totalWeight == 100 ? 'text-success' : 'text-danger' ?>">
    Total bobot: <strong><?= $totalWeight ?></strong> (disarankan 100)
</p>

<div class="card shadow-sm"><div class="table-responsive">
<table class="table table-hover mb-0">
    <thead class="table-light"><tr><th>Kriteria</th><th>Deskripsi</th><th>Bobot</th><th>Aksi</th></tr></thead>
    <tbody>
    <?php if (!$criterias): ?><tr><td colspan="4" class="text-center text-muted">Belum ada kriteria.</td></tr><?php endif; ?>
    <?php foreach ($criterias as $c): ?>
        <tr>
            <td><?= e($c['name']) ?></td>
            <td><?= e($c['description']) ?></td>
            <td><?= (int)$c['weight'] ?></td>
            <td class="d-flex gap-1">
                <a class="btn btn-sm btn-warning" href="criteria_form.php?id=<?= (int)$c['id'] ?>">Edit</a>
                <?php if (is_admin()): ?>
                <form method="POST" action="criteria_delete.php" onsubmit="return confirm('Hapus kriteria ini?')">
                    <?= csrf_field() ?>
                    <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
                    <button class="btn btn-sm btn-danger">Hapus</button>
                </form>
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div></div>
<?php require 'footer.php'; ?>
