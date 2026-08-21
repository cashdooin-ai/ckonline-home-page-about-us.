<?php
$pageTitle = 'Find Your Perfect Online University | CollegeKampus';
$pageDesc  = 'Answer 7 quick questions and get matched to the best online university for your goals, budget, and preferences.';
require_once __DIR__ . '/config/db.php';
include __DIR__ . '/includes/header.php';
?>

<style>
.suggest-hero{background:linear-gradient(135deg,#0f172a 0%,#1e3a5f 60%,#312e81 100%);padding:48px 0 56px;}
.suggest-hero h1{font-size:clamp(1.6rem,3.5vw,2.4rem);font-weight:900;color:#fff;margin-bottom:8px;}
.suggest-hero p{color:rgba(255,255,255,.72);font-size:1rem;margin:0;}
.wizard-wrap{max-width:700px;margin:-36px auto 60px;position:relative;z-index:10;}
.wizard-card{background:#fff;border-radius:20px;box-shadow:0 20px 60px rgba(0,0,0,.12);overflow:hidden;}
.advisor-strip{display:flex;align-items:center;gap:16px;padding:22px 28px;background:#eff6ff;border-bottom:1px solid #dbeafe;}
.advisor-avatar{width:56px;height:56px;border-radius:50%;background:linear-gradient(135deg,#2563eb,#7c3aed);display:flex;align-items:center;justify-content:center;font-size:1.6rem;flex-shrink:0;}
.advisor-bubble{background:#fff;border:1px solid #dbeafe;border-radius:12px 12px 12px 2px;padding:10px 16px;font-size:.88rem;color:#1e3a5f;font-weight:500;line-height:1.4;}
.advisor-name{font-size:.72rem;color:#64748b;margin-top:4px;font-weight:600;text-transform:uppercase;letter-spacing:.04em;}
.progress-bar-wrap{padding:20px 28px 0;}
.progress-label{display:flex;justify-content:space-between;font-size:.78rem;font-weight:600;color:#64748b;margin-bottom:6px;}
.progress{height:6px;border-radius:99px;background:#e2e8f0;}
.progress-fill{height:100%;border-radius:99px;background:linear-gradient(90deg,#2563eb,#7c3aed);transition:width .4s ease;}
.wizard-steps{padding:28px;}
.step{display:none;animation:fadeIn .3s ease;}
.step.active{display:block;}
@keyframes fadeIn{from{opacity:0;transform:translateY(8px);}to{opacity:1;transform:translateY(0);}}
.step-title{font-size:1.15rem;font-weight:800;color:#0f172a;margin-bottom:20px;text-align:center;}
.option-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(140px,1fr));gap:12px;margin-bottom:8px;}
.option-card{border:2px solid #e2e8f0;border-radius:12px;padding:18px 12px;text-align:center;cursor:pointer;transition:all .2s;background:#fff;position:relative;user-select:none;}
.option-card:hover{border-color:#93c5fd;background:#f0f9ff;}
.option-card.selected{border-color:#2563eb;background:#eff6ff;}
.option-card .check{position:absolute;top:8px;right:8px;width:20px;height:20px;border-radius:50%;background:#2563eb;color:#fff;display:none;align-items:center;justify-content:center;font-size:.7rem;}
.option-card.selected .check{display:flex;}
.option-card .oc-icon{font-size:2rem;margin-bottom:8px;display:block;}
.option-card .oc-label{font-size:.82rem;font-weight:700;color:#0f172a;line-height:1.3;}
.option-card .oc-sub{font-size:.7rem;color:#64748b;margin-top:3px;}
.multi-hint{text-align:center;font-size:.78rem;color:#64748b;margin-bottom:14px;}
.lead-form .cf-field{margin-bottom:14px;}
.lead-form label{display:block;font-size:.78rem;font-weight:600;color:#64748b;margin-bottom:5px;text-transform:uppercase;letter-spacing:.04em;}
.lead-form input,.lead-form select{width:100%;padding:11px 14px;border:1.5px solid #e2e8f0;border-radius:8px;font-size:.9rem;color:#0f172a;transition:border-color .2s;font-family:inherit;}
.lead-form input:focus,.lead-form select:focus{outline:none;border-color:#2563eb;}
.lead-form-row{display:grid;grid-template-columns:1fr 1fr;gap:14px;}
@media(max-width:480px){.lead-form-row{grid-template-columns:1fr;}}
.wizard-nav{display:flex;justify-content:space-between;align-items:center;padding:16px 28px 24px;border-top:1px solid #f1f5f9;}
.btn-wiz-back{background:none;border:2px solid #e2e8f0;color:#64748b;padding:10px 22px;border-radius:8px;font-weight:700;font-size:.88rem;cursor:pointer;transition:all .2s;}
.btn-wiz-back:hover{border-color:#93c5fd;color:#2563eb;}
.btn-wiz-next{background:#2563eb;color:#fff;border:none;padding:11px 28px;border-radius:8px;font-weight:700;font-size:.9rem;cursor:pointer;transition:background .2s;display:flex;align-items:center;gap:8px;}
.btn-wiz-next:hover{background:#1d4ed8;}
.btn-wiz-next:disabled{background:#93c5fd;cursor:not-allowed;}
.results-wrap{padding:0 28px 32px;}
.results-title{font-size:1.2rem;font-weight:800;color:#0f172a;margin-bottom:18px;text-align:center;}
.college-card{border:1px solid #e2e8f0;border-radius:14px;padding:18px;display:flex;gap:14px;align-items:flex-start;margin-bottom:12px;background:#fff;box-shadow:0 2px 8px rgba(0,0,0,.04);transition:box-shadow .2s;}
.college-card:hover{box-shadow:0 6px 20px rgba(37,99,235,.1);}
.cc-logo{width:56px;height:56px;border-radius:10px;background:#f1f5f9;display:flex;align-items:center;justify-content:center;font-size:1.4rem;flex-shrink:0;}
.cc-body{flex:1;}
.cc-name{font-size:.95rem;font-weight:800;color:#0f172a;margin-bottom:3px;}
.cc-meta{font-size:.78rem;color:#64748b;}
.cc-badges{display:flex;flex-wrap:wrap;gap:5px;margin-top:7px;}
.cc-badge{font-size:.68rem;padding:2px 8px;border-radius:4px;font-weight:700;}
.cc-badge.blue{background:#dbeafe;color:#1d4ed8;}
.cc-badge.green{background:#dcfce7;color:#15803d;}
.cc-btn{font-size:.78rem;font-weight:700;color:#2563eb;text-decoration:none;margin-top:10px;display:inline-flex;align-items:center;gap:4px;}
.cc-btn:hover{color:#1d4ed8;}
.results-cta{text-align:center;margin-top:20px;}
.spinner-wrap{text-align:center;padding:32px;}
</style>

<section class="suggest-hero">
  <div class="container text-center">
    <h1>Find Your Perfect Online University</h1>
    <p>Answer 7 quick questions &mdash; get matched to the right degree in 2 minutes.</p>
  </div>
</section>

<div class="container">
  <div class="wizard-wrap">
    <div class="wizard-card">

      <div class="advisor-strip">
        <div class="advisor-avatar">&#129489;</div>
        <div>
          <div class="advisor-bubble">Hi! I'm Arjun, your personal college advisor. Let me help you find the perfect university.</div>
          <div class="advisor-name">Arjun &middot; CollegeKampus Advisor</div>
        </div>
      </div>

      <div class="progress-bar-wrap">
        <div class="progress-label">
          <span id="stepLabel">Step 1 of 7</span>
          <span id="stepPct">14%</span>
        </div>
        <div class="progress">
          <div class="progress-fill" id="progressFill" style="width:14%;"></div>
        </div>
      </div>

      <div class="wizard-steps">

        <!-- Step 1 -->
        <div class="step active" id="step1">
          <div class="step-title">Which degree level are you looking for?</div>
          <div class="option-grid" id="grid1">
            <div class="option-card" data-val="PG">
              <span class="check"><i class="bi bi-check"></i></span>
              <span class="oc-icon">&#127891;</span>
              <div class="oc-label">PG</div><div class="oc-sub">Post Graduate</div>
            </div>
            <div class="option-card" data-val="UG">
              <span class="check"><i class="bi bi-check"></i></span>
              <span class="oc-icon">&#128218;</span>
              <div class="oc-label">UG</div><div class="oc-sub">Under Graduate</div>
            </div>
            <div class="option-card" data-val="Certificate">
              <span class="check"><i class="bi bi-check"></i></span>
              <span class="oc-icon">&#128220;</span>
              <div class="oc-label">Certificate</div><div class="oc-sub">/ Diploma</div>
            </div>
            <div class="option-card" data-val="Executive">
              <span class="check"><i class="bi bi-check"></i></span>
              <span class="oc-icon">&#128188;</span>
              <div class="oc-label">Executive</div><div class="oc-sub">Education</div>
            </div>
          </div>
        </div>

        <!-- Step 2 (populated by JS) -->
        <div class="step" id="step2">
          <div class="step-title">Which program interests you?</div>
          <div class="option-grid" id="grid2"></div>
        </div>

        <!-- Step 3 -->
        <div class="step" id="step3">
          <div class="step-title">What is your annual budget?</div>
          <div class="option-grid" id="grid3">
            <div class="option-card" data-val="under50k">
              <span class="check"><i class="bi bi-check"></i></span>
              <span class="oc-icon">&#128176;</span>
              <div class="oc-label">Under &#8377;50,000</div>
            </div>
            <div class="option-card" data-val="50k-1l">
              <span class="check"><i class="bi bi-check"></i></span>
              <span class="oc-icon">&#128181;</span>
              <div class="oc-label">&#8377;50K &ndash; &#8377;1L</div>
            </div>
            <div class="option-card" data-val="1l-2l">
              <span class="check"><i class="bi bi-check"></i></span>
              <span class="oc-icon">&#128179;</span>
              <div class="oc-label">&#8377;1L &ndash; &#8377;2L</div>
            </div>
            <div class="option-card" data-val="above2l">
              <span class="check"><i class="bi bi-check"></i></span>
              <span class="oc-icon">&#127968;</span>
              <div class="oc-label">Above &#8377;2L</div>
            </div>
            <div class="option-card" data-val="emi">
              <span class="check"><i class="bi bi-check"></i></span>
              <span class="oc-icon">&#128197;</span>
              <div class="oc-label">Open to EMI</div>
            </div>
          </div>
        </div>

        <!-- Step 4 -->
        <div class="step" id="step4">
          <div class="step-title">What is your work experience?</div>
          <div class="option-grid" id="grid4">
            <div class="option-card" data-val="fresher">
              <span class="check"><i class="bi bi-check"></i></span>
              <span class="oc-icon">&#127807;</span>
              <div class="oc-label">Fresher</div><div class="oc-sub">0 years</div>
            </div>
            <div class="option-card" data-val="1-3">
              <span class="check"><i class="bi bi-check"></i></span>
              <span class="oc-icon">&#128200;</span>
              <div class="oc-label">1 &ndash; 3 years</div>
            </div>
            <div class="option-card" data-val="3-7">
              <span class="check"><i class="bi bi-check"></i></span>
              <span class="oc-icon">&#128640;</span>
              <div class="oc-label">3 &ndash; 7 years</div>
            </div>
            <div class="option-card" data-val="7plus">
              <span class="check"><i class="bi bi-check"></i></span>
              <span class="oc-icon">&#127942;</span>
              <div class="oc-label">7+ years</div>
            </div>
          </div>
        </div>

        <!-- Step 5 -->
        <div class="step" id="step5">
          <div class="step-title">How do you prefer to study?</div>
          <div class="option-grid" id="grid5">
            <div class="option-card" data-val="online">
              <span class="check"><i class="bi bi-check"></i></span>
              <span class="oc-icon">&#128187;</span>
              <div class="oc-label">100% Online</div>
            </div>
            <div class="option-card" data-val="weekend">
              <span class="check"><i class="bi bi-check"></i></span>
              <span class="oc-icon">&#128197;</span>
              <div class="oc-label">Weekend Classes</div>
            </div>
            <div class="option-card" data-val="live">
              <span class="check"><i class="bi bi-check"></i></span>
              <span class="oc-icon">&#127909;</span>
              <div class="oc-label">Live Classes</div>
            </div>
            <div class="option-card" data-val="recorded">
              <span class="check"><i class="bi bi-check"></i></span>
              <span class="oc-icon">&#9654;</span>
              <div class="oc-label">Recorded + Self-paced</div>
            </div>
          </div>
        </div>

        <!-- Step 6 multi-select -->
        <div class="step" id="step6">
          <div class="step-title">What matters most to you?</div>
          <div class="multi-hint">Select up to 2 options</div>
          <div class="option-grid" id="grid6">
            <div class="option-card multi" data-val="placement">
              <span class="check"><i class="bi bi-check"></i></span>
              <span class="oc-icon">&#129309;</span>
              <div class="oc-label">Placement Support</div>
            </div>
            <div class="option-card multi" data-val="brand">
              <span class="check"><i class="bi bi-check"></i></span>
              <span class="oc-icon">&#11088;</span>
              <div class="oc-label">Brand Value</div>
            </div>
            <div class="option-card multi" data-val="low-fees">
              <span class="check"><i class="bi bi-check"></i></span>
              <span class="oc-icon">&#128184;</span>
              <div class="oc-label">Low Fees</div>
            </div>
            <div class="option-card multi" data-val="fast">
              <span class="check"><i class="bi bi-check"></i></span>
              <span class="oc-icon">&#9889;</span>
              <div class="oc-label">Fast Completion</div>
            </div>
            <div class="option-card multi" data-val="ugc">
              <span class="check"><i class="bi bi-check"></i></span>
              <span class="oc-icon">&#9989;</span>
              <div class="oc-label">UGC Approved</div>
            </div>
            <div class="option-card multi" data-val="emi">
              <span class="check"><i class="bi bi-check"></i></span>
              <span class="oc-icon">&#128198;</span>
              <div class="oc-label">EMI Facility</div>
            </div>
          </div>
        </div>

        <!-- Step 7 lead form -->
        <div class="step" id="step7">
          <div class="step-title">Almost there! Tell us about yourself</div>
          <form class="lead-form" id="leadForm" novalidate>
            <div class="lead-form-row">
              <div class="cf-field">
                <label for="lf_name">Full Name *</label>
                <input type="text" id="lf_name" name="name" placeholder="Your name" required>
              </div>
              <div class="cf-field">
                <label for="lf_phone">Phone *</label>
                <input type="tel" id="lf_phone" name="phone" placeholder="+91 9XXXXXXXXX" required>
              </div>
            </div>
            <div class="lead-form-row">
              <div class="cf-field">
                <label for="lf_email">Email *</label>
                <input type="email" id="lf_email" name="email" placeholder="you@email.com" required>
              </div>
              <div class="cf-field">
                <label for="lf_city">City</label>
                <input type="text" id="lf_city" name="city" placeholder="Your city">
              </div>
            </div>
            <div id="leadError" class="text-danger small mb-2" style="display:none;"></div>
          </form>
        </div>

        <!-- Results -->
        <div class="step" id="stepResults">
          <div class="results-title">&#127881; Your Top University Matches</div>
          <div id="resultsContainer">
            <div class="spinner-wrap">
              <div class="spinner-border text-primary" role="status"></div>
              <div class="mt-2 text-muted small">Finding your best matches&hellip;</div>
            </div>
          </div>
          <div class="results-cta">
            <a href="<?= $base ?>/colleges" class="btn btn-primary px-4">View All Universities &amp; Compare &#8594;</a>
          </div>
        </div>

      </div>

      <div class="wizard-nav" id="wizardNav">
        <button class="btn-wiz-back" id="btnBack" style="visibility:hidden;">&#8592; Back</button>
        <button class="btn-wiz-next" id="btnNext" disabled>Next <i class="bi bi-arrow-right"></i></button>
      </div>

    </div>
  </div>
</div>

<script>
(function(){
  var TOTAL=7, current=1, answers={};
  var programMap={
    PG:[{v:'MBA',i:'&#128202;',s:'Master of Business Admin'},{v:'MCA',i:'&#128421;',s:'Master of Computer Apps'},{v:'M.Com',i:'&#128200;',s:'Master of Commerce'},{v:'MA',i:'&#128214;',s:'Master of Arts'},{v:'M.Sc',i:'&#128300;',s:'Master of Science'},{v:'PGDM',i:'&#127919;',s:'PG Diploma Management'}],
    UG:[{v:'BBA',i:'&#128203;',s:'Bachelor of Business Admin'},{v:'BCA',i:'&#128187;',s:'Bachelor of Computer Apps'},{v:'B.Com',i:'&#128200;',s:'Bachelor of Commerce'},{v:'BA',i:'&#128218;',s:'Bachelor of Arts'},{v:'B.Sc',i:'&#9879;',s:'Bachelor of Science'},{v:'BHM',i:'&#127968;',s:'Hotel Management'}],
    Certificate:[{v:'Data Science',i:'&#128202;',s:''},{v:'Digital Marketing',i:'&#128241;',s:''},{v:'Cybersecurity',i:'&#128272;',s:''},{v:'Finance',i:'&#128185;',s:''},{v:'HR Management',i:'&#128101;',s:''},{v:'Python',i:'&#128013;',s:''}],
    Executive:[{v:'MBA Executive',i:'&#128084;',s:''},{v:'Leadership',i:'&#127775;',s:''},{v:'Strategy',i:'&#9820;',s:''},{v:'Operations',i:'&#9881;',s:''}]
  };

  function buildGrid2(){
    var level=answers[1], items=programMap[level]||[];
    var g=document.getElementById('grid2');
    g.innerHTML=items.map(function(it){
      return '<div class="option-card" data-val="'+it.v+'"><span class="check"><i class="bi bi-check"></i></span><span class="oc-icon">'+it.i+'</span><div class="oc-label">'+it.v+'</div>'+(it.s?'<div class="oc-sub">'+it.s+'</div>':'')+'</div>';
    }).join('');
    attachSingle(g);
  }

  function attachSingle(grid){
    grid.querySelectorAll('.option-card:not(.multi)').forEach(function(card){
      card.addEventListener('click',function(){
        grid.querySelectorAll('.option-card').forEach(function(c){c.classList.remove('selected');});
        card.classList.add('selected');
        document.getElementById('btnNext').disabled=false;
      });
    });
  }

  function attachMulti(grid,max){
    grid.querySelectorAll('.option-card.multi').forEach(function(card){
      card.addEventListener('click',function(){
        var sel=grid.querySelectorAll('.option-card.selected');
        if(card.classList.contains('selected')){card.classList.remove('selected');}
        else if(sel.length<max){card.classList.add('selected');}
        document.getElementById('btnNext').disabled=grid.querySelectorAll('.option-card.selected').length===0;
      });
    });
  }

  [1,3,4,5].forEach(function(n){var g=document.getElementById('grid'+n);if(g)attachSingle(g);});
  attachMulti(document.getElementById('grid6'),2);

  function getAns(step){
    if(step===6){var s=document.querySelectorAll('#grid6 .option-card.selected');return s.length>0?Array.from(s).map(function(c){return c.dataset.val;}):null;}
    if(step===7)return true;
    var g=document.getElementById('grid'+step);
    if(!g)return null;
    var s=g.querySelector('.option-card.selected');
    return s?s.dataset.val:null;
  }

  function showStep(n){
    document.querySelectorAll('.step').forEach(function(s){s.classList.remove('active');});
    var id=n===8?'stepResults':'step'+n;
    var el=document.getElementById(id);
    if(el)el.classList.add('active');
    var nav=document.getElementById('wizardNav');
    var btnBack=document.getElementById('btnBack');
    var btnNext=document.getElementById('btnNext');
    if(n===8){nav.style.display='none';loadResults();return;}
    nav.style.display='';
    btnBack.style.visibility=n>1?'visible':'hidden';
    if(n===7){btnNext.innerHTML='Find My Best University <i class="bi bi-arrow-right"></i>';btnNext.disabled=false;}
    else{btnNext.innerHTML='Next <i class="bi bi-arrow-right"></i>';btnNext.disabled=!getAns(n);}
    var pct=Math.round((n/TOTAL)*100);
    document.getElementById('progressFill').style.width=pct+'%';
    document.getElementById('stepLabel').textContent='Step '+n+' of '+TOTAL;
    document.getElementById('stepPct').textContent=pct+'%';
  }

  document.getElementById('btnNext').addEventListener('click',function(){
    if(current===7){submitLead();return;}
    var ans=getAns(current);
    if(!ans)return;
    answers[current]=ans;
    current++;
    if(current===2)buildGrid2();
    showStep(current);
  });

  document.getElementById('btnBack').addEventListener('click',function(){
    if(current>1){current--;showStep(current);}
  });

  function submitLead(){
    var name=document.getElementById('lf_name').value.trim();
    var phone=document.getElementById('lf_phone').value.trim();
    var email=document.getElementById('lf_email').value.trim();
    var city=document.getElementById('lf_city').value.trim();
    var errEl=document.getElementById('leadError');
    errEl.style.display='none';
    if(!name||!phone||!email){errEl.textContent='Name, phone and email are required.';errEl.style.display='block';return;}
    if(!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)){errEl.textContent='Please enter a valid email.';errEl.style.display='block';return;}
    answers[7]={name:name,phone:phone,email:email,city:city};
    var payload={name:name,phone:phone,email:email,city:city,source:'suggest_wizard',degree_level:answers[1],program:answers[2],budget:answers[3],experience:answers[4],study_mode:answers[5],priorities:(answers[6]||[]).join(',')};
    fetch('<?= $base ?>/api/leads.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(payload)}).catch(function(){});
    current=8;
    showStep(8);
  }

  function loadResults(){
    var program=answers[2]||answers[1]||'';
    var budget=answers[3]||'';
    fetch('<?= $base ?>/api/colleges.php?limit=5&course='+encodeURIComponent(program)+'&budget='+encodeURIComponent(budget))
      .then(function(r){return r.json();})
      .then(function(data){renderResults(Array.isArray(data)?data:(data.colleges||data.data||[]));})
      .catch(function(){renderResults([]);});
  }

  function renderResults(colleges){
    var c=document.getElementById('resultsContainer');
    if(!colleges||!colleges.length){c.innerHTML='<div class="text-center text-muted py-4">No exact matches found. <a href="<?= $base ?>/colleges" class="text-primary">Browse all universities &#8594;</a></div>';return;}
    c.innerHTML=colleges.map(function(col){
      var name=esc(col.name||col.college_name||'University');
      var loc=esc(col.location||col.state||'');
      var naac=col.naac_grade?' &middot; NAAC '+esc(col.naac_grade):'';
      var ugcBadge=(col.ugc_approved||col.is_ugc)?'<span class="cc-badge green">UGC Approved</span>':'';
      var feeBadge=col.fee_per_year?'<span class="cc-badge blue">&#8377;'+Number(col.fee_per_year).toLocaleString('en-IN')+'/yr</span>':'';
      var placeBadge=col.placement_rate?'<span class="cc-badge green">'+esc(col.placement_rate)+'% Placement</span>':'';
      var id=col.id||col.college_id||'';
      return '<div class="college-card"><div class="cc-logo">&#127979;</div><div class="cc-body"><div class="cc-name">'+name+'</div><div class="cc-meta">'+loc+naac+'</div><div class="cc-badges">'+ugcBadge+feeBadge+placeBadge+'</div><a href="<?= $base ?>/college-detail.php?id='+id+'" class="cc-btn">View Details <i class="bi bi-arrow-right"></i></a></div></div>';
    }).join('');
  }

  function esc(s){var d=document.createElement('div');d.textContent=String(s||'');return d.innerHTML;}
})();
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
