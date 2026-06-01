<?php
$pageTitle = 'Contact Us';
$pageDesc  = 'Get in touch with CollegeKampus Online. We are here to help with all your education queries.';
require_once __DIR__ . '/config/db.php';

$success = false;
$error   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = trim($_POST['name'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $phone   = trim($_POST['phone'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if (!$name || !$email) {
        $error = 'Name and email are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        try {
            $db = getDB();
            $leadCols = [];
            $stmt = $db->query("SHOW COLUMNS FROM leads");
            foreach ($stmt->fetchAll() as $r) $leadCols[] = $r['Field'];

            $insertCols = ['name', 'email'];
            $insertVals = [$name, $email];
            if (in_array('phone', $leadCols) && $phone) { $insertCols[] = 'phone'; $insertVals[] = $phone; }
            if (in_array('source', $leadCols)) { $insertCols[] = 'source'; $insertVals[] = 'contact'; }
            if (in_array('message', $leadCols)) { $insertCols[] = 'message'; $insertVals[] = ($subject ? "[$subject] " : '') . $message; }
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
  <div class="counsel-hero" style="padding:60px 0;">
    <div class="container">
      <h1>Contact Us</h1>
      <p>We are here to help with all your college and course queries</p>
    </div>
  </div>

  <div class="container" style="padding:40px 24px;">
    <div style="display:grid;grid-template-columns:1fr 1.6fr;gap:40px;max-width:960px;margin:0 auto;">
      <!-- Info -->
      <div>
        <h2 style="font-size:1.2rem;font-weight:800;margin-bottom:20px;">Get In Touch</h2>
        <div style="display:flex;flex-direction:column;gap:20px;">
          <div>
            <div style="font-weight:700;color:#0f172a;margin-bottom:4px;">&#128222; Phone</div>
            <div style="color:#64748b;">+91 98765 43210</div>
            <div style="color:#64748b;font-size:0.8rem;">Mon–Sat, 9am–6pm IST</div>
          </div>
          <div>
            <div style="font-weight:700;color:#0f172a;margin-bottom:4px;">&#128140; Email</div>
            <div style="color:#64748b;">support@collegekampus.com</div>
          </div>
          <div>
            <div style="font-weight:700;color:#0f172a;margin-bottom:4px;">&#128205; Address</div>
            <div style="color:#64748b;">CollegeKampus Online<br>India</div>
          </div>
        </div>
      </div>
      <!-- Form -->
      <div class="counsel-form-wrap" style="margin:0;">
        <?php if ($success): ?>
          <div class="success-box">
            <div class="success-icon">&#x2705;</div>
            <h2>Message Sent!</h2>
            <p>Thank you for reaching out. We will get back to you within 24 hours.</p>
          </div>
        <?php else: ?>
          <?php if ($error): ?><div class="alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
          <form method="POST">
            <div class="form-row" style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
              <div class="form-field">
                <label>Full Name *</label>
                <input type="text" name="name" required placeholder="Your name" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
              </div>
              <div class="form-field">
                <label>Phone</label>
                <input type="tel" name="phone" placeholder="10-digit mobile" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
              </div>
            </div>
            <div class="form-field">
              <label>Email *</label>
              <input type="email" name="email" required placeholder="your@email.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            </div>
            <div class="form-field">
              <label>Subject</label>
              <input type="text" name="subject" placeholder="What is your query about?" value="<?= htmlspecialchars($_POST['subject'] ?? '') ?>">
            </div>
            <div class="form-field">
              <label>Message</label>
              <textarea name="message" rows="4" placeholder="Your message..."><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea>
            </div>
            <button type="submit" class="btn-submit">Send Message &rarr;</button>
          </form>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
