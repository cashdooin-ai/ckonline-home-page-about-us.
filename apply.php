<?php
// INTEGRATION: Admin portal reads this DB. Partner portal writes to colleges/courses tables.
// Shared DB: collegekampus -- see database/schema.sql

require_once __DIR__ . '/config/db.php';

$pdo = getPDO();
$collegeId = !empty($_GET['college_id']) ? (int)$_GET['college_id'] : 0;
$courseId  = !empty($_GET['course_id'])  ? (int)$_GET['course_id']  : 0;

$colleges = $pdo->query("SELECT id, name FROM colleges WHERE is_active=1 ORDER BY name")->fetchAll();
$selectedCollegeCourses = [];
if ($collegeId) {
    $cs = $pdo->prepare("SELECT cr.id, cr.name FROM college_courses cc JOIN courses cr ON cc.course_id=cr.id WHERE cc.college_id=? AND cc.is_active=1 ORDER BY cr.name");
    $cs->execute([$collegeId]);
    $selectedCollegeCourses = $cs->fetchAll();
}

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postCollegeId = !empty($_POST['college_id'])  ? (int)$_POST['college_id']  : 0;
    $postCourseId  = !empty($_POST['course_id'])   ? (int)$_POST['course_id']   : 0;
    $name          = trim($_POST['student_name']   ?? '');
    $email         = trim($_POST['email']          ?? '');
    $phone         = trim($_POST['phone']          ?? '');
    $marks10       = trim($_POST['marks_10']       ?? '');
    $marks12       = trim($_POST['marks_12']       ?? '');
    $entranceScore = trim($_POST['entrance_score'] ?? '');
    $message       = trim($_POST['message']        ?? '');

    if (!$name)  $errors[] = 'Full name is required.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required.';
    if ($phone && !preg_match('/^[6-9]\d{9}$/', preg_replace('/\D/','',$phone))) $errors[] = 'Valid 10-digit mobile required.';
    if (!$postCollegeId) $errors[] = 'Please select a college.';
    if (!$postCourseId)  $errors[] = 'Please select a course.';

    if (!$errors) {
        $chk = $pdo->prepare("SELECT id FROM college_courses WHERE college_id=? AND course_id=? AND is_active=1");
        $chk->execute([$postCollegeId, $postCourseId]);
        if (!$chk->fetch()) $errors[] = 'Selected course is not offered by this college.';
    }

    if (!$errors) {
        try {
            $pdo->beginTransaction();
            $cnStmt = $pdo->prepare("SELECT name FROM courses WHERE id=?");
            $cnStmt->execute([$postCourseId]);
            $courseName = $cnStmt->fetchColumn() ?: '';
            $noteMsg = "10th: $marks10% | 12th: $marks12% | Entrance: $entranceScore\n$message";
            $ins = $pdo->prepare("INSERT INTO leads (student_name,email,phone,course_interest,college_id,message,source) VALUES (?,?,?,?,?,?,?)");
            $ins->execute([$name, $email, $phone, $courseName, $postCollegeId, trim($noteMsg), 'apply']);
            $leadId = (int)$pdo->lastInsertId();
            $app = $pdo->prepare("INSERT INTO applications (lead_id, college_id, course_id, status) VALUES (?,?,?,?)");
            $app->execute([$leadId, $postCollegeId, $postCourseId, 'submitted']);
            $pdo->commit();
            $success = true;
        } catch (PDOException $e) {
            $pdo->rollBack();
            $errors[] = 'Could not submit application. Please try again.';
        }
    }
}

$pageTitle = 'Apply Now';
include __DIR__ . '/includes/header.php';
?>
<main id="main-content">

<section class="ck-page-hero">
    <div class="container">
        <h1 class="mb-2"><i class="bi bi-pencil-square me-2"></i>Apply Now</h1>
        <p class="mb-0">Submit your college application. Our counsellors will guide you through the next steps.</p>
    </div>
</section>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <?php if ($success): ?>
            <div class="alert alert-success ck-flash text-center py-4">
                <i class="bi bi-check-circle-fill fs-1 text-success d-block mb-2"></i>
                <h4>Application Submitted!</h4>
                <p class="mb-3">Your application has been received. Our admissions team will contact you within 2 working days.</p>
                <a href="/colleges.php" class="btn btn-ck-primary me-2">Browse More Colleges</a>
                <a href="/index.html" class="btn btn-outline-secondary">Back to Home</a>
            </div>
            <?php else: ?>
            <?php if ($errors): ?>
            <div class="alert alert-danger ck-flash"><i class="bi bi-exclamation-triangle-fill me-2"></i><?= implode('<br>', array_map('htmlspecialchars', $errors)) ?></div>
            <?php endif; ?>
            <div class="ck-form-card">
                <h4 class="fw-bold mb-4 text-center">College Application Form</h4>
                <form method="post" novalidate>
                    <h6 class="text-muted text-uppercase small mb-3 fw-bold border-bottom pb-2">Personal Information</h6>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label">Full Name <span class="text-danger">*</span></label>
                            <input type="text" name="student_name" class="form-control" required maxlength="150" value="<?= htmlspecialchars($_POST['student_name'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email Address <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control" required maxlength="180" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Mobile Number</label>
                            <input type="tel" name="phone" class="form-control" maxlength="15" placeholder="10-digit mobile" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
                        </div>
                    </div>
                    <h6 class="text-muted text-uppercase small mb-3 fw-bold border-bottom pb-2">Academic Details</h6>
                    <div class="row g-3 mb-4">
                        <div class="col-md-4"><label class="form-label">10th Marks (%)</label><input type="number" name="marks_10" class="form-control" min="0" max="100" step="0.01" placeholder="e.g. 85.5" value="<?= htmlspecialchars($_POST['marks_10'] ?? '') ?>"></div>
                        <div class="col-md-4"><label class="form-label">12th / Diploma Marks (%)</label><input type="number" name="marks_12" class="form-control" min="0" max="100" step="0.01" placeholder="e.g. 78.0" value="<?= htmlspecialchars($_POST['marks_12'] ?? '') ?>"></div>
                        <div class="col-md-4"><label class="form-label">Entrance Exam Score</label><input type="text" name="entrance_score" class="form-control" placeholder="JEE/NEET/CAT" maxlength="50" value="<?= htmlspecialchars($_POST['entrance_score'] ?? '') ?>"></div>
                    </div>
                    <h6 class="text-muted text-uppercase small mb-3 fw-bold border-bottom pb-2">College &amp; Course</h6>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label">Select College <span class="text-danger">*</span></label>
                            <select name="college_id" class="form-select" id="apply-college-sel" required>
                                <option value="">-- Choose College --</option>
                                <?php foreach ($colleges as $col): ?>
                                <option value="<?= $col['id'] ?>" <?= (isset($_POST['college_id'])?(int)$_POST['college_id']:$collegeId)==$col['id']?'selected':'' ?>><?= htmlspecialchars($col['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Select Course <span class="text-danger">*</span></label>
                            <select name="course_id" class="form-select" id="apply-course-sel" required>
                                <option value="">-- Choose Course --</option>
                                <?php foreach ($selectedCollegeCourses as $cr): ?>
                                <option value="<?= $cr['id'] ?>" <?= (isset($_POST['course_id'])?(int)$_POST['course_id']:$courseId)==$cr['id']?'selected':'' ?>><?= htmlspecialchars($cr['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="mb-4"><label class="form-label">Message / Additional Info</label><textarea name="message" class="form-control" rows="3" maxlength="1000"><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea></div>
                    <button type="submit" class="btn btn-ck-primary w-100 py-2 fs-5"><i class="bi bi-send-fill me-2"></i>Submit Application</button>
                </form>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
</main>
<script>
(function(){
    var collSel = document.getElementById('apply-college-sel');
    var courseSel = document.getElementById('apply-course-sel');
    if (!collSel || !courseSel) return;
    collSel.addEventListener('change', function(){
        var cid = this.value;
        courseSel.innerHTML = '<option value="">Loading...</option>';
        if (!cid) { courseSel.innerHTML = '<option value="">-- Choose Course --</option>'; return; }
        fetch('/api/college_courses.php?college_id=' + cid)
            .then(r => r.json())
            .then(courses => {
                courseSel.innerHTML = '<option value="">-- Choose Course --</option>';
                courses.forEach(function(c){ var opt = document.createElement('option'); opt.value = c.id; opt.textContent = c.name; courseSel.appendChild(opt); });
            }).catch(() => { courseSel.innerHTML = '<option value="">Error loading courses</option>'; });
    });
})();
</script>
<?php include __DIR__ . '/includes/footer.php'; ?>
