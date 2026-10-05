<?php
/**
 * Reusable sticky lead-capture form card.
 * Pre-fill course by setting $leadFormCourse before including.
 */
$_lfCourse = isset($leadFormCourse) ? htmlspecialchars($leadFormCourse) : '';
$_lfId     = 'leadForm_' . substr(md5(uniqid()), 0, 6);
?>
<style>
.ck-lead-card{background:#fff;border-radius:14px;box-shadow:0 8px 40px rgba(37,99,235,.13);overflow:hidden;border:1px solid #e2e8f0;}
.ck-lead-header{background:linear-gradient(135deg,#1d4ed8 0%,#2563eb 100%);color:#fff;padding:18px 20px 14px;}
.ck-lead-header h5{font-size:1rem;font-weight:800;margin:0 0 2px;}
.ck-lead-header p{font-size:.78rem;opacity:.85;margin:0;}
.ck-lead-body{padding:20px;}
.ck-lead-body .form-label{font-size:.78rem;font-weight:600;color:#374151;margin-bottom:4px;}
.ck-lead-body .form-control,.ck-lead-body .form-select{font-size:.85rem;border:1.5px solid #e2e8f0;border-radius:8px;padding:8px 12px;color:#0f172a;transition:border-color .2s;}
.ck-lead-body .form-control:focus,.ck-lead-body .form-select:focus{border-color:#2563eb;box-shadow:0 0 0 3px rgba(37,99,235,.1);outline:none;}
.ck-lead-phone-wrap{display:flex;gap:0;}
.ck-lead-phone-prefix{background:#f1f5f9;border:1.5px solid #e2e8f0;border-right:none;border-radius:8px 0 0 8px;padding:8px 10px;font-size:.85rem;font-weight:600;color:#374151;white-space:nowrap;display:flex;align-items:center;}
.ck-lead-phone-wrap .form-control{border-radius:0 8px 8px 0;}
.ck-lead-trust{display:flex;align-items:center;gap:6px;font-size:.73rem;color:#16a34a;font-weight:600;margin-bottom:14px;}
.ck-lead-trust i{font-size:.9rem;}
.btn-lead-submit{width:100%;background:linear-gradient(90deg,#2563eb,#1d4ed8);color:#fff;border:none;border-radius:10px;padding:11px 16px;font-size:.9rem;font-weight:700;cursor:pointer;transition:opacity .2s,transform .1s;display:flex;align-items:center;justify-content:center;gap:6px;}
.btn-lead-submit:hover{opacity:.93;}
.btn-lead-submit:active{transform:scale(.98);}
.ck-lead-success{display:none;padding:24px 20px;text-align:center;}
.ck-lead-success .success-icon{font-size:2.4rem;margin-bottom:10px;}
.ck-lead-success p{font-size:.95rem;font-weight:700;color:#16a34a;margin:0 0 4px;}
.ck-lead-success small{color:#64748b;font-size:.78rem;}
</style>

<div class="ck-lead-card" id="<?= $_lfId ?>_wrapper">
  <div class="ck-lead-header">
    <h5><i class="bi bi-headset me-1"></i> Get Free Counselling</h5>
    <p>Talk to our expert counsellors &mdash; 100% free &amp; no obligation</p>
  </div>

  <div class="ck-lead-body" id="<?= $_lfId ?>_body">
    <form id="<?= $_lfId ?>" novalidate>
      <input type="hidden" name="source" value="lead_form_sidebar">

      <div class="mb-3">
        <label class="form-label" for="<?= $_lfId ?>_name">Full Name <span class="text-danger">*</span></label>
        <input type="text" class="form-control" id="<?= $_lfId ?>_name" name="student_name"
               placeholder="Enter your full name" required>
      </div>

      <div class="mb-3">
        <label class="form-label" for="<?= $_lfId ?>_phone">Phone <span class="text-danger">*</span></label>
        <div class="ck-lead-phone-wrap">
          <span class="ck-lead-phone-prefix"><i class="bi bi-flag me-1"></i>+91</span>
          <input type="tel" class="form-control" id="<?= $_lfId ?>_phone" name="student_phone"
                 placeholder="10-digit mobile" maxlength="10" pattern="[6-9][0-9]{9}" required>
        </div>
      </div>

      <div class="mb-3">
        <label class="form-label" for="<?= $_lfId ?>_email">Email <span class="text-danger">*</span></label>
        <input type="email" class="form-control" id="<?= $_lfId ?>_email" name="student_email"
               placeholder="you@example.com" required>
      </div>

      <div class="mb-3">
        <label class="form-label" for="<?= $_lfId ?>_state">Select State <span class="text-danger">*</span></label>
        <select class="form-select" id="<?= $_lfId ?>_state" name="state" required>
          <option value="">-- Select your state --</option>
          <option>Andhra Pradesh</option><option>Arunachal Pradesh</option><option>Assam</option>
          <option>Bihar</option><option>Chhattisgarh</option><option>Goa</option><option>Gujarat</option>
          <option>Haryana</option><option>Himachal Pradesh</option><option>Jharkhand</option>
          <option>Karnataka</option><option>Kerala</option><option>Madhya Pradesh</option>
          <option>Maharashtra</option><option>Manipur</option><option>Meghalaya</option><option>Mizoram</option>
          <option>Nagaland</option><option>Odisha</option><option>Punjab</option><option>Rajasthan</option>
          <option>Sikkim</option><option>Tamil Nadu</option><option>Telangana</option><option>Tripura</option>
          <option>Uttar Pradesh</option><option>Uttarakhand</option><option>West Bengal</option>
          <option>Andaman &amp; Nicobar Islands</option><option>Chandigarh</option>
          <option>Dadra &amp; Nagar Haveli and Daman &amp; Diu</option><option>Delhi</option>
          <option>Jammu &amp; Kashmir</option><option>Ladakh</option><option>Lakshadweep</option>
          <option>Puducherry</option>
        </select>
      </div>

      <div class="mb-3">
        <label class="form-label" for="<?= $_lfId ?>_course">Course Interest</label>
        <input type="text" class="form-control" id="<?= $_lfId ?>_course" name="course_interest"
               placeholder="e.g. Online MBA" value="<?= $_lfCourse ?>">
      </div>

      <div class="ck-lead-trust">
        <i class="bi bi-shield-check-fill"></i>
        We Don&rsquo;t Spam &mdash; Your data is 100% safe with us
      </div>

      <button type="submit" class="btn-lead-submit">
        Find Best University &nbsp;<i class="bi bi-arrow-right"></i>
      </button>

      <div id="<?= $_lfId ?>_error" style="display:none;color:#dc2626;font-size:.78rem;margin-top:8px;text-align:center;"></div>
    </form>
  </div>

  <div class="ck-lead-success" id="<?= $_lfId ?>_success">
    <div class="success-icon">&#9989;</div>
    <p>Thank you! Our counsellor will call you within 2 hours.</p>
    <small>Check your email/SMS for confirmation.</small>
  </div>
</div>

<script>
(function(){
  var form    = document.getElementById('<?= $_lfId ?>');
  var body    = document.getElementById('<?= $_lfId ?>_body');
  var success = document.getElementById('<?= $_lfId ?>_success');
  var errBox  = document.getElementById('<?= $_lfId ?>_error');
  if (!form) return;

  form.addEventListener('submit', function(e){
    e.preventDefault();
    errBox.style.display = 'none';
    if (!form.checkValidity()){ form.reportValidity(); return; }

    var btn = form.querySelector('.btn-lead-submit');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span>Submitting...';

    var fd = new FormData(form);
    var phone = fd.get('student_phone') || '';
    if (phone && !phone.startsWith('+91')) fd.set('student_phone', '+91' + phone.replace(/^0/, ''));
    var qs = new URLSearchParams(window.location.search);
    fd.set('referrer_url',  window.location.href);
    fd.set('utm_source',    qs.get('utm_source')   || '');
    fd.set('utm_medium',    qs.get('utm_medium')   || '');
    fd.set('utm_campaign',  qs.get('utm_campaign') || '');

    fetch(window.CK_BASE + '/api/leads.php', { method: 'POST', body: fd })
      .then(function(r){ return r.json(); })
      .then(function(data){
        if (data.success) {
          body.style.display    = 'none';
          success.style.display = 'block';
        } else {
          errBox.textContent   = data.message || 'Something went wrong. Please try again.';
          errBox.style.display = 'block';
          btn.disabled         = false;
          btn.innerHTML        = 'Find Best University &nbsp;<i class="bi bi-arrow-right"></i>';
        }
      })
      .catch(function(){
        errBox.textContent   = 'Network error. Please try again.';
        errBox.style.display = 'block';
        btn.disabled         = false;
        btn.innerHTML        = 'Find Best University &nbsp;<i class="bi bi-arrow-right"></i>';
      });
  });
})();
</script>
