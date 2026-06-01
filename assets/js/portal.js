/**
 * CollegeKampus Online -- Portal JS
 * Live filter & search via fetch to /api/colleges.php
 */
(function () {
    'use strict';
    function qs(sel, ctx) { return (ctx || document).querySelector(sel); }

    function formatINR(amount) {
        if (!amount || amount == 0) return 'N/A';
        return '\u20B9' + Number(amount).toLocaleString('en-IN');
    }
    function typeLabel(type) {
        return {government:'Government',private:'Private',deemed:'Deemed'}[type] || type;
    }
    function typeBadgeClass(type) {
        return {government:'badge-government',private:'badge-private',deemed:'badge-deemed'}[type] || 'badge-secondary';
    }
    function collegeInitials(name) {
        return name.split(' ').slice(0,2).map(w=>w[0]).join('').toUpperCase();
    }

    function renderCollegeCard(c) {
        const logoHtml = c.logo_url
            ? '<img src="' + c.logo_url + '" alt="' + c.name + '">'
            : '<span>' + collegeInitials(c.name) + '</span>';
        const coursesPills = (c.top_courses || []).slice(0,3).map(course =>
            '<span class="ck-course-pill">' + course + '</span>'
        ).join('');
        const minFees = c.min_fees ? formatINR(c.min_fees) + '/yr' : 'Varies';
        const maxFees = c.max_fees ? formatINR(c.max_fees) + '/yr' : '';
        return `
        <div class="col">
            <div class="ck-college-card">
                <div class="card-body">
                    <div class="d-flex gap-3 align-items-start mb-3">
                        <div class="ck-college-logo">${logoHtml}</div>
                        <div class="flex-grow-1">
                            <h5 class="card-title">${c.name}</h5>
                            <div class="d-flex flex-wrap gap-1 align-items-center">
                                <span class="badge ${typeBadgeClass(c.type)} small">${typeLabel(c.type)}</span>
                                <span class="text-muted small"><i class="bi bi-geo-alt me-1"></i>${c.city}, ${c.state}</span>
                            </div>
                        </div>
                    </div>
                    ${c.accreditation ? '<p class="small text-muted mb-2"><i class="bi bi-patch-check-fill text-success me-1"></i>'+c.accreditation+'</p>' : ''}
                    <div class="mb-2">${coursesPills || '<span class="text-muted small">No courses listed</span>'}</div>
                    <div class="mt-auto"><div class="small text-muted"><i class="bi bi-currency-rupee me-1"></i>Fees: <strong class="text-dark">${minFees}${maxFees && maxFees !== minFees ? ' - ' + maxFees : ''}</strong></div></div>
                </div>
                <div class="card-footer d-flex justify-content-between align-items-center">
                    ${c.ranking_score > 0 ? '<small class="text-muted"><i class="bi bi-star-fill text-warning me-1"></i>Score: ' + c.ranking_score + '</small>' : '<span></span>'}
                    <a href="/college-detail.php?slug=${c.slug}" class="btn btn-ck-primary btn-sm">View Details</a>
                </div>
            </div>
        </div>`;
    }

    const collegeGrid    = qs('#ck-college-grid');
    const collegesCount  = qs('#ck-colleges-count');
    const filterForm     = qs('#ck-filter-form');
    const searchInput    = qs('#ck-search-input');
    const paginationWrap = qs('#ck-pagination');

    if (!collegeGrid) return;

    let currentPage = 1;
    let debounceTimer;
    const PER_PAGE = 12;

    function buildParams(page) {
        const params = new URLSearchParams();
        if (page > 1) params.set('page', page);
        if (searchInput && searchInput.value.trim()) params.set('search', searchInput.value.trim());
        if (filterForm) {
            const fd = new FormData(filterForm);
            for (const [key, val] of fd.entries()) {
                if (val) params.append(key, val);
            }
        }
        return params;
    }

    function showSpinner() {
        collegeGrid.innerHTML = '<div class="col-12"><div class="ck-spinner"></div></div>';
    }

    function renderPagination(total, page) {
        if (!paginationWrap) return;
        const totalPages = Math.ceil(total / PER_PAGE);
        if (totalPages <= 1) { paginationWrap.innerHTML = ''; return; }
        let html = '<ul class="pagination justify-content-center flex-wrap gap-1">';
        html += `<li class="page-item ${page<=1?'disabled':''}"><button class="page-link" data-page="${page-1}">&laquo;</button></li>`;
        for (let i=1; i<=totalPages; i++) {
            if (i===1||i===totalPages||Math.abs(i-page)<=2) {
                html += `<li class="page-item ${i===page?'active':''}"><button class="page-link" data-page="${i}">${i}</button></li>`;
            } else if (Math.abs(i-page)===3) {
                html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
            }
        }
        html += `<li class="page-item ${page>=totalPages?'disabled':''}"><button class="page-link" data-page="${page+1}">&raquo;</button></li>`;
        html += '</ul>';
        paginationWrap.innerHTML = html;
        paginationWrap.querySelectorAll('button[data-page]').forEach(btn => {
            btn.addEventListener('click', () => {
                const p = parseInt(btn.dataset.page);
                if (p>=1 && p<=totalPages) { currentPage=p; loadColleges(); }
            });
        });
    }

    function loadColleges() {
        showSpinner();
        const params = buildParams(currentPage);
        fetch('/api/colleges.php?' + params.toString())
            .then(r => r.json())
            .then(data => {
                if (!data.colleges || data.colleges.length === 0) {
                    collegeGrid.innerHTML = '<div class="col-12"><div class="ck-empty-state"><i class="bi bi-building-slash"></i><p>No colleges found matching your criteria.</p><a href="/colleges.php" class="btn btn-ck-primary mt-2">Reset Filters</a></div></div>';
                    if (collegesCount) collegesCount.textContent = '0';
                    renderPagination(0, 1);
                    return;
                }
                collegeGrid.innerHTML = data.colleges.map(renderCollegeCard).join('');
                if (collegesCount) collegesCount.textContent = data.total || data.colleges.length;
                renderPagination(data.total || data.colleges.length, currentPage);
            })
            .catch(() => {
                collegeGrid.innerHTML = '<div class="col-12"><div class="alert alert-danger ck-flash">Failed to load colleges. Please try again.</div></div>';
            });
    }

    if (searchInput) {
        searchInput.addEventListener('input', () => {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => { currentPage=1; loadColleges(); }, 350);
        });
    }

    if (filterForm) {
        filterForm.querySelectorAll('input, select').forEach(el => {
            el.addEventListener('change', () => { currentPage=1; loadColleges(); });
        });
        const feeRange = filterForm.querySelector('#fee-range');
        const feeLabel = filterForm.querySelector('#fee-range-label');
        if (feeRange && feeLabel) {
            feeRange.addEventListener('input', () => {
                const v = parseInt(feeRange.value);
                feeLabel.textContent = v >= 1000000 ? 'Any' : '\u20B9' + v.toLocaleString('en-IN');
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => { currentPage=1; loadColleges(); }, 400);
            });
        }
    }

    loadColleges();

    document.querySelectorAll('.ck-flash.alert-success').forEach(el => {
        setTimeout(() => {
            el.style.transition = 'opacity .5s';
            el.style.opacity = '0';
            setTimeout(() => el.remove(), 500);
        }, 4000);
    });
})();
