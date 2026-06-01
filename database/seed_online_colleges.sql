-- ============================================================
-- CollegeKampus Online — Seed: Online & Distance Colleges
-- Run this in phpMyAdmin on database: u939138857_ckampus_dash26
-- ============================================================
-- STEP 1: Add is_online flag to colleges table (safe if already exists)
-- STEP 2: Insert 50 real online/distance universities
-- STEP 3: Insert their streams/courses
-- ============================================================

-- ── STEP 1: Add is_online column ─────────────────────────────
ALTER TABLE `colleges`
  ADD COLUMN IF NOT EXISTS `is_online`   TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Is this an online/distance college',
  ADD COLUMN IF NOT EXISTS `online_mode` VARCHAR(50) DEFAULT NULL COMMENT 'online / distance / hybrid',
  ADD COLUMN IF NOT EXISTS `ugc_approved` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'UGC DEB approved for online education';

-- Add index for fast filtering
ALTER TABLE `colleges` ADD INDEX IF NOT EXISTS `idx_is_online` (`is_online`);

-- ── STEP 2: Insert Online/Distance Colleges ──────────────────
-- Using INSERT IGNORE to skip duplicates if re-run
INSERT IGNORE INTO `colleges`
  (`id`, `name`, `short_name`, `slug`, `description`, `college_type`, `institution_type`,
   `established_year`, `accreditation`, `naac_grade`, `city`, `state`,
   `website`, `min_fees`, `max_fees`, `is_featured`, `is_online`, `online_mode`, `ugc_approved`,
   `avg_package`, `ownership`, `gender_accepted`, `rating`)
VALUES

-- ── GOVERNMENT / OPEN UNIVERSITIES ───────────────────────────
(10001,'Indira Gandhi National Open University','IGNOU','ignou',
 'India\'s largest open university offering 200+ programmes in online and distance mode. UGC DEB approved. Enrolment of 3+ million students.',
 'government','university',1985,'UGC-DEB',
 'A','New Delhi','Delhi',
 'https://ignou.ac.in',10000,50000,1,1,'distance',1,350000,'central_govt','co-ed',4.2),

(10002,'Dr. B.R. Ambedkar Open University','BRAOU','braou',
 'Andhra Pradesh\'s premier open university offering UG, PG and diploma programmes in distance mode. UGC approved.',
 'government','university',1982,'UGC-DEB',
 'B+','Hyderabad','Telangana',
 'https://braou.ac.in',8000,40000,0,1,'distance',1,280000,'state_govt','co-ed',3.8),

(10003,'YCMOU – Yashwantrao Chavan Maharashtra Open University','YCMOU','ycmou',
 'Maharashtra state open university offering 100+ programmes in distance and online mode.',
 'government','university',1989,'UGC-DEB',
 'A','Nashik','Maharashtra',
 'https://ycmou.ac.in',9000,45000,0,1,'distance',1,300000,'state_govt','co-ed',3.7),

(10004,'Karnataka State Open University','KSOU','ksou',
 'Karnataka\'s state open university with programmes in arts, science, commerce and management.',
 'government','university',1996,'UGC-DEB',
 'B+','Mysuru','Karnataka',
 'https://ksoumysuru.ac.in',8000,38000,0,1,'distance',1,260000,'state_govt','co-ed',3.6),

(10005,'Tamil Nadu Open University','TNOU','tnou',
 'State open university offering distance education programmes across all disciplines.',
 'government','university',2002,'UGC-DEB',
 'B','Chennai','Tamil Nadu',
 'https://tnou.ac.in',7500,35000,0,1,'distance',1,240000,'state_govt','co-ed',3.5),

(10006,'Netaji Subhas Open University','NSOU','nsou',
 'West Bengal state open university, one of the largest distance education providers in eastern India.',
 'government','university',1997,'UGC-DEB',
 'B+','Kolkata','West Bengal',
 'https://wbnsou.ac.in',7000,35000,0,1,'distance',1,250000,'state_govt','co-ed',3.6),

(10007,'Vardhaman Mahaveer Open University','VMOU','vmou',
 'Rajasthan state open university offering 100+ courses in arts, science, commerce and professional streams.',
 'government','university',1987,'UGC-DEB',
 'B+','Kota','Rajasthan',
 'https://vmou.ac.in',7000,32000,0,1,'distance',1,230000,'state_govt','co-ed',3.5),

(10008,'Nalanda Open University','NOU','nalanda-open-university',
 'Bihar state open university offering UG and PG distance education programmes.',
 'government','university',1987,'UGC-DEB',
 'B','Patna','Bihar',
 'https://nalandaopenuniversity.com',6000,28000,0,1,'distance',1,210000,'state_govt','co-ed',3.4),

(10009,'MP Bhoj Open University','MPBOU','mp-bhoj-open-university',
 'Madhya Pradesh state open university offering distance programmes in management, arts and science.',
 'government','university',1991,'UGC-DEB',
 'B','Bhopal','Madhya Pradesh',
 'https://bhojvirtualuniversity.com',6500,30000,0,1,'distance',1,220000,'state_govt','co-ed',3.4),

(10010,'Himachal Pradesh Open University','HPOU','hpou',
 'Open university for Himachal Pradesh offering distance education across multiple disciplines.',
 'government','university',2009,'UGC-DEB',
 'B','Shimla','Himachal Pradesh',
 'https://hpou.ac.in',6000,28000,0,1,'distance',1,200000,'state_govt','co-ed',3.3),

(10011,'Annamalai University (DDE)','AU-DDE','annamalai-university-dde',
 'Directorate of Distance Education — one of India\'s oldest and largest distance education providers with 60+ programmes.',
 'government','university',1929,'UGC-DEB',
 'B+','Chidambaram','Tamil Nadu',
 'https://annamalaiuniversity.ac.in',8000,42000,1,1,'distance',1,280000,'state_govt','co-ed',3.8),

(10012,'Madurai Kamaraj University (IDE)','MKU','mku-ide',
 'Institute of Distance Education offering UG, PG and diploma programmes across all streams.',
 'government','university',1966,'UGC-DEB',
 'A','Madurai','Tamil Nadu',
 'https://mkudde.org',7500,38000,0,1,'distance',1,270000,'state_govt','co-ed',3.7),

(10013,'Alagappa University (DDE)','AU','alagappa-university-dde',
 'Distance Education wing offering UG, PG, M.Phil and diploma programmes.',
 'government','university',1985,'UGC-DEB',
 'A','Karaikudi','Tamil Nadu',
 'https://alagappauniversity.ac.in',7000,36000,0,1,'distance',1,260000,'state_govt','co-ed',3.7),

(10014,'University of Mumbai (IDOL)','MU-IDOL','mumbai-university-idol',
 'Institute of Distance and Open Learning offering UG and PG programmes.',
 'government','university',1857,'UGC-DEB',
 'A+','Mumbai','Maharashtra',
 'https://idol.mu.ac.in',9000,45000,1,1,'distance',1,350000,'state_govt','co-ed',4.0),

(10015,'Osmania University (DDE)','OU','osmania-dde',
 'Centre for Distance Education offering UG, PG, diploma and certificate programmes.',
 'government','university',1918,'UGC-DEB',
 'A','Hyderabad','Telangana',
 'https://osmania.ac.in',8000,38000,0,1,'distance',1,270000,'state_govt','co-ed',3.8),

(10016,'Punjabi University (Distance)','PU','punjabi-university-distance',
 'Directorate of Correspondence Courses offering management, arts and science programmes.',
 'government','university',1962,'UGC-DEB',
 'B+','Patiala','Punjab',
 'https://punjabiuniversity.ac.in',7500,35000,0,1,'distance',1,250000,'state_govt','co-ed',3.6),

(10017,'Kurukshetra University (Distance)','KU','kurukshetra-university-distance',
 'Directorate of Distance Education offering UG and PG programmes in all disciplines.',
 'government','university',1956,'UGC-DEB',
 'A','Kurukshetra','Haryana',
 'https://kuk.ac.in',7000,33000,0,1,'distance',1,240000,'state_govt','co-ed',3.6),

(10018,'University of Rajasthan (Distance)','UNIRAJ','uniraj-distance',
 'Department of Distance Education offering management, arts and science programmes.',
 'government','university',1947,'UGC-DEB',
 'B+','Jaipur','Rajasthan',
 'https://uniraj.ac.in',7000,32000,0,1,'distance',1,230000,'state_govt','co-ed',3.5),

(10019,'Andhra University (DUCK)','AU-DUCK','andhra-university-duck',
 'Directorate of UGC-Distance, Continuing and Online Education offering 50+ programmes.',
 'government','university',1926,'UGC-DEB',
 'A+','Visakhapatnam','Andhra Pradesh',
 'https://andhrauniversity.edu.in',8000,40000,0,1,'distance',1,280000,'state_govt','co-ed',3.8),

(10020,'Bangalore University (Distance)','BU','bangalore-university-distance',
 'Centre for PG Studies and Distance Education offering management and science programmes.',
 'government','university',1964,'UGC-DEB',
 'A','Bengaluru','Karnataka',
 'https://bangaloreuniversity.ac.in',8000,38000,0,1,'distance',1,270000,'state_govt','co-ed',3.7),

-- ── DEEMED / PRIVATE ONLINE UNIVERSITIES ─────────────────────
(10021,'NMIMS Global Access School for Continuing Education','NGASCE','nmims-online',
 'UGC DEB approved online programmes in management, science and commerce. Known for industry-integrated curriculum.',
 'deemed','university',2004,'UGC-DEB',
 'A+','Mumbai','Maharashtra',
 'https://ngasce.nmims.edu',80000,250000,1,1,'online',1,650000,'private','co-ed',4.5),

(10022,'Amity University Online','Amity Online','amity-university-online',
 'Top-ranked private university offering 30+ fully online UG and PG programmes with live classes.',
 'private','university',2005,'UGC-DEB',
 'A+','Noida','Uttar Pradesh',
 'https://amityonline.com',70000,220000,1,1,'online',1,600000,'private','co-ed',4.4),

(10023,'Manipal University Online','MUO','manipal-university-online',
 'UGC DEB approved online programmes from Manipal Academy of Higher Education. 100% placement support.',
 'deemed','university',1993,'UGC-DEB',
 'A++','Manipal','Karnataka',
 'https://onlinemanipal.com',65000,200000,1,1,'online',1,620000,'private','co-ed',4.5),

(10024,'LPU Online – Lovely Professional University','LPU Online','lpu-online',
 'India\'s largest private university offering 50+ online programmes with live interaction and placement support.',
 'private','university',2005,'UGC-DEB',
 'A+','Phagwara','Punjab',
 'https://online.lpu.in',50000,180000,1,1,'online',1,550000,'private','co-ed',4.3),

(10025,'Chandigarh University Online','CU Online','chandigarh-university-online',
 'QS ranked university offering fully online programmes with strong corporate connections and placement record.',
 'private','university',2012,'UGC-DEB',
 'A+','Mohali','Punjab',
 'https://online.cumail.in',55000,185000,1,1,'online',1,580000,'private','co-ed',4.3),

(10026,'Jain Online – Jain (Deemed-to-be) University','Jain Online','jain-university-online',
 'UGC approved online university with specialised management, IT and commerce programmes.',
 'deemed','university',2009,'UGC-DEB',
 'A+','Bengaluru','Karnataka',
 'https://online.jainuniversity.ac.in',60000,190000,1,1,'online',1,570000,'private','co-ed',4.3),

(10027,'Symbiosis Centre for Distance Learning','SCDL','scdl',
 'India\'s premier distance management school offering PGDBA, PGDHRM and other management diplomas.',
 'deemed','university',2001,'UGC-DEB',
 'A','Pune','Maharashtra',
 'https://www.scdl.net',40000,120000,1,1,'distance',1,500000,'private','co-ed',4.2),

(10028,'BITS Pilani – Work Integrated Learning Programmes','BITS WILP','bits-pilani-wilp',
 'Prestigious BITS Pilani offering M.Tech, MBA and MSc programmes for working professionals.',
 'deemed','university',1964,'UGC-DEB',
 'A+','Pilani','Rajasthan',
 'https://www.bits-pilani.ac.in/wilp',90000,300000,1,1,'online',1,900000,'private','co-ed',4.7),

(10029,'UPES Online','UPES','upes-online',
 'University of Petroleum and Energy Studies offering online management, law and technology programmes.',
 'private','university',2003,'UGC-DEB',
 'A','Dehradun','Uttarakhand',
 'https://online.upes.ac.in',65000,200000,1,1,'online',1,560000,'private','co-ed',4.2),

(10030,'DY Patil Vidyapeeth Online','DYP Online','dy-patil-online',
 'Deemed university offering online health sciences, management and technology programmes.',
 'deemed','university',2002,'UGC-DEB',
 'A','Pune','Maharashtra',
 'https://dypatilvideoyapeeth.edu.in',65000,210000,0,1,'online',1,520000,'private','co-ed',4.1),

(10031,'SRM University Online','SRM Online','srm-university-online',
 'Top-ranked private university offering online UG and PG programmes with strong placement record.',
 'private','university',1985,'UGC-DEB',
 'A++','Chennai','Tamil Nadu',
 'https://online.srmist.edu.in',60000,195000,1,1,'online',1,590000,'private','co-ed',4.3),

(10032,'VIT Online – Vellore Institute of Technology','VIT Online','vit-online',
 'NIRF top-ranked engineering university offering online MBA and MCA programmes.',
 'private','university',1984,'UGC-DEB',
 'A++','Vellore','Tamil Nadu',
 'https://online.vit.ac.in',70000,200000,1,1,'online',1,700000,'private','co-ed',4.4),

(10033,'Sharda University Online','Sharda Online','sharda-university-online',
 'Greater Noida based private university offering online management and IT programmes.',
 'private','university',2009,'UGC-DEB',
 'A','Greater Noida','Uttar Pradesh',
 'https://shardaonline.ac.in',55000,175000,0,1,'online',1,480000,'private','co-ed',4.0),

(10034,'GLA University Online','GLA Online','gla-university-online',
 'UGC approved online programmes in management, computing and commerce.',
 'private','university',2010,'UGC-DEB',
 'A+','Mathura','Uttar Pradesh',
 'https://gla.ac.in/online',50000,165000,0,1,'online',1,460000,'private','co-ed',4.0),

(10035,'Graphic Era University Online','GEU Online','graphic-era-university-online',
 'Uttarakhand\'s leading private university offering online management and technology programmes.',
 'private','university',1993,'UGC-DEB',
 'A','Dehradun','Uttarakhand',
 'https://online.graphicera.edu.in',50000,160000,0,1,'online',1,450000,'private','co-ed',3.9),

(10036,'Amrita University Online','Amrita Online','amrita-university-online',
 'Deemed university offering online MBA, MCA and M.Sc programmes with live faculty sessions.',
 'deemed','university',2003,'UGC-DEB',
 'A++','Coimbatore','Tamil Nadu',
 'https://www.amrita.edu/online',70000,210000,1,1,'online',1,620000,'private','co-ed',4.4),

(10037,'Alliance University Online','Alliance Online','alliance-university-online',
 'Bengaluru based private university offering online MBA and MCA programmes.',
 'private','university',2010,'UGC-DEB',
 'A','Bengaluru','Karnataka',
 'https://online.alliance.edu.in',60000,185000,0,1,'online',1,490000,'private','co-ed',4.0),

(10038,'Vignan\'s University Online','Vignan Online','vignans-university-online',
 'Andhra Pradesh university offering online management and technology programmes.',
 'private','university',2008,'UGC-DEB',
 'A','Guntur','Andhra Pradesh',
 'https://online.vignanuniversity.ac.in',45000,150000,0,1,'online',1,420000,'private','co-ed',3.8),

(10039,'Parul University Online','Parul Online','parul-university-online',
 'Gujarat\'s leading private university offering online programmes in management, science and commerce.',
 'private','university',2015,'UGC-DEB',
 'A','Vadodara','Gujarat',
 'https://online.paruluniversity.ac.in',50000,165000,0,1,'online',1,440000,'private','co-ed',3.9),

(10040,'MIT World Peace University Online','MIT-WPU Online','mit-wpu-online',
 'Pune based private university offering online MBA, MCA and B.Sc programmes.',
 'private','university',2017,'UGC-DEB',
 'A+','Pune','Maharashtra',
 'https://online.mitwpu.edu.in',60000,190000,0,1,'online',1,500000,'private','co-ed',4.1),

(10041,'Symbiosis School of Online and Digital Learning','SSODL','symbiosis-online',
 'Symbiosis International University\'s fully online school offering MBA, BBA and other programmes.',
 'deemed','university',2019,'UGC-DEB',
 'A+','Pune','Maharashtra',
 'https://ssodl.siu.edu.in',75000,220000,1,1,'online',1,600000,'private','co-ed',4.3),

(10042,'Hindustan Online – Hindustan Institute of Technology and Science','HITS Online','hindustan-online',
 'Chennai based deemed university offering online management and technology programmes.',
 'deemed','university',1985,'UGC-DEB',
 'A','Chennai','Tamil Nadu',
 'https://online.hindustanuniv.ac.in',55000,175000,0,1,'online',1,470000,'private','co-ed',4.0),

(10043,'Shoolini University Online','Shoolini Online','shoolini-university-online',
 'Himachal Pradesh private university offering online management and pharmacy programmes.',
 'private','university',2009,'UGC-DEB',
 'A+','Solan','Himachal Pradesh',
 'https://shooliniuniversity.com/online',50000,165000,0,1,'online',1,450000,'private','co-ed',4.0),

(10044,'Centurion University Online','CUO','centurion-university-online',
 'Odisha based private university offering online management, technology and vocational programmes.',
 'private','university',2010,'UGC-DEB',
 'A','Bhubaneswar','Odisha',
 'https://online.cutm.ac.in',45000,155000,0,1,'online',1,400000,'private','co-ed',3.8),

(10045,'RV University Online','RVU Online','rv-university-online',
 'Bengaluru private university offering online management and design programmes.',
 'private','university',2021,'UGC-DEB',
 'A','Bengaluru','Karnataka',
 'https://online.rvu.edu.in',60000,185000,0,1,'online',1,480000,'private','co-ed',4.0),

(10046,'Presidency University Online','PU Online','presidency-university-online',
 'Bengaluru private university offering UGC approved online management and IT programmes.',
 'private','university',2013,'UGC-DEB',
 'A','Bengaluru','Karnataka',
 'https://online.presidencyuniversity.in',55000,175000,0,1,'online',1,460000,'private','co-ed',3.9),

(10047,'Saveetha University Online','Saveetha Online','saveetha-university-online',
 'Chennai deemed university offering online MBA and MHA programmes.',
 'deemed','university',2005,'UGC-DEB',
 'A','Chennai','Tamil Nadu',
 'https://online.saveetha.ac.in',60000,180000,0,1,'online',1,480000,'private','co-ed',3.9),

(10048,'Sikkim Manipal University (Online/Distance)','SMU-DE','sikkim-manipal-online',
 'Pioneer in online and distance management education. NAAC accredited with 20+ years of experience.',
 'private','university',1995,'UGC-DEB',
 'B+','Gangtok','Sikkim',
 'https://smude.edu.in',45000,160000,1,1,'online',1,480000,'private','co-ed',4.0),

(10049,'Swami Vivekananda Subharti University (Distance)','SVSU','svsu-distance',
 'Uttar Pradesh university offering distance UG and PG programmes in arts, commerce and management.',
 'private','university',2008,'UGC-DEB',
 'A','Meerut','Uttar Pradesh',
 'https://subhartionline.com',30000,100000,0,1,'distance',1,350000,'private','co-ed',3.7),

(10050,'Indira Gandhi Delhi Technical University for Women (Online)','IGDTUW','igdtuw-online',
 'First technical university for women offering online technology and management programmes.',
 'government','university',1998,'UGC-DEB',
 'A','New Delhi','Delhi',
 'https://igdtuw.ac.in',35000,120000,0,1,'online',1,550000,'state_govt','female',4.1);

-- ── STEP 3: Insert College Streams for Online Colleges ────────
-- First, detect what columns college_streams has and insert accordingly
-- Common columns: id, college_id, stream_name (or name/stream), created_at

-- We use a safe INSERT that works with typical stream table structures
-- Run this block — if it fails, check the college_streams table columns in debug.php

INSERT IGNORE INTO `college_streams` (`college_id`, `stream_name`)
SELECT ins.college_id, ins.stream_name
FROM (
  SELECT 10001 AS college_id, 'BA' AS stream_name UNION ALL
  SELECT 10001, 'MA' UNION ALL SELECT 10001, 'B.Com' UNION ALL
  SELECT 10001, 'M.Com' UNION ALL SELECT 10001, 'MBA' UNION ALL
  SELECT 10001, 'MCA' UNION ALL SELECT 10001, 'BCA' UNION ALL
  SELECT 10001, 'B.Sc' UNION ALL SELECT 10001, 'M.Sc' UNION ALL
  SELECT 10001, 'BTS (Tourism)' UNION ALL

  SELECT 10002, 'BA' UNION ALL SELECT 10002, 'B.Com' UNION ALL
  SELECT 10002, 'MBA' UNION ALL SELECT 10002, 'M.Com' UNION ALL
  SELECT 10002, 'M.Sc' UNION ALL

  SELECT 10003, 'BA' UNION ALL SELECT 10003, 'B.Com' UNION ALL
  SELECT 10003, 'MBA' UNION ALL SELECT 10003, 'MCA' UNION ALL
  SELECT 10003, 'M.Com' UNION ALL

  SELECT 10004, 'BA' UNION ALL SELECT 10004, 'B.Com' UNION ALL
  SELECT 10004, 'MBA' UNION ALL SELECT 10004, 'M.Com' UNION ALL

  SELECT 10005, 'BA' UNION ALL SELECT 10005, 'B.Com' UNION ALL
  SELECT 10005, 'MBA' UNION ALL SELECT 10005, 'MCA' UNION ALL

  SELECT 10006, 'BA' UNION ALL SELECT 10006, 'B.Com' UNION ALL
  SELECT 10006, 'MBA' UNION ALL SELECT 10006, 'M.Sc' UNION ALL

  SELECT 10007, 'BA' UNION ALL SELECT 10007, 'B.Com' UNION ALL
  SELECT 10007, 'MBA' UNION ALL SELECT 10007, 'M.Com' UNION ALL

  SELECT 10008, 'BA' UNION ALL SELECT 10008, 'B.Com' UNION ALL
  SELECT 10008, 'MBA' UNION ALL SELECT 10008, 'M.Com' UNION ALL

  SELECT 10009, 'BA' UNION ALL SELECT 10009, 'B.Com' UNION ALL
  SELECT 10009, 'MBA' UNION ALL SELECT 10009, 'M.Com' UNION ALL

  SELECT 10010, 'BA' UNION ALL SELECT 10010, 'B.Com' UNION ALL
  SELECT 10010, 'MBA' UNION ALL SELECT 10010, 'M.Com' UNION ALL

  SELECT 10011, 'BA' UNION ALL SELECT 10011, 'B.Com' UNION ALL
  SELECT 10011, 'MBA' UNION ALL SELECT 10011, 'MCA' UNION ALL
  SELECT 10011, 'M.Com' UNION ALL SELECT 10011, 'M.Sc' UNION ALL

  SELECT 10012, 'BA' UNION ALL SELECT 10012, 'B.Com' UNION ALL
  SELECT 10012, 'MBA' UNION ALL SELECT 10012, 'MCA' UNION ALL
  SELECT 10012, 'M.Com' UNION ALL

  SELECT 10013, 'BA' UNION ALL SELECT 10013, 'B.Com' UNION ALL
  SELECT 10013, 'MBA' UNION ALL SELECT 10013, 'MCA' UNION ALL

  SELECT 10014, 'BA' UNION ALL SELECT 10014, 'B.Com' UNION ALL
  SELECT 10014, 'MBA' UNION ALL SELECT 10014, 'MCA' UNION ALL
  SELECT 10014, 'M.Com' UNION ALL SELECT 10014, 'LLB' UNION ALL

  SELECT 10015, 'BA' UNION ALL SELECT 10015, 'B.Com' UNION ALL
  SELECT 10015, 'MBA' UNION ALL SELECT 10015, 'MCA' UNION ALL
  SELECT 10015, 'M.Com' UNION ALL

  SELECT 10016, 'BA' UNION ALL SELECT 10016, 'B.Com' UNION ALL
  SELECT 10016, 'MBA' UNION ALL SELECT 10016, 'M.Com' UNION ALL

  SELECT 10017, 'BA' UNION ALL SELECT 10017, 'B.Com' UNION ALL
  SELECT 10017, 'MBA' UNION ALL SELECT 10017, 'M.Com' UNION ALL

  SELECT 10018, 'BA' UNION ALL SELECT 10018, 'B.Com' UNION ALL
  SELECT 10018, 'MBA' UNION ALL SELECT 10018, 'M.Com' UNION ALL

  SELECT 10019, 'BA' UNION ALL SELECT 10019, 'B.Com' UNION ALL
  SELECT 10019, 'MBA' UNION ALL SELECT 10019, 'MCA' UNION ALL
  SELECT 10019, 'M.Com' UNION ALL

  SELECT 10020, 'BA' UNION ALL SELECT 10020, 'B.Com' UNION ALL
  SELECT 10020, 'MBA' UNION ALL SELECT 10020, 'MCA' UNION ALL

  -- Private / Deemed online universities
  SELECT 10021, 'MBA' UNION ALL SELECT 10021, 'BBA' UNION ALL
  SELECT 10021, 'B.Com' UNION ALL SELECT 10021, 'M.Com' UNION ALL
  SELECT 10021, 'MCA' UNION ALL SELECT 10021, 'BCA' UNION ALL
  SELECT 10021, 'B.Sc (IT)' UNION ALL

  SELECT 10022, 'MBA' UNION ALL SELECT 10022, 'BBA' UNION ALL
  SELECT 10022, 'MCA' UNION ALL SELECT 10022, 'BCA' UNION ALL
  SELECT 10022, 'M.Com' UNION ALL SELECT 10022, 'B.Com' UNION ALL
  SELECT 10022, 'MA (Psychology)' UNION ALL SELECT 10022, 'M.Sc (Data Science)' UNION ALL

  SELECT 10023, 'MBA' UNION ALL SELECT 10023, 'MCA' UNION ALL
  SELECT 10023, 'BBA' UNION ALL SELECT 10023, 'BCA' UNION ALL
  SELECT 10023, 'B.Com' UNION ALL SELECT 10023, 'M.Com' UNION ALL
  SELECT 10023, 'M.Sc (Data Science)' UNION ALL SELECT 10023, 'MA' UNION ALL

  SELECT 10024, 'MBA' UNION ALL SELECT 10024, 'BBA' UNION ALL
  SELECT 10024, 'MCA' UNION ALL SELECT 10024, 'BCA' UNION ALL
  SELECT 10024, 'B.Com' UNION ALL SELECT 10024, 'M.Com' UNION ALL
  SELECT 10024, 'B.Sc (CS)' UNION ALL SELECT 10024, 'M.Sc (CS)' UNION ALL
  SELECT 10024, 'BA' UNION ALL SELECT 10024, 'MA' UNION ALL

  SELECT 10025, 'MBA' UNION ALL SELECT 10025, 'BBA' UNION ALL
  SELECT 10025, 'MCA' UNION ALL SELECT 10025, 'BCA' UNION ALL
  SELECT 10025, 'B.Com' UNION ALL SELECT 10025, 'M.Com' UNION ALL
  SELECT 10025, 'M.Sc (Data Science)' UNION ALL SELECT 10025, 'LLB' UNION ALL

  SELECT 10026, 'MBA' UNION ALL SELECT 10026, 'BBA' UNION ALL
  SELECT 10026, 'MCA' UNION ALL SELECT 10026, 'BCA' UNION ALL
  SELECT 10026, 'B.Com' UNION ALL SELECT 10026, 'M.Sc (Data Science)' UNION ALL

  SELECT 10027, 'PGDBA' UNION ALL SELECT 10027, 'PGDHRM' UNION ALL
  SELECT 10027, 'PGDIT' UNION ALL SELECT 10027, 'PGDIM' UNION ALL
  SELECT 10027, 'PG Diploma (Marketing)' UNION ALL

  SELECT 10028, 'M.Tech' UNION ALL SELECT 10028, 'MBA' UNION ALL
  SELECT 10028, 'M.Sc (CS)' UNION ALL SELECT 10028, 'M.Sc (Biological Sciences)' UNION ALL

  SELECT 10029, 'MBA' UNION ALL SELECT 10029, 'BBA' UNION ALL
  SELECT 10029, 'MCA' UNION ALL SELECT 10029, 'LLB' UNION ALL
  SELECT 10029, 'M.Sc (Data Science)' UNION ALL

  SELECT 10030, 'MBA' UNION ALL SELECT 10030, 'MCA' UNION ALL
  SELECT 10030, 'BBA' UNION ALL SELECT 10030, 'BCA' UNION ALL

  SELECT 10031, 'MBA' UNION ALL SELECT 10031, 'MCA' UNION ALL
  SELECT 10031, 'BBA' UNION ALL SELECT 10031, 'BCA' UNION ALL
  SELECT 10031, 'B.Com' UNION ALL SELECT 10031, 'M.Sc (Data Science)' UNION ALL

  SELECT 10032, 'MBA' UNION ALL SELECT 10032, 'MCA' UNION ALL
  SELECT 10032, 'M.Tech' UNION ALL SELECT 10032, 'B.Tech' UNION ALL

  SELECT 10033, 'MBA' UNION ALL SELECT 10033, 'MCA' UNION ALL
  SELECT 10033, 'BBA' UNION ALL SELECT 10033, 'BCA' UNION ALL
  SELECT 10033, 'B.Com' UNION ALL

  SELECT 10034, 'MBA' UNION ALL SELECT 10034, 'MCA' UNION ALL
  SELECT 10034, 'BBA' UNION ALL SELECT 10034, 'BCA' UNION ALL
  SELECT 10034, 'B.Com' UNION ALL

  SELECT 10035, 'MBA' UNION ALL SELECT 10035, 'MCA' UNION ALL
  SELECT 10035, 'BBA' UNION ALL SELECT 10035, 'BCA' UNION ALL

  SELECT 10036, 'MBA' UNION ALL SELECT 10036, 'MCA' UNION ALL
  SELECT 10036, 'M.Sc (Data Science)' UNION ALL SELECT 10036, 'M.Sc (AI)' UNION ALL

  SELECT 10037, 'MBA' UNION ALL SELECT 10037, 'MCA' UNION ALL
  SELECT 10037, 'BBA' UNION ALL SELECT 10037, 'BCA' UNION ALL

  SELECT 10038, 'MBA' UNION ALL SELECT 10038, 'MCA' UNION ALL
  SELECT 10038, 'BBA' UNION ALL SELECT 10038, 'BCA' UNION ALL

  SELECT 10039, 'MBA' UNION ALL SELECT 10039, 'MCA' UNION ALL
  SELECT 10039, 'BBA' UNION ALL SELECT 10039, 'BCA' UNION ALL
  SELECT 10039, 'B.Com' UNION ALL

  SELECT 10040, 'MBA' UNION ALL SELECT 10040, 'MCA' UNION ALL
  SELECT 10040, 'BCA' UNION ALL SELECT 10040, 'B.Sc (CS)' UNION ALL

  SELECT 10041, 'MBA' UNION ALL SELECT 10041, 'BBA' UNION ALL
  SELECT 10041, 'M.Sc (Data Science)' UNION ALL SELECT 10041, 'PGDM' UNION ALL

  SELECT 10042, 'MBA' UNION ALL SELECT 10042, 'MCA' UNION ALL
  SELECT 10042, 'BBA' UNION ALL SELECT 10042, 'BCA' UNION ALL

  SELECT 10043, 'MBA' UNION ALL SELECT 10043, 'BBA' UNION ALL
  SELECT 10043, 'M.Sc (Pharmaceutical Chemistry)' UNION ALL

  SELECT 10044, 'MBA' UNION ALL SELECT 10044, 'MCA' UNION ALL
  SELECT 10044, 'BBA' UNION ALL SELECT 10044, 'BCA' UNION ALL

  SELECT 10045, 'MBA' UNION ALL SELECT 10045, 'BBA' UNION ALL
  SELECT 10045, 'M.Des' UNION ALL SELECT 10045, 'B.Des' UNION ALL

  SELECT 10046, 'MBA' UNION ALL SELECT 10046, 'MCA' UNION ALL
  SELECT 10046, 'BBA' UNION ALL SELECT 10046, 'BCA' UNION ALL
  SELECT 10046, 'B.Com' UNION ALL

  SELECT 10047, 'MBA' UNION ALL SELECT 10047, 'MHA' UNION ALL
  SELECT 10047, 'BBA' UNION ALL

  SELECT 10048, 'MBA' UNION ALL SELECT 10048, 'MCA' UNION ALL
  SELECT 10048, 'BBA' UNION ALL SELECT 10048, 'BCA' UNION ALL
  SELECT 10048, 'B.Com' UNION ALL SELECT 10048, 'M.Com' UNION ALL

  SELECT 10049, 'BA' UNION ALL SELECT 10049, 'B.Com' UNION ALL
  SELECT 10049, 'MBA' UNION ALL SELECT 10049, 'MCA' UNION ALL

  SELECT 10050, 'B.Tech' UNION ALL SELECT 10050, 'M.Tech' UNION ALL
  SELECT 10050, 'MBA' UNION ALL SELECT 10050, 'MCA' UNION ALL
  SELECT 10050, 'BCA' UNION ALL SELECT 10050, 'B.Sc (CS)' UNION ALL

  SELECT 10050, 'B.Tech' -- end of last block (duplicate ignored)
) AS ins
WHERE EXISTS (SELECT 1 FROM colleges WHERE id = ins.college_id);

-- ── STEP 4: Mark these as featured (top 10) ──────────────────
UPDATE `colleges`
SET `is_featured` = 1
WHERE `id` IN (10001, 10021, 10022, 10023, 10024, 10025, 10027, 10028, 10031, 10032)
  AND `is_online` = 1;

-- ── VERIFY ───────────────────────────────────────────────────
SELECT COUNT(*) AS total_online_colleges FROM colleges WHERE is_online = 1;
SELECT COUNT(*) AS featured_online FROM colleges WHERE is_online = 1 AND is_featured = 1;
