<?php
/**
 * Self-migrating schema for the `streams` / `college_streams` tables that
 * power the course/stream filter on colleges.php + api/colleges.php
 * ("course=MBA" etc). These tables were previously only ever created by
 * manually running database/seed_streams_final.sql in phpMyAdmin - if that
 * step was never run (or wasn't run on this environment), any course/stream
 * filter silently found nothing, and api/colleges.php's course-matching
 * fell back to matching the query against the *college's own name*, which
 * almost never matches (e.g. "MBA" against "Manipal University Jaipur") -
 * producing the "0 colleges found" bug for a filter that should have real
 * results. Same self-migrating CREATE TABLE IF NOT EXISTS idiom used
 * throughout the shared CollegeKampus codebase, so this fixes itself on
 * first request rather than depending on a manual SQL step ever having run.
 */

function streamsEnsureSchema(PDO $db): void
{
    static $checked = false;
    if ($checked) return;
    $checked = true;

    try {
        $db->query("SELECT 1 FROM streams LIMIT 1");
    } catch (Throwable $e) {
        $db->exec("CREATE TABLE IF NOT EXISTS streams (
            id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            name           VARCHAR(100) NOT NULL,
            slug           VARCHAR(120) NOT NULL,
            icon           VARCHAR(10) DEFAULT NULL,
            display_order  INT DEFAULT 0,
            is_active      TINYINT(1) NOT NULL DEFAULT 1,
            UNIQUE KEY uq_streams_name (name),
            UNIQUE KEY uq_streams_slug (slug)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    }

    try {
        $db->query("SELECT 1 FROM college_streams LIMIT 1");
    } catch (Throwable $e) {
        $db->exec("CREATE TABLE IF NOT EXISTS college_streams (
            id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            college_id  INT UNSIGNED NOT NULL,
            stream_id   INT UNSIGNED NOT NULL,
            UNIQUE KEY uq_college_stream (college_id, stream_id),
            INDEX idx_college (college_id),
            INDEX idx_stream (stream_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    }

    streamsSeed($db);
}

/**
 * Seeds the 32 real programme/stream names and links them to the 50
 * seeded online/distance colleges (IDs 10001-10050, see
 * database/seed_online_colleges.sql). Mirrors database/seed_streams_final.sql
 * exactly, just executed via PHP instead of a manual phpMyAdmin run so it
 * can't be skipped. Idempotent: INSERT IGNORE + only runs the college link
 * pass while college_streams is empty.
 */
function streamsSeed(PDO $db): void
{
    static $checked = false;
    if ($checked) return;
    $checked = true;

    $streams = [
        ['MBA', 'mba', "\u{1F4BC}", 1], ['BBA', 'bba', "\u{1F4CA}", 2],
        ['MCA', 'mca', "\u{1F4BB}", 3], ['BCA', 'bca', "\u{1F5A5}", 4],
        ['B.Com', 'bcom', "\u{1F4D2}", 5], ['M.Com', 'mcom', "\u{1F4C8}", 6],
        ['BA', 'ba', "\u{1F4DA}", 7], ['MA', 'ma', "\u{1F393}", 8],
        ['B.Sc', 'bsc', "\u{1F52C}", 9], ['M.Sc', 'msc', "\u{1F9EA}", 10],
        ['B.Tech', 'btech', "\u{2699}", 11], ['M.Tech', 'mtech', "\u{1F527}", 12],
        ['LLB', 'llb', "\u{2696}", 13], ['LLM', 'llm', "\u{1F3DB}", 14],
        ['PGDM', 'pgdm', "\u{1F4CB}", 15], ['PGDBA', 'pgdba', "\u{1F4CB}", 16],
        ['PGDHRM', 'pgdhrm', "\u{1F465}", 17], ['PGDIT', 'pgdit', "\u{1F4BE}", 18],
        ['PGDIM', 'pgdim', "\u{1F4E6}", 19], ['B.Sc (IT)', 'bsc-it', "\u{1F4A1}", 20],
        ['B.Sc (CS)', 'bsc-cs', "\u{1F5A5}", 21], ['M.Sc (CS)', 'msc-cs', "\u{1F4BB}", 22],
        ['M.Sc (Data Science)', 'msc-data-science', "\u{1F4CA}", 23], ['M.Sc (AI)', 'msc-ai', "\u{1F916}", 24],
        ['M.Sc (Biological Sciences)', 'msc-bio', "\u{1F9EC}", 25], ['M.Sc (Pharmaceutical Chemistry)', 'msc-pharma-chem', "\u{1F48A}", 26],
        ['MA (Psychology)', 'ma-psychology', "\u{1F9E0}", 27], ['MHA', 'mha', "\u{1F3E5}", 28],
        ['M.Des', 'mdes', "\u{1F3A8}", 29], ['B.Des', 'bdes', "\u{270F}", 30],
        ['BTS (Tourism)', 'bts-tourism', "\u{2708}", 31], ['PG Diploma (Marketing)', 'pg-diploma-marketing', "\u{1F4E3}", 32],
    ];
    $ins = $db->prepare("INSERT IGNORE INTO streams (name, slug, icon, display_order, is_active) VALUES (?, ?, ?, ?, 1)");
    foreach ($streams as $s) {
        $ins->execute($s);
    }

    try {
        // 200 is a safety margin below the full 248 expected links (50
        // colleges), not an exact match - a bare ">0" check here would wrongly
        // treat a small pre-existing partial seed (e.g. from an earlier,
        // incomplete manual run of seed_streams_final.sql) as "already done"
        // and skip backfilling the rest forever. INSERT IGNORE below is safe
        // to re-run - it can't create duplicates (uq_college_stream).
        $linked = (int) $db->query("SELECT COUNT(*) FROM college_streams WHERE college_id BETWEEN 10001 AND 10050")->fetchColumn();
        if ($linked >= 200) return;
    } catch (Throwable $e) {
        return;
    }

    // college_id => [stream names], from database/seed_streams_final.sql
    $collegeStreams = [
            10001 => ['BA', 'MA', 'B.Com', 'M.Com', 'MBA', 'MCA', 'BCA', 'B.Sc', 'M.Sc', 'BTS (Tourism)'],
            10002 => ['BA', 'B.Com', 'MBA', 'M.Com', 'M.Sc'],
            10003 => ['BA', 'B.Com', 'MBA', 'MCA', 'M.Com'],
            10004 => ['BA', 'B.Com', 'MBA', 'M.Com'],
            10005 => ['BA', 'B.Com', 'MBA', 'MCA'],
            10006 => ['BA', 'B.Com', 'MBA', 'M.Sc'],
            10007 => ['BA', 'B.Com', 'MBA', 'M.Com'],
            10008 => ['BA', 'B.Com', 'MBA', 'M.Com'],
            10009 => ['BA', 'B.Com', 'MBA', 'M.Com'],
            10010 => ['BA', 'B.Com', 'MBA', 'M.Com'],
            10011 => ['BA', 'B.Com', 'MBA', 'MCA', 'M.Com', 'M.Sc'],
            10012 => ['BA', 'B.Com', 'MBA', 'MCA', 'M.Com'],
            10013 => ['BA', 'B.Com', 'MBA', 'MCA'],
            10014 => ['BA', 'B.Com', 'MBA', 'MCA', 'M.Com', 'LLB'],
            10015 => ['BA', 'B.Com', 'MBA', 'MCA', 'M.Com'],
            10016 => ['BA', 'B.Com', 'MBA', 'M.Com'],
            10017 => ['BA', 'B.Com', 'MBA', 'M.Com'],
            10018 => ['BA', 'B.Com', 'MBA', 'M.Com'],
            10019 => ['BA', 'B.Com', 'MBA', 'MCA', 'M.Com'],
            10020 => ['BA', 'B.Com', 'MBA', 'MCA'],
            10021 => ['MBA', 'BBA', 'B.Com', 'M.Com', 'MCA', 'BCA', 'B.Sc (IT)'],
            10022 => ['MBA', 'BBA', 'MCA', 'BCA', 'M.Com', 'B.Com', 'MA (Psychology)', 'M.Sc (Data Science)'],
            10023 => ['MBA', 'MCA', 'BBA', 'BCA', 'B.Com', 'M.Com', 'M.Sc (Data Science)', 'MA'],
            10024 => ['MBA', 'BBA', 'MCA', 'BCA', 'B.Com', 'M.Com', 'B.Sc (CS)', 'M.Sc (CS)', 'BA', 'MA'],
            10025 => ['MBA', 'BBA', 'MCA', 'BCA', 'B.Com', 'M.Com', 'M.Sc (Data Science)', 'LLB'],
            10026 => ['MBA', 'BBA', 'MCA', 'BCA', 'B.Com', 'M.Sc (Data Science)'],
            10027 => ['PGDBA', 'PGDHRM', 'PGDIT', 'PGDIM', 'PG Diploma (Marketing)'],
            10028 => ['M.Tech', 'MBA', 'M.Sc (CS)', 'M.Sc (Biological Sciences)'],
            10029 => ['MBA', 'BBA', 'MCA', 'LLB', 'M.Sc (Data Science)'],
            10030 => ['MBA', 'MCA', 'BBA', 'BCA'],
            10031 => ['MBA', 'MCA', 'BBA', 'BCA', 'B.Com', 'M.Sc (Data Science)'],
            10032 => ['MBA', 'MCA', 'M.Tech', 'B.Tech'],
            10033 => ['MBA', 'MCA', 'BBA', 'BCA', 'B.Com'],
            10034 => ['MBA', 'MCA', 'BBA', 'BCA', 'B.Com'],
            10035 => ['MBA', 'MCA', 'BBA', 'BCA'],
            10036 => ['MBA', 'MCA', 'M.Sc (Data Science)', 'M.Sc (AI)'],
            10037 => ['MBA', 'MCA', 'BBA', 'BCA'],
            10038 => ['MBA', 'MCA', 'BBA', 'BCA'],
            10039 => ['MBA', 'MCA', 'BBA', 'BCA', 'B.Com'],
            10040 => ['MBA', 'MCA', 'BCA', 'B.Sc (CS)'],
            10041 => ['MBA', 'BBA', 'M.Sc (Data Science)', 'PGDM'],
            10042 => ['MBA', 'MCA', 'BBA', 'BCA'],
            10043 => ['MBA', 'BBA', 'M.Sc (Pharmaceutical Chemistry)'],
            10044 => ['MBA', 'MCA', 'BBA', 'BCA'],
            10045 => ['MBA', 'BBA', 'M.Des', 'B.Des'],
            10046 => ['MBA', 'MCA', 'BBA', 'BCA', 'B.Com'],
            10047 => ['MBA', 'MHA', 'BBA'],
            10048 => ['MBA', 'MCA', 'BBA', 'BCA', 'B.Com', 'M.Com'],
            10049 => ['BA', 'B.Com', 'MBA', 'MCA'],
            10050 => ['B.Tech', 'M.Tech', 'MBA', 'MCA', 'BCA', 'B.Sc (CS)'],
    ];

    $streamIds = [];
    foreach ($db->query("SELECT id, name FROM streams")->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $streamIds[$row['name']] = (int) $row['id'];
    }

    $link = $db->prepare("INSERT IGNORE INTO college_streams (college_id, stream_id) VALUES (?, ?)");
    foreach ($collegeStreams as $collegeId => $names) {
        foreach ($names as $name) {
            if (!isset($streamIds[$name])) continue;
            $link->execute([$collegeId, $streamIds[$name]]);
        }
    }
}
