<?php
$pageTitle = 'Online Degree ROI Calculator | Is It Worth It?';
$pageDesc  = 'Calculate the return on investment of your online degree. See payback period, 10-year earnings gain and break-even chart instantly.';
require_once dirname(__DIR__) . '/config/db.php';
include dirname(__DIR__) . '/includes/header.php';
?>

<style>
.roi-hero{background:linear-gradient(135deg,#0f172a 0%,#1e3a5f 60%,#0d4f2e 100%);padding:48px 0 56px;color:#fff;text-align:center;}
.roi-hero h1{font-size:clamp(1.6rem,3.5vw,2.4rem);font-weight:900;margin-bottom:8px;}
.roi-hero p{color:rgba(255,255,255,.72);font-size:1rem;margin:0;}
.roi-layout{display:grid;grid-template-columns:1fr 1fr;gap:32px;padding:48px 0;}
@media(max-width:860px){.roi-layout{grid-template-columns:1fr;}}
.roi-panel{background:#fff;border:1px solid #e2e8f0;border-radius:16px;padding:28px;box-shadow:0 4px 20px rgba(0,0,0,.06);}
.roi-panel h2{font-size:1rem;font-weight:800;color:#0f172a;margin-bottom:22px;text-transform:uppercase;letter-spacing:.05em;}
.inp-group{margin-bottom:22px;}
.inp-group label{display:block;font-size:.78rem;font-weight:700;color:#64748b;margin-bottom:8px;text-transform:uppercase;letter-spacing:.04em;}
.inp-row{display:flex;align-items:center;gap:10px;}
.inp-row input[type=range]{flex:1;accent-color:#2563eb;}
.inp-row input[type=number]{width:100px;padding:8px 10px;border:1.5px solid #e2e8f0;border-radius:8px;font-size:.88rem;color:#0f172a;font-family:inherit;}
.inp-row input[type=number]:focus{outline:none;border-color:#2563eb;}
.inp-group select{width:100%;padding:10px 12px;border:1.5px solid #e2e8f0;border-radius:8px;font-size:.88rem;color:#0f172a;font-family:inherit;}
.inp-group select:focus{outline:none;border-color:#2563eb;}
.toggle-wrap{display:flex;align-items:center;gap:12px;}
.toggle{position:relative;display:inline-block;width:44px;height:24px;}
.toggle input{opacity:0;width:0;height:0;}
.slider-tog{position:absolute;cursor:pointer;top:0;left:0;right:0;bottom:0;background:#cbd5e1;transition:.3s;border-radius:24px;}
.slider-tog:before{position:absolute;content:"";height:18px;width:18px;left:3px;bottom:3px;background:white;transition:.3s;border-radius:50%;}
.toggle input:checked + .slider-tog{background:#22c55e;}
.toggle input:checked + .slider-tog:before{transform:translateX(20px);}
.toggle-label{font-size:.88rem;font-weight:600;color:#0f172a;}

/* Results */
.result-row{display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:22px;}
.result-box{border:1px solid #e2e8f0;border-radius:12px;padding:18px;text-align:center;}
.result-box .rb-label{font-size:.72rem;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.04em;margin-bottom:6px;}
.result-box .rb-val{font-size:1.6rem;font-weight:900;line-height:1;}
.result-box .rb-sub{font-size:.72rem;color:#64748b;margin-top:4px;}
.rb-green .rb-val{color:#22c55e;}
.rb-blue  .rb-val{color:#2563eb;}
.rb-purple .rb-val{color:#7c3aed;}
.rb-orange .rb-val{color:#f59e0b;}
.verdict-badge{display:inline-flex;align-items:center;gap:8px;padding:10px 20px;border-radius:99px;font-size:.9rem;font-weight:800;margin-bottom:22px;}
.verdict-excellent{background:#dcfce7;color:#15803d;}
.verdict-good{background:#dbeafe;color:#1d4ed8;}
.verdict-moderate{background:#fef3c7;color:#92400e;}
.chart-wrap{margin-top:4px;}
.chart-wrap h3{font-size:.8rem;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.04em;margin-bottom:10px;}
svg.break-chart{width:100%;border-radius:8px;background:#f8fafc;overflow:visible;}

/* Featured colleges bottom */
.feat-section{padding:0 0 56px;}
.feat-section h2{font-size:1.2rem;font-weight:800;color:#0f172a;margin-bottom:20px;}
.feat-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:16px;}
.feat-card{border:1px solid #e2e8f0;border-radius:14px;padding:20px;background:#fff;box-shadow:0 2px 8px rgba(0,0,0,.04);}
.feat-card .fc-logo{font-size:2rem;margin-bottom:10px;}
.feat-card .fc-name{font-size:.9rem;font-weight:800;color:#0f172a;margin-bottom:4px;}
.feat-card .fc-meta{font-size:.75rem;color:#64748b;margin-bottom:10px;}
.feat-card a.fc-link{font-size:.78rem;font-weight:700;color:#2563eb;text-decoration:none;}
.feat-cta{margin-top:24px;text-align:center;}
</style>

<section class="roi-hero">
  <div class="container">
    <h1>&#128200; Online Degree ROI Calculator</h1>
    <p>Is your online degree worth the investment? Find out in seconds.</p>
  </div>
</section>

<div class="container">
  <div class="roi-layout">

    <!-- LEFT: Inputs -->
    <div class="roi-panel">
      <h2>&#128204; Your Numbers</h2>

      <div class="inp-group">
        <label>Current Annual Salary (&#8377;)</label>
        <div class="inp-row">
          <input type="range" id="currentSalary" min="0" max="2000000" step="50000" value="400000">
          <input type="number" id="currentSalaryNum" min="0" max="2000000" step="50000" value="400000">
        </div>
      </div>

      <div class="inp-group">
        <label>Expected Salary After Degree (&#8377;)</label>
        <div class="inp-row">
          <input type="range" id="expectedSalary" min="200000" max="5000000" step="50000" value="700000">
          <input type="number" id="expectedSalaryNum" min="200000" max="5000000" step="50000" value="700000">
        </div>
      </div>

      <div class="inp-group">
        <label>Course Fee (&#8377;)</label>
        <div class="inp-row">
          <input type="range" id="courseFee" min="10000" max="500000" step="5000" value="80000">
          <input type="number" id="courseFeeNum" min="10000" max="500000" step="5000" value="80000">
        </div>
      </div>

      <div class="inp-group">
        <label>Course Duration</label>
        <select id="courseDuration">
          <option value="1">1 Year</option>
          <option value="2" selected>2 Years</option>
          <option value="3">3 Years</option>
        </select>
      </div>

      <div class="inp-group">
        <label>Working while studying?</label>
        <div class="toggle-wrap">
          <label class="toggle">
            <input type="checkbox" id="workingToggle" checked>
            <span class="slider-tog"></span>
          </label>
          <span class="toggle-label" id="workingLabel">Yes &mdash; earning while studying</span>
        </div>
      </div>
    </div>

    <!-- RIGHT: Results -->
    <div class="roi-panel">
      <h2>&#127775; Your Results</h2>

      <div id="verdictWrap" style="text-align:center;">
        <span class="verdict-badge verdict-good" id="verdictBadge">Good Investment &#9989;</span>
      </div>

      <div class="result-row">
        <div class="result-box rb-green">
          <div class="rb-label">ROI</div>
          <div class="rb-val" id="roiPct">—%</div>
          <div class="rb-sub">return on investment</div>
        </div>
        <div class="result-box rb-blue">
          <div class="rb-label">Payback Period</div>
          <div class="rb-val" id="paybackMonths">— mo</div>
          <div class="rb-sub">to recover investment</div>
        </div>
      </div>

      <div class="result-row">
        <div class="result-box rb-purple">
          <div class="rb-label">10-Year Gain</div>
          <div class="rb-val" id="tenYearGain">—</div>
          <div class="rb-sub">extra earnings</div>
        </div>
        <div class="result-box rb-orange">
          <div class="rb-label">Total Cost</div>
          <div class="rb-val" id="totalCost">—</div>
          <div class="rb-sub">fee + opportunity</div>
        </div>
      </div>

      <div class="chart-wrap">
        <h3>Break-Even Chart (cumulative savings vs cost)</h3>
        <svg class="break-chart" id="breakChart" viewBox="0 0 460 180" xmlns="http://www.w3.org/2000/svg"></svg>
      </div>
    </div>

  </div>

  <!-- Featured colleges -->
  <div class="feat-section">
    <h2>&#127979; Top Universities for Your Program</h2>
    <div class="feat-grid" id="featGrid">
      <div class="text-muted small">Loading universities&hellip;</div>
    </div>
    <div class="feat-cta">
      <a href="<?= $base ?>/colleges" class="btn btn-primary px-5">View All Universities &amp; Compare &#8594;</a>
    </div>
  </div>
</div>

<script>
(function(){
  // Sync range <-> number inputs
  function syncPair(rangeId, numId){
    var r=document.getElementById(rangeId), n=document.getElementById(numId);
    r.addEventListener('input',function(){n.value=r.value;calc();});
    n.addEventListener('input',function(){r.value=n.value;calc();});
  }
  syncPair('currentSalary','currentSalaryNum');
  syncPair('expectedSalary','expectedSalaryNum');
  syncPair('courseFee','courseFeeNum');

  document.getElementById('courseDuration').addEventListener('change',calc);
  document.getElementById('workingToggle').addEventListener('change',function(){
    document.getElementById('workingLabel').textContent=this.checked?'Yes — earning while studying':'No — not working during study';
    calc();
  });

  function fmt(n){
    if(n>=10000000)return (n/10000000).toFixed(1)+'Cr';
    if(n>=100000)return (n/100000).toFixed(1)+'L';
    if(n>=1000)return (n/1000).toFixed(0)+'K';
    return String(Math.round(n));
  }

  function calc(){
    var cur=parseFloat(document.getElementById('currentSalaryNum').value)||0;
    var exp=parseFloat(document.getElementById('expectedSalaryNum').value)||0;
    var fee=parseFloat(document.getElementById('courseFeeNum').value)||1;
    var dur=parseInt(document.getElementById('courseDuration').value)||2;
    var working=document.getElementById('workingToggle').checked;

    var annualGain=exp-cur;
    var oppLoss=working?0:(cur*dur);
    var totalCost=fee+oppLoss;
    var roi=((annualGain*dur*10-totalCost)/totalCost*100);
    var monthlyGain=annualGain/12;
    var payback=monthlyGain>0?Math.ceil(totalCost/monthlyGain):9999;
    var tenYearGain=annualGain*10;

    document.getElementById('roiPct').textContent=roi.toFixed(0)+'%';
    document.getElementById('paybackMonths').textContent=payback<9999?payback+' mo':'N/A';
    document.getElementById('tenYearGain').textContent='&#8377;'+fmt(tenYearGain);
    document.getElementById('totalCost').textContent='&#8377;'+fmt(totalCost);

    // Verdict
    var vb=document.getElementById('verdictBadge');
    if(roi>=150){vb.className='verdict-badge verdict-excellent';vb.innerHTML='Excellent Investment &#128640;';}
    else if(roi>=60){vb.className='verdict-badge verdict-good';vb.innerHTML='Good Investment &#9989;';}
    else{vb.className='verdict-badge verdict-moderate';vb.innerHTML='Moderate &#9888;&#65039;';}

    drawChart(totalCost,annualGain);
  }

  function drawChart(totalCost,annualGain){
    var svg=document.getElementById('breakChart');
    var W=460,H=180,pad=40;
    var years=10;
    var pts=[];
    for(var y=0;y<=years;y++){
      var savings=annualGain*y-totalCost;
      pts.push({x:y,y:savings});
    }
    var minY=Math.min.apply(null,pts.map(function(p){return p.y;}))||0;
    var maxY=Math.max.apply(null,pts.map(function(p){return p.y;}))||1;
    function sx(x){return pad+(x/years)*(W-pad*2);}
    function sy(y){return H-pad-((y-minY)/(maxY-minY||1))*(H-pad*2);}

    var linePts=pts.map(function(p){return sx(p.x)+','+sy(p.y);}).join(' ');
    var zeroY=sy(0);
    var costLine='M '+sx(0)+' '+sy(-totalCost)+' L '+sx(years)+' '+sy(-totalCost);

    svg.innerHTML=
      '<defs><linearGradient id="gg" x1="0" y1="0" x2="0" y2="1"><stop offset="0%" stop-color="#22c55e" stop-opacity=".3"/><stop offset="100%" stop-color="#22c55e" stop-opacity="0"/></linearGradient></defs>'+
      '<line x1="'+pad+'" y1="'+(H-pad)+'" x2="'+(W-pad)+'" y2="'+(H-pad)+'" stroke="#e2e8f0" stroke-width="1"/>'+
      '<line x1="'+pad+'" y1="'+pad+'" x2="'+pad+'" y2="'+(H-pad)+'" stroke="#e2e8f0" stroke-width="1"/>'+
      // zero line
      (zeroY>pad&&zeroY<H-pad?'<line x1="'+pad+'" y1="'+zeroY+'" x2="'+(W-pad)+'" y2="'+zeroY+'" stroke="#94a3b8" stroke-width="1" stroke-dasharray="4,3"/>':'')+
      // area fill
      '<polygon points="'+sx(0)+','+sy(0)+' '+linePts+' '+sx(10)+','+sy(0)+'" fill="url(#gg)"/>'+
      '<polyline points="'+linePts+'" fill="none" stroke="#22c55e" stroke-width="2.5" stroke-linejoin="round"/>'+
      // cost flat line
      '<line x1="'+sx(0)+'" y1="'+sy(0)+'" x2="'+sx(10)+'" y2="'+sy(0)+'" stroke="#ef4444" stroke-width="1.5" stroke-dasharray="5,3"/>'+
      // axis labels
      '<text x="'+sx(0)+'" y="'+(H-pad+14)+'" font-size="9" fill="#94a3b8" text-anchor="middle">0</text>'+
      '<text x="'+sx(5)+'" y="'+(H-pad+14)+'" font-size="9" fill="#94a3b8" text-anchor="middle">5yr</text>'+
      '<text x="'+sx(10)+'" y="'+(H-pad+14)+'" font-size="9" fill="#94a3b8" text-anchor="middle">10yr</text>'+
      '<text x="'+(W-pad+2)+'" y="'+(H-pad+14)+'" font-size="8" fill="#64748b">&#8594; years</text>'+
      '<text x="8" y="'+pad+'" font-size="8" fill="#22c55e">&#8593; gain</text>';
  }

  // Load featured colleges
  fetch('<?= $base ?>/api/colleges.php?featured=1&limit=3')
    .then(function(r){return r.json();})
    .then(function(data){
      var cols=Array.isArray(data)?data:(data.colleges||data.data||[]);
      var g=document.getElementById('featGrid');
      if(!cols||!cols.length){g.innerHTML='<div class="text-muted small">No featured colleges found.</div>';return;}
      g.innerHTML=cols.map(function(c){
        var name=esc(c.name||c.college_name||'University');
        var meta=(c.location||c.state||'')+(c.naac_grade?' &middot; NAAC '+esc(c.naac_grade):'');
        var id=c.id||c.college_id||'';
        return '<div class="feat-card"><div class="fc-logo">&#127979;</div><div class="fc-name">'+name+'</div><div class="fc-meta">'+meta+'</div><a href="<?= $base ?>/college-detail.php?id='+id+'" class="fc-link">View Details &#8594;</a></div>';
      }).join('');
    })
    .catch(function(){document.getElementById('featGrid').innerHTML='';});

  function esc(s){var d=document.createElement('div');d.textContent=String(s||'');return d.innerHTML;}

  calc();
})();
</script>

<?php include dirname(__DIR__) . '/includes/footer.php'; ?>
