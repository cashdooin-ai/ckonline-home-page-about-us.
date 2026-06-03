<?php /* Exit-intent popup — include once per page, ideally in footer */ ?>
<style>
#ckExitOverlay{position:fixed;inset:0;background:rgba(15,23,42,.72);z-index:9999;display:none;align-items:center;justify-content:center;padding:16px;}
#ckExitOverlay.active{display:flex;}
#ckExitCard{background:#fff;border-radius:18px;max-width:520px;width:100%;box-shadow:0 24px 80px rgba(0,0,0,.22);position:relative;overflow:hidden;animation:ckPopIn .3s cubic-bezier(.34,1.56,.64,1);}
@keyframes ckPopIn{from{opacity:0;transform:scale(.88)}to{opacity:1;transform:scale(1)}}
#ckExitCard .ck-ep-close{position:absolute;top:14px;right:14px;width:32px;height:32px;border-radius:50%;background:#f1f5f9;border:none;cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:1.1rem;color:#64748b;z-index:2;transition:background .2s;}
#ckExitCard .ck-ep-close:hover{background:#e2e8f0;}
.ck-ep-left{background:linear-gradient(135deg,#1d4ed8 0%,#2563eb 100%);color:#fff;padding:32px 24px;flex:1;}
.ck-ep-left h4{font-size:1.3rem;font-weight:900;margin:0 0 8px;}
.ck-ep-left p{font-size:.85rem;opacity:.9;margin:0 0 16px;}
.ck-ep-bullets{list-style:none;padding:0;margin:0;}
.ck-ep-bullets li{display:flex;align-items:center;gap:8px;font-size:.82rem;margin-bottom:8px;opacity:.92;}
.ck-ep-bullets li::before{content:"\2714";background:rgba(255,255,255,.2);border-radius:50%;width:20px;height:20px;display:flex;align-items:center;justify-content:center;font-size:.72rem;flex-shrink:0;}
.ck-ep-right{padding:24px 20px;flex:1;min-width:0;}
.ck-ep-right .form-label{font-size:.77rem;font-weight:600;color:#374151;margin-bottom:3px;}
.ck-ep-right .form-control,.ck-ep-right .form-select{font-size:.82rem;border:1.5px solid #e2e8f0;border-radius:8px;padding:7px 10px;}
.ck-ep-right .form-control:focus,.ck-ep-right .form-select:focus{border-color:#2563eb;box-shadow:0 0 0 2px rgba(37,99,235,.1);outline:none;}
.ck-ep-phone-wrap{display:flex;}
.ck-ep-prefix{background:#f1f5f9;border:1.5px solid #e2e8f0;border-right:none;border-radius:8px 0 0 8px;padding:7px 8px;font-size:.82rem;font-weight:600;color:#374151;display:flex;align-items:center;}
.ck-ep-phone-wrap .form-control{border-radius:0 8px 8px 0;}
.btn-ep-submit{width:100%;background:linear-gradient(90deg,#2563eb,#1d4ed8);color:#fff;border:none;border-radius:10px;padding:10px;font-size:.88rem;font-weight:700;cursor:pointer;transition:opacity .2s;margin-top:4px;}
.btn-ep-submit:hover{opacity:.9;}
.ck-ep-success{text-align:center;padding:20px 0;}
.ck-ep-success .ep-ok{font-size:2rem;margin-bottom:8px;}
.ck-ep-success p{font-size:.9rem;font-weight:700;color:#16a34a;margin:0;}
@media(max-width:540px){
  #ckExitCard{flex-direction:column !important;}
  .ck-ep-left{padding:20px 18px;}
  .ck-ep-right{padding:16px 16px;}
}
</style>

<div id="ckExitOverlay" role="dialog" aria-modal="true" aria-label="Free counselling offer">
  <div id="ckExitCard" style="display:flex;flex-direction:row;">
    <button class="ck-ep-close" id="ckExitClose" aria-label="Close">&times;</button>

    <!-- Left panel -->
    <div class="ck-ep-left">
      <h4>Wait! Before you go...</h4>
      <p>Get <strong>FREE counselling</strong> from our experts.<br>Limited slots today!</p>
      <ul class="ck-ep-bullets">
        <li>Free 30-minute session</li>
        <li>Expert guidance on best courses</li>
        <li>No obligations, cancel anytime</li>
        <li>Response within 2 hours</li>
      </ul>
    </div>

    <!-- Right panel: form -->
    <div class="ck-ep-right">
      <div id="ckExitFormWrap">
        <form id="ckExitForm" novalidate>
          <input type="hidden" name="source" value="exit_popup">

          <div class="mb-2">
            <label class="form-label" for="ep_name">Full Name <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="ep_name" name="student_name"
                   placeholder="Your name" required>
          </div>

          <div class="mb-2">
            <label class="form-label" for="ep_phone">Phone <span class="text-danger">*</span></label>
            <div class="ck-ep-phone-wrap">
              <span class="ck-ep-prefix">+91</span>
              <input type="tel" class="form-control" id="ep_phone" name="student_phone"
                     placeholder="10-digit mobile" maxlength="10" pattern="[6-9][0-9]{9}" required>
            </div>
          </div>

          <div class="mb-2">
            <label class="form-label" for="ep_email">Email <span class="text-danger">*</span></label>
            <input type="email" class="form-control" id="ep_email" name="student_email"
                   placeholder="you@example.com" required>
          </div>

          <div class="mb-2">
            <label class="form-label" for="ep_state">State <span class="text-danger">*</span></label>
            <select class="form-select" id="ep_state" name="state" required>
              <option value="">-- Select state --</option>
              <option>Andhra Pradesh</option><option>Arunachal Pradesh</option><option>Assam</option>
              <option>Bihar</option><option>Chhattisgarh</option><option>Goa</option><option>Gujarat</option>
              <option>Haryana</option><option>Himachal Pradesh</option><option>Jharkhand</option>
              <option>Karnataka</option><option>Kerala</option><option>Madhya Pradesh</option>
              <option>Maharashtra</option><option>Manipur</option><option>Meghalaya</option><option>Mizoram</option>
              <option>Nagaland</option><option>Odisha</option><option>Punjab</option><option>Rajasthan</option>
              <option>Sikkim</option><option>Tamil Nadu</option><option>Telangana</option><option>Tripura</option>
              <option>Uttar Pradesh</option><option>Uttarakhand</option><option>West Bengal</option>
              <option>Chandigarh</option><option>Delhi</option><option>Jammu &amp; Kashmir</option>
              <option>Ladakh</option><option>Puducherry</option>
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label" for="ep_course">Course Interest</label>
            <input type="text" class="form-control" id="ep_course" name="course_interest"
                   placeholder="e.g. Online MBA">
          </div>

          <button type="submit" class="btn-ep-submit">
            Get Free Counselling &#8594;
          </button>
          <div id="ckExitFormErr" style="display:none;color:#dc2626;font-size:.75rem;margin-top:6px;text-align:center;"></div>
        </form>
      </div>

      <div class="ck-ep-success" id="ckExitSuccess" style="display:none;">
        <div class="ep-ok">&#9989;</div>
        <p>Thank you! We'll call you shortly.</p>
        <small style="color:#64748b;font-size:.78rem;">Closing in 3 seconds...</small>
      </div>
    </div>
  </div>
</div>

<script>
(function(){
  var overlay  = document.getElementById('ckExitOverlay');
  var closeBtn = document.getElementById('ckExitClose');
  var form     = document.getElementById('ckExitForm');
  var formWrap = document.getElementById('ckExitFormWrap');
  var success  = document.getElementById('ckExitSuccess');
  var errBox   = document.getElementById('ckExitFormErr');
  var fired    = false;

  function openPopup(){
    if (fired) return;
    if (sessionStorage.getItem('ck_exit_shown')) return;
    fired = true;
    sessionStorage.setItem('ck_exit_shown', '1');
    overlay.classList.add('active');
  }

  function closePopup(){
    overlay.classList.remove('active');
  }

  // Exit intent: mouse leaves top of viewport
  document.addEventListener('mouseleave', function(e){
    if (e.clientY < 10) openPopup();
  });

  // Close on overlay click (outside card)
  overlay.addEventListener('click', function(e){
    if (e.target === overlay) closePopup();
  });
  closeBtn.addEventListener('click', closePopup);

  // ESC key
  document.addEventListener('keydown', function(e){
    if (e.key === 'Escape') closePopup();
  });

  // Form submit
  form.addEventListener('submit', function(e){
    e.preventDefault();
    errBox.style.display = 'none';
    if (!form.checkValidity()){ form.reportValidity(); return; }

    var btn = form.querySelector('.btn-ep-submit');
    btn.disabled = true;
    btn.textContent = 'Submitting...';

    var fd = new FormData(form);
    var phone = fd.get('student_phone') || '';
    if (phone && !phone.startsWith('+91')) fd.set('student_phone', '+91' + phone.replace(/^0/, ''));
    var qs = new URLSearchParams(window.location.search);
    fd.set('referrer_url', window.location.href);
    fd.set('utm_source',   qs.get('utm_source')   || '');
    fd.set('utm_medium',   qs.get('utm_medium')   || '');
    fd.set('utm_campaign', qs.get('utm_campaign') || '');

    fetch(window.CK_BASE + '/api/leads.php', { method: 'POST', body: fd })
      .then(function(r){ return r.json(); })
      .then(function(data){
        if (data.success) {
          formWrap.style.display = 'none';
          success.style.display  = 'block';
          setTimeout(closePopup, 3000);
        } else {
          errBox.textContent   = data.message || 'Something went wrong.';
          errBox.style.display = 'block';
          btn.disabled = false;
          btn.textContent = 'Get Free Counselling →';
        }
      })
      .catch(function(){
        errBox.textContent   = 'Network error. Please try again.';
        errBox.style.display = 'block';
        btn.disabled = false;
        btn.textContent = 'Get Free Counselling →';
      });
  });
})();
</script>
