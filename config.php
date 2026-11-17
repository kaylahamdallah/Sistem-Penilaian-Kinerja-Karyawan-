<?php
session_start();

// ====== Konfigurasi Database (sesuaikan dengan XAMPP/Laragon Anda) ======
const DB_HOST = 'localhost';
const DB_NAME = 'penilaian_kinerja';
const DB_USER = 'root';
const DB_PASS = '';

try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
} catch (PDOException $e) {
    die('Koneksi database gagal: ' . $e->getMessage());
}

// ====== Helper ======
function e($s): string { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

function redirect(string $url): void { header("Location: $url"); exit; }

function is_admin(): bool { return ($_SESSION['user']['role'] ?? '') === 'admin'; }

function require_login(): void {
    if (!isset($_SESSION['user'])) redirect('login.php');
}

function require_admin(): void {
    require_login();
    if (!is_admin()) {
        http_response_code(403);
        die('Akses ditolak. Halaman ini khusus admin.');
    }
}

function is_manager(): bool  { return ($_SESSION['user']['role'] ?? '') === 'manager'; }
function is_karyawan(): bool { return ($_SESSION['user']['role'] ?? '') === 'karyawan'; }

function require_manager_or_admin(): void {
    require_login();
    if (!in_array($_SESSION['user']['role'] ?? '', ['admin', 'manager'], true)) {
        http_response_code(403);
        die('Akses ditolak. Halaman ini khusus admin / manager.');
    }
}

function require_not_karyawan(): void {
    require_login();
    if (is_karyawan()) redirect('appraisals.php');
}

function csrf_token(): string {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf'];
}
function csrf_field(): string { return '<input type="hidden" name="csrf" value="' . csrf_token() . '">'; }
function csrf_check(): void {
    if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) die('Token CSRF tidak valid.');
}

function flash(?string $msg = null, string $type = 'success') {
    if ($msg !== null) { $_SESSION['flash'] = ['msg' => $msg, 'type' => $type]; return null; }
    $f = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $f;
}

function grade_for(float $score): string {
    if ($score >= 90) return 'A';
    if ($score >= 80) return 'B';
    if ($score >= 70) return 'C';
    if ($score >= 60) return 'D';
    return 'E';
}

function grade_label(?string $g): string {
    return [
        'A' => 'Sangat Baik', 'B' => 'Baik', 'C' => 'Cukup',
        'D' => 'Kurang', 'E' => 'Sangat Kurang',
    ][$g] ?? '-';
}

function pagination(int $page, int $pages, array $params = []): string {
    if ($pages <= 1) return '';
    $html = '<nav><ul class="pagination">';
    for ($i = 1; $i <= $pages; $i++) {
        $q = http_build_query($params + ['page' => $i]);
        $html .= '<li class="page-item ' . ($i === $page ? 'active' : '') . '">'
               . '<a class="page-link" href="?' . $q . '">' . $i . '</a></li>';
    }
    return $html . '</ul></nav>';
}
