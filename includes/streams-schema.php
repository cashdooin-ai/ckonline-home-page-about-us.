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
 *
 * college_streams links by real college ID, resolved via
 * collegesEnsureSeed()'s slug map - an earlier version of this file linked
 * against the hardcoded IDs 10001-10050 from database/seed_online_colleges.sql,
 * which turned out to already be occupied by unrelated bulk-imported
 * colleges (see includes/colleges-seed.php). This also cleans up those
 * stale mislinked rows on first run.
 */

require_once __DIR__ . '/colleges-seed.php';

function streamsEnsureSchema(PDO $db): void
{
    static $checked = false;
    if ($checked) return;
    $checked = true;

    // Everything below is best-effort self-healing, not a hard dependency of
    // the page/API that calls this. A previous version let any failure here
    // (schema mismatch, a bad row mid-seed, a transient DB error) propagate
    // straight out of this function - which, since callers invoke it before
    // running their real query, silently killed the entire request (e.g.
    // api/colleges.php's catch-all turned any such error into "0 colleges
    // found" for every course-filtered search). Wrapping the whole body means
    // a problem here can degrade the stream filter, never take the page down.
    try {
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

        // One-time cleanup: an earlier version of this seeder linked streams
        // against the fake IDs 10001-10050 before discovering those were
        // already occupied by unrelated regular colleges - delete whatever
        // it wrongly attached to them.
        try {
            $db->exec("DELETE FROM college_streams WHERE college_id BETWEEN 10001 AND 10050");
        } catch (Throwable $e) {}

        $slugToId = collegesEnsureSeed($db);
        streamsSeed($db, $slugToId);
    } catch (Throwable $e) {
        error_log('streamsEnsureSchema failed: ' . $e->getMessage());
    }
}

/**
 * Seeds the 32 real programme/stream names and links them to the 50 real
 * online/distance colleges, resolved by slug via $slugToId (from
 * collegesEnsureSeed()). Mirrors database/seed_streams_final.sql's data
 * exactly, just executed via PHP instead of a manual phpMyAdmin run so it
 * can't be skipped. Idempotent: INSERT IGNORE + only runs the college link
 * pass while those specific colleges don't already have their links.
 */
function streamsSeed(PDO $db, array $slugToId): void
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

    if (!$slugToId) return;

    // slug => [stream names], from database/seed_streams_final.sql
    // (originally keyed by the fake IDs 10001-10050 - see collegesEnsureSeed())
    $collegeStreams = [
            'ignou' => ['BA', 'MA', 'B.Com', 'M.Com', 'MBA', 'MCA', 'BCA', 'B.Sc', 'M.Sc', 'BTS (Tourism)'],
            'braou' => ['BA', 'B.Com', 'MBA', 'M.Com', 'M.Sc'],
            'ycmou' => ['BA', 'B.Com', 'MBA', 'MCA', 'M.Com'],
            'ksou' => ['BA', 'B.Com', 'MBA', 'M.Com'],
            'tnou' => ['BA', 'B.Com', 'MBA', 'MCA'],
            'nsou' => ['BA', 'B.Com', 'MBA', 'M.Sc'],
            'vmou' => ['BA', 'B.Com', 'MBA', 'M.Com'],
            'nalanda-open-university' => ['BA', 'B.Com', 'MBA', 'M.Com'],
            'mp-bhoj-open-university' => ['BA', 'B.Com', 'MBA', 'M.Com'],
            'hpou' => ['BA', 'B.Com', 'MBA', 'M.Com'],
            'annamalai-university-dde' => ['BA', 'B.Com', 'MBA', 'MCA', 'M.Com', 'M.Sc'],
            'mku-ide' => ['BA', 'B.Com', 'MBA', 'MCA', 'M.Com'],
            'alagappa-university-dde' => ['BA', 'B.Com', 'MBA', 'MCA'],
            'mumbai-university-idol' => ['BA', 'B.Com', 'MBA', 'MCA', 'M.Com', 'LLB'],
            'osmania-dde' => ['BA', 'B.Com', 'MBA', 'MCA', 'M.Com'],
            'punjabi-university-distance' => ['BA', 'B.Com', 'MBA', 'M.Com'],
            'kurukshetra-university-distance' => ['BA', 'B.Com', 'MBA', 'M.Com'],
            'uniraj-distance' => ['BA', 'B.Com', 'MBA', 'M.Com'],
            'andhra-university-duck' => ['BA', 'B.Com', 'MBA', 'MCA', 'M.Com'],
            'bangalore-university-distance' => ['BA', 'B.Com', 'MBA', 'MCA'],
            'nmims-online' => ['MBA', 'BBA', 'B.Com', 'M.Com', 'MCA', 'BCA', 'B.Sc (IT)'],
            'amity-university-online' => ['MBA', 'BBA', 'MCA', 'BCA', 'M.Com', 'B.Com', 'MA (Psychology)', 'M.Sc (Data Science)'],
            'manipal-university-online' => ['MBA', 'MCA', 'BBA', 'BCA', 'B.Com', 'M.Com', 'M.Sc (Data Science)', 'MA'],
            'lpu-online' => ['MBA', 'BBA', 'MCA', 'BCA', 'B.Com', 'M.Com', 'B.Sc (CS)', 'M.Sc (CS)', 'BA', 'MA'],
            'chandigarh-university-online' => ['MBA', 'BBA', 'MCA', 'BCA', 'B.Com', 'M.Com', 'M.Sc (Data Science)', 'LLB'],
            'jain-university-online' => ['MBA', 'BBA', 'MCA', 'BCA', 'B.Com', 'M.Sc (Data Science)'],
            'scdl' => ['PGDBA', 'PGDHRM', 'PGDIT', 'PGDIM', 'PG Diploma (Marketing)'],
            'bits-pilani-wilp' => ['M.Tech', 'MBA', 'M.Sc (CS)', 'M.Sc (Biological Sciences)'],
            'upes-online' => ['MBA', 'BBA', 'MCA', 'LLB', 'M.Sc (Data Science)'],
            'dy-patil-online' => ['MBA', 'MCA', 'BBA', 'BCA'],
            'srm-university-online' => ['MBA', 'MCA', 'BBA', 'BCA', 'B.Com', 'M.Sc (Data Science)'],
            'vit-online' => ['MBA', 'MCA', 'M.Tech', 'B.Tech'],
            'sharda-university-online' => ['MBA', 'MCA', 'BBA', 'BCA', 'B.Com'],
            'gla-university-online' => ['MBA', 'MCA', 'BBA', 'BCA', 'B.Com'],
            'graphic-era-university-online' => ['MBA', 'MCA', 'BBA', 'BCA'],
            'amrita-university-online' => ['MBA', 'MCA', 'M.Sc (Data Science)', 'M.Sc (AI)'],
            'alliance-university-online' => ['MBA', 'MCA', 'BBA', 'BCA'],
            'vignans-university-online' => ['MBA', 'MCA', 'BBA', 'BCA'],
            'parul-university-online' => ['MBA', 'MCA', 'BBA', 'BCA', 'B.Com'],
            'mit-wpu-online' => ['MBA', 'MCA', 'BCA', 'B.Sc (CS)'],
            'symbiosis-online' => ['MBA', 'BBA', 'M.Sc (Data Science)', 'PGDM'],
            'hindustan-online' => ['MBA', 'MCA', 'BBA', 'BCA'],
            'shoolini-university-online' => ['MBA', 'BBA', 'M.Sc (Pharmaceutical Chemistry)'],
            'centurion-university-online' => ['MBA', 'MCA', 'BBA', 'BCA'],
            'rv-university-online' => ['MBA', 'BBA', 'M.Des', 'B.Des'],
            'presidency-university-online' => ['MBA', 'MCA', 'BBA', 'BCA', 'B.Com'],
            'saveetha-university-online' => ['MBA', 'MHA', 'BBA'],
            'sikkim-manipal-online' => ['MBA', 'MCA', 'BBA', 'BCA', 'B.Com', 'M.Com'],
            'svsu-distance' => ['BA', 'B.Com', 'MBA', 'MCA'],
            'igdtuw-online' => ['B.Tech', 'M.Tech', 'MBA', 'MCA', 'BCA', 'B.Sc (CS)'],
    ];

    $realIds = array_values(array_intersect_key($slugToId, $collegeStreams));
    if (!$realIds) return;

    try {
        // 200 is a safety margin below the full 248 expected links, not an
        // exact match - a bare ">0" check would wrongly treat a small
        // pre-existing partial seed as "already done" and skip backfilling
        // the rest forever. INSERT IGNORE below is safe to re-run.
        $ph   = implode(',', array_fill(0, count($realIds), '?'));
        $stmt = $db->prepare("SELECT COUNT(*) FROM college_streams WHERE college_id IN ($ph)");
        $stmt->execute($realIds);
        $linked = (int) $stmt->fetchColumn();
        if ($linked >= 200) return;
    } catch (Throwable $e) {
        return;
    }

    $streamIds = [];
    foreach ($db->query("SELECT id, name FROM streams")->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $streamIds[$row['name']] = (int) $row['id'];
    }

    $link = $db->prepare("INSERT IGNORE INTO college_streams (college_id, stream_id) VALUES (?, ?)");
    foreach ($collegeStreams as $slug => $names) {
        if (!isset($slugToId[$slug])) continue;
        $collegeId = $slugToId[$slug];
        foreach ($names as $name) {
            if (!isset($streamIds[$name])) continue;
            try {
                $link->execute([$collegeId, $streamIds[$name]]);
            } catch (Throwable $e) {
                // Don't let one bad row (e.g. a stray FK issue) abort the
                // rest of the 248-link backfill.
                continue;
            }
        }
    }
}
