<?php
// INTEGRATION: Admin portal reads this DB. Partner portal writes to colleges/courses tables.
// Shared DB: collegekampus -- see database/schema.sql

require_once __DIR__ . '/config/db.php';

$pdo  = getPDO();
$slug = trim($_GET['slug'] ?? '');

if (!$slug) { header('Location: /colleges.php'); exit; }

$stmt = $pdo->prepare('SELECT * FROM colleges WHERE slug = ? AND is_active = 1');
$stmt->execute([$slug]);
$college = $stmt->fetch();

if (!$college) {
    http_response_code(404);
    $pageTitle = 'Not Found';
    include __DIR__ . '/includes/header.php';
    echo '<main id="main-content"><div class="container py-5 text-center"><h2>College not found.</h2><a href="/colleges.php" class="btn btn-ck-primary mt-3">Browse Colleges</a></div></main>';
    include __DIR__ . '/includes/footer.php';
    exit;
}

$cStmt = $pdo->prepare('
    SELECT cc.id AS cc_id, cr.id AS course_id, cr.name AS course_name, cr.category, cr.degree_level, cr.duration_years,
           cc.annual_fees, cc.seats, cc.eligibility, cc.application_deadline
    FROM college_courses cc
    JOIN courses cr ON cc.course_id = cr.id
    WHERE cc.college_id = ? AND cc.is_active = 1
    ORDER BY cc.annual_fees DESC
');
$cStmt->execute([$college['id']]);
$courses = $cStmt->fetchAll();

$flash = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['inquiry_submit'])) {
    $name    = trim($_POST['student_name'] ?? '');
    $email   = trim($_POST['email']        ?? '');
    $phone   = trim($_POST['phone']        ?? '');
    $course  = trim($_POST['course_interest'] ?? '');
    $msg     = trim($_POST['message']      ?? '');
    $errors  = [];
    if (!$name)  $errors[] = 'Name is required.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email required.';
    if ($phone && !preg_match('/^[6-9]\d{9}$/', preg_replace('/\D/','',$phone))) $errors[] = 'Valid mobile required.';
    if (!$errors) {
        $ins = $pdo->prepare('INSERT INTO leads (student_name, email, phone, course_interest, college_id, message, source) VALUES (?,?,?,?,?,?,?)');
        $ins->execute([$name, $email, $phone, $course, $college['id'], $msg, 'college_detail']);
        $flash = 'success';
    } else {
        $flash = implode(' ', $errors);
    }
}

function feeINR($n) {
    if (!$n || $n == 0) return 'N/A';
    return '&#8377;' . number_format((float)$n, 0, '.', ',');
}
function degreeLabel($d) {
    return ['ug'=>'UG','pg'=>'PG','phd'=>'PhD','diploma'=>'Diploma','certificate'=>'Certificate'][$d] ?? strtoupper($d);
}
function typeClass($t) {
    return ['government'=>'badge-government','private'=>'badge-private','deemed'=>'badge-deemed'][$t] ?? 'bg-secondary';
}

$pageTitle = htmlspecialchars($college['name']);
$pageDesc  = mb_strimwidth(strip_tags($college['description'] ?? ''), 0, 160, '...');

include __DIR__ . '/includes/header.php';
?>
<main id="main-content">

<div class="container-fluid px-0">
    <?php if ($college['banner_url']): ?>
        <img src="<?= htmlspecialchars($college['banner_url']) ?>" class="ck-banner" alt="<?= htmlspecialchars($college['name']) ?> banner">
    <?php else: ?>
        <div class="ck-banner-placeholder"><i class="bi bi-building-fill-gear"></i></div>
    <?php endif; ?>
</div>

<div class="container py-4">
    <?php if ($flash === 'success'): ?>
    <div class="alert alert-success ck-flash alert-dismissible fade show">
        <i class="bi bi-check-circle-fill me-2"></i>Your enquiry was submitted! Our team will contact you shortly.
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php elseif ($flash && $flash !== 'success'): ?>
    <div class="alert alert-danger ck-flash"><i class="bi bi-exclamation-circle me-2"></i><?= htmlspecialchars($flash) ?></div>
    <?php endif; ?>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="d-flex align-items-start gap-3 mb-4 flex-wrap">
                <div class="ck-detail-logo">
                    <?php if ($college['logo_url']): ?>
                        <img src="<?= htmlspecialchars($college['logo_url']) ?>" alt="logo" style="width:100%;height:100%;object-fit:contain;">
                    <?php else: ?>
                        <div class="ck-college-logo" style="width:90px;height:90px;border-radius:12px;">
                            <?= strtoupper(implode('', array_slice(array_map(fn($w)=>$w[0], explode(' ',$college['name'])),0,2))) ?>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="flex-grow-1">
                    <h1 class="h3 fw-bold mb-1"><?= htmlspecialchars($college['name']) ?></h1>
                    <div class="d-flex flex-wrap gap-2 align-items-center">
                        <span class="badge <?= typeClass($college['type']) ?>"><?= ucfirst($college['type']) ?></span>
                        <span class="text-muted small"><i class="bi bi-geo-alt me-1"></i><?= htmlspecialchars($college['city'] . ', ' . $college['state']) ?></span>
                        <?php if ($college['established_year']): ?>
                        <span class="text-muted small"><i class="bi bi-calendar me-1"></i>Est. <?= htmlspecialchars($college['established_year']) ?></span>
                        <?php endif; ?>
                        <?php if ($college['accreditation']): ?>
                        <span class="text-muted small"><i class="bi bi-patch-check-fill text-success me-1"></i><?= htmlspecialchars($college['accreditation']) ?></span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-6 col-md-3"><div class="ck-stat-box"><div class="ck-stat-val text-success"><?= count($courses) ?></div><div class="ck-stat-lbl">Courses</div></div></div>
                <div class="col-6 col-md-3"><div class="ck-stat-box"><div class="ck-stat-val"><?= $college['ranking_score'] ? number_format($college['ranking_score'],1) : 'N/A' ?></div><div class="ck-stat-lbl">Ranking Score</div></div></div>
                <div class="col-6 col-md-3"><div class="ck-stat-box"><?php $minF = min(array_column($courses,'annual_fees') ?: [0]); ?><div class="ck-stat-val" style="font-size:1rem"><?= $minF ? feeINR($minF) : 'N/A' ?></div><div class="ck-stat-lbl">Min Fees/yr</div></div></div>
                <div class="col-6 col-md-3"><div class="ck-stat-box"><div class="ck-stat-val"><?= htmlspecialchars($college['established_year'] ?? '-') ?></div><div class="ck-stat-lbl">Established</div></div></div>
            </div>

            <?php if ($college['description']): ?>
            <div class="ck-form-card mb-4">
                <h5 class="fw-bold mb-3"><i class="bi bi-info-circle me-2 text-primary"></i>About <?= htmlspecialchars($college['name']) ?></h5>
                <p class="mb-0"><?= nl2br(htmlspecialchars($college['description'])) ?></p>
            </div>
            <?php endif; ?>

            <div class="ck-form-card mb-4">
                <h5 class="fw-bold mb-3"><i class="bi bi-book me-2 text-primary"></i>Courses Offered</h5>
                <?php if ($courses): ?>
                <div class="table-responsive">
                    <table class="table ck-courses-table align-middle">
                        <thead><tr><th>Course</th><th>Level</th><th>Duration</th><th>Annual Fees</th><th>Seats</th><th>Deadline</th><th></th></tr></thead>
                        <tbody>
                        <?php foreach ($courses as $cr): ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($cr['course_name']) ?></strong>
                                    <?php if ($cr['eligibility']): ?><div class="small text-muted mt-1"><i class="bi bi-info-circle me-1"></i><?= htmlspecialchars($cr['eligibility']) ?></div><?php endif; ?>
                                </td>
                                <td><span class="badge bg-secondary"><?= degreeLabel($cr['degree_level']) ?></span></td>
                                <td><?= htmlspecialchars($cr['duration_years']) ?> yr<?= $cr['duration_years'] != 1 ? 's' : '' ?></td>
                                <td class="fw-semibold"><?= feeINR($cr['annual_fees']) ?></td>
                                <td><?= $cr['seats'] ?: '-' ?></td>
                                <td class="small"><?= $cr['application_deadline'] ? date('d M Y', strtotime($cr['application_deadline'])) : '-' ?></td>
                                <td><a href="/apply.php?college_id=<?= $college['id'] ?>&amp;course_id=<?= $cr['course_id'] ?>" class="btn btn-ck-primary btn-sm">Apply</a></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?><p class="text-muted">No courses listed.</p><?php endif; ?>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="ck-inquiry-card">
                <h5 class="mb-1"><i class="bi bi-send me-2"></i>Send Enquiry</h5>
                <p class="text-white-50 small mb-3">Get free counselling from our team.</p>
                <form method="post">
                    <input type="hidden" name="inquiry_submit" value="1">
                    <div class="mb-3"><input type="text" name="student_name" class="form-control" placeholder="Your Full Name" required maxlength="150"></div>
                    <div class="mb-3"><input type="email" name="email" class="form-control" placeholder="Email Address" required maxlength="180"></div>
                    <div class="mb-3"><input type="tel" name="phone" class="form-control" placeholder="Mobile Number" maxlength="15"></div>
                    <div class="mb-3">
                        <select name="course_interest" class="form-select">
                            <option value="">Select Course Interest</option>
                            <?php foreach ($courses as $cr): ?>
                            <option value="<?= htmlspecialchars($cr['course_name']) ?>"><?= htmlspecialchars($cr['course_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3"><textarea name="message" class="form-control" rows="3" placeholder="Your message..." maxlength="1000"></textarea></div>
                    <button type="submit" class="btn btn-ck-primary w-100"><i class="bi bi-send me-2"></i>Send Enquiry</button>
                </form>
            </div>
            <?php if ($courses): ?>
            <div class="ck-form-card mt-3 text-center">
                <p class="mb-2 small text-muted">Ready to apply directly?</p>
                <a href="/apply.php?college_id=<?= $college['id'] ?>" class="btn btn-outline-success w-100">
                    <i class="bi bi-pencil-square me-2"></i>Start Application
                </a>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
</main>
<?php include __DIR__ . '/includes/footer.php'; ?>
