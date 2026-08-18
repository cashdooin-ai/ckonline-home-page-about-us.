<?php
$pageTitle = 'Contact Us | CollegeKampus Online';
$pageDesc  = 'Get in touch with CollegeKampus Online. We are here to help with all your online education and college queries.';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/leads-schema.php';

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
            onlineLeadsEnsureSchema($db);
            $leadCols = [];
            $stmt = $db->query("SHOW COLUMNS FROM online_leads");
            foreach ($stmt->fetchAll() as $r) $leadCols[] = $r['Field'];

            $insertCols = ['name', 'email'];
            $insertVals = [$name, $email];
            if (in_array('phone', $leadCols) && $phone)   { $insertCols[] = 'phone';   $insertVals[] = $phone; }
            if (in_array('source', $leadCols))             { $insertCols[] = 'source';  $insertVals[] = 'contact'; }
            if (in_array('message', $leadCols))            { $insertCols[] = 'message'; $insertVals[] = ($subject ? "[$subject] " : '') . $message; }
            if (in_array('created_at', $leadCols))         { $insertCols[] = 'created_at'; $insertVals[] = date('Y-m-d H:i:s'); }

            $ph = implode(',', array_fill(0, count($insertCols), '?'));
            $stmt = $db->prepare("INSERT INTO online_leads (" . implode(',', $insertCols) . ") VALUES ($ph)");
            $stmt->execute($insertVals);
            $success = true;
        } catch (Throwable $e) {
            $error = 'Sorry, something went wrong. Please try again.';
        }
    }
}

include __DIR__ . '/includes/header.php';
?>

<style>
.contact-hero{background:linear-gradient(135deg,#0f172a,#1e3a5f);padding:50px 0;color:#fff;text-align:center;}
.contact-hero h1{font-size:clamp(1.6rem,3vw,2.4rem);font-weight:900;margin-bottom:8px;}
.contact-hero p{color:rgba(255,255,255,.7);}
.contact-layout{display:grid;grid-template-columns:340px 1fr;gap:40px;padding:48px 0;align-items:start;}
@media(max-width:860px){.contact-layout{grid-template-columns:1fr;}}
.contact-info-card{background:#f8fafc;border:1px solid #e2e8f0;border-radius:14px;padding:28px;}
.contact-info-card h3{font-size:1rem;font-weight:700;margin-bottom:20px;color:#0f172a;}
.contact-info-item{display:flex;gap:12px;align-items:flex-start;margin-bottom:20px;}
.contact-info-icon{width:40px;height:40px;border-radius:10px;background:#eff6ff;display:flex;align-items:center;justify-content:center;font-size:1rem;flex-shrink:0;color:#2563eb;}
.contact-info-label{font-size:.75rem;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.05em;margin-bottom:4px;}
.contact-info-val{font-size:.875rem;color:#374151;}
.contact-info-val a{color:#2563eb;text-decoration:none;}
.contact-info-val a:hover{text-decoration:underline;}
.contact-form-card{background:#fff;border:1px solid #e2e8f0;border-radius:14px;padding:32px;box-shadow:0 4px 24px rgba(0,0,0,.05);}
.cf-row{display:grid;grid-template-columns:1fr 1fr;gap:14px;}
@media(max-width:540px){.cf-row{grid-template-columns:1fr;}}
.cf-field{margin-bottom:14px;}
.cf-field label{display:block;font-size:.78rem;font-weight:600;color:#64748b;margin-bottom:5px;text-transform:uppercase;letter-spacing:.04em;}
.cf-field input,.cf-field select,.cf-field textarea{width:100%;padding:10px 12px;border:1px solid #e2e8f0;border-radius:8px;font-size:.875rem;color:#0f172a;transition:border-color .2s;font-family:inherit;}
.cf-field input:focus,.cf-field select:focus,.cf-field textarea:focus{outline:none;border-color:#2563eb;}
.btn-contact-submit{width:100%;padding:13px;background:#2563eb;color:#fff;border:none;border-radius:8px;font-size:.95rem;font-weight:800;cursor:pointer;transition:background .2s;}
.btn-contact-submit:hover{background:#1d4ed8;}
.success-panel{text-align:center;padding:40px;}
.alert-error{background:#fee2e2;border:1px solid #fca5a5;color:#991b1b;padding:12px 16px;border-radius:8px;font-size:.875rem;margin-bottom:16px;}
</style>

<div class="contact-hero">
  <div class="container">
    <h1>&#9993; Contact Us</h1>
    <p>We're here to help with all your college and course queries</p>
  </div>
</div>

<div class="page-wrapper" style="margin-top:0;">
  <div class="container">
    <div class="contact-layout">

      <!-- Info -->
      <div class="contact-info-card">
        <h3>Get in Touch</h3>
        <div class="contact-info-item">
          <div class="contact-info-icon"><i class="bi bi-telephone-fill"></i></div>
          <div>
            <div class="contact-info-label">Phone / WhatsApp</div>
            <div class="contact-info-val"><a href="tel:18001234567">1800-123-4567</a> (Toll Free)</div>
            <div style="font-size:.78rem;color:#9ca3af;margin-top:2px;">Mon–Sat, 9am–7pm IST</div>
          </div>
        </div>
        <div class="contact-info-item">
          <div class="contact-info-icon"><i class="bi bi-envelope-fill"></i></div>
          <div>
            <div class="contact-info-label">Email</div>
            <div class="contact-info-val"><a href="mailto:info@collegekampus.in">info@collegekampus.in</a></div>
            <div style="font-size:.78rem;color:#9ca3af;margin-top:2px;">We reply within 24 hours</div>
          </div>
        </div>
        <div class="contact-info-item">
          <div class="contact-info-icon"><i class="bi bi-geo-alt-fill"></i></div>
          <div>
            <div class="contact-info-label">Address</div>
            <div class="contact-info-val">CollegeKampus Online<br>New Delhi, India</div>
          </div>
        </div>
        <div class="contact-info-item">
          <div class="contact-info-icon"><i class="bi bi-clock-fill"></i></div>
          <div>
            <div class="contact-info-label">Working Hours</div>
            <div class="contact-info-val">Monday – Saturday<br>9:00 AM – 7:00 PM IST</div>
          </div>
        </div>

        <hr style="border-color:#e2e8f0;margin:16px 0;">
        <div style="font-size:.82rem;font-weight:700;color:#0f172a;margin-bottom:10px;">Follow us</div>
        <div class="d-flex gap-2">
          <a href="https://www.facebook.com/collegekampus" rel="noopener" target="_blank" class="ck-social-link"><i class="bi bi-facebook"></i></a>
          <a href="https://www.instagram.com/collegekampus" rel="noopener" target="_blank" class="ck-social-link" style="background:#f1f5f9;color:#374151;"><i class="bi bi-instagram"></i></a>
          <a href="https://twitter.com/collegekampus" rel="noopener" target="_blank" class="ck-social-link" style="background:#f1f5f9;color:#374151;"><i class="bi bi-twitter-x"></i></a>
          <a href="https://www.linkedin.com/company/collegekampus" rel="noopener" target="_blank" class="ck-social-link" style="background:#f1f5f9;color:#374151;"><i class="bi bi-linkedin"></i></a>
        </div>

        <div style="margin-top:20px;">
          <a href="<?= $base ?>/counselling.php" class="btn-ck-primary" style="width:100%;justify-content:center;padding:10px;">
            <i class="bi bi-headset me-2"></i>Book Free Counselling
          </a>
        </div>
      </div>

      <!-- Form -->
      <div class="contact-form-card">
        <?php if ($success): ?>
        <div class="success-panel">
          <div style="font-size:3rem;margin-bottom:12px;">&#10003;</div>
          <h3 style="font-size:1.3rem;font-weight:800;color:#16a34a;margin-bottom:8px;">Message Sent!</h3>
          <p style="color:#64748b;margin-bottom:20px;">Thank you for reaching out. We'll get back to you within 24 hours.</p>
          <a href="<?= $base ?>/colleges.php" class="btn-ck-primary" style="padding:12px 24px;">Browse Colleges</a>
        </div>
        <?php else: ?>
        <h2 style="font-size:1.1rem;font-weight:800;color:#0f172a;margin-bottom:6px;">Send Us a Message</h2>
        <p style="font-size:.85rem;color:#64748b;margin-bottom:24px;">Fill in the form below and we'll respond within 24 hours.</p>
        <?php if ($error): ?><div class="alert-error">&#9888; <?= htmlspecialchars($error) ?></div><?php endif; ?>
        <form method="POST" action="">
          <div class="cf-row">
            <div class="cf-field">
              <label>Full Name *</label>
              <input type="text" name="name" required placeholder="Your name" value="<?= htmlspecialchars($_POST['name']??'') ?>">
            </div>
            <div class="cf-field">
              <label>Phone</label>
              <input type="tel" name="phone" placeholder="+91 98765 43210" value="<?= htmlspecialchars($_POST['phone']??'') ?>">
            </div>
          </div>
          <div class="cf-field">
            <label>Email Address *</label>
            <input type="email" name="email" required placeholder="you@email.com" value="<?= htmlspecialchars($_POST['email']??'') ?>">
          </div>
          <div class="cf-field">
            <label>Subject</label>
            <select name="subject">
              <option value="">Select a topic</option>
              <option>General Enquiry</option>
              <option>College Admissions</option>
              <option>Course Information</option>
              <option>Technical Support</option>
              <option>Partnership / Advertising</option>
              <option>Other</option>
            </select>
          </div>
          <div class="cf-field">
            <label>Message</label>
            <textarea name="message" rows="5" placeholder="Your message..."><?= htmlspecialchars($_POST['message']??'') ?></textarea>
          </div>
          <button type="submit" class="btn-contact-submit">&#9993; Send Message &rarr;</button>
          <p style="font-size:.72rem;color:#9ca3af;text-align:center;margin-top:10px;">By submitting you agree to our Privacy Policy.</p>
        </form>
        <?php endif; ?>
      </div>

    </div>
  </div>
  <div style="height:40px;"></div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
