<!DOCTYPE html>
<?php
require_once __DIR__ . '/auth.php';
require_once dirname(__DIR__) . '/config/db.php';

$db = getDB();
$flash = $_SESSION['flash'] ?? '';
unset($_SESSION['flash']);

$search = trim($_GET['search'] ?? '');
$editId = (int)($_GET['id'] ?? 0);
$isNew  = isset($_GET['new']);

$colleges = [];
try {
    if ($search !== '') {
        $stmt = $db->prepare("SELECT id, name, city, state, type, min_fees, max_fees FROM colleges WHERE name LIKE ? ORDER BY name ASC LIMIT 100");
        $stmt->execute(['%' . $search . '%']);
    } else {
        $stmt = $db->query("SELECT id, name, city, state, type, min_fees, max_fees FROM colleges ORDER BY name ASC LIMIT 100");
    }
    $colleges = $stmt->fetchAll();
} catch (Throwable $e) {}

$editCollege = null;
$editCourses = [];
if ($editId) {
    try {
        $stmt = $db->prepare("SELECT * FROM colleges WHERE id = ?");
        $stmt->execute([$editId]);
        $editCollege = $stmt->fetch();
        if ($editCollege) {
            $cs = $db->prepare("SELECT cr.id, cr.name, cr.category, cr.degree_level, cr.duration_years, cc.annual_fees, cc.eligibility FROM college_courses cc JOIN courses cr ON cr.id = cc.course_id WHERE cc.college_id = ? ORDER BY cr.name");
            $cs->execute([$editId]);
            $editCourses = $cs->fetchAll();
        }
    } catch (Throwable $e) {}
}
?>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Colleges | CollegeKampus Admin</title>
<meta name="robots" content="noindex,nofollow">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<style>
body{background:#f1f5f9;font-family:'Inter',sans-serif;}
.topbar{background:#0f172a;color:#fff;padding:14px 24px;display:flex;align-items:center;gap:16px;}
.topbar a{color:rgba(255,255,255,.7);text-decoration:none;font-size:.875rem;}
.topbar a:hover{color:#fff;}
.admin-card{background:#fff;border-radius:12px;border:1px solid #e2e8f0;padding:24px;}
.table th{font-size:.78rem;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.05em;white-space:nowrap;}
.table td{font-size:.875rem;vertical-align:middle;}
.ai-note{font-size:.78rem;color:#b45309;background:#fffbeb;border:1px solid #fde68a;border-radius:6px;padding:8px 12px;margin-bottom:12px;}
</style>
</head>
<body>

<div class="topbar d-flex align-items-center gap-3">
    <div style="font-size:1.1rem;font-weight:800;">🏫 Colleges</div>
    <div class="ms-auto d-flex gap-3">
        <a href="index.php"><i class="bi bi-arrow-left"></i> Back to Admin</a>
        <a href="generate.php"><i class="bi bi-robot"></i> AI Generate</a>
        <a href="change-password.php"><i class="bi bi-key"></i> Change Password</a>
        <a href="/dashboard/" target="_blank"><i class="bi bi-house"></i> Front-end</a>
    </div>
</div>

<div class="container-fluid py-4 px-4">
    <?php if ($flash): ?>
    <div class="alert alert-<?= strpos($flash,'Error')!==false?'danger':'success' ?> alert-dismissible py-2 mb-4" role="alert">
        <?= htmlspecialchars($flash) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <div class="row g-4">
        <!-- List -->
        <div class="col-lg-5">
            <div class="admin-card">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0 fw-bold">All Colleges</h5>
                    <a href="?new=1" class="btn btn-sm btn-primary fw-bold"><i class="bi bi-plus-lg"></i> Add College</a>
                </div>
                <form method="GET" class="mb-3">
                    <input type="text" name="search" class="form-control form-control-sm" placeholder="Search by name…" value="<?= htmlspecialchars($search) ?>">
                </form>
                <?php if (empty($colleges)): ?>
                <p class="text-muted text-center py-4">No colleges found.</p>
                <?php else: ?>
                <div class="table-responsive" style="max-height:600px;overflow-y:auto;">
                <table class="table table-hover table-sm mb-0">
                    <thead class="table-light"><tr><th>Name</th><th>City</th><th>Type</th><th></th></tr></thead>
                    <tbody>
                    <?php foreach ($colleges as $c): ?>
                    <tr class="<?= (int)$c['id'] === $editId ? 'table-primary' : '' ?>">
                        <td><?= htmlspecialchars(mb_strimwidth($c['name'], 0, 34, '…')) ?></td>
                        <td><?= htmlspecialchars($c['city'] ?? '—') ?></td>
                        <td><span class="badge bg-secondary" style="font-size:.68rem;"><?= htmlspecialchars($c['type'] ?? '') ?></span></td>
                        <td><a href="?id=<?= $c['id'] ?>" class="btn btn-xs btn-outline-secondary btn-sm"><i class="bi bi-pencil"></i></a></td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Edit -->
        <div class="col-lg-7">
            <?php if ($editCollege || $isNew): ?>
            <div class="admin-card mb-4">
                <h5 class="fw-bold mb-3"><?= $editCollege ? '✏️ Edit: ' . htmlspecialchars($editCollege['name']) : '➕ New College' ?></h5>
                <form method="POST" action="save-college.php">
                    <?php if ($editCollege): ?><input type="hidden" name="id" value="<?= $editCollege['id'] ?>"><?php endif; ?>
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label fw-bold">Name *</label>
                            <input type="text" name="name" class="form-control" required value="<?= htmlspecialchars($editCollege['name'] ?? '') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Type</label>
                            <select name="type" class="form-select">
                                <?php foreach (['private','government','deemed'] as $t): ?>
                                <option value="<?= $t ?>" <?= ($editCollege['type'] ?? '') === $t ? 'selected' : '' ?>><?= ucfirst($t) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">City</label>
                            <input type="text" name="city" class="form-control" value="<?= htmlspecialchars($editCollege['city'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">State</label>
                            <input type="text" name="state" class="form-control" value="<?= htmlspecialchars($editCollege['state'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Min Fees (INR)</label>
                            <input type="number" name="min_fees" class="form-control" value="<?= $editCollege['min_fees'] ?? '' ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Max Fees (INR)</label>
                            <input type="number" name="max_fees" class="form-control" value="<?= $editCollege['max_fees'] ?? '' ?>">
                        </div>
                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center">
                                <label class="form-label fw-bold mb-0">Description</label>
                                <?php if ($editCollege): ?>
                                <button type="button" class="btn btn-outline-primary btn-sm" id="aiDescBtn" onclick="aiRewriteDescription(<?= $editCollege['id'] ?>)">✨ AI Rewrite</button>
                                <?php endif; ?>
                            </div>
                            <textarea name="description" id="collegeDescription" class="form-control" rows="4"><?= htmlspecialchars($editCollege['description'] ?? '') ?></textarea>
                            <small id="aiDescStatus" class="text-muted"></small>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary fw-bold"><i class="bi bi-save"></i> Save College</button>
                            <a href="colleges.php" class="btn btn-outline-secondary">Cancel</a>
                        </div>
                    </div>
                </form>
            </div>
            <?php endif; ?>

            <?php if ($editCollege): ?>
            <div class="admin-card">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0 fw-bold">Courses &amp; Fees (<?= count($editCourses) ?>)</h5>
                    <button type="button" class="btn btn-outline-primary btn-sm" id="aiCoursesBtn" onclick="aiSuggestCourses(<?= $editCollege['id'] ?>)">✨ AI Suggest Courses &amp; Fees</button>
                </div>
                <?php if ($editCourses): ?>
                <table class="table table-sm mb-0">
                    <thead class="table-light"><tr><th>Course</th><th>Category</th><th>Level</th><th>Duration</th><th>Fees/Year</th></tr></thead>
                    <tbody>
                    <?php foreach ($editCourses as $cr): ?>
                    <tr>
                        <td><?= htmlspecialchars($cr['name']) ?></td>
                        <td><?= htmlspecialchars($cr['category'] ?? '—') ?></td>
                        <td><?= htmlspecialchars(strtoupper($cr['degree_level'] ?? '—')) ?></td>
                        <td><?= $cr['duration_years'] ? $cr['duration_years'] . ' yr' : '—' ?></td>
                        <td><?= $cr['annual_fees'] ? '₹' . number_format($cr['annual_fees']) : '—' ?></td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <p class="text-muted text-center py-3 mb-0">No courses yet. Use "AI Suggest Courses &amp; Fees" above to draft a starting list.</p>
                <?php endif; ?>
                <div id="aiCoursesStatus" class="mt-2" style="font-size:.82rem;"></div>
                <div id="aiCoursesPanel" style="display:none;margin-top:14px;">
                    <div class="ai-note">⚠️ AI-generated estimates based on general knowledge of this institution — not verified against a live source. Review and edit before saving.</div>
                    <div class="table-responsive">
                    <table class="table table-sm" id="aiCoursesTable">
                        <thead class="table-light">
                            <tr>
                                <th style="width:24px;"><input type="checkbox" id="aiSelectAll" checked onchange="document.querySelectorAll('.ai-course-check').forEach(c=>c.checked=this.checked)"></th>
                                <th>Course</th><th>Category</th><th>Level</th><th>Yrs</th><th>Fees/Year</th><th>Eligibility</th>
                            </tr>
                        </thead>
                        <tbody id="aiCoursesTbody"></tbody>
                    </table>
                    </div>
                    <button type="button" class="btn btn-success btn-sm fw-bold" onclick="aiSaveCourses(<?= $editCollege['id'] ?>)">Save Selected Courses</button>
                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="document.getElementById('aiCoursesPanel').style.display='none'">Discard</button>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
const AI_CATEGORIES = ['engineering','management','medical','law','arts','science','commerce','design','other'];
const AI_LEVELS = ['certificate','diploma','ug','pg','phd'];

async function aiRewriteDescription(collegeId) {
    const btn = document.getElementById('aiDescBtn');
    const status = document.getElementById('aiDescStatus');
    btn.disabled = true; btn.textContent = 'Generating…'; status.textContent = '';
    try {
        const body = new FormData();
        body.append('college_id', collegeId);
        const r = await fetch('/dashboard/api/ai-rewrite-description.php', { method: 'POST', body });
        const d = await r.json();
        if (d.success) {
            document.getElementById('collegeDescription').value = d.description;
            status.textContent = 'Draft inserted — review and Save College to keep it.';
            status.style.color = '#16a34a';
        } else {
            status.textContent = 'Error: ' + (d.error || 'Generation failed');
            status.style.color = '#dc2626';
        }
    } catch (e) {
        status.textContent = 'Network error';
        status.style.color = '#dc2626';
    }
    btn.disabled = false; btn.textContent = '✨ AI Rewrite';
}

async function aiSuggestCourses(collegeId) {
    const btn = document.getElementById('aiCoursesBtn');
    const status = document.getElementById('aiCoursesStatus');
    btn.disabled = true; btn.textContent = 'Thinking…'; status.textContent = '';
    try {
        const body = new FormData();
        body.append('college_id', collegeId);
        const r = await fetch('/dashboard/api/ai-suggest-courses.php', { method: 'POST', body });
        const d = await r.json();
        if (!d.success || !Array.isArray(d.suggestions) || d.suggestions.length === 0) {
            status.innerHTML = '<span class="text-danger">Error: ' + (d.error || 'No suggestions returned — try again.') + '</span>';
        } else {
            const tbody = document.getElementById('aiCoursesTbody');
            tbody.innerHTML = d.suggestions.map((c, i) => `
                <tr>
                    <td><input type="checkbox" class="ai-course-check" checked data-idx="${i}"></td>
                    <td><input type="text" class="form-control form-control-sm ai-c-name" value="${(c.name || '').replace(/"/g, '&quot;')}"></td>
                    <td>
                        <select class="form-select form-select-sm ai-c-category">
                            ${AI_CATEGORIES.map(cat => `<option value="${cat}" ${cat === c.category ? 'selected' : ''}>${cat}</option>`).join('')}
                        </select>
                    </td>
                    <td>
                        <select class="form-select form-select-sm ai-c-level">
                            ${AI_LEVELS.map(l => `<option value="${l}" ${l === c.degree_level ? 'selected' : ''}>${l}</option>`).join('')}
                        </select>
                    </td>
                    <td><input type="number" step="0.5" class="form-control form-control-sm ai-c-duration" value="${c.duration_years || 4}" style="width:70px;"></td>
                    <td><input type="number" class="form-control form-control-sm ai-c-fees" value="${c.annual_fees || ''}" style="width:110px;"></td>
                    <td><input type="text" class="form-control form-control-sm ai-c-eligibility" value="${(c.eligibility || '').replace(/"/g, '&quot;')}"></td>
                </tr>
            `).join('');
            document.getElementById('aiCoursesPanel').style.display = '';
            status.textContent = '';
        }
    } catch (e) {
        status.innerHTML = '<span class="text-danger">Network error</span>';
    }
    btn.disabled = false; btn.innerHTML = '✨ AI Suggest Courses &amp; Fees';
}

async function aiSaveCourses(collegeId) {
    const rows = [...document.querySelectorAll('#aiCoursesTbody tr')].filter(tr => tr.querySelector('.ai-course-check').checked);
    if (rows.length === 0) { alert('Select at least one course to save.'); return; }
    const courses = rows.map(tr => ({
        name: tr.querySelector('.ai-c-name').value.trim(),
        category: tr.querySelector('.ai-c-category').value,
        degree_level: tr.querySelector('.ai-c-level').value,
        duration_years: parseFloat(tr.querySelector('.ai-c-duration').value) || 4,
        annual_fees: parseFloat(tr.querySelector('.ai-c-fees').value) || 0,
        eligibility: tr.querySelector('.ai-c-eligibility').value.trim(),
    })).filter(c => c.name);

    const status = document.getElementById('aiCoursesStatus');
    status.textContent = 'Saving…';
    try {
        const r = await fetch('/dashboard/api/college-courses-save.php', {
            method: 'POST', headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ college_id: collegeId, courses })
        });
        const d = await r.json();
        if (d.success) {
            location.reload();
        } else {
            status.innerHTML = '<span class="text-danger">Error: ' + (d.error || 'Save failed') + '</span>';
        }
    } catch (e) {
        status.innerHTML = '<span class="text-danger">Network error</span>';
    }
}
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
