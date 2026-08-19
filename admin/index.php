<?php
require_once __DIR__ . '/auth.php';
require_once dirname(__DIR__) . '/config/db.php';
require_once dirname(__DIR__) . '/includes/leads-schema.php';

$pdo   = getDB();
onlineLeadsEnsureSchema($pdo);
$flash = $_SESSION['flash'] ?? '';
unset($_SESSION['flash']);

// ── Stats ─────────────────────────────────────────────────────────────────────
$stats = ['posts_total'=>0,'posts_published'=>0,'posts_draft'=>0,'program_pages'=>0,'leads_7d'=>0];
try {
    $r = $pdo->query("SELECT COUNT(*) AS t, SUM(status='published') AS p, SUM(status='draft') AS d FROM blog_posts")->fetch();
    $stats['posts_total']     = $r['t'] ?? 0;
    $stats['posts_published'] = $r['p'] ?? 0;
    $stats['posts_draft']     = $r['d'] ?? 0;

    $stats['program_pages'] = $pdo->query("SELECT COUNT(*) FROM program_pages")->fetchColumn();
    $stats['leads_7d']      = $pdo->query("SELECT COUNT(*) FROM online_leads WHERE created_at >= NOW() - INTERVAL 7 DAY")->fetchColumn();
} catch(Exception $e) {}

// ── Fetch posts for table ─────────────────────────────────────────────────────
$posts = [];
try {
    $posts = $pdo->query("SELECT id,title,category,status,author,published_at,created_at FROM blog_posts ORDER BY created_at DESC")->fetchAll();
} catch(Exception $e) {}

// ── Fetch post for edit form ──────────────────────────────────────────────────
$editPost = null;
$editId   = (int)($_GET['edit'] ?? 0);
if ($editId) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM blog_posts WHERE id = ?");
        $stmt->execute([$editId]);
        $editPost = $stmt->fetch();
    } catch(Exception $e) {}
}

$isNew = isset($_GET['new']);

// ── Active tab ────────────────────────────────────────────────────────────────
$tab = $_GET['tab'] ?? 'posts';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>CMS Admin | CollegeKampus</title>
<meta name="robots" content="noindex,nofollow">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<style>
body{background:#f1f5f9;font-family:'Inter',sans-serif;}
.admin-sidebar{width:220px;min-height:100vh;background:#0f172a;position:fixed;top:0;left:0;z-index:100;padding-top:20px;}
.admin-sidebar .brand{color:#fff;font-size:1.1rem;font-weight:800;padding:0 20px 20px;border-bottom:1px solid rgba(255,255,255,.1);}
.admin-sidebar .nav-link{color:rgba(255,255,255,.7);font-size:.875rem;font-weight:500;padding:10px 20px;display:flex;align-items:center;gap:10px;transition:background .2s,color .2s;}
.admin-sidebar .nav-link:hover,.admin-sidebar .nav-link.active{background:rgba(255,255,255,.1);color:#fff;}
.admin-main{margin-left:220px;min-height:100vh;padding:28px;}
.stat-card{background:#fff;border-radius:12px;border:1px solid #e2e8f0;padding:22px;display:flex;align-items:center;gap:16px;}
.stat-icon{width:48px;height:48px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:1.4rem;flex-shrink:0;}
.stat-val{font-size:1.7rem;font-weight:800;color:#0f172a;line-height:1;}
.stat-lbl{font-size:.78rem;color:#64748b;font-weight:600;margin-top:3px;}
.admin-card{background:#fff;border-radius:12px;border:1px solid #e2e8f0;padding:24px;margin-top:24px;}
.table th{font-size:.78rem;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.05em;white-space:nowrap;}
.table td{font-size:.875rem;vertical-align:middle;}
.badge-published{background:#dcfce7;color:#16a34a;font-size:.72rem;font-weight:700;padding:3px 10px;border-radius:20px;}
.badge-draft{background:#fef9c3;color:#ca8a04;font-size:.72rem;font-weight:700;padding:3px 10px;border-radius:20px;}
.edit-form-panel{background:#eff6ff;border:2px solid #bfdbfe;border-radius:12px;padding:24px;margin-top:24px;}
.edit-form-panel h5{font-size:1rem;font-weight:800;color:#1d4ed8;margin-bottom:18px;}
textarea.content-area{font-family:monospace;font-size:.82rem;min-height:280px;}
</style>
</head>
<body>

<!-- Sidebar -->
<nav class="admin-sidebar">
    <div class="brand">🎓 CK Admin</div>
    <ul class="nav flex-column mt-2">
        <li class="nav-item"><a class="nav-link <?= $tab==='posts'?'active':'' ?>" href="?tab=posts"><i class="bi bi-journal-text"></i> Blog Posts</a></li>
        <li class="nav-item"><a class="nav-link <?= $tab==='programs'?'active':'' ?>" href="?tab=programs"><i class="bi bi-mortarboard"></i> Program Pages</a></li>
        <li class="nav-item"><a class="nav-link <?= $tab==='leads'?'active':'' ?>" href="?tab=leads"><i class="bi bi-people"></i> Leads</a></li>
        <li class="nav-item"><a class="nav-link" href="colleges.php"><i class="bi bi-bank"></i> Colleges</a></li>
        <li class="nav-item"><a class="nav-link <?= $tab==='generate'?'active':'' ?>" href="generate.php"><i class="bi bi-robot"></i> AI Generate</a></li>
        <li class="nav-item"><a class="nav-link" href="site-settings.php"><i class="bi bi-gear"></i> Site Settings</a></li>
        <li class="nav-item"><a class="nav-link" href="marketing.php"><i class="bi bi-megaphone"></i> Marketing &amp; Ads</a></li>
        <li class="nav-item mt-3"><a class="nav-link" href="/dashboard/"><i class="bi bi-house"></i> Front-end</a></li>
    </ul>
</nav>

<!-- Main -->
<div class="admin-main">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0 fw-800" style="font-weight:800;">CMS Dashboard</h4>
            <small class="text-muted">CollegeKampus Online · Admin Panel</small>
        </div>
        <a href="?tab=posts&new=1" class="btn btn-primary btn-sm fw-bold"><i class="bi bi-plus-lg"></i> New Post</a>
    </div>

    <?php if ($flash): ?>
    <div class="alert alert-<?= strpos($flash,'Error')!==false?'danger':'success' ?> alert-dismissible py-2 mb-4" role="alert">
        <?= htmlspecialchars($flash) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <!-- Stats -->
    <div class="row g-3 mb-2">
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-icon" style="background:#eff6ff;"><i class="bi bi-journal-text text-primary" style="font-size:1.3rem;"></i></div>
                <div><div class="stat-val"><?= $stats['posts_total'] ?></div><div class="stat-lbl">Total Posts</div></div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-icon" style="background:#f0fdf4;"><i class="bi bi-check-circle text-success" style="font-size:1.3rem;"></i></div>
                <div><div class="stat-val"><?= $stats['posts_published'] ?></div><div class="stat-lbl">Published</div></div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-icon" style="background:#fef9c3;"><i class="bi bi-pencil text-warning" style="font-size:1.3rem;"></i></div>
                <div><div class="stat-val"><?= $stats['posts_draft'] ?></div><div class="stat-lbl">Drafts</div></div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-icon" style="background:#fdf4ff;"><i class="bi bi-people text-purple" style="color:#7c3aed;font-size:1.3rem;"></i></div>
                <div><div class="stat-val"><?= $stats['leads_7d'] ?></div><div class="stat-lbl">Leads (7d)</div></div>
            </div>
        </div>
    </div>

    <?php if ($tab === 'posts'): ?>
    <!-- Blog Posts tab -->
    <div class="admin-card">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0 fw-bold">Blog Posts</h5>
            <a href="?tab=posts&new=1" class="btn btn-sm btn-outline-primary fw-bold"><i class="bi bi-plus"></i> New Post</a>
        </div>
        <?php if (empty($posts)): ?>
        <p class="text-muted text-center py-4">No posts yet. <a href="?tab=posts&new=1">Create your first post →</a></p>
        <?php else: ?>
        <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>#</th><th>Title</th><th>Category</th><th>Status</th><th>Author</th><th>Date</th><th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($posts as $p): ?>
            <tr>
                <td class="text-muted" style="font-size:.78rem;"><?= $p['id'] ?></td>
                <td style="max-width:280px;">
                    <div style="font-weight:600;line-height:1.3;"><?= htmlspecialchars(mb_strimwidth($p['title'],0,60,'…')) ?></div>
                </td>
                <td><?= htmlspecialchars($p['category'] ?? '—') ?></td>
                <td><span class="badge-<?= $p['status'] ?>"><?= ucfirst($p['status']) ?></span></td>
                <td><?= htmlspecialchars($p['author'] ?? '') ?></td>
                <td><?= $p['published_at'] ? date('d M Y', strtotime($p['published_at'])) : date('d M Y', strtotime($p['created_at'])) ?></td>
                <td>
                    <a href="?tab=posts&edit=<?= $p['id'] ?>" class="btn btn-xs btn-outline-secondary btn-sm me-1" title="Edit"><i class="bi bi-pencil"></i></a>
                    <a href="/dashboard/blog-post.php?slug=<?= urlencode($p['slug'] ?? '') ?>" target="_blank" class="btn btn-xs btn-outline-primary btn-sm me-1" title="View"><i class="bi bi-eye"></i></a>
                    <a href="save-blog.php?delete=<?= $p['id'] ?>"
                       onclick="return confirm('Delete this post?')"
                       class="btn btn-xs btn-outline-danger btn-sm" title="Delete"><i class="bi bi-trash"></i></a>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
        <?php endif; ?>
    </div>

    <!-- Edit/New Form -->
    <?php if ($editPost || $isNew): ?>
    <div class="edit-form-panel">
        <h5><?= $editPost ? '✏️ Edit Post: ' . htmlspecialchars(mb_strimwidth($editPost['title'],0,50,'…')) : '➕ New Blog Post' ?></h5>
        <form method="POST" action="save-blog.php">
            <?php if ($editPost): ?>
            <input type="hidden" name="id" value="<?= $editPost['id'] ?>">
            <?php endif; ?>
            <div class="row g-3">
                <div class="col-md-8">
                    <label class="form-label fw-bold">Title *</label>
                    <input type="text" name="title" class="form-control" required
                           value="<?= htmlspecialchars($editPost['title'] ?? '') ?>" placeholder="e.g. Best Online MBA Universities 2025">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Category</label>
                    <select name="category" class="form-select">
                        <?php foreach (['Online MBA','Online BCA','Online BBA','Admissions','Career Tips','University News'] as $c): ?>
                        <option value="<?= $c ?>" <?= ($editPost['category'] ?? '') === $c ? 'selected' : '' ?>><?= $c ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Slug (auto-generated if blank)</label>
                    <input type="text" name="slug" class="form-control" value="<?= htmlspecialchars($editPost['slug'] ?? '') ?>" placeholder="best-online-mba-2025">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Author</label>
                    <input type="text" name="author" class="form-control" value="<?= htmlspecialchars($editPost['author'] ?? 'CollegeKampus Team') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Status</label>
                    <select name="status" class="form-select">
                        <option value="draft" <?= ($editPost['status'] ?? 'draft') === 'draft' ? 'selected' : '' ?>>Draft</option>
                        <option value="published" <?= ($editPost['status'] ?? '') === 'published' ? 'selected' : '' ?>>Published</option>
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label fw-bold">Excerpt</label>
                    <textarea name="excerpt" class="form-control" rows="2" placeholder="Short description (150 chars)"><?= htmlspecialchars($editPost['excerpt'] ?? '') ?></textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Tags (comma separated)</label>
                    <input type="text" name="tags" class="form-control" value="<?= htmlspecialchars($editPost['tags'] ?? '') ?>" placeholder="Online MBA,UGC Approved">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Featured Image URL</label>
                    <input type="text" name="featured_image" class="form-control" value="<?= htmlspecialchars($editPost['featured_image'] ?? '') ?>" placeholder="https://...">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Meta Title</label>
                    <input type="text" name="meta_title" class="form-control" value="<?= htmlspecialchars($editPost['meta_title'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Meta Description</label>
                    <input type="text" name="meta_desc" class="form-control" value="<?= htmlspecialchars($editPost['meta_desc'] ?? '') ?>">
                </div>
                <div class="col-12">
                    <label class="form-label fw-bold">Content HTML *</label>
                    <textarea name="content_html" class="form-control content-area" required><?= htmlspecialchars($editPost['content_html'] ?? '') ?></textarea>
                    <small class="text-muted">Paste HTML content. Use H2/H3 for headings, table for comparisons.</small>
                </div>
                <div class="col-12 d-flex gap-2">
                    <button type="submit" class="btn btn-primary fw-bold"><i class="bi bi-save"></i> Save Post</button>
                    <a href="?tab=posts" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </div>
        </form>
    </div>
    <?php endif; ?>

    <?php elseif ($tab === 'programs'): ?>
    <!-- Program Pages tab -->
    <div class="admin-card">
        <h5 class="fw-bold mb-3">Program Pages</h5>
        <?php
        $progPages = [];
        try { $progPages = $pdo->query("SELECT id,slug,program_name,status,university_count,created_at FROM program_pages ORDER BY created_at DESC")->fetchAll(); } catch(Exception $e){}
        ?>
        <?php if (empty($progPages)): ?>
        <p class="text-muted text-center py-4">No program pages yet. Use <a href="generate.php">AI Generator</a> to create one.</p>
        <?php else: ?>
        <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light"><tr><th>#</th><th>Program</th><th>Slug</th><th>Status</th><th>Universities</th><th>Created</th></tr></thead>
            <tbody>
            <?php foreach ($progPages as $pp): ?>
            <tr>
                <td><?= $pp['id'] ?></td>
                <td><?= htmlspecialchars($pp['program_name']) ?></td>
                <td><code><?= htmlspecialchars($pp['slug']) ?></code></td>
                <td><span class="badge-<?= $pp['status'] ?>"><?= ucfirst($pp['status']) ?></span></td>
                <td><?= $pp['university_count'] ?></td>
                <td><?= date('d M Y', strtotime($pp['created_at'])) ?></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
        <?php endif; ?>
    </div>

    <?php elseif ($tab === 'leads'): ?>
    <!-- Leads tab -->
    <div class="admin-card">
        <h5 class="fw-bold mb-3">Recent Leads (Last 30 days)</h5>
        <?php
        $leads = [];
        try {
            $leads = $pdo->query("SELECT id,name,phone,email,source,page_url,created_at FROM online_leads WHERE created_at >= NOW() - INTERVAL 30 DAY ORDER BY created_at DESC LIMIT 100")->fetchAll();
        } catch(Exception $e) {}
        ?>
        <?php if (empty($leads)): ?>
        <p class="text-muted text-center py-4">No leads in the last 30 days.</p>
        <?php else: ?>
        <div class="table-responsive">
        <table class="table table-hover table-sm mb-0">
            <thead class="table-light"><tr><th>#</th><th>Name</th><th>Phone</th><th>Email</th><th>Source</th><th>Date</th></tr></thead>
            <tbody>
            <?php foreach ($leads as $lead): ?>
            <tr>
                <td><?= $lead['id'] ?></td>
                <td><?= htmlspecialchars($lead['name'] ?? '—') ?></td>
                <td><?= htmlspecialchars($lead['phone'] ?? '—') ?></td>
                <td><?= htmlspecialchars($lead['email'] ?? '—') ?></td>
                <td><span class="badge bg-secondary" style="font-size:.7rem;"><?= htmlspecialchars($lead['source'] ?? '') ?></span></td>
                <td><?= date('d M, H:i', strtotime($lead['created_at'])) ?></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
