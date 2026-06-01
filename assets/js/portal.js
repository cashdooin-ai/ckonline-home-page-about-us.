/* ===== PORTAL.JS ===== */
'use strict';

/* ---- Compare state ---- */
let compareList = JSON.parse(sessionStorage.getItem('ck_compare') || '[]'); // [{id, name}]

function saveCompare() {
  sessionStorage.setItem('ck_compare', JSON.stringify(compareList));
}

function addToCompare(id, name) {
  id = String(id);
  if (compareList.find(c => c.id === id)) {
    removeFromCompare(id);
    return;
  }
  if (compareList.length >= 3) {
    alert('You can compare up to 3 colleges at a time.');
    return;
  }
  compareList.push({ id, name });
  saveCompare();
  renderCompareBar();
  updateCompareButtons();
  updateNavCount();
}

function removeFromCompare(id) {
  id = String(id);
  compareList = compareList.filter(c => c.id !== id);
  saveCompare();
  renderCompareBar();
  updateCompareButtons();
  updateNavCount();
}

function clearCompare() {
  compareList = [];
  saveCompare();
  renderCompareBar();
  updateCompareButtons();
  updateNavCount();
}

function goToCompare() {
  const ids = compareList.map(c => c.id).join(',');
  window.location.href = '/compare.php?ids=' + ids;
}

function renderCompareBar() {
  const bar = document.getElementById('compareBar');
  if (!bar) return;
  if (compareList.length === 0) {
    bar.classList.remove('visible');
    return;
  }
  bar.classList.add('visible');
  const chips = document.getElementById('compareChips');
  if (chips) {
    chips.innerHTML = compareList.map(c =>
      `<span class="compare-chip">${escHtml(c.name)}
        <button class="compare-chip-remove" onclick="removeFromCompare('${c.id}')" title="Remove">&times;</button>
      </span>`
    ).join('');
  }
}

function updateCompareButtons() {
  document.querySelectorAll('.btn-compare-add').forEach(btn => {
    const id = String(btn.dataset.id);
    const inList = compareList.find(c => c.id === id);
    if (inList) {
      btn.classList.add('added');
      btn.textContent = 'Added ✓';
    } else {
      btn.classList.remove('added');
      btn.textContent = '+ Compare';
    }
  });
}

function updateNavCount() {
  const span = document.getElementById('navCompareCount');
  const btn = document.getElementById('navCompareBtn');
  if (span) span.textContent = compareList.length;
  if (btn) btn.style.display = compareList.length > 0 ? '' : 'none';
}

function escHtml(str) {
  return String(str).replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m]));
}

/* ---- Load Colleges ---- */
let currentPage = 1;
let currentFilters = {};
let debounceTimer = null;

function loadColleges(filters, page) {
  page = page || 1;
  currentFilters = filters || currentFilters;
  currentPage = page;

  const grid = document.getElementById('collegesGrid');
  const countEl = document.getElementById('resultCount');
  if (!grid) return;

  grid.innerHTML = Array(6).fill('<div class="skeleton skeleton-card"></div>').join('');

  const params = new URLSearchParams();
  Object.entries(currentFilters).forEach(([k, v]) => { if (v !== '' && v !== null && v !== undefined) params.set(k, v); });
  params.set('page', page);
  params.set('limit', 12);

  fetch('/api/colleges.php?' + params.toString())
    .then(r => r.json())
    .then(data => {
      if (!data.colleges || data.colleges.length === 0) {
        grid.innerHTML = '<div style="grid-column:1/-1;text-align:center;padding:40px;color:#64748b;">No colleges found matching your criteria.</div>';
        if (countEl) countEl.textContent = '0 colleges found';
        renderPagination(0, page);
        return;
      }
      if (countEl) countEl.textContent = data.total_count + ' colleges found';
      grid.innerHTML = data.colleges.map(renderCollegeCard).join('');
      renderPagination(data.total_count, page);
      updateCompareButtons();
    })
    .catch(err => {
      console.error(err);
      grid.innerHTML = '<div style="grid-column:1/-1;text-align:center;padding:40px;color:#ef4444;">Failed to load colleges. Please try again.</div>';
    });
}

function renderCollegeCard(c) {
  const typeBadge = c.type ? `<span class="college-badge badge-${escHtml(c.type.toLowerCase())}">${escHtml(c.type)}</span>` : '';
  const courses = c.top_courses ? `<div class="college-courses">${escHtml(c.top_courses)}</div>` : '';
  const fees = c.fees_display ? `<div class="college-fees">Fees: <span>${escHtml(c.fees_display)}</span></div>` : '';
  return `<div class="college-card">
    <div class="college-card-body">
      <div class="college-card-name">${escHtml(c.name)}</div>
      <div class="college-card-location">${escHtml((c.city ? c.city + ', ' : '') + (c.state || ''))}</div>
      ${typeBadge}
      ${courses}
      ${fees}
    </div>
    <div class="college-card-footer">
      <a href="/college-detail.php?id=${encodeURIComponent(c.id)}" class="btn-view">View Details</a>
      <button class="btn-compare-add" data-id="${escHtml(String(c.id))}" data-name="${escHtml(c.name)}" onclick="addToCompare(this.dataset.id, this.dataset.name)">+ Compare</button>
    </div>
  </div>`;
}

function renderPagination(total, page) {
  const wrap = document.getElementById('paginationWrap');
  if (!wrap) return;
  const totalPages = Math.ceil(total / 12);
  if (totalPages <= 1) { wrap.innerHTML = ''; return; }

  let html = '';
  if (page > 1) html += `<button class="page-btn" onclick="loadColleges(null,${page-1})">&laquo; Prev</button>`;

  const start = Math.max(1, page - 2);
  const end = Math.min(totalPages, page + 2);
  for (let i = start; i <= end; i++) {
    html += `<button class="page-btn${i===page?' active':''}" onclick="loadColleges(null,${i})">${i}</button>`;
  }
  if (page < totalPages) html += `<button class="page-btn" onclick="loadColleges(null,${page+1})">Next &raquo;</button>`;
  wrap.innerHTML = html;
}

/* ---- Filter form wiring (colleges.php) ---- */
function initFilters() {
  const form = document.getElementById('filterForm');
  if (!form) return;

  function getFilters() {
    const fd = new FormData(form);
    const f = {};
    for (const [k, v] of fd.entries()) {
      if (f[k]) {
        f[k] = [].concat(f[k], v);
      } else {
        f[k] = v;
      }
    }
    // Flatten arrays to comma-separated
    Object.keys(f).forEach(k => { if (Array.isArray(f[k])) f[k] = f[k].join(','); });
    return f;
  }

  form.addEventListener('input', function() {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => loadColleges(getFilters(), 1), 400);
  });

  form.addEventListener('change', function() {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => loadColleges(getFilters(), 1), 200);
  });

  // Reset
  const resetBtn = document.getElementById('resetFilters');
  if (resetBtn) {
    resetBtn.addEventListener('click', function() {
      form.reset();
      loadColleges({}, 1);
    });
  }

  // Initial load
  loadColleges(getFilters(), 1);
}

/* ---- Multi-step apply form ---- */
function initStepForm() {
  const form = document.getElementById('applyForm');
  if (!form) return;

  let step = 1;
  const totalSteps = 3;

  function showStep(n) {
    document.querySelectorAll('.apply-step').forEach(el => el.classList.remove('active'));
    const s = document.getElementById('step' + n);
    if (s) s.classList.add('active');

    document.querySelectorAll('.step-item').forEach((el, idx) => {
      el.classList.remove('active', 'done');
      if (idx + 1 < n) el.classList.add('done');
      if (idx + 1 === n) el.classList.add('active');
    });

    if (n === 3) buildReview();
    step = n;
  }

  function buildReview() {
    const fd = new FormData(form);
    const fields = {
      'Full Name': fd.get('full_name'),
      'Email': fd.get('email'),
      'Phone': fd.get('phone'),
      'Date of Birth': fd.get('dob'),
      'City': fd.get('city'),
      'State': fd.get('state'),
      '10th %': fd.get('tenth_pct'),
      '12th %': fd.get('twelfth_pct'),
      'Entrance Exam': fd.get('entrance_exam'),
      'Exam Score': fd.get('exam_score'),
    };
    const wrap = document.getElementById('reviewContent');
    if (wrap) {
      wrap.innerHTML = Object.entries(fields).filter(([,v]) => v).map(([l,v]) =>
        `<div class="review-row"><span class="review-label">${escHtml(l)}</span><span class="review-val">${escHtml(v)}</span></div>`
      ).join('');
    }
  }

  document.querySelectorAll('.btn-next').forEach(btn => {
    btn.addEventListener('click', function() {
      if (step < totalSteps) showStep(step + 1);
    });
  });

  document.querySelectorAll('.btn-back').forEach(btn => {
    btn.addEventListener('click', function() {
      if (step > 1) showStep(step - 1);
    });
  });

  showStep(1);

  form.addEventListener('submit', function(e) {
    e.preventDefault();
    const btn = form.querySelector('.btn-submit-final');
    if (btn) btn.disabled = true;
    fetch('/api/leads.php', { method: 'POST', body: new FormData(form) })
      .then(r => r.json())
      .then(data => {
        if (data.success) {
          document.getElementById('applyFormWrap').innerHTML = `
            <div class="success-box">
              <div class="success-icon">&#x2705;</div>
              <h2>Application Submitted!</h2>
              <p>Thank you! Our counsellor will contact you within 24 hours.<br>Application ID: <strong>${data.application_id || 'CK' + Date.now()}</strong></p>
              <a href="/colleges.php" class="btn-view" style="display:inline-block;margin-top:20px;padding:12px 24px;">Browse More Colleges</a>
            </div>`;
        } else {
          alert(data.message || 'Submission failed. Please try again.');
          if (btn) btn.disabled = false;
        }
      })
      .catch(() => { alert('Network error. Please try again.'); if (btn) btn.disabled = false; });
  });
}

/* ---- Init on DOM ready ---- */
document.addEventListener('DOMContentLoaded', function() {
  renderCompareBar();
  updateCompareButtons();
  updateNavCount();
  initFilters();
  initStepForm();
});
