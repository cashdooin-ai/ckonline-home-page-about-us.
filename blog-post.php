<?php
$slug = trim($_GET['slug'] ?? '');
if (!$slug) { header('Location: ' . (defined('SITE_BASE') ? SITE_BASE : '') . '/blog.php'); exit; }

// Safe slug
$slug = preg_replace('/[^a-zA-Z0-9\-_]/', '', $slug);

$post       = null;
$relatedPosts = [];
$useStatic  = true;

// ── Static fallback posts ─────────────────────────────────────────────────────
$staticPosts = [
    'best-online-mba-universities-india-2025' => [
        'slug'         => 'best-online-mba-universities-india-2025',
        'title'        => 'Best Online MBA Universities in India 2025 — Complete Guide',
        'category'     => 'Online MBA',
        'excerpt'      => 'Discover the top UGC-approved universities offering online MBA in 2025. Compare fees, specializations and placements.',
        'author'       => 'CollegeKampus Team',
        'published_at' => '2025-05-10',
        'content_html' => '<h2>Why Online MBA?</h2><p>Online MBA programs have gained massive acceptance in India, backed by UGC recognition and NAAC accreditation. Whether you are a working professional or a fresh graduate, an online MBA offers flexibility without compromising on quality.</p><h2>Top Universities for Online MBA 2025</h2><ul><li><strong>Amity University Online</strong> — Fees: ₹1.2L–1.8L, NAAC A+ rated</li><li><strong>Chandigarh University Online</strong> — Fees: ₹80K–1.2L, strong placement network</li><li><strong>LPU Online</strong> — Fees: ₹1L–1.5L, industry partnerships</li><li><strong>Symbiosis Online</strong> — Fees: ₹1.5L–2L, premium brand value</li><li><strong>Jain University Online</strong> — Fees: ₹90K–1.4L, flexible learning</li></ul><h2>Fees Comparison Table</h2><table class="table table-bordered"><thead><tr><th>University</th><th>Total Fees</th><th>Duration</th><th>NAAC Grade</th></tr></thead><tbody><tr><td>Amity Online</td><td>₹1.2–1.8 Lakh</td><td>2 Years</td><td>A+</td></tr><tr><td>Chandigarh University</td><td>₹80K–1.2L</td><td>2 Years</td><td>A+</td></tr><tr><td>LPU Online</td><td>₹1–1.5 Lakh</td><td>2 Years</td><td>A+</td></tr><tr><td>Symbiosis Online</td><td>₹1.5–2 Lakh</td><td>2 Years</td><td>A</td></tr></tbody></table><h2>Eligibility</h2><p>Most online MBA programs require a bachelor\'s degree with at least 50% marks. Some universities accept students with lower marks or work experience.</p><h2>Conclusion</h2><p>Choosing the right online MBA university depends on your budget, career goals, and preferred specialization. <strong>Get Free Counselling from CollegeKampus</strong> to shortlist the best program for you.</p>',
        'tags'         => 'Online MBA,UGC Approved,MBA Fees,Top Universities',
        'meta_title'   => 'Best Online MBA Universities India 2025 | CollegeKampus',
        'meta_desc'    => 'Top UGC-approved online MBA universities in India 2025. Compare fees, specializations and placements. Get free counselling from CollegeKampus.',
    ],
];

// ── Try DB ────────────────────────────────────────────────────────────────────
try {
    $pdo   = getDB();
    $check = $pdo->query("SHOW TABLES LIKE 'blog_posts'")->rowCount();
    if ($check > 0) {
        $stmt = $pdo->prepare("SELECT * FROM blog_posts WHERE slug = ? AND status = 'published' LIMIT 1");
        $stmt->execute([$slug]);
        $dbPost = $stmt->fetch();
        if ($dbPost) {
            $post      = $dbPost;
            $useStatic = false;
            // increment views
            $pdo->prepare("UPDATE blog_posts SET views = views + 1 WHERE id = ?")->execute([$post['id']]);
            // related posts
            $relStmt = $pdo->prepare("SELECT slug,title,category,published_at FROM blog_posts WHERE status='published' AND id != ? AND category = ? LIMIT 3");
            $relStmt->execute([$post['id'], $post['category']]);
            $relatedPosts = $relStmt->fetchAll();
        }
    }
} catch (Exception $e) { /* silent */ }

if ($useStatic) {
    $post = $staticPosts[$slug] ?? null;
    if ($post) {
        $relatedPosts = array_values(array_filter($staticPosts, fn($p) => $p['slug'] !== $slug));
        $relatedPosts = array_slice($relatedPosts, 0, 3);
    }
}

// ── SEO setup ─────────────────────────────────────────────────────────────────
if ($post) {
    $pageTitle    = $post['meta_title'] ?? $post['title'];
    $pageDesc     = $post['meta_desc']  ?? $post['excerpt'] ?? '';
    $pageCanonical = SITE_BASE . '/blog-post.php?slug=' . urlencode($slug);
    $pageOgImage  = !empty($post['featured_image']) ? $post['featured_image'] : SITE_BASE . '/assets/img/og-default.jpg';
    $pageType     = 'article';

    $publishedIso = !empty($post['published_at']) ? date('c', strtotime($post['published_at'])) : '';
    $jsonLd = [
        '@context'         => 'https://schema.org',
        '@type'            => 'Article',
        'headline'         => $post['title'],
        'description'      => $pageDesc,
        'image'            => $pageOgImage,
        'author'           => ['@type' => 'Organization', 'name' => $post['author'] ?? 'CollegeKampus Team'],
        'publisher'        => ['@type' => 'Organization', 'name' => 'CollegeKampus Online', 'logo' => ['@type' => 'ImageObject', 'url' => SITE_BASE . '/assets/img/logo.png']],
        'datePublished'    => $publishedIso,
        'mainEntityOfPage' => $pageCanonical,
    ];
} else {
    $pageTitle = 'Post Not Found | CollegeKampus';
    $pageDesc  = 'The article you are looking for could not be found.';
}

require_once __DIR__ . '/includes/header.php';

// ── Lead save ─────────────────────────────────────────────────────────────────
$leadMsg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['lead_name'])) {
    $lName  = trim($_POST['lead_name'] ?? '');
    $lPhone = trim($_POST['lead_phone'] ?? '');
    $lEmail = trim($_POST['lead_email'] ?? '');
    if ($lName && $lPhone) {
        try {
            $pdo = getDB();
            $pdo->prepare("INSERT INTO leads (name,phone,email,source,page_url,created_at) VALUES (?,?,?,?,?,NOW())")
                ->execute([$lName, $lPhone, $lEmail, 'blog-post', $pageCanonical ?? '']);
            $leadMsg = 'success';
        } catch(Exception $e) { $leadMsg = 'error'; }
    } else { $leadMsg = 'invalid'; }
}

// ── TOC generation from H2s ───────────────────────────────────────────────────
$toc = [];
if ($post && !empty($post['content_html'])) {
    preg_match_all('/<h2[^>]*>(.*?)<\/h2>/i', $post['content_html'], $h2Matches);
    foreach ($h2Matches[1] as $idx => $h2Text) {
        $plain = strip_tags($h2Text);
        $anchor = 'section-' . ($idx + 1);
        $toc[] = ['text' => $plain, 'anchor' => $anchor];
    }
    // Inject IDs into H2s
    $i = 0;
    $post['content_html'] = preg_replace_callback('/<h2([^>]*)>/i', function($m) use (&$i) {
        $id = 'section-' . (++$i);
        return "<h2{$m[1]} id=\"$id\">";
    }, $post['content_html']);
}

$catGradients = [
    'Online MBA'     => 'linear-gradient(135deg,#2563eb,#1d4ed8)',
    'Online BCA'     => 'linear-gradient(135deg,#7c3aed,#6d28d9)',
    'Online BBA'     => 'linear-gradient(135deg,#059669,#047857)',
    'Admissions'     => 'linear-gradient(135deg,#dc2626,#b91c1c)',
    'Career Tips'    => 'linear-gradient(135deg,#d97706,#b45309)',
    'University News'=> 'linear-gradient(135deg,#0891b2,#0e7490)',
];
?>

<style>
.post-hero{padding:60px 0 40px;color:#fff;}
.post-hero .cat-badge{background:rgba(255,255,255,.2);color:#fff;font-size:.8rem;font-weight:700;padding:5px 14px;border-radius:20px;}
.post-hero h1{font-size:2.1rem;font-weight:800;line-height:1.3;margin:1rem 0;}
.post-meta-bar{font-size:.85rem;opacity:.8;display:flex;gap:18px;flex-wrap:wrap;align-items:center;}
.article-body{font-size:1rem;line-height:1.85;color:#1e293b;}
.article-body h2{font-size:1.5rem;font-weight:800;color:#0f172a;margin:2rem 0 1rem;padding-top:.5rem;}
.article-body h3{font-size:1.2rem;font-weight:700;color:#1e293b;margin:1.5rem 0 .75rem;}
.article-body ul{padding-left:1.3rem;margin-bottom:1rem;}
.article-body ul li{margin-bottom:.4rem;}
.article-body table{margin:1.5rem 0;}
.toc-box{background:#eff6ff;border:1px solid #bfdbfe;border-radius:12px;padding:20px;margin-bottom:2rem;}
.toc-box h6{font-size:.85rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:#1d4ed8;margin-bottom:10px;}
.toc-box ol{margin:0;padding-left:1.3rem;}
.toc-box ol li{margin-bottom:5px;}
.toc-box ol li a{font-size:.875rem;color:#2563eb;text-decoration:none;}
.toc-box ol li a:hover{text-decoration:underline;}
.share-bar{display:flex;gap:10px;flex-wrap:wrap;margin:2rem 0;}
.share-btn{display:inline-flex;align-items:center;gap:7px;padding:9px 18px;border-radius:8px;font-size:.85rem;font-weight:700;text-decoration:none;transition:opacity .2s;}
.share-btn:hover{opacity:.85;}
.share-fb{background:#1877f2;color:#fff;}
.share-tw{background:#1da1f2;color:#fff;}
.share-wa{background:#25d366;color:#fff;}
.share-li{background:#0a66c2;color:#fff;}
.author-box{background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;padding:20px;margin-top:2.5rem;display:flex;gap:16px;align-items:flex-start;}
.author-avatar{width:56px;height:56px;background:linear-gradient(135deg,#2563eb,#7c3aed);border-radius:50%;display:flex;align-items:center;justify-content:center;color:#fff;font-size:1.3rem;font-weight:800;flex-shrink:0;}
.lead-sidebar-card{background:#fff;border-radius:14px;border:2px solid #2563eb;padding:22px;margin-bottom:24px;position:sticky;top:80px;}
.lead-sidebar-card h5{font-size:1rem;font-weight:800;color:#0f172a;margin-bottom:4px;}
.lead-sidebar-card p{font-size:.82rem;color:#64748b;margin-bottom:14px;}
.lead-sidebar-card .form-control{border-radius:8px;font-size:.875rem;margin-bottom:8px;}
.lead-sidebar-card .btn{width:100%;background:#2563eb;border:none;font-weight:700;border-radius:8px;}
.related-card{background:#fff;border:1px solid #e2e8f0;border-radius:10px;padding:14px;margin-bottom:12px;text-decoration:none;display:block;transition:box-shadow .2s;}
.related-card:hover{box-shadow:0 4px 16px rgba(0,0,0,.1);}
.related-card .rc-cat{font-size:.72rem;font-weight:700;color:#2563eb;margin-bottom:4px;}
.related-card .rc-title{font-size:.85rem;font-weight:700;color:#0f172a;line-height:1.4;}
</style>

<?php if (!$post): ?>
<!-- Not found -->
<div class="container py-5 text-center">
    <i class="bi bi-journal-x display-3 text-muted"></i>
    <h2 class="mt-3">Post Not Found</h2>
    <p class="text-muted">The article you're looking for doesn't exist or has been removed.</p>
    <a href="<?= SITE_BASE ?>/blog.php" class="btn btn-primary mt-2">← Back to Blog</a>
    <?php if (!empty($relatedPosts)): ?>
    <h4 class="mt-5 mb-3">You might like these</h4>
    <div class="row justify-content-center g-3">
        <?php foreach ($relatedPosts as $rp): ?>
        <div class="col-md-4">
            <a href="<?= SITE_BASE ?>/blog-post.php?slug=<?= urlencode($rp['slug']) ?>" class="related-card">
                <div class="rc-cat"><?= htmlspecialchars($rp['category'] ?? '') ?></div>
                <div class="rc-title"><?= htmlspecialchars($rp['title']) ?></div>
            </a>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<?php else:
$grad = $catGradients[$post['category'] ?? ''] ?? 'linear-gradient(135deg,#2563eb,#1d4ed8)';
$dateStr = !empty($post['published_at']) ? date('M j, Y', strtotime($post['published_at'])) : '';
$encTitle = urlencode($post['title']);
$encUrl   = urlencode($pageCanonical ?? '');
?>

<!-- Hero -->
<section class="post-hero" style="background:<?= $grad ?>;">
    <div class="container">
        <span class="cat-badge"><?= htmlspecialchars($post['category'] ?? 'Blog') ?></span>
        <h1><?= htmlspecialchars($post['title']) ?></h1>
        <div class="post-meta-bar">
            <span><i class="bi bi-person-fill"></i> <?= htmlspecialchars($post['author'] ?? 'CollegeKampus Team') ?></span>
            <?php if ($dateStr): ?><span><i class="bi bi-calendar3"></i> <?= $dateStr ?></span><?php endif; ?>
            <?php if (!empty($post['views'])): ?><span><i class="bi bi-eye-fill"></i> <?= number_format($post['views']) ?> views</span><?php endif; ?>
        </div>
    </div>
</section>

<!-- Content + Sidebar -->
<div class="container py-5">
    <div class="row g-4">
        <!-- Article -->
        <div class="col-lg-8">
            <!-- TOC -->
            <?php if (!empty($toc)): ?>
            <div class="toc-box">
                <h6>📋 Table of Contents</h6>
                <ol>
                    <?php foreach ($toc as $t): ?>
                    <li><a href="#<?= $t['anchor'] ?>"><?= htmlspecialchars($t['text']) ?></a></li>
                    <?php endforeach; ?>
                </ol>
            </div>
            <?php endif; ?>

            <!-- Article body -->
            <div class="article-body">
                <?= $post['content_html'] ?? '' ?>
            </div>

            <!-- Share -->
            <div class="share-bar">
                <span style="font-size:.875rem;font-weight:700;color:#374151;align-self:center;">Share:</span>
                <a class="share-btn share-fb" href="https://www.facebook.com/sharer/sharer.php?u=<?= $encUrl ?>" target="_blank" rel="noopener">
                    <i class="bi bi-facebook"></i> Facebook
                </a>
                <a class="share-btn share-tw" href="https://twitter.com/intent/tweet?text=<?= $encTitle ?>&url=<?= $encUrl ?>" target="_blank" rel="noopener">
                    <i class="bi bi-twitter-x"></i> Twitter
                </a>
                <a class="share-btn share-wa" href="https://api.whatsapp.com/send?text=<?= $encTitle ?>%20<?= $encUrl ?>" target="_blank" rel="noopener">
                    <i class="bi bi-whatsapp"></i> WhatsApp
                </a>
                <a class="share-btn share-li" href="https://www.linkedin.com/sharing/share-offsite/?url=<?= $encUrl ?>" target="_blank" rel="noopener">
                    <i class="bi bi-linkedin"></i> LinkedIn
                </a>
            </div>

            <!-- Author box -->
            <div class="author-box">
                <div class="author-avatar">CK</div>
                <div>
                    <div style="font-size:.95rem;font-weight:700;color:#0f172a;"><?= htmlspecialchars($post['author'] ?? 'CollegeKampus Team') ?></div>
                    <div style="font-size:.82rem;color:#64748b;margin-top:4px;">The CollegeKampus editorial team comprises education counsellors, researchers and writers dedicated to helping Indian students make informed decisions about online and distance education.</div>
                </div>
            </div>
        </div>

        <!-- Right Sidebar -->
        <div class="col-lg-4">
            <!-- Lead form -->
            <div class="lead-sidebar-card">
                <h5>🎓 Get Free Counselling</h5>
                <p>Talk to our experts and find the best online program for your career.</p>
                <?php if ($leadMsg === 'success'): ?>
                <div class="alert alert-success py-2" style="font-size:.82rem;">✅ We'll call you within 24 hours!</div>
                <?php else: ?>
                <?php if ($leadMsg === 'invalid'): ?><div class="alert alert-warning py-2 mb-2" style="font-size:.78rem;">Please enter your name and phone.</div><?php endif; ?>
                <form method="POST">
                    <input type="hidden" name="lead_source" value="blog-post">
                    <input type="text"  name="lead_name"  class="form-control form-control-sm" placeholder="Your Name *" required>
                    <input type="tel"   name="lead_phone" class="form-control form-control-sm" placeholder="Phone Number *" required>
                    <input type="email" name="lead_email" class="form-control form-control-sm" placeholder="Email (optional)">
                    <button type="submit" class="btn btn-primary">Get Free Counselling 📞</button>
                </form>
                <?php endif; ?>
            </div>

            <!-- Related posts -->
            <?php if (!empty($relatedPosts)): ?>
            <div class="sidebar-card" style="background:#fff;border-radius:14px;border:1px solid #e2e8f0;padding:20px;">
                <h5 style="font-size:.95rem;font-weight:700;color:#0f172a;margin-bottom:14px;padding-bottom:10px;border-bottom:2px solid #eff6ff;">Related Articles</h5>
                <?php foreach ($relatedPosts as $rp): ?>
                <a href="<?= SITE_BASE ?>/blog-post.php?slug=<?= urlencode($rp['slug']) ?>" class="related-card">
                    <div class="rc-cat"><?= htmlspecialchars($rp['category'] ?? '') ?></div>
                    <div class="rc-title"><?= htmlspecialchars($rp['title']) ?></div>
                </a>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <!-- Program links -->
            <div style="background:#fff;border-radius:14px;border:1px solid #e2e8f0;padding:20px;margin-top:0;">
                <h5 style="font-size:.95rem;font-weight:700;color:#0f172a;margin-bottom:14px;padding-bottom:10px;border-bottom:2px solid #eff6ff;">Explore Programs</h5>
                <?php
                $programs = [
                    ['name'=>'Online MBA','icon'=>'🏅','url'=>'mba'],
                    ['name'=>'Online BCA','icon'=>'💻','url'=>'bca'],
                    ['name'=>'Online BBA','icon'=>'📊','url'=>'bba'],
                    ['name'=>'Online MCA','icon'=>'🖥️','url'=>'mca'],
                    ['name'=>'Online B.Com','icon'=>'📈','url'=>'bcom'],
                ];
                foreach ($programs as $prog): ?>
                <a href="<?= SITE_BASE ?>/courses.php?program=<?= $prog['url'] ?>"
                   style="display:flex;align-items:center;gap:10px;padding:8px 0;text-decoration:none;color:#374151;border-bottom:1px solid #f1f5f9;font-size:.875rem;font-weight:600;transition:color .2s;"
                   onmouseover="this.style.color='#2563eb'" onmouseout="this.style.color='#374151'">
                    <span><?= $prog['icon'] ?></span> <?= $prog['name'] ?>
                    <i class="bi bi-chevron-right ms-auto" style="font-size:.7rem;color:#94a3b8;"></i>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
