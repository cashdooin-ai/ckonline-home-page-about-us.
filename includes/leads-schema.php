<?php
/**
 * Self-migrating schema for this portal's own lead-capture table.
 *
 * Every lead-capture entry point here (api/leads.php, counselling.php,
 * ck-assured.php, contact.php, admin/index.php, api/stats.php) assumed a
 * table simply called `leads` already existed, with columns name/email/
 * phone/source/message/course_interest/state/qualification/college_id/
 * course_id/page_url/created_at - but that table was never actually
 * created for this repo. Worse, `leads` already exists on the shared DB
 * as ckampus-dasboard's partner-lead-routing table (student_name/
 * student_email/student_phone columns, and a NOT NULL partner_id this
 * portal has no value for) - a real, deployed, incompatible table this
 * portal's inserts were silently colliding with by name. Using a
 * distinctly-named table here avoids that collision entirely.
 */
function onlineLeadsEnsureSchema(PDO $db): void
{
    static $checked = false;
    if ($checked) return;
    $checked = true;

    try {
        try {
            $db->query("SELECT 1 FROM online_leads LIMIT 1");
        } catch (Throwable $e) {
            $db->exec("CREATE TABLE IF NOT EXISTS online_leads (
                id               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                name             VARCHAR(150) NOT NULL,
                email            VARCHAR(180) DEFAULT NULL,
                phone            VARCHAR(20) DEFAULT NULL,
                source           VARCHAR(100) DEFAULT NULL,
                message          TEXT DEFAULT NULL,
                course_interest  VARCHAR(150) DEFAULT NULL,
                state            VARCHAR(100) DEFAULT NULL,
                qualification    VARCHAR(150) DEFAULT NULL,
                college_id       INT UNSIGNED DEFAULT NULL,
                course_id        INT UNSIGNED DEFAULT NULL,
                page_url         VARCHAR(500) DEFAULT NULL,
                created_at       DATETIME DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_created (created_at),
                INDEX idx_college (college_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        }
    } catch (Throwable $e) {
        error_log('onlineLeadsEnsureSchema failed: ' . $e->getMessage());
    }
}
