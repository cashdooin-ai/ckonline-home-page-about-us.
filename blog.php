<?php
$pageTitle    = 'Online Education Blog 2025 | CollegeVidya Alternative | CollegeKampus';
$pageDesc     = 'Read expert guides on online MBA, BCA, BBA admissions, UGC rules, university rankings and career tips for Indian students choosing online degrees in 2025.';
$pageKeywords = 'online education blog, online MBA 2025, UGC approved universities, online degree India, CollegeVidya alternative';
require_once __DIR__ . '/includes/header.php';

// ── Static placeholder posts ──────────────────────────────────────────────────
$staticPosts = [
    [
        'slug'       => 'best-online-mba-universities-india-2025',
        'title'      => 'Best Online MBA Universities in India 2025 — Complete Guide',
        'category'   => 'Online MBA',
        'excerpt'    => 'Discover the top UGC-approved universities offering online MBA in 2025. Compare fees, specializations, placements and choose the right program for your career.',
        'author'     => 'CollegeKampus Team',
        'published_at'=> '2025-05-10',
        'read_time'  => '8 min read',
    ],
    [
        'slug'       => 'is-online-degree-valid-india-ugc-rules',
        'title'      => 'Is Online Degree Valid in India? UGC Rules Explained',
        'category'   => 'Admissions',
        'excerpt'    => 'Confused about the validity of online degrees for government jobs and further studies? We break down the latest UGC guidelines so you know exactly where you stand.',
        'author'     => 'CollegeKampus Team',
        'published_at'=> '2025-04-22',
        'read_time'  => '6 min read',
    ],
    [
        'slug'       => 'online-bca-vs-regular-bca-2025',
        'title'      => 'Online BCA vs Regular BCA: Which is Better in 2025?',
        'category'   => 'Online BCA',
        'excerpt'    => 'Weighing online BCA against a regular campus degree? We compare fees, job prospects, flexibility and recognition to help you make the right call.',
        'author'     => 'CollegeKampus Team',
        'published_at'=> '2025-04-05',
        'read_time'  => '7 min read',
    ],
    [
        'slug'       => 'nirf-rankings-2025-top-online-universities',
        'title'      => 'NIRF Rankings 2025: Top Online Universities You Should Know',
        'category'   => 'University News',
        'excerpt'    => 'The NIRF 2025 rankings are out. Here\'s how the leading online and distance education universities performed and what it means for your admission decision.',
        'author'     => 'CollegeKampus Team',
        'published_at'=> '2025-03-30',
        'read_time'  => '5 min read',
    ],
    [
        'slug'       => 'online-mba-fees-guide-what-you-actually-pay',
        'title'      => 'Online MBA Fees Guide: What You Actually Pay vs What Ads Say',
        'category'   => 'Online MBA',
        'excerpt'    => 'Ads show ₹50,000 but you end up paying ₹1.5 lakh. We reveal the hidden costs of online MBA programs — exam fees, study material, certification charges and more.',
        'author'     => 'CollegeKampus Team',
        'published_at'=> '2025-03-15',
        'read_time'  => '9 min read',
    ],
    [
        'slug'       => 'how-to-choose-online-mba-specialization',
        'title'      => 'How to Choose Between Online MBA Specializations',
        'category'   => 'Career Tips',
        'excerpt'    => 'Finance, Marketing, HR, Operations, Data Analytics — the list of MBA specializations is long. Here\'s a practical framework to pick the one that matches your career goals.',
        'author'     => 'CollegeKampus Team',
        'published_at'=> '2025-03-01',
        'read_time'  => '7 min read',
    ],
];

// ── Try fetching from DB ──────────────────────────────────────────────────────
$posts = [];
$useStatic = true;
try {
    $pdo   = getDB();
    $check = $pdo->query("SHOW TABLES LIKE 'blog_posts'")->rowCount();
    if ($check > 0) {
        $search = trim($_GET['q'] ?? '');
        $cat    = trim($_GET['cat'] ?? '');
        $page   = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 6;
        $offset  = ($page - 1) * $perPage;

        $where = ["status = 'published'"];
        $params = [];
        if ($search) { $where[] = "(title LIKE ? OR excerpt LIKE ?)"; $params[] = "%$search%"; $params[] = "%$search%"; }
        if ($cat)    { $where[] = "category = ?"; $params[] = $cat; }
        $sql = "SELECT * FROM blog_posts WHERE " . implode(' AND ', $where) . " ORDER BY published_at DESC LIMIT $perPage OFFSET $offset";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $posts = $stmt->fetchAll();

        if (!empty($posts)) $useStatic = false;

        // total count for pagination
        $cntSql  = "SELECT COUNT(*) FROM blog_posts WHERE " . implode(' AND ', $where);
        $cntStmt = $pdo->prepare($cntSql);
        $cntStmt->execute($params);
        $totalPosts = (int)$cntStmt->fetchColumn();
        $totalPages  = (int)ceil($totalPosts / $perPage);
    }
} catch (Exception $e) { /* silent fallback */ }

if ($useStatic) {
    $posts      = $staticPosts;
    $page       = 1;
    $totalPages = 1;
    $search     = '';
    $cat        = '';
}

$categories = ['Online MBA','Online BCA','Online BBA','Admissions','Career Tips','University News'];
$popularTags = ['Online MBA 2025','UGC Approved','Distance Education','Online BCA','NIRF Rankings','MBA Fees','Online Degree Valid','Work While Study'];

$catGradients = [
    'Online MBA'    => 'linear-gradient(135deg,#2563eb,#1d4ed8)',
    'Online BCA'    => 'linear-gradient(135deg,#7c3aed,#6d28d9)',
    'Online BBA'    => 'linear-gradient(135deg,#059669,#047857)',
    'Admissions'    => 'linear-gradient(135deg,#dc2626,#b91c1c)',
    'Career Tips'   => 'linear-gradient(135deg,#d97706,#b45309)',
    'University News'=> 'linear-gradient(135deg,#0891b2,#0e7490)',
];

// ── Newsletter signup ─────────────────────────────────────────────────────────
$nlMsg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nl_email'])) {
    $nlName  = trim($_POST['nl_name'] ?? '');
    $nlEmail = trim($_POST['nl_email'] ?? '');
    if (filter_var($nlEmail, FILTER_VALIDATE_EMAIL)) {
        try {
            require_once __DIR__ . '/includes/leads-schema.php';
            $pdo = getDB();
            onlineLeadsEnsureSchema($pdo);
            $pdo->prepare("INSERT INTO online_leads (name,email,source,created_at) VALUES (?,?,?,NOW()) ON DUPLICATE KEY UPDATE name=VALUES(name),source=VALUES(source)")
                ->execute([$nlName, $nlEmail, 'newsletter']);
            $nlMsg = 'success';
        } catch(Exception $e) { $nlMsg = 'error'; }
    } else { $nlMsg = 'invalid'; }
}
?>

<style>
.blog-hero{background:linear-gradient(135deg,#0f172a 0%,#1e3a5f 100%);padding:60px 0 40px;color:#fff;}
.blog-hero h1{font-size:2.4rem;font-weight:800;margin-bottom:.5rem;}
.blog-search-bar{max-width:560px;margin:1.5rem auto 0;}
.blog-search-bar .form-control{border-radius:12px 0 0 12px;height:50px;font-size:1rem;border:none;}
.blog-search-bar .btn{border-radius:0 12px 12px 0;height:50px;background:#2563eb;border:none;font-weight:700;padding:0 24px;}
.sidebar-card{background:#fff;border-radius:12px;border:1px solid #e2e8f0;padding:20px;margin-bottom:20px;}
.sidebar-card h5{font-size:.95rem;font-weight:700;color:#0f172a;margin-bottom:14px;padding-bottom:10px;border-bottom:2px solid #eff6ff;}
.cat-link{display:flex;justify-content:space-between;align-items:center;padding:7px 0;font-size:.875rem;color:#374151;text-decoration:none;border-bottom:1px solid #f8fafc;transition:color .2s;}
.cat-link:hover{color:#2563eb;}
.tag-pill{display:inline-block;background:#eff6ff;color:#2563eb;font-size:.75rem;font-weight:600;padding:4px 12px;border-radius:20px;margin:3px;text-decoration:none;transition:background .2s,color .2s;}
.tag-pill:hover{background:#2563eb;color:#fff;}
.post-card{background:#fff;border-radius:14px;border:1px solid #e2e8f0;overflow:hidden;transition:box-shadow .2s,transform .2s;height:100%;}
.post-card:hover{box-shadow:0 8px 32px rgba(0,0,0,.12);transform:translateY(-3px);}
.post-img{height:180px;display:flex;align-items:center;justify-content:center;color:#fff;font-size:1.1rem;font-weight:700;letter-spacing:.03em;}
.post-body{padding:18px;}
.cat-badge{display:inline-block;font-size:.72rem;font-weight:700;padding:3px 10px;border-radius:20px;background:#eff6ff;color:#2563eb;margin-bottom:8px;}
.post-title{font-size:1rem;font-weight:700;color:#0f172a;line-height:1.4;margin-bottom:8px;}
.post-title a{text-decoration:none;color:inherit;}
.post-title a:hover{color:#2563eb;}
.post-excerpt{font-size:.85rem;color:#64748b;line-height:1.6;margin-bottom:12px;}
.post-meta{font-size:.78rem;color:#94a3b8;display:flex;gap:12px;align-items:center;flex-wrap:wrap;}
.read-more{font-size:.82rem;font-weight:700;color:#2563eb;text-decoration:none;}
.read-more:hover{color:#1d4ed8;}
.nl-form input{border-radius:8px;margin-bottom:8px;font-size:.875rem;}
.nl-form .btn{width:100%;background:#2563eb;border:none;font-weight:700;border-radius:8px;}
</style>

<!-- Hero -->
<section class="blog-hero text-center">
    <div class="container">
        <h1>📚 Online Education Blog</h1>
        <p class="lead opacity-75 mb-0">Expert guides for Indian students navigating online & distance education</p>
        <form class="blog-search-bar d-flex" method="GET" action="">
            <input type="text" name="q" class="form-control" placeholder="Search articles... e.g. Online MBA fees, UGC rules"
                   value="<?= htmlspecialchars($search) ?>">
            <button type="submit" class="btn"><i class="bi bi-search"></i> Search</button>
        </form>
    </div>
</section>

<div class="container py-5">
    <div class="row g-4">
        <!-- Sidebar -->
        <div class="col-lg-3 order-lg-1">
            <!-- Categories -->
            <div class="sidebar-card">
                <h5><i class="bi bi-grid-3x3-gap-fill text-primary me-2"></i>Categories</h5>
                <?php foreach ($categories as $c):
                    $active = ($cat === $c) ? 'fw-bold text-primary' : ''; ?>
                <a href="?cat=<?= urlencode($c) ?>" class="cat-link <?= $active ?>">
                    <?= htmlspecialchars($c) ?>
                    <i class="bi bi-chevron-right" style="font-size:.7rem;"></i>
                </a>
                <?php endforeach; ?>
                <?php if ($cat): ?>
                <a href="?" class="d-block mt-2 text-center text-muted" style="font-size:.8rem;">✕ Clear filter</a>
                <?php endif; ?>
            </div>
            <!-- Tags -->
            <div class="sidebar-card">
                <h5><i class="bi bi-tags-fill text-primary me-2"></i>Popular Tags</h5>
                <?php foreach ($popularTags as $tag): ?>
                <a href="?q=<?= urlencode($tag) ?>" class="tag-pill"><?= htmlspecialchars($tag) ?></a>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Main grid -->
        <div class="col-lg-6 order-lg-2">
            <?php if ($search || $cat): ?>
            <p class="text-muted mb-3" style="font-size:.875rem;">
                <?php if ($search): ?>Showing results for "<strong><?= htmlspecialchars($search) ?></strong>"<?php endif; ?>
                <?php if ($cat): ?> in <strong><?= htmlspecialchars($cat) ?></strong><?php endif; ?>
            </p>
            <?php endif; ?>

            <?php if (empty($posts)): ?>
            <div class="text-center py-5">
                <i class="bi bi-journal-x display-4 text-muted"></i>
                <p class="text-muted mt-3">No posts found. Try a different search or category.</p>
                <a href="?" class="btn btn-outline-primary btn-sm">View all posts</a>
            </div>
            <?php else: ?>
            <div class="row g-4">
                <?php foreach ($posts as $p):
                    $catKey  = $p['category'] ?? 'Online MBA';
                    $grad    = $catGradients[$catKey] ?? 'linear-gradient(135deg,#2563eb,#1d4ed8)';
                    $excerpt = $p['excerpt'] ?? '';
                    if (strlen($excerpt) > 150) $excerpt = substr($excerpt, 0, 147) . '...';
                    $dateStr = '';
                    if (!empty($p['published_at'])) {
                        $ts = strtotime($p['published_at']);
                        $dateStr = date('M j, Y', $ts);
                    }
                    $readTime = $p['read_time'] ?? '5 min read';
                ?>
                <div class="col-md-6">
                    <div class="post-card">
                        <div class="post-img" style="background:<?= $grad ?>;">
                            <?= htmlspecialchars($p['category'] ?? 'Blog') ?>
                        </div>
                        <div class="post-body">
                            <span class="cat-badge"><?= htmlspecialchars($p['category'] ?? 'General') ?></span>
                            <div class="post-title">
                                <a href="<?= SITE_BASE ?>/blog-post.php?slug=<?= urlencode($p['slug']) ?>">
                                    <?= htmlspecialchars($p['title']) ?>
                                </a>
                            </div>
                            <div class="post-excerpt"><?= htmlspecialchars($excerpt) ?></div>
                            <div class="post-meta mb-2">
                                <span><i class="bi bi-person-fill"></i> <?= htmlspecialchars($p['author'] ?? 'CollegeKampus Team') ?></span>
                                <?php if ($dateStr): ?><span><i class="bi bi-calendar3"></i> <?= $dateStr ?></span><?php endif; ?>
                                <span><i class="bi bi-clock"></i> <?= htmlspecialchars($readTime) ?></span>
                            </div>
                            <a class="read-more" href="<?= SITE_BASE ?>/blog-post.php?slug=<?= urlencode($p['slug']) ?>">Read More →</a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
            <nav class="mt-5 d-flex justify-content-center">
                <ul class="pagination">
                    <?php for ($i = 1; $i <= $totalPages; $i++):
                        $qStr = http_build_query(array_merge($_GET, ['page' => $i]));
                    ?>
                    <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                        <a class="page-link" href="?<?= $qStr ?>"><?= $i ?></a>
                    </li>
                    <?php endfor; ?>
                </ul>
            </nav>
            <?php endif; ?>
            <?php endif; ?>
        </div>

        <!-- Right Sidebar -->
        <div class="col-lg-3 order-lg-3">
            <!-- Newsletter -->
            <div class="sidebar-card" style="background:linear-gradient(135deg,#eff6ff,#dbeafe);">
                <h5><i class="bi bi-envelope-heart-fill text-primary me-2"></i>Newsletter</h5>
                <p style="font-size:.82rem;color:#374151;">Get weekly updates on admissions, rankings and career tips.</p>
                <?php if ($nlMsg === 'success'): ?>
                <div class="alert alert-success py-2 mb-0" style="font-size:.82rem;">✅ Subscribed! Check your inbox.</div>
                <?php else: ?>
                <form class="nl-form" method="POST">
                    <?php if ($nlMsg === 'invalid'): ?>
                    <div class="alert alert-danger py-2 mb-2" style="font-size:.78rem;">Please enter a valid email.</div>
                    <?php endif; ?>
                    <input type="text"  name="nl_name"  class="form-control form-control-sm" placeholder="Your Name">
                    <input type="email" name="nl_email" class="form-control form-control-sm" placeholder="Your Email" required>
                    <button type="submit" class="btn btn-primary btn-sm">Subscribe Free 🔔</button>
                </form>
                <?php endif; ?>
            </div>

            <!-- Recent Posts -->
            <div class="sidebar-card">
                <h5><i class="bi bi-clock-history text-primary me-2"></i>Recent Posts</h5>
                <?php foreach (array_slice($useStatic ? $staticPosts : $posts, 0, 4) as $rp): ?>
                <a href="<?= SITE_BASE ?>/blog-post.php?slug=<?= urlencode($rp['slug']) ?>"
                   style="display:block;font-size:.82rem;font-weight:600;color:#374151;text-decoration:none;padding:6px 0;border-bottom:1px solid #f1f5f9;line-height:1.4;transition:color .2s;"
                   onmouseover="this.style.color='#2563eb'" onmouseout="this.style.color='#374151'">
                    <?= htmlspecialchars($rp['title']) ?>
                </a>
                <?php endforeach; ?>
            </div>

            <!-- Popular Tags -->
            <div class="sidebar-card">
                <h5><i class="bi bi-hash text-primary me-2"></i>Popular Tags</h5>
                <?php foreach ($popularTags as $tag): ?>
                <a href="?q=<?= urlencode($tag) ?>" class="tag-pill"><?= htmlspecialchars($tag) ?></a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
