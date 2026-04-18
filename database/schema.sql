-- Script único para criar a base e todas as tabelas (substitui os antigos patch_auth_attempts.sql e patch_password_resets_schema.sql).
-- Uso: mysql -u root < database/schema.sql  (ou importar no phpMyAdmin / HeidiSQL)
--
-- Bases já existentes com password_resets desatualizada: fazer cópia de segurança, DROP TABLE password_resets; e executar
-- apenas o bloco CREATE TABLE password_resets deste ficheiro.

CREATE DATABASE IF NOT EXISTS auth_app
  DEFAULT CHARACTER SET utf8mb4
  DEFAULT COLLATE utf8mb4_unicode_ci;

USE auth_app;

CREATE TABLE IF NOT EXISTS users (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  email VARCHAR(255) NOT NULL UNIQUE,
  temporary_password VARCHAR(255) NOT NULL,
  must_reset_password TINYINT(1) NOT NULL DEFAULT 1,
  temporary_password_expires_at DATETIME NULL,
  password_hash VARCHAR(255) NULL,
  name VARCHAR(100) NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS password_resets (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id INT UNSIGNED NOT NULL,
  token_hash VARCHAR(255) NOT NULL UNIQUE,
  expires_at DATETIME NOT NULL,
  used TINYINT(1) NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  INDEX idx_password_resets_user_id (user_id),
  CONSTRAINT fk_password_resets_user
    FOREIGN KEY (user_id) REFERENCES users(id)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS auth_attempts (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  action VARCHAR(50) NOT NULL,
  identifier VARCHAR(255) NOT NULL,
  ip_address VARCHAR(64) NOT NULL,
  attempted_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  INDEX idx_auth_attempts_action_identifier_time (action, identifier, attempted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
