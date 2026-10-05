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
                channel          VARCHAR(40) DEFAULT NULL,
                phone_normalized VARCHAR(15) DEFAULT NULL,
                status           ENUM('new','contacted','interested','applied','converted','lost','follow_up') DEFAULT 'new',
                priority         ENUM('low','medium','high') DEFAULT 'medium',
                notes            TEXT DEFAULT NULL,
                created_at       DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at       DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_partner (partner_id),
                INDEX idx_status (status),
                INDEX idx_created (created_at),
                INDEX idx_college (college_id),
                INDEX idx_phone_normalized (phone_normalized)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        }

        // In production this table already exists (created by ckampus-
        // dasboard's admin, which also already migrated these two columns
        // in - see shared/includes/lead-ingest.php's leadIngestEnsureSchema()
        // there) - this only matters the first time this app ever runs
        // against a fresh database that admin hasn't touched yet.
        $leadsCols = array_flip($db->query("SHOW COLUMNS FROM leads")->fetchAll(PDO::FETCH_COLUMN));
        if (!isset($leadsCols['channel'])) {
            try { $db->exec("ALTER TABLE leads ADD COLUMN channel VARCHAR(40) DEFAULT NULL"); } catch (Throwable $e) {}
        }
        if (!isset($leadsCols['phone_normalized'])) {
            try { $db->exec("ALTER TABLE leads ADD COLUMN phone_normalized VARCHAR(15) DEFAULT NULL"); } catch (Throwable $e) {}
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

        // ckampus-dasboard's admin has its own "Lead Engine" (shared/includes/
        // lead-ingest.php) that every OTHER lead source there goes through -
        // a `channel` tag (so it shows up on admin's Lead Channels/Activity
        // pages instead of being invisible there), phone-based dedup (so the
        // same person submitting twice doesn't create two lead rows), and a
        // lead_intake_log audit trail. This app can't call that PHP directly
        // (separate codebase/deploy - see this file's own header comment),
        // but it's the same database, so it can write directly to the same
        // tables that engine itself writes to and get the same behavior.
        // Defensive parity only (see the partners/leads ensure-blocks
        // above) - in production these already exist, created by that admin.
        try {
            $db->query("SELECT 1 FROM lead_intake_channels LIMIT 1");
        } catch (Throwable $e) {
            $db->exec("CREATE TABLE IF NOT EXISTS lead_intake_channels (
                id                INT AUTO_INCREMENT PRIMARY KEY,
                channel_key       VARCHAR(40) NOT NULL UNIQUE,
                label             VARCHAR(80) NOT NULL,
                webhook_secret    VARCHAR(64) NOT NULL,
                is_active         TINYINT(1) NOT NULL DEFAULT 1,
                last_received_at  DATETIME DEFAULT NULL,
                total_received    INT NOT NULL DEFAULT 0,
                created_at        DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at        DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        }
        try {
            $db->query("SELECT 1 FROM lead_intake_log LIMIT 1");
        } catch (Throwable $e) {
            $db->exec("CREATE TABLE IF NOT EXISTS lead_intake_log (
                id             INT AUTO_INCREMENT PRIMARY KEY,
                channel_key    VARCHAR(40) NOT NULL,
                ip_address     VARCHAR(45) DEFAULT NULL,
                success        TINYINT(1) NOT NULL DEFAULT 0,
                error_message  VARCHAR(255) DEFAULT NULL,
                lead_id        INT DEFAULT NULL,
                created_at     DATETIME DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_channel_time (channel_key, created_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        }
        $chStmt = $db->prepare("SELECT 1 FROM lead_intake_channels WHERE channel_key = 'online_portal'");
        $chStmt->execute();
        if (!$chStmt->fetch()) {
            // Codex P2: two concurrent first-ever requests can both pass the
            // SELECT above before either INSERTs - channel_key is UNIQUE, so
            // the losing request's INSERT throws. Caught locally (the row
            // the winner just created is exactly what this request wanted
            // anyway) so that request doesn't abort out to the outer catch
            // and skip mirroring its own lead into `leads` entirely.
            try {
                $db->prepare("INSERT INTO lead_intake_channels (channel_key, label, webhook_secret) VALUES (?, ?, ?)")
                    ->execute(['online_portal', 'Online Portal (online.collegekampus.com)', bin2hex(random_bytes(32))]);
            } catch (Throwable $e) {}
        }

        $noteParts = [];
        if (!empty($lead['message']))       $noteParts[] = $lead['message'];
        if (!empty($lead['qualification'])) $noteParts[] = 'Qualification: ' . $lead['qualification'];
        if (!empty($lead['utm_source']) || !empty($lead['utm_campaign'])) {
            $noteParts[] = 'Campaign: ' . trim(($lead['utm_source'] ?? '') . ' / ' . ($lead['utm_medium'] ?? '') . ' / ' . ($lead['utm_campaign'] ?? ''), ' /');
        }
        if (!empty($lead['page_url'])) $noteParts[] = 'Page: ' . $lead['page_url'];
        $noteText = $noteParts ? implode("\n", $noteParts) : null;

        // Same identity rule as the admin's own Lead Engine
        // (leadIngestNormalizePhone()): strip everything but digits, drop a
        // leading country code, and only a plausible 10-digit Indian mobile
        // counts - mirrored here rather than shared, same cross-app
        // constraint as the schema ensure-blocks above.
        $digits = preg_replace('/\D/', '', (string) ($lead['phone'] ?? ''));
        if (strlen($digits) > 10 && substr($digits, 0, 2) === '91') $digits = substr($digits, 2);
        elseif (strlen($digits) > 10 && substr($digits, 0, 4) === '0091') $digits = substr($digits, 4);
        if (strlen($digits) === 11 && $digits[0] === '0') $digits = substr($digits, 1);
        $phoneNormalized = (strlen($digits) === 10 && preg_match('/^[6-9]\d{9}$/', $digits)) ? $digits : null;

        // Codex P2: the SELECT then conditional INSERT below isn't atomic -
        // two concurrent submissions with the same phone number (a double-
        // click, or a retried request) can both pass the SELECT before
        // either INSERTs, creating two lead rows instead of one lead plus a
        // touchpoint. phone_normalized is only a non-unique index (matching
        // the admin's own `leads` table, which this doesn't own and won't
        // add a UNIQUE constraint to from this separate repo), so closing
        // this with a DB-level constraint isn't this PR's call to make -
        // a session-scoped advisory lock on the phone number serializes
        // concurrent requests for the SAME number through this whole
        // check-then-insert sequence instead, without touching that table's
        // schema. Self-releases if the connection drops, and the 5s wait
        // comfortably covers this fast query+insert - the normal case is an
        // uncontended lock acquired instantly.
        $lockName = 'online_portal_lead_phone_' . ($phoneNormalized ?: uniqid('', true));
        $db->query("SELECT GET_LOCK(" . $db->quote($lockName) . ", 5)");
        // A missed/timed-out lock (heavy contention, or a crashed worker
        // that never released one) falls through to the old racy behavior
        // rather than failing the submission outright - a rare duplicate
        // lead is a far smaller problem than losing a real one.
        try {
            $existingLeadId = null;
            if ($phoneNormalized) {
                $dupStmt = $db->prepare("SELECT id FROM leads WHERE phone_normalized = ? ORDER BY created_at DESC LIMIT 1");
                $dupStmt->execute([$phoneNormalized]);
                $existingLeadId = $dupStmt->fetchColumn() ?: null;
            }

            if ($existingLeadId) {
                // Same person already has a lead row (from this portal or
                // any other channel) - a new touchpoint note instead of a
                // duplicate row, same as every other channel's
                // repeat-submission path.
                $touchpoint = 'New touchpoint via online_portal (' . ($lead['source'] ?: 'website') . ') - same person, existing lead.';
                if ($noteText) $touchpoint .= ' | ' . $noteText;
                try {
                    $db->prepare("INSERT INTO lead_notes (lead_id, author_type, author_id, note_type, content) VALUES (?, 'admin', 0, 'note', ?)")
                        ->execute([$existingLeadId, mb_substr($touchpoint, 0, 500)]);
                } catch (Throwable $e) { /* lead_notes missing - dedup itself still worked, just no note trail */ }
                $leadIdForLog = $existingLeadId;
            } else {
                $ins = $db->prepare(
                    "INSERT INTO leads (partner_id, student_name, student_email, student_phone, phone_normalized, course_interest, college_interest, college_id, state, source, channel, notes)
                     VALUES (?,?,?,?,?,?,?,?,?,?,?,?)"
                );
                $ins->execute([
                    $partnerId,
                    $lead['name'],
                    $lead['email'] ?: null,
                    $lead['phone'],
                    $phoneNormalized,
                    $lead['course'] ?: null,
                    $lead['college_name'] ?: null,
                    $lead['college_id'] ?: null,
                    $lead['state'] ?: null,
                    'online-portal:' . ($lead['source'] ?: 'website'),
                    'online_portal',
                    $noteText,
                ]);
                $leadIdForLog = (int) $db->lastInsertId();
            }
        } finally {
            $db->query("SELECT RELEASE_LOCK(" . $db->quote($lockName) . ")");
        }

        // Same per-attempt audit trail every other channel's own webhook
        // writes (admin's Lead Channels -> Activity view reads this) - this
        // app can't call leadIngestBumpChannelStats()/ckIngestLead() directly
        // (separate codebase), so writes the same two tables it already
        // bumps for directly instead.
        try {
            $db->prepare("INSERT INTO lead_intake_log (channel_key, ip_address, success, lead_id) VALUES ('online_portal', ?, 1, ?)")
                ->execute([$_SERVER['REMOTE_ADDR'] ?? null, $leadIdForLog]);
            $db->prepare("UPDATE lead_intake_channels SET last_received_at = NOW(), total_received = total_received + 1 WHERE channel_key = 'online_portal'")->execute();
        } catch (Throwable $e) { /* Activity visibility is best-effort - the lead itself is already saved either way */ }
    } catch (Throwable $e) {
        error_log('pushLeadToSharedCrm failed: ' . $e->getMessage());
        try {
            $db->prepare("INSERT INTO lead_intake_log (channel_key, ip_address, success, error_message) VALUES ('online_portal', ?, 0, ?)")
                ->execute([$_SERVER['REMOTE_ADDR'] ?? null, mb_substr($e->getMessage(), 0, 255)]);
        } catch (Throwable $e2) {}
    }
}
