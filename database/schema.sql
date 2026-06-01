-- CollegeKampus Online - Full MySQL Schema
-- INTEGRATION: Admin portal reads this DB. Partner portal writes to colleges/courses tables.
-- Shared DB: collegekampus -- see database/schema.sql

CREATE DATABASE IF NOT EXISTS collegekampus CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE collegekampus;

CREATE TABLE IF NOT EXISTS partners (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    college_id INT UNSIGNED NULL,
    name VARCHAR(120) NOT NULL,
    email VARCHAR(180) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    is_approved TINYINT(1) NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS colleges (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    slug VARCHAR(220) NOT NULL UNIQUE,
    logo_url VARCHAR(500),
    banner_url VARCHAR(500),
    city VARCHAR(100),
    state VARCHAR(100),
    type ENUM('private','government','deemed') NOT NULL DEFAULT 'private',
    established_year YEAR,
    description TEXT,
    accreditation VARCHAR(200),
    ranking_score DECIMAL(5,2) DEFAULT 0,
    is_featured TINYINT(1) NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    partner_id INT UNSIGNED NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (partner_id) REFERENCES partners(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS courses (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    slug VARCHAR(220) NOT NULL UNIQUE,
    category ENUM('engineering','management','medical','law','arts','science','commerce','design','other') NOT NULL DEFAULT 'other',
    duration_years DECIMAL(3,1) NOT NULL DEFAULT 4.0,
    degree_level ENUM('certificate','diploma','ug','pg','phd') NOT NULL DEFAULT 'ug',
    description TEXT,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS college_courses (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    college_id INT UNSIGNED NOT NULL,
    course_id INT UNSIGNED NOT NULL,
    annual_fees DECIMAL(10,2) NOT NULL DEFAULT 0,
    seats SMALLINT UNSIGNED DEFAULT 60,
    eligibility TEXT,
    application_deadline DATE,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    FOREIGN KEY (college_id) REFERENCES colleges(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    UNIQUE KEY uq_college_course (college_id, course_id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS admin_users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    email VARCHAR(180) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('super_admin','admin','editor') NOT NULL DEFAULT 'admin',
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS leads (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    student_name VARCHAR(150) NOT NULL,
    email VARCHAR(180) NOT NULL,
    phone VARCHAR(20),
    course_interest VARCHAR(200),
    college_id INT UNSIGNED NULL,
    message TEXT,
    status ENUM('new','contacted','converted','closed') NOT NULL DEFAULT 'new',
    source VARCHAR(80) DEFAULT 'website',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (college_id) REFERENCES colleges(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS applications (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    lead_id INT UNSIGNED NOT NULL,
    college_id INT UNSIGNED NOT NULL,
    course_id INT UNSIGNED NOT NULL,
    status ENUM('submitted','under_review','accepted','rejected') NOT NULL DEFAULT 'submitted',
    applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (lead_id) REFERENCES leads(id) ON DELETE CASCADE,
    FOREIGN KEY (college_id) REFERENCES colleges(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- SAMPLE DATA
-- ============================================================

-- Admin user password: Admin@123 (bcrypt hash)
INSERT INTO admin_users (name, email, password_hash, role) VALUES
('Super Admin', 'admin@collegekampus.in', '$2y$12$YuYe6nFhM1dCREPMp6K5suVRb5FJiGLxvJF7/eXjFaAMW83SLm5x2', 'super_admin');

-- 20 Courses
INSERT INTO courses (name, slug, category, duration_years, degree_level, description) VALUES
('B.Tech Computer Science Engineering',      'btech-cse',       'engineering', 4.0, 'ug',      'Undergraduate programme covering algorithms, software engineering, AI/ML and systems programming.'),
('B.Tech Mechanical Engineering',             'btech-mech',      'engineering', 4.0, 'ug',      'Core mechanical principles: thermodynamics, manufacturing, CAD/CAM and robotics.'),
('B.Tech Electronics & Communication',       'btech-ece',       'engineering', 4.0, 'ug',      'Circuits, VLSI, embedded systems, communication networks and signal processing.'),
('MBA General Management',                    'mba-general',     'management',  2.0, 'pg',      'Full-time PG programme with specialisations in finance, marketing and HR.'),
('MBA Business Analytics',                    'mba-analytics',   'management',  2.0, 'pg',      'Combining management with data science, machine learning and business intelligence.'),
('MBBS',                                      'mbbs',            'medical',     5.5, 'ug',      'Bachelor of Medicine and Bachelor of Surgery -- primary medical degree in India.'),
('B.Pharm',                                   'bpharm',          'medical',     4.0, 'ug',      'Pharmaceutical sciences covering pharmacology, medicinal chemistry and drug formulation.'),
('LLB (Hons)',                                'llb-hons',        'law',         5.0, 'ug',      'Integrated five-year law programme covering constitutional, corporate and criminal law.'),
('BA English Literature',                     'ba-english',      'arts',        3.0, 'ug',      'Study of classic and contemporary literature, linguistics and creative writing.'),
('B.Sc Computer Science',                     'bsc-cs',          'science',     3.0, 'ug',      'Theoretical computer science with hands-on programming and mathematics.'),
('B.Com (Hons)',                              'bcom-hons',       'commerce',    3.0, 'ug',      'Commerce foundation with accounting, taxation, business law and financial markets.'),
('B.Des Product Design',                      'bdes-product',    'design',      4.0, 'ug',      'Industrial and product design with focus on user experience and sustainability.'),
('M.Tech Artificial Intelligence',            'mtech-ai',        'engineering', 2.0, 'pg',      'Post-graduate specialisation in machine learning, computer vision and NLP.'),
('Ph.D Computer Science',                     'phd-cs',          'engineering', 3.0, 'phd',     'Doctoral programme for advanced research in computing and information systems.'),
('Diploma in Web Development',                'diploma-webdev',  'engineering', 1.0, 'diploma', 'Practical web development: HTML, CSS, JS, PHP, React and deployment.'),
('B.Sc Data Science',                         'bsc-datascience', 'science',     3.0, 'ug',      'Statistics, Python, ML and data visualisation for aspiring data analysts.'),
('M.Sc Biotechnology',                        'msc-biotech',     'science',     2.0, 'pg',      'Advanced biotechnology covering genomics, proteomics and bioinformatics.'),
('B.Tech Civil Engineering',                  'btech-civil',     'engineering', 4.0, 'ug',      'Structural engineering, construction management, geotechnics and environmental engineering.'),
('BBA (Bachelor of Business Administration)', 'bba',             'management',  3.0, 'ug',      'Foundational business programme covering marketing, finance, HR and entrepreneurship.'),
('M.Com Financial Management',                'mcom-finance',    'commerce',    2.0, 'pg',      'Advanced commerce degree focusing on corporate finance, investment and taxation.');

-- 10 Colleges
INSERT INTO colleges (name, slug, city, state, type, established_year, description, accreditation, ranking_score, is_featured, is_active) VALUES
('Indian Institute of Technology Delhi',     'iit-delhi',          'New Delhi',  'Delhi',          'government', 1961, 'IIT Delhi is one of India\'s premier engineering institutes, renowned for its research output and alumni network spanning the globe.', 'NAAC A++, NBA',      98.50, 1, 1),
('Indian Institute of Management Ahmedabad', 'iim-ahmedabad',      'Ahmedabad',  'Gujarat',        'government', 1961, 'IIM Ahmedabad is India\'s most prestigious management institution, offering world-class MBA programmes and executive education.', 'AACSB, AMBA, EQUIS', 99.00, 1, 1),
('Manipal Academy of Higher Education',      'manipal-university', 'Manipal',    'Karnataka',      'deemed',     1953, 'A leading deemed university offering programmes across engineering, medicine, management, law and design with international collaborations.', 'NAAC A+, NBA',       87.00, 1, 1),
('Amity University Noida',                   'amity-noida',        'Noida',      'Uttar Pradesh',  'private',    2005, 'Amity University is a large private university with 150+ programmes emphasising industry partnerships, entrepreneurship and international exposure.', 'NAAC A+',            82.50, 0, 1),
('VIT Vellore',                              'vit-vellore',        'Vellore',    'Tamil Nadu',     'deemed',     1984, 'VIT is a globally recognised deemed university famous for its technology programmes, placement record and vibrant campus life attracting students from 50+ countries.', 'NAAC A++, NBA',      91.00, 1, 1),
('Symbiosis International University',       'symbiosis-pune',     'Pune',       'Maharashtra',    'deemed',     2002, 'Symbiosis is a multi-disciplinary deemed university offering management, law, media, engineering and humanities with strong international immersion.', 'NAAC A',             84.00, 0, 1),
('Delhi University (DU)',                    'delhi-university',   'New Delhi',  'Delhi',          'government', 1922, 'One of the largest central universities in India, comprising 90 colleges offering UG and PG programmes across arts, science, commerce and law.', 'NAAC A++',           93.00, 1, 1),
('Lovely Professional University',           'lpu-phagwara',       'Phagwara',   'Punjab',         'private',    2005, 'LPU is one of India\'s largest private universities offering 200+ programmes with strong emphasis on skill development, entrepreneurship and international partnerships.', 'NAAC A+',            79.00, 0, 1),
('BITS Pilani',                              'bits-pilani',        'Pilani',     'Rajasthan',      'deemed',     1964, 'BITS Pilani is a premier science and technology institution known for its rigorous academics, Practice School programme and consistently high placements.', 'NAAC A, NBA',        95.00, 1, 1),
('Tata Institute of Social Sciences',        'tiss-mumbai',        'Mumbai',     'Maharashtra',    'government', 1936, 'TISS is a public university specialising in social sciences, management, human rights and media studies, recognised as an Institute of National Importance.', 'NAAC A++',           89.50, 0, 1);

-- College-Course links
INSERT INTO college_courses (college_id, course_id, annual_fees, seats, eligibility, application_deadline) VALUES
(1,  1,  250000, 120, '10+2 PCM, JEE Advanced rank',          '2025-05-15'),
(1,  2,  250000,  90, '10+2 PCM, JEE Advanced rank',          '2025-05-15'),
(1,  3,  250000,  90, '10+2 PCM, JEE Advanced rank',          '2025-05-15'),
(1, 13,  280000,  60, 'B.Tech/B.E. with 60%, GATE score',     '2025-03-31'),
(1, 14,       0,  20, 'M.Tech/M.Sc with research proposal',   '2025-02-28'),
(2,  4, 2400000, 385, 'Graduation 50%, CAT score',            '2025-01-15'),
(2,  5, 2400000,  60, 'Graduation 50%, CAT/GMAT',             '2025-01-15'),
(3,  1,  280000, 180, '10+2 PCM 60%, MET/JEE Main',           '2025-06-30'),
(3,  6,  900000, 150, '10+2 PCB 60%, NEET-UG',                '2025-06-30'),
(3,  8,  150000, 120, '10+2 any stream, CLAT/MLAT',           '2025-05-30'),
(3, 12,  220000,  60, '10+2 any stream, portfolio',           '2025-05-30'),
(4,  1,  180000, 240, '10+2 PCM 60%',                         '2025-07-31'),
(4,  4,  850000, 180, 'Graduation 50%, CAT/MAT/XAT',          '2025-03-31'),
(4, 19,   95000, 120, '10+2 any stream',                      '2025-07-31'),
(5,  1,  195000, 360, '10+2 PCM 60%, VITEEE/JEE Main',       '2025-04-30'),
(5,  2,  195000, 180, '10+2 PCM 60%, VITEEE',                 '2025-04-30'),
(5,  3,  195000, 180, '10+2 PCM 60%, VITEEE',                 '2025-04-30'),
(5, 13,  225000,  90, 'B.Tech 60%, VITMEE/GATE',              '2025-03-31'),
(5, 18,  175000, 120, '10+2 PCM 60%, VITEEE',                 '2025-04-30'),
(6,  4,  950000, 180, 'Graduation 50%, SNAP score',           '2025-01-31'),
(6,  8,  130000, 120, '10+2 any stream, SET/CLAT',            '2025-05-31'),
(7,  9,   25000, 360, '10+2 any stream, CUET merit',          '2025-06-15'),
(7, 10,   28000, 240, '10+2 PCM, CUET merit',                 '2025-06-15'),
(7, 11,   22000, 300, '10+2 Commerce, CUET merit',            '2025-06-15'),
(7,  8,   30000, 240, '10+2 any stream, CUET/CLAT',           '2025-06-15'),
(8,  1,  160000, 600, '10+2 PCM 60%',                         '2025-07-31'),
(8,  4,  350000, 240, 'Graduation any stream',                '2025-07-31'),
(8, 15,   60000, 120, '10+2 any stream',                      '2025-07-31'),
(8, 19,   90000, 180, '10+2 any stream',                      '2025-07-31'),
(9,  1,  540000,  90, '10+2 PCM, BITSAT score',               '2025-05-20'),
(9, 10,  540000,  60, '10+2 PCM, BITSAT score',               '2025-05-20'),
(9, 16,  520000,  60, '10+2 PCM/PCB, BITSAT score',           '2025-05-20'),
(9, 14,       0,  15, 'M.Sc equivalent, BITS HD',             '2025-03-31'),
(10, 4,  160000,  60, 'Graduation 45%, TISS-NET/CAT',         '2025-01-31'),
(10,17,   85000,  30, 'B.Sc/B.Tech Life Sciences',            '2025-03-15');
