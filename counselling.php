<?php
$pageTitle = 'Free Expert Counselling for Online Colleges | CollegeKampus';
$pageDesc  = 'Get free expert counselling to choose the best online college and course for you. 1.25L+ students helped, 600+ expert mentors.';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/leads-schema.php';

$success = false;
$error   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name          = trim($_POST['name'] ?? '');
    $email         = trim($_POST['email'] ?? '');
    $phone         = trim($_POST['phone'] ?? '');
    $qualification = trim($_POST['current_qualification'] ?? '');
    $course        = trim($_POST['interested_course'] ?? '');
    $budget        = trim($_POST['budget'] ?? '');
    $state         = trim($_POST['preferred_state'] ?? '');
    $message       = trim($_POST['message'] ?? '');

    if (!$name || !$email || !$phone) {
        $error = 'Name, email and phone are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        try {
            $db = getDB();
            onlineLeadsEnsureSchema($db);
            $leadCols = [];
            $stmt = $db->query("SHOW COLUMNS FROM online_leads");
            foreach ($stmt->fetchAll() as $r) $leadCols[] = $r['Field'];

            $insertCols = ['name', 'email', 'phone'];
            $insertVals = [$name, $email, $phone];
            if (in_array('source', $leadCols))         { $insertCols[] = 'source';         $insertVals[] = 'counselling'; }
            if (in_array('message', $leadCols) && ($message||$budget)) {
                $insertCols[] = 'message';
                $insertVals[] = ($budget?"[Budget: $budget] ":'') . $message;
            }
            if (in_array('course_interest', $leadCols) && $course)        { $insertCols[] = 'course_interest'; $insertVals[] = $course; }
            if (in_array('state', $leadCols) && $state)                   { $insertCols[] = 'state';           $insertVals[] = $state; }
            if (in_array('qualification', $leadCols) && $qualification)   { $insertCols[] = 'qualification';   $insertVals[] = $qualification; }
            if (in_array('created_at', $leadCols))                        { $insertCols[] = 'created_at';      $insertVals[] = date('Y-m-d H:i:s'); }

            $ph = implode(',', array_fill(0, count($insertCols), '?'));
            $stmt = $db->prepare("INSERT INTO online_leads (" . implode(',', $insertCols) . ") VALUES ($ph)");
            $stmt->execute($insertVals);
            $success = true;
        } catch (Throwable $e) {
            $error = 'Sorry, something went wrong. Please try again.';
        }
    }
}

$prefillCollege = trim($_GET['college'] ?? '');

include __DIR__ . '/includes/header.php';
?>

<style>
.counsel-hero{background:linear-gradient(135deg,#0f172a 0%,#1e3a5f 60%,#312e81 100%);padding:60px 0;color:#fff;}
.counsel-hero h1{font-size:clamp(1.8rem,4vw,2.8rem);font-weight:900;margin-bottom:10px;}
.counsel-hero p{color:rgba(255,255,255,.75);font-size:1rem;max-width:560px;}
.trust-badges{display:flex;gap:20px;flex-wrap:wrap;margin-top:20px;}
.trust-badge{display:flex;align-items:center;gap:8px;background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.2);padding:8px 14px;border-radius:8px;font-size:.82rem;font-weight:600;}
.counsel-layout{display:grid;grid-template-columns:1fr 440px;gap:40px;padding:48px 0;align-items:start;}
@media(max-width:900px){.counsel-layout{grid-template-columns:1fr;}}
.counsel-form-card{background:#fff;border:1px solid #e2e8f0;border-radius:16px;overflow:hidden;box-shadow:0 10px 40px rgba(0,0,0,.07);}
.counsel-form-header{background:linear-gradient(135deg,#2563eb,#1d4ed8);padding:24px;color:#fff;}
.counsel-form-header h2{font-size:1.1rem;font-weight:800;margin-bottom:4px;}
.counsel-form-header p{font-size:.82rem;color:rgba(255,255,255,.8);margin:0;}
.counsel-form-body{padding:28px;}
.cf-row{display:grid;grid-template-columns:1fr 1fr;gap:14px;}
@media(max-width:540px){.cf-row{grid-template-columns:1fr;}}
.cf-field{margin-bottom:14px;}
.cf-field label{display:block;font-size:.78rem;font-weight:600;color:#64748b;margin-bottom:5px;text-transform:uppercase;letter-spacing:.04em;}
.cf-field input,.cf-field select,.cf-field textarea{width:100%;padding:10px 12px;border:1px solid #e2e8f0;border-radius:8px;font-size:.875rem;color:#0f172a;transition:border-color .2s;font-family:inherit;}
.cf-field input:focus,.cf-field select:focus,.cf-field textarea:focus{outline:none;border-color:#2563eb;}
.btn-counsel-submit{width:100%;padding:13px;background:#22c55e;color:#fff;border:none;border-radius:8px;font-size:.95rem;font-weight:800;cursor:pointer;transition:background .2s;}
.btn-counsel-submit:hover{background:#16a34a;}
.value-prop-list{display:flex;flex-direction:column;gap:20px;margin-top:0;}
.vp-item{display:flex;gap:14px;align-items:flex-start;}
.vp-icon{width:44px;height:44px;border-radius:10px;background:#eff6ff;display:flex;align-items:center;justify-content:center;font-size:1.2rem;flex-shrink:0;}
.vp-title{font-size:.95rem;font-weight:700;color:#0f172a;margin-bottom:3px;}
.vp-desc{font-size:.83rem;color:#64748b;line-height:1.5;}
.success-panel{text-align:center;padding:40px 24px;}
.success-panel .check{font-size:3rem;margin-bottom:12px;display:block;}
.success-panel h3{font-size:1.3rem;font-weight:800;color:#16a34a;margin-bottom:8px;}
.success-panel p{color:#64748b;margin-bottom:20px;}
.alert-error{background:#fee2e2;border:1px solid #fca5a5;color:#991b1b;padding:12px 16px;border-radius:8px;font-size:.875rem;margin-bottom:16px;}
</style>

<!-- Hero -->
<div class="counsel-hero">
  <div class="container">
    <div style="display:inline-flex;align-items:center;gap:8px;background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.2);padding:5px 14px;border-radius:20px;font-size:.8rem;font-weight:600;margin-bottom:16px;">
      &#127891; 100% Free Service
    </div>
    <h1>Get Free Expert Counselling</h1>
    <p>Our education experts will help you choose the right college and course based on your profile and career goals.</p>
    <div class="trust-badges">
      <div class="trust-badge">&#128100; 1.25L+ Students Helped</div>
      <div class="trust-badge">&#127891; 600+ Expert Mentors</div>
      <div class="trust-badge">&#9989; 100% Free Session</div>
      <div class="trust-badge">&#9200; Response within 24hrs</div>
    </div>
  </div>
</div>

<div class="page-wrapper" style="margin-top:0;">
  <div class="container">
    <div class="counsel-layout">

      <!-- Left: Value Props -->
      <div>
        <h2 style="font-size:1.3rem;font-weight:800;color:#0f172a;margin-bottom:24px;">Why Our Counselling is Different</h2>
        <div class="value-prop-list">
          <div class="vp-item">
            <div class="vp-icon">&#127919;</div>
            <div>
              <div class="vp-title">Personalised Guidance</div>
              <div class="vp-desc">We analyse your profile, goals and budget to recommend only the most suitable programs for you.</div>
            </div>
          </div>
          <div class="vp-item">
            <div class="vp-icon">&#127891;</div>
            <div>
              <div class="vp-title">Expert Mentors</div>
              <div class="vp-desc">Talk to counsellors who have helped 1.25L+ students from top MNCs and universities.</div>
            </div>
          </div>
          <div class="vp-item">
            <div class="vp-icon">&#9989;</div>
            <div>
              <div class="vp-title">Completely Free</div>
              <div class="vp-desc">No hidden charges. Our counselling sessions are 100% free with no obligation to enrol.</div>
            </div>
          </div>
          <div class="vp-item">
            <div class="vp-icon">&#128222;</div>
            <div>
              <div class="vp-title">Fast Response</div>
              <div class="vp-desc">Our team will reach out within 24 hours on your preferred channel — call, WhatsApp, or email.</div>
            </div>
          </div>
          <div class="vp-item">
            <div class="vp-icon">&#128200;</div>
            <div>
              <div class="vp-title">End-to-End Support</div>
              <div class="vp-desc">From choosing the right program to admission and post-enrollment — we're with you every step.</div>
            </div>
          </div>
        </div>

        <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:12px;padding:20px;margin-top:28px;">
          <div style="font-size:.82rem;color:#166534;font-weight:700;margin-bottom:10px;">What students say</div>
          <blockquote style="margin:0;font-size:.875rem;color:#374151;font-style:italic;line-height:1.7;">"The counsellor helped me shortlist MBA programs matching my budget and goals. Got admitted to IGNOU with scholarship!"</blockquote>
          <div style="margin-top:8px;font-size:.78rem;color:#64748b;">— Priya S., MBA Student, Delhi</div>
        </div>
      </div>

      <!-- Right: Form -->
      <div>
        <div class="counsel-form-card">
          <div class="counsel-form-header">
            <h2>&#128221; Book Your Free Session</h2>
            <p>Fill the form &mdash; we'll call you back within 24 hours</p>
          </div>
          <div class="counsel-form-body">
            <?php if ($success): ?>
            <div class="success-panel">
              <span class="check">&#10003;</span>
              <h3>Request Submitted!</h3>
              <p>Thank you, <strong><?= htmlspecialchars($_POST['name']??'') ?></strong>! Our expert counsellor will contact you within 24 hours.</p>
              <a href="<?= $base ?>/colleges" class="btn-ck-primary" style="padding:12px 24px;">Browse Colleges</a>
            </div>
            <?php else: ?>
            <?php if ($error): ?><div class="alert-error">&#9888; <?= htmlspecialchars($error) ?></div><?php endif; ?>
            <form method="POST" action="">
              <div class="cf-row">
                <div class="cf-field">
                  <label>Full Name *</label>
                  <input type="text" name="name" placeholder="Your full name" required value="<?= htmlspecialchars($_POST['name']??'') ?>">
                </div>
                <div class="cf-field">
                  <label>Mobile Number *</label>
                  <input type="tel" name="phone" placeholder="+91 98765 43210" required value="<?= htmlspecialchars($_POST['phone']??'') ?>">
                </div>
              </div>
              <div class="cf-field">
                <label>Email Address *</label>
                <input type="email" name="email" placeholder="you@email.com" required value="<?= htmlspecialchars($_POST['email']??'') ?>">
              </div>
              <div class="cf-row">
                <div class="cf-field">
                  <label>Course Interest</label>
                  <select name="interested_course">
                    <option value="">Select a course</option>
                    <?php foreach(['Online MBA','Executive MBA','1-Year MBA','Distance MBA','Online BBA','Online BCA','Online MCA','Online B.Com','Online M.Com','Online MA','Online BA','Online B.Sc','Online M.Sc','Certificate Course','Other'] as $opt): ?>
                    <option value="<?= $opt ?>" <?= ($_POST['interested_course']??'')===$opt?'selected':'' ?>><?= $opt ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div class="cf-field">
                  <label>Current Qualification</label>
                  <select name="current_qualification">
                    <option value="">Select...</option>
                    <?php foreach(['10th Pass','12th Pass','Diploma','Graduate','Post Graduate','Working Professional'] as $q): ?>
                    <option value="<?= $q ?>" <?= ($_POST['current_qualification']??'')===$q?'selected':'' ?>><?= $q ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
              </div>
              <div class="cf-row">
                <div class="cf-field">
                  <label>Budget (Annual Fees)</label>
                  <select name="budget">
                    <option value="">Any budget</option>
                    <option>Under &#8377;50,000</option>
                    <option>&#8377;50,000 – 1,00,000</option>
                    <option>&#8377;1,00,000 – 2,00,000</option>
                    <option>&#8377;2,00,000 – 3,00,000</option>
                    <option>Above &#8377;3,00,000</option>
                  </select>
                </div>
                <div class="cf-field">
                  <label>Preferred State</label>
                  <input type="text" name="preferred_state" placeholder="e.g. Delhi, Maharashtra" value="<?= htmlspecialchars($_POST['preferred_state']??'') ?>">
                </div>
              </div>
              <?php if ($prefillCollege): ?>
              <div class="cf-field">
                <label>College of Interest</label>
                <input type="text" name="college_interest" value="<?= htmlspecialchars($prefillCollege) ?>">
              </div>
              <?php endif; ?>
              <div class="cf-field">
                <label>Message (Optional)</label>
                <textarea name="message" rows="3" placeholder="Tell us your specific requirements, work experience, or any questions..."><?= htmlspecialchars($_POST['message']??'') ?></textarea>
              </div>
              <button type="submit" class="btn-counsel-submit">&#127891; Get Free Counselling &rarr;</button>
              <p style="font-size:.72rem;color:#9ca3af;text-align:center;margin-top:10px;">By submitting you agree to our Privacy Policy. No spam, ever.</p>
            </form>
            <?php endif; ?>
          </div>
        </div>
      </div>

    </div>
  </div>
  <div style="height:40px;"></div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
