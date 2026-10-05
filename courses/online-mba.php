<?php
$pageTitle    = 'Online MBA Admission 2025 — Compare 138+ Universities | CollegeKampus';
$pageDesc     = 'Compare 138+ online MBA universities in India. Check fees, specializations, eligibility & apply free. UGC-approved programs with 15% exclusive discount.';
$pageKeywords = 'online MBA 2025, online MBA admission, online MBA universities India, best online MBA, online MBA fees, UGC approved online MBA';
$leadFormCourse = 'Online MBA';
require_once __DIR__ . '/../includes/header.php';
?>

<style>
/* MBA Program Page */
.mba-hero{background:linear-gradient(135deg,#1e3a8a 0%,#2563eb 60%,#312e81 100%);color:#fff;padding:48px 0 40px;}
.mba-hero h1{font-size:clamp(1.4rem,3.5vw,2.1rem);font-weight:900;margin:0 0 10px;line-height:1.2;}
.mba-badges{display:flex;flex-wrap:wrap;gap:8px;margin-bottom:20px;}
.mba-badge{background:rgba(255,255,255,.12);border:1px solid rgba(255,255,255,.22);color:#fff;font-size:.75rem;font-weight:700;padding:5px 12px;border-radius:20px;display:flex;align-items:center;gap:5px;}
.mba-univ-logos{display:grid;grid-template-columns:repeat(6,1fr);gap:8px;margin-top:20px;}
.mba-univ-chip{background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.2);border-radius:8px;padding:8px 4px;text-align:center;font-size:.7rem;font-weight:800;color:#fff;}
/* Content sections */
.mba-section{margin-bottom:36px;}
.mba-section h2{font-size:1.2rem;font-weight:800;color:#0f172a;margin:0 0 12px;padding-bottom:8px;border-bottom:2px solid #e2e8f0;}
.mba-section h3{font-size:1rem;font-weight:700;color:#1d4ed8;margin:16px 0 8px;}
.mba-section p{font-size:.88rem;color:#374151;line-height:1.7;}
.spec-chip{display:inline-flex;align-items:center;background:#eff6ff;border:1px solid #bfdbfe;color:#1d4ed8;border-radius:20px;padding:6px 14px;font-size:.78rem;font-weight:600;text-decoration:none;margin:4px;transition:background .15s;}
.spec-chip:hover{background:#dbeafe;color:#1d4ed8;}
/* College cards from API */
.mba-college-card{border:1px solid #e2e8f0;border-radius:12px;padding:16px;background:#fff;transition:box-shadow .2s;}
.mba-college-card:hover{box-shadow:0 6px 20px rgba(37,99,235,.1);}
.mba-college-logo{width:44px;height:44px;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:.78rem;font-weight:900;color:#fff;flex-shrink:0;}
/* Fees table */
.fees-table{width:100%;border-collapse:collapse;font-size:.84rem;}
.fees-table th{background:#f1f5f9;font-weight:700;padding:10px 12px;text-align:left;border:1px solid #e2e8f0;}
.fees-table td{padding:9px 12px;border:1px solid #e2e8f0;color:#374151;}
.fees-table tr:nth-child(even) td{background:#f8fafc;}
/* Comparison table */
.comp-table th{background:#1d4ed8;color:#fff;font-weight:700;padding:10px 12px;border:1px solid #1d4ed8;font-size:.82rem;}
.comp-table td{padding:9px 12px;border:1px solid #e2e8f0;font-size:.82rem;color:#374151;}
.comp-table td.pos{color:#16a34a;font-weight:600;}
.comp-table td.neg{color:#dc2626;font-weight:600;}
/* Accordion FAQ */
.mba-faq-item{border:1px solid #e2e8f0;border-radius:10px;margin-bottom:8px;overflow:hidden;}
.mba-faq-q{width:100%;background:#f8fafc;border:none;text-align:left;padding:14px 16px;font-size:.88rem;font-weight:700;color:#0f172a;cursor:pointer;display:flex;justify-content:space-between;align-items:center;gap:12px;transition:background .15s;}
.mba-faq-q:hover{background:#eff6ff;}
.mba-faq-q .faq-icon{flex-shrink:0;transition:transform .2s;font-size:.75rem;}
.mba-faq-a{display:none;padding:0 16px 14px;font-size:.84rem;color:#374151;line-height:1.7;}
.mba-faq-item.open .mba-faq-q{background:#eff6ff;color:#1d4ed8;}
.mba-faq-item.open .mba-faq-a{display:block;}
.mba-faq-item.open .faq-icon{transform:rotate(180deg);}
/* Mid-page lead strip */
.mba-mid-strip{background:#f0f9ff;border:1.5px solid #bae6fd;border-radius:14px;padding:22px 24px;margin:28px 0;}
.mba-mid-strip h5{font-size:.95rem;font-weight:800;color:#0f172a;margin:0 0 4px;}
.mba-mid-strip p{font-size:.8rem;color:#64748b;margin:0 0 12px;}
.mba-mid-form{display:flex;gap:8px;flex-wrap:wrap;}
.mba-mid-form input{flex:1;min-width:140px;border:1.5px solid #e2e8f0;border-radius:8px;padding:9px 12px;font-size:.83rem;}
.mba-mid-form button{background:#2563eb;color:#fff;border:none;border-radius:8px;padding:9px 18px;font-weight:700;font-size:.83rem;cursor:pointer;white-space:nowrap;transition:background .2s;}
.mba-mid-form button:hover{background:#1d4ed8;}
/* Sticky sidebar */
@media(min-width:992px){
  .mba-sticky-col{position:sticky;top:80px;}
}
#mbaCollegeGrid .loading-placeholder{text-align:center;color:#9ca3af;font-size:.85rem;padding:24px;}
</style>

<!-- Hero -->
<section class="mba-hero">
  <div class="container">
    <div class="row align-items-center">
      <div class="col-lg-8">
        <p style="font-size:.78rem;opacity:.7;margin:0 0 8px;">Home &rsaquo; Courses &rsaquo; Online MBA</p>
        <h1>Compare &amp; Apply from 138+ Online MBA Universities</h1>
        <div class="mba-badges mt-3">
          <span class="mba-badge"><i class="bi bi-tag-fill"></i> 15% Exclusive Discount</span>
          <span class="mba-badge"><i class="bi bi-headset"></i> Free Counselling</span>
          <span class="mba-badge"><i class="bi bi-shield-check-fill"></i> Lowest Price Guarantee</span>
          <span class="mba-badge"><i class="bi bi-patch-check-fill"></i> CollegeKampus Assured</span>
        </div>
        <div class="mba-univ-logos d-none d-sm-grid">
          <div class="mba-univ-chip">Manipal</div>
          <div class="mba-univ-chip">Amity</div>
          <div class="mba-univ-chip">LPU</div>
          <div class="mba-univ-chip">BITS</div>
          <div class="mba-univ-chip">Symbiosis</div>
          <div class="mba-univ-chip">IGNOU</div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Two-column layout -->
<div class="container py-5">
  <div class="row g-5">

    <!-- LEFT: Main content (8 cols) -->
    <div class="col-lg-8">

      <!-- 1. What is Online MBA? -->
      <div class="mba-section">
        <h2>What is Online MBA?</h2>
        <p>An Online MBA (Master of Business Administration) is a postgraduate management degree delivered entirely through an internet-based platform. It offers working professionals and fresh graduates the flexibility to study at their own pace without relocating or quitting their jobs. The curriculum mirrors that of a traditional MBA, covering subjects like Financial Management, Marketing Strategy, Human Resource Management, Operations, and Business Analytics.</p>
        <p>Online MBA programs in India are recognised by the University Grants Commission (UGC) and the Distance Education Bureau (DEB). Degrees earned from UGC-approved universities carry the same legal validity as on-campus degrees, making them acceptable for government jobs, promotions and higher studies abroad.</p>
        <p>With tuition fees typically ranging from Rs 40,000 to Rs 2,00,000 per year &mdash; a fraction of traditional MBA costs &mdash; the online format has democratised management education in India. Over 5 lakh students enrolled in online MBA programs in 2024, a figure expected to grow by 30% in 2025.</p>
      </div>

      <!-- 2. Specializations -->
      <div class="mba-section">
        <h2>Online MBA Specializations 2025</h2>
        <p>Choose from a wide range of specializations designed to align with industry demand:</p>
        <div>
          <?php $specs = ['Finance','Marketing','Human Resource Management','Operations Management','Information Technology','Business Analytics','International Business','Healthcare Management','Supply Chain Management','Entrepreneurship & Startups','Banking & Financial Services','Digital Marketing']; ?>
          <?php foreach($specs as $s): ?>
          <a href="<?= $base ?>/colleges?course=MBA&spec=<?= urlencode($s) ?>" class="spec-chip"><?= htmlspecialchars($s) ?></a>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- 3. Top Universities (loaded from API) -->
      <div class="mba-section">
        <h2>Top Online MBA Universities 2025</h2>
        <div class="row g-3" id="mbaCollegeGrid">
          <div class="col-12 loading-placeholder"><div class="spinner-border spinner-border-sm me-2"></div> Loading universities...</div>
        </div>
        <div class="text-center mt-3">
          <a href="<?= $base ?>/colleges?course=MBA" class="btn-ck-primary" style="border-radius:10px;padding:10px 24px;">
            View All 138+ MBA Universities <i class="bi bi-arrow-right ms-1"></i>
          </a>
        </div>
      </div>

      <!-- Mid-page lead strip -->
      <div class="mba-mid-strip">
        <h5>Still confused? Get free counselling &rarr;</h5>
        <p>Talk to an expert and find the best online MBA for your budget &amp; goals.</p>
        <form class="mba-mid-form" id="mbaMidForm" novalidate>
          <input type="hidden" name="source" value="mba_mid_strip">
          <input type="hidden" name="course_interest" value="Online MBA">
          <input type="text" name="student_name"  placeholder="Your Name"  required>
          <input type="tel"  name="student_phone" placeholder="+91 Phone"  required maxlength="10" pattern="[6-9][0-9]{9}">
          <button type="submit">Get Free Advice &rarr;</button>
        </form>
        <div id="mbaMidMsg" style="display:none;margin-top:8px;font-size:.82rem;font-weight:600;color:#16a34a;"></div>
      </div>

      <!-- 4. Eligibility -->
      <div class="mba-section">
        <h2>Online MBA Eligibility Criteria</h2>
        <ul style="font-size:.88rem;color:#374151;line-height:2;">
          <li>Bachelor's degree (any stream) from a UGC-recognised university</li>
          <li>Minimum 50% aggregate marks in graduation (45% for SC/ST/OBC in some universities)</li>
          <li>No entrance exam required at most universities; some may need CAT / MAT / GMAT scores</li>
          <li>Final-year graduation students can apply provisionally</li>
          <li>Work experience preferred but not mandatory at most online programs</li>
        </ul>
      </div>

      <!-- 5. Fees table -->
      <div class="mba-section">
        <h2>Online MBA Fees in India 2025</h2>
        <table class="fees-table">
          <thead>
            <tr><th>University Type</th><th>Annual Fees (Approx.)</th><th>Total Program Cost</th><th>EMI Available</th></tr>
          </thead>
          <tbody>
            <tr><td>State / Central Universities (IGNOU, etc.)</td><td>Rs 8,000 – Rs 20,000</td><td>Rs 16,000 – Rs 40,000</td><td>Yes</td></tr>
            <tr><td>Private Deemed Universities (Amity, Manipal)</td><td>Rs 60,000 – Rs 1,00,000</td><td>Rs 1.2L – Rs 2L</td><td>Yes</td></tr>
            <tr><td>Premium / IIM Online (IIM Indore, Calcutta)</td><td>Rs 1,50,000 – Rs 3,00,000</td><td>Rs 3L – Rs 6L</td><td>Yes</td></tr>
            <tr><td>International Collaboration Programs</td><td>Rs 2,00,000 – Rs 4,50,000</td><td>Rs 4L – Rs 9L</td><td>Yes</td></tr>
          </tbody>
        </table>
        <p style="font-size:.75rem;color:#9ca3af;margin-top:8px;">* Fees are indicative. Use CollegeKampus coupon codes to get an additional 5–15% off.</p>
      </div>

      <!-- 6. Comparison table -->
      <div class="mba-section">
        <h2>Online MBA vs Regular MBA &mdash; Key Differences</h2>
        <div style="overflow-x:auto;">
          <table class="fees-table comp-table">
            <thead>
              <tr><th>Parameter</th><th>Online MBA</th><th>Regular / Full-time MBA</th></tr>
            </thead>
            <tbody>
              <tr><td>Duration</td><td class="pos">2 Years (flexible)</td><td>2 Years (fixed schedule)</td></tr>
              <tr><td>Average Fees</td><td class="pos">Rs 60,000 – Rs 2,00,000</td><td class="neg">Rs 5L – Rs 25L</td></tr>
              <tr><td>Attendance</td><td class="pos">Online (no relocation)</td><td class="neg">Full-time campus presence</td></tr>
              <tr><td>Work while studying</td><td class="pos">Yes, fully compatible</td><td class="neg">Difficult</td></tr>
              <tr><td>UGC Validity</td><td class="pos">Equal validity</td><td class="pos">Equal validity</td></tr>
              <tr><td>Networking</td><td>Virtual &amp; webinars</td><td class="pos">In-person campus network</td></tr>
              <tr><td>Entrance Exam</td><td class="pos">Usually not required</td><td class="neg">CAT / GMAT / MAT</td></tr>
              <tr><td>Placements</td><td>University assistance</td><td class="pos">Dedicated placement cell</td></tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- 7. FAQs -->
      <div class="mba-section">
        <h2>Frequently Asked Questions — Online MBA</h2>
        <?php $faqs = [
          ['q'=>'Is an Online MBA from Indian universities valid?','a'=>'Yes. Online MBA degrees from UGC/DEB-approved universities are fully valid and legally equivalent to campus degrees for both private sector jobs and government employment (as per the UGC ODL Regulations 2020).'],
          ['q'=>'Which is the best online MBA university in India in 2025?','a'=>'Top choices include Manipal University Online, Amity University Online, LPU, Symbiosis Online, Jain University Online, and BITS Pilani WILP. The "best" depends on your budget, specialization, and career goals. Use our comparison tool to shortlist.'],
          ['q'=>'Can I do an Online MBA while working full-time?','a'=>'Absolutely. That is the primary advantage. Classes are pre-recorded and available 24/7. Live sessions (if any) are usually on weekends. Most students complete assignments in 5–8 hours per week.'],
          ['q'=>'What is the minimum marks required for Online MBA?','a'=>'Most universities require 50% aggregate in any bachelor\'s degree. Some, like IGNOU, accept 45% for certain categories. A few premium institutes may require entrance test scores.'],
          ['q'=>'How do online exams work in Online MBA programs?','a'=>'Universities use a mix of proctored online exams (via AI-powered software like Mettl or TalView), open-book assignments, project submissions, and occasional campus visits (2–4 days per semester) for some programs.'],
          ['q'=>'What is the salary after Online MBA in India?','a'=>'Starting salaries range from Rs 4 LPA (freshers) to Rs 15+ LPA (for experienced professionals). An online MBA from a premium institute can lead to significant hikes, especially in Finance, Analytics, and Marketing.'],
          ['q'=>'Is Online MBA accepted for government jobs in India?','a'=>'Yes, as long as the university is UGC-recognised. The Supreme Court has upheld the equivalence of distance and online degrees. Several PSUs and government departments now specifically accept UGC-approved online degrees.'],
          ['q'=>'What is the duration of an Online MBA?','a'=>'Typically 2 years (4 semesters). Some universities offer an accelerated 18-month format for experienced professionals. IGNOU offers a 3-year program with flexible semester options.'],
          ['q'=>'Are there any entrance exams for Online MBA?','a'=>'Most universities do not require entrance exams. A direct merit-based admission is offered. However, some universities may use their own aptitude tests or accept CAT/MAT/CMAT scores for scholarship purposes.'],
          ['q'=>'What is the CollegeKampus fee guarantee?','a'=>'If you find a lower price on any other platform for the same program at the same university, CollegeKampus will match or beat that price. Additionally, all applications through CollegeKampus include exclusive discount codes worth 5–15%.'],
        ]; ?>
        <?php foreach($faqs as $fi => $faq): ?>
        <div class="mba-faq-item" id="faq_<?= $fi ?>">
          <button class="mba-faq-q" onclick="ckToggleFaq(<?= $fi ?>)">
            <span><?= htmlspecialchars($faq['q']) ?></span>
            <i class="bi bi-chevron-down faq-icon"></i>
          </button>
          <div class="mba-faq-a"><?= htmlspecialchars($faq['a']) ?></div>
        </div>
        <?php endforeach; ?>
      </div>

      <!-- 8. Is Online MBA valid? -->
      <div class="mba-section">
        <h2>Is Online MBA Valid in India? — A Complete Guide</h2>
        <p>The question of validity is the most common concern among prospective students. The short answer: <strong>yes, completely</strong>. In 2020, the UGC released the Open and Distance Learning (ODL) Regulations which explicitly stated that online degrees from recognised universities carry equal legal standing as conventional on-campus degrees.</p>
        <p>The Ministry of Education and AICTE have further reinforced this by including online management degrees in their approved framework. NIRF rankings now have a separate category for online institutions. Several IIMs, IITs, and central universities offer online programs, which has further legitimised the sector.</p>
        <p>For employment purposes, recruiters at leading firms like TCS, Infosys, Deloitte, and KPMG have publicly stated they evaluate online MBA credentials on par with campus degrees when assessing skills and experience. The key factor is the accreditation status of the university, not the mode of delivery.</p>
      </div>

    </div><!-- /col-lg-8 -->

    <!-- RIGHT: Sticky lead form (4 cols) -->
    <div class="col-lg-4">
      <div class="mba-sticky-col">
        <?php include __DIR__ . '/../includes/lead-form.php'; ?>
        <div style="margin-top:16px;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:10px;padding:14px 16px;font-size:.78rem;color:#374151;">
          <strong style="color:#16a34a;font-size:.82rem;"><i class="bi bi-patch-check-fill me-1"></i>CollegeKampus Guarantee</strong>
          <ul style="margin:6px 0 0;padding-left:16px;line-height:1.8;">
            <li>Verified university listings</li>
            <li>Lowest fee guarantee</li>
            <li>100% free service</li>
            <li>No hidden charges</li>
          </ul>
        </div>
      </div>
    </div>

  </div><!-- /row -->
</div><!-- /container -->

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

<script>
// FAQ accordion
function ckToggleFaq(idx){
  var item = document.getElementById('faq_' + idx);
  if (!item) return;
  var wasOpen = item.classList.contains('open');
  document.querySelectorAll('.mba-faq-item').forEach(function(el){ el.classList.remove('open'); });
  if (!wasOpen) item.classList.add('open');
}

// Load universities from API
(function(){
  fetch(window.CK_BASE + '/api/colleges.php?course=MBA&limit=6&featured=1')
    .then(function(r){ return r.json(); })
    .then(function(data){
      var grid = document.getElementById('mbaCollegeGrid');
      var colleges = data.colleges || data.data || data || [];
      if (!Array.isArray(colleges) || colleges.length === 0){
        grid.innerHTML = '<div class="col-12"><p style="color:#9ca3af;font-size:.85rem;">University data loading... <a href="' + window.CK_BASE + '/colleges?course=MBA">Browse all MBA universities</a></p></div>';
        return;
      }
      var html = '';
      colleges.forEach(function(c){
        var name  = c.name || c.college_name || 'University';
        var city  = c.city || c.location || '';
        var fee   = c.min_fee || c.fee_min || '';
        var naac  = c.naac_grade || '';
        var init  = name.split(' ').slice(0,2).map(function(w){ return w[0]; }).join('').toUpperCase();
        var colors = ['#e11d48','#7c3aed','#0369a1','#d97706','#059669','#2563eb'];
        var col   = colors[Math.floor(Math.random() * colors.length)];
        html += '<div class="col-md-6">'
              + '<div class="mba-college-card d-flex gap-3 align-items-start">'
              + '<div class="mba-college-logo" style="background:' + col + ';">' + init + '</div>'
              + '<div style="flex:1;min-width:0;">'
              + '<div style="font-size:.84rem;font-weight:700;color:#0f172a;">' + name + '</div>'
              + '<div style="font-size:.75rem;color:#64748b;">' + city + (naac ? ' &bull; NAAC ' + naac : '') + '</div>'
              + (fee ? '<div style="font-size:.75rem;color:#2563eb;font-weight:600;margin-top:3px;">From ₹' + Number(fee).toLocaleString('en-IN') + '/yr</div>' : '')
              + '<a href="' + window.CK_BASE + '/college-detail.php?id=' + (c.id || '') + '" style="font-size:.72rem;color:#2563eb;font-weight:600;text-decoration:none;">View Details &rarr;</a>'
              + '</div></div></div>';
      });
      grid.innerHTML = html;
    })
    .catch(function(){
      document.getElementById('mbaCollegeGrid').innerHTML = '<div class="col-12"><p style="font-size:.85rem;color:#9ca3af;">Unable to load data. <a href="' + window.CK_BASE + '/colleges?course=MBA">Browse all MBA universities</a></p></div>';
    });
})();

// Mid-page form
document.getElementById('mbaMidForm').addEventListener('submit', function(e){
  e.preventDefault();
  if (!this.checkValidity()){ this.reportValidity(); return; }
  var btn = this.querySelector('button');
  btn.disabled = true; btn.textContent = 'Submitting...';
  var fd = new FormData(this);
  var ph = fd.get('student_phone') || '';
  if (ph && !ph.startsWith('+91')) fd.set('student_phone', '+91' + ph.replace(/^0/,''));
  fd.set('referrer_url', window.location.href);
  fetch(window.CK_BASE + '/api/leads.php', { method:'POST', body:fd })
    .then(function(r){ return r.json(); })
    .then(function(data){
      var msg = document.getElementById('mbaMidMsg');
      msg.textContent = data.success ? 'Great! Our expert will call you within 2 hours.' : (data.message || 'Error. Please try again.');
      msg.style.display = 'block';
      btn.disabled = false; btn.textContent = 'Get Free Advice →';
    })
    .catch(function(){ btn.disabled=false; btn.textContent='Get Free Advice →'; });
});
</script>
