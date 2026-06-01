<?php
// INTEGRATION: Admin portal reads this DB. Partner portal writes to colleges/courses tables.
// Shared DB: collegekampus -- see database/schema.sql

require_once __DIR__ . '/config/db.php';

$pageTitle = 'Browse Courses';
$pageDesc  = 'Explore all courses available across top colleges in India.';

$pdo = getPDO();

$filterCat = trim($_GET['category'] ?? '');
$allowedCats = ['engineering','management','medical','law','arts','science','commerce','design','other'];
if ($filterCat && !in_array($filterCat, $allowedCats)) $filterCat = '';

$catIcons = ['engineering'=>'bi-cpu','management'=>'bi-briefcase','medical'=>'bi-heart-pulse','law'=>'bi-balance','arts'=>'bi-palette','science'=>'bi-flask','commerce'=>'bi-graph-up-arrow','design'=>'bi-pen','other'=>'bi-mortarboard'];

$where  = 'WHERE cr.is_active = 1';
$params = [];
if ($filterCat) { $where .= ' AND cr.category = :cat'; $params[':cat'] = $filterCat; }

$stmt = $pdo->prepare("
    SELECT cr.*, COUNT(DISTINCT cc.college_id) AS college_count,
           MIN(cc.annual_fees) AS min_fees, MAX(cc.annual_fees) AS max_fees
    FROM courses cr
    LEFT JOIN college_courses cc ON cc.course_id = cr.id AND cc.is_active = 1
    $where
    GROUP BY cr.id
    ORDER BY cr.category, cr.degree_level, cr.name
");
foreach ($params as $k => $v) $stmt->bindValue($k, $v);
$stmt->execute();
$allCourses = $stmt->fetchAll();

$grouped = [];
foreach ($allCourses as $c) { $grouped[$c['category']][] = $c; }

function feeINR($n) { if (!$n) return 'N/A'; return '&#8377;' . number_format((float)$n, 0, '.', ','); }
function degreeLabel($d) { return ['ug'=>'UG','pg'=>'PG','phd'=>'PhD','diploma'=>'Diploma','certificate'=>'Certificate'][$d] ?? strtoupper($d); }

include __DIR__ . '/includes/header.php';
?>
<main id="main-content">

<section class="ck-page-hero">
    <div class="container">
        <h1 class="mb-2"><i class="bi bi-book me-2"></i>All Courses</h1>
        <p class="mb-0">Explore <?= count($allCourses) ?> courses across <?= count($grouped) ?> categories.</p>
    </div>
</section>

<div class="container py-4">
    <div class="d-flex flex-wrap gap-2 mb-4">
        <a href="/courses.php" class="btn btn-sm <?= !$filterCat ? 'btn-ck-primary' : 'btn-outline-secondary' ?>">All</a>
        <?php foreach ($allowedCats as $cat): ?>
        <a href="/courses.php?category=<?= $cat ?>"
           class="btn btn-sm <?= $filterCat===$cat ? 'btn-ck-primary' : 'btn-outline-secondary' ?>">
            <i class="bi <?= $catIcons[$cat] ?> me-1"></i><?= ucfirst($cat) ?>
        </a>
        <?php endforeach; ?>
    </div>

    <?php foreach ($grouped as $category => $courses): ?>
    <div class="mb-5">
        <div class="ck-category-header">
            <div class="ck-category-icon"><i class="bi <?= $catIcons[$category] ?? 'bi-mortarboard' ?>"></i></div>
            <div><h5 class="mb-0 fw-bold"><?= ucfirst($category) ?></h5><small class="opacity-75"><?= count($courses) ?> course<?= count($courses)!==1?'s':'' ?></small></div>
        </div>
        <?php foreach ($courses as $course): ?>
        <div class="ck-course-row">
            <div class="row align-items-center g-2">
                <div class="col-md-5">
                    <h6 class="fw-bold mb-1"><?= htmlspecialchars($course['name']) ?></h6>
                    <div class="d-flex gap-2 flex-wrap">
                        <span class="badge bg-secondary small"><?= degreeLabel($course['degree_level']) ?></span>
                        <span class="small text-muted"><i class="bi bi-clock me-1"></i><?= htmlspecialchars($course['duration_years']) ?> yr<?= $course['duration_years']!=1?'s':'' ?></span>
                    </div>
                    <?php if ($course['description']): ?>
                    <p class="small text-muted mb-0 mt-1"><?= htmlspecialchars(mb_strimwidth($course['description'], 0, 100, '...')) ?></p>
                    <?php endif; ?>
                </div>
                <div class="col-md-2 text-md-center">
                    <div class="fw-semibold text-success"><?= $course['college_count'] ?></div>
                    <div class="small text-muted">College<?= $course['college_count']!==1?'s':'' ?></div>
                </div>
                <div class="col-md-3">
                    <?php if ($course['min_fees']): ?>
                    <div class="small text-muted">Fees/yr</div>
                    <div class="fw-semibold">
                        <?= feeINR($course['min_fees']) ?>
                        <?php if ($course['max_fees'] && $course['max_fees'] != $course['min_fees']): ?> - <?= feeINR($course['max_fees']) ?><?php endif; ?>
                    </div>
                    <?php else: ?><span class="text-muted small">Fees vary</span><?php endif; ?>
                </div>
                <div class="col-md-2 text-md-end">
                    <a href="/colleges.php?category=<?= urlencode($category) ?>" class="btn btn-outline-primary btn-sm">View Colleges</a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endforeach; ?>
</div>
</main>
<?php include __DIR__ . '/includes/footer.php'; ?>
