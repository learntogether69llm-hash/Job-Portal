-- =====================================================================
-- schema.sql  |  Job Portal  |  Phase 1: signup, login, onboarding
-- MariaDB 10.4+ / XAMPP  |  InnoDB  |  utf8mb4 / utf8mb4_unicode_ci
-- All datetimes are stored in UTC.
-- =====================================================================

-- ---------------------------------------------------------------------
-- Database and session settings
-- ---------------------------------------------------------------------
CREATE DATABASE IF NOT EXISTS job_portal
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE job_portal;
SET NAMES utf8mb4;

-- ---------------------------------------------------------------------
-- 1. Reference table: industries
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS industries (
  id         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  name       VARCHAR(120)    NOT NULL,
  created_at DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_industries_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 2. Reference table: qualifications
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS qualifications (
  id         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  name       VARCHAR(120)    NOT NULL,
  created_at DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_qualifications_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 3. Reference table: employment_types
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS employment_types (
  id         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  name       VARCHAR(60)     NOT NULL,
  created_at DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_employment_types_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 4. Users (signup / login)
--    role stays NULL until the user picks "Looking for an Employee"
--    (employer) or "Looking for Work" (job_seeker).
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS users (
  id            BIGINT UNSIGNED                         NOT NULL AUTO_INCREMENT,
  name          VARCHAR(150)                            NOT NULL,
  email         VARCHAR(255)                            NOT NULL,
  mobile_number VARCHAR(20)                             NOT NULL COMMENT 'Store with country code, e.g. +919876543210',
  password_hash VARCHAR(255)                            NOT NULL COMMENT 'PHP password_hash() output',
  role          ENUM('employer','job_seeker')           NULL DEFAULT NULL,
  status        ENUM('active','suspended','deleted')    NOT NULL DEFAULT 'active',
  last_login_at DATETIME                                NULL,
  created_at    DATETIME                                NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at    DATETIME                                NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_users_email (email),
  UNIQUE KEY uq_users_mobile_number (mobile_number),
  KEY idx_users_role_status (role, status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 5. Password resets (one active reset per user)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS password_resets (
  user_id    BIGINT UNSIGNED NOT NULL,
  token_hash CHAR(64)        NOT NULL COMMENT 'SHA-256 hex of the reset token',
  expires_at DATETIME        NOT NULL,
  created_at DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (user_id),
  UNIQUE KEY uq_password_resets_token_hash (token_hash),
  KEY idx_password_resets_expires_at (expires_at),
  CONSTRAINT fk_password_resets_user
    FOREIGN KEY (user_id) REFERENCES users (id)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 6. Employer profiles ("Looking for an Employee" onboarding)
--    id is both the primary key and the foreign key to users.id.
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS employer_profiles (
  id                 BIGINT UNSIGNED NOT NULL,
  name               VARCHAR(150)    NOT NULL,
  company_name       VARCHAR(200)    NOT NULL,
  location           VARCHAR(200)    NOT NULL,
  industry_id        BIGINT UNSIGNED NOT NULL,
  position           VARCHAR(150)    NOT NULL,
  employment_type_id BIGINT UNSIGNED NOT NULL,
  salary             DECIMAL(12,2)   NULL,
  job_details        TEXT            NULL,
  company_logo       VARCHAR(500)    NULL COMMENT 'File path or URL',
  created_at         DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at         DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_employer_profiles_industry_id (industry_id),
  KEY idx_employer_profiles_employment_type_id (employment_type_id),
  KEY idx_employer_profiles_location (location),
  KEY idx_employer_profiles_company_name (company_name),
  CONSTRAINT fk_employer_profiles_user
    FOREIGN KEY (id) REFERENCES users (id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_employer_profiles_industry
    FOREIGN KEY (industry_id) REFERENCES industries (id)
    ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT fk_employer_profiles_employment_type
    FOREIGN KEY (employment_type_id) REFERENCES employment_types (id)
    ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT chk_employer_profiles_salary
    CHECK (salary IS NULL OR salary >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 7. Job seeker profiles ("Looking for Work" onboarding)
--    id is both the primary key and the foreign key to users.id.
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS job_seeker_profiles (
  id                 BIGINT UNSIGNED  NOT NULL,
  name               VARCHAR(150)     NOT NULL,
  age                TINYINT UNSIGNED NOT NULL,
  location           VARCHAR(200)     NOT NULL,
  nationality        VARCHAR(100)     NOT NULL,
  industry_id        BIGINT UNSIGNED  NOT NULL,
  qualification_id   BIGINT UNSIGNED  NOT NULL,
  experience_years   DECIMAL(4,1)     NOT NULL DEFAULT 0.0,
  expected_salary    DECIMAL(12,2)    NULL,
  employment_type_id BIGINT UNSIGNED  NOT NULL,
  profile_picture    VARCHAR(500)     NULL,
  cv_path            VARCHAR(500)     NULL,
  created_at         DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at         DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_job_seeker_profiles_location (location),
  KEY idx_job_seeker_profiles_industry_id (industry_id),
  KEY idx_job_seeker_profiles_qualification_id (qualification_id),
  KEY idx_job_seeker_profiles_employment_type_id (employment_type_id),
  KEY idx_job_seeker_profiles_experience_years (experience_years),
  KEY idx_job_seeker_profiles_expected_salary (expected_salary),
  CONSTRAINT fk_job_seeker_profiles_user
    FOREIGN KEY (id) REFERENCES users (id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_job_seeker_profiles_industry
    FOREIGN KEY (industry_id) REFERENCES industries (id)
    ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT fk_job_seeker_profiles_qualification
    FOREIGN KEY (qualification_id) REFERENCES qualifications (id)
    ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT fk_job_seeker_profiles_employment_type
    FOREIGN KEY (employment_type_id) REFERENCES employment_types (id)
    ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT chk_job_seeker_profiles_age
    CHECK (age BETWEEN 16 AND 90),
  CONSTRAINT chk_job_seeker_profiles_experience_years
    CHECK (experience_years >= 0 AND experience_years <= 70),
  CONSTRAINT chk_job_seeker_profiles_expected_salary
    CHECK (expected_salary IS NULL OR expected_salary >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
-- SEED DATA (reference tables only; safe to re-run)
-- =====================================================================

-- Industries
INSERT IGNORE INTO industries (name) VALUES
  ('Information Technology'),
  ('Healthcare'),
  ('Finance & Banking'),
  ('Education'),
  ('Manufacturing'),
  ('Retail & E-commerce'),
  ('Construction & Real Estate'),
  ('Hospitality & Tourism'),
  ('Logistics & Transportation'),
  ('Marketing & Advertising'),
  ('Media & Entertainment'),
  ('Legal Services'),
  ('Telecommunications'),
  ('Energy & Utilities'),
  ('Agriculture'),
  ('Government & Non-profit'),
  ('Other');

-- Qualifications
INSERT IGNORE INTO qualifications (name) VALUES
  ('No Formal Education'),
  ('High School'),
  ('Vocational / Trade Certificate'),
  ('Diploma'),
  ('Associate Degree'),
  ('Bachelor''s Degree'),
  ('Postgraduate Diploma'),
  ('Master''s Degree'),
  ('Doctorate (PhD)'),
  ('Professional Certification');

-- Employment types
INSERT IGNORE INTO employment_types (name) VALUES
  ('Full Time'),
  ('Part Time'),
  ('Contract'),
  ('Freelance'),
  ('Internship'),
  ('Temporary'),
  ('Remote');
