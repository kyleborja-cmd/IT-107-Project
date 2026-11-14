CREATE DATABASE IF NOT EXISTS health_secure CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE health_secure;

CREATE TABLE users (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 id_number CHAR(9) NOT NULL UNIQUE,
 first_name VARCHAR(20) NOT NULL,
 middle_name VARCHAR(20) NULL,
 family_name VARCHAR(20) NOT NULL,
 name_extension VARCHAR(10) NULL,
 birthdate DATE NOT NULL,
 age SMALLINT UNSIGNED NOT NULL,
 purok_street VARCHAR(50) NOT NULL,
 barangay VARCHAR(50) NOT NULL,
 city VARCHAR(50) NOT NULL,
 province VARCHAR(50) NOT NULL,
 country VARCHAR(50) NOT NULL,
 zip_code CHAR(4) NOT NULL,
 email VARCHAR(100) NOT NULL UNIQUE,
 username VARCHAR(16) NOT NULL UNIQUE,
 password_hash VARCHAR(255) NOT NULL,
 philhealth_enc TEXT NOT NULL,
 blood_type VARCHAR(3) NOT NULL,
 emergency_contact CHAR(11) NOT NULL,
 known_allergies VARCHAR(100) NULL,
 role ENUM('user','admin') NOT NULL DEFAULT 'user',
 is_verified TINYINT(1) NOT NULL DEFAULT 0,
 consent_at DATETIME NOT NULL,
 failed_attempts INT NOT NULL DEFAULT 0,
 lockout_until DATETIME NULL,
 last_failure_at DATETIME NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE auth_answers (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 user_id BIGINT UNSIGNED NOT NULL,
 question_code CHAR(2) NOT NULL,
 answer_hash VARCHAR(255) NOT NULL,
 UNIQUE KEY uq_user_question(user_id, question_code),
 CONSTRAINT fk_answers_user FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE otps (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 user_id BIGINT UNSIGNED NOT NULL,
 purpose ENUM('register','login','reset') NOT NULL,
 otp_hash VARCHAR(255) NOT NULL,
 expires_at DATETIME NOT NULL,
 attempts TINYINT UNSIGNED NOT NULL DEFAULT 0,
 resend_count TINYINT UNSIGNED NOT NULL DEFAULT 0,
 last_sent_at DATETIME NOT NULL,
 consumed_at DATETIME NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 INDEX idx_otp_user_purpose(user_id,purpose,created_at),
 CONSTRAINT fk_otp_user FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE password_history (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 user_id BIGINT UNSIGNED NOT NULL,
 password_hash VARCHAR(255) NOT NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 INDEX idx_password_history(user_id,created_at),
 CONSTRAINT fk_history_user FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE activity_log (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 event_type VARCHAR(40) NOT NULL,
 username_typed VARCHAR(100) NULL,
 user_id BIGINT UNSIGNED NULL,
 ip_address VARCHAR(45) NOT NULL,
 user_agent VARCHAR(255) NULL,
 details TEXT NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 INDEX idx_log_created(created_at),
 INDEX idx_log_ip(ip_address,created_at),
 INDEX idx_log_event(event_type,created_at),
 INDEX idx_log_username(username_typed,created_at)
) ENGINE=InnoDB;

CREATE TABLE ip_blocks (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 ip_address VARCHAR(45) NOT NULL UNIQUE,
 blocked_until DATETIME NOT NULL,
 reason VARCHAR(255) NOT NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE security_flags (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 pattern_key VARCHAR(40) NOT NULL,
 target_value VARCHAR(100) NOT NULL,
 severity ENUM('LOW','MED','HIGH') NOT NULL,
 details TEXT NOT NULL,
 flag_date DATE NOT NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 UNIQUE KEY uq_flag_day(pattern_key,target_value,flag_date)
) ENGINE=InnoDB;
