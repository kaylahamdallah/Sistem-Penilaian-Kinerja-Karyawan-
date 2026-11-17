<?php
require 'config.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $id = (int)($_POST['id'] ?? 0);

    $stmt = $pdo->prepare('SELECT id, role FROM users WHERE id = ?');
    $stmt->execute([$id]);
    $user = $stmt->fetch();

    if (!$user) {
        flash('Akun tidak ditemukan.', 'danger');
    } elseif ($id === (int)$_SESSION['user']['id']) {
        flash('Anda tidak dapat menghapus akun Anda sendiri.', 'danger');
    } else {
        // appraisals.appraiser_id memakai ON DELETE CASCADE
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM appraisals WHERE appraiser_id = ?');
        $stmt->execute([$id]);
        if ($stmt->fetchColumn() > 0) {
            flash('Akun ini sudah pernah membuat penilaian dan tidak bisa dihapus agar data penilaian tidak ikut hilang.', 'danger');
        } else {
            $pdo->prepare('DELETE FROM users WHERE id = ?')->execute([$id]);
            flash('Akun berhasil dihapus.');
        }
    }
}
redirect('users.php');