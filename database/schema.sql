-- =====================================================================
-- Sistem Penilaian Tugas Mahasiswa — Skema Basis Data MySQL
-- ETS PAW (Soal 3) — Laravel 10 + MySQL 8
--
-- Skema ini setara dengan hasil `php artisan migrate` yang ditulis
-- sebagai SQL manual agar mudah dibaca dan diimpor (deliverable PRD:
-- "SQL schema"). Untuk menyalakan ulang seluruh project, gunakan:
--   php artisan migrate:fresh --seed
-- =====================================================================

CREATE DATABASE IF NOT EXISTS penilaian_mahasiswa
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE penilaian_mahasiswa;

-- ---------------------------------------------------------------------
-- 1. Pengguna (dosen). Mahasiswa TIDAK memiliki akun (per PRD).
-- ---------------------------------------------------------------------
CREATE TABLE users (
    id                BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name              VARCHAR(255) NOT NULL,
    email             VARCHAR(255) NOT NULL UNIQUE,
    email_verified_at TIMESTAMP NULL,
    password          VARCHAR(255) NOT NULL,
    remember_token    VARCHAR(100) NULL,
    created_at        TIMESTAMP NULL,
    updated_at        TIMESTAMP NULL
) ENGINE=InnoDB;

CREATE TABLE password_reset_tokens (
    email      VARCHAR(255) PRIMARY KEY,
    token      VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NULL
) ENGINE=InnoDB;

CREATE TABLE failed_jobs (
    id         BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid       VARCHAR(255) NOT NULL UNIQUE,
    connection TEXT NOT NULL,
    queue      TEXT NOT NULL,
    payload    LONGTEXT NOT NULL,
    exception  LONGTEXT NOT NULL,
    failed_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE personal_access_tokens (
    id             BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tokenable_type VARCHAR(255) NOT NULL,
    tokenable_id   BIGINT UNSIGNED NOT NULL,
    name           VARCHAR(255) NOT NULL,
    token          VARCHAR(64) NOT NULL UNIQUE,
    abilities      TEXT NULL,
    last_used_at   TIMESTAMP NULL,
    expires_at     TIMESTAMP NULL,
    created_at     TIMESTAMP NULL,
    updated_at     TIMESTAMP NULL,
    INDEX tokenable_index (tokenable_type, tokenable_id)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 2. Mata kuliah (diampu dosen)
-- ---------------------------------------------------------------------
CREATE TABLE mata_kuliah (
    id         BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id    BIGINT UNSIGNED NOT NULL,
    kode       VARCHAR(20) NOT NULL UNIQUE,
    nama       VARCHAR(255) NOT NULL,
    semester   TINYINT UNSIGNED NOT NULL,
    sks        TINYINT UNSIGNED NOT NULL DEFAULT 3,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    CONSTRAINT fk_mk_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 3. Kelas (rombel per mata kuliah)
-- ---------------------------------------------------------------------
CREATE TABLE kelas (
    id            BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    mata_kuliah_id BIGINT UNSIGNED NOT NULL,
    nama_kelas    VARCHAR(50) NOT NULL,
    created_at    TIMESTAMP NULL,
    updated_at    TIMESTAMP NULL,
    CONSTRAINT fk_kelas_mk FOREIGN KEY (mata_kuliah_id) REFERENCES mata_kuliah (id) ON DELETE CASCADE,
    UNIQUE KEY uniq_kelas (mata_kuliah_id, nama_kelas)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 4. Mahasiswa (tanpa akun login)
-- ---------------------------------------------------------------------
CREATE TABLE mahasiswa (
    id             BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    kelas_id       BIGINT UNSIGNED NOT NULL,
    nim            VARCHAR(20) NOT NULL UNIQUE,
    nama           VARCHAR(255) NOT NULL,
    program_studi  VARCHAR(100) NOT NULL DEFAULT 'Teknik Informatika',
    created_at     TIMESTAMP NULL,
    updated_at     TIMESTAMP NULL,
    CONSTRAINT fk_mhs_kelas FOREIGN KEY (kelas_id) REFERENCES kelas (id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 5. Komponen penilaian + bobot (decimal 3,2 -> 0,20 = 20%)
-- ---------------------------------------------------------------------
CREATE TABLE komponen_penilaian (
    id             BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    mata_kuliah_id BIGINT UNSIGNED NOT NULL,
    nama           VARCHAR(100) NOT NULL,
    bobot          DECIMAL(3, 2) NOT NULL,
    created_at     TIMESTAMP NULL,
    updated_at     TIMESTAMP NULL,
    CONSTRAINT fk_komponen_mk FOREIGN KEY (mata_kuliah_id) REFERENCES mata_kuliah (id) ON DELETE CASCADE,
    UNIQUE KEY uniq_komponen (mata_kuliah_id, nama)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 6. Nilai per mahasiswa per komponen (0-100, two decimals)
-- ---------------------------------------------------------------------
CREATE TABLE nilai (
    id           BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    mahasiswa_id BIGINT UNSIGNED NOT NULL,
    komponen_id  BIGINT UNSIGNED NOT NULL,
    nilai        DECIMAL(5, 2) NOT NULL,
    created_at   TIMESTAMP NULL,
    updated_at   TIMESTAMP NULL,
    CONSTRAINT fk_nilai_mhs FOREIGN KEY (mahasiswa_id) REFERENCES mahasiswa (id) ON DELETE CASCADE,
    CONSTRAINT fk_nilai_komponen FOREIGN KEY (komponen_id) REFERENCES komponen_penilaian (id) ON DELETE CASCADE,
    UNIQUE KEY uniq_nilai (mahasiswa_id, komponen_id)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 7. Feedback dosen (opsional) per mahasiswa per mata kuliah
-- ---------------------------------------------------------------------
CREATE TABLE feedback (
    id             BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    mahasiswa_id   BIGINT UNSIGNED NOT NULL,
    mata_kuliah_id BIGINT UNSIGNED NOT NULL,
    isi            TEXT NOT NULL,
    created_at     TIMESTAMP NULL,
    updated_at     TIMESTAMP NULL,
    CONSTRAINT fk_fb_mhs FOREIGN KEY (mahasiswa_id) REFERENCES mahasiswa (id) ON DELETE CASCADE,
    CONSTRAINT fk_fb_mk FOREIGN KEY (mata_kuliah_id) REFERENCES mata_kuliah (id) ON DELETE CASCADE,
    UNIQUE KEY uniq_feedback (mahasiswa_id, mata_kuliah_id)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- Data awal minimal (hasil seeder menyediakan data lengkap).
-- Password: "password" (bcrypt).
-- ---------------------------------------------------------------------
INSERT INTO users (name, email, password, email_verified_at, created_at, updated_at)
VALUES (
    'Dzaky Raihan Fauzan',
    'raiiisaja@gmail.com',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    NOW(), NOW(), NOW()
);