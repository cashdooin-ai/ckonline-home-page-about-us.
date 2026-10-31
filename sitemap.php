<?php
// Dynamic XML sitemap — access at /dashboard/sitemap.php
// Add to Google Search Console as: https://online.collegekampus.com/dashboard/sitemap.php

require_once __DIR__ . '/config/db.php';
header('Content-Type: application/xml; charset=utf-8');

$base = SITE_BASE;
$today = date('Y-m-d');

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

// Static pages
$staticPages = [
    ['url' => $base,                       'priority' => '1.0', 'freq' => 'daily'],
    ['url' => $base . '/colleges',     'priority' => '0.9', 'freq' => 'daily'],
    ['url' => $base . '/programs',         'priority' => '0.9', 'freq' => 'weekly'],
    ['url' => $base . '/blog',             'priority' => '0.8', 'freq' => 'daily'],
    ['url' => $base . '/compare',      'priority' => '0.7', 'freq' => 'weekly'],
    ['url' => $base . '/counselling',  'priority' => '0.8', 'freq' => 'weekly'],
    ['url' => $base . '/ck-assured',       'priority' => '0.6', 'freq' => 'monthly'],
    ['url' => $base . '/coupons',          'priority' => '0.5', 'freq' => 'weekly'],
    ['url' => $base . '/contact',      'priority' => '0.6', 'freq' => 'monthly'],
    ['url' => $base . '/privacy-policy',   'priority' => '0.3', 'freq' => 'yearly'],
    ['url' => $base . '/terms',            'priority' => '0.3', 'freq' => 'yearly'],
];

foreach ($staticPages as $page) {
    echo "  <url>\n";
    echo "    <loc>" . htmlspecialchars($page['url']) . "</loc>\n";
    echo "    <lastmod>{$today}</lastmod>\n";
    echo "    <changefreq>{$page['freq']}</changefreq>\n";
    echo "    <priority>{$page['priority']}</priority>\n";
    echo "  </url>\n";
}

// Dynamic college pages
try {
    $db = getDB();
    $colleges = $db->query("SELECT id, slug, updated_at FROM colleges WHERE is_active = 1 ORDER BY id LIMIT 50000")->fetchAll();
    foreach ($colleges as $c) {
        $identifier = !empty($c['slug']) ? urlencode($c['slug']) : $c['id'];
        $lastmod = !empty($c['updated_at']) ? date('Y-m-d', strtotime($c['updated_at'])) : $today;
        echo "  <url>\n";
        echo "    <loc>" . htmlspecialchars($base . '/college/' . $identifier) . "</loc>\n";
        echo "    <lastmod>{$lastmod}</lastmod>\n";
        echo "    <changefreq>weekly</changefreq>\n";
        echo "    <priority>0.8</priority>\n";
        echo "  </url>\n";
    }
} catch (Throwable $e) {}

// Dynamic blog posts - missing from this sitemap entirely until now, despite
// being real, SEO-driven traffic (see blog.php/blog-post.php's own fixes for
// why some of these posts only have a body in `content`, not
// `content_html` - the same guard keeps a post with no real body in either
// column out of the sitemap).
try {
    $db = getDB();
    $hasContentCol = $db->query("SHOW COLUMNS FROM blog_posts LIKE 'content'")->rowCount() > 0;
    $bodyPredicate = $hasContentCol
        ? "COALESCE(NULLIF(content_html,''), NULLIF(content,'')) IS NOT NULL"
        : "content_html IS NOT NULL AND content_html != ''";
    $posts = $db->query("SELECT slug, updated_at, published_at FROM blog_posts WHERE status = 'published' AND $bodyPredicate ORDER BY id LIMIT 50000")->fetchAll();
    foreach ($posts as $p) {
        $lastmodSrc = $p['updated_at'] ?: $p['published_at'];
        $lastmod = $lastmodSrc ? date('Y-m-d', strtotime($lastmodSrc)) : $today;
        echo "  <url>\n";
        echo "    <loc>" . htmlspecialchars($base . '/blog/' . urlencode($p['slug'])) . "</loc>\n";
        echo "    <lastmod>{$lastmod}</lastmod>\n";
        echo "    <changefreq>monthly</changefreq>\n";
        echo "    <priority>0.6</priority>\n";
        echo "  </url>\n";
    }
} catch (Throwable $e) {}

echo '</urlset>';
