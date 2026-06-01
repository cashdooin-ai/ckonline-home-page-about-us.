<?php
// INTEGRATION: Admin portal reads this DB. Partner portal writes to colleges/courses tables.
// Shared DB: collegekampus -- see database/schema.sql

// ─── Site base URL (change this when deploying to /dashboard subfolder) ───────
// For Hostinger: 'https://online.collegekampus.com/dashboard'
// For local dev:  'http://localhost/dashboard'
define('SITE_BASE', rtrim(getenv('SITE_BASE') ?: 'https://online.collegekampus.com/dashboard', '/'));
define('SITE_NAME', 'CollegeKampus Online');
define('SITE_DOMAIN', 'online.collegekampus.com');

define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('DB_NAME') ?: 'collegekampus');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');
define('DB_CHARSET', 'utf8mb4');

function getPDO(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            http_response_code(500);
            die(json_encode(['error' => 'Database connection failed.']));
        }
    }
    return $pdo;
}

// Alias for compatibility with student portal files
function getDB(): PDO {
    return getPDO();
}
