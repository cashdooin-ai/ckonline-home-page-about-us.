<?php
// TODO: Add authentication check before production
require_once __DIR__ . '/../config/db.php';

$msg = '';

// Save handler
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $db = getDB();
        $db->exec("CREATE TABLE IF NOT EXISTS site_settings (
            `key` VARCHAR(100) NOT NULL PRIMARY KEY,
            `value` LONGTEXT,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        $tab = $_POST['_tab'] ?? 'cashback';

        if ($tab === 'cashback') {
            $keys = ['cashback_enabled','cashback_amount','cashback_label','cashback_badge_text',
                     'cashback_colleges', 'cashback_cta', 'cashback_terms'];
            foreach ($keys as $k) {
                $val = $_POST[$k] ?? '';
                $db->prepare("INSERT INTO site_settings(`key`,`value`) VALUES(?,?) ON DUPLICATE KEY UPDATE `value`=?")->execute([$k,$val,$val]);
            }
        } elseif ($tab === 'banners') {
            $val = json_encode($_POST['banners'] ?? []);
            $db->prepare("INSERT INTO site_settings(`key`,`value`) VALUES('promo_banners',?) ON DUPLICATE KEY UPDATE `value`=?")->execute([$val,$val]);
        } elseif ($tab === 'popups') {
            $keys = ['popup_enabled','popup_title','popup_subtitle','popup_cta_text','popup_cta_link','popup_delay'];
            foreach ($keys as $k) {
                $val = $_POST[$k] ?? '';
                $db->prepare("INSERT INTO site_settings(`key`,`value`) VALUES(?,?) ON DUPLICATE KEY UPDATE `value`=?")->execute([$k,$val,$val]);
            }
        } elseif ($tab === 'cta_buttons') {
            $val = json_encode($_POST['cta_buttons'] ?? []);
            $db->prepare("INSERT INTO site_settings(`key`,`value`) VALUES('cta_buttons',?) ON DUPLICATE KEY UPDATE `value`=?")->execute([$val,$val]);
        }

        $msg = '<div class="alert alert-success">Saved successfully.</div>';
    } catch (Throwable $e) {
        $msg = '<div class="alert alert-danger">Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
    }
}

// Load settings
$s = [];
try {
    $db = getDB();
    $s = $db->query("SELECT `key`,`value` FROM site_settings")->fetchAll(PDO::FETCH_KEY_PAIR);
} catch (Throwable $e) {}

function sv($s, $k, $d = '') { return htmlspecialchars($s[$k] ?? $d); }

$banners = json_decode($s['promo_banners'] ?? '[]', true) ?: [];
while (count($banners) < 3) $banners[] = ['text'=>'','link'=>'','bg'=>'#1a4fba','color'=>'#ffffff','icon'=>'🎓','active'=>'1'];

$ctaBtns = json_decode($s['cta_buttons'] ?? '[]', true) ?: [];
while (count($ctaBtns) < 3) $ctaBtns[] = ['label'=>'','link'=>'','style'=>'primary','page'=>''];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Marketing & Ads | CK Admin</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<style>
body{background:#f8fafc;font-family:Inter,sans-serif;}
.sidebar{background:#0f172a;min-height:100vh;width:230px;flex-shrink:0;}
.sidebar a{display:block;padding:10px 20px;color:rgba(255,255,255,.7);text-decoration:none;font-size:.85rem;border-left:3px solid transparent;}
.sidebar a:hover,.sidebar a.active{color:#fff;background:rgba(255,255,255,.07);border-left-color:#2563eb;}
.sidebar .brand{color:#fff;font-weight:800;font-size:1.1rem;padding:20px;}
.sidebar .nav-label{color:rgba(255,255,255,.35);font-size:.68rem;font-weight:700;text-transform:uppercase;padding:14px 20px 4px;letter-spacing:.08em;}
.main{flex:1;padding:28px;max-width:960px;}
.tab-btn{padding:8px 18px;border-radius:8px 8px 0 0;border:1px solid #e2e8f0;border-bottom:none;background:#f1f5f9;font-size:.84rem;font-weight:600;color:#374151;cursor:pointer;margin-right:4px;}
.tab-btn.active{background:#fff;color:#2563eb;border-bottom:1px solid #fff;margin-bottom:-1px;position:relative;z-index:1;}
.tab-pane{display:none;}.tab-pane.active{display:block;}
.form-label{font-size:.82rem;font-weight:600;color:#374151;}
.preview-banner{padding:10px 16px;border-radius:8px;font-size:.85rem;font-weight:600;display:flex;align-items:center;gap:8px;margin-top:8px;}
.cashback-badge{display:inline-flex;align-items:center;gap:6px;background:linear-gradient(90deg,#22c55e,#16a34a);color:#fff;padding:5px 14px;border-radius:20px;font-size:.8rem;font-weight:700;box-shadow:0 2px 8px rgba(22,163,74,.3);}
</style>
</head>
<body>
<div class="d-flex">

<!-- Sidebar -->
<div class="sidebar">
  <div class="brand"><i class="bi bi-mortarboard-fill me-2 text-primary"></i>CK Admin</div>
  <div class="nav-label">Content</div>
  <a href="index.php"><i class="bi bi-file-earmark-text me-2"></i>Blog Posts</a>
  <a href="generate.php"><i class="bi bi-stars me-2"></i>AI Generate</a>
  <div class="nav-label">Site</div>
  <a href="site-settings.php"><i class="bi bi-sliders me-2"></i>Site Settings</a>
  <a href="marketing.php" class="active"><i class="bi bi-megaphone me-2"></i>Marketing & Ads</a>
  <div class="nav-label">Links</div>
  <a href="../index.php" target="_blank"><i class="bi bi-house me-2"></i>Homepage</a>
  <a href="../colleges.php" target="_blank"><i class="bi bi-building me-2"></i>Colleges</a>
</div>

<!-- Main -->
<div class="main">
  <h4 class="fw-800 mb-1">Marketing &amp; Advertising</h4>
  <p class="text-muted small mb-4">Manage cashback offers, promo banners, popups and CTA buttons across the site.</p>

  <?= $msg ?>

  <!-- Tabs -->
  <div class="mb-0">
    <button class="tab-btn active" onclick="showTab('cashback')"><i class="bi bi-currency-rupee me-1"></i>Cashback Offers</button>
    <button class="tab-btn" onclick="showTab('banners')"><i class="bi bi-card-image me-1"></i>Promo Banners</button>
    <button class="tab-btn" onclick="showTab('popups')"><i class="bi bi-window me-1"></i>Popup / Modal</button>
    <button class="tab-btn" onclick="showTab('cta_buttons')"><i class="bi bi-cursor-fill me-1"></i>CTA Buttons</button>
  </div>
  <div style="border:1px solid #e2e8f0;border-radius:0 8px 8px 8px;background:#fff;padding:24px;">

    <!-- ── CASHBACK TAB ── -->
    <div class="tab-pane active" id="tab-cashback">
      <form method="post">
        <input type="hidden" name="_tab" value="cashback">
        <div class="row g-3">
          <div class="col-12">
            <div class="form-check form-switch">
              <input class="form-check-input" type="checkbox" name="cashback_enabled" value="1" id="cbEnabled" <?= ($s['cashback_enabled']??'') === '1' ? 'checked' : '' ?>>
              <label class="form-check-label fw-600" for="cbEnabled">Enable Cashback Badges on College Cards</label>
            </div>
            <p class="text-muted small mt-1">When enabled, a cashback badge shows on every college card (like CollegeVidya's cashback model).</p>
          </div>

          <div class="col-md-4">
            <label class="form-label">Cashback Amount (₹)</label>
            <input type="text" class="form-control form-control-sm" name="cashback_amount" value="<?= sv($s,'cashback_amount','5,000') ?>">
          </div>
          <div class="col-md-4">
            <label class="form-label">Badge Label</label>
            <input type="text" class="form-control form-control-sm" name="cashback_label" value="<?= sv($s,'cashback_label','Cashback on Admission') ?>">
          </div>
          <div class="col-md-4">
            <label class="form-label">Badge Text (short)</label>
            <input type="text" class="form-control form-control-sm" name="cashback_badge_text" value="<?= sv($s,'cashback_badge_text','Get ₹5,000 Cashback') ?>">
          </div>

          <div class="col-md-6">
            <label class="form-label">CTA Button Text</label>
            <input type="text" class="form-control form-control-sm" name="cashback_cta" value="<?= sv($s,'cashback_cta','Claim Cashback & Apply') ?>">
          </div>
          <div class="col-md-6">
            <label class="form-label">Apply to Colleges (comma-separated IDs, blank = all)</label>
            <input type="text" class="form-control form-control-sm" name="cashback_colleges" value="<?= sv($s,'cashback_colleges','') ?>" placeholder="e.g. 1,5,12 or leave blank for all">
          </div>

          <div class="col-12">
            <label class="form-label">Terms & Conditions (1–2 lines shown under badge)</label>
            <textarea class="form-control form-control-sm" name="cashback_terms" rows="2"><?= sv($s,'cashback_terms','*Cashback credited within 30 days of fee payment. T&C apply.') ?></textarea>
          </div>

          <div class="col-12">
            <label class="form-label">Preview</label>
            <div>
              <span class="cashback-badge"><i class="bi bi-cash-coin"></i> <?= sv($s,'cashback_badge_text','Get ₹5,000 Cashback') ?></span>
            </div>
          </div>
        </div>
        <button type="submit" class="btn btn-primary mt-3">Save Cashback Settings</button>
      </form>
    </div>

    <!-- ── BANNERS TAB ── -->
    <div class="tab-pane" id="tab-banners">
      <p class="text-muted small mb-3">Promo banners appear as a slim bar below the navbar or above sections. Up to 3 active banners (rotated or stacked).</p>
      <form method="post">
        <input type="hidden" name="_tab" value="banners">
        <?php foreach ($banners as $i => $b): ?>
        <div class="card mb-3">
          <div class="card-body">
            <h6 class="fw-700 mb-3">Banner <?= $i+1 ?></h6>
            <div class="row g-2">
              <div class="col-md-5">
                <label class="form-label">Banner Text</label>
                <input type="text" class="form-control form-control-sm" name="banners[<?=$i?>][text]" value="<?= htmlspecialchars($b['text']??'') ?>">
              </div>
              <div class="col-md-3">
                <label class="form-label">Link URL</label>
                <input type="text" class="form-control form-control-sm" name="banners[<?=$i?>][link]" value="<?= htmlspecialchars($b['link']??'') ?>">
              </div>
              <div class="col-md-1">
                <label class="form-label">BG Color</label>
                <input type="color" class="form-control form-control-sm form-control-color" name="banners[<?=$i?>][bg]" value="<?= htmlspecialchars($b['bg']??'#1a4fba') ?>">
              </div>
              <div class="col-md-1">
                <label class="form-label">Text Color</label>
                <input type="color" class="form-control form-control-sm form-control-color" name="banners[<?=$i?>][color]" value="<?= htmlspecialchars($b['color']??'#ffffff') ?>">
              </div>
              <div class="col-md-1">
                <label class="form-label">Icon/Emoji</label>
                <input type="text" class="form-control form-control-sm" name="banners[<?=$i?>][icon]" value="<?= htmlspecialchars($b['icon']??'🎓') ?>">
              </div>
              <div class="col-md-1 d-flex align-items-end">
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" name="banners[<?=$i?>][active]" value="1" <?= ($b['active']??'') === '1' ? 'checked' : '' ?>>
                  <label class="form-check-label small">Active</label>
                </div>
              </div>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
        <button type="submit" class="btn btn-primary">Save Banners</button>
      </form>
    </div>

    <!-- ── POPUP TAB ── -->
    <div class="tab-pane" id="tab-popups">
      <p class="text-muted small mb-3">Exit-intent or timed popup to capture leads or show offers.</p>
      <form method="post">
        <input type="hidden" name="_tab" value="popups">
        <div class="row g-3">
          <div class="col-12">
            <div class="form-check form-switch">
              <input class="form-check-input" type="checkbox" name="popup_enabled" value="1" id="popEnabled" <?= ($s['popup_enabled']??'') === '1' ? 'checked' : '' ?>>
              <label class="form-check-label fw-600" for="popEnabled">Enable Popup</label>
            </div>
          </div>
          <div class="col-md-6">
            <label class="form-label">Title</label>
            <input type="text" class="form-control form-control-sm" name="popup_title" value="<?= sv($s,'popup_title','Get ₹5,000 Cashback on Admission!') ?>">
          </div>
          <div class="col-md-6">
            <label class="form-label">Subtitle</label>
            <input type="text" class="form-control form-control-sm" name="popup_subtitle" value="<?= sv($s,'popup_subtitle','Apply through CollegeKampus and claim your cashback.') ?>">
          </div>
          <div class="col-md-4">
            <label class="form-label">CTA Button Text</label>
            <input type="text" class="form-control form-control-sm" name="popup_cta_text" value="<?= sv($s,'popup_cta_text','Claim Now — Free') ?>">
          </div>
          <div class="col-md-4">
            <label class="form-label">CTA Link</label>
            <input type="text" class="form-control form-control-sm" name="popup_cta_link" value="<?= sv($s,'popup_cta_link','apply.php') ?>">
          </div>
          <div class="col-md-4">
            <label class="form-label">Trigger Delay (seconds)</label>
            <input type="number" class="form-control form-control-sm" name="popup_delay" min="0" max="60" value="<?= sv($s,'popup_delay','8') ?>">
          </div>
        </div>
        <button type="submit" class="btn btn-primary mt-3">Save Popup Settings</button>
      </form>
    </div>

    <!-- ── CTA BUTTONS TAB ── -->
    <div class="tab-pane" id="tab-cta_buttons">
      <p class="text-muted small mb-3">Custom CTA buttons that can appear in the homepage hero, college cards, or section footers. Reference them in templates as <code>cms('cta_buttons')</code>.</p>
      <form method="post">
        <input type="hidden" name="_tab" value="cta_buttons">
        <?php foreach ($ctaBtns as $i => $b): ?>
        <div class="card mb-3">
          <div class="card-body">
            <h6 class="fw-700 mb-3">CTA Button <?= $i+1 ?></h6>
            <div class="row g-2">
              <div class="col-md-3">
                <label class="form-label">Button Label</label>
                <input type="text" class="form-control form-control-sm" name="cta_buttons[<?=$i?>][label]" value="<?= htmlspecialchars($b['label']??'') ?>">
              </div>
              <div class="col-md-3">
                <label class="form-label">Link / URL</label>
                <input type="text" class="form-control form-control-sm" name="cta_buttons[<?=$i?>][link]" value="<?= htmlspecialchars($b['link']??'') ?>">
              </div>
              <div class="col-md-2">
                <label class="form-label">Style</label>
                <select class="form-select form-select-sm" name="cta_buttons[<?=$i?>][style]">
                  <?php foreach (['primary','success','warning','danger','outline-primary','outline-success'] as $st): ?>
                  <option value="<?=$st?>" <?= ($b['style']??'primary')===$st?'selected':'' ?>><?=$st?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="col-md-4">
                <label class="form-label">Show on Page (homepage/colleges/all)</label>
                <input type="text" class="form-control form-control-sm" name="cta_buttons[<?=$i?>][page]" value="<?= htmlspecialchars($b['page']??'') ?>" placeholder="homepage">
              </div>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
        <button type="submit" class="btn btn-primary">Save CTA Buttons</button>
      </form>
    </div>

  </div><!-- /tab content -->
</div><!-- /main -->
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
function showTab(name) {
  document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
  document.querySelectorAll('.tab-pane').forEach(p => p.classList.remove('active'));
  event.target.classList.add('active');
  document.getElementById('tab-' + name).classList.add('active');
}
</script>
</body>
</html>
