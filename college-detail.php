<?php
require_once __DIR__ . '/config/db.php';

$id   = intval($_GET['id'] ?? 0);
$slug = trim($_GET['slug'] ?? '');

// Ad-campaign / lead-gen landing mode: reached via ?lp=1 or any utm_source
// (paid traffic), strips the mega-nav and footer link farm so a visitor who
// clicked an ad sees the lead form, not 15 ways to leave the page. Captured
// UTM params are threaded into the lead form's hidden fields below so leads
// are attributed back to the campaign that generated them.
$utmSource   = trim($_GET['utm_source'] ?? '');
$utmMedium   = trim($_GET['utm_medium'] ?? '');
$utmCampaign = trim($_GET['utm_campaign'] ?? '');
$landingMode = !empty($_GET['lp']) || $utmSource !== '';

if (!$id && !$slug) {
    header('Location: ' . SITE_BASE . '/colleges');
    exit;
}

$college = null;
$courses = [];
$related = [];

try {
    $db = getDB();

    $allCols = [];
    $stmt = $db->query("SHOW COLUMNS FROM colleges");
    foreach ($stmt->fetchAll() as $r) $allCols[] = $r['Field'];

    $safeCols = ['city','state','district','address','pincode','college_type','delivery_mode','institution_type',
                 'established_year','accreditation','naac_grade','nirf_rank','nirf_score','rating',
                 'min_fees','max_fees','placement_rate','avg_package','is_featured','is_partner',
                 'ugc_approved','online_mode','website','email','phone','short_name',
                 'logo_url','cover_image_url','description','slug'];
    $select = 'c.id, c.name';
    foreach ($safeCols as $col) {
        if (in_array($col, $allCols)) $select .= ", c.$col";
    }

    if ($id > 0) {
        $stmt = $db->prepare("SELECT $select FROM colleges c WHERE c.id = ? LIMIT 1");
        $stmt->execute([$id]);
    } else {
        $stmt = $db->prepare("SELECT $select FROM colleges c WHERE c.slug = ? LIMIT 1");
        $stmt->execute([$slug]);
    }
    $college = $stmt->fetch();

    if (!$college) {
        header('Location: ' . SITE_BASE . '/colleges');
        exit;
    }
    $id = $college['id'];

    // Courses offered
    try {
        // college_courses is a flat table - course_name/stream/duration/
        // degree_type/annual_fees/seats_available/eligibility live directly
        // on the row, no separate courses catalog to join (there never was
        // one on this shared DB - see api/college-courses-save.php for the
        // full story). Aliased to the column names this page's display code
        // and the college_streams fallback below both already expect.
        $stmt2 = $db->prepare(
            "SELECT course_name AS name, stream, degree_type AS degree_level, duration,
                    annual_fees AS fees, seats_available AS seats
             FROM college_courses WHERE college_id = ? AND is_active = 1 ORDER BY course_name"
        );
        $stmt2->execute([$id]);
        $courses = $stmt2->fetchAll();

        // Fallback: the online-colleges dataset (database/seed_online_colleges.sql)
        // stores each college's programmes as plain stream names in
        // college_streams instead of college_courses/courses, so every one of
        // these colleges had zero rows above - an empty "Courses & Fees" tab
        // and an empty Course Interest dropdown on the lead form, even though
        // the college genuinely does offer named programmes. Surface those
        // names (with no fee/duration data to show, since college_streams
        // never had any) rather than showing nothing at all.
        if (empty($courses)) {
            try {
                $ss = $db->prepare("SELECT stream_name AS name FROM college_streams WHERE college_id = ? ORDER BY stream_name");
                $ss->execute([$id]);
                $courses = $ss->fetchAll();
            } catch (Throwable $e) {}
        }
    } catch (Throwable $e) {}

    // Related colleges: prefer the same institution_type (university vs. a
    // single-purpose college - e.g. a B.Ed college) and the same online/
    // distance status first. This portal's online-colleges dataset
    // (database/seed_online_colleges.sql) stores its "courses" as free-text
    // rows in college_streams, not in the courses/college_courses tables -
    // matching on courses.category (an earlier attempt at this fix) silently
    // matched nothing for every one of these rows and fell straight back to
    // an unfiltered same-state pick, which is why a general/DDE university
    // could still end up next to single-purpose B.Ed colleges. Falls back to
    // progressively looser matches only when a stricter tier returns too few
    // results, so a college with no institution_type/online_mode data still
    // gets a same-state result rather than an empty section.
    try {
        $relSelect = 'c.id, c.name';
        foreach (['city','state','naac_grade','slug','logo_url','college_type','min_fees','short_name'] as $col) {
            if (in_array($col, $allCols)) $relSelect .= ", c.$col";
        }

        $hasInstType   = in_array('institution_type', $allCols) && !empty($college['institution_type']);
        $hasOnlineMode = in_array('online_mode', $allCols) && !empty($college['online_mode']);

        $related = [];

        // Tier 1: same state + same institution_type + also online/distance
        if ($hasInstType || $hasOnlineMode) {
            $relWhere = ['c.id != ?'];
            $relParams = [$id];
            if (!empty($college['state'])) { $relWhere[] = 'c.state = ?'; $relParams[] = $college['state']; }
            if (in_array('is_active', $allCols)) $relWhere[] = 'c.is_active = 1';
            if ($hasInstType)   { $relWhere[] = 'c.institution_type = ?'; $relParams[] = $college['institution_type']; }
            if ($hasOnlineMode) { $relWhere[] = "c.online_mode IS NOT NULL AND c.online_mode != ''"; }
            $stmt3 = $db->prepare("SELECT $relSelect FROM colleges c WHERE " . implode(' AND ', $relWhere) . " ORDER BY RAND() LIMIT 4");
            $stmt3->execute($relParams);
            $related = $stmt3->fetchAll();
        }

        // Tier 2: same institution_type only, drop the state restriction
        if (count($related) < 2 && $hasInstType) {
            $relWhere = ['c.id != ?', 'c.institution_type = ?'];
            $relParams = [$id, $college['institution_type']];
            if (in_array('is_active', $allCols)) $relWhere[] = 'c.is_active = 1';
            $stmt3 = $db->prepare("SELECT $relSelect FROM colleges c WHERE " . implode(' AND ', $relWhere) . " ORDER BY RAND() LIMIT 4");
            $stmt3->execute($relParams);
            $related = $stmt3->fetchAll();
        }

        // Tier 3: original state-only fallback for colleges with no
        // institution_type/online_mode data at all.
        if (count($related) < 2) {
            $relWhere = ['c.id != ?'];
            $relParams = [$id];
            if (!empty($college['state'])) { $relWhere[] = 'c.state = ?'; $relParams[] = $college['state']; }
            if (in_array('is_active', $allCols)) $relWhere[] = 'c.is_active = 1';
            $stmt3 = $db->prepare("SELECT $relSelect FROM colleges c WHERE " . implode(' AND ', $relWhere) . " ORDER BY RAND() LIMIT 4");
            $stmt3->execute($relParams);
            $related = $stmt3->fetchAll();
        }
    } catch (Throwable $e) {}

} catch (Throwable $e) {
    die('Error: ' . htmlspecialchars($e->getMessage()));
}

$pageTitle = $college['name'];
$pageDesc  = 'Explore ' . $college['name'] . ' — courses, fees, placements, admission process. Apply free at CollegeKampus Online.';
$collegeSlug = $college['slug'] ?? $id;

include __DIR__ . '/includes/header.php';

// Helpers
function fmt_fees($min, $max) {
    if (!$min && !$max) return null;
    $f = fn($v) => $v >= 100000 ? '&#8377;' . round($v/100000,1) . 'L' : '&#8377;' . number_format($v);
    if ($min && $max) return $f($min) . ' – ' . $f($max) . '/yr';
    return ($min ? $f($min) : $f($max)) . '/yr';
}
$feesDisplay = fmt_fees($college['min_fees']??0, $college['max_fees']??0);
$typeVal = $college['college_type'] ?? $college['type'] ?? '';
$modeVal = $college['delivery_mode'] ?? $college['online_mode'] ?? '';
?>

<style>
.college-detail-hero{background:linear-gradient(135deg,#0f172a 0%,#1e3a5f 100%);padding:40px 0 0;color:#fff;position:relative;overflow:hidden;}
.college-detail-hero::before{content:'';position:absolute;inset:0;background:url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="80" cy="20" r="40" fill="rgba(255,255,255,.03)"/><circle cx="20" cy="80" r="30" fill="rgba(255,255,255,.03)"/></svg>');background-size:cover;}
.college-detail-hero .container{position:relative;}
.college-hero-main{display:flex;align-items:flex-start;gap:20px;flex-wrap:wrap;padding-bottom:24px;}
.college-detail-logo{width:80px;height:80px;border-radius:14px;background:#fff;display:flex;align-items:center;justify-content:center;overflow:hidden;flex-shrink:0;border:3px solid rgba(255,255,255,.2);}
.college-detail-logo img{width:100%;height:100%;object-fit:contain;}
.college-detail-logo-abbr{font-size:1.2rem;font-weight:900;color:#2563eb;}
.college-hero-name{font-size:clamp(1.2rem,3vw,1.9rem);font-weight:900;margin-bottom:6px;line-height:1.2;}
.college-hero-badges{display:flex;gap:6px;flex-wrap:wrap;margin-bottom:10px;}
.college-hero-meta{display:flex;gap:16px;flex-wrap:wrap;}
.college-hero-meta-item{font-size:.82rem;color:rgba(255,255,255,.7);display:flex;align-items:center;gap:5px;}
.college-hero-meta-item strong{color:#fff;}
.college-hero-ctas{display:flex;gap:10px;flex-wrap:wrap;margin-top:16px;}
.btn-hero-primary{background:#22c55e;color:#fff;padding:10px 20px;border-radius:8px;font-size:.85rem;font-weight:700;text-decoration:none;display:inline-flex;align-items:center;gap:6px;transition:background .2s;}
.btn-hero-primary:hover{background:#16a34a;color:#fff;}
.btn-hero-outline{background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.3);color:#fff;padding:10px 18px;border-radius:8px;font-size:.85rem;font-weight:600;text-decoration:none;display:inline-flex;align-items:center;gap:6px;transition:background .2s;}
.btn-hero-outline:hover{background:rgba(255,255,255,.2);color:#fff;}
.college-detail-tabs{background:#fff;border-bottom:1px solid #e2e8f0;position:sticky;top:64px;z-index:100;}
.detail-tab-nav{display:flex;gap:0;overflow-x:auto;scrollbar-width:none;}
.detail-tab-nav::-webkit-scrollbar{display:none;}
.detail-tab-btn{padding:14px 20px;font-size:.875rem;font-weight:600;color:#64748b;border:none;background:none;cursor:pointer;white-space:nowrap;border-bottom:3px solid transparent;transition:color .2s,border-color .2s;}
.detail-tab-btn:hover{color:#2563eb;}
.detail-tab-btn.active{color:#2563eb;border-bottom-color:#2563eb;}
.detail-tab-pane{display:none;}
.detail-tab-pane.active{display:block;}
.detail-layout{display:grid;grid-template-columns:1fr 320px;gap:28px;padding:28px 0;align-items:start;}
@media(max-width:900px){.detail-layout{grid-template-columns:1fr;}}
.stats-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(140px,1fr));gap:12px;margin:20px 0;}
.stat-card{background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:14px;text-align:center;}
.stat-card .stat-val{font-size:1.2rem;font-weight:800;color:#0f172a;}
.stat-card .stat-label{font-size:.72rem;color:#64748b;margin-top:4px;}
.courses-table{width:100%;border-collapse:collapse;font-size:.875rem;}
.courses-table th{background:#f8fafc;padding:10px 14px;text-align:left;font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#64748b;border-bottom:2px solid #e2e8f0;}
.courses-table td{padding:12px 14px;border-bottom:1px solid #f1f5f9;color:#0f172a;vertical-align:middle;}
.courses-table tr:hover td{background:#f8fafc;}
.apply-sidebar{background:#fff;border:1px solid #e2e8f0;border-radius:14px;overflow:hidden;position:sticky;top:120px;}
.apply-sidebar-header{background:linear-gradient(135deg,#2563eb,#1d4ed8);color:#fff;padding:18px 20px;}
.apply-sidebar-header h3{font-size:1rem;font-weight:800;margin-bottom:2px;}
.apply-sidebar-header p{font-size:.78rem;color:rgba(255,255,255,.8);margin:0;}
.apply-sidebar-body{padding:20px;}
.apply-field{margin-bottom:14px;}
.apply-field label{display:block;font-size:.78rem;font-weight:600;color:#64748b;margin-bottom:5px;}
.apply-field input,.apply-field select{width:100%;padding:9px 12px;border:1px solid #e2e8f0;border-radius:8px;font-size:.875rem;color:#0f172a;transition:border-color .2s;font-family:inherit;}
.apply-field input:focus,.apply-field select:focus{outline:none;border-color:#2563eb;}
.btn-apply-submit{width:100%;padding:12px;background:#22c55e;color:#fff;border:none;border-radius:8px;font-size:.9rem;font-weight:700;cursor:pointer;transition:background .2s;}
.btn-apply-submit:hover{background:#16a34a;}
.related-card{display:flex;align-items:center;gap:12px;padding:12px;border:1px solid #e2e8f0;border-radius:10px;transition:box-shadow .2s;text-decoration:none;color:inherit;}
.related-card:hover{box-shadow:0 4px 16px rgba(0,0,0,.08);}
.related-logo{width:40px;height:40px;border-radius:8px;background:#eff6ff;display:flex;align-items:center;justify-content:center;font-size:.72rem;font-weight:800;color:#2563eb;flex-shrink:0;}
</style>

<!-- Hero -->
<div class="college-detail-hero">
  <div class="container">
    <?php if (!empty($college['cover_image_url'])): ?>
    <img src="<?= htmlspecialchars($college['cover_image_url']) ?>" alt="<?= htmlspecialchars($college['name']) ?> campus" style="position:absolute;inset:0;width:100%;height:100%;object-fit:cover;opacity:.15;" onerror="this.remove()">
    <?php endif; ?>
    <div class="college-hero-main">
      <div class="college-detail-logo">
        <?php if (!empty($college['logo_url'])): ?>
        <img src="<?= htmlspecialchars($college['logo_url']) ?>" alt="<?= htmlspecialchars($college['name']) ?>" onerror="this.parentElement.innerHTML='<span class=college-detail-logo-abbr><?= substr($college['short_name']??$college['name'],0,2) ?></span>'">
        <?php else: ?>
        <span class="college-detail-logo-abbr"><?= htmlspecialchars(substr($college['short_name']??$college['name'],0,2)) ?></span>
        <?php endif; ?>
      </div>
      <div style="flex:1;">
        <?php if (!empty($college['is_featured'])): ?>
        <div style="display:inline-flex;align-items:center;gap:5px;background:rgba(34,197,94,.2);border:1px solid rgba(34,197,94,.4);color:#22c55e;font-size:.72rem;font-weight:700;padding:3px 10px;border-radius:4px;margin-bottom:8px;">
          &#11088; Featured University
        </div>
        <?php endif; ?>
        <h1 class="college-hero-name"><?= htmlspecialchars($college['name']) ?></h1>
        <div class="college-hero-badges">
          <?php if ($typeVal): ?>
          <span class="college-badge badge-<?= strtolower($typeVal) ?>"><?= ucfirst($typeVal) ?></span>
          <?php endif; ?>
          <?php if (!empty($college['naac_grade'])): ?>
          <span class="college-badge badge-naac">NAAC <?= htmlspecialchars($college['naac_grade']) ?></span>
          <?php endif; ?>
          <?php if (!empty($college['nirf_rank'])): ?>
          <span class="college-badge badge-nirf">NIRF #<?= htmlspecialchars($college['nirf_rank']) ?></span>
          <?php endif; ?>
          <?php if (!empty($college['ugc_approved'])): ?>
          <span class="college-badge badge-ugc">UGC Approved</span>
          <?php endif; ?>
          <?php if ($modeVal): ?>
          <span class="college-badge badge-online"><?= ucfirst($modeVal) ?></span>
          <?php endif; ?>
        </div>
        <div class="college-hero-meta">
          <?php if (!empty($college['city']) || !empty($college['state'])): ?>
          <span class="college-hero-meta-item">&#128205; <?= htmlspecialchars(implode(', ', array_filter([$college['city']??'', $college['state']??'']))) ?></span>
          <?php endif; ?>
          <?php if (!empty($college['established_year'])): ?>
          <span class="college-hero-meta-item">&#128197; Est. <strong><?= htmlspecialchars($college['established_year']) ?></strong></span>
          <?php endif; ?>
          <?php if ($feesDisplay): ?>
          <span class="college-hero-meta-item">&#128179; <strong><?= $feesDisplay ?></strong></span>
          <?php endif; ?>
          <?php if (!empty($college['rating'])): ?>
          <span class="college-hero-meta-item">&#11088; <strong><?= number_format($college['rating'],1) ?>/5</strong></span>
          <?php endif; ?>
        </div>
        <div class="college-hero-ctas">
          <a href="<?= $base ?>/apply.php?college_id=<?= $id ?>" class="btn-hero-primary">&#128221; Apply Now</a>
          <?php if (!$landingMode): ?>
          <button class="btn-hero-outline" onclick="addToCompare('<?= $id ?>','<?= addslashes(htmlspecialchars($college['name'])) ?>')">&#9878; Add to Compare</button>
          <?php endif; ?>
          <a href="<?= $base ?>/counselling?college=<?= urlencode($college['name']) ?>" class="btn-hero-outline">&#128222; Get Free Advice</a>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Sticky Tabs -->
<div class="college-detail-tabs">
  <div class="container">
    <div class="detail-tab-nav">
      <button class="detail-tab-btn active" onclick="showTab('overview',this)">Overview</button>
      <button class="detail-tab-btn" onclick="showTab('courses',this)">Courses &amp; Fees</button>
      <button class="detail-tab-btn" onclick="showTab('admission',this)">Admission</button>
      <button class="detail-tab-btn" onclick="showTab('placements',this)">Placements</button>
    </div>
  </div>
</div>

<div class="page-wrapper" style="margin-top:0;">
  <div class="container">
    <div class="detail-layout">

      <!-- Left: Tabs Content -->
      <div>
        <!-- Overview Tab -->
        <div class="detail-tab-pane active" id="tab-overview">
          <?php if (!empty($college['description'])): ?>
          <div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:24px;margin-bottom:20px;">
            <h2 style="font-size:1.05rem;font-weight:700;margin-bottom:12px;">About <?= htmlspecialchars($college['name']) ?></h2>
            <p style="font-size:.875rem;color:#374151;line-height:1.7;"><?= nl2br(htmlspecialchars($college['description'])) ?></p>
          </div>
          <?php endif; ?>

          <div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:24px;margin-bottom:20px;">
            <h2 style="font-size:1.05rem;font-weight:700;margin-bottom:14px;">Key Statistics</h2>
            <div class="stats-grid">
              <?php if (!empty($college['established_year'])): ?>
              <div class="stat-card"><div class="stat-val"><?= htmlspecialchars($college['established_year']) ?></div><div class="stat-label">Founded</div></div>
              <?php endif; ?>
              <?php if ($typeVal): ?>
              <div class="stat-card"><div class="stat-val"><?= ucfirst(htmlspecialchars($typeVal)) ?></div><div class="stat-label">Type</div></div>
              <?php endif; ?>
              <?php if (!empty($college['accreditation'])): ?>
              <div class="stat-card"><div class="stat-val"><?= htmlspecialchars($college['accreditation']) ?></div><div class="stat-label">Accreditation</div></div>
              <?php endif; ?>
              <?php if (!empty($college['naac_grade'])): ?>
              <div class="stat-card"><div class="stat-val" style="color:#16a34a;"><?= htmlspecialchars($college['naac_grade']) ?></div><div class="stat-label">NAAC Grade</div></div>
              <?php endif; ?>
              <?php if (!empty($college['nirf_rank'])): ?>
              <div class="stat-card"><div class="stat-val" style="color:#2563eb;">#<?= htmlspecialchars($college['nirf_rank']) ?></div><div class="stat-label">NIRF Rank</div></div>
              <?php endif; ?>
              <?php if (!empty($college['placement_rate'])): ?>
              <div class="stat-card"><div class="stat-val"><?= htmlspecialchars($college['placement_rate']) ?>%</div><div class="stat-label">Placement Rate</div></div>
              <?php endif; ?>
              <?php if (!empty($college['avg_package'])): ?>
              <div class="stat-card"><div class="stat-val">&#8377;<?= number_format($college['avg_package']/100000,1) ?>L</div><div class="stat-label">Avg Package</div></div>
              <?php endif; ?>
              <?php if (!empty($college['rating'])): ?>
              <div class="stat-card"><div class="stat-val" style="color:#f59e0b;"><?= number_format($college['rating'],1) ?>/5</div><div class="stat-label">Rating</div></div>
              <?php endif; ?>
            </div>
          </div>

          <?php if (!empty($college['website']) || !empty($college['email']) || !empty($college['phone'])): ?>
          <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;padding:20px;">
            <h3 style="font-size:.9rem;font-weight:700;margin-bottom:12px;">Contact Information</h3>
            <div style="display:flex;flex-direction:column;gap:10px;">
              <?php if (!empty($college['address'])): ?><div style="font-size:.85rem;color:#374151;display:flex;gap:8px;"><i class="bi bi-geo-alt-fill text-danger"></i><?= htmlspecialchars($college['address']) ?></div><?php endif; ?>
              <?php if (!empty($college['phone'])): ?><div style="font-size:.85rem;"><a href="tel:<?= preg_replace('/[^0-9+]/','',$college['phone']) ?>" style="color:#2563eb;text-decoration:none;display:flex;gap:8px;"><i class="bi bi-telephone-fill"></i><?= htmlspecialchars($college['phone']) ?></a></div><?php endif; ?>
              <?php if (!empty($college['email'])): ?><div style="font-size:.85rem;"><a href="mailto:<?= htmlspecialchars($college['email']) ?>" style="color:#2563eb;text-decoration:none;display:flex;gap:8px;"><i class="bi bi-envelope-fill"></i><?= htmlspecialchars($college['email']) ?></a></div><?php endif; ?>
              <?php if (!empty($college['website'])): ?><div><a href="<?= htmlspecialchars($college['website']) ?>" target="_blank" rel="noopener" style="color:#2563eb;font-size:.85rem;display:flex;gap:8px;align-items:center;text-decoration:none;"><i class="bi bi-globe"></i> <?= htmlspecialchars($college['website']) ?> <i class="bi bi-box-arrow-up-right" style="font-size:.7rem;"></i></a></div><?php endif; ?>
            </div>
          </div>
          <?php endif; ?>
        </div>

        <!-- Courses Tab -->
        <div class="detail-tab-pane" id="tab-courses">
          <div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:24px;">
            <h2 style="font-size:1.05rem;font-weight:700;margin-bottom:16px;">Courses &amp; Fees Offered</h2>
            <?php if ($courses): ?>
            <div style="overflow-x:auto;">
              <table class="courses-table">
                <thead>
                  <tr>
                    <th>Program</th>
                    <th>Level</th>
                    <th>Duration</th>
                    <th>Fees</th>
                    <th>Seats</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach($courses as $cr): ?>
                  <tr>
                    <td style="font-weight:600;"><?= htmlspecialchars($cr['name']) ?></td>
                    <td><?= htmlspecialchars($cr['degree_level'] ?? '—') ?></td>
                    <td><?= htmlspecialchars($cr['duration'] ?? '—') ?></td>
                    <td><?= !empty($cr['fees']) ? '&#8377;'.number_format($cr['fees']) : '—' ?></td>
                    <td><?= !empty($cr['seats']) ? htmlspecialchars($cr['seats']) : '—' ?></td>
                  </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
            <?php else: ?>
            <div style="text-align:center;padding:40px;color:#64748b;">
              <div style="font-size:2rem;margin-bottom:8px;">&#128218;</div>
              <p>Course details not available. <a href="<?= $base ?>/counselling" style="color:#2563eb;">Contact us for details</a></p>
            </div>
            <?php endif; ?>
          </div>
        </div>

        <!-- Admission Tab -->
        <div class="detail-tab-pane" id="tab-admission">
          <div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:24px;">
            <h2 style="font-size:1.05rem;font-weight:700;margin-bottom:16px;">Admission Process</h2>
            <div style="display:flex;flex-direction:column;gap:0;">
              <?php $steps = [
                ['1','Check Eligibility','Verify your qualification meets the program requirements'],
                ['2','Fill Application','Complete the online application form with required documents'],
                ['3','Document Verification','Submit scanned copies of certificates and ID proof'],
                ['4','Fee Payment','Pay the admission fee online through the portal'],
                ['5','Confirmation','Receive admission confirmation and welcome kit'],
              ]; foreach($steps as [$num,$title,$desc]): ?>
              <div style="display:flex;gap:16px;padding:16px 0;border-bottom:1px solid #f1f5f9;">
                <div style="width:32px;height:32px;border-radius:50%;background:#2563eb;color:#fff;display:flex;align-items:center;justify-content:center;font-size:.8rem;font-weight:800;flex-shrink:0;"><?= $num ?></div>
                <div>
                  <div style="font-size:.9rem;font-weight:700;color:#0f172a;margin-bottom:3px;"><?= htmlspecialchars($title) ?></div>
                  <div style="font-size:.82rem;color:#64748b;"><?= htmlspecialchars($desc) ?></div>
                </div>
              </div>
              <?php endforeach; ?>
            </div>
            <a href="<?= $base ?>/apply.php?college_id=<?= $id ?>" class="btn-hero-primary" style="margin-top:20px;display:inline-flex;">&#128221; Start Application</a>
          </div>
        </div>

        <!-- Placements Tab -->
        <div class="detail-tab-pane" id="tab-placements">
          <div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:24px;">
            <h2 style="font-size:1.05rem;font-weight:700;margin-bottom:16px;">Placement Statistics</h2>
            <div class="stats-grid">
              <?php if (!empty($college['placement_rate'])): ?>
              <div class="stat-card"><div class="stat-val" style="color:#22c55e;"><?= htmlspecialchars($college['placement_rate']) ?>%</div><div class="stat-label">Placement Rate</div></div>
              <?php endif; ?>
              <?php if (!empty($college['avg_package'])): ?>
              <div class="stat-card"><div class="stat-val">&#8377;<?= number_format($college['avg_package']/100000,1) ?>L</div><div class="stat-label">Average Package</div></div>
              <?php endif; ?>
            </div>
            <?php if (empty($college['placement_rate']) && empty($college['avg_package'])): ?>
            <p style="color:#64748b;font-size:.875rem;">Placement data not available. <a href="<?= $base ?>/counselling" style="color:#2563eb;">Contact us</a> for more information.</p>
            <?php endif; ?>
          </div>
        </div>

        <!-- Related Colleges -->
        <?php if ($related): ?>
        <div style="margin-top:28px;">
          <h3 style="font-size:1rem;font-weight:700;margin-bottom:14px;">Similar Universities in <?= htmlspecialchars($college['state']??'India') ?></h3>
          <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:12px;">
            <?php foreach($related as $r): $rSlug = $r['slug']??$r['id']; ?>
            <a href="<?= $base ?>/college/<?= urlencode($rSlug) ?>" class="related-card">
              <div class="related-logo"><?= htmlspecialchars(substr($r['short_name']??$r['name'],0,2)) ?></div>
              <div>
                <div style="font-size:.85rem;font-weight:700;color:#0f172a;margin-bottom:2px;"><?= htmlspecialchars($r['name']) ?></div>
                <div style="font-size:.75rem;color:#64748b;"><?= htmlspecialchars(implode(', ',array_filter([$r['city']??'',$r['state']??'']))) ?></div>
                <?php if(!empty($r['naac_grade'])): ?><span class="college-badge badge-naac" style="margin-top:4px;">NAAC <?= htmlspecialchars($r['naac_grade']) ?></span><?php endif; ?>
              </div>
            </a>
            <?php endforeach; ?>
          </div>
        </div>
        <?php endif; ?>
      </div>

      <!-- Right: Apply Sidebar -->
      <div>
        <div class="apply-sidebar">
          <div class="apply-sidebar-header">
            <h3>&#9993; Quick Enquiry</h3>
            <p>Get personalised guidance — 100% Free</p>
            <?php if ($landingMode): ?>
            <p style="margin:8px 0 0;font-size:.72rem;font-weight:700;background:rgba(255,255,255,.15);display:inline-flex;padding:3px 10px;border-radius:12px;">&#9989; Official Admission Partner</p>
            <?php endif; ?>
          </div>
          <div class="apply-sidebar-body">
            <?php if (!empty($_GET['submitted'])): ?>
            <div style="text-align:center;padding:20px;">
              <div style="font-size:2rem;margin-bottom:8px;">&#10003;</div>
              <p style="font-weight:700;color:#16a34a;">Submitted! We'll call you soon.</p>
            </div>
            <?php else: ?>
            <form method="POST" action="<?= $base ?>/api/leads.php" onsubmit="return handleApply(event,this)">
              <input type="hidden" name="college_id" value="<?= $id ?>">
              <input type="hidden" name="college_name" value="<?= htmlspecialchars($college['name']) ?>">
              <input type="hidden" name="source" value="<?= $landingMode ? 'college-landing' : 'college-detail' ?>">
              <input type="hidden" name="page_url" value="<?= htmlspecialchars((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https://' : 'http://') . ($_SERVER['HTTP_HOST'] ?? '') . ($_SERVER['REQUEST_URI'] ?? '')) ?>">
              <input type="hidden" name="utm_source" value="<?= htmlspecialchars($utmSource) ?>">
              <input type="hidden" name="utm_medium" value="<?= htmlspecialchars($utmMedium) ?>">
              <input type="hidden" name="utm_campaign" value="<?= htmlspecialchars($utmCampaign) ?>">
              <div class="apply-field">
                <label>Full Name *</label>
                <input type="text" name="name" placeholder="Your full name" required>
              </div>
              <div class="apply-field">
                <label>Mobile Number *</label>
                <input type="tel" name="phone" placeholder="+91 98765 43210" required pattern="[0-9+\s\-]{8,15}">
              </div>
              <div class="apply-field">
                <label>Email Address *</label>
                <input type="email" name="email" placeholder="you@email.com" required>
              </div>
              <div class="apply-field">
                <label>Course Interest</label>
                <select name="course_interest">
                  <option value="">Select a course</option>
                  <?php foreach($courses as $cr): ?>
                  <option value="<?= htmlspecialchars($cr['name']) ?>"><?= htmlspecialchars($cr['name']) ?></option>
                  <?php endforeach; ?>
                  <option value="Other">Other</option>
                </select>
              </div>
              <button type="submit" class="btn-apply-submit">&#9993; Get Free Counselling</button>
              <p style="font-size:.72rem;color:#9ca3af;text-align:center;margin-top:10px;">By submitting you agree to our <a href="<?= $base ?>/privacy-policy" style="color:#2563eb;">Privacy Policy</a></p>
            </form>
            <?php endif; ?>
          </div>
        </div>

        <!-- Trust indicators -->
        <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:12px;padding:16px;margin-top:14px;text-align:center;">
          <div style="font-size:.78rem;color:#16a34a;font-weight:700;margin-bottom:8px;">&#10003; Trusted by 1.25L+ Students</div>
          <div style="display:flex;justify-content:space-around;font-size:.72rem;color:#166534;">
            <span>&#127891; Free Guidance</span>
            <span>&#128222; Expert Mentors</span>
          </div>
        </div>
      </div>

    </div>
  </div>
</div>

<script>
function showTab(name, btn) {
  document.querySelectorAll('.detail-tab-pane').forEach(function(p){ p.classList.remove('active'); });
  document.querySelectorAll('.detail-tab-btn').forEach(function(b){ b.classList.remove('active'); });
  var pane = document.getElementById('tab-'+name);
  if(pane) pane.classList.add('active');
  if(btn) btn.classList.add('active');
}
function handleApply(e, form) {
  e.preventDefault();
  var btn = form.querySelector('.btn-apply-submit');
  btn.disabled = true;
  btn.textContent = 'Submitting...';
  fetch(form.action, {method:'POST', body: new FormData(form)})
    .then(function(r){ return r.json(); })
    .then(function(d){
      if(d.success){
        form.closest('.apply-sidebar-body').innerHTML = '<div style="text-align:center;padding:20px;"><div style="font-size:2rem;margin-bottom:8px;">&#10003;</div><p style="font-weight:700;color:#16a34a;">Thank you! Our counsellor will contact you shortly.</p></div>';
      } else {
        btn.disabled = false;
        btn.textContent = 'Get Free Counselling';
        alert(d.message || 'Something went wrong. Please try again.');
      }
    })
    .catch(function(){
      btn.disabled = false;
      btn.textContent = 'Get Free Counselling';
    });
  return false;
}
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
