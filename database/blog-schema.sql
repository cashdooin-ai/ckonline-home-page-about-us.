-- Blog System Schema for CollegeKampus Online
-- Run after schema.sql

CREATE TABLE IF NOT EXISTS blog_posts (
  id INT AUTO_INCREMENT PRIMARY KEY,
  slug VARCHAR(300) UNIQUE NOT NULL,
  title VARCHAR(400) NOT NULL,
  category VARCHAR(100),
  excerpt TEXT,
  content_html LONGTEXT,
  author VARCHAR(100) DEFAULT 'CollegeKampus Team',
  featured_image VARCHAR(500),
  meta_title VARCHAR(400),
  meta_desc TEXT,
  tags VARCHAR(500),
  status ENUM('draft','published') DEFAULT 'draft',
  views INT DEFAULT 0,
  published_at DATETIME,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS program_pages (
  id INT AUTO_INCREMENT PRIMARY KEY,
  slug VARCHAR(200) UNIQUE NOT NULL,
  program_name VARCHAR(200) NOT NULL,
  tagline VARCHAR(400),
  content_html LONGTEXT,
  specializations_json TEXT,
  meta_title VARCHAR(400),
  meta_desc TEXT,
  university_count INT DEFAULT 0,
  status ENUM('draft','published') DEFAULT 'draft',
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS coupons (
  id INT AUTO_INCREMENT PRIMARY KEY,
  college_id INT,
  college_name VARCHAR(300),
  program VARCHAR(200),
  coupon_code VARCHAR(50),
  discount_pct INT,
  offer_title VARCHAR(400),
  offer_desc TEXT,
  valid_until DATE,
  terms TEXT,
  is_active TINYINT DEFAULT 1,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
