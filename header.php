<?php
// Setiap halaman memanggil require_login() / require_admin() SEBELUM include file ini.
$title = $title ?? 'Sistem Penilaian Kinerja';
$current = basename($_SERVER['PHP_SELF']);

// Menandai menu yang sedang dibuka
function nav_active(array $files): string {
    global $current;
    return in_array($current, $files, true) ? 'active' : '';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title) ?> - Penilaian Kinerja</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        :root {
            --top-h: 64px; --side-w: 260px;
            --c-top: #2b36cb;   
            --c-side: #5193ea;   
        }

        .topbar {
            position: fixed; top: 0; left: 0; right: 0; height: var(--top-h);
            background: var(--c-top); color: #fff; z-index: 1030;
            display: flex; align-items: center; gap: 1rem; padding: 0 1rem;
        }
        .topbar .toggle {
            background: #fff; border: 0; width: 56px; height: 40px;
            font-size: 1.5rem; color: #222; line-height: 1;
        }
        .topbar .brand {
            color: #fff; text-decoration: none; font-weight: 700;
            font-size: 1.25rem; letter-spacing: .5px; text-transform: uppercase;
        }
        .topbar .user { margin-left: auto; display: flex; align-items: center; gap: .75rem; }

        .sidebar {
            position: fixed; top: var(--top-h); bottom: 0; left: 0; width: var(--side-w);
            background: var(--c-side); overflow-y: auto; padding: 1rem 0;
            transition: transform .2s; z-index: 1020;
        }
        .sidebar a {
            display: flex; align-items: center; gap: 1rem;
            color: #fff; text-decoration: none; padding: .85rem 1.5rem; font-size: 1.05rem;
        }
        .sidebar a i { font-size: 1.4rem; width: 1.6rem; text-align: center; }
        .sidebar a:hover, .sidebar a.active { background: rgba(255, 255, 255, .2); }

        .content {
            margin-left: var(--side-w);
            padding: calc(var(--top-h) + 1.5rem) 1.5rem 0;
            transition: margin .2s;
        }
        body.sidebar-hidden .sidebar { transform: translateX(-100%); }
        body.sidebar-hidden .content { margin-left: 0; }

        .logo-badge {
        display: inline-flex; align-items: center; justify-content: center;
        width: 40px; height: 40px; border-radius: 10px;
        background: var(--c-side); color: #fff; font-size: 1.4rem;
        }
        .topbar .brand { display: flex; align-items: center; gap: .65rem; }

        .sidebar-logo {
            text-align: center; color: #fff;
            padding: .5rem 1rem 1.25rem; margin-bottom: .5rem;
            border-bottom: 1px solid rgba(255, 255, 255, .25);
        }
        .sidebar-logo .logo-badge {
            width: 64px; height: 64px; font-size: 2.2rem; border-radius: 16px;
            background: #fff; color: var(--c-side); margin-bottom: .6rem;
        }
        .sidebar-logo .name { font-weight: 700; letter-spacing: .5px; }
        .sidebar-logo .role { font-size: .85rem; opacity: .85; text-transform: capitalize; }

        @media (max-width: 991.98px) {
            .sidebar { transform: translateX(-100%); }
            .content { margin-left: 0; }
            body.sidebar-open .sidebar { transform: none; }
            .topbar .user-name { display: none; }
        }
        @media print {
            .topbar, .sidebar { display: none !important; }
            .content { margin: 0; padding: 0; }
        }
    </style>
</head>
<body class="bg-light">

<header class="topbar">
    <button type="button" class="toggle" id="sidebarToggle" aria-label="Menu"><i class="bi bi-list"></i></button>
    <a class="brand" href="<?= is_karyawan() ? 'appraisals.php' : 'index.php' ?>">
        <span class="logo-badge"><i class="bi bi-graph-up-arrow"></i></span>
        Penilaian Kinerja
    </a>
    <div class="user">
        <a href="profile.php" class="user-name text-white text-decoration-none"><i class="bi bi-person-circle"></i> <?= e($_SESSION['user']['name']) ?> (<?= e($_SESSION['user']['role']) ?>)</a>
        <a class="btn btn-outline-light btn-sm" href="logout.php">Logout</a>
    </div>
</header>

<aside class="sidebar" id="sidebar">
    <div class="sidebar-logo">
        <span class="logo-badge"><i class="bi bi-graph-up-arrow"></i></span>
        <div class="name">Penilaian Kinerja</div>
        <div class="role"><?= e($_SESSION['user']['role']) ?></div>
    </div>
    
    <?php if (!is_karyawan()): ?>
    <a href="index.php" class="<?= nav_active(['index.php']) ?>"><i class="bi bi-speedometer2"></i> Dashboard</a>
    <?php endif; ?>

    <a href="appraisals.php" class="<?= nav_active(['appraisals.php', 'appraisal_form.php', 'appraisal_show.php']) ?>">
        <i class="bi bi-clipboard-check"></i> <?= is_karyawan() ? 'Nilai Saya' : 'Penilaian' ?>
    </a>

    <?php if (is_admin() || is_manager()): ?>
    <a href="employees.php" class="<?= nav_active(['employees.php', 'employee_form.php']) ?>"><i class="bi bi-people"></i> Data Karyawan</a>
    <?php endif; ?>

    <?php if (is_admin() || is_manager()): ?>
    <a href="criterias.php" class="<?= nav_active(['criterias.php', 'criteria_form.php']) ?>"><i class="bi bi-list-check"></i> Kriteria</a>
    <?php endif; ?>

    <?php if (is_admin()): ?>
    <a href="users.php" class="<?= nav_active(['users.php', 'users_form.php']) ?>"><i class="bi bi-person-gear"></i> Akun Pengguna</a>
    <?php endif; ?>

    <a href="profile.php" class="user-name text-white text-decoration-none d-flex align-items-center gap-2">
        <?php if (!empty($_SESSION['user']['photo'])): ?>
            <img src="uploads/profile/<?= e($_SESSION['user']['photo']) ?>" alt="" class="rounded-circle" style="width:32px;height:32px;object-fit:cover">
            <?php else: ?>
                <i class="bi bi-person-circle"></i>
            <?php endif; ?>
            <?= e($_SESSION['user']['name']) ?> (<?= e($_SESSION['user']['role']) ?>)
        </a>
</aside>

<main class="content">
<div class="container-fluid pb-5">
<?php if ($f = flash()): ?>
    <div class="alert alert-<?= e($f['type']) ?>"><?= e($f['msg']) ?></div>
<?php endif; ?>