<?php
$pageTitle = 'Apply Now';
$pageDesc  = 'Apply online to your chosen college in 3 easy steps.';
require_once __DIR__ . '/config/db.php';
if (!isset($extraHead)) $extraHead = '';
$extraHead .= '<script>window.CK_BASE = ' . json_encode(rtrim(SITE_BASE, '/')) . ';</script>';

$college_id = intval($_GET['college_id'] ?? 0);
$course_id  = intval($_GET['course_id'] ?? 0);

$college = null;
$course  = null;
if ($college_id) {
    try {
        $db = getDB();
        $stmt = $db->prepare("SELECT id, name FROM colleges WHERE id = ? LIMIT 1");
        $stmt->execute([$college_id]);
        $college = $stmt->fetch();
    } catch (Throwable $e) {}
}
if ($course_id) {
    try {
        $db = getDB();
        $stmt = $db->prepare("SELECT id, name FROM courses WHERE id = ? LIMIT 1");
        $stmt->execute([$course_id]);
        $course = $stmt->fetch();
    } catch (Throwable $e) {}
}

include __DIR__ . '/includes/header.php';
?>

<div class="page-wrapper">
  <div class="apply-hero">
    <div class="container">
      <h1>Apply Online</h1>
      <p><?= $college ? htmlspecialchars($college['name']) : 'Complete your application in 3 easy steps' ?></p>
    </div>
  </div>

  <div class="container" style="padding-top:0;">
    <!-- Step Progress -->
    <div class="step-progress">
      <div class="step-item active" id="stepIndicator1">
        <div class="step-num">1</div>
        <div class="step-label">Personal Info</div>
      </div>
      <div class="step-item" id="stepIndicator2">
        <div class="step-num">2</div>
        <div class="step-label">Academic Info</div>
      </div>
      <div class="step-item" id="stepIndicator3">
        <div class="step-num">3</div>
        <div class="step-label">Review &amp; Submit</div>
      </div>
    </div>

    <div id="applyFormWrap">
      <div class="apply-card">
        <form id="applyForm" novalidate>
          <input type="hidden" name="college_id" value="<?= $college_id ?>">
          <input type="hidden" name="course_id" value="<?= $course_id ?>">
          <input type="hidden" name="source" value="application">

          <!-- Step 1: Personal Info -->
          <div class="apply-step active" id="step1">
            <h3 style="font-size:1.1rem;font-weight:700;margin-bottom:20px;">Step 1: Personal Information</h3>
            <div class="form-row">
              <div class="form-field">
                <label>Full Name *</label>
                <input type="text" name="full_name" placeholder="Your full name" required>
              </div>
              <div class="form-field">
                <label>Date of Birth</label>
                <input type="date" name="dob">
              </div>
            </div>
            <div class="form-row">
              <div class="form-field">
                <label>Email *</label>
                <input type="email" name="email" placeholder="your@email.com" required>
              </div>
              <div class="form-field">
                <label>Phone *</label>
                <input type="tel" name="phone" placeholder="10-digit mobile" required>
              </div>
            </div>
            <div class="form-row">
              <div class="form-field">
                <label>City</label>
                <input type="text" name="city" placeholder="Your city">
              </div>
              <div class="form-field">
                <label>State</label>
                <input type="text" name="state" placeholder="Your state">
              </div>
            </div>
            <div class="step-nav">
              <span></span>
              <button type="button" class="btn-next">Next: Academic Info &rarr;</button>
            </div>
          </div>

          <!-- Step 2: Academic Info -->
          <div class="apply-step" id="step2">
            <h3 style="font-size:1.1rem;font-weight:700;margin-bottom:20px;">Step 2: Academic Information</h3>
            <div class="form-row">
              <div class="form-field">
                <label>10th Percentage</label>
                <input type="number" name="tenth_pct" placeholder="e.g. 75.5" min="0" max="100" step="0.01">
              </div>
              <div class="form-field">
                <label>12th Percentage</label>
                <input type="number" name="twelfth_pct" placeholder="e.g. 82.0" min="0" max="100" step="0.01">
              </div>
            </div>
            <div class="form-row">
              <div class="form-field">
                <label>Entrance Exam (if any)</label>
                <select name="entrance_exam">
                  <option value="">None / Not applicable</option>
                  <option>JEE Main</option>
                  <option>JEE Advanced</option>
                  <option>CAT</option>
                  <option>MAT</option>
                  <option>XAT</option>
                  <option>NEET</option>
                  <option>CLAT</option>
                  <option>CUET</option>
                  <option>Other</option>
                </select>
              </div>
              <div class="form-field">
                <label>Exam Score / Percentile</label>
                <input type="text" name="exam_score" placeholder="e.g. 95.4 percentile">
              </div>
            </div>
            <div class="step-nav">
              <button type="button" class="btn-back">&larr; Back</button>
              <button type="button" class="btn-next">Review Application &rarr;</button>
            </div>
          </div>

          <!-- Step 3: Review & Submit -->
          <div class="apply-step" id="step3">
            <h3 style="font-size:1.1rem;font-weight:700;margin-bottom:20px;">Step 3: Review &amp; Submit</h3>
            <?php if ($college): ?>
            <div style="background:#f0f9ff;border:1px solid #bae6fd;border-radius:8px;padding:14px 18px;margin-bottom:20px;font-size:0.88rem;">
              <strong>Applying to:</strong> <?= htmlspecialchars($college['name']) ?>
              <?php if ($course): ?> &mdash; <?= htmlspecialchars($course['name']) ?><?php endif; ?>
            </div>
            <?php endif; ?>
            <div id="reviewContent"></div>
            <div class="step-nav">
              <button type="button" class="btn-back">&larr; Back</button>
              <button type="submit" class="btn-next btn-submit-final" style="background:#22c55e;">Submit Application &#10003;</button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
