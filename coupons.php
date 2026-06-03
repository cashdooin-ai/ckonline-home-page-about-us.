<?php
$pageTitle    = 'University Coupons & Offers 2025 | CollegeKampus Online';
$pageDesc     = 'Grab exclusive university coupons, scholarship codes and admission discounts for online MBA, BCA, BBA and more. Save up to 30% on top universities.';
$pageKeywords = 'online MBA coupon, university discount code, online degree scholarship, college admission offer 2025, Manipal coupon, Amity discount';
require_once __DIR__ . '/includes/header.php';

// Static coupon data (12 cards)
$coupons = [
  ['univ'=>'Manipal University Online',   'init'=>'MU',  'color'=>'#e11d48','prog'=>'Online MBA',    'disc'=>'15% OFF', 'title'=>'Merit Scholarship on Online MBA',          'code'=>'MANIPAL15',  'validity'=>'30 Jun 2025', 'cat'=>'MBA'],
  ['univ'=>'Amity University Online',     'init'=>'AU',  'color'=>'#7c3aed','prog'=>'Online MBA',    'disc'=>'12% OFF', 'title'=>'Early Bird Discount — Online MBA 2025',     'code'=>'AMITY12',    'validity'=>'15 Jul 2025', 'cat'=>'MBA'],
  ['univ'=>'BITS Pilani WILP',            'init'=>'BP',  'color'=>'#0369a1','prog'=>'Online M.Tech', 'disc'=>'10% OFF', 'title'=>'Alumni & Merit Scholarship',               'code'=>'BITS10',     'validity'=>'31 May 2025', 'cat'=>'MCA'],
  ['univ'=>'Lovely Professional Univ.',   'init'=>'LP',  'color'=>'#d97706','prog'=>'Online BCA',    'disc'=>'20% OFF', 'title'=>'Flat 20% Off — Online BCA Admission',      'code'=>'LPU20BCA',   'validity'=>'30 Jun 2025', 'cat'=>'BCA'],
  ['univ'=>'IGNOU',                       'init'=>'IG',  'color'=>'#059669','prog'=>'Online MBA',    'disc'=>'FREE',    'title'=>'No Tuition Fee — Distance MBA (OBC/SC/ST)','code'=>'IGNOUSC',    'validity'=>'31 Jul 2025', 'cat'=>'MBA'],
  ['univ'=>'Chandigarh University Online','init'=>'CU',  'color'=>'#2563eb','prog'=>'Online BBA',    'disc'=>'18% OFF', 'title'=>'CollegeKampus Exclusive Scholarship',      'code'=>'CU18BBA',    'validity'=>'30 Jun 2025', 'cat'=>'BBA'],
  ['univ'=>'Symbiosis Online',            'init'=>'SU',  'color'=>'#be185d','prog'=>'Online MBA',    'disc'=>'8% OFF',  'title'=>'Women Empowerment Scholarship',             'code'=>'SYMWOMEN8',  'validity'=>'15 Jun 2025', 'cat'=>'MBA'],
  ['univ'=>'Jain University Online',      'init'=>'JU',  'color'=>'#0891b2','prog'=>'Online MCA',    'disc'=>'15% OFF', 'title'=>'Tech Future Scholarship — Online MCA',     'code'=>'JAIN15MCA',  'validity'=>'30 Jun 2025', 'cat'=>'MCA'],
  ['univ'=>'Sikkim Manipal Univ.',        'init'=>'SM',  'color'=>'#ea580c','prog'=>'Online BBA',    'disc'=>'10% OFF', 'title'=>'Management Studies Incentive Grant',        'code'=>'SMU10BBA',   'validity'=>'31 Jul 2025', 'cat'=>'BBA'],
  ['univ'=>'Amrita AHEAD',               'init'=>'AA',  'color'=>'#7c3aed','prog'=>'Online B.Com',  'disc'=>'12% OFF', 'title'=>'Commerce & Finance Scholarship 2025',      'code'=>'AMRITA12',   'validity'=>'30 Jun 2025', 'cat'=>'B.Com'],
  ['univ'=>'DY Patil Vidyapeeth',        'init'=>'DP',  'color'=>'#16a34a','prog'=>'Online MBA',    'disc'=>'25% OFF', 'title'=>'Flagship Silver Jubilee Scholarship',       'code'=>'DYP25MBA',   'validity'=>'31 May 2025', 'cat'=>'MBA'],
  ['univ'=>'Venkateshwara Online Univ.', 'init'=>'VU',  'color'=>'#dc2626','prog'=>'Online BCA',    'disc'=>'30% OFF', 'title'=>'Rural India Education Initiative',          'code'=>'VOU30BCA',   'validity'=>'15 Jul 2025', 'cat'=>'BCA'],
];
?>

<style>
/* ─── Coupons Page ─── */
.cp-hero{background:linear-gradient(135deg,#1e3a8a 0%,#2563eb 60%,#7c3aed 100%);color:#fff;padding:60px 0 48px;}
.cp-hero h1{font-size:clamp(1.6rem,4vw,2.4rem);font-weight:900;margin:0 0 10px;}
.cp-hero p{font-size:1rem;opacity:.88;max-width:540px;}
.cp-stats-bar{background:#0f172a;color:#fff;padding:18px 0;}
.cp-stat{text-align:center;}
.cp-stat .num{font-size:1.6rem;font-weight:900;color:#22c55e;}
.cp-stat .lbl{font-size:.75rem;color:rgba(255,255,255,.6);font-weight:500;}
.cp-sidebar .card{border:1px solid #e2e8f0;border-radius:12px;padding:18px;}
.cp-sidebar h6{font-size:.78rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#9ca3af;margin-bottom:12px;}
.cp-filter-btn{display:block;width:100%;text-align:left;background:none;border:1.5px solid #e2e8f0;border-radius:8px;padding:7px 12px;font-size:.83rem;font-weight:600;color:#374151;margin-bottom:6px;cursor:pointer;transition:background .15s,border-color .15s;}
.cp-filter-btn:hover,.cp-filter-btn.active{background:#eff6ff;border-color:#2563eb;color:#2563eb;}
.cp-coupon-card{border:1px solid #e2e8f0;border-radius:14px;overflow:hidden;background:#fff;box-shadow:0 2px 12px rgba(0,0,0,.05);transition:box-shadow .2s,transform .2s;}
.cp-coupon-card:hover{box-shadow:0 8px 30px rgba(37,99,235,.12);transform:translateY(-3px);}
.cp-card-top{padding:16px 16px 12px;display:flex;align-items:flex-start;gap:12px;}
.cp-univ-logo{width:48px;height:48px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:.85rem;font-weight:900;color:#fff;flex-shrink:0;}
.cp-card-meta{flex:1;min-width:0;}
.cp-card-meta .univ-name{font-size:.82rem;font-weight:700;color:#0f172a;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.cp-card-meta .prog-name{font-size:.75rem;color:#64748b;margin-top:1px;}
.cp-disc-badge{background:#fef3c7;color:#d97706;font-size:.8rem;font-weight:800;padding:4px 10px;border-radius:20px;white-space:nowrap;flex-shrink:0;}
.cp-disc-badge.free{background:#dcfce7;color:#16a34a;}
.cp-card-body{padding:0 16px 16px;}
.cp-offer-title{font-size:.85rem;font-weight:700;color:#0f172a;margin-bottom:10px;line-height:1.3;}
.cp-code-wrap{display:flex;align-items:center;gap:8px;margin-bottom:10px;}
.cp-code-box{flex:1;background:#f8fafc;border:1.5px dashed #cbd5e1;border-radius:8px;padding:7px 12px;font-size:.82rem;font-weight:700;font-family:monospace;color:#1d4ed8;letter-spacing:.08em;}
.btn-reveal{background:none;border:1.5px solid #2563eb;border-radius:8px;padding:6px 12px;font-size:.78rem;font-weight:700;color:#2563eb;cursor:pointer;white-space:nowrap;transition:background .15s;}
.btn-reveal:hover,.btn-reveal.copied{background:#2563eb;color:#fff;}
.cp-validity{font-size:.72rem;color:#9ca3af;margin-bottom:12px;}
.cp-validity i{color:#f59e0b;}
.btn-apply-enroll{display:block;width:100%;background:linear-gradient(90deg,#2563eb,#1d4ed8);color:#fff;border:none;border-radius:10px;padding:10px;font-size:.85rem;font-weight:700;text-align:center;text-decoration:none;transition:opacity .2s;}
.btn-apply-enroll:hover{opacity:.9;color:#fff;}
/* Lead strip bottom */
.cp-lead-strip{background:linear-gradient(90deg,#1d4ed8,#2563eb);color:#fff;padding:28px 0;}
.cp-lead-strip h4{font-size:1.15rem;font-weight:800;margin:0 0 4px;}
.cp-lead-strip p{font-size:.85rem;opacity:.85;margin:0;}
.cp-strip-form{display:flex;gap:10px;flex-wrap:wrap;}
.cp-strip-form input{flex:1;min-width:160px;border:none;border-radius:8px;padding:10px 14px;font-size:.88rem;}
.cp-strip-form button{background:#22c55e;color:#fff;border:none;border-radius:8px;padding:10px 22px;font-weight:700;font-size:.88rem;cursor:pointer;white-space:nowrap;transition:background .2s;}
.cp-strip-form button:hover{background:#16a34a;}
</style>

<!-- Hero -->
<section class="cp-hero">
  <div class="container">
    <div class="row align-items-center">
      <div class="col-lg-7">
        <div class="d-flex align-items-center gap-2 mb-3">
          <span style="background:rgba(255,255,255,.15);border-radius:20px;padding:4px 14px;font-size:.78rem;font-weight:600;">&#127881; Exclusive Offers</span>
        </div>
        <h1>University Coupons &amp; Scholarships 2025</h1>
        <p class="mb-4">Save big on your online degree. Verified coupon codes from top UGC-approved universities — updated weekly.</p>
        <a href="#coupon-grid" class="btn-ck-primary" style="padding:12px 28px;font-size:.95rem;border-radius:10px;">
          &#127883; Show All Coupons
        </a>
      </div>
      <div class="col-lg-5 d-none d-lg-flex justify-content-end">
        <div style="background:rgba(255,255,255,.1);border-radius:18px;padding:24px 28px;text-align:center;">
          <div style="font-size:3rem;margin-bottom:8px;">&#127881;</div>
          <div style="font-size:1.4rem;font-weight:900;">Up to 30% OFF</div>
          <div style="font-size:.85rem;opacity:.8;">on top universities</div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Stats bar -->
<div class="cp-stats-bar">
  <div class="container">
    <div class="row justify-content-center text-center g-0">
      <div class="col-4 col-md-3 cp-stat">
        <div class="num">48+</div>
        <div class="lbl">Active Coupons</div>
      </div>
      <div class="col-4 col-md-3 cp-stat">
        <div class="num">22</div>
        <div class="lbl">Universities with Offers</div>
      </div>
      <div class="col-4 col-md-3 cp-stat">
        <div class="num">14%</div>
        <div class="lbl">Average Discount</div>
      </div>
    </div>
  </div>
</div>

<!-- Main grid -->
<section class="py-5" id="coupon-grid">
  <div class="container">
    <div class="row g-4">

      <!-- Sidebar filters -->
      <div class="col-lg-3 cp-sidebar">
        <div class="card mb-3">
          <h6>Filter by Program</h6>
          <button class="cp-filter-btn active" data-filter="all">All Programs</button>
          <?php foreach(['MBA','BCA','BBA','MCA','B.Com'] as $p): ?>
          <button class="cp-filter-btn" data-filter="<?= $p ?>"><?= $p ?></button>
          <?php endforeach; ?>
        </div>
        <div class="card">
          <h6>Filter by Discount</h6>
          <button class="cp-filter-btn active" data-disc="all">Any Discount</button>
          <button class="cp-filter-btn" data-disc="low">Up to 10%</button>
          <button class="cp-filter-btn" data-disc="mid">11% – 20%</button>
          <button class="cp-filter-btn" data-disc="high">21% &amp; above</button>
        </div>
      </div>

      <!-- Coupon cards -->
      <div class="col-lg-9">
        <div class="row g-3" id="couponGrid">
          <?php foreach ($coupons as $i => $c):
            $isFree = $c['disc'] === 'FREE';
          ?>
          <div class="col-md-6 coupon-item" data-cat="<?= $c['cat'] ?>" data-disc-pct="<?= $isFree ? 100 : (int)$c['disc'] ?>">
            <div class="cp-coupon-card">
              <div class="cp-card-top">
                <div class="cp-univ-logo" style="background:<?= $c['color'] ?>;"><?= $c['init'] ?></div>
                <div class="cp-card-meta">
                  <div class="univ-name" title="<?= htmlspecialchars($c['univ']) ?>"><?= htmlspecialchars($c['univ']) ?></div>
                  <div class="prog-name"><?= htmlspecialchars($c['prog']) ?></div>
                </div>
                <span class="cp-disc-badge <?= $isFree?'free':'' ?>"><?= $c['disc'] ?></span>
              </div>
              <div class="cp-card-body">
                <div class="cp-offer-title"><?= htmlspecialchars($c['title']) ?></div>
                <div class="cp-code-wrap">
                  <div class="cp-code-box" id="code_<?= $i ?>"><?= htmlspecialchars($c['code']) ?></div>
                  <button class="btn-reveal" onclick="ckCopyCode('<?= $c['code'] ?>', this)">Copy</button>
                </div>
                <div class="cp-validity">
                  <i class="bi bi-clock"></i> Valid till <?= htmlspecialchars($c['validity']) ?>
                </div>
                <a href="<?= $base ?>/counselling.php?course=<?= urlencode($c['prog']) ?>&coupon=<?= urlencode($c['code']) ?>"
                   class="btn-apply-enroll">Apply &amp; Enroll &#8594;</a>
              </div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>

    </div>
  </div>
</section>

<!-- Bottom lead strip -->
<div class="cp-lead-strip">
  <div class="container">
    <div class="row align-items-center g-3">
      <div class="col-md-5">
        <h4>&#127881; Get additional 5% off + Free Counselling</h4>
        <p>Our experts will find the best coupon + match you to the right university.</p>
      </div>
      <div class="col-md-7">
        <form class="cp-strip-form" id="cpStripForm" novalidate>
          <input type="hidden" name="source" value="coupon_strip">
          <input type="text"  name="student_name"  placeholder="Your Name"  required>
          <input type="tel"   name="student_phone" placeholder="+91 Phone"  required maxlength="13">
          <button type="submit">Get Extra 5% Off &#8594;</button>
        </form>
        <div id="cpStripMsg" style="display:none;margin-top:8px;font-size:.85rem;font-weight:600;color:#86efac;"></div>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

<script>
// Copy code to clipboard
function ckCopyCode(code, btn){
  navigator.clipboard.writeText(code).then(function(){
    btn.textContent = 'Copied!';
    btn.classList.add('copied');
    setTimeout(function(){ btn.textContent='Copy'; btn.classList.remove('copied'); }, 2500);
  });
}

// Filter by program
document.querySelectorAll('.cp-filter-btn[data-filter]').forEach(function(btn){
  btn.addEventListener('click', function(){
    document.querySelectorAll('.cp-filter-btn[data-filter]').forEach(function(b){ b.classList.remove('active'); });
    this.classList.add('active');
    var f = this.dataset.filter;
    document.querySelectorAll('.coupon-item').forEach(function(item){
      item.style.display = (f === 'all' || item.dataset.cat === f) ? '' : 'none';
    });
  });
});

// Filter by discount
document.querySelectorAll('.cp-filter-btn[data-disc]').forEach(function(btn){
  btn.addEventListener('click', function(){
    document.querySelectorAll('.cp-filter-btn[data-disc]').forEach(function(b){ b.classList.remove('active'); });
    this.classList.add('active');
    var d = this.dataset.disc;
    document.querySelectorAll('.coupon-item').forEach(function(item){
      var pct = parseInt(item.dataset.discPct, 10);
      var show = d === 'all' ||
                 (d === 'low'  && pct <= 10) ||
                 (d === 'mid'  && pct >= 11 && pct <= 20) ||
                 (d === 'high' && pct >= 21);
      item.style.display = show ? '' : 'none';
    });
  });
});

// Strip form
document.getElementById('cpStripForm').addEventListener('submit', function(e){
  e.preventDefault();
  if (!this.checkValidity()){ this.reportValidity(); return; }
  var btn = this.querySelector('button');
  btn.disabled = true; btn.textContent = 'Submitting...';
  var fd = new FormData(this);
  fd.set('referrer_url', window.location.href);
  fetch(window.CK_BASE + '/api/leads.php', { method:'POST', body:fd })
    .then(function(r){ return r.json(); })
    .then(function(data){
      var msg = document.getElementById('cpStripMsg');
      msg.textContent = data.success ? 'Thank you! Our counsellor will reach out shortly.' : (data.message || 'Error. Please try again.');
      msg.style.display = 'block';
      btn.disabled = false; btn.textContent = 'Get Extra 5% Off →';
    })
    .catch(function(){ btn.disabled=false; btn.textContent='Get Extra 5% Off →'; });
});
</script>
