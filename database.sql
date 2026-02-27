CREATE DATABASE IF NOT EXISTS sipena;
USE sipena;

CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(100) UNIQUE NOT NULL,
  password VARCHAR(255) NOT NULL,
  tipe_user ENUM('siswa','pegawai') NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE siswa_profiles (
  user_id INT PRIMARY KEY,
  nama VARCHAR(100) NOT NULL,
  nis VARCHAR(30),
  kelas VARCHAR(20),
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE pegawai_profiles (
  user_id INT PRIMARY KEY,
  nama VARCHAR(100) NOT NULL,
  nip VARCHAR(30),
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE jabatan (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nama_jabatan VARCHAR(50) NOT NULL
);

CREATE TABLE user_jabatan (
  user_id INT,
  jabatan_id INT,
  PRIMARY KEY (user_id, jabatan_id),
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (jabatan_id) REFERENCES jabatan(id) ON DELETE CASCADE
);

CREATE TABLE kategori_laporan (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nama VARCHAR(50) NOT NULL
);

CREATE TABLE laporan (
  id INT AUTO_INCREMENT PRIMARY KEY,
  judul VARCHAR(150),
  deskripsi TEXT,
  kategori_id INT,
  pelapor_id INT,
  wali_kelas_id INT NULL,
  status ENUM(
    'menunggu',
    'ditolak_wali',
    'diterima_wali',
    'diproses_sarpras',
    'selesai'
  ) DEFAULT 'menunggu',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (kategori_id) REFERENCES kategori_laporan(id),
  FOREIGN KEY (pelapor_id) REFERENCES users(id),
  FOREIGN KEY (wali_kelas_id) REFERENCES users(id)
);

CREATE TABLE anggaran (
  id INT AUTO_INCREMENT PRIMARY KEY,
  laporan_id INT UNIQUE,
  dibuat_oleh INT,
  total_biaya DECIMAL(15,2),
  status ENUM(
    'draft',
    'diajukan_ke_tu',
    'ditolak_tu',
    'diterima_tu',
    'ditolak_kepsek',
    'disetujui_kepsek'
  ) DEFAULT 'draft',
  catatan TEXT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (laporan_id) REFERENCES laporan(id),
  FOREIGN KEY (dibuat_oleh) REFERENCES users(id)
);

CREATE TABLE anggaran_detail (
  id INT AUTO_INCREMENT PRIMARY KEY,
  anggaran_id INT,
  nama_item VARCHAR(100),
  qty INT,
  harga_satuan DECIMAL(15,2),
  subtotal DECIMAL(15,2),
  FOREIGN KEY (anggaran_id) REFERENCES anggaran(id) ON DELETE CASCADE
);

CREATE TABLE notifications (
  id INT AUTO_INCREMENT PRIMARY KEY,
  laporan_id INT,
  tipe VARCHAR(50),
  judul VARCHAR(150),
  pesan TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (laporan_id) REFERENCES laporan(id)
);

CREATE TABLE notification_recipients (
  id INT AUTO_INCREMENT PRIMARY KEY,
  notification_id INT,
  user_id INT,
  is_read BOOLEAN DEFAULT 0,
  read_at TIMESTAMP NULL,
  FOREIGN KEY (notification_id) REFERENCES notifications(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE app_settings (
  id INT PRIMARY KEY DEFAULT 1,
  app_name VARCHAR(100) NOT NULL,
  app_short_name VARCHAR(50),
  app_version VARCHAR(20),
  school_name VARCHAR(150),
  school_logo VARCHAR(255),
  maintenance_mode BOOLEAN DEFAULT 0,
  updated_at TIMESTAMP NULL
);

CREATE TABLE audit_logs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NULL,
  action VARCHAR(100),
  target_type VARCHAR(50),
  target_id INT NULL,
  description TEXT,
  ip_address VARCHAR(45),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE app_installer (
  id INT AUTO_INCREMENT PRIMARY KEY,
  installed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  installed_by VARCHAR(100),
  environment ENUM('local','development','production') DEFAULT 'development',
  db_version VARCHAR(20),
  notes TEXT
);

CREATE TABLE license_cache (
  id INT PRIMARY KEY DEFAULT 1,
  license_key VARCHAR(100),
  status ENUM('active','expired','invalid','revoked') NOT NULL,
  expires_at DATE NULL,
  last_checked_at TIMESTAMP,
  grace_until DATE NULL,
  server_fingerprint VARCHAR(255)
);

-- Users
CREATE UNIQUE INDEX idx_users_email
ON users (email);

CREATE INDEX idx_users_tipe
ON users (tipe_user);

-- User Jabatan
CREATE INDEX idx_user_jabatan_user
ON user_jabatan (user_id);

CREATE INDEX idx_user_jabatan_jabatan
ON user_jabatan (jabatan_id);

-- Laporan
CREATE INDEX idx_laporan_kategori
ON laporan (kategori_id);

CREATE INDEX idx_laporan_pelapor
ON laporan (pelapor_id);

CREATE INDEX idx_laporan_wali
ON laporan (wali_kelas_id);

CREATE INDEX idx_laporan_status
ON laporan (status);

CREATE INDEX idx_laporan_created
ON laporan (created_at);

-- Anggaran
CREATE INDEX idx_anggaran_laporan
ON anggaran (laporan_id);

CREATE INDEX idx_anggaran_status
ON anggaran (status);

-- Notifications
CREATE INDEX idx_notifications_laporan
ON notifications (laporan_id);

CREATE INDEX idx_notification_recipients_user
ON notification_recipients (user_id);

CREATE INDEX idx_notification_recipients_read
ON notification_recipients (user_id, is_read);

-- Audit Logs
CREATE INDEX idx_audit_user
ON audit_logs (user_id);

CREATE INDEX idx_audit_created
ON audit_logs (created_at);

-- License Cache
CREATE INDEX idx_license_cache_status
ON license_cache (status);