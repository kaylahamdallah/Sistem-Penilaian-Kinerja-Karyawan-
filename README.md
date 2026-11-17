# Sistem Penilaian Kinerja Karyawan (PHP Native + MySQL)

## Cara Menjalankan
1. Install XAMPP (PHP 8.1+ dan MySQL), lalu nyalakan Apache & MySQL.
2. Salin folder `penilaian-kinerja` ke `C:\xampp\htdocs\`.
3. Buka phpMyAdmin (`http://localhost/phpmyadmin`) -> tab **Import** -> pilih `database.sql` -> Go.
4. Sesuaikan koneksi database di `config.php` bila perlu (default: user `root`, password kosong).
5. Buka `http://localhost/penilaian-kinerja/` di browser.

## Akun Demo
| Role    | Email               | Password |
|---------|---------------------|----------|
| Admin   | admin@example.com   | password |
| Manager | manager@example.com | password |
| budi    | budi@example.com    | password |
| siti  a  | siti@example.com    | password |

## Hak Akses
- Admin: kelola karyawan, kriteria, penilaian (termasuk hapus).
- Manager: membuat dan melihat penilaian.
- Karyawan: melihat dan mencetak hasil penilaian sendiri.

## Rumus
Nilai Akhir = Σ(nilai kriteria × bobot) / Σ bobot

Predikat: A (>=90), B (>=80), C (>=70), D (>=60), E (<60)

## Struktur File
- `config.php`      koneksi PDO, session, helper (login, CSRF, flash, predikat)
- `header.php` / `footer.php`  layout + navbar (Bootstrap 5 via CDN)
- `login.php`, `logout.php`, `index.php` (dashboard)
- `employees.php`, `employee_form.php`, `employee_delete.php`
- `criterias.php`, `criteria_form.php`, `criteria_delete.php`
- `appraisals.php`, `appraisal_form.php`, `appraisal_show.php`, `appraisal_delete.php`
- `database.sql`    struktur tabel + data contoh
