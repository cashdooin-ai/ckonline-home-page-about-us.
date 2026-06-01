<?php
// INTEGRATION: Admin portal reads this DB. Partner portal writes to colleges/courses tables.
// Shared DB: collegekampus -- see database/schema.sql
if (!defined('SITE_BASE')) require_once dirname(__DIR__) . '/config/db.php';
$base = SITE_BASE;
?>
</main>

<footer class="ck-footer mt-auto">
    <div class="container">
        <div class="row gy-4 py-5">
            <div class="col-lg-4 col-md-6">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <div class="ck-logo-icon">CK</div>
                    <div class="lh-1">
                        <span class="ck-brand-main text-white">CollegeKampus</span>
                        <span class="ck-brand-sub d-block text-white-50">Online</span>
                    </div>
                </div>
                <p class="text-white-50 small">India's most trusted platform for online & distance college discovery. Compare 40,000+ colleges, explore courses, and apply free.</p>
                <div class="d-flex gap-3 mt-3">
                    <a href="https://www.facebook.com/collegekampus" rel="noopener" target="_blank" class="ck-social-link" aria-label="Facebook"><i class="bi bi-facebook"></i></a>
                    <a href="https://www.instagram.com/collegekampus" rel="noopener" target="_blank" class="ck-social-link" aria-label="Instagram"><i class="bi bi-instagram"></i></a>
                    <a href="https://twitter.com/collegekampus"    rel="noopener" target="_blank" class="ck-social-link" aria-label="Twitter/X"><i class="bi bi-twitter-x"></i></a>
                    <a href="https://www.linkedin.com/company/collegekampus" rel="noopener" target="_blank" class="ck-social-link" aria-label="LinkedIn"><i class="bi bi-linkedin"></i></a>
                    <a href="https://www.youtube.com/collegekampus" rel="noopener" target="_blank" class="ck-social-link" aria-label="YouTube"><i class="bi bi-youtube"></i></a>
                </div>
            </div>
            <div class="col-lg-2 col-md-3 col-6">
                <h6 class="text-white fw-semibold mb-3 text-uppercase small">Explore</h6>
                <ul class="list-unstyled ck-footer-links">
                    <li><a href="<?= $base ?>/colleges.php">All Colleges</a></li>
                    <li><a href="<?= $base ?>/courses.php">All Courses</a></li>
                    <li><a href="<?= $base ?>/colleges.php?type=government">Govt Colleges</a></li>
                    <li><a href="<?= $base ?>/colleges.php?type=private">Private Colleges</a></li>
                    <li><a href="<?= $base ?>/colleges.php?type=deemed">Deemed Universities</a></li>
                    <li><a href="<?= $base ?>/compare.php">Compare Colleges</a></li>
                </ul>
            </div>
            <div class="col-lg-2 col-md-3 col-6">
                <h6 class="text-white fw-semibold mb-3 text-uppercase small">Courses</h6>
                <ul class="list-unstyled ck-footer-links">
                    <li><a href="<?= $base ?>/courses.php?category=engineering">Online B.Tech</a></li>
                    <li><a href="<?= $base ?>/courses.php?category=management">Online MBA</a></li>
                    <li><a href="<?= $base ?>/courses.php?category=commerce">Online BBA</a></li>
                    <li><a href="<?= $base ?>/courses.php?category=science">Online BCA/MCA</a></li>
                    <li><a href="<?= $base ?>/courses.php?category=arts">Online BA/MA</a></li>
                    <li><a href="<?= $base ?>/courses.php?category=law">Online LLB</a></li>
                </ul>
            </div>
            <div class="col-lg-4 col-md-6">
                <h6 class="text-white fw-semibold mb-3 text-uppercase small">Get in Touch</h6>
                <ul class="list-unstyled ck-footer-links">
                    <li><i class="bi bi-geo-alt-fill me-2 text-ck-green"></i>New Delhi, India</li>
                    <li class="mt-2"><i class="bi bi-telephone-fill me-2 text-ck-green"></i><a href="tel:18001234567">1800-123-4567</a> (Toll Free)</li>
                    <li class="mt-2"><i class="bi bi-envelope-fill me-2 text-ck-green"></i><a href="mailto:info@collegekampus.in">info@collegekampus.in</a></li>
                    <li class="mt-3">
                        <a href="<?= $base ?>/counselling.php" class="btn btn-ck-primary btn-sm">
                            <i class="bi bi-headset me-1"></i>Free Counselling
                        </a>
                    </li>
                </ul>
            </div>
        </div>
        <hr class="border-secondary">
        <div class="row align-items-center py-3">
            <div class="col-md-8 text-center text-md-start">
                <small class="text-white-50">
                    &copy; <?= date('Y') ?> CollegeKampus Online. All rights reserved. &nbsp;|&nbsp;
                    <a href="<?= $base ?>/privacy-policy.php" class="text-white-50">Privacy Policy</a> &nbsp;|&nbsp;
                    <a href="<?= $base ?>/terms.php"          class="text-white-50">Terms of Use</a> &nbsp;|&nbsp;
                    <a href="<?= $base ?>/sitemap.xml"        class="text-white-50">Sitemap</a>
                </small>
            </div>
            <div class="col-md-4 text-center text-md-end mt-2 mt-md-0">
                <small class="text-white-50">UGC Approved &nbsp;|&nbsp; AICTE Listed &nbsp;|&nbsp; NAAC Accredited</small>
            </div>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= $base ?>/assets/js/portal.js"></script>
<?php if (isset($extraScript)) echo $extraScript; ?>
</body>
</html>
