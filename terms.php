<?php
$pageTitle = 'Terms of Use | CollegeKampus Online';
$pageDesc  = 'The terms and conditions for using the CollegeKampus Online platform.';
require_once __DIR__ . '/config/db.php';
include __DIR__ . '/includes/header.php';
?>

<style>
.legal-hero{background:linear-gradient(135deg,#0f172a,#1e3a5f);padding:50px 0;color:#fff;text-align:center;}
.legal-hero h1{font-size:clamp(1.6rem,3vw,2.4rem);font-weight:900;margin-bottom:8px;}
.legal-hero p{color:rgba(255,255,255,.7);}
.legal-body{max-width:820px;margin:0 auto;padding:48px 20px;color:#374151;line-height:1.75;font-size:.95rem;}
.legal-body h2{font-size:1.1rem;font-weight:800;color:#0f172a;margin:32px 0 12px;}
.legal-body h2:first-child{margin-top:0;}
.legal-body p{margin-bottom:14px;}
.legal-body ul{margin:0 0 14px 20px;}
.legal-body li{margin-bottom:6px;}
.legal-updated{font-size:.82rem;color:#9ca3af;margin-bottom:24px;}
</style>

<div class="legal-hero">
  <div class="container">
    <h1>Terms of Use</h1>
    <p>The rules for using CollegeKampus Online</p>
  </div>
</div>

<div class="legal-body">
  <p class="legal-updated">Last updated: <?= date('F Y') ?></p>

  <h2>1. Acceptance of Terms</h2>
  <p>By accessing or using CollegeKampus Online ("the Platform"), you agree to be bound by these Terms of Use. If you do not agree with any part of these terms, please do not use the Platform.</p>

  <h2>2. What We Offer</h2>
  <p>CollegeKampus Online is a free discovery and comparison platform for online and distance-education colleges, universities, and courses in India. We help you research programmes and connect with counsellors or institutions — we are not the college or university itself, and we do not control admissions decisions, fees, or curricula.</p>

  <h2>3. Information Accuracy</h2>
  <p>We source college, course, and fee information from institution disclosures and public data, and update it periodically. Figures such as fees, seats, and placement statistics can change and should always be confirmed directly with the institution before you make a financial or admissions decision. If you spot outdated or incorrect information, please use the "Report an Issue" option on the relevant page or contact us.</p>

  <h2>4. Using the Platform</h2>
  <p>You agree to:</p>
  <ul>
    <li>Provide accurate information when filling out enquiry, counselling, or application forms</li>
    <li>Use the Platform only for lawful purposes related to researching and applying to educational programmes</li>
    <li>Not attempt to scrape, copy, or resell the Platform's content without permission</li>
    <li>Not misuse the Platform to submit spam, false enquiries, or impersonate another person</li>
  </ul>

  <h2>5. Leads and Applications</h2>
  <p>When you submit an enquiry, counselling request, or application through the Platform, you consent to us sharing the relevant details with the college(s) or counsellor(s) associated with that request, and to being contacted by phone, email, SMS, or WhatsApp about it.</p>

  <h2>6. Third-Party Links</h2>
  <p>The Platform may link to college or university websites and other third-party resources. We are not responsible for the content, accuracy, or practices of those external sites.</p>

  <h2>7. Intellectual Property</h2>
  <p>The CollegeKampus Online name, logo, design, and original content are our property or that of our licensors. College and university names, logos, and marks belong to their respective institutions and are used for identification purposes only.</p>

  <h2>8. Disclaimer of Warranties</h2>
  <p>The Platform is provided "as is" without warranties of any kind. We do not guarantee admission outcomes, scholarship eligibility, or that any specific college or course will meet your requirements.</p>

  <h2>9. Limitation of Liability</h2>
  <p>To the fullest extent permitted by law, CollegeKampus Online will not be liable for any indirect, incidental, or consequential damages arising from your use of the Platform or your dealings with any college, university, or counsellor connected through it.</p>

  <h2>10. Changes to These Terms</h2>
  <p>We may update these Terms of Use from time to time. The "Last updated" date above reflects the most recent revision. Continued use of the Platform after changes take effect constitutes acceptance of the updated terms.</p>

  <h2>11. Contact Us</h2>
  <p>Questions about these Terms of Use can be sent to <a href="mailto:info@collegekampus.in">info@collegekampus.in</a> or <a href="tel:18001234567">1800-123-4567</a>.</p>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
