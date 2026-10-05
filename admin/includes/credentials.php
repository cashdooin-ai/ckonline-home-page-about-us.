<?php
/**
 * Resolves the effective admin login credentials, checked in this order:
 *   1. site_settings table (admin_username / admin_password_hash) - set via
 *      admin/change-password.php once logged in; works without a code
 *      deploy and always wins once set, so it can override a stale env var.
 *   2. ADMIN_USER / ADMIN_PASS_HASH environment variables on the server.
 *   3. Hardcoded fallback constants below, so login still works even if the
 *      DB is unreachable or nothing above has ever been set.
 */

function ckAdminDefaultUsername(): string
{
    return 'admin';
}

function ckAdminDefaultPassHash(): string
{
    // Reset here on 2026-08-19 (previous hash was unknown/lost) - the
    // plaintext was shared with the site owner once, out of band, at
    // reset time. Change it via admin/change-password.php once logged in;
    // that path is self-migrating and doesn't require another deploy.
    return '$2y$12$6v5hN3lrdRzUtEWGb1rXqu0NHKS0TFUkjOe0MKtLDhYg13hkVbRcK';
}

function ckAdminEnsureSettingsTable(PDO $pdo): void
{
    static $checked = false;
    if ($checked) return;
    $checked = true;
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS site_settings (
            `key` VARCHAR(100) NOT NULL PRIMARY KEY,
            `value` LONGTEXT,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    } catch (Throwable $e) {}
}

function ckGetAdminCredentials(): array
{
    $username = null;
    $hash     = null;

    try {
        require_once __DIR__ . '/../../config/db.php';
        $pdo = getDB();
        ckAdminEnsureSettingsTable($pdo);
        $stmt = $pdo->prepare("SELECT `key`, `value` FROM site_settings WHERE `key` IN ('admin_username','admin_password_hash')");
        $stmt->execute();
        foreach ($stmt->fetchAll(PDO::FETCH_KEY_PAIR) as $k => $v) {
            if ($k === 'admin_username' && $v !== '')      $username = $v;
            if ($k === 'admin_password_hash' && $v !== '') $hash     = $v;
        }
    } catch (Throwable $e) {}

    if ($username === null) $username = getenv('ADMIN_USER') ?: ckAdminDefaultUsername();
    if ($hash === null)     $hash     = getenv('ADMIN_PASS_HASH') ?: ckAdminDefaultPassHash();

    return ['username' => $username, 'hash' => $hash];
}

function ckSaveAdminCredentials(string $username, string $passwordHash): void
{
    require_once __DIR__ . '/../../config/db.php';
    $pdo = getDB();
    ckAdminEnsureSettingsTable($pdo);
    $stmt = $pdo->prepare(
        "INSERT INTO site_settings (`key`, `value`) VALUES (?, ?)
         ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)"
    );
    $stmt->execute(['admin_username', $username]);
    $stmt->execute(['admin_password_hash', $passwordHash]);
}
