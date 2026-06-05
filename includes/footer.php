<?php
if (!defined('SITE_BASE')) require_once dirname(__DIR__) . '/config/db.php';
$base = SITE_BASE;
?>
</main>

<footer class="ck-footer mt-auto">
  <div class="container">
    <div class="row gy-4 py-5">

      <!-- Col 1: Brand -->
      <div class="col-lg-3 col-md-6">
        <div class="d-flex align-items-center gap-2 mb-3">
          <div class="ck-logo-icon">CK</div>
          <div class="lh-1">
            <span class="ck-brand-main text-white">CollegeKampus</span>
            <span class="ck-brand-sub d-block" style="color:#22c55e;">Online</span>
          </div>
        </div>
        <p class="small mb-3" style="color:rgba(255,255,255,.6);">India's most trusted platform for online &amp; distance college discovery. Compare 40,000+ colleges, explore courses, and apply free.</p>
        <div class="d-flex gap-2 flex-wrap mb-3">
          <span style="background:rgba(255,255,255,.08);color:rgba(255,255,255,.7);padding:3px 10px;border-radius:4px;font-size:.72rem;font-weight:600;">UGC Approved</span>
          <span style="background:rgba(255,255,255,.08);color:rgba(255,255,255,.7);padding:3px 10px;border-radius:4px;font-size:.72rem;font-weight:600;">AICTE Listed</span>
          <span style="background:rgba(255,255,255,.08);color:rgba(255,255,255,.7);padding:3px 10px;border-radius:4px;font-size:.72rem;font-weight:600;">NAAC Accredited</span>
        </div>
        <div class="d-flex gap-2">
          <a href="https://www.facebook.com/collegekampus"  rel="noopener" target="_blank" class="ck-social-link" aria-label="Facebook"><i class="bi bi-facebook"></i></a>
          <a href="https://www.instagram.com/collegekampus" rel="noopener" target="_blank" class="ck-social-link" aria-label="Instagram"><i class="bi bi-instagram"></i></a>
          <a href="https://twitter.com/collegekampus"       rel="noopener" target="_blank" class="ck-social-link" aria-label="Twitter"><i class="bi bi-twitter-x"></i></a>
          <a href="https://www.linkedin.com/company/collegekampus" rel="noopener" target="_blank" class="ck-social-link" aria-label="LinkedIn"><i class="bi bi-linkedin"></i></a>
          <a href="https://www.youtube.com/collegekampus"   rel="noopener" target="_blank" class="ck-social-link" aria-label="YouTube"><i class="bi bi-youtube"></i></a>
        </div>
      </div>

      <!-- Col 2: Explore -->
      <div class="col-lg-2 col-md-3 col-6">
        <h6 class="text-white fw-bold mb-3" style="font-size:.8rem;text-transform:uppercase;letter-spacing:.06em;">Explore</h6>
        <ul class="ck-footer-links">
          <li><a href="<?= $base ?>/colleges.php">All Colleges</a></li>
          <li><a href="<?= $base ?>/courses.php">All Courses</a></li>
          <li><a href="<?= $base ?>/colleges.php?type=government">Govt Universities</a></li>
          <li><a href="<?= $base ?>/colleges.php?type=private">Private Universities</a></li>
          <li><a href="<?= $base ?>/colleges.php?type=deemed">Deemed Universities</a></li>
          <li><a href="<?= $base ?>/compare.php">Compare Colleges</a></li>
          <li><a href="<?= $base ?>/colleges.php?sort=nirf">NIRF Rankings</a></li>
          <li><a href="<?= $base ?>/colleges.php?sort=naac">NAAC Accredited</a></li>
        </ul>
      </div>

      <!-- Col 3: Programs -->
      <div class="col-lg-2 col-md-3 col-6">
        <h6 class="text-white fw-bold mb-3" style="font-size:.8rem;text-transform:uppercase;letter-spacing:.06em;">Top Programs</h6>
        <ul class="ck-footer-links">
          <li><a href="<?= $base ?>/colleges.php?course=MBA">Online MBA</a></li>
          <li><a href="<?= $base ?>/colleges.php?course=BBA">Online BBA</a></li>
          <li><a href="<?= $base ?>/colleges.php?course=BCA">Online BCA</a></li>
          <li><a href="<?= $base ?>/colleges.php?course=MCA">Online MCA</a></li>
          <li><a href="<?= $base ?>/colleges.php?course=B.Com">Online B.Com</a></li>
          <li><a href="<?= $base ?>/colleges.php?course=MA">Online MA</a></li>
          <li><a href="<?= $base ?>/colleges.php?course=M.Com">Online M.Com</a></li>
          <li><a href="<?= $base ?>/colleges.php?course=B.Sc">Online B.Sc</a></li>
        </ul>
      </div>

      <!-- Col 4: Quick Links -->
      <div class="col-lg-2 col-md-3 col-6">
        <h6 class="text-white fw-bold mb-3" style="font-size:.8rem;text-transform:uppercase;letter-spacing:.06em;">Quick Links</h6>
        <ul class="ck-footer-links">
          <li><a href="<?= $base ?>/counselling.php">Free Counselling</a></li>
          <li><a href="<?= $base ?>/apply.php">Apply Online</a></li>
          <li><a href="<?= $base ?>/compare.php">Compare Tool</a></li>
          <li><a href="<?= $base ?>/index.html#about">About Us</a></li>
          <li><a href="<?= $base ?>/contact.php">Contact Us</a></li>
          <li><a href="<?= $base ?>/colleges.php?delivery_mode=online">Online Colleges</a></li>
          <li><a href="<?= $base ?>/colleges.php?delivery_mode=hybrid">Hybrid Colleges</a></li>
        </ul>
      </div>

      <!-- Col 5: Contact -->
      <div class="col-lg-3 col-md-3 col-6">
        <h6 class="text-white fw-bold mb-3" style="font-size:.8rem;text-transform:uppercase;letter-spacing:.06em;">Get in Touch</h6>
        <ul class="ck-footer-links">
          <li style="display:flex;gap:8px;align-items:flex-start;margin-bottom:10px;">
            <i class="bi bi-geo-alt-fill mt-1" style="color:#22c55e;flex-shrink:0;"></i>
            <span>New Delhi, India</span>
          </li>
          <li style="display:flex;gap:8px;align-items:center;margin-bottom:10px;">
            <i class="bi bi-telephone-fill" style="color:#22c55e;flex-shrink:0;"></i>
            <a href="tel:18001234567">1800-123-4567</a>&nbsp;<span style="font-size:.75rem;">(Toll Free)</span>
          </li>
          <li style="display:flex;gap:8px;align-items:center;margin-bottom:10px;">
            <i class="bi bi-whatsapp" style="color:#22c55e;flex-shrink:0;"></i>
            <a href="https://wa.me/918001234567">WhatsApp Us</a>
          </li>
          <li style="display:flex;gap:8px;align-items:center;margin-bottom:16px;">
            <i class="bi bi-envelope-fill" style="color:#22c55e;flex-shrink:0;"></i>
            <a href="mailto:info@collegekampus.in">info@collegekampus.in</a>
          </li>
        </ul>
        <a href="<?= $base ?>/counselling.php" class="btn-ck-primary" style="width:100%;justify-content:center;">
          <i class="bi bi-headset"></i> Book Free Session
        </a>
      </div>

    </div>

    <hr style="border-color:rgba(255,255,255,.1);margin:0;">

    <div class="row align-items-center py-3">
      <div class="col-md-8 text-center text-md-start">
        <small style="color:rgba(255,255,255,.45);">
          &copy; <?= date('Y') ?> CollegeKampus Online. All rights reserved. &nbsp;|&nbsp;
          <a href="<?= $base ?>/privacy-policy.php" style="color:rgba(255,255,255,.45);text-decoration:none;">Privacy Policy</a> &nbsp;|&nbsp;
          <a href="<?= $base ?>/terms.php"          style="color:rgba(255,255,255,.45);text-decoration:none;">Terms of Use</a> &nbsp;|&nbsp;
          <a href="<?= $base ?>/sitemap.xml"        style="color:rgba(255,255,255,.45);text-decoration:none;">Sitemap</a>
        </small>
      </div>
      <div class="col-md-4 text-center text-md-end mt-2 mt-md-0">
        <small>
          <a href="https://collegekampus.com/admin"   target="_blank" style="color:rgba(255,255,255,.4);text-decoration:none;margin-right:12px;">
            <i class="bi bi-shield-lock"></i> Admin
          </a>
          <a href="https://collegekampus.com/partner" target="_blank" style="color:rgba(255,255,255,.4);text-decoration:none;">
            <i class="bi bi-building"></i> Partner
          </a>
        </small>
      </div>
    </div>
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<?php
// Inject CMS marketing config for JS consumption
$_ckCms = [];
try {
    $_db2 = getDB();
    $_ckCms = $_db2->query("SELECT `key`,`value` FROM site_settings")->fetchAll(PDO::FETCH_KEY_PAIR);
} catch (Throwable $_) {}
$_cb = [
    'enabled'    => ($_ckCms['cashback_enabled'] ?? '') === '1',
    'badge_text' => $_ckCms['cashback_badge_text'] ?? 'Get ₹5,000 Cashback',
    'cta'        => $_ckCms['cashback_cta'] ?? 'Claim Cashback & Apply',
    'amount'     => $_ckCms['cashback_amount'] ?? '5,000',
    'terms'      => $_ckCms['cashback_terms'] ?? '',
];
$_banners = json_decode($_ckCms['promo_banners'] ?? '[]', true) ?: [];
$_popup = [
    'enabled'  => ($_ckCms['popup_enabled'] ?? '') === '1',
    'title'    => $_ckCms['popup_title'] ?? '',
    'subtitle' => $_ckCms['popup_subtitle'] ?? '',
    'cta_text' => $_ckCms['popup_cta_text'] ?? 'Apply Now',
    'cta_link' => $_ckCms['popup_cta_link'] ?? 'apply.php',
    'delay'    => (int)($_ckCms['popup_delay'] ?? 8),
];
?>
<script>
window.CK_CASHBACK = <?= json_encode($_cb, JSON_UNESCAPED_SLASHES) ?>;
window.CK_POPUP    = <?= json_encode($_popup, JSON_UNESCAPED_SLASHES) ?>;
window.CK_BANNERS  = <?= json_encode(array_values(array_filter($_banners, fn($b)=>!empty($b['active']) && !empty($b['text']))), JSON_UNESCAPED_SLASHES) ?>;
</script>
<script src="<?= $base ?>/assets/js/portal.js"></script>
<?php if (isset($extraScript)) echo $extraScript; ?>
<?php include __DIR__ . '/../includes/exit-popup.php'; ?>
<?php include __DIR__ . '/../includes/floating-cta.php'; ?>
</body>
</html>
