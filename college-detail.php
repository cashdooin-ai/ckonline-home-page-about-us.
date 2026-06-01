<?php
require_once __DIR__ . '/config/db.php';
if (!isset($extraHead)) $extraHead = '';
$extraHead .= '<script>window.CK_BASE = ' . json_encode(rtrim(SITE_BASE, '/')) . ';</script>';

$id   = intval($_GET['id'] ?? 0);
$slug = trim($_GET['slug'] ?? '');

if (!$id && !$slug) {
    header('Location: /colleges.php');
    exit;
}

$college  = null;
$courses  = [];
$related  = [];

try {
    $db = getDB();

    // Detect columns
    $allCols = [];
    $stmt = $db->query("SHOW COLUMNS FROM colleges");
    foreach ($stmt->fetchAll() as $r) $allCols[] = $r['Field'];

    $select = "c.id, c.name";
    foreach (['city','state','type','min_fees','max_fees','established_year','accreditation','description','slug','logo_url','banner_url','total_seats','website'] as $col) {
        if (in_array($col, $allCols)) $select .= ", c.$col";
    }

    if ($id > 0) {
        $stmt = $db->prepare("SELECT $select FROM colleges c WHERE c.id = ?");
        $stmt->execute([$id]);
    } else {
        if (!in_array('slug', $allCols)) { header('Location: /colleges.php'); exit; }
        $stmt = $db->prepare("SELECT $select FROM colleges c WHERE c.slug = ?");
        $stmt->execute([$slug]);
    }
    $college = $stmt->fetch();

    if (!$college) {
        header('Location: /colleges.php');
        exit;
    }
    $id = $college['id'];

    // Fetch courses
    try {
        $ccCols = [];
        $st2 = $db->query("SHOW COLUMNS FROM college_courses");
        foreach ($st2->fetchAll() as $r) $ccCols[] = $r['Field'];

        $crCols = [];
        $st3 = $db->query("SHOW COLUMNS FROM courses");
        foreach ($st3->fetchAll() as $r) $crCols[] = $r['Field'];

        $crSelect = "cr.id, cr.name";
        if (in_array('duration', $crCols)) $crSelect .= ", cr.duration";
        if (in_array('degree_level', $crCols)) $crSelect .= ", cr.degree_level";

        if (in_array('fees', $ccCols)) $crSelect .= ", cc.fees";
        if (in_array('seats', $ccCols)) $crSelect .= ", cc.seats";
        if (in_array('admission_deadline', $ccCols)) $crSelect .= ", cc.admission_deadline";

        $stmt2 = $db->prepare("SELECT $crSelect FROM college_courses cc JOIN courses cr ON cr.id = cc.course_id WHERE cc.college_id = ? ORDER BY cr.name");
        $stmt2->execute([$id]);
        $courses = $stmt2->fetchAll();
    } catch (Throwable $e) {}

    // Related colleges (same state / type)
    try {
        $whereR = ["c.id != ?"];
        $paramsR = [$id];
        if (isset($college['state']) && $college['state']) {
            $whereR[] = "c.state = ?"; $paramsR[] = $college['state'];
        }
        if (in_array('is_active', $allCols)) $whereR[] = "c.is_active = 1";
        $stmt3 = $db->prepare("SELECT id, name, " . (in_array('city',$allCols)?'city,':'') . (in_array('state',$allCols)?'state,':'') . "type FROM colleges c WHERE " . implode(' AND ', $whereR) . " ORDER BY RAND() LIMIT 4");
        $stmt3->execute($paramsR);
        $related = $stmt3->fetchAll();
    } catch (Throwable $e) {}

} catch (Throwable $e) {
    die('Error: ' . htmlspecialchars($e->getMessage()));
}

$pageTitle = $college['name'];
include __DIR__ . '/includes/header.php';
?>

<div class="page-wrapper">
  <!-- College Hero -->
  <div class="college-hero">
    <div class="container">
      <div class="college-hero-inner">
        <div class="college-logo-placeholder">&#127891;</div>
        <div class="college-hero-info">
          <h1><?= htmlspecialchars($college['name']) ?></h1>
          <div class="college-hero-meta">
            <?php if (!empty($college['city']) || !empty($college['state'])): ?>
            <span>&#128205; <?= htmlspecialchars(trim(($college['city'] ?? '') . ', ' . ($college['state'] ?? ''), ', ')) ?></span>
            <?php endif; ?>
            <?php if (!empty($college['type'])): ?>
            <span>&#127979; <?= htmlspecialchars($college['type']) ?></span>
            <?php endif; ?>
            <?php if (!empty($college['established_year'])): ?>
            <span>&#128197; Est. <?= htmlspecialchars($college['established_year']) ?></span>
            <?php endif; ?>
            <?php if (!empty($college['accreditation'])): ?>
            <span class="accr-badge"><?= htmlspecialchars($college['accreditation']) ?></span>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="container">
    <div class="detail-layout">
      <!-- Tabs -->
      <div class="detail-tabs">
        <ul class="nav nav-tabs" id="detailTabs">
          <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#tabAbout">About</a></li>
          <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tabCourses">Courses &amp; Fees</a></li>
          <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tabAdmission">Admission</a></li>
        </ul>

        <div class="tab-content">
          <!-- About -->
          <div class="tab-pane fade show active" id="tabAbout">
            <?php if (!empty($college['description'])): ?>
              <p><?= nl2br(htmlspecialchars($college['description'])) ?></p>
            <?php else: ?>
              <p style="color:#64748b;">Detailed information about <?= htmlspecialchars($college['name']) ?> will be updated soon. Please contact us for more details or submit an inquiry.</p>
            <?php endif; ?>
            <?php if (!empty($related)): ?>
            <div class="related-section">
              <h3>Related Colleges</h3>
              <div class="related-grid">
                <?php foreach ($related as $r): ?>
                <a href="/college-detail.php?id=<?= $r['id'] ?>" class="related-card" style="text-decoration:none;">
                  <h5><?= htmlspecialchars($r['name']) ?></h5>
                  <p><?= htmlspecialchars(trim(($r['city'] ?? '') . ', ' . ($r['state'] ?? ''), ', ')) ?></p>
                </a>
                <?php endforeach; ?>
              </div>
            </div>
            <?php endif; ?>
          </div>

          <!-- Courses & Fees -->
          <div class="tab-pane fade" id="tabCourses">
            <?php if ($courses): ?>
            <div style="overflow-x:auto;">
              <table class="courses-table">
                <thead>
                  <tr>
                    <th>Course Name</th>
                    <th>Level</th>
                    <th>Duration</th>
                    <th>Fees</th>
                    <th>Seats</th>
                    <th>Deadline</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($courses as $c): ?>
                  <tr>
                    <td><?= htmlspecialchars($c['name']) ?></td>
                    <td><?= htmlspecialchars($c['degree_level'] ?? '—') ?></td>
                    <td><?= htmlspecialchars($c['duration'] ?? '—') ?></td>
                    <td><?= isset($c['fees']) ? '&#8377;' . number_format($c['fees']) : '—' ?></td>
                    <td><?= htmlspecialchars($c['seats'] ?? '—') ?></td>
                    <td><?= htmlspecialchars($c['admission_deadline'] ?? '—') ?></td>
                    <td><a href="/apply.php?college_id=<?= $college['id'] ?>&course_id=<?= $c['id'] ?>" class="btn-view" style="font-size:0.78rem;padding:6px 12px;">Apply</a></td>
                  </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
            <?php else: ?>
              <p style="color:#64748b;">Course details are being updated. Please submit an inquiry for the latest information.</p>
            <?php endif; ?>
          </div>

          <!-- Admission -->
          <div class="tab-pane fade" id="tabAdmission">
            <p>For admission enquiries, eligibility criteria and application process, please fill the inquiry form or contact our counsellors.</p>
            <a href="/apply.php?college_id=<?= $college['id'] ?>" class="btn-view" style="display:inline-block;margin-top:12px;padding:10px 24px;">Apply Now</a>
          </div>
        </div>
      </div>

      <!-- Inquiry Sidebar -->
      <aside class="inquiry-sidebar">
        <h3>Quick Inquiry</h3>
        <p>Get free counselling from our experts</p>
        <form id="inquiryForm">
          <input type="hidden" name="college_id" value="<?= $college['id'] ?>">
          <input type="hidden" name="source" value="college_detail">
          <div class="form-field">
            <label>Full Name *</label>
            <input type="text" name="full_name" placeholder="Your name" required>
          </div>
          <div class="form-field">
            <label>Email *</label>
            <input type="email" name="email" placeholder="your@email.com" required>
          </div>
          <div class="form-field">
            <label>Phone *</label>
            <input type="tel" name="phone" placeholder="10-digit mobile" required>
          </div>
          <div class="form-field">
            <label>Course Interest</label>
            <input type="text" name="course_interest" placeholder="e.g. MBA, BBA...">
          </div>
          <button type="submit" class="btn-submit" id="inquirySubmitBtn">Send Inquiry</button>
          <div id="inquiryMsg" style="margin-top:10px;font-size:0.82rem;"></div>
        </form>
      </aside>
    </div>
  </div>
</div>

<script>
document.getElementById('inquiryForm').addEventListener('submit', function(e) {
  e.preventDefault();
  const btn = document.getElementById('inquirySubmitBtn');
  btn.disabled = true; btn.textContent = 'Sending...';
  fetch('/api/leads.php', { method: 'POST', body: new FormData(this) })
    .then(r => r.json())
    .then(d => {
      const msg = document.getElementById('inquiryMsg');
      if (d.success) {
        msg.style.color = '#16a34a';
        msg.textContent = 'Thank you! We will contact you soon.';
        this.reset();
      } else {
        msg.style.color = '#dc2626';
        msg.textContent = d.message || 'Something went wrong.';
        btn.disabled = false; btn.textContent = 'Send Inquiry';
      }
    })
    .catch(() => {
      btn.disabled = false; btn.textContent = 'Send Inquiry';
    });
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
