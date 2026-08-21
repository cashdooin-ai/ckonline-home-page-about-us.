<!DOCTYPE html>
<?php
require_once __DIR__ . '/auth.php';
require_once dirname(__DIR__) . '/config/db.php';

$db = getDB();
$flash = $_SESSION['flash'] ?? '';
unset($_SESSION['flash']);

// Detect which columns actually exist on `colleges` rather than assuming
// the base schema.sql shape - the live online-colleges dataset added
// college_type/institution_type/online_mode/etc. via
// database/seed_online_colleges.sql, and college-detail.php already reads
// this way for the same reason. The previous version of this page hardcoded
// a `type` column that doesn't exist on the live table (it's `college_type`
// there), so every query below failed and was silently swallowed by a
// try/catch, always showing "No colleges found" with no hint why.
$allCols = [];
try {
    $allCols = $db->query("SHOW COLUMNS FROM colleges")->fetchAll(PDO::FETCH_COLUMN);
} catch (Throwable $e) {}
$has = array_flip($allCols);

$search   = trim($_GET['search'] ?? '');
$editId   = (int)($_GET['id'] ?? 0);
$isNew    = isset($_GET['new']);
$loadError = '';

$listSelect = 'id, name';
foreach (['city', 'state', 'college_type', 'institution_type', 'min_fees', 'max_fees'] as $col) {
    if (isset($has[$col])) $listSelect .= ", $col";
}

// This admin manages online.collegekampus.com's curated online/distance
// universities, not the whole shared `colleges` table (which also holds
// every regular offline college from explore.collegekampus.com's dataset,
// via database/seed_online_colleges.sql's is_online flag). Without this
// filter the list here is dominated by unrelated regular colleges.
$onlineWhere = isset($has['is_online']) ? 'is_online = 1' : '1=1';

$colleges = [];
try {
    if ($search !== '') {
        $stmt = $db->prepare("SELECT $listSelect FROM colleges WHERE $onlineWhere AND name LIKE ? ORDER BY name ASC LIMIT 100");
        $stmt->execute(['%' . $search . '%']);
    } else {
        $stmt = $db->query("SELECT $listSelect FROM colleges WHERE $onlineWhere ORDER BY name ASC LIMIT 100");
    }
    $colleges = $stmt->fetchAll();
} catch (Throwable $e) {
    $loadError = $e->getMessage();
}

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

// Field definitions for the edit form: only rendered/saved if the column
// actually exists on this install. type = text|number|select|checkbox.
$typeCol = isset($has['college_type']) ? 'college_type' : (isset($has['type']) ? 'type' : null);
$fieldDefs = [
    'is_online'        => ['label' => 'Online / Distance College (shows on the public site)', 'type' => 'checkbox', 'col' => 12, 'default_checked_if_new' => true],
    'short_name'       => ['label' => 'Short Name', 'type' => 'text', 'col' => 6],
    'institution_type' => ['label' => 'Institution Type', 'type' => 'select', 'options' => ['university', 'college'], 'col' => 6],
    'online_mode'      => ['label' => 'Delivery Mode', 'type' => 'select', 'options' => ['online', 'distance', 'hybrid'], 'col' => 6],
    'city'             => ['label' => 'City', 'type' => 'text', 'col' => 6],
    'state'            => ['label' => 'State', 'type' => 'text', 'col' => 6],
    'established_year' => ['label' => 'Established Year', 'type' => 'number', 'col' => 6],
    'accreditation'    => ['label' => 'Accreditation', 'type' => 'text', 'col' => 6],
    'naac_grade'       => ['label' => 'NAAC Grade', 'type' => 'text', 'col' => 4],
    'rating'           => ['label' => 'Rating (0-5)', 'type' => 'number', 'step' => '0.1', 'col' => 4],
    'ugc_approved'     => ['label' => 'UGC-DEB Approved', 'type' => 'checkbox', 'col' => 4],
    'min_fees'         => ['label' => 'Min Fees (INR)', 'type' => 'number', 'col' => 6],
    'max_fees'         => ['label' => 'Max Fees (INR)', 'type' => 'number', 'col' => 6],
    'website'          => ['label' => 'Website', 'type' => 'text', 'col' => 12],
];
if ($typeCol !== null) {
    $fieldDefs = [$typeCol => ['label' => 'Type', 'type' => 'select', 'options' => ['government', 'private', 'deemed', 'autonomous'], 'col' => 6]] + $fieldDefs;
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
                <?php if ($loadError): ?>
                <div class="alert alert-danger py-2" style="font-size:.8rem;">Could not load colleges: <?= htmlspecialchars($loadError) ?></div>
                <?php elseif (empty($colleges)): ?>
                <p class="text-muted text-center py-4"><?= $search !== '' ? 'No colleges match that search.' : 'No colleges yet — click "Add College" to create the first one.' ?></p>
                <?php else: ?>
                <div class="table-responsive" style="max-height:600px;overflow-y:auto;">
                <table class="table table-hover table-sm mb-0">
                    <thead class="table-light"><tr><th>Name</th><th>City</th><th>Type</th><th></th></tr></thead>
                    <tbody>
                    <?php foreach ($colleges as $c): ?>
                    <tr class="<?= (int)$c['id'] === $editId ? 'table-primary' : '' ?>">
                        <td><?= htmlspecialchars(mb_strimwidth($c['name'], 0, 34, '…')) ?></td>
                        <td><?= htmlspecialchars($c['city'] ?? '—') ?></td>
                        <td><span class="badge bg-secondary" style="font-size:.68rem;"><?= htmlspecialchars($c['college_type'] ?? $c['type'] ?? '') ?></span></td>
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

                        <?php foreach ($fieldDefs as $col => $def): if (!isset($has[$col])) continue; ?>
                        <div class="col-md-<?= $def['col'] ?>">
                            <?php if ($def['type'] === 'checkbox'):
                                $checkboxChecked = $editCollege ? !empty($editCollege[$col]) : !empty($def['default_checked_if_new']);
                            ?>
                            <div class="form-check mt-4">
                                <input type="checkbox" class="form-check-input" name="<?= $col ?>" id="f_<?= $col ?>" value="1" <?= $checkboxChecked ? 'checked' : '' ?>>
                                <label class="form-check-label fw-bold" for="f_<?= $col ?>"><?= htmlspecialchars($def['label']) ?></label>
                            </div>
                            <?php else: ?>
                            <label class="form-label fw-bold"><?= htmlspecialchars($def['label']) ?></label>
                            <?php if ($def['type'] === 'select'): ?>
                            <select name="<?= $col ?>" class="form-select">
                                <option value="">—</option>
                                <?php foreach ($def['options'] as $opt): ?>
                                <option value="<?= $opt ?>" <?= ($editCollege[$col] ?? '') === $opt ? 'selected' : '' ?>><?= ucfirst($opt) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <?php else: ?>
                            <input type="<?= $def['type'] ?>" name="<?= $col ?>" class="form-control" <?= isset($def['step']) ? 'step="' . $def['step'] . '"' : '' ?> value="<?= htmlspecialchars($editCollege[$col] ?? '') ?>">
                            <?php endif; ?>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>

                        <?php if (isset($has['description'])): ?>
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
                        <?php endif; ?>

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
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="addManualCourseRow()"><i class="bi bi-plus-lg"></i> Add Course Manually</button>
                        <button type="button" class="btn btn-outline-primary btn-sm" id="aiCoursesBtn" onclick="aiSuggestCourses(<?= $editCollege['id'] ?>)">✨ AI Suggest Courses &amp; Fees</button>
                    </div>
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
                <p class="text-muted text-center py-3 mb-0">No courses yet. Add one manually, or use "AI Suggest Courses &amp; Fees" above to draft a starting list.</p>
                <?php endif; ?>
                <div id="aiCoursesStatus" class="mt-2" style="font-size:.82rem;"></div>
                <div id="aiCoursesPanel" style="display:none;margin-top:14px;">
                    <div class="ai-note" id="courseFormNote" style="display:none;">⚠️ AI-generated estimates based on general knowledge of this institution — not verified against a live source. Review and edit before saving.</div>
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

function courseRowHtml(c, i) {
    c = c || {};
    return `
                <tr>
                    <td><input type="checkbox" class="ai-course-check" checked data-idx="${i}"></td>
                    <td><input type="text" class="form-control form-control-sm ai-c-name" value="${(c.name || '').replace(/"/g, '&quot;')}" placeholder="Course name"></td>
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
                    <td><input type="number" class="form-control form-control-sm ai-c-fees" value="${c.annual_fees || ''}" style="width:110px;" placeholder="0"></td>
                    <td><input type="text" class="form-control form-control-sm ai-c-eligibility" value="${(c.eligibility || '').replace(/"/g, '&quot;')}"></td>
                </tr>
    `;
}

function addManualCourseRow() {
    const tbody = document.getElementById('aiCoursesTbody');
    const nextIdx = tbody.querySelectorAll('tr').length;
    tbody.insertAdjacentHTML('beforeend', courseRowHtml(null, nextIdx));
    document.getElementById('courseFormNote').style.display = 'none';
    document.getElementById('aiCoursesPanel').style.display = '';
    document.getElementById('aiCoursesStatus').textContent = '';
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
            status.innerHTML = '<span class="text-danger">Error: ' + (d.error || 'No suggestions returned — try again, or use "Add Course Manually" instead.') + '</span>';
        } else {
            const tbody = document.getElementById('aiCoursesTbody');
            tbody.innerHTML = d.suggestions.map((c, i) => courseRowHtml(c, i)).join('');
            document.getElementById('courseFormNote').style.display = '';
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
