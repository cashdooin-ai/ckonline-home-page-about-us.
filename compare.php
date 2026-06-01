<?php
$pageTitle = 'Compare Colleges';
$pageDesc  = 'Compare up to 3 online colleges side-by-side on fees, courses, accreditation and more.';
require_once __DIR__ . '/config/db.php';
if (!isset($extraHead)) $extraHead = '';
$extraHead .= '<script>window.CK_BASE = ' . json_encode(rtrim(SITE_BASE, '/')) . ';</script>';

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
        foreach (['city','state','type','min_fees','max_fees','established_year','accreditation','total_seats','description'] as $col) {
            if (in_array($col, $allCols)) $select .= ", c.$col";
        }
        $coursesSub = '';
        try {
            $db->query("SELECT 1 FROM college_courses LIMIT 1");
            $coursesSub = ", (SELECT GROUP_CONCAT(cr.name SEPARATOR ', ') FROM college_courses cc JOIN courses cr ON cr.id = cc.course_id WHERE cc.college_id = c.id) AS all_courses";
        } catch (Throwable $e) {}

        $ph = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $db->prepare("SELECT $select $coursesSub FROM colleges c WHERE c.id IN ($ph)");
        $stmt->execute($ids);
        $colleges = $stmt->fetchAll();
    } catch (Throwable $e) {}
}

include __DIR__ . '/includes/header.php';
?>

<div class="page-wrapper">
  <div class="compare-hero">
    <div class="container">
      <h1>&#128202; Compare Colleges</h1>
      <p>Make an informed decision with side-by-side comparison</p>
    </div>
  </div>

  <div class="container">
    <?php if (empty($colleges)): ?>
      <div style="text-align:center;padding:80px 24px;">
        <p style="font-size:1.1rem;color:#64748b;margin-bottom:24px;">No colleges selected for comparison.</p>
        <a href="/colleges.php" class="btn-view" style="display:inline-block;padding:12px 28px;">Browse Colleges</a>
      </div>
    <?php else: ?>
    <div class="compare-table-wrap">
      <table class="compare-table">
        <thead>
          <tr>
            <th>Feature</th>
            <?php foreach ($colleges as $col): ?>
            <th class="college-col"><?= htmlspecialchars($col['name']) ?></th>
            <?php endforeach; ?>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td><strong>Location</strong></td>
            <?php foreach ($colleges as $col): ?>
            <td class="college-col"><?= htmlspecialchars(trim(($col['city'] ?? '') . ', ' . ($col['state'] ?? ''), ', ') ?: '—') ?></td>
            <?php endforeach; ?>
          </tr>
          <tr>
            <td><strong>Type</strong></td>
            <?php foreach ($colleges as $col): ?>
            <td class="college-col"><?= htmlspecialchars($col['type'] ?? '—') ?></td>
            <?php endforeach; ?>
          </tr>
          <tr>
            <td><strong>Established</strong></td>
            <?php foreach ($colleges as $col): ?>
            <td class="college-col"><?= htmlspecialchars($col['established_year'] ?? '—') ?></td>
            <?php endforeach; ?>
          </tr>
          <tr>
            <td><strong>Accreditation</strong></td>
            <?php foreach ($colleges as $col): ?>
            <td class="college-col"><?= htmlspecialchars($col['accreditation'] ?? '—') ?></td>
            <?php endforeach; ?>
          </tr>
          <tr>
            <td><strong>Fees Range</strong></td>
            <?php foreach ($colleges as $col): ?>
            <td class="college-col">
              <?php
              $min = $col['min_fees'] ?? null;
              $max = $col['max_fees'] ?? null;
              if ($min && $max) echo '&#8377;' . number_format($min) . ' – &#8377;' . number_format($max);
              elseif ($min) echo 'From &#8377;' . number_format($min);
              elseif ($max) echo 'Upto &#8377;' . number_format($max);
              else echo '—';
              ?>
            </td>
            <?php endforeach; ?>
          </tr>
          <tr>
            <td><strong>Total Seats</strong></td>
            <?php foreach ($colleges as $col): ?>
            <td class="college-col"><?= htmlspecialchars($col['total_seats'] ?? '—') ?></td>
            <?php endforeach; ?>
          </tr>
          <tr>
            <td><strong>Courses Offered</strong></td>
            <?php foreach ($colleges as $col): ?>
            <td class="college-col" style="max-width:220px;"><?= htmlspecialchars($col['all_courses'] ?? '—') ?></td>
            <?php endforeach; ?>
          </tr>
          <tr>
            <td></td>
            <?php foreach ($colleges as $col): ?>
            <td class="college-col">
              <a href="/college-detail.php?id=<?= $col['id'] ?>" class="btn-view" style="display:block;margin-bottom:8px;">View Details</a>
              <a href="/apply.php?college_id=<?= $col['id'] ?>" class="btn-view" style="display:block;background:#22c55e;">Apply Now</a>
            </td>
            <?php endforeach; ?>
          </tr>
        </tbody>
      </table>
    </div>
    <div style="text-align:center;margin:32px 0;">
      <a href="/colleges.php" class="btn-view" style="display:inline-block;padding:12px 28px;">&#x2B; Add More Colleges</a>
    </div>
    <?php endif; ?>
  </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
