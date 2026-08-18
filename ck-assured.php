<?php
$pageTitle = 'CK Assured — 100% Placement Guarantee | CollegeKampus';
$pageDesc  = 'CK Assured combines the best online degree with guaranteed placement support. If you are not placed in 6 months, we pay your first month\'s salary.';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/leads-schema.php';

$success = false;
$error   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name   = trim($_POST['name'] ?? '');
    $phone  = trim($_POST['phone'] ?? '');
    $email  = trim($_POST['email'] ?? '');
    $qual   = trim($_POST['qualification'] ?? '');
    $prog   = trim($_POST['program'] ?? '');
    if (!$name || !$phone || !$email) {
        $error = 'Name, phone and email are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        try {
            $db = getDB();
            onlineLeadsEnsureSchema($db);
            $leadCols = [];
            $stmt = $db->query("SHOW COLUMNS FROM online_leads");
            foreach ($stmt->fetchAll() as $r) $leadCols[] = $r['Field'];
            $ic = ['name','email','phone'];
            $iv = [$name,$email,$phone];
            if (in_array('source', $leadCols))           { $ic[] = 'source';         $iv[] = 'ck_assured'; }
            if (in_array('qualification', $leadCols) && $qual) { $ic[] = 'qualification'; $iv[] = $qual; }
            if (in_array('course_interest', $leadCols) && $prog) { $ic[] = 'course_interest'; $iv[] = $prog; }
            if (in_array('created_at', $leadCols))       { $ic[] = 'created_at';     $iv[] = date('Y-m-d H:i:s'); }
            $ph = implode(',', array_fill(0, count($ic), '?'));
            $db->prepare("INSERT INTO online_leads (" . implode(',', $ic) . ") VALUES ($ph)")->execute($iv);
            $success = true;
        } catch (Throwable $e) {
            $error = 'Sorry, something went wrong. Please try again.';
        }
    }
}

include __DIR__ . '/includes/header.php';
?>

<style>
/* Hero */
.cka-hero{background:linear-gradient(135deg,#0f172a 0%,#0c2d5e 50%,#0a1a3a 100%);padding:72px 0;color:#fff;text-align:center;}
.cka-hero h1{font-size:clamp(2rem,5vw,3.2rem);font-weight:900;line-height:1.15;margin-bottom:14px;}
.cka-hero h1 span{color:#22c55e;}
.cka-hero p{font-size:1.1rem;color:rgba(255,255,255,.75);max-width:600px;margin:0 auto 28px;}
.cka-hero-btns{display:flex;gap:14px;justify-content:center;flex-wrap:wrap;}
.btn-cka-primary{background:#22c55e;color:#fff;border:none;padding:14px 32px;border-radius:10px;font-size:1rem;font-weight:800;cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;gap:8px;transition:background .2s;}
.btn-cka-primary:hover{background:#16a34a;color:#fff;}
.btn-cka-outline{background:transparent;color:#fff;border:2px solid rgba(255,255,255,.4);padding:13px 28px;border-radius:10px;font-size:1rem;font-weight:700;cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;gap:8px;transition:all .2s;}
.btn-cka-outline:hover{border-color:#fff;color:#fff;}

/* What is CK Assured */
.cka-section{padding:56px 0;}
.cka-section-title{text-align:center;font-size:1.6rem;font-weight:900;color:#0f172a;margin-bottom:8px;}
.cka-section-sub{text-align:center;color:#64748b;font-size:1rem;margin-bottom:36px;}
.cka-3cards{display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:20px;}
.cka-card{border:1px solid #e2e8f0;border-radius:16px;padding:28px 20px;text-align:center;background:#fff;box-shadow:0 4px 16px rgba(0,0,0,.05);}
.cka-card .cka-icon{font-size:2.4rem;margin-bottom:14px;display:block;}
.cka-card h3{font-size:1rem;font-weight:800;color:#0f172a;margin-bottom:8px;}
.cka-card p{font-size:.85rem;color:#64748b;line-height:1.5;margin:0;}

/* How it works */
.how-wrap{background:#f8fafc;padding:56px 0;}
.steps-list{display:flex;flex-direction:column;gap:0;max-width:640px;margin:0 auto;}
.how-step{display:flex;gap:20px;align-items:flex-start;padding:24px 0;border-bottom:1px solid #e2e8f0;}
.how-step:last-child{border-bottom:none;}
.how-num{width:44px;height:44px;border-radius:50%;background:#2563eb;color:#fff;display:flex;align-items:center;justify-content:center;font-size:1rem;font-weight:900;flex-shrink:0;}
.how-body h4{font-size:1rem;font-weight:800;color:#0f172a;margin-bottom:4px;}
.how-body p{font-size:.85rem;color:#64748b;margin:0;}

/* Stats bar */
.stats-bar{background:linear-gradient(90deg,#2563eb,#1d4ed8);color:#fff;padding:32px 0;}
.stats-inner{display:flex;justify-content:space-around;flex-wrap:wrap;gap:20px;text-align:center;}
.stat-item .si-num{font-size:2rem;font-weight:900;}
.stat-item .si-label{font-size:.8rem;color:rgba(255,255,255,.75);font-weight:600;}

/* Eligible Programs */
.prog-section{padding:56px 0;}
.prog-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:16px;}
.prog-card{border:2px solid #e2e8f0;border-radius:14px;padding:20px 16px;text-align:center;background:#fff;position:relative;transition:border-color .2s,box-shadow .2s;}
.prog-card:hover{border-color:#2563eb;box-shadow:0 6px 20px rgba(37,99,235,.1);}
.assured-badge{position:absolute;top:10px;right:10px;background:#22c55e;color:#fff;font-size:.65rem;font-weight:800;padding:3px 8px;border-radius:4px;}
.prog-card .pc-icon{font-size:2rem;margin-bottom:10px;display:block;}
.prog-card h4{font-size:.9rem;font-weight:800;color:#0f172a;margin-bottom:4px;}
.prog-card p{font-size:.75rem;color:#64748b;margin:0;}
.prog-card a{font-size:.78rem;font-weight:700;color:#2563eb;text-decoration:none;margin-top:10px;display:inline-block;}

/* Partners */
.partners-section{background:#f8fafc;padding:48px 0;}
.partners-grid{display:flex;flex-wrap:wrap;gap:12px;justify-content:center;margin-top:24px;}
.partner-badge{background:#fff;border:1px solid #e2e8f0;border-radius:8px;padding:10px 20px;font-size:.85rem;font-weight:700;color:#0f172a;box-shadow:0 2px 6px rgba(0,0,0,.04);}

/* Lead form */
.cka-form-section{padding:56px 0;background:linear-gradient(135deg,#eff6ff,#f0fdf4);}
.cka-form-wrap{max-width:600px;margin:0 auto;background:#fff;border-radius:20px;box-shadow:0 12px 40px rgba(0,0,0,.08);overflow:hidden;}
.cka-form-header{background:linear-gradient(135deg,#2563eb,#1d4ed8);padding:28px;color:#fff;text-align:center;}
.cka-form-header h2{font-size:1.2rem;font-weight:900;margin-bottom:4px;}
.cka-form-header p{font-size:.85rem;color:rgba(255,255,255,.8);margin:0;}
.cka-form-body{padding:32px;}
.cf-field{margin-bottom:16px;}
.cf-field label{display:block;font-size:.78rem;font-weight:700;color:#64748b;margin-bottom:6px;text-transform:uppercase;letter-spacing:.04em;}
.cf-field input,.cf-field select{width:100%;padding:11px 14px;border:1.5px solid #e2e8f0;border-radius:8px;font-size:.9rem;color:#0f172a;font-family:inherit;transition:border-color .2s;}
.cf-field input:focus,.cf-field select:focus{outline:none;border-color:#2563eb;}
.cf-row{display:grid;grid-template-columns:1fr 1fr;gap:14px;}
@media(max-width:500px){.cf-row{grid-template-columns:1fr;}}
.btn-cka-submit{width:100%;padding:14px;background:#22c55e;color:#fff;border:none;border-radius:8px;font-size:1rem;font-weight:800;cursor:pointer;transition:background .2s;}
.btn-cka-submit:hover{background:#16a34a;}

/* FAQ */
.faq-section{padding:56px 0;}
.faq-section .accordion-button{font-weight:700;color:#0f172a;}
.faq-section .accordion-button:not(.collapsed){color:#2563eb;background:#eff6ff;}
</style>

<!-- Hero -->
<section class="cka-hero">
  <div class="container">
    <h1>Graduate with a Job. <span>Guaranteed.</span></h1>
    <p>CK Assured combines the best online degree with guaranteed placement support. If you don't get placed in 6 months, we pay your first month's salary.</p>
    <div class="cka-hero-btns">
      <a href="#cka-form" class="btn-cka-primary">&#127919; Enroll in CK Assured</a>
      <a href="#how-it-works" class="btn-cka-outline">Learn More &#8595;</a>
    </div>
  </div>
</section>

<!-- What is CK Assured -->
<section class="cka-section">
  <div class="container">
    <div class="cka-section-title">What is CK Assured?</div>
    <div class="cka-section-sub">Three pillars that guarantee your success</div>
    <div class="cka-3cards">
      <div class="cka-card">
        <span class="cka-icon">&#127891;</span>
        <h3>Best Online Degree</h3>
        <p>UGC approved, NAAC A++ universities with industry-aligned curriculum and live mentorship.</p>
      </div>
      <div class="cka-card">
        <span class="cka-icon">&#128188;</span>
        <h3>Placement Guarantee</h3>
        <p>100% placement assistance. If you're not placed in 6 months, we compensate your first month's salary.</p>
      </div>
      <div class="cka-card">
        <span class="cka-icon">&#129489;&#8205;&#128187;</span>
        <h3>Career Prep</h3>
        <p>Resume building, LinkedIn optimization, mock interviews and dedicated placement coordinator.</p>
      </div>
    </div>
  </div>
</section>

<!-- How it Works -->
<section class="how-wrap" id="how-it-works">
  <div class="container">
    <div class="cka-section-title">How CK Assured Works</div>
    <div class="cka-section-sub">4 simple steps to your guaranteed placement</div>
    <div class="steps-list">
      <div class="how-step">
        <div class="how-num">1</div>
        <div class="how-body">
          <h4>Choose Your Online Degree</h4>
          <p>Pick from CK Assured partner universities — all UGC approved, NAAC accredited programs.</p>
        </div>
      </div>
      <div class="how-step">
        <div class="how-num">2</div>
        <div class="how-body">
          <h4>Complete Degree + Career Workshops</h4>
          <p>Study online with live career workshops every month — resume, interviews, networking.</p>
        </div>
      </div>
      <div class="how-step">
        <div class="how-num">3</div>
        <div class="how-body">
          <h4>Get Placed via 500+ Hiring Partners</h4>
          <p>Our dedicated placement team connects you with top companies across India.</p>
        </div>
      </div>
      <div class="how-step">
        <div class="how-num">4</div>
        <div class="how-body">
          <h4>Not Placed? We Pay &#8377;15,000</h4>
          <p>If you don't land a job within 6 months of graduation, CollegeKampus pays your first month's salary equivalent — no questions asked.</p>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Stats bar -->
<div class="stats-bar">
  <div class="container">
    <div class="stats-inner">
      <div class="stat-item"><div class="si-num">2,400+</div><div class="si-label">Students Placed</div></div>
      <div class="stat-item"><div class="si-num">500+</div><div class="si-label">Hiring Partners</div></div>
      <div class="stat-item"><div class="si-num">&#8377;4.2L</div><div class="si-label">Avg CTC</div></div>
      <div class="stat-item"><div class="si-num">94%</div><div class="si-label">Placement Rate</div></div>
    </div>
  </div>
</div>

<!-- Eligible Programs -->
<section class="prog-section">
  <div class="container">
    <div class="cka-section-title">CK Assured Eligible Programs</div>
    <div class="cka-section-sub">All programs come with the placement guarantee</div>
    <div class="prog-grid">
      <div class="prog-card">
        <span class="assured-badge">CK Assured &#10003;</span>
        <span class="pc-icon">&#128202;</span>
        <h4>Online MBA</h4>
        <p>2 years &middot; PG</p>
        <a href="<?= $base ?>/colleges.php?course=MBA">Explore &#8594;</a>
      </div>
      <div class="prog-card">
        <span class="assured-badge">CK Assured &#10003;</span>
        <span class="pc-icon">&#128421;</span>
        <h4>Online MCA</h4>
        <p>2 years &middot; PG</p>
        <a href="<?= $base ?>/colleges.php?course=MCA">Explore &#8594;</a>
      </div>
      <div class="prog-card">
        <span class="assured-badge">CK Assured &#10003;</span>
        <span class="pc-icon">&#128203;</span>
        <h4>Online BBA</h4>
        <p>3 years &middot; UG</p>
        <a href="<?= $base ?>/colleges.php?course=BBA">Explore &#8594;</a>
      </div>
      <div class="prog-card">
        <span class="assured-badge">CK Assured &#10003;</span>
        <span class="pc-icon">&#128187;</span>
        <h4>Online BCA</h4>
        <p>3 years &middot; UG</p>
        <a href="<?= $base ?>/colleges.php?course=BCA">Explore &#8594;</a>
      </div>
      <div class="prog-card">
        <span class="assured-badge">CK Assured &#10003;</span>
        <span class="pc-icon">&#128200;</span>
        <h4>Online M.Com</h4>
        <p>2 years &middot; PG</p>
        <a href="<?= $base ?>/colleges.php?course=M.Com">Explore &#8594;</a>
      </div>
    </div>
  </div>
</section>

<!-- Hiring Partners -->
<section class="partners-section">
  <div class="container">
    <div class="cka-section-title">Our Hiring Partners</div>
    <div class="cka-section-sub">500+ companies hiring from CK Assured graduates</div>
    <div class="partners-grid">
      <span class="partner-badge">TCS</span>
      <span class="partner-badge">Infosys</span>
      <span class="partner-badge">Wipro</span>
      <span class="partner-badge">Deloitte</span>
      <span class="partner-badge">Accenture</span>
      <span class="partner-badge">Amazon</span>
      <span class="partner-badge">Flipkart</span>
      <span class="partner-badge">HDFC Bank</span>
      <span class="partner-badge">HCL</span>
      <span class="partner-badge">Capgemini</span>
    </div>
  </div>
</section>

<!-- Lead Form -->
<section class="cka-form-section" id="cka-form">
  <div class="container">
    <div class="cka-form-wrap">
      <div class="cka-form-header">
        <h2>Get CK Assured &mdash; Free Consultation</h2>
        <p>Talk to our placement experts. Zero cost. Zero obligation.</p>
      </div>
      <div class="cka-form-body">
        <?php if($success): ?>
          <div class="alert alert-success text-center">
            <strong>&#127881; Thank you!</strong> Our placement advisor will call you within 24 hours.
          </div>
        <?php else: ?>
          <?php if($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
          <?php endif; ?>
          <form method="POST" action="#cka-form">
            <div class="cf-row">
              <div class="cf-field">
                <label for="cka_name">Full Name *</label>
                <input type="text" id="cka_name" name="name" placeholder="Your name" required>
              </div>
              <div class="cf-field">
                <label for="cka_phone">Phone *</label>
                <input type="tel" id="cka_phone" name="phone" placeholder="+91 9XXXXXXXXX" required>
              </div>
            </div>
            <div class="cf-field">
              <label for="cka_email">Email *</label>
              <input type="email" id="cka_email" name="email" placeholder="you@email.com" required>
            </div>
            <div class="cf-row">
              <div class="cf-field">
                <label for="cka_qual">Current Qualification</label>
                <select id="cka_qual" name="qualification">
                  <option value="">Select</option>
                  <option>12th Pass</option>
                  <option>Diploma</option>
                  <option>Graduate</option>
                  <option>Post Graduate</option>
                  <option>Working Professional</option>
                </select>
              </div>
              <div class="cf-field">
                <label for="cka_prog">Program Interested In</label>
                <select id="cka_prog" name="program">
                  <option value="">Select Program</option>
                  <option>Online MBA</option>
                  <option>Online MCA</option>
                  <option>Online BBA</option>
                  <option>Online BCA</option>
                  <option>Online M.Com</option>
                </select>
              </div>
            </div>
            <button type="submit" class="btn-cka-submit">&#128197; Book Free Session</button>
          </form>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>

<!-- FAQ -->
<section class="faq-section">
  <div class="container" style="max-width:700px;">
    <div class="cka-section-title">Frequently Asked Questions</div>
    <div class="cka-section-sub" style="margin-bottom:28px;">Everything you need to know about CK Assured</div>
    <div class="accordion" id="faqAccordion">

      <div class="accordion-item border-0 mb-2" style="border-radius:10px!important;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,.05);">
        <h2 class="accordion-header">
          <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
            What exactly is the placement guarantee?
          </button>
        </h2>
        <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
          <div class="accordion-body text-muted" style="font-size:.9rem;">
            If you complete your CK Assured program, attend all career workshops, and don't receive a job offer within 6 months of graduation, CollegeKampus will pay you &#8377;15,000 as salary compensation. Terms and conditions apply.
          </div>
        </div>
      </div>

      <div class="accordion-item border-0 mb-2" style="border-radius:10px!important;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,.05);">
        <h2 class="accordion-header">
          <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
            Which universities are CK Assured partners?
          </button>
        </h2>
        <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
          <div class="accordion-body text-muted" style="font-size:.9rem;">
            We partner with top UGC-approved, NAAC A++ accredited universities. All partner institutions are carefully vetted for academic quality, placement record, and industry reputation.
          </div>
        </div>
      </div>

      <div class="accordion-item border-0 mb-2" style="border-radius:10px!important;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,.05);">
        <h2 class="accordion-header">
          <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
            Is there any extra cost for CK Assured?
          </button>
        </h2>
        <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
          <div class="accordion-body text-muted" style="font-size:.9rem;">
            No. CK Assured is offered at the same fee as the regular program. There is no additional charge for the placement guarantee or career prep services.
          </div>
        </div>
      </div>

      <div class="accordion-item border-0 mb-2" style="border-radius:10px!important;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,.05);">
        <h2 class="accordion-header">
          <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
            What career support is included?
          </button>
        </h2>
        <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
          <div class="accordion-body text-muted" style="font-size:.9rem;">
            CK Assured includes: professional resume building, LinkedIn profile optimization, 10+ mock interview sessions, access to 500+ hiring partner job board, dedicated placement coordinator, and monthly industry networking events.
          </div>
        </div>
      </div>

      <div class="accordion-item border-0 mb-2" style="border-radius:10px!important;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,.05);">
        <h2 class="accordion-header">
          <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq5">
            Who is eligible for CK Assured?
          </button>
        </h2>
        <div id="faq5" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
          <div class="accordion-body text-muted" style="font-size:.9rem;">
            Any student enrolling in an eligible CK Assured program (MBA, MCA, BBA, BCA, M.Com) through CollegeKampus Online is eligible. You must maintain 70%+ attendance, complete all assignments, and actively participate in all career prep sessions to qualify for the guarantee.
          </div>
        </div>
      </div>

    </div>
  </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
