<?php
// Database settings - edit these for your server
const DB_HOST = 'localhost';
const DB_NAME = 'wealth_producers';
const DB_USER = 'root';
const DB_PASS = '';

const UPLOAD_DIR = __DIR__ . '/uploads/';
const MAX_UPLOAD_BYTES = 5 * 1024 * 1024; // 5 MB per file

const POSITIONS = ['MARKETING','TRAINEE','CEO','GRAPHICS','VIDEO EDITOR','COPY EDITOR','SMM','WEB DEV'];
const RELATIONSHIPS = ['Father','Mother','Relative','Sibling','Friend'];
const SKILLS = [
  'Video Editing','Social Media Management','Copywriting','Marketing',
  'Graphics Design','Web Development','Animation','Photography',
];

session_start();

function db(): PDO {
    static $pdo = null;
    if (!$pdo) {
        $pdo = new PDO(
            'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
            DB_USER, DB_PASS,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
        );
    }
    return $pdo;
}

function ensure_applicant_schema(): void {
    try {
        $tables = db()->query("SHOW TABLES LIKE 'applicants'")->fetchAll(PDO::FETCH_COLUMN);
        if (!$tables) {
            db()->exec(
                "CREATE TABLE IF NOT EXISTS applicants (
                    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    full_name VARCHAR(150) NOT NULL,
                    date_of_birth DATE NOT NULL,
                    address VARCHAR(255) NOT NULL,
                    email VARCHAR(150) NOT NULL,
                    contact_number VARCHAR(30) NOT NULL,
                    position ENUM('MARKETING','TRAINEE','CEO','GRAPHICS','VIDEO EDITOR','COPY EDITOR','SMM','WEB DEV') NOT NULL,
                    emergency_name VARCHAR(150) NOT NULL,
                    emergency_number VARCHAR(30) NOT NULL,
                    emergency_relationship ENUM('Father','Mother','Relative','Sibling','Friend') NOT NULL,
                    skills VARCHAR(500) NOT NULL DEFAULT '',
                    other_niche VARCHAR(255) NULL,
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
                ) ENGINE=InnoDB;"
            );
            return;
        }

        $columns = db()->query('SHOW COLUMNS FROM applicants')->fetchAll(PDO::FETCH_COLUMN, 0);
        $required = [
            'bank_to_follow' => 'TINYINT(1) NOT NULL DEFAULT 0',
            'assessment_to_follow' => 'TINYINT(1) NOT NULL DEFAULT 0',
            'contract_to_follow' => 'TINYINT(1) NOT NULL DEFAULT 0',
        ];

        foreach ($required as $column => $definition) {
            if (!in_array($column, $columns, true)) {
                db()->exec("ALTER TABLE applicants ADD COLUMN `$column` $definition");
            }
        }
    } catch (Throwable $e) {
        error_log('Schema check failed: ' . $e->getMessage());
    }
}

function e(string $s): string {
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

function csrf_token(): string {
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}
