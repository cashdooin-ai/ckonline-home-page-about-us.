<?php
$pageTitle = 'Compare Online Colleges Side-by-Side | CollegeKampus';
$pageDesc  = 'Compare up to 3 online universities side-by-side on fees, NAAC grade, NIRF rank, delivery mode, placements and more.';
require_once __DIR__ . '/config/db.php';

$idsParam = trim($_GET['ids'] ?? '');
$ids = array_slice(array_filter(array_map('intval', explode(',', $idsParam))), 0, 3);

$colleges = [];
if ($ids) {
    try {
        $db = getDB();
        $allCols = [];
        $stmt = $db->query("SHOW COLUMNS FROM colleges");
        foreach ($stmt->fetchAll() as $r) $allCols[] = $r['Field'];

        $select = "c.id, c.name";
        $wantCols = ['city','state','college_type','delivery_mode','min_fees','max_fees',
                     'established_year','accreditation','naac_grade','nirf_rank','nirf_score',
                     'rating','placement_rate','avg_package','ugc_approved','website',
                     'logo_url','short_name','slug'];
        foreach ($wantCols as $col) {
            if (in_array($col, $allCols)) $select .= ", c.$col";
        }
        // Checking college_courses alone isn't enough - this DB (shared with
        // ckampus-dasboard) has college_courses but no `courses` table at
        // all, so the JOIN below would throw "Table 'courses' doesn't
        // exist" the moment the main query ran, taking the whole compare
        // page down with it. Test the actual join, not just one side of it.
        $coursesSub = '';
        try {
            $db->query("SELECT 1 FROM college_courses cc JOIN courses cr ON cr.id = cc.course_id LIMIT 1");
            $coursesSub = ", (SELECT GROUP_CONCAT(cr.name ORDER BY cr.name SEPARATOR ', ') FROM college_courses cc JOIN courses cr ON cr.id = cc.course_id WHERE cc.college_id = c.id LIMIT 8) AS all_courses";
        } catch (Throwable $e) {}

        $ph = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $db->prepare("SELECT $select $coursesSub FROM colleges c WHERE c.id IN ($ph)");
        $stmt->execute($ids);
        $colleges = $stmt->fetchAll();
    } catch (Throwable $e) {}
}

include __DIR__ . '/includes/header.php';
?>

<style>
.compare-hero{background:linear-gradient(135deg,#0f172a,#1e3a5f);padding:48px 0;color:#fff;text-align:center;}
.compare-hero h1{font-size:clamp(1.6rem,3vw,2.4rem);font-weight:900;margin-bottom:8px;}
.compare-hero p{color:rgba(255,255,255,.7);}
.compare-search-box{background:#fff;border:1px solid #e2e8f0;border-radius:16px;padding:32px;max-width:700px;margin:40px auto;}
.compare-search-box h3{font-size:1.1rem;font-weight:700;margin-bottom:16px;color:#0f172a;}
.college-search-row{display:flex;gap:12px;flex-wrap:wrap;}
.college-picker{flex:1;min-width:200px;}
.college-picker input{width:100%;padding:10px 14px;border:1px solid #e2e8f0;border-radius:8px;font-size:.875rem;}
.compare-table-wrap{overflow-x:auto;padding:32px 0;}
.compare-table{width:100%;border-collapse:collapse;min-width:500px;}
.compare-table .row-label{background:#f8fafc;padding:12px 16px;font-size:.8rem;font-weight:700;color:#374151;text-transform:uppercase;letter-spacing:.04em;border-bottom:1px solid #e2e8f0;white-space:nowrap;width:140px;}
.compare-table .college-cell{padding:14px 16px;border-bottom:1px solid #f1f5f9;text-align:center;vertical-align:middle;font-size:.875rem;color:#374151;}
.compare-table .college-header{background:#0f172a;color:#fff;padding:18px 16px;text-align:center;}
.compare-table .college-header .cname{font-size:.9rem;font-weight:700;}
.compare-table .college-header .cloc{font-size:.75rem;color:rgba(255,255,255,.6);margin-top:3px;}
.compare-table .highlight-green{color:#16a34a;font-weight:700;}
.compare-table .highlight-blue{color:#2563eb;font-weight:700;}
.compare-table tr:hover .college-cell{background:#f8fafc;}
.clabel-icon{margin-right:5px;}
</style>

<div class="compare-hero">
  <div class="container">
    <div style="display:inline-flex;align-items:center;gap:8px;background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.2);padding:5px 14px;border-radius:20px;font-size:.8rem;font-weight:600;margin-bottom:14px;">
      &#9878; Free Comparison Tool
    </div>
    <h1>Compare Universities Side-by-Side</h1>
    <p>Make a smarter decision with detailed feature comparison</p>
  </div>
</div>

<div class="page-wrapper" style="margin-top:0;">
  <div class="container">
    <?php if (empty($colleges)): ?>
    <div class="compare-search-box">
      <h3>&#128269; Select colleges to compare</h3>
      <p style="color:#64748b;font-size:.875rem;margin-bottom:20px;">Search for up to 3 colleges and click "Compare Now"</p>
      <div style="display:flex;gap:12px;flex-wrap:wrap;margin-bottom:16px;">
        <input id="compareSearch1" type="text" placeholder="Search College 1..." style="flex:1;min-width:180px;padding:10px 14px;border:1px solid #e2e8f0;border-radius:8px;font-size:.875rem;" oninput="searchCompareColleges(1,this.value)">
        <input id="compareSearch2" type="text" placeholder="Search College 2..." style="flex:1;min-width:180px;padding:10px 14px;border:1px solid #e2e8f0;border-radius:8px;font-size:.875rem;" oninput="searchCompareColleges(2,this.value)">
        <input id="compareSearch3" type="text" placeholder="Search College 3..." style="flex:1;min-width:180px;padding:10px 14px;border:1px solid #e2e8f0;border-radius:8px;font-size:.875rem;" oninput="searchCompareColleges(3,this.value)">
      </div>
      <div id="searchResults1" style="margin-bottom:8px;"></div>
      <div id="searchResults2" style="margin-bottom:8px;"></div>
      <div id="searchResults3" style="margin-bottom:16px;"></div>
      <div id="selectedList" style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:16px;"></div>
      <button onclick="goCompare()" class="btn-ck-primary" style="padding:10px 24px;font-size:.9rem;">&#9878; Compare Now</button>
      <p style="margin-top:20px;font-size:.82rem;color:#9ca3af;">Or <a href="<?= $base ?>/colleges.php" style="color:#2563eb;">browse colleges</a> and click "Add to Compare"</p>
    </div>

    <script>
    var selectedIds = {};
    function searchCompareColleges(slot, q) {
      var res = document.getElementById('searchResults'+slot);
      if(!q || q.length < 2){ res.innerHTML=''; return; }
      fetch((window.CK_BASE||'')+'/api/colleges.php?search='+encodeURIComponent(q)+'&limit=5')
        .then(function(r){return r.json();})
        .then(function(d){
          if(!d.colleges||!d.colleges.length){ res.innerHTML='<div style="color:#9ca3af;font-size:.82rem;padding:6px;">No results</div>'; return; }
          res.innerHTML = '<div style="background:#fff;border:1px solid #e2e8f0;border-radius:8px;overflow:hidden;">'+d.colleges.map(function(c){
            return '<div style="padding:10px 14px;font-size:.85rem;cursor:pointer;transition:background .15s;border-bottom:1px solid #f1f5f9;" onmouseover="this.style.background=\'#eff6ff\'" onmouseout="this.style.background=\'#fff\'" onclick="selectCollege('+c.id+',\''+c.name.replace(/'/g,"\\'")+"',"+slot+')">'+
              '<strong>'+c.name+'</strong>'+(c.city?'<span style="color:#9ca3af;margin-left:6px;font-size:.78rem;">'+c.city+'</span>':'')+
            '</div>';
          }).join('')+'</div>';
        }).catch(function(){});
    }
    function selectCollege(id, name, slot) {
      if(Object.keys(selectedIds).length>=3 && !selectedIds['s'+slot]){
        alert('You can compare up to 3 colleges.'); return;
      }
      selectedIds['s'+slot] = {id:id, name:name};
      document.getElementById('searchResults'+slot).innerHTML='';
      document.getElementById('compareSearch'+slot).value=name;
      renderSelected();
    }
    function renderSelected() {
      var el = document.getElementById('selectedList');
      el.innerHTML = Object.values(selectedIds).map(function(c){
        return '<span style="background:#eff6ff;border:1px solid #dbeafe;color:#1e40af;padding:6px 12px;border-radius:6px;font-size:.82rem;font-weight:600;">'+c.name+'</span>';
      }).join('');
    }
    function goCompare() {
      var ids = Object.values(selectedIds).map(function(c){return c.id;});
      if(ids.length<2){alert('Please select at least 2 colleges to compare.'); return;}
      window.location = (window.CK_BASE||'')+'/compare.php?ids='+ids.join(',');
    }
    </script>

    <?php else: ?>
    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;padding:20px 0 8px;">
      <h2 style="font-size:1.1rem;font-weight:700;color:#0f172a;margin:0;">Comparing <?= count($colleges) ?> Universities</h2>
      <a href="<?= $base ?>/compare.php" style="font-size:.85rem;color:#2563eb;text-decoration:none;">&#43; Compare Different Colleges</a>
    </div>
    <div class="compare-table-wrap">
      <table class="compare-table">
        <thead>
          <tr>
            <th class="row-label" style="background:#0f172a;"></th>
            <?php foreach($colleges as $col): ?>
            <th class="college-header">
              <?php if(!empty($col['logo_url'])): ?><img src="<?= htmlspecialchars($col['logo_url']) ?>" style="width:44px;height:44px;border-radius:8px;object-fit:contain;background:#fff;display:block;margin:0 auto 8px;" onerror="this.remove()"><?php endif; ?>
              <div class="cname"><?= htmlspecialchars($col['name']) ?></div>
              <div class="cloc"><?= htmlspecialchars(implode(', ',array_filter([$col['city']??'',$col['state']??'']))) ?></div>
            </th>
            <?php endforeach; ?>
          </tr>
        </thead>
        <tbody>
          <?php
          $rows = [
            ['College Type', function($c){ return ucfirst($c['college_type']??$c['type']??'—'); }, null],
            ['Location', function($c){ return implode(', ',array_filter([$c['city']??'',$c['state']??''])) ?: '—'; }, null],
            ['Delivery Mode', function($c){ return ucfirst($c['delivery_mode']??$c['online_mode']??'—'); }, 'green'],
            ['Established', function($c){ return $c['established_year']??'—'; }, null],
            ['NAAC Grade', function($c){ return $c['naac_grade']??'—'; }, 'green'],
            ['NIRF Rank', function($c){ return $c['nirf_rank']??'—'; }, null],
            ['Fees Range', function($c){
              $min=$c['min_fees']??0;$max=$c['max_fees']??0;
              $f=function($v){return '&#8377;'.($v>=100000?round($v/100000,1).'L':number_format($v));};
              if($min&&$max)return $f($min).' – '.$f($max);
              if($min)return 'From '.$f($min);
              if($max)return 'Upto '.$f($max);
              return '—';
            }, null],
            ['Placement Rate', function($c){ return $c['placement_rate']?$c['placement_rate'].'%':'—'; }, 'green'],
            ['Avg Package', function($c){ return $c['avg_package']?'&#8377;'.number_format($c['avg_package']/100000,1).'L':'—'; }, null],
            ['Accreditation', function($c){ return $c['accreditation']??'—'; }, null],
            ['UGC Approved', function($c){ return !empty($c['ugc_approved'])?'&#10003; Yes':'—'; }, 'green'],
            ['Courses Offered', function($c){ return $c['all_courses']??'—'; }, null],
            ['Website', function($c){ return !empty($c['website'])?'<a href="'.htmlspecialchars($c['website']).'" target="_blank" rel="noopener" style="color:#2563eb;">Visit Website</a>':'—'; }, null],
          ];
          foreach($rows as [$label, $fn, $hl]):
          ?>
          <tr>
            <td class="row-label"><?= $label ?></td>
            <?php foreach($colleges as $col): $val = $fn($col); ?>
            <td class="college-cell <?= $hl==='green'&&$val!=='—'?'highlight-green':'' ?> <?= $hl==='blue'&&$val!=='—'?'highlight-blue':'' ?>"><?= $val ?></td>
            <?php endforeach; ?>
          </tr>
          <?php endforeach; ?>
          <tr>
            <td class="row-label">Actions</td>
            <?php foreach($colleges as $col): $colSlug=$col['slug']??$col['id']; ?>
            <td class="college-cell">
              <a href="<?= $base ?>/college-detail.php?slug=<?= urlencode($colSlug) ?>" class="btn-view" style="display:block;margin-bottom:8px;text-align:center;">View Details</a>
              <a href="<?= $base ?>/apply.php?college_id=<?= $col['id'] ?>" class="btn-view" style="display:block;text-align:center;background:#22c55e;">Apply Now</a>
            </td>
            <?php endforeach; ?>
          </tr>
        </tbody>
      </table>
    </div>
    <div style="text-align:center;padding:20px 0 40px;">
      <a href="<?= $base ?>/colleges.php" class="btn-ck-green" style="padding:12px 28px;font-size:.9rem;">&#43; Add More Colleges to Compare</a>
      &nbsp;
      <a href="<?= $base ?>/counselling.php" class="btn-ck-primary" style="padding:12px 28px;font-size:.9rem;">&#127891; Get Free Counselling</a>
    </div>
    <?php endif; ?>
  </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
