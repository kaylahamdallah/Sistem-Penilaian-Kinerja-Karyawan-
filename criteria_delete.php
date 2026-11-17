<?php
require 'config.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $stmt = $pdo->prepare('DELETE FROM criterias WHERE id = ?');
    $stmt->execute([(int)($_POST['id'] ?? 0)]);
    flash('Kriteria berhasil dihapus.');
}
redirect('criterias.php');
