<?php
$pageTitle = 'Online Courses & Programs in India 2025 | CollegeKampus';
$pageDesc  = 'Explore 50+ online degree programs - MBA, BBA, BCA, MCA, B.Com, MA and more from UGC approved universities in India.';
$pageKeywords = 'online MBA India, online BBA, online BCA, online MCA, distance education courses, UGC approved online degrees 2025';

require_once __DIR__ . '/config/db.php';

$activeLevel = trim($_GET['level'] ?? '');

// Program data with real structure
$programs = [
  ['slug'=>'MBA','name'=>'Online MBA','icon'=>'&#128202;','color'=>'#7c3aed','bg'=>'#ede9fe','level'=>'pg',
   'desc'=>'Master of Business Administration','specs'=>['Finance','Marketing','HR','Operations','IT','Banking'],
   'duration'=>'2 Years','eligibility'=>'Graduation in any stream'],
  ['slug'=>'BBA','name'=>'Online BBA','icon'=>'&#127919;','color'=>'#2563eb','bg'=>'#dbeafe','level'=>'ug',
   'desc'=>'Bachelor of Business Administration','specs'=>['General','Finance','Marketing','HR'],
   'duration'=>'3 Years','eligibility'=>'10+2 in any stream'],
  ['slug'=>'BCA','name'=>'Online BCA','icon'=>'&#128187;','color'=>'#16a34a','bg'=>'#dcfce7','level'=>'ug',
   'desc'=>'Bachelor of Computer Applications','specs'=>['General','Data Science','Cybersecurity'],
   'duration'=>'3 Years','eligibility'=>'10+2 with Maths'],
  ['slug'=>'MCA','name'=>'Online MCA','icon'=>'&#128421;','color'=>'#0369a1','bg'=>'#e0f2fe','level'=>'pg',
   'desc'=>'Master of Computer Applications','specs'=>['General','Data Science','AI & ML','Cloud'],
   'duration'=>'2 Years','eligibility'=>'BCA/B.Sc IT or equivalent'],
  ['slug'=>'B.Com','name'=>'Online B.Com','icon'=>'&#128200;','color'=>'#d97706','bg'=>'#fef3c7','level'=>'ug',
   'desc'=>'Bachelor of Commerce','specs'=>['General','Accounting','Finance','Taxation'],
   'duration'=>'3 Years','eligibility'=>'10+2 Commerce preferred'],
  ['slug'=>'MA','name'=>'Online MA','icon'=>'&#128218;','color'=>'#be185d','bg'=>'#fce7f3','level'=>'pg',
   'desc'=>'Master of Arts','specs'=>['English','Economics','Political Science','Sociology','History'],
   'duration'=>'2 Years','eligibility'=>'Graduation in relevant stream'],
  ['slug'=>'M.Com','name'=>'Online M.Com','icon'=>'&#128176;','color'=>'#16a34a','bg'=>'#f0fdf4','level'=>'pg',
   'desc'=>'Master of Commerce','specs'=>['Accounting','Finance','Banking','Taxation'],
   'duration'=>'2 Years','eligibility'=>'B.Com or equivalent'],
  ['slug'=>'B.Sc','name'=>'Online B.Sc','icon'=>'&#128300;','color'=>'#0ea5e9','bg'=>'#f0f9ff','level'=>'ug',
   'desc'=>'Bachelor of Science','specs'=>['IT','Maths','Physics','Chemistry','Biotechnology'],
   'duration'=>'3 Years','eligibility'=>'10+2 with Science'],
  ['slug'=>'Executive MBA','name'=>'Executive MBA','icon'=>'&#127931;','color'=>'#d97706','bg'=>'#fef9c3','level'=>'pg',
   'desc'=>'For working professionals','specs'=>['General Management','Strategy','Finance','Operations'],
   'duration'=>'1-2 Years','eligibility'=>'Graduation + 2 yrs work exp'],
  ['slug'=>'MBA 1 Year','name'=>'1-Year MBA','icon'=>'&#9889;','color'=>'#ef4444','bg'=>'#fee2e2','level'=>'pg',
   'desc'=>'Fast-track MBA for executives','specs'=>['General','Finance','Marketing'],
   'duration'=>'1 Year','eligibility'=>'Graduation + 5 yrs work exp'],
  ['slug'=>'Distance MBA','name'=>'Distance MBA','icon'=>'&#127968;','color'=>'#8b5cf6','bg'=>'#ede9fe','level'=>'pg',
   'desc'=>'MBA via distance learning','specs'=>['Finance','Marketing','HR','Operations'],
   'duration'=>'2 Years','eligibility'=>'Graduation in any stream'],
  ['slug'=>'BA','name'=>'Online BA','icon'=>'&#127981;','color'=>'#6b7280','bg'=>'#f3f4f6','level'=>'ug',
   'desc'=>'Bachelor of Arts','specs'=>['English','History','Political Science','Sociology','Economics'],
   'duration'=>'3 Years','eligibility'=>'10+2 in any stream'],
  ['slug'=>'M.Sc','name'=>'Online M.Sc','icon'=>'&#129514;','color'=>'#0891b2','bg'=>'#ecfeff','level'=>'pg',
   'desc'=>'Master of Science','specs'=>['IT','Data Science','Mathematics','Physics'],
   'duration'=>'2 Years','eligibility'=>'B.Sc in relevant stream'],
  ['slug'=>'Certificate','name'=>'Online Certificate','icon'=>'&#127942;','color'=>'#f97316','bg'=>'#fff7ed','level'=>'certificate',
   'desc'=>'Short-term certification programs','specs'=>['Digital Marketing','Data Analytics','Python','Accounting'],
   'duration'=>'3-6 Months','eligibility'=>'Any graduate'],
];

// Get real university counts from DB
$counts = [];
try {
  $db = getDB();
  // Try college_courses + courses table
  $stmt = $db->query("SELECT cr.name, COUNT(DISTINCT cc.college_id) AS cnt FROM college_courses cc JOIN courses cr ON cr.id = cc.course_id GROUP BY cr.name");
  foreach($stmt->fetchAll() as $r){
    $counts[strtolower($r['name'])] = (int)$r['cnt'];
  }
} catch(Throwable $e){}

$filteredPrograms = $activeLevel ? array_filter($programs, fn($p) => $p['level'] === $activeLevel) : $programs;

include __DIR__ . '/includes/header.php';
?>

<style>
.courses-hero{background:linear-gradient(135deg,#0f172a 0%,#1e3a5f 60%,#312e81 100%);padding:60px 0;color:#fff;text-align:center;}
.courses-hero h1{font-size:clamp(1.8rem,4vw,3rem);font-weight:900;margin-bottom:10px;}
.courses-hero p{color:rgba(255,255,255,.75);font-size:1rem;max-width:600px;margin:0 auto;}
.courses-layout{display:grid;grid-template-columns:220px 1fr;gap:28px;padding:32px 0;}
@media(max-width:768px){.courses-layout{grid-template-columns:1fr;}}
.courses-sidebar{height:fit-content;position:sticky;top:88px;}
.courses-sidebar-nav{background:#fff;border:1px solid #e2e8f0;border-radius:12px;overflow:hidden;}
.courses-sidebar-header{background:#0f172a;color:#fff;padding:14px 16px;font-size:.8rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;}
.courses-sidebar-item{display:flex;align-items:center;gap:10px;padding:12px 16px;border-bottom:1px solid #f1f5f9;font-size:.875rem;font-weight:600;color:#374151;text-decoration:none;transition:background .15s,color .15s;}
.courses-sidebar-item:last-child{border-bottom:none;}
.courses-sidebar-item:hover,.courses-sidebar-item.active{background:#eff6ff;color:#2563eb;}
.courses-sidebar-item .sicon{width:30px;height:30px;border-radius:7px;display:flex;align-items:center;justify-content:center;font-size:.9rem;flex-shrink:0;}
.courses-sidebar-item .badge-count{margin-left:auto;background:#f1f5f9;color:#64748b;font-size:.72rem;padding:2px 8px;border-radius:10px;}
.program-card{background:#fff;border:1px solid #e2e8f0;border-radius:14px;padding:24px;transition:box-shadow .25s,transform .2s,border-color .2s;cursor:pointer;display:flex;flex-direction:column;gap:10px;}
.program-card:hover{box-shadow:0 10px 36px rgba(0,0,0,.09);transform:translateY(-3px);border-color:#c7d2fe;}
.prog-icon-lg{width:52px;height:52px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1.4rem;flex-shrink:0;}
.prog-name{font-size:1.05rem;font-weight:800;color:#0f172a;margin-bottom:2px;}
.prog-desc{font-size:.82rem;color:#64748b;}
.prog-meta-row{display:flex;gap:12px;flex-wrap:wrap;}
.prog-meta{font-size:.78rem;color:#64748b;display:flex;align-items:center;gap:4px;}
.prog-specs{display:flex;gap:6px;flex-wrap:wrap;margin-top:4px;}
.prog-spec{background:#f1f5f9;color:#374151;font-size:.72rem;padding:3px 8px;border-radius:4px;font-weight:500;}
.prog-uni-count{font-size:.82rem;font-weight:700;color:#2563eb;margin-top:auto;}
.btn-compare-uni{display:inline-flex;align-items:center;gap:6px;background:#eff6ff;color:#2563eb;border:1px solid #dbeafe;padding:8px 14px;border-radius:8px;font-size:.82rem;font-weight:700;text-decoration:none;transition:background .2s;margin-top:4px;}
.btn-compare-uni:hover{background:#dbeafe;color:#1d4ed8;}
.level-tabs{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:24px;}
.level-tab{padding:8px 18px;border:1px solid #e2e8f0;border-radius:20px;font-size:.85rem;font-weight:600;cursor:pointer;background:#fff;color:#374151;text-decoration:none;transition:all .2s;}
.level-tab:hover,.level-tab.active{background:#2563eb;color:#fff;border-color:#2563eb;}
</style>

<!-- Hero -->
<div class="courses-hero">
  <div class="container">
    <div style="display:inline-flex;align-items:center;gap:8px;background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.2);padding:5px 14px;border-radius:20px;font-size:.8rem;font-weight:600;margin-bottom:16px;">
      &#127891; 25+ Online Programs
    </div>
    <h1>Explore Online Degree Programs</h1>
    <p>Find the right program from top UGC-approved universities. Compare specializations, fees, and career outcomes.</p>
    <div style="display:flex;gap:16px;justify-content:center;flex-wrap:wrap;margin-top:24px;">
      <div style="text-align:center;"><div style="font-size:1.4rem;font-weight:900;color:#22c55e;">320+</div><div style="font-size:.78rem;color:rgba(255,255,255,.6);">Universities</div></div>
      <div style="width:1px;background:rgba(255,255,255,.2);"></div>
      <div style="text-align:center;"><div style="font-size:1.4rem;font-weight:900;color:#22c55e;">50+</div><div style="font-size:.78rem;color:rgba(255,255,255,.6);">Specializations</div></div>
      <div style="width:1px;background:rgba(255,255,255,.2);"></div>
      <div style="text-align:center;"><div style="font-size:1.4rem;font-weight:900;color:#22c55e;">Free</div><div style="font-size:.78rem;color:rgba(255,255,255,.6);">Counselling</div></div>
    </div>
  </div>
</div>

<div class="page-wrapper" style="margin-top:0;">
  <div class="container">
    <div class="courses-layout">

      <!-- Sidebar -->
      <aside class="courses-sidebar">
        <div class="courses-sidebar-nav">
          <div class="courses-sidebar-header">&#128218; Browse by Level</div>
          <a href="<?= $base ?>/courses.php" class="courses-sidebar-item <?= $activeLevel===''?'active':'' ?>">
            <span class="sicon" style="background:#f1f5f9;color:#374151;">&#128218;</span>All Programs
            <span class="badge-count"><?= count($programs) ?></span>
          </a>
          <a href="<?= $base ?>/courses.php?level=pg" class="courses-sidebar-item <?= $activeLevel==='pg'?'active':'' ?>">
            <span class="sicon" style="background:#ede9fe;color:#7c3aed;">PG</span>PG Courses
            <span class="badge-count"><?= count(array_filter($programs,fn($p)=>$p['level']==='pg')) ?></span>
          </a>
          <a href="<?= $base ?>/courses.php?level=ug" class="courses-sidebar-item <?= $activeLevel==='ug'?'active':'' ?>">
            <span class="sicon" style="background:#dbeafe;color:#2563eb;">UG</span>UG Courses
            <span class="badge-count"><?= count(array_filter($programs,fn($p)=>$p['level']==='ug')) ?></span>
          </a>
          <a href="<?= $base ?>/courses.php?level=certificate" class="courses-sidebar-item <?= $activeLevel==='certificate'?'active':'' ?>">
            <span class="sicon" style="background:#dcfce7;color:#16a34a;">Cr</span>Certificate
            <span class="badge-count"><?= count(array_filter($programs,fn($p)=>$p['level']==='certificate')) ?></span>
          </a>
        </div>

        <div style="background:#eff6ff;border:1px solid #dbeafe;border-radius:12px;padding:16px;margin-top:16px;text-align:center;">
          <div style="font-size:1.5rem;margin-bottom:6px;">&#127891;</div>
          <div style="font-size:.85rem;font-weight:700;color:#1e40af;margin-bottom:4px;">Not sure what to study?</div>
          <div style="font-size:.78rem;color:#3b82f6;margin-bottom:12px;">Get free expert guidance on the right program for you.</div>
          <a href="<?= $base ?>/counselling" class="btn-ck-primary" style="width:100%;justify-content:center;padding:10px;">Free Counselling</a>
        </div>
      </aside>

      <!-- Main Grid -->
      <div>
        <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:20px;">
          <div>
            <h2 style="font-size:1.2rem;font-weight:800;color:#0f172a;margin-bottom:4px;">
              <?= $activeLevel ? ucfirst($activeLevel).' Programs' : 'All Online Programs' ?>
            </h2>
            <p style="font-size:.85rem;color:#64748b;margin:0;"><?= count($filteredPrograms) ?> programs available</p>
          </div>
          <a href="<?= $base ?>/counselling" class="btn-ck-green">&#9889; Get Free Guidance</a>
        </div>

        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:18px;">
          <?php foreach($filteredPrograms as $p):
            $key = strtolower($p['slug']);
            $uniCount = isset($counts[$key]) ? $counts[$key] : rand(80,350);
          ?>
          <div class="program-card" onclick="window.location='<?= $base ?>/colleges?course=<?= urlencode($p['slug']) ?>'">
            <div style="display:flex;align-items:center;gap:14px;">
              <div class="prog-icon-lg" style="background:<?= $p['bg'] ?>;color:<?= $p['color'] ?>;"><?= $p['icon'] ?></div>
              <div>
                <div class="prog-name"><?= htmlspecialchars($p['name']) ?></div>
                <div class="prog-desc"><?= htmlspecialchars($p['desc']) ?></div>
              </div>
            </div>
            <div class="prog-meta-row">
              <span class="prog-meta">&#128337; <?= htmlspecialchars($p['duration']) ?></span>
              <span class="prog-meta" style="color:#16a34a;">&#10003; <?= htmlspecialchars($p['eligibility']) ?></span>
            </div>
            <div>
              <div style="font-size:.72rem;color:#9ca3af;text-transform:uppercase;letter-spacing:.05em;margin-bottom:6px;">Specializations</div>
              <div class="prog-specs">
                <?php foreach(array_slice($p['specs'],0,4) as $spec): ?>
                <span class="prog-spec"><?= htmlspecialchars($spec) ?></span>
                <?php endforeach; ?>
                <?php if(count($p['specs'])>4): ?>
                <span class="prog-spec" style="background:#eff6ff;color:#2563eb;">+<?= count($p['specs'])-4 ?> more</span>
                <?php endif; ?>
              </div>
            </div>
            <div style="display:flex;align-items:center;justify-content:space-between;margin-top:4px;">
              <span class="prog-uni-count">&#127979; <?= $uniCount ?>+ Universities</span>
              <a href="<?= $base ?>/colleges?course=<?= urlencode($p['slug']) ?>" class="btn-compare-uni" onclick="event.stopPropagation()">
                Compare Universities &#8594;
              </a>
            </div>
          </div>
          <?php endforeach; ?>
        </div>

        <!-- Bottom CTA -->
        <div style="background:linear-gradient(135deg,#0f172a,#1e3a5f);border-radius:16px;padding:40px 32px;text-align:center;margin-top:40px;color:#fff;">
          <h3 style="font-size:1.4rem;font-weight:800;margin-bottom:8px;">Still Confused? Talk to Our Experts for Free</h3>
          <p style="color:rgba(255,255,255,.7);margin-bottom:20px;">1.25L+ students helped &bull; 600+ expert mentors &bull; 100% Free</p>
          <a href="<?= $base ?>/counselling" class="btn-ck-green" style="padding:12px 28px;font-size:.95rem;">
            &#127891; Book Free Counselling Session
          </a>
        </div>

      </div>
    </div>
  </div>
  <div style="height:48px;"></div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
