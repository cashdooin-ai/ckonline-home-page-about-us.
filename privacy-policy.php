<?php
$pageTitle = 'Privacy Policy | CollegeKampus Online';
$pageDesc  = 'How CollegeKampus Online collects, uses, and protects your personal information.';
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
    <h1>Privacy Policy</h1>
    <p>How we collect, use, and protect your information</p>
  </div>
</div>

<div class="legal-body">
  <p class="legal-updated">Last updated: <?= date('F Y') ?></p>

  <h2>1. Who We Are</h2>
  <p>CollegeKampus Online ("CollegeKampus", "we", "us", "our") is a college and course discovery platform that helps students compare online and distance-education universities, courses, and fees, and connects them with counsellors and partner institutions.</p>

  <h2>2. Information We Collect</h2>
  <p>We collect information you provide directly, such as when you fill out a lead, counselling, or application form:</p>
  <ul>
    <li>Name, email address, and phone number</li>
    <li>City, state, and preferred course or stream</li>
    <li>Any details you add in free-text fields (e.g. messages, comments)</li>
  </ul>
  <p>We also automatically collect limited technical information such as your IP address, browser type, and pages visited, to keep the site secure and improve it over time.</p>

  <h2>3. How We Use Your Information</h2>
  <ul>
    <li>To respond to your enquiries and connect you with relevant colleges, courses, or counsellors</li>
    <li>To share your enquiry with a partner institution when you request information or apply through our platform</li>
    <li>To send you updates about your enquiry or application by phone, email, or WhatsApp</li>
    <li>To improve our website, content, and services</li>
    <li>To detect and prevent fraud, spam, and abuse</li>
  </ul>

  <h2>4. Sharing Your Information</h2>
  <p>We share your information only with the specific college(s), university representative(s), or counsellors relevant to the enquiry or application you submit. We do not sell your personal information to third parties. We may share information with service providers who help us operate the platform (e.g. hosting, email/SMS delivery), under confidentiality obligations, or when required by law.</p>

  <h2>5. Cookies</h2>
  <p>We use cookies and similar technologies to remember your preferences, keep you signed in where applicable, and understand how visitors use the site. You can disable cookies in your browser settings, though some features may not work as intended.</p>

  <h2>6. Data Retention</h2>
  <p>We retain your information for as long as needed to fulfil the purposes described in this policy, or as required by law. You may request deletion of your data at any time (see Section 8).</p>

  <h2>7. Data Security</h2>
  <p>We take reasonable technical and organisational measures to protect your information against unauthorised access, loss, or misuse. No method of transmission over the internet is 100% secure, and we cannot guarantee absolute security.</p>

  <h2>8. Your Choices</h2>
  <p>You can ask us to access, correct, or delete your personal information, or to stop contacting you, at any time by writing to <a href="mailto:info@collegekampus.in">info@collegekampus.in</a> or using our <a href="<?= SITE_BASE ?>/contact.php">Contact Us</a> page.</p>

  <h2>9. Children's Privacy</h2>
  <p>Our services are intended for prospective college students and their families. We do not knowingly collect personal information from children under 13.</p>

  <h2>10. Changes to This Policy</h2>
  <p>We may update this Privacy Policy from time to time. The "Last updated" date at the top of this page reflects the most recent revision. Continued use of the site after changes take effect constitutes acceptance of the updated policy.</p>

  <h2>11. Contact Us</h2>
  <p>If you have any questions about this Privacy Policy, reach us at <a href="mailto:info@collegekampus.in">info@collegekampus.in</a> or <a href="tel:18001234567">1800-123-4567</a>.</p>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
