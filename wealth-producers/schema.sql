-- Wealth Producers information form
CREATE DATABASE IF NOT EXISTS wealth_producers
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE wealth_producers;

CREATE TABLE IF NOT EXISTS applicants (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

  -- Step 1: Personal information
  full_name VARCHAR(150) NOT NULL,
  date_of_birth DATE NOT NULL,
  address VARCHAR(255) NOT NULL,
  email VARCHAR(150) NOT NULL,
  contact_number VARCHAR(30) NOT NULL,
  position ENUM('MARKETING','TRAINEE','CEO','GRAPHICS','VIDEO EDITOR','COPY EDITOR','SMM','WEB DEV') NOT NULL,

  -- Step 2: Emergency contact
  emergency_name VARCHAR(150) NOT NULL,
  emergency_number VARCHAR(30) NOT NULL,
  emergency_relationship ENUM('Father','Mother','Relative','Sibling','Friend') NOT NULL,

  -- Step 3: Skills (comma separated) + other niche
  skills VARCHAR(500) NOT NULL DEFAULT '',
  other_niche VARCHAR(255) NULL,

  -- Step 4: Documents (stored filenames, optional for now - "to follow")
  bank_info_file VARCHAR(255) NULL,
  assessment_file VARCHAR(255) NULL,
  contract_file VARCHAR(255) NULL,
  bank_to_follow TINYINT(1) NOT NULL DEFAULT 0,
  assessment_to_follow TINYINT(1) NOT NULL DEFAULT 0,
  contract_to_follow TINYINT(1) NOT NULL DEFAULT 0,

  submitted_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  ip_address VARCHAR(45) NULL,
  INDEX idx_email (email),
  INDEX idx_submitted (submitted_at)
) ENGINE=InnoDB;
