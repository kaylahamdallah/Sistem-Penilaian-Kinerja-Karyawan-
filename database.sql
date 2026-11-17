CREATE DATABASE IF NOT EXISTS penilaian_kinerja CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE penilaian_kinerja;

DROP TABLE IF EXISTS appraisal_details;
DROP TABLE IF EXISTS appraisals;
DROP TABLE IF EXISTS criterias;
DROP TABLE IF EXISTS employees;
DROP TABLE IF EXISTS users;

CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(100) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role ENUM('admin','manager') NOT NULL DEFAULT 'manager',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE employees (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nip VARCHAR(30) NOT NULL UNIQUE,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(100) NULL,
  position VARCHAR(100) NOT NULL,
  department VARCHAR(100) NOT NULL,
  join_date DATE NOT NULL,
  status ENUM('aktif','nonaktif') NOT NULL DEFAULT 'aktif',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE criterias (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  description TEXT NULL,
  weight TINYINT UNSIGNED NOT NULL
);

CREATE TABLE appraisals (
  id INT AUTO_INCREMENT PRIMARY KEY,
  employee_id INT NOT NULL,
  appraiser_id INT NOT NULL,
  period VARCHAR(50) NOT NULL,
  appraisal_date DATE NOT NULL,
  final_score DECIMAL(5,2) NOT NULL DEFAULT 0,
  grade CHAR(1) NULL,
  notes TEXT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
  FOREIGN KEY (appraiser_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE appraisal_details (
  id INT AUTO_INCREMENT PRIMARY KEY,
  appraisal_id INT NOT NULL,
  criteria_id INT NOT NULL,
  score TINYINT UNSIGNED NOT NULL,
  FOREIGN KEY (appraisal_id) REFERENCES appraisals(id) ON DELETE CASCADE,
  FOREIGN KEY (criteria_id) REFERENCES criterias(id) ON DELETE CASCADE
);

-- Password kedua akun di bawah: "password"
INSERT INTO users (name, email, password, role) VALUES
('Administrator', 'admin@example.com',   '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
('Manager',       'manager@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'manager');

INSERT INTO criterias (name, description, weight) VALUES
('Kualitas Kerja', 'Ketelitian dan hasil pekerjaan', 30),
('Produktivitas',  'Jumlah dan ketepatan waktu penyelesaian tugas', 25),
('Kedisiplinan',   'Kehadiran dan ketaatan pada aturan', 20),
('Kerja Sama',     'Kemampuan bekerja dalam tim', 15),
('Inisiatif',      'Kemampuan mengambil inisiatif dan solusi', 10);

INSERT INTO employees (nip, name, email, position, department, join_date, status) VALUES
('1001', 'Budi Santoso', 'budi@example.com', 'Staff IT',   'Teknologi Informasi',  '2022-01-10', 'aktif'),
('1002', 'Siti Aminah',  'siti@example.com', 'Staff HRD',  'Sumber Daya Manusia',  '2021-06-01', 'aktif');
