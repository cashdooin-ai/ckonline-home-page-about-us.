<?php
require_once __DIR__ . '/auth.php';
require_once dirname(__DIR__) . '/config/db.php';

$pdo   = getDB();
$flash = '';

// ── Helper: upsert a setting ──────────────────────────────────────────────────
function saveSetting(PDO $pdo, string $key, string $value): void {
    $stmt = $pdo->prepare(
        "INSERT INTO site_settings (`key`, `value`) VALUES (?, ?)
         ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)"
    );
    $stmt->execute([$key, $value]);
}

// ── Helper: get setting from DB with fallback ─────────────────────────────────
function getSetting(PDO $pdo, string $key, string $default = ''): string {
    try {
        $stmt = $pdo->prepare("SELECT `value` FROM site_settings WHERE `key` = ?");
        $stmt->execute([$key]);
        $v = $stmt->fetchColumn();
        return $v !== false ? $v : $default;
    } catch (Throwable $e) {
        return $default;
    }
}

// ── Ensure table exists ───────────────────────────────────────────────────────
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS site_settings (
        `key` VARCHAR(100) NOT NULL PRIMARY KEY,
        `value` LONGTEXT,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
} catch (Throwable $e) {}

// ── Handle POST ───────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tab = $_POST['save_tab'] ?? 'homepage';
    try {
        if ($tab === 'homepage') {
            $fields = ['hero_badge','hero_headline','hero_subheadline','stat_colleges','stat_colleges_label','stat_students','stat_ugc','cta_primary','cta_secondary','stats_bar_colleges'];
            foreach ($fields as $f) {
                if (isset($_POST[$f])) saveSetting($pdo, $f, trim($_POST[$f]));
            }
        } elseif ($tab === 'navigation') {
            $nav_json = trim($_POST['nav_links'] ?? '');
            json_decode($nav_json); // validate JSON
            if (json_last_error() === JSON_ERROR_NONE) {
                saveSetting($pdo, 'nav_links', $nav_json);
            } else {
                throw new Exception("Invalid JSON in navigation links.");
            }
        } elseif ($tab === 'footer') {
            $fields = ['footer_tagline','contact_phone','contact_email','contact_hours',
                       'social_facebook','social_instagram','social_twitter','social_linkedin','social_youtube'];
            foreach ($fields as $f) {
                if (isset($_POST[$f])) saveSetting($pdo, $f, trim($_POST[$f]));
            }
        } elseif ($tab === 'carousel') {
            $slides = [];
            for ($i = 1; $i <= 5; $i++) {
                $title = trim($_POST["slide_{$i}_title"] ?? '');
                if ($title === '') continue;
                $slides[] = [
                    'title'     => $title,
                    'subtitle'  => trim($_POST["slide_{$i}_subtitle"] ?? ''),
                    'img'       => trim($_POST["slide_{$i}_img"] ?? ''),
                    'btn_text'  => trim($_POST["slide_{$i}_btn_text"] ?? ''),
                    'btn_link'  => trim($_POST["slide_{$i}_btn_link"] ?? ''),
                ];
            }
            saveSetting($pdo, 'carousel_slides', json_encode($slides, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        }
        $flash = ['type'=>'success','msg'=>'Settings saved successfully.'];
    } catch (Throwable $e) {
        $flash = ['type'=>'danger','msg'=>'Error saving settings: ' . $e->getMessage()];
    }
}

$activeTab = $_GET['tab'] ?? 'homepage';

// ── Load carousel slides ──────────────────────────────────────────────────────
$carouselRaw = getSetting($pdo, 'carousel_slides', '[]');
$carouselSlides = json_decode($carouselRaw, true);
if (!is_array($carouselSlides)) $carouselSlides = [];
// pad to 5
while (count($carouselSlides) < 5) $carouselSlides[] = ['title'=>'','subtitle'=>'','img'=>'','btn_text'=>'','btn_link'=>''];

// ── Load nav links ────────────────────────────────────────────────────────────
$navLinksDefault = '[{"label":"Explore Programs","url":"/colleges.php","children":[]},{"label":"Top Universities","url":"/colleges.php","children":[]},{"label":"Tools","url":"#","children":[]},{"label":"About Us","url":"/#about-us","children":[]}]';
$navLinks = getSetting($pdo, 'nav_links', $navLinksDefault);

function e(string $s): string { return htmlspecialchars($s, ENT_QUOTES); }
function gs(PDO $pdo, string $key, string $default = ''): string { return e(getSetting($pdo, $key, $default)); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Site Settings | CK Admin</title>
<meta name="robots" content="noindex,nofollow">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<style>
body{background:#f1f5f9;font-family:'Inter',sans-serif;}
.admin-sidebar{width:220px;min-height:100vh;background:#0f172a;position:fixed;top:0;left:0;z-index:100;padding-top:20px;}
.admin-sidebar .brand{color:#fff;font-size:1.1rem;font-weight:800;padding:0 20px 20px;border-bottom:1px solid rgba(255,255,255,.1);}
.admin-sidebar .nav-link{color:rgba(255,255,255,.7);font-size:.875rem;font-weight:500;padding:10px 20px;display:flex;align-items:center;gap:10px;transition:background .2s,color .2s;}
.admin-sidebar .nav-link:hover,.admin-sidebar .nav-link.active{background:rgba(255,255,255,.1);color:#fff;}
.admin-main{margin-left:220px;min-height:100vh;padding:28px;}
.settings-card{background:#fff;border-radius:12px;border:1px solid #e2e8f0;padding:28px;margin-top:20px;}
.tab-nav-link{color:#64748b;border:none;border-bottom:3px solid transparent;padding:10px 20px;font-weight:600;font-size:.88rem;background:none;cursor:pointer;transition:all .2s;}
.tab-nav-link.active{color:#1a4fba;border-bottom-color:#1a4fba;}
.tab-nav-link:hover{color:#1a4fba;}
.slide-block{background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:20px;margin-bottom:16px;}
.slide-num{display:inline-flex;width:28px;height:28px;border-radius:50%;background:#1a4fba;color:#fff;align-items:center;justify-content:center;font-size:.78rem;font-weight:800;margin-right:8px;flex-shrink:0;}
textarea.json-area{font-family:monospace;font-size:.78rem;min-height:200px;}
</style>
</head>
<body>

<!-- Sidebar -->
<nav class="admin-sidebar">
  <div class="brand">🎓 CK Admin</div>
  <ul class="nav flex-column mt-2">
    <li class="nav-item"><a class="nav-link" href="index.php?tab=posts"><i class="bi bi-journal-text"></i> Blog Posts</a></li>
    <li class="nav-item"><a class="nav-link" href="index.php?tab=programs"><i class="bi bi-mortarboard"></i> Program Pages</a></li>
    <li class="nav-item"><a class="nav-link" href="index.php?tab=leads"><i class="bi bi-people"></i> Leads</a></li>
    <li class="nav-item"><a class="nav-link" href="colleges.php"><i class="bi bi-bank"></i> Colleges</a></li>
    <li class="nav-item"><a class="nav-link" href="generate.php"><i class="bi bi-robot"></i> AI Generate</a></li>
    <li class="nav-item"><a class="nav-link active" href="site-settings.php"><i class="bi bi-gear"></i> Site Settings</a></li>
    <li class="nav-item"><a class="nav-link" href="change-password.php"><i class="bi bi-key"></i> Change Password</a></li>
    <li class="nav-item mt-3"><a class="nav-link" href="/dashboard/"><i class="bi bi-house"></i> Front-end</a></li>
  </ul>
</nav>

<!-- Main -->
<div class="admin-main">
  <div class="d-flex justify-content-between align-items-center mb-2">
    <div>
      <h4 class="mb-0" style="font-weight:800;">Site Settings</h4>
      <small class="text-muted">Manage homepage content, navigation, footer and carousel</small>
    </div>
  </div>

  <?php if ($flash): ?>
  <div class="alert alert-<?= $flash['type'] ?> alert-dismissible py-2 mt-3" role="alert">
    <?= htmlspecialchars($flash['msg']) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
  <?php endif; ?>

  <!-- Tab Nav -->
  <div class="bg-white border rounded-3 mt-3 px-2 d-flex gap-0 overflow-auto">
    <button class="tab-nav-link <?= $activeTab==='homepage'?'active':'' ?>" onclick="switchTab('homepage')">
      <i class="bi bi-house me-1"></i>Homepage
    </button>
    <button class="tab-nav-link <?= $activeTab==='navigation'?'active':'' ?>" onclick="switchTab('navigation')">
      <i class="bi bi-list me-1"></i>Navigation
    </button>
    <button class="tab-nav-link <?= $activeTab==='footer'?'active':'' ?>" onclick="switchTab('footer')">
      <i class="bi bi-layout-text-window-reverse me-1"></i>Footer
    </button>
    <button class="tab-nav-link <?= $activeTab==='carousel'?'active':'' ?>" onclick="switchTab('carousel')">
      <i class="bi bi-images me-1"></i>Carousel
    </button>
  </div>

  <!-- ── Homepage Tab ──────────────────────────────────────────────────────── -->
  <div id="tab-homepage" class="tab-panel" style="display:<?= $activeTab==='homepage'?'block':'none' ?>">
    <form method="POST">
      <input type="hidden" name="save_tab" value="homepage">
      <div class="settings-card">
        <h5 class="fw-bold mb-4">Hero Section</h5>
        <div class="row g-3">
          <div class="col-12">
            <label class="form-label fw-bold">Hero Badge Text</label>
            <input type="text" name="hero_badge" class="form-control"
              value="<?= gs($pdo,'hero_badge',"India's #1 Online Degree Discovery Platform") ?>">
          </div>
          <div class="col-12">
            <label class="form-label fw-bold">Hero Headline <small class="text-muted fw-normal">(HTML allowed: use &lt;span&gt; for colored text, &lt;br&gt; for line break)</small></label>
            <input type="text" name="hero_headline" class="form-control"
              value="<?= gs($pdo,'hero_headline','Find the Best <span>Online Degree</span><br>Universities in India') ?>">
          </div>
          <div class="col-12">
            <label class="form-label fw-bold">Hero Subheadline</label>
            <textarea name="hero_subheadline" class="form-control" rows="2"><?= gs($pdo,'hero_subheadline','Compare 500+ UGC-approved online universities. Check fees, placements, NAAC ratings — all in one place. Apply in minutes.') ?></textarea>
          </div>
        </div>
      </div>

      <div class="settings-card">
        <h5 class="fw-bold mb-4">Stats (Hero Cards &amp; Stats Bar)</h5>
        <div class="row g-3">
          <div class="col-md-4">
            <label class="form-label fw-bold">Colleges Count</label>
            <input type="text" name="stat_colleges" class="form-control" value="<?= gs($pdo,'stat_colleges','500+') ?>">
          </div>
          <div class="col-md-4">
            <label class="form-label fw-bold">Colleges Label</label>
            <input type="text" name="stat_colleges_label" class="form-control" value="<?= gs($pdo,'stat_colleges_label','Online Universities') ?>">
          </div>
          <div class="col-md-4">
            <label class="form-label fw-bold">Stats Bar Count</label>
            <input type="text" name="stats_bar_colleges" class="form-control" value="<?= gs($pdo,'stats_bar_colleges','500+') ?>">
            <small class="text-muted">Shown in the stats bar row</small>
          </div>
          <div class="col-md-4">
            <label class="form-label fw-bold">Students Guided</label>
            <input type="text" name="stat_students" class="form-control" value="<?= gs($pdo,'stat_students','1.25 Lakh+') ?>">
          </div>
          <div class="col-md-4">
            <label class="form-label fw-bold">UGC Percent</label>
            <input type="text" name="stat_ugc" class="form-control" value="<?= gs($pdo,'stat_ugc','100%') ?>">
          </div>
        </div>
      </div>

      <div class="settings-card">
        <h5 class="fw-bold mb-4">CTA Buttons</h5>
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label fw-bold">Primary CTA Text</label>
            <input type="text" name="cta_primary" class="form-control" value="<?= gs($pdo,'cta_primary','Browse Universities') ?>">
          </div>
          <div class="col-md-6">
            <label class="form-label fw-bold">Secondary CTA Text</label>
            <input type="text" name="cta_secondary" class="form-control" value="<?= gs($pdo,'cta_secondary','Talk to a Counsellor') ?>">
          </div>
        </div>
      </div>

      <div class="mt-3">
        <button type="submit" class="btn btn-primary fw-bold px-4">
          <i class="bi bi-save me-1"></i>Save Homepage Settings
        </button>
      </div>
    </form>
  </div>

  <!-- ── Navigation Tab ────────────────────────────────────────────────────── -->
  <div id="tab-navigation" class="tab-panel" style="display:<?= $activeTab==='navigation'?'block':'none' ?>">
    <form method="POST">
      <input type="hidden" name="save_tab" value="navigation">
      <div class="settings-card">
        <h5 class="fw-bold mb-2">Navigation Links <small class="text-muted fw-normal">(JSON)</small></h5>
        <p class="text-muted small mb-3">Edit navigation structure as JSON. Each item: <code>{"label":"...", "url":"...", "children":[]}</code></p>
        <textarea name="nav_links" class="form-control json-area"><?= htmlspecialchars($navLinks, ENT_QUOTES) ?></textarea>
      </div>
      <div class="mt-3">
        <button type="submit" class="btn btn-primary fw-bold px-4">
          <i class="bi bi-save me-1"></i>Save Navigation
        </button>
      </div>
    </form>
  </div>

  <!-- ── Footer Tab ─────────────────────────────────────────────────────────── -->
  <div id="tab-footer" class="tab-panel" style="display:<?= $activeTab==='footer'?'block':'none' ?>">
    <form method="POST">
      <input type="hidden" name="save_tab" value="footer">
      <div class="settings-card">
        <h5 class="fw-bold mb-4">Footer Info</h5>
        <div class="row g-3">
          <div class="col-12">
            <label class="form-label fw-bold">Footer Tagline</label>
            <input type="text" name="footer_tagline" class="form-control"
              value="<?= gs($pdo,'footer_tagline',"India's trusted online college discovery platform. Compare 500+ UGC-approved universities and apply in minutes.") ?>">
          </div>
          <div class="col-md-4">
            <label class="form-label fw-bold">Contact Phone</label>
            <input type="text" name="contact_phone" class="form-control" value="<?= gs($pdo,'contact_phone','1800-123-4567') ?>">
          </div>
          <div class="col-md-4">
            <label class="form-label fw-bold">Contact Email</label>
            <input type="email" name="contact_email" class="form-control" value="<?= gs($pdo,'contact_email','info@collegekampus.in') ?>">
          </div>
          <div class="col-md-4">
            <label class="form-label fw-bold">Office Hours</label>
            <input type="text" name="contact_hours" class="form-control" value="<?= gs($pdo,'contact_hours','Mon–Sat 9am–7pm') ?>">
          </div>
        </div>
      </div>

      <div class="settings-card">
        <h5 class="fw-bold mb-4">Social Media Links</h5>
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label fw-bold"><i class="bi bi-facebook me-1 text-primary"></i>Facebook URL</label>
            <input type="url" name="social_facebook" class="form-control" placeholder="https://www.facebook.com/collegekampus"
              value="<?= gs($pdo,'social_facebook','https://www.facebook.com/collegekampus') ?>">
          </div>
          <div class="col-md-6">
            <label class="form-label fw-bold"><i class="bi bi-instagram me-1" style="color:#e1306c"></i>Instagram URL</label>
            <input type="url" name="social_instagram" class="form-control" placeholder="https://www.instagram.com/collegekampus"
              value="<?= gs($pdo,'social_instagram','https://www.instagram.com/collegekampus') ?>">
          </div>
          <div class="col-md-6">
            <label class="form-label fw-bold"><i class="bi bi-twitter-x me-1"></i>Twitter/X URL</label>
            <input type="url" name="social_twitter" class="form-control" placeholder="https://twitter.com/collegekampus"
              value="<?= gs($pdo,'social_twitter','https://twitter.com/collegekampus') ?>">
          </div>
          <div class="col-md-6">
            <label class="form-label fw-bold"><i class="bi bi-linkedin me-1 text-primary"></i>LinkedIn URL</label>
            <input type="url" name="social_linkedin" class="form-control" placeholder="https://www.linkedin.com/company/collegekampus"
              value="<?= gs($pdo,'social_linkedin','https://www.linkedin.com/company/collegekampus') ?>">
          </div>
          <div class="col-md-6">
            <label class="form-label fw-bold"><i class="bi bi-youtube me-1 text-danger"></i>YouTube URL</label>
            <input type="url" name="social_youtube" class="form-control" placeholder="https://www.youtube.com/collegekampus"
              value="<?= gs($pdo,'social_youtube','https://www.youtube.com/collegekampus') ?>">
          </div>
        </div>
      </div>

      <div class="mt-3">
        <button type="submit" class="btn btn-primary fw-bold px-4">
          <i class="bi bi-save me-1"></i>Save Footer Settings
        </button>
      </div>
    </form>
  </div>

  <!-- ── Carousel Tab ───────────────────────────────────────────────────────── -->
  <div id="tab-carousel" class="tab-panel" style="display:<?= $activeTab==='carousel'?'block':'none' ?>">
    <form method="POST">
      <input type="hidden" name="save_tab" value="carousel">
      <div class="settings-card">
        <h5 class="fw-bold mb-1">Carousel Slides</h5>
        <p class="text-muted small mb-4">Up to 5 slides. Leave the Title blank to skip a slide.</p>

        <?php for ($i = 1; $i <= 5; $i++):
          $s = $carouselSlides[$i-1];
        ?>
        <div class="slide-block">
          <div class="d-flex align-items-center mb-3">
            <span class="slide-num"><?= $i ?></span>
            <strong>Slide <?= $i ?></strong>
            <?php if ($i > 3): ?><span class="badge bg-secondary ms-2" style="font-size:.68rem;">Optional</span><?php endif; ?>
          </div>
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label fw-bold">Title</label>
              <input type="text" name="slide_<?= $i ?>_title" class="form-control"
                value="<?= e($s['title'] ?? '') ?>" placeholder="e.g. Find Your Perfect Online Degree">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-bold">Subtitle</label>
              <input type="text" name="slide_<?= $i ?>_subtitle" class="form-control"
                value="<?= e($s['subtitle'] ?? '') ?>" placeholder="Short description">
            </div>
            <div class="col-12">
              <label class="form-label fw-bold">Image URL</label>
              <input type="url" name="slide_<?= $i ?>_img" class="form-control"
                value="<?= e($s['img'] ?? '') ?>" placeholder="https://placehold.co/800x400/1a4fba/ffffff?text=...">
              <?php if (!empty($s['img'])): ?>
              <div class="mt-2"><img src="<?= e($s['img']) ?>" alt="Preview" style="height:60px;border-radius:6px;border:1px solid #e2e8f0;"></div>
              <?php endif; ?>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-bold">Button Text</label>
              <input type="text" name="slide_<?= $i ?>_btn_text" class="form-control"
                value="<?= e($s['btn_text'] ?? '') ?>" placeholder="e.g. Explore Now">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-bold">Button Link</label>
              <input type="text" name="slide_<?= $i ?>_btn_link" class="form-control"
                value="<?= e($s['btn_link'] ?? '') ?>" placeholder="e.g. colleges.php">
            </div>
          </div>
        </div>
        <?php endfor; ?>

      </div>
      <div class="mt-3">
        <button type="submit" class="btn btn-primary fw-bold px-4">
          <i class="bi bi-save me-1"></i>Save Carousel
        </button>
      </div>
    </form>
  </div>

</div><!-- /admin-main -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
function switchTab(tab) {
  document.querySelectorAll('.tab-panel').forEach(p => p.style.display = 'none');
  document.querySelectorAll('.tab-nav-link').forEach(b => b.classList.remove('active'));
  document.getElementById('tab-' + tab).style.display = 'block';
  event.target.classList.add('active');
  // update URL without reload
  const url = new URL(window.location);
  url.searchParams.set('tab', tab);
  window.history.replaceState({}, '', url);
}
</script>
</body>
</html>
