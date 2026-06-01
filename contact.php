<?php
// INTEGRATION: Admin portal reads this DB. Partner portal writes to colleges/courses tables.
// Shared DB: collegekampus -- see database/schema.sql

require_once __DIR__ . '/config/db.php';

$pdo = getPDO();
$errors  = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = trim($_POST['student_name'] ?? '');
    $email   = trim($_POST['email']        ?? '');
    $phone   = trim($_POST['phone']        ?? '');
    $subject = trim($_POST['subject']      ?? '');
    $message = trim($_POST['message']      ?? '');

    if (!$name)  $errors[] = 'Your name is required.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'A valid email address is required.';
    if ($phone && !preg_match('/^[6-9]\d{9}$/', preg_replace('/\D/','',$phone))) $errors[] = 'Enter a valid 10-digit mobile number.';
    if (!$message) $errors[] = 'Please enter your message.';

    if (!$errors) {
        $stmt = $pdo->prepare("INSERT INTO leads (student_name, email, phone, course_interest, message, source) VALUES (?,?,?,?,?,?)");
        $stmt->execute([$name, $email, $phone, $subject, $message, 'contact']);
        $success = true;
    }
}

$pageTitle = 'Contact Us';
include __DIR__ . '/includes/header.php';
?>
<main id="main-content">

<section class="ck-page-hero">
    <div class="container">
        <h1 class="mb-2"><i class="bi bi-envelope me-2"></i>Contact Us</h1>
        <p class="mb-0">Have questions? Our counsellors are here to help you find the right college.</p>
    </div>
</section>

<div class="container py-5">
    <div class="row g-5 justify-content-center">
        <div class="col-lg-4">
            <h4 class="fw-bold mb-4">Get in Touch</h4>
            <div class="d-flex gap-3 mb-4">
                <div style="width:48px;height:48px;background:#d4edda;border-radius:12px;display:flex;align-items:center;justify-content:center;flex-shrink:0;"><i class="bi bi-geo-alt-fill text-success fs-5"></i></div>
                <div><h6 class="fw-bold mb-0">Address</h6><p class="text-muted small mb-0">123 Education Hub, Connaught Place<br>New Delhi - 110001</p></div>
            </div>
            <div class="d-flex gap-3 mb-4">
                <div style="width:48px;height:48px;background:#cce5ff;border-radius:12px;display:flex;align-items:center;justify-content:center;flex-shrink:0;"><i class="bi bi-telephone-fill text-primary fs-5"></i></div>
                <div><h6 class="fw-bold mb-0">Phone</h6><p class="text-muted small mb-0">1800-123-4567 (Toll Free)<br>Mon-Sat, 9 AM - 7 PM</p></div>
            </div>
            <div class="d-flex gap-3 mb-4">
                <div style="width:48px;height:48px;background:#fff3cd;border-radius:12px;display:flex;align-items:center;justify-content:center;flex-shrink:0;"><i class="bi bi-envelope-fill text-warning fs-5"></i></div>
                <div><h6 class="fw-bold mb-0">Email</h6><p class="text-muted small mb-0">info@collegekampus.in<br>admissions@collegekampus.in</p></div>
            </div>
        </div>

        <div class="col-lg-6">
            <?php if ($success): ?>
            <div class="alert alert-success ck-flash text-center py-4">
                <i class="bi bi-check-circle-fill fs-1 text-success d-block mb-2"></i>
                <h4>Message Sent!</h4>
                <p class="mb-3">Thank you for reaching out. Our team will get back to you within 24 hours.</p>
                <a href="/colleges.php" class="btn btn-ck-primary">Browse Colleges</a>
            </div>
            <?php else: ?>
            <?php if ($errors): ?>
            <div class="alert alert-danger ck-flash"><i class="bi bi-exclamation-triangle me-2"></i><?= implode('<br>', array_map('htmlspecialchars', $errors)) ?></div>
            <?php endif; ?>
            <div class="ck-form-card">
                <h4 class="fw-bold mb-4">Send a Message</h4>
                <form method="post" novalidate>
                    <div class="row g-3">
                        <div class="col-md-6"><label class="form-label">Your Name <span class="text-danger">*</span></label><input type="text" name="student_name" class="form-control" required maxlength="150" value="<?= htmlspecialchars($_POST['student_name'] ?? '') ?>"></div>
                        <div class="col-md-6"><label class="form-label">Email <span class="text-danger">*</span></label><input type="email" name="email" class="form-control" required maxlength="180" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"></div>
                        <div class="col-md-6"><label class="form-label">Mobile Number</label><input type="tel" name="phone" class="form-control" maxlength="15" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>"></div>
                        <div class="col-md-6">
                            <label class="form-label">Subject</label>
                            <select name="subject" class="form-select">
                                <option value="">-- Select Topic --</option>
                                <option value="Admissions Enquiry">Admissions Enquiry</option>
                                <option value="College Partnership">College Partnership</option>
                                <option value="Technical Support">Technical Support</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div class="col-12"><label class="form-label">Message <span class="text-danger">*</span></label><textarea name="message" class="form-control" rows="5" required maxlength="2000" placeholder="How can we help you?"><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea></div>
                        <div class="col-12"><button type="submit" class="btn btn-ck-primary w-100 py-2"><i class="bi bi-send me-2"></i>Send Message</button></div>
                    </div>
                </form>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
</main>
<?php include __DIR__ . '/includes/footer.php'; ?>
