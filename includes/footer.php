<?php
// INTEGRATION: Admin portal reads this DB. Partner portal writes to colleges/courses tables.
// Shared DB: collegekampus -- see database/schema.sql
?>
</main><!-- /#main-content -->

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
                <p class="text-white-50 small">India's trusted college discovery platform. Compare 500+ colleges, explore courses and apply online.</p>
                <div class="d-flex gap-3 mt-3">
                    <a href="#" class="ck-social-link" aria-label="Facebook"><i class="bi bi-facebook"></i></a>
                    <a href="#" class="ck-social-link" aria-label="Instagram"><i class="bi bi-instagram"></i></a>
                    <a href="#" class="ck-social-link" aria-label="Twitter/X"><i class="bi bi-twitter-x"></i></a>
                    <a href="#" class="ck-social-link" aria-label="LinkedIn"><i class="bi bi-linkedin"></i></a>
                </div>
            </div>
            <div class="col-lg-2 col-md-3 col-6">
                <h6 class="text-white fw-semibold mb-3 text-uppercase">Explore</h6>
                <ul class="list-unstyled ck-footer-links">
                    <li><a href="/colleges.php">All Colleges</a></li>
                    <li><a href="/courses.php">All Courses</a></li>
                    <li><a href="/colleges.php?type=government">Govt Colleges</a></li>
                    <li><a href="/colleges.php?type=private">Private Colleges</a></li>
                </ul>
            </div>
            <div class="col-lg-2 col-md-3 col-6">
                <h6 class="text-white fw-semibold mb-3 text-uppercase">Courses</h6>
                <ul class="list-unstyled ck-footer-links">
                    <li><a href="/courses.php?category=engineering">Engineering</a></li>
                    <li><a href="/courses.php?category=management">Management</a></li>
                    <li><a href="/courses.php?category=medical">Medical</a></li>
                    <li><a href="/courses.php?category=law">Law</a></li>
                </ul>
            </div>
            <div class="col-lg-4 col-md-6">
                <h6 class="text-white fw-semibold mb-3 text-uppercase">Contact</h6>
                <ul class="list-unstyled ck-footer-links">
                    <li><i class="bi bi-geo-alt-fill me-2 text-ck-green"></i>123 Education Hub, New Delhi</li>
                    <li class="mt-2"><i class="bi bi-telephone-fill me-2 text-ck-green"></i>1800-123-4567</li>
                    <li class="mt-2"><i class="bi bi-envelope-fill me-2 text-ck-green"></i>info@collegekampus.in</li>
                </ul>
            </div>
        </div>
        <hr class="border-secondary">
        <div class="row align-items-center py-3">
            <div class="col-md-6 text-center text-md-start">
                <small class="text-white-50">&copy; <?= date('Y') ?> CollegeKampus Online. All rights reserved.</small>
            </div>
        </div>
    </div>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="/assets/js/portal.js"></script>
</body>
</html>
