<?php
$pageTitle = 'Free Expert Counselling';
$pageDesc  = 'Get free expert counselling to choose the best online college and course for you.';
require_once __DIR__ . '/config/db.php';
if (!isset($extraHead)) $extraHead = '';
$extraHead .= '<script>window.CK_BASE = ' . json_encode(rtrim(SITE_BASE, '/')) . ';</script>';

$success = false;
$error   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name          = trim($_POST['name'] ?? '');
    $email         = trim($_POST['email'] ?? '');
    $phone         = trim($_POST['phone'] ?? '');
    $qualification = trim($_POST['current_qualification'] ?? '');
    $course        = trim($_POST['interested_course'] ?? '');
    $state         = trim($_POST['preferred_state'] ?? '');
    $message       = trim($_POST['message'] ?? '');

    if (!$name || !$email || !$phone) {
        $error = 'Name, email and phone are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        try {
            $db = getDB();
            $leadCols = [];
            $stmt = $db->query("SHOW COLUMNS FROM leads");
            foreach ($stmt->fetchAll() as $r) $leadCols[] = $r['Field'];

            $insertCols = ['name', 'email', 'phone'];
            $insertVals = [$name, $email, $phone];
            if (in_array('source', $leadCols)) { $insertCols[] = 'source'; $insertVals[] = 'counselling'; }
            if (in_array('message', $leadCols) && $message) { $insertCols[] = 'message'; $insertVals[] = $message; }
            if (in_array('course_interest', $leadCols) && $course) { $insertCols[] = 'course_interest'; $insertVals[] = $course; }
            if (in_array('state', $leadCols) && $state) { $insertCols[] = 'state'; $insertVals[] = $state; }
            if (in_array('qualification', $leadCols) && $qualification) { $insertCols[] = 'qualification'; $insertVals[] = $qualification; }
            if (in_array('created_at', $leadCols)) { $insertCols[] = 'created_at'; $insertVals[] = date('Y-m-d H:i:s'); }

            $ph = implode(',', array_fill(0, count($insertCols), '?'));
            $stmt = $db->prepare("INSERT INTO leads (" . implode(',', $insertCols) . ") VALUES ($ph)");
            $stmt->execute($insertVals);
            $success = true;
        } catch (Throwable $e) {
            $error = 'Sorry, something went wrong. Please try again.';
        }
    }
}

include __DIR__ . '/includes/header.php';
?>

<div class="page-wrapper">
  <div class="counsel-hero">
    <div class="container">
      <h1>&#127891; Get Free Expert Counselling</h1>
      <p>Our education experts will help you choose the right college and course based on your profile and goals — completely free!</p>
    </div>
  </div>

  <div class="container">
    <div class="counsel-form-wrap">
      <?php if ($success): ?>
        <div class="success-box">
          <div class="success-icon">&#x2705;</div>
          <h2>Request Received!</h2>
          <p>Thank you! Our expert counsellor will reach out to you within 24 hours on your phone/email.</p>
          <a href="/colleges.php" class="btn-view" style="display:inline-block;margin-top:20px;padding:12px 24px;">Browse Colleges</a>
        </div>
      <?php else: ?>
        <?php if ($error): ?><div class="alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
        <h2 style="font-size:1.3rem;font-weight:800;margin-bottom:6px;">Tell us about yourself</h2>
        <p style="color:#64748b;font-size:0.92rem;margin-bottom:28px;">Fill in the form below and we'll match you with the best options.</p>
        <form method="POST">
          <div class="form-row" style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
            <div class="form-field">
              <label>Full Name *</label>
              <input type="text" name="name" placeholder="Your name" required value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
            </div>
            <div class="form-field">
              <label>Phone Number *</label>
              <input type="tel" name="phone" placeholder="10-digit mobile" required value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
            </div>
          </div>
          <div class="form-field">
            <label>Email Address *</label>
            <input type="email" name="email" placeholder="your@email.com" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
          </div>
          <div class="form-row" style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
            <div class="form-field">
              <label>Current Qualification</label>
              <select name="current_qualification">
                <option value="">Select...</option>
                <option>10th Pass</option>
                <option>12th Pass</option>
                <option>Diploma</option>
                <option>Graduate</option>
                <option>Post Graduate</option>
                <option>Working Professional</option>
              </select>
            </div>
            <div class="form-field">
              <label>Interested Course</label>
              <input type="text" name="interested_course" placeholder="e.g. MBA, B.Tech..." value="<?= htmlspecialchars($_POST['interested_course'] ?? '') ?>">
            </div>
          </div>
          <div class="form-field">
            <label>Preferred State</label>
            <input type="text" name="preferred_state" placeholder="e.g. Maharashtra, Delhi..." value="<?= htmlspecialchars($_POST['preferred_state'] ?? '') ?>">
          </div>
          <div class="form-field">
            <label>Message (optional)</label>
            <textarea name="message" rows="3" placeholder="Any specific requirements or questions..."><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea>
          </div>
          <button type="submit" class="btn-submit">Get Free Counselling &rarr;</button>
        </form>
      <?php endif; ?>
    </div>
  </div>
  <div style="height:48px;"></div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
