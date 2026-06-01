<?php
$pageTitle = 'Browse Courses';
$pageDesc  = 'Explore online and distance courses across engineering, management, medical, law, arts and more.';
require_once __DIR__ . '/config/db.php';
if (!isset($extraHead)) $extraHead = '';
$extraHead .= '<script>window.CK_BASE = ' . json_encode(rtrim(SITE_BASE, '/')) . ';</script>';

$categories = [
    ['key'=>'Engineering','icon'=>'&#9881;','bg'=>'#3b82f6'],
    ['key'=>'Management','icon'=>'&#128200;','bg'=>'#8b5cf6'],
    ['key'=>'Medical','icon'=>'&#10084;','bg'=>'#ef4444'],
    ['key'=>'Law','icon'=>'&#9878;','bg'=>'#f97316'],
    ['key'=>'Arts','icon'=>'&#127912;','bg'=>'#ec4899'],
    ['key'=>'Science','icon'=>'&#128300;','bg'=>'#06b6d4'],
    ['key'=>'Commerce','icon'=>'&#128181;','bg'=>'#22c55e'],
    ['key'=>'Design','icon'=>'&#127775;','bg'=>'#a855f7'],
];

$activeCategory = trim($_GET['category'] ?? '');
$courses = [];

try {
    $db = getDB();
    $crCols = [];
    $stmt = $db->query("SHOW COLUMNS FROM courses");
    foreach ($stmt->fetchAll() as $r) $crCols[] = $r['Field'];

    $select = "cr.id, cr.name";
    if (in_array('category', $crCols)) $select .= ", cr.category";
    if (in_array('degree_level', $crCols)) $select .= ", cr.degree_level";
    if (in_array('duration', $crCols)) $select .= ", cr.duration";

    // Count colleges per course
    $countSub = '';
    try {
        $db->query("SELECT 1 FROM college_courses LIMIT 1");
        $countSub = ", (SELECT COUNT(*) FROM college_courses cc WHERE cc.course_id = cr.id) AS college_count";
    } catch (Throwable $e) {}

    $where = [];
    $params = [];
    if ($activeCategory !== '') {
        if (in_array('category', $crCols)) { $where[] = "cr.category = ?"; $params[] = $activeCategory; }
    }
    if (in_array('is_active', $crCols)) $where[] = "cr.is_active = 1";

    $whereStr = $where ? 'WHERE ' . implode(' AND ', $where) : '';
    $sql = "SELECT $select $countSub FROM courses cr $whereStr ORDER BY cr.name LIMIT 200";
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $courses = $stmt->fetchAll();
} catch (Throwable $e) {}

include __DIR__ . '/includes/header.php';
?>

<div class="page-wrapper">
  <div class="courses-hero">
    <div class="container">
      <h1>Explore Online Courses</h1>
      <p>Find the right course from 40,000+ colleges across India</p>
    </div>
  </div>

  <div class="container">
    <!-- Category Tabs -->
    <div class="category-tabs">
      <a href="/courses.php" class="cat-tab <?= $activeCategory === '' ? 'active' : '' ?>">All Courses</a>
      <?php foreach ($categories as $cat): ?>
        <a href="/courses.php?category=<?= urlencode($cat['key']) ?>" class="cat-tab <?= $activeCategory === $cat['key'] ? 'active' : '' ?>">
          <?= $cat['icon'] ?> <?= htmlspecialchars($cat['key']) ?>
        </a>
      <?php endforeach; ?>
    </div>

    <?php if (empty($courses)): ?>
      <p style="text-align:center;padding:48px;color:#64748b;">No courses found. Please check back soon.</p>
    <?php else: ?>
    <div class="courses-grid">
      <?php
      $catBgMap = array_combine(array_column($categories,'key'), array_column($categories,'bg'));
      $catIconMap = array_combine(array_column($categories,'key'), array_column($categories,'icon'));
      foreach ($courses as $c):
        $cat = $c['category'] ?? '';
        $bg  = $catBgMap[$cat] ?? '#2563eb';
        $icon = $catIconMap[$cat] ?? '&#128218;';
        $colleges = isset($c['college_count']) ? $c['college_count'] . ' colleges' : '';
      ?>
      <div class="course-card" onclick="window.location='/colleges.php?course=<?= urlencode($c['name']) ?>'">
        <div class="course-cat-icon" style="background:<?= $bg ?>;"><?= $icon ?></div>
        <h4><?= htmlspecialchars($c['name']) ?></h4>
        <?php if (!empty($c['degree_level'])): ?>
        <div class="course-meta">&#127931; <?= htmlspecialchars($c['degree_level']) ?></div>
        <?php endif; ?>
        <?php if (!empty($c['duration'])): ?>
        <div class="course-meta">&#128337; <?= htmlspecialchars($c['duration']) ?></div>
        <?php endif; ?>
        <?php if ($colleges): ?>
        <div class="course-colleges">&#127979; <?= $colleges ?></div>
        <?php endif; ?>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
  <div style="height:48px;"></div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
