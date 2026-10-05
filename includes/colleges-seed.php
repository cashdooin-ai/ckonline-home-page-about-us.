<?php
/**
 * Self-migrating seed for the 50 real online/distance colleges that power
 * this whole portal. database/seed_online_colleges.sql was written to be
 * run manually in phpMyAdmin, assuming IDs 10001-10050 were free - they
 * were not. The real `colleges` table (shared with ckampus-dasboard) had
 * already grown to ~49,800 bulk-imported rows by the time this ran, so
 * 10001-10050 were already occupied by unrelated regular colleges. Every
 * `INSERT IGNORE ... VALUES (10001, ...)` silently no-opped on the
 * duplicate primary key - is_online stayed 0 on all ~49,800 rows, and the
 * college_streams links this file's earlier version created under those
 * IDs were attached to the WRONG (regular) colleges.
 *
 * Fixed by matching/inserting on `slug` (collision-safe - a real product
 * page identifier, not an arbitrary reserved block) and never specifying
 * `id` on insert; the real auto-assigned ID is looked up afterward. Also
 * cleans up the mislinked college_streams rows from the old ID-based
 * version, and re-links using the real resolved IDs.
 *
 * The INSERT column list is built dynamically from SHOW COLUMNS - this
 * repo's own database/schema.sql is a stale stub that doesn't match the
 * real production `colleges` table (shared with the ckampus-dasboard repo,
 * which api/colleges.php's own dynamic column checks already assume has
 * columns like nirf_rank/rating/college_type/is_online etc.) - so only
 * columns that actually exist get written to, never an "unknown column"
 * fatal regardless of exactly which schema is live.
 */

/**
 * Returns [slug => realCollegeId] for all 50 seed colleges, inserting any
 * that are missing. Safe to call every request - each row is looked up by
 * slug first, only inserted if genuinely absent.
 */
function collegesEnsureSeed(PDO $db): array
{
    static $cached = null;
    if ($cached !== null) return $cached;

    $slugToId = [];
    try {
        $has = array_flip($db->query("SHOW COLUMNS FROM colleges")->fetchAll(PDO::FETCH_COLUMN));

        if (!isset($has['is_online'])) {
            $db->exec("ALTER TABLE colleges ADD COLUMN is_online TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Is this an online/distance college'");
            $has['is_online'] = true;
        }
        if (!isset($has['online_mode'])) {
            $db->exec("ALTER TABLE colleges ADD COLUMN online_mode VARCHAR(50) DEFAULT NULL COMMENT 'online / distance / hybrid'");
            $has['online_mode'] = true;
        }
        if (!isset($has['ugc_approved'])) {
            $db->exec("ALTER TABLE colleges ADD COLUMN ugc_approved TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'UGC DEB approved for online education'");
            $has['ugc_approved'] = true;
        }

        // legacy_id (unused for insert, kept only as a stable array key),
        // name, short_name, slug, description, college_type, institution_type,
        // established_year, accreditation, naac_grade, city, state, website,
        // min_fees, max_fees, is_featured, is_online, online_mode, ugc_approved,
        // avg_package, ownership, gender_accepted, rating
        $colleges = [
            [10001, 'Indira Gandhi National Open University', 'IGNOU', 'ignou', 'India\'s largest open university offering 200+ programmes in online and distance mode. UGC DEB approved. Enrolment of 3+ million students.', 'government', 'university', 1985, 'UGC-DEB', 'A', 'New Delhi', 'Delhi', 'https://ignou.ac.in', 10000, 50000, 1, 1, 'distance', 1, 350000, 'central_govt', 'co-ed', 4.2],
            [10002, 'Dr. B.R. Ambedkar Open University', 'BRAOU', 'braou', 'Andhra Pradesh\'s premier open university offering UG, PG and diploma programmes in distance mode. UGC approved.', 'government', 'university', 1982, 'UGC-DEB', 'B+', 'Hyderabad', 'Telangana', 'https://braou.ac.in', 8000, 40000, 0, 1, 'distance', 1, 280000, 'state_govt', 'co-ed', 3.8],
            [10003, 'YCMOU – Yashwantrao Chavan Maharashtra Open University', 'YCMOU', 'ycmou', 'Maharashtra state open university offering 100+ programmes in distance and online mode.', 'government', 'university', 1989, 'UGC-DEB', 'A', 'Nashik', 'Maharashtra', 'https://ycmou.ac.in', 9000, 45000, 0, 1, 'distance', 1, 300000, 'state_govt', 'co-ed', 3.7],
            [10004, 'Karnataka State Open University', 'KSOU', 'ksou', 'Karnataka\'s state open university with programmes in arts, science, commerce and management.', 'government', 'university', 1996, 'UGC-DEB', 'B+', 'Mysuru', 'Karnataka', 'https://ksoumysuru.ac.in', 8000, 38000, 0, 1, 'distance', 1, 260000, 'state_govt', 'co-ed', 3.6],
            [10005, 'Tamil Nadu Open University', 'TNOU', 'tnou', 'State open university offering distance education programmes across all disciplines.', 'government', 'university', 2002, 'UGC-DEB', 'B', 'Chennai', 'Tamil Nadu', 'https://tnou.ac.in', 7500, 35000, 0, 1, 'distance', 1, 240000, 'state_govt', 'co-ed', 3.5],
            [10006, 'Netaji Subhas Open University', 'NSOU', 'nsou', 'West Bengal state open university, one of the largest distance education providers in eastern India.', 'government', 'university', 1997, 'UGC-DEB', 'B+', 'Kolkata', 'West Bengal', 'https://wbnsou.ac.in', 7000, 35000, 0, 1, 'distance', 1, 250000, 'state_govt', 'co-ed', 3.6],
            [10007, 'Vardhaman Mahaveer Open University', 'VMOU', 'vmou', 'Rajasthan state open university offering 100+ courses in arts, science, commerce and professional streams.', 'government', 'university', 1987, 'UGC-DEB', 'B+', 'Kota', 'Rajasthan', 'https://vmou.ac.in', 7000, 32000, 0, 1, 'distance', 1, 230000, 'state_govt', 'co-ed', 3.5],
            [10008, 'Nalanda Open University', 'NOU', 'nalanda-open-university', 'Bihar state open university offering UG and PG distance education programmes.', 'government', 'university', 1987, 'UGC-DEB', 'B', 'Patna', 'Bihar', 'https://nalandaopenuniversity.com', 6000, 28000, 0, 1, 'distance', 1, 210000, 'state_govt', 'co-ed', 3.4],
            [10009, 'MP Bhoj Open University', 'MPBOU', 'mp-bhoj-open-university', 'Madhya Pradesh state open university offering distance programmes in management, arts and science.', 'government', 'university', 1991, 'UGC-DEB', 'B', 'Bhopal', 'Madhya Pradesh', 'https://bhojvirtualuniversity.com', 6500, 30000, 0, 1, 'distance', 1, 220000, 'state_govt', 'co-ed', 3.4],
            [10010, 'Himachal Pradesh Open University', 'HPOU', 'hpou', 'Open university for Himachal Pradesh offering distance education across multiple disciplines.', 'government', 'university', 2009, 'UGC-DEB', 'B', 'Shimla', 'Himachal Pradesh', 'https://hpou.ac.in', 6000, 28000, 0, 1, 'distance', 1, 200000, 'state_govt', 'co-ed', 3.3],
            [10011, 'Annamalai University (DDE)', 'AU-DDE', 'annamalai-university-dde', 'Directorate of Distance Education — one of India\'s oldest and largest distance education providers with 60+ programmes.', 'government', 'university', 1929, 'UGC-DEB', 'B+', 'Chidambaram', 'Tamil Nadu', 'https://annamalaiuniversity.ac.in', 8000, 42000, 1, 1, 'distance', 1, 280000, 'state_govt', 'co-ed', 3.8],
            [10012, 'Madurai Kamaraj University (IDE)', 'MKU', 'mku-ide', 'Institute of Distance Education offering UG, PG and diploma programmes across all streams.', 'government', 'university', 1966, 'UGC-DEB', 'A', 'Madurai', 'Tamil Nadu', 'https://mkudde.org', 7500, 38000, 0, 1, 'distance', 1, 270000, 'state_govt', 'co-ed', 3.7],
            [10013, 'Alagappa University (DDE)', 'AU', 'alagappa-university-dde', 'Distance Education wing offering UG, PG, M.Phil and diploma programmes.', 'government', 'university', 1985, 'UGC-DEB', 'A', 'Karaikudi', 'Tamil Nadu', 'https://alagappauniversity.ac.in', 7000, 36000, 0, 1, 'distance', 1, 260000, 'state_govt', 'co-ed', 3.7],
            [10014, 'University of Mumbai (IDOL)', 'MU-IDOL', 'mumbai-university-idol', 'Institute of Distance and Open Learning offering UG and PG programmes.', 'government', 'university', 1857, 'UGC-DEB', 'A+', 'Mumbai', 'Maharashtra', 'https://idol.mu.ac.in', 9000, 45000, 1, 1, 'distance', 1, 350000, 'state_govt', 'co-ed', 4.0],
            [10015, 'Osmania University (DDE)', 'OU', 'osmania-dde', 'Centre for Distance Education offering UG, PG, diploma and certificate programmes.', 'government', 'university', 1918, 'UGC-DEB', 'A', 'Hyderabad', 'Telangana', 'https://osmania.ac.in', 8000, 38000, 0, 1, 'distance', 1, 270000, 'state_govt', 'co-ed', 3.8],
            [10016, 'Punjabi University (Distance)', 'PU', 'punjabi-university-distance', 'Directorate of Correspondence Courses offering management, arts and science programmes.', 'government', 'university', 1962, 'UGC-DEB', 'B+', 'Patiala', 'Punjab', 'https://punjabiuniversity.ac.in', 7500, 35000, 0, 1, 'distance', 1, 250000, 'state_govt', 'co-ed', 3.6],
            [10017, 'Kurukshetra University (Distance)', 'KU', 'kurukshetra-university-distance', 'Directorate of Distance Education offering UG and PG programmes in all disciplines.', 'government', 'university', 1956, 'UGC-DEB', 'A', 'Kurukshetra', 'Haryana', 'https://kuk.ac.in', 7000, 33000, 0, 1, 'distance', 1, 240000, 'state_govt', 'co-ed', 3.6],
            [10018, 'University of Rajasthan (Distance)', 'UNIRAJ', 'uniraj-distance', 'Department of Distance Education offering management, arts and science programmes.', 'government', 'university', 1947, 'UGC-DEB', 'B+', 'Jaipur', 'Rajasthan', 'https://uniraj.ac.in', 7000, 32000, 0, 1, 'distance', 1, 230000, 'state_govt', 'co-ed', 3.5],
            [10019, 'Andhra University (DUCK)', 'AU-DUCK', 'andhra-university-duck', 'Directorate of UGC-Distance, Continuing and Online Education offering 50+ programmes.', 'government', 'university', 1926, 'UGC-DEB', 'A+', 'Visakhapatnam', 'Andhra Pradesh', 'https://andhrauniversity.edu.in', 8000, 40000, 0, 1, 'distance', 1, 280000, 'state_govt', 'co-ed', 3.8],
            [10020, 'Bangalore University (Distance)', 'BU', 'bangalore-university-distance', 'Centre for PG Studies and Distance Education offering management and science programmes.', 'government', 'university', 1964, 'UGC-DEB', 'A', 'Bengaluru', 'Karnataka', 'https://bangaloreuniversity.ac.in', 8000, 38000, 0, 1, 'distance', 1, 270000, 'state_govt', 'co-ed', 3.7],
            [10021, 'NMIMS Global Access School for Continuing Education', 'NGASCE', 'nmims-online', 'UGC DEB approved online programmes in management, science and commerce. Known for industry-integrated curriculum.', 'deemed', 'university', 2004, 'UGC-DEB', 'A+', 'Mumbai', 'Maharashtra', 'https://ngasce.nmims.edu', 80000, 250000, 1, 1, 'online', 1, 650000, 'private', 'co-ed', 4.5],
            [10022, 'Amity University Online', 'Amity Online', 'amity-university-online', 'Top-ranked private university offering 30+ fully online UG and PG programmes with live classes.', 'private', 'university', 2005, 'UGC-DEB', 'A+', 'Noida', 'Uttar Pradesh', 'https://amityonline.com', 70000, 220000, 1, 1, 'online', 1, 600000, 'private', 'co-ed', 4.4],
            [10023, 'Manipal University Online', 'MUO', 'manipal-university-online', 'UGC DEB approved online programmes from Manipal Academy of Higher Education. 100% placement support.', 'deemed', 'university', 1993, 'UGC-DEB', 'A++', 'Manipal', 'Karnataka', 'https://onlinemanipal.com', 65000, 200000, 1, 1, 'online', 1, 620000, 'private', 'co-ed', 4.5],
            [10024, 'LPU Online – Lovely Professional University', 'LPU Online', 'lpu-online', 'India\'s largest private university offering 50+ online programmes with live interaction and placement support.', 'private', 'university', 2005, 'UGC-DEB', 'A+', 'Phagwara', 'Punjab', 'https://online.lpu.in', 50000, 180000, 1, 1, 'online', 1, 550000, 'private', 'co-ed', 4.3],
            [10025, 'Chandigarh University Online', 'CU Online', 'chandigarh-university-online', 'QS ranked university offering fully online programmes with strong corporate connections and placement record.', 'private', 'university', 2012, 'UGC-DEB', 'A+', 'Mohali', 'Punjab', 'https://online.cumail.in', 55000, 185000, 1, 1, 'online', 1, 580000, 'private', 'co-ed', 4.3],
            [10026, 'Jain Online – Jain (Deemed-to-be) University', 'Jain Online', 'jain-university-online', 'UGC approved online university with specialised management, IT and commerce programmes.', 'deemed', 'university', 2009, 'UGC-DEB', 'A+', 'Bengaluru', 'Karnataka', 'https://online.jainuniversity.ac.in', 60000, 190000, 1, 1, 'online', 1, 570000, 'private', 'co-ed', 4.3],
            [10027, 'Symbiosis Centre for Distance Learning', 'SCDL', 'scdl', 'India\'s premier distance management school offering PGDBA, PGDHRM and other management diplomas.', 'deemed', 'university', 2001, 'UGC-DEB', 'A', 'Pune', 'Maharashtra', 'https://www.scdl.net', 40000, 120000, 1, 1, 'distance', 1, 500000, 'private', 'co-ed', 4.2],
            [10028, 'BITS Pilani – Work Integrated Learning Programmes', 'BITS WILP', 'bits-pilani-wilp', 'Prestigious BITS Pilani offering M.Tech, MBA and MSc programmes for working professionals.', 'deemed', 'university', 1964, 'UGC-DEB', 'A+', 'Pilani', 'Rajasthan', 'https://www.bits-pilani.ac.in/wilp', 90000, 300000, 1, 1, 'online', 1, 900000, 'private', 'co-ed', 4.7],
            [10029, 'UPES Online', 'UPES', 'upes-online', 'University of Petroleum and Energy Studies offering online management, law and technology programmes.', 'private', 'university', 2003, 'UGC-DEB', 'A', 'Dehradun', 'Uttarakhand', 'https://online.upes.ac.in', 65000, 200000, 1, 1, 'online', 1, 560000, 'private', 'co-ed', 4.2],
            [10030, 'DY Patil Vidyapeeth Online', 'DYP Online', 'dy-patil-online', 'Deemed university offering online health sciences, management and technology programmes.', 'deemed', 'university', 2002, 'UGC-DEB', 'A', 'Pune', 'Maharashtra', 'https://dypatilvideoyapeeth.edu.in', 65000, 210000, 0, 1, 'online', 1, 520000, 'private', 'co-ed', 4.1],
            [10031, 'SRM University Online', 'SRM Online', 'srm-university-online', 'Top-ranked private university offering online UG and PG programmes with strong placement record.', 'private', 'university', 1985, 'UGC-DEB', 'A++', 'Chennai', 'Tamil Nadu', 'https://online.srmist.edu.in', 60000, 195000, 1, 1, 'online', 1, 590000, 'private', 'co-ed', 4.3],
            [10032, 'VIT Online – Vellore Institute of Technology', 'VIT Online', 'vit-online', 'NIRF top-ranked engineering university offering online MBA and MCA programmes.', 'private', 'university', 1984, 'UGC-DEB', 'A++', 'Vellore', 'Tamil Nadu', 'https://online.vit.ac.in', 70000, 200000, 1, 1, 'online', 1, 700000, 'private', 'co-ed', 4.4],
            [10033, 'Sharda University Online', 'Sharda Online', 'sharda-university-online', 'Greater Noida based private university offering online management and IT programmes.', 'private', 'university', 2009, 'UGC-DEB', 'A', 'Greater Noida', 'Uttar Pradesh', 'https://shardaonline.ac.in', 55000, 175000, 0, 1, 'online', 1, 480000, 'private', 'co-ed', 4.0],
            [10034, 'GLA University Online', 'GLA Online', 'gla-university-online', 'UGC approved online programmes in management, computing and commerce.', 'private', 'university', 2010, 'UGC-DEB', 'A+', 'Mathura', 'Uttar Pradesh', 'https://gla.ac.in/online', 50000, 165000, 0, 1, 'online', 1, 460000, 'private', 'co-ed', 4.0],
            [10035, 'Graphic Era University Online', 'GEU Online', 'graphic-era-university-online', 'Uttarakhand\'s leading private university offering online management and technology programmes.', 'private', 'university', 1993, 'UGC-DEB', 'A', 'Dehradun', 'Uttarakhand', 'https://online.graphicera.edu.in', 50000, 160000, 0, 1, 'online', 1, 450000, 'private', 'co-ed', 3.9],
            [10036, 'Amrita University Online', 'Amrita Online', 'amrita-university-online', 'Deemed university offering online MBA, MCA and M.Sc programmes with live faculty sessions.', 'deemed', 'university', 2003, 'UGC-DEB', 'A++', 'Coimbatore', 'Tamil Nadu', 'https://www.amrita.edu/online', 70000, 210000, 1, 1, 'online', 1, 620000, 'private', 'co-ed', 4.4],
            [10037, 'Alliance University Online', 'Alliance Online', 'alliance-university-online', 'Bengaluru based private university offering online MBA and MCA programmes.', 'private', 'university', 2010, 'UGC-DEB', 'A', 'Bengaluru', 'Karnataka', 'https://online.alliance.edu.in', 60000, 185000, 0, 1, 'online', 1, 490000, 'private', 'co-ed', 4.0],
            [10038, 'Vignan\'s University Online', 'Vignan Online', 'vignans-university-online', 'Andhra Pradesh university offering online management and technology programmes.', 'private', 'university', 2008, 'UGC-DEB', 'A', 'Guntur', 'Andhra Pradesh', 'https://online.vignanuniversity.ac.in', 45000, 150000, 0, 1, 'online', 1, 420000, 'private', 'co-ed', 3.8],
            [10039, 'Parul University Online', 'Parul Online', 'parul-university-online', 'Gujarat\'s leading private university offering online programmes in management, science and commerce.', 'private', 'university', 2015, 'UGC-DEB', 'A', 'Vadodara', 'Gujarat', 'https://online.paruluniversity.ac.in', 50000, 165000, 0, 1, 'online', 1, 440000, 'private', 'co-ed', 3.9],
            [10040, 'MIT World Peace University Online', 'MIT-WPU Online', 'mit-wpu-online', 'Pune based private university offering online MBA, MCA and B.Sc programmes.', 'private', 'university', 2017, 'UGC-DEB', 'A+', 'Pune', 'Maharashtra', 'https://online.mitwpu.edu.in', 60000, 190000, 0, 1, 'online', 1, 500000, 'private', 'co-ed', 4.1],
            [10041, 'Symbiosis School of Online and Digital Learning', 'SSODL', 'symbiosis-online', 'Symbiosis International University\'s fully online school offering MBA, BBA and other programmes.', 'deemed', 'university', 2019, 'UGC-DEB', 'A+', 'Pune', 'Maharashtra', 'https://ssodl.siu.edu.in', 75000, 220000, 1, 1, 'online', 1, 600000, 'private', 'co-ed', 4.3],
            [10042, 'Hindustan Online – Hindustan Institute of Technology and Science', 'HITS Online', 'hindustan-online', 'Chennai based deemed university offering online management and technology programmes.', 'deemed', 'university', 1985, 'UGC-DEB', 'A', 'Chennai', 'Tamil Nadu', 'https://online.hindustanuniv.ac.in', 55000, 175000, 0, 1, 'online', 1, 470000, 'private', 'co-ed', 4.0],
            [10043, 'Shoolini University Online', 'Shoolini Online', 'shoolini-university-online', 'Himachal Pradesh private university offering online management and pharmacy programmes.', 'private', 'university', 2009, 'UGC-DEB', 'A+', 'Solan', 'Himachal Pradesh', 'https://shooliniuniversity.com/online', 50000, 165000, 0, 1, 'online', 1, 450000, 'private', 'co-ed', 4.0],
            [10044, 'Centurion University Online', 'CUO', 'centurion-university-online', 'Odisha based private university offering online management, technology and vocational programmes.', 'private', 'university', 2010, 'UGC-DEB', 'A', 'Bhubaneswar', 'Odisha', 'https://online.cutm.ac.in', 45000, 155000, 0, 1, 'online', 1, 400000, 'private', 'co-ed', 3.8],
            [10045, 'RV University Online', 'RVU Online', 'rv-university-online', 'Bengaluru private university offering online management and design programmes.', 'private', 'university', 2021, 'UGC-DEB', 'A', 'Bengaluru', 'Karnataka', 'https://online.rvu.edu.in', 60000, 185000, 0, 1, 'online', 1, 480000, 'private', 'co-ed', 4.0],
            [10046, 'Presidency University Online', 'PU Online', 'presidency-university-online', 'Bengaluru private university offering UGC approved online management and IT programmes.', 'private', 'university', 2013, 'UGC-DEB', 'A', 'Bengaluru', 'Karnataka', 'https://online.presidencyuniversity.in', 55000, 175000, 0, 1, 'online', 1, 460000, 'private', 'co-ed', 3.9],
            [10047, 'Saveetha University Online', 'Saveetha Online', 'saveetha-university-online', 'Chennai deemed university offering online MBA and MHA programmes.', 'deemed', 'university', 2005, 'UGC-DEB', 'A', 'Chennai', 'Tamil Nadu', 'https://online.saveetha.ac.in', 60000, 180000, 0, 1, 'online', 1, 480000, 'private', 'co-ed', 3.9],
            [10048, 'Sikkim Manipal University (Online/Distance)', 'SMU-DE', 'sikkim-manipal-online', 'Pioneer in online and distance management education. NAAC accredited with 20+ years of experience.', 'private', 'university', 1995, 'UGC-DEB', 'B+', 'Gangtok', 'Sikkim', 'https://smude.edu.in', 45000, 160000, 1, 1, 'online', 1, 480000, 'private', 'co-ed', 4.0],
            [10049, 'Swami Vivekananda Subharti University (Distance)', 'SVSU', 'svsu-distance', 'Uttar Pradesh university offering distance UG and PG programmes in arts, commerce and management.', 'private', 'university', 2008, 'UGC-DEB', 'A', 'Meerut', 'Uttar Pradesh', 'https://subhartionline.com', 30000, 100000, 0, 1, 'distance', 1, 350000, 'private', 'co-ed', 3.7],
            [10050, 'Indira Gandhi Delhi Technical University for Women (Online)', 'IGDTUW', 'igdtuw-online', 'First technical university for women offering online technology and management programmes.', 'government', 'university', 1998, 'UGC-DEB', 'A', 'New Delhi', 'Delhi', 'https://igdtuw.ac.in', 35000, 120000, 0, 1, 'online', 1, 550000, 'state_govt', 'female', 4.1],
        ];

        // Map each seed field to a real column name, skipping any column
        // that doesn't actually exist on this database (e.g. this repo's
        // own database/schema.sql calls it `type`, not `college_type` -
        // handle both spellings; other optional columns like `website` or
        // `avg_package` just get skipped if genuinely absent). `id` is
        // deliberately never mapped/inserted - the real ID is auto-assigned
        // and resolved afterward by slug.
        $fieldMap = [
            'id' => null, 'name' => 'name', 'short_name' => 'short_name', 'slug' => 'slug',
            'description' => 'description',
            'college_type' => isset($has['college_type']) ? 'college_type' : (isset($has['type']) ? 'type' : null),
            'institution_type' => 'institution_type',
            'established_year' => 'established_year', 'accreditation' => 'accreditation',
            'naac_grade' => 'naac_grade', 'city' => 'city', 'state' => 'state',
            'website' => 'website', 'min_fees' => 'min_fees', 'max_fees' => 'max_fees',
            'is_featured' => 'is_featured', 'is_online' => 'is_online',
            'online_mode' => 'online_mode', 'ugc_approved' => 'ugc_approved',
            'avg_package' => 'avg_package', 'ownership' => 'ownership',
            'gender_accepted' => 'gender_accepted', 'rating' => 'rating',
        ];
        $seedKeys = array_keys($fieldMap);

        $cols = [];
        $idx  = [];
        foreach ($seedKeys as $i => $key) {
            $col = $fieldMap[$key];
            if ($col === null || !isset($has[$col])) continue;
            $cols[] = $col;
            $idx[]  = $i;
        }
        if (!$cols) return [];

        // Resolve existing rows by slug first - a single batch lookup, so
        // once seeding is complete this whole function costs one SELECT.
        $slugs = array_column($colleges, 3);
        $ph    = implode(',', array_fill(0, count($slugs), '?'));
        $sel   = $db->prepare("SELECT id, slug FROM colleges WHERE slug IN ($ph)");
        $sel->execute($slugs);
        foreach ($sel->fetchAll(PDO::FETCH_ASSOC) as $r) {
            $slugToId[$r['slug']] = (int) $r['id'];
        }

        $placeholders = implode(',', array_fill(0, count($cols), '?'));
        $colList      = implode(',', array_map(fn($c) => "`$c`", $cols));
        $stmt = $db->prepare("INSERT INTO colleges ($colList) VALUES ($placeholders)");

        // Some of these 50 (e.g. IGNOU) are well-known enough that the
        // ~49,800-row bulk-imported college directory already had its own
        // regular-college row under the same slug. For those, don't insert
        // a duplicate - just flip on the online-specific flags on the
        // existing row, leaving its other (likely more authoritative,
        // bulk-imported) data untouched.
        $updStmt = null;
        if (isset($has['is_online'], $has['online_mode'], $has['ugc_approved'])) {
            $updStmt = $db->prepare("UPDATE colleges SET is_online = ?, online_mode = ?, ugc_approved = ? WHERE id = ?");
        }

        foreach ($colleges as $row) {
            $slug = $row[3];
            if (isset($slugToId[$slug])) {
                if ($updStmt) {
                    try {
                        $updStmt->execute([$row[16], $row[17], $row[18], $slugToId[$slug]]);
                    } catch (Throwable $e) {}
                }
                continue;
            }

            $vals = [];
            foreach ($idx as $i) $vals[] = $row[$i];
            try {
                $stmt->execute($vals);
                $slugToId[$slug] = (int) $db->lastInsertId();
            } catch (Throwable $e) {
                continue; // one bad row shouldn't abort the other 49
            }
        }

        // Feature the top 10, same colleges as database/seed_online_colleges.sql
        // (translated from that file's old fake IDs to real slugs).
        if (isset($has['is_featured'])) {
            $featuredSlugs = ['ignou','nmims-online','amity-university-online','manipal-university-online',
                'lpu-online','chandigarh-university-online','scdl','bits-pilani-wilp',
                'srm-university-online','vit-online'];
            $featuredIds = array_values(array_filter(array_map(fn($s) => $slugToId[$s] ?? null, $featuredSlugs)));
            if ($featuredIds) {
                try {
                    $inClause = implode(',', array_map('intval', $featuredIds));
                    $db->exec("UPDATE colleges SET is_featured = 1 WHERE id IN ($inClause) AND is_online = 1");
                } catch (Throwable $e) {}
            }
        }
    } catch (Throwable $e) {
        error_log('collegesEnsureSeed failed: ' . $e->getMessage());
    }

    return $cached = $slugToId;
}
