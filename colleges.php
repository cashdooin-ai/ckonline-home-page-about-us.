<?php
// INTEGRATION: Admin portal reads this DB. Partner portal writes to colleges/courses tables.
// Shared DB: collegekampus -- see database/schema.sql

require_once __DIR__ . '/config/db.php';

$pageTitle = 'Browse Colleges';
$pageDesc  = 'Explore top colleges in India. Filter by state, type and course category.';

$pdo    = getPDO();
$states = $pdo->query("SELECT DISTINCT state FROM colleges WHERE is_active=1 ORDER BY state")->fetchAll(PDO::FETCH_COLUMN);
$categories = ['engineering','management','medical','law','arts','science','commerce','design','other'];

$initSearch  = htmlspecialchars(trim($_GET['search']   ?? ''));
$initState   = htmlspecialchars(trim($_GET['state']    ?? ''));
$initType    = htmlspecialchars(trim($_GET['type']     ?? ''));
$initCat     = htmlspecialchars(trim($_GET['category'] ?? ''));

include __DIR__ . '/includes/header.php';
?>
<main id="main-content">

<section class="ck-page-hero">
    <div class="container">
        <h1 class="mb-2"><i class="bi bi-mortarboard me-2"></i>Find Your Dream College</h1>
        <p class="mb-0">Browse <?= $pdo->query("SELECT COUNT(*) FROM colleges WHERE is_active=1")->fetchColumn() ?>+ colleges across India. Filter by state, type, course and fees.</p>
    </div>
</section>

<div class="container py-4">
    <div class="row mb-4">
        <div class="col-md-8 mx-auto">
            <div class="ck-search-wrap">
                <i class="bi bi-search ck-search-icon"></i>
                <input type="search" id="ck-search-input" class="form-control form-control-lg"
                       placeholder="Search colleges by name, city or state..." value="<?= $initSearch ?>" autocomplete="off">
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-3">
            <form id="ck-filter-form" class="ck-filter-card">
                <h6>College Type</h6>
                <div class="mb-3">
                    <?php foreach (['government'=>'Government','private'=>'Private','deemed'=>'Deemed'] as $val=>$lbl): ?>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="type" value="<?= $val ?>" id="type-<?= $val ?>" <?= $initType===$val?'checked':'' ?>>
                        <label class="form-check-label" for="type-<?= $val ?>"><?= $lbl ?></label>
                    </div>
                    <?php endforeach; ?>
                </div>

                <h6>State</h6>
                <div class="mb-3">
                    <select name="state" class="form-select form-select-sm">
                        <option value="">All States</option>
                        <?php foreach ($states as $s): ?>
                        <option value="<?= htmlspecialchars($s) ?>" <?= $initState===$s?'selected':'' ?>><?= htmlspecialchars($s) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <h6>Course Category</h6>
                <div class="mb-3">
                    <?php foreach ($categories as $cat): ?>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="category" value="<?= $cat ?>" id="cat-<?= $cat ?>" <?= $initCat===$cat?'checked':'' ?>>
                        <label class="form-check-label" for="cat-<?= $cat ?>"><?= ucfirst($cat) ?></label>
                    </div>
                    <?php endforeach; ?>
                </div>

                <h6>Max Annual Fees</h6>
                <div class="mb-3">
                    <input type="range" class="form-range ck-fee-range" id="fee-range" name="max_fee"
                           min="50000" max="1000000" step="50000" value="1000000">
                    <div class="d-flex justify-content-between small text-muted">
                        <span>&#8377;50K</span>
                        <span id="fee-range-label" class="fw-semibold text-dark">Any</span>
                        <span>&#8377;10L+</span>
                    </div>
                </div>

                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="featured" value="1" id="chk-featured">
                    <label class="form-check-label" for="chk-featured"><i class="bi bi-star-fill text-warning me-1"></i>Top Ranked Only</label>
                </div>

                <button type="button" class="btn btn-outline-secondary btn-sm w-100 mt-3"
                    onclick="document.getElementById('ck-filter-form').reset(); window.location.href='/colleges.php';">
                    <i class="bi bi-arrow-counterclockwise me-1"></i>Reset Filters
                </button>
            </form>
        </div>

        <div class="col-lg-9">
            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                <p class="mb-0 text-muted">Showing <strong id="ck-colleges-count">...</strong> colleges</p>
            </div>
            <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-3" id="ck-college-grid">
                <div class="col-12"><div class="ck-spinner"></div></div>
            </div>
            <div id="ck-pagination" class="mt-4"></div>
        </div>
    </div>
</div>
</main>
<?php include __DIR__ . '/includes/footer.php'; ?>
