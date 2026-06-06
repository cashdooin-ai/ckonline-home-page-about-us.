<?php
require_once __DIR__ . '/auth.php';
require_once dirname(__DIR__) . '/config/db.php';
$pdo = getDB();

// ── DELETE ────────────────────────────────────────────────────────────────────
if (isset($_GET['delete'])) {
    $delId = (int)$_GET['delete'];
    try {
        $pdo->prepare("DELETE FROM blog_posts WHERE id = ?")->execute([$delId]);
        $_SESSION['flash'] = 'Post deleted successfully.';
    } catch(Exception $e) {
        $_SESSION['flash'] = 'Error deleting post: ' . $e->getMessage();
    }
    header('Location: /dashboard/admin/index.php?tab=posts');
    exit;
}

// ── Only accept POST ──────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /dashboard/admin/index.php');
    exit;
}

// ── Validation ────────────────────────────────────────────────────────────────
$title   = trim($_POST['title'] ?? '');
$content = trim($_POST['content_html'] ?? '');

if (!$title) {
    $_SESSION['flash'] = 'Error: Title is required.';
    header('Location: /dashboard/admin/index.php?tab=posts');
    exit;
}
if (!$content) {
    $_SESSION['flash'] = 'Error: Content is required.';
    header('Location: /dashboard/admin/index.php?tab=posts');
    exit;
}

// ── Slug generation ───────────────────────────────────────────────────────────
$slug = trim($_POST['slug'] ?? '');
if (!$slug) {
    $slug = strtolower($title);
    $slug = preg_replace('/[^a-z0-9\s\-]/', '', $slug);
    $slug = preg_replace('/[\s\-]+/', '-', $slug);
    $slug = trim($slug, '-');
    $slug = substr($slug, 0, 280);
}

// ── Build data ────────────────────────────────────────────────────────────────
$id       = (int)($_POST['id'] ?? 0);
$category = trim($_POST['category'] ?? 'Online MBA');
$excerpt  = trim($_POST['excerpt'] ?? '');
$author   = trim($_POST['author'] ?? 'CollegeKampus Team');
$status   = in_array($_POST['status'] ?? '', ['draft','published']) ? $_POST['status'] : 'draft';
$tags     = trim($_POST['tags'] ?? '');
$metaTitle= trim($_POST['meta_title'] ?? '');
$metaDesc = trim($_POST['meta_desc'] ?? '');
$featImg  = trim($_POST['featured_image'] ?? '');
$publishedAt = ($status === 'published') ? date('Y-m-d H:i:s') : null;

try {
    if ($id) {
        // Update existing
        $stmt = $pdo->prepare("
            UPDATE blog_posts SET
                title=?, slug=?, category=?, excerpt=?, content_html=?,
                author=?, featured_image=?, meta_title=?, meta_desc=?, tags=?,
                status=?, published_at=COALESCE(published_at, ?), updated_at=NOW()
            WHERE id=?
        ");
        $stmt->execute([$title,$slug,$category,$excerpt,$content,$author,$featImg,$metaTitle,$metaDesc,$tags,$status,$publishedAt,$id]);
        $_SESSION['flash'] = 'Post updated successfully!';
    } else {
        // Insert new
        $stmt = $pdo->prepare("
            INSERT INTO blog_posts
                (title,slug,category,excerpt,content_html,author,featured_image,meta_title,meta_desc,tags,status,published_at,created_at)
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?,NOW())
        ");
        $stmt->execute([$title,$slug,$category,$excerpt,$content,$author,$featImg,$metaTitle,$metaDesc,$tags,$status,$publishedAt]);
        $_SESSION['flash'] = 'Post created successfully!';
    }
} catch(Exception $e) {
    // Handle duplicate slug
    if (strpos($e->getMessage(), 'Duplicate') !== false || $e->getCode() == 23000) {
        $slug .= '-' . time();
        try {
            if ($id) {
                $pdo->prepare("UPDATE blog_posts SET slug=? WHERE id=?")->execute([$slug,$id]);
                $_SESSION['flash'] = 'Post saved (slug auto-adjusted to avoid duplicate).';
            } else {
                $stmt = $pdo->prepare("
                    INSERT INTO blog_posts
                        (title,slug,category,excerpt,content_html,author,featured_image,meta_title,meta_desc,tags,status,published_at,created_at)
                    VALUES (?,?,?,?,?,?,?,?,?,?,?,?,NOW())
                ");
                $stmt->execute([$title,$slug,$category,$excerpt,$content,$author,$featImg,$metaTitle,$metaDesc,$tags,$status,$publishedAt]);
                $_SESSION['flash'] = 'Post created (slug auto-adjusted to avoid duplicate).';
            }
        } catch(Exception $e2) {
            $_SESSION['flash'] = 'Error saving post: ' . $e2->getMessage();
        }
    } else {
        $_SESSION['flash'] = 'Error saving post: ' . $e->getMessage();
    }
}

header('Location: /dashboard/admin/index.php?tab=posts');
exit;
