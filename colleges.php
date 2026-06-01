<?php
$pageTitle    = 'Browse Online Colleges in India 2025 | Distance & Online Education';
$pageDesc     = 'Search and filter from 40,000+ online and distance learning colleges across India. Compare fees, courses, accreditation and apply free.';
$pageKeywords = 'online colleges India, distance education colleges, UGC approved online colleges, online MBA colleges, online BCA colleges 2025';
// Inject base URL for JS before any output
$extraHead = '<script>window.CK_BASE = ' . json_encode(rtrim(SITE_BASE, '/')) . ';</script>';

require_once __DIR__ . '/config/db.php';
include __DIR__ . '/includes/header.php';

// Fetch states for dropdown
$states = [];
try {
    $db = getDB();
    $stmt = $db->query("SHOW COLUMNS FROM colleges LIKE 'state'");
    if ($stmt->rowCount() > 0) {
        $stmt = $db->query("SELECT DISTINCT state FROM colleges WHERE state IS NOT NULL AND state != '' ORDER BY state");
        $states = array_column($stmt->fetchAll(), 'state');
    }
} catch (Throwable $e) {}
?>

<div class="page-wrapper">
  <div class="container">
    <div class="colleges-layout">
      <!-- Sidebar -->
      <aside class="filter-sidebar">
        <h3>&#x1F50D; Filter Colleges</h3>
        <form id="filterForm">
          <div class="filter-group">
            <label>Search</label>
            <input type="text" name="search" placeholder="College name, city..." autocomplete="off">
          </div>
          <?php if ($states): ?>
          <div class="filter-group">
            <label>State</label>
            <select name="state">
              <option value="">All States</option>
              <?php foreach ($states as $s): ?>
                <option value="<?= htmlspecialchars($s) ?>"><?= htmlspecialchars($s) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <?php endif; ?>
          <div class="filter-group">
            <label>College Type</label>
            <label class="check-item"><input type="checkbox" name="type" value="private"> Private</label>
            <label class="check-item"><input type="checkbox" name="type" value="government"> Government</label>
            <label class="check-item"><input type="checkbox" name="type" value="deemed"> Deemed</label>
            <label class="check-item"><input type="checkbox" name="type" value="autonomous"> Autonomous</label>
          </div>
          <div class="filter-group">
            <label>Fees Range (₹)</label>
            <div class="fees-row">
              <input type="number" name="min_fees" placeholder="Min" min="0" step="1000">
              <input type="number" name="max_fees" placeholder="Max" min="0" step="1000">
            </div>
          </div>
          <button type="button" class="btn-filter-reset" id="resetFilters">Reset Filters</button>
        </form>
      </aside>

      <!-- Main content -->
      <main class="colleges-main">
        <div class="colleges-toolbar">
          <span class="result-count" id="resultCount">Loading...</span>
        </div>
        <div class="colleges-grid" id="collegesGrid">
          <!-- Loaded via JS -->
        </div>
        <div class="pagination-wrap" id="paginationWrap"></div>
      </main>
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

<?php include __DIR__ . '/includes/footer.php'; ?>
