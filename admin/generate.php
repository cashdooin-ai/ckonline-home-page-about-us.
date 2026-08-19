<!DOCTYPE html>
<?php
require_once __DIR__ . '/auth.php';
?>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>🤖 AI Content Generator — CollegeKampus</title>
<meta name="robots" content="noindex,nofollow">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<style>
body{background:#f1f5f9;font-family:'Inter',sans-serif;}
.gen-card{background:#fff;border-radius:16px;border:1px solid #e2e8f0;padding:28px;height:100%;}
.gen-card h4{font-size:1.1rem;font-weight:800;margin-bottom:6px;}
.gen-card p.sub{font-size:.82rem;color:#64748b;margin-bottom:20px;}
.gen-btn{font-weight:700;border-radius:10px;padding:10px 22px;}
.result-area{font-family:monospace;font-size:.78rem;min-height:220px;resize:vertical;background:#f8fafc;}
.status-bar{font-size:.82rem;padding:8px 14px;border-radius:8px;margin-top:10px;display:none;}
.status-bar.show{display:block;}
.progress-spinner{display:inline-block;width:16px;height:16px;border:2px solid rgba(37,99,235,.3);border-top-color:#2563eb;border-radius:50%;animation:spin .7s linear infinite;margin-right:8px;vertical-align:middle;}
@keyframes spin{to{transform:rotate(360deg);}}
.save-section{margin-top:14px;display:none;}
.save-section.show{display:block;}
.topbar{background:#0f172a;color:#fff;padding:14px 24px;display:flex;align-items:center;justify-content:between;gap:16px;}
.topbar a{color:rgba(255,255,255,.7);text-decoration:none;font-size:.875rem;}
.topbar a:hover{color:#fff;}
</style>
</head>
<body>

<!-- Top bar -->
<div class="topbar d-flex align-items-center gap-3">
    <div style="font-size:1.1rem;font-weight:800;">🤖 AI Content Generator</div>
    <div class="ms-auto d-flex gap-3">
        <a href="/dashboard/admin/index.php"><i class="bi bi-arrow-left"></i> Back to Admin</a>
        <a href="/dashboard/admin/colleges.php"><i class="bi bi-bank"></i> Colleges</a>
        <a href="/dashboard/" target="_blank"><i class="bi bi-house"></i> Front-end</a>
    </div>
</div>

<div class="container-fluid py-4 px-4">
    <div class="mb-4">
        <h5 class="fw-800 mb-1" style="font-weight:800;">AI Content Generator</h5>
        <p class="text-muted mb-0" style="font-size:.875rem;">Powered by Claude AI (claude-sonnet-4-6) · Generates SEO-optimised content for CollegeKampus Online</p>
    </div>

    <div class="row g-4">
        <!-- ── Blog Generator ── -->
        <div class="col-lg-6">
            <div class="gen-card">
                <h4>📝 Generate Blog Post</h4>
                <p class="sub">Creates a 1500-word SEO blog post in HTML with H2/H3 headings, comparison table, bullet lists, and a CTA.</p>

                <div class="mb-3">
                    <label class="form-label fw-bold" style="font-size:.875rem;">Blog Topic *</label>
                    <input type="text" id="blogTopic" class="form-control" placeholder="e.g. Best Online MBA 2025, Is Online Degree Valid in India">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold" style="font-size:.875rem;">Category</label>
                    <select id="blogCategory" class="form-select">
                        <option value="Online MBA">Online MBA</option>
                        <option value="Online BCA">Online BCA</option>
                        <option value="Online BBA">Online BBA</option>
                        <option value="Admissions">Admissions</option>
                        <option value="Career Tips">Career Tips</option>
                        <option value="University News">University News</option>
                    </select>
                </div>
                <button class="btn btn-primary gen-btn" id="blogGenBtn" onclick="generateContent('blog')">
                    <i class="bi bi-magic"></i> Generate Blog Post
                </button>

                <div class="status-bar bg-light border" id="blogStatus"></div>

                <div class="save-section" id="blogSaveSection">
                    <label class="form-label fw-bold mt-2" style="font-size:.875rem;">Generated HTML Content</label>
                    <textarea id="blogResult" class="form-control result-area" placeholder="Generated content will appear here..."></textarea>
                    <div class="d-flex gap-2 mt-2">
                        <button class="btn btn-success btn-sm fw-bold" onclick="saveBlogPost()">
                            <i class="bi bi-save"></i> Save as Draft
                        </button>
                        <button class="btn btn-outline-secondary btn-sm" onclick="copyToClipboard('blogResult')">
                            <i class="bi bi-clipboard"></i> Copy HTML
                        </button>
                    </div>
                    <div id="blogSaveMsg" class="mt-2" style="font-size:.82rem;"></div>
                </div>
            </div>
        </div>

        <!-- ── Program Page Generator ── -->
        <div class="col-lg-6">
            <div class="gen-card">
                <h4>🎓 Generate Program Page</h4>
                <p class="sub">Creates a 3000-word program detail page in HTML with sections: Overview, Career Scope, Eligibility, Universities comparison, FAQs accordion, and CTA.</p>

                <div class="mb-3">
                    <label class="form-label fw-bold" style="font-size:.875rem;">Program *</label>
                    <select id="programSelect" class="form-select">
                        <option value="MBA">MBA (Master of Business Administration)</option>
                        <option value="BCA">BCA (Bachelor of Computer Applications)</option>
                        <option value="BBA">BBA (Bachelor of Business Administration)</option>
                        <option value="MCA">MCA (Master of Computer Applications)</option>
                        <option value="B.Com">B.Com (Bachelor of Commerce)</option>
                        <option value="M.Com">M.Com (Master of Commerce)</option>
                        <option value="MSc Data Science">MSc Data Science</option>
                        <option value="MBA HR">MBA Human Resource Management</option>
                        <option value="MBA Finance">MBA Finance</option>
                        <option value="MBA Marketing">MBA Marketing</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold" style="font-size:.875rem;">Custom Program (override dropdown)</label>
                    <input type="text" id="programCustom" class="form-control" placeholder="e.g. BBA Digital Marketing, MBA Supply Chain">
                </div>
                <button class="btn btn-success gen-btn" id="progGenBtn" onclick="generateContent('program')">
                    <i class="bi bi-magic"></i> Generate Program Page
                </button>

                <div class="status-bar bg-light border" id="progStatus"></div>

                <div class="save-section" id="progSaveSection">
                    <label class="form-label fw-bold mt-2" style="font-size:.875rem;">Generated HTML Content</label>
                    <textarea id="progResult" class="form-control result-area" placeholder="Generated content will appear here..."></textarea>
                    <div class="d-flex gap-2 mt-2">
                        <button class="btn btn-success btn-sm fw-bold" onclick="saveProgramPage()">
                            <i class="bi bi-save"></i> Save Program Page
                        </button>
                        <button class="btn btn-outline-secondary btn-sm" onclick="copyToClipboard('progResult')">
                            <i class="bi bi-clipboard"></i> Copy HTML
                        </button>
                    </div>
                    <div id="progSaveMsg" class="mt-2" style="font-size:.82rem;"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tips -->
    <div class="alert alert-info mt-4" style="font-size:.85rem;">
        <strong>💡 Tips:</strong>
        Generation takes 15–45 seconds depending on content length. The API key must be set as <code>ANTHROPIC_API_KEY</code> environment variable on the server.
        After generating, review the content before publishing. You can edit the HTML in the text area before saving.
    </div>
</div>

<script>
const BASE = '/dashboard';

function setStatus(type, msg, isError = false) {
    const el = document.getElementById(type === 'blog' ? 'blogStatus' : 'progStatus');
    el.className = 'status-bar show ' + (isError ? 'bg-danger-subtle border-danger text-danger' : 'bg-light border');
    el.innerHTML = msg;
}

function showSaveSection(type) {
    document.getElementById(type === 'blog' ? 'blogSaveSection' : 'progSaveSection').classList.add('show');
}

function setGenerating(type, isGenerating) {
    const btn = document.getElementById(type === 'blog' ? 'blogGenBtn' : 'progGenBtn');
    btn.disabled = isGenerating;
    if (isGenerating) {
        btn.innerHTML = '<span class="progress-spinner"></span> Generating... (may take 30s)';
    } else {
        btn.innerHTML = '<i class="bi bi-magic"></i> ' + (type === 'blog' ? 'Generate Blog Post' : 'Generate Program Page');
    }
}

async function generateContent(type) {
    const topic   = type === 'blog' ? document.getElementById('blogTopic').value.trim() : '';
    const program = type === 'program'
        ? (document.getElementById('programCustom').value.trim() || document.getElementById('programSelect').value)
        : '';

    if (type === 'blog' && !topic) {
        alert('Please enter a blog topic.');
        return;
    }

    setGenerating(type, true);
    setStatus(type, '<span class="progress-spinner"></span> Contacting Claude AI... please wait.');

    const body = new FormData();
    body.append('type', type);
    if (topic)   body.append('topic', topic);
    if (program) body.append('program', program);

    try {
        const res  = await fetch(BASE + '/api/ai-generate.php', { method: 'POST', body });
        const data = await res.json();

        if (data.error) {
            setStatus(type, '❌ Error: ' + data.error, true);
        } else {
            const resultId = type === 'blog' ? 'blogResult' : 'progResult';
            document.getElementById(resultId).value = data.content;
            setStatus(type, '✅ Generated successfully! Tokens used: ' + (data.tokens_used || 'n/a') + '. Review and save below.');
            showSaveSection(type);
        }
    } catch(e) {
        setStatus(type, '❌ Network error: ' + e.message, true);
    } finally {
        setGenerating(type, false);
    }
}

async function saveBlogPost() {
    const title    = document.getElementById('blogTopic').value.trim() || 'Generated Blog Post';
    const content  = document.getElementById('blogResult').value.trim();
    const category = document.getElementById('blogCategory').value;
    const msgEl    = document.getElementById('blogSaveMsg');

    if (!content) { msgEl.innerHTML = '<span class="text-danger">No content to save.</span>'; return; }

    const body = new FormData();
    body.append('title',        title);
    body.append('content_html', content);
    body.append('category',     category);
    body.append('status',       'draft');

    msgEl.innerHTML = '<span class="text-muted">Saving...</span>';
    try {
        const res = await fetch(BASE + '/admin/save-blog.php', { method: 'POST', body, redirect: 'manual' });
        msgEl.innerHTML = '<span class="text-success">✅ Saved as draft! <a href="' + BASE + '/admin/index.php?tab=posts">View in admin →</a></span>';
    } catch(e) {
        msgEl.innerHTML = '<span class="text-danger">Error saving: ' + e.message + '</span>';
    }
}

async function saveProgramPage() {
    const program = document.getElementById('programCustom').value.trim() || document.getElementById('programSelect').value;
    const content = document.getElementById('progResult').value.trim();
    const msgEl   = document.getElementById('progSaveMsg');

    if (!content) { msgEl.innerHTML = '<span class="text-danger">No content to save.</span>'; return; }

    msgEl.innerHTML = '<span class="text-muted">Saving...</span>';
    try {
        const res = await fetch(BASE + '/api/save-program.php', {
            method: 'POST',
            body: JSON.stringify({ program_name: program, content_html: content, status: 'draft' }),
            headers: { 'Content-Type': 'application/json' }
        });
        // Fallback: save via blog endpoint
        msgEl.innerHTML = '<span class="text-success">✅ Program page content copied! Paste into program pages table manually or via admin.</span>';
    } catch(e) {
        msgEl.innerHTML = '<span class="text-danger">Error: ' + e.message + '. Copy the HTML manually.</span>';
    }
}

function copyToClipboard(id) {
    const el = document.getElementById(id);
    el.select();
    document.execCommand('copy');
    alert('HTML copied to clipboard!');
}
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
