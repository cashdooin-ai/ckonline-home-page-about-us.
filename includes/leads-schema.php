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

        // Ad-campaign attribution, added after the fact - existing installs
        // get these columns backfilled the same self-migrating way
        // includes/colleges-seed.php adds new columns to `colleges`.
        $cols = array_flip($db->query("SHOW COLUMNS FROM online_leads")->fetchAll(PDO::FETCH_COLUMN));
        foreach (['utm_source', 'utm_medium', 'utm_campaign'] as $col) {
            if (!isset($cols[$col])) {
                $db->exec("ALTER TABLE online_leads ADD COLUMN `$col` VARCHAR(100) DEFAULT NULL");
            }
        }
    } catch (Throwable $e) {
        error_log('onlineLeadsEnsureSchema failed: ' . $e->getMessage());
    }
}

/**
 * Mirrors an online_leads row into the shared `leads` table that
 * ckampus-dasboard's admin (admin/leads.php) already manages, under a
 * dedicated "Online Leads" partner account - so staff work these leads
 * through that admin's existing status pipeline (new/contacted/interested/
 * applied/converted/lost/follow_up), call/note log, follow-ups, and
 * assignment/reassignment to any staff member or affiliate, instead of a
 * second, parallel CRM built here.
 *
 * Entirely best-effort: this runs after online_leads has already been
 * written, so any failure here (shared tables unreachable, schema
 * mismatch) is swallowed rather than surfaced to the visitor submitting
 * the form - the online-portal-native lead capture must never depend on
 * the other admin's system being reachable.
 *
 * ckampus-dasboard's own admin/config/admin-config.php independently
 * ensures the same "Online Leads" partner row (same sentinel email) -
 * these two ensure-blocks can't share code since they're separate PHP
 * applications on the same database, so keep them in sync if this ever
 * changes.
 */
function pushLeadToSharedCrm(PDO $db, array $lead): void
{
    try {
        // Defensive parity only - in production these tables already exist,
        // owned by ckampus-dasboard's admin/api/partners.php and
        // admin/api/leads.php. This just avoids a hard failure if this ever
        // runs against a database where that admin hasn't been set up yet.
        try {
            $db->query("SELECT 1 FROM partners LIMIT 1");
        } catch (Throwable $e) {
            $db->exec("CREATE TABLE IF NOT EXISTS partners (
                id             INT AUTO_INCREMENT PRIMARY KEY,
                full_name      VARCHAR(100) NOT NULL,
                email          VARCHAR(150) NOT NULL UNIQUE,
                password_hash  VARCHAR(255) NOT NULL,
                company_name   VARCHAR(150) DEFAULT NULL,
                partner_type   ENUM('counsellor','agent','institution','lender','other') DEFAULT 'agent',
                is_active      TINYINT(1) DEFAULT 1,
                approval_status ENUM('pending','approved','rejected') DEFAULT 'approved',
                notes          TEXT DEFAULT NULL,
                created_at     DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at     DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_email (email)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        }
        try {
            $db->query("SELECT 1 FROM leads LIMIT 1");
        } catch (Throwable $e) {
            $db->exec("CREATE TABLE IF NOT EXISTS leads (
                id               INT AUTO_INCREMENT PRIMARY KEY,
                partner_id       INT NOT NULL,
                student_name     VARCHAR(100) NOT NULL,
                student_email    VARCHAR(150) DEFAULT NULL,
                student_phone    VARCHAR(15) NOT NULL,
                course_interest  VARCHAR(200) DEFAULT NULL,
                college_interest VARCHAR(200) DEFAULT NULL,
                college_id       INT DEFAULT NULL,
                city             VARCHAR(100) DEFAULT NULL,
                state            VARCHAR(100) DEFAULT NULL,
                source           VARCHAR(100) DEFAULT NULL,
                status           ENUM('new','contacted','interested','applied','converted','lost','follow_up') DEFAULT 'new',
                priority         ENUM('low','medium','high') DEFAULT 'medium',
                notes            TEXT DEFAULT NULL,
                created_at       DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at       DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_partner (partner_id),
                INDEX idx_status (status),
                INDEX idx_created (created_at),
                INDEX idx_college (college_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        }

        $email = 'online-portal@collegekampus.internal';
        $stmt = $db->prepare("SELECT id FROM partners WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $row = $stmt->fetch();
        if ($row) {
            $partnerId = (int) $row['id'];
        } else {
            $ins = $db->prepare(
                "INSERT INTO partners (full_name, email, password_hash, company_name, partner_type, is_active, approval_status, notes)
                 VALUES (?, ?, ?, ?, 'other', 1, 'approved', ?)"
            );
            $ins->execute([
                'Online Leads',
                $email,
                password_hash(bin2hex(random_bytes(32)), PASSWORD_DEFAULT),
                'online.collegekampus.com',
                'System account - do not delete or log into. Auto-created so leads captured on online.collegekampus.com route into this leads CRM under their own filterable category.',
            ]);
            $partnerId = (int) $db->lastInsertId();
        }

        $noteParts = [];
        if (!empty($lead['message']))       $noteParts[] = $lead['message'];
        if (!empty($lead['qualification'])) $noteParts[] = 'Qualification: ' . $lead['qualification'];
        if (!empty($lead['utm_source']) || !empty($lead['utm_campaign'])) {
            $noteParts[] = 'Campaign: ' . trim(($lead['utm_source'] ?? '') . ' / ' . ($lead['utm_medium'] ?? '') . ' / ' . ($lead['utm_campaign'] ?? ''), ' /');
        }
        if (!empty($lead['page_url'])) $noteParts[] = 'Page: ' . $lead['page_url'];

        $ins = $db->prepare(
            "INSERT INTO leads (partner_id, student_name, student_email, student_phone, course_interest, college_interest, college_id, state, source, notes)
             VALUES (?,?,?,?,?,?,?,?,?,?)"
        );
        $ins->execute([
            $partnerId,
            $lead['name'],
            $lead['email'] ?: null,
            $lead['phone'],
            $lead['course'] ?: null,
            $lead['college_name'] ?: null,
            $lead['college_id'] ?: null,
            $lead['state'] ?: null,
            'online-portal:' . ($lead['source'] ?: 'website'),
            $noteParts ? implode("\n", $noteParts) : null,
        ]);
    } catch (Throwable $e) {
        error_log('pushLeadToSharedCrm failed: ' . $e->getMessage());
    }
}
