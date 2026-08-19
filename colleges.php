<?php
$pageTitle    = 'Browse Online Colleges in India 2025 | Distance & Online Education';
$pageDesc     = 'Search and filter from 50+ UGC-approved online and distance learning universities across India. Compare fees, courses, accreditation and apply free.';
$pageKeywords = 'online colleges India, distance education colleges, UGC approved online colleges, online MBA colleges, online BCA colleges 2025';

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/streams-schema.php';
require_once __DIR__ . '/includes/colleges-seed.php';

// Fetch states for dropdown
$states = [];
try {
    $db = getDB();
    $stmt = $db->query("SELECT DISTINCT state FROM colleges WHERE state IS NOT NULL AND state != '' ORDER BY state");
    $states = array_column($stmt->fetchAll(), 'state');
} catch (Throwable $e) {}

// Real counts for the hero stats (was hardcoded "500+" for both, which
// didn't match the actual seeded online-college dataset).
$onlineCollegeCount = 0;
$onlineProgramCount = 0;
try {
    $db = $db ?? getDB();
    collegesEnsureSeed($db);
    streamsEnsureSchema($db);
    $onlineCollegeCount = (int) $db->query("SELECT COUNT(*) FROM colleges WHERE is_online = 1")->fetchColumn();
    $onlineProgramCount = (int) $db->query("
        SELECT COUNT(DISTINCT cs.stream_id)
        FROM college_streams cs
        JOIN colleges c ON c.id = cs.college_id
        WHERE c.is_online = 1
    ")->fetchColumn();
} catch (Throwable $e) {}

// Pre-read URL params to pre-fill filters
$urlSearch       = trim($_GET['search'] ?? '');
$urlState        = trim($_GET['state'] ?? '');
$urlType         = trim($_GET['type'] ?? '');
$urlCourse       = trim($_GET['course'] ?? '');
$urlDelivery     = trim($_GET['delivery_mode'] ?? '');
$urlSort         = trim($_GET['sort'] ?? '');
$urlFeatured     = trim($_GET['featured'] ?? '');
$urlNaac         = trim($_GET['naac'] ?? '');

include __DIR__ . '/includes/header.php';
?>

<style>
.colleges-page-hero{background:linear-gradient(135deg,#0f172a 0%,#1e3a5f 100%);padding:40px 0 30px;color:#fff;}
.colleges-page-hero h1{font-size:clamp(1.4rem,3vw,2rem);font-weight:800;margin-bottom:6px;}
.colleges-page-hero p{color:rgba(255,255,255,.7);font-size:.9rem;}
.hero-stats{display:flex;gap:24px;flex-wrap:wrap;margin-top:16px;}
.hero-stat{display:flex;align-items:center;gap:8px;font-size:.85rem;color:rgba(255,255,255,.8);}
.hero-stat strong{color:#22c55e;font-size:1rem;}
.ck-toolbar{background:#fff;border-bottom:1px solid #e2e8f0;padding:12px 0;position:sticky;top:64px;z-index:100;}
.sort-select{padding:7px 32px 7px 12px;border:1px solid #e2e8f0;border-radius:8px;font-size:.85rem;font-weight:500;color:#0f172a;background:#fff;cursor:pointer;appearance:auto;}
.result-count{font-size:.9rem;color:#64748b;font-weight:500;}
.mode-tabs{display:flex;gap:4px;flex-wrap:wrap;}
.mode-tab{padding:6px 14px;border:1px solid #e2e8f0;border-radius:20px;font-size:.8rem;font-weight:600;cursor:pointer;background:#fff;color:#374151;transition:all .2s;white-space:nowrap;}
.mode-tab:hover,.mode-tab.active{background:#2563eb;color:#fff;border-color:#2563eb;}
.filter-section-label{font-size:.72rem;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.06em;margin-bottom:8px;display:block;}
.filter-section{margin-bottom:18px;padding-bottom:18px;border-bottom:1px solid #f1f5f9;}
.filter-section:last-child{border-bottom:none;margin-bottom:0;}
.ck-radio-item,.ck-check-item{display:flex;align-items:center;gap:8px;padding:6px 0;font-size:.85rem;color:#374151;cursor:pointer;}
.ck-radio-item input,.ck-check-item input{accent-color:#2563eb;width:15px;height:15px;flex-shrink:0;}
.college-card-header{padding:16px 20px 0;display:flex;align-items:center;gap:12px;}
.college-logo-img{width:48px;height:48px;border-radius:8px;object-fit:contain;border:1px solid #e2e8f0;background:#f8fafc;flex-shrink:0;}
.college-logo-abbr{width:48px;height:48px;border-radius:8px;background:#eff6ff;display:flex;align-items:center;justify-content:center;font-size:.8rem;font-weight:800;color:#2563eb;flex-shrink:0;}
.badge-naac{background:#f0fdf4;color:#16a34a;}
.badge-nirf{background:#eff6ff;color:#2563eb;}
.badge-ugc{background:#fef3c7;color:#d97706;}
.badge-online{background:#e0f2fe;color:#0369a1;}
.badge-hybrid{background:#f0fdf4;color:#16a34a;}
.badge-autonomous{background:#f3f4f6;color:#6b7280;}
.college-stats{display:flex;gap:12px;flex-wrap:wrap;margin-top:10px;}
.college-stat-item{font-size:.78rem;color:#64748b;display:flex;align-items:center;gap:4px;}
.college-stat-item strong{color:#0f172a;font-weight:600;}
.skeleton{background:linear-gradient(90deg,#f1f5f9 25%,#e2e8f0 50%,#f1f5f9 75%);background-size:200% 100%;animation:shimmer 1.5s infinite;}
.skeleton-card{height:220px;border-radius:14px;margin-bottom:4px;}
@keyframes shimmer{0%{background-position:200% 0}100%{background-position:-200% 0}}
</style>

<!-- Hero -->
<div class="colleges-page-hero">
  <div class="container">
    <h1>&#127979; Online &amp; Distance Colleges in India</h1>
    <p>Discover, compare and apply to top universities offering online degrees</p>
    <div class="hero-stats">
      <div class="hero-stat"><strong><?= $onlineCollegeCount > 0 ? $onlineCollegeCount . '+' : '50+' ?></strong> Online Universities</div>
      <div class="hero-stat"><strong><?= $onlineProgramCount > 0 ? $onlineProgramCount . '+' : '25+' ?></strong> Online Programs</div>
      <div class="hero-stat"><strong>UGC</strong> Approved Only</div>
      <div class="hero-stat"><strong>Free</strong> Counselling</div>
    </div>
  </div>
</div>

<!-- Sticky Toolbar -->
<div class="ck-toolbar">
  <div class="container d-flex align-items-center justify-content-between gap-3 flex-wrap">
    <div class="d-flex align-items-center gap-3 flex-wrap">
      <span class="result-count" id="resultCount">Loading colleges...</span>
      <div class="mode-tabs" id="modeTabs">
        <button class="mode-tab <?= $urlDelivery===''?'active':'' ?>" onclick="setMode('')">All</button>
        <button class="mode-tab <?= $urlDelivery==='online'?'active':'' ?>" onclick="setMode('online')">Online</button>
        <button class="mode-tab <?= $urlDelivery==='hybrid'?'active':'' ?>" onclick="setMode('hybrid')">Hybrid</button>
        <button class="mode-tab <?= $urlDelivery==='regular'?'active':'' ?>" onclick="setMode('regular')">Regular</button>
      </div>
    </div>
    <div class="d-flex align-items-center gap-2">
      <label class="result-count mb-0">Sort:</label>
      <select class="sort-select" id="sortSelect" onchange="applySort(this.value)">
        <option value="" <?= $urlSort===''?'selected':'' ?>>Default</option>
        <option value="nirf" <?= $urlSort==='nirf'?'selected':'' ?>>NIRF Rank</option>
        <option value="naac" <?= $urlSort==='naac'?'selected':'' ?>>NAAC Grade</option>
        <option value="fees_low" <?= $urlSort==='fees_low'?'selected':'' ?>>Fees: Low to High</option>
        <option value="fees_high" <?= $urlSort==='fees_high'?'selected':'' ?>>Fees: High to Low</option>
        <option value="rating" <?= $urlSort==='rating'?'selected':'' ?>>Rating</option>
      </select>
    </div>
  </div>
</div>

<div class="page-wrapper" style="margin-top:0;">
  <div class="container">
    <div class="colleges-layout">

      <!-- Sidebar Filters -->
      <aside class="filter-sidebar">
        <div class="d-flex align-items-center justify-content-between mb-3">
          <h3 class="mb-0" style="font-size:.95rem;font-weight:700;color:#0f172a;">&#x1F50D; Filters</h3>
          <button class="btn-filter-reset" id="resetFilters" style="width:auto;padding:4px 12px;margin:0;">Reset</button>
        </div>
        <form id="filterForm">
          <input type="hidden" name="delivery_mode" id="deliveryModeHidden" value="<?= htmlspecialchars($urlDelivery) ?>">
          <input type="hidden" name="sort" id="sortHidden" value="<?= htmlspecialchars($urlSort) ?>">

          <div class="filter-section">
            <span class="filter-section-label">Search</span>
            <div class="filter-group" style="margin:0;padding:0;border:none;">
              <input type="text" name="search" id="searchInput" placeholder="College name, city..." autocomplete="off" value="<?= htmlspecialchars($urlSearch) ?>">
            </div>
          </div>

          <div class="filter-section">
            <span class="filter-section-label">State</span>
            <div class="filter-group" style="margin:0;padding:0;border:none;">
              <select name="state" id="stateSelect">
                <option value="">All States</option>
                <?php foreach ($states as $s): ?>
                  <option value="<?= htmlspecialchars($s) ?>" <?= $urlState===$s?'selected':'' ?>><?= htmlspecialchars($s) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <div class="filter-section">
            <span class="filter-section-label">College Type</span>
            <?php
            $types = [['government','Government'],['private','Private'],['deemed','Deemed'],['autonomous','Autonomous']];
            $urlTypes = $urlType ? explode(',', $urlType) : [];
            foreach ($types as [$val, $label]):
            ?>
            <label class="ck-check-item">
              <input type="checkbox" name="type" value="<?= $val ?>" <?= in_array($val,$urlTypes)?'checked':'' ?>>
              <?= $label ?>
            </label>
            <?php endforeach; ?>
          </div>

          <div class="filter-section">
            <span class="filter-section-label">NAAC Grade</span>
            <label class="ck-radio-item"><input type="radio" name="naac" value="" <?= $urlNaac===''?'checked':'' ?>> Any Grade</label>
            <?php foreach (['A++','A+','A','B++','B+','B'] as $g): ?>
            <label class="ck-radio-item"><input type="radio" name="naac" value="<?= $g ?>" <?= $urlNaac===$g?'checked':'' ?>> NAAC <?= $g ?></label>
            <?php endforeach; ?>
          </div>

          <div class="filter-section">
            <span class="filter-section-label">Fees Range (&#8377;)</span>
            <div class="filter-group fees-row" style="margin:0;padding:0;border:none;">
              <input type="number" name="min_fees" placeholder="Min" min="0" step="5000">
              <input type="number" name="max_fees" placeholder="Max" min="0" step="5000">
            </div>
          </div>
        </form>
      </aside>

      <!-- Main -->
      <div class="colleges-main">
        <?php if ($urlCourse): ?>
        <div style="background:#eff6ff;border:1px solid #dbeafe;border-radius:10px;padding:12px 16px;margin-bottom:16px;display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;">
          <span style="font-size:.9rem;color:#1d4ed8;font-weight:600;">&#128218; Showing colleges for: <strong><?= htmlspecialchars($urlCourse) ?></strong></span>
          <a href="<?= $base ?>/colleges.php" style="font-size:.8rem;color:#64748b;text-decoration:none;">Clear &times;</a>
        </div>
        <?php endif; ?>

        <div class="colleges-grid" id="collegesGrid">
          <?php for($i=0;$i<6;$i++): ?>
          <div class="skeleton skeleton-card"></div>
          <?php endfor; ?>
        </div>
        <div class="pagination-wrap" id="paginationWrap"></div>
      </div>

    </div>
  </div>
</div>

<!-- Compare Bar -->
<div class="compare-bar" id="compareBar">
  <span class="compare-bar-label">Compare:</span>
  <div class="compare-chips" id="compareChips"></div>
  <button class="btn-go-compare" onclick="goToCompare()">Compare Now</button>
  <button class="btn-clear-compare" onclick="clearCompare()">Clear</button>
</div>

<script>
// Pre-fill from URL and init
(function(){
  var urlParams = new URLSearchParams(window.location.search);

  // Apply URL params to form on load
  function getFiltersFromForm(){
    var form = document.getElementById('filterForm');
    if(!form) return {};
    var fd = new FormData(form);
    var f = {};
    for(var pair of fd.entries()){
      if(f[pair[0]]){ f[pair[0]] = [].concat(f[pair[0]], pair[1]); }
      else { f[pair[0]] = pair[1]; }
    }
    Object.keys(f).forEach(function(k){ if(Array.isArray(f[k])) f[k] = f[k].join(','); });
    // Add course from URL if present
    var course = urlParams.get('course');
    if(course) f['course'] = course;
    return f;
  }

  window.setMode = function(mode){
    document.getElementById('deliveryModeHidden').value = mode;
    document.querySelectorAll('.mode-tab').forEach(function(t){ t.classList.toggle('active', t.getAttribute('onclick') === "setMode('"+mode+"')"); });
    loadColleges(getFiltersFromForm(), 1);
  };

  window.applySort = function(val){
    document.getElementById('sortHidden').value = val;
    loadColleges(getFiltersFromForm(), 1);
  };

  document.addEventListener('DOMContentLoaded', function(){
    var form = document.getElementById('filterForm');
    if(form){
      form.addEventListener('input', function(){
        clearTimeout(window._debounce);
        window._debounce = setTimeout(function(){ loadColleges(getFiltersFromForm(), 1); }, 400);
      });
      form.addEventListener('change', function(){
        clearTimeout(window._debounce);
        window._debounce = setTimeout(function(){ loadColleges(getFiltersFromForm(), 1); }, 200);
      });
      var resetBtn = document.getElementById('resetFilters');
      if(resetBtn) resetBtn.addEventListener('click', function(){
        form.reset();
        document.getElementById('deliveryModeHidden').value = '';
        document.getElementById('sortHidden').value = '';
        document.querySelectorAll('.mode-tab').forEach(function(t){ t.classList.remove('active'); });
        document.querySelector('.mode-tab').classList.add('active');
        loadColleges({}, 1);
      });
    }
    loadColleges(getFiltersFromForm(), 1);
  });
})();
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
