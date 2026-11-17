<?php
require 'config.php';
require_login();

$uid = (int)$_SESSION['user']['id'];

$stmt = $pdo->prepare('SELECT id, name, email, phone, photo, role, created_at FROM users WHERE id = ?');
$stmt->execute([$uid]);
$me = $stmt->fetch();
if (!$me) redirect('logout.php');

// Data kepegawaian untuk karyawan (dicocokkan lewat email yang sama)
$emp = null;
if ($me['role'] === 'karyawan') {
    $stmt = $pdo->prepare('SELECT nip, position, department, join_date FROM employees WHERE email = ? LIMIT 1');
    $stmt->execute([$me['email']]);
    $emp = $stmt->fetch() ?: null;
}

$uploadDir = __DIR__ . '/uploads/profile/';
$errors = [];
$phone = (string)$me['phone'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $phone = trim($_POST['phone'] ?? '');

    if ($phone !== '' && !preg_match('/^\+?[0-9][0-9\s\-]{6,18}[0-9]$/', $phone)) {
        $errors[] = 'Nomor telepon tidak valid (8-20 karakter, hanya angka, spasi, tanda - dan boleh diawali +).';
    }

    // Validasi foto (jika ada yang diunggah)
    $ext = null;
    $f = $_FILES['photo'] ?? null;
    if ($f && $f['error'] !== UPLOAD_ERR_NO_FILE) {
        if ($f['error'] === UPLOAD_ERR_INI_SIZE || $f['error'] === UPLOAD_ERR_FORM_SIZE || $f['size'] > 2 * 1024 * 1024) {
            $errors[] = 'Ukuran foto maksimal 2 MB.';
        } elseif ($f['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'Upload foto gagal. Coba lagi.';
        } else {
            $info = @getimagesize($f['tmp_name']);
            $allowed = [IMAGETYPE_JPEG => 'jpg', IMAGETYPE_PNG => 'png', IMAGETYPE_WEBP => 'webp'];
            if (!$info || !isset($allowed[$info[2]])) {
                $errors[] = 'Format foto harus JPG, PNG, atau WEBP.';
            } else {
                $ext = $allowed[$info[2]];
            }
        }
    }

    if (!$errors) {
        $newPhoto = $me['photo'];

        // Hapus foto lama jika dicentang
        if (!empty($_POST['remove_photo']) && $newPhoto) {
            @unlink($uploadDir . basename($newPhoto));
            $newPhoto = null;
        }

        // Simpan foto baru
        if ($ext !== null) {
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0775, true);
            $fileName = 'u' . $uid . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
            if (move_uploaded_file($f['tmp_name'], $uploadDir . $fileName)) {
                if ($newPhoto) @unlink($uploadDir . basename($newPhoto));
                $newPhoto = $fileName;
            } else {
                $errors[] = 'Foto gagal disimpan. Pastikan folder uploads bisa ditulis.';
            }
        }

        if (!$errors) {
            $stmt = $pdo->prepare('UPDATE users SET phone = ?, photo = ? WHERE id = ?');
            $stmt->execute([$phone !== '' ? $phone : null, $newPhoto, $uid]);
            $_SESSION['user']['photo'] = $newPhoto;
            flash('Profil berhasil disimpan.');
            redirect('profile.php');
        }
    }
}

$roleBadge = ['admin' => 'danger', 'manager' => 'primary', 'karyawan' => 'secondary'];

$title = 'Profil Saya';
require 'header.php';
?>
<h3 class="mb-3"><i class="bi bi-person-circle"></i> Profil Saya</h3>

<?php if ($errors): ?>
    <div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $er): ?><li><?= e($er) ?></li><?php endforeach; ?></ul></div>
<?php endif; ?>

<div class="row g-3">
    <div class="col-lg-4">
        <div class="card shadow-sm text-center p-4">
            <?php if ($me['photo']): ?>
                <img src="uploads/profile/<?= e($me['photo']) ?>" alt="Foto profil"
                     class="rounded-circle mx-auto mb-3" style="width:120px;height:120px;object-fit:cover">
            <?php else: ?>
                <div class="mx-auto mb-3 d-flex align-items-center justify-content-center text-white rounded-circle fw-bold"
                     style="width:120px;height:120px;font-size:3rem;background:var(--c-side,#0d6efd)">
                    <?= e(strtoupper(mb_substr($me['name'], 0, 1))) ?>
                </div>
            <?php endif; ?>
            <h5 class="mb-1"><?= e($me['name']) ?></h5>
            <div><span class="badge bg-<?= $roleBadge[$me['role']] ?? 'secondary' ?>"><?= e($me['role']) ?></span></div>
            <div class="text-muted small mt-2">Terdaftar sejak <?= e(date('d M Y', strtotime($me['created_at']))) ?></div>
        </div>
    </div>

    <div class="col-lg-8">
        <form method="POST" enctype="multipart/form-data" class="card shadow-sm card-body">
            <?= csrf_field() ?>
            <h5 class="mb-3">Kontak</h5>

            <div class="mb-3">
                <label class="form-label"><i class="bi bi-envelope"></i> Email</label>
                <input type="email" class="form-control" value="<?= e($me['email']) ?>" disabled>
                <div class="form-text">Email tidak bisa diubah di sini. Hubungi admin jika perlu diganti.</div>
            </div>

            <div class="mb-3">
                <label class="form-label"><i class="bi bi-telephone"></i> Nomor Telepon</label>
                <input type="text" name="phone" class="form-control" value="<?= e($phone) ?>" placeholder="Contoh: 081234567890">
            </div>

            <div class="mb-3">
                <label class="form-label"><i class="bi bi-image"></i> Foto Profil</label>
                <input type="file" name="photo" class="form-control" accept="image/jpeg,image/png,image/webp">
                <div class="form-text">JPG, PNG, atau WEBP. Maksimal 2 MB.</div>
                <?php if ($me['photo']): ?>
                <div class="form-check mt-2">
                    <input class="form-check-input" type="checkbox" name="remove_photo" id="remove_photo" value="1">
                    <label class="form-check-label" for="remove_photo">Hapus foto saat ini</label>
                </div>
                <?php endif; ?>
            </div>

            <div><button class="btn btn-primary">Simpan</button></div>
        </form>

        <?php if ($emp): ?>
        <div class="card shadow-sm card-body mt-3">
            <h5 class="mb-3">Data Kepegawaian</h5>
            <p class="mb-1"><span class="text-muted">NIP:</span> <?= e($emp['nip']) ?></p>
            <p class="mb-1"><span class="text-muted">Jabatan:</span> <?= e($emp['position']) ?></p>
            <p class="mb-1"><span class="text-muted">Departemen:</span> <?= e($emp['department']) ?></p>
            <p class="mb-0"><span class="text-muted">Tanggal Bergabung:</span> <?= e(date('d M Y', strtotime($emp['join_date']))) ?></p>
        </div>
        <?php endif; ?>
    </div>
</div>
<?php require 'footer.php'; ?>