<?php /* Floating WhatsApp + Call buttons — fixed bottom-right */ ?>
<style>
#ckFloatingCTA{position:fixed;bottom:28px;right:20px;z-index:1100;display:flex;flex-direction:column;align-items:center;gap:12px;transition:bottom .2s;}
/* Shifted up while the compare bar (colleges.php) is showing at the bottom
   of the screen, so these buttons (z-index 1100) stop sitting on top of
   and swallowing clicks meant for the compare bar's own buttons (z-index 900). */
#ckFloatingCTA.compare-bar-open{bottom:92px;}
.ck-float-btn{width:54px;height:54px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:1.4rem;color:#fff;text-decoration:none;box-shadow:0 4px 18px rgba(0,0,0,.22);transition:transform .2s,box-shadow .2s;position:relative;}
.ck-float-btn:hover{transform:scale(1.1);box-shadow:0 6px 24px rgba(0,0,0,.3);color:#fff;}
.ck-float-btn-wa{background:#25d366;}
.ck-float-btn-call{background:#2563eb;}
.ck-float-label{font-size:.62rem;font-weight:700;color:#374151;margin-top:-6px;text-align:center;letter-spacing:.04em;background:#fff;border-radius:4px;padding:1px 5px;box-shadow:0 1px 4px rgba(0,0,0,.1);}

/* Tooltip on hover */
.ck-float-btn::before{
  content: attr(data-tip);
  position:absolute;right:calc(100% + 10px);top:50%;transform:translateY(-50%);
  background:#0f172a;color:#fff;font-size:.72rem;font-weight:600;
  padding:5px 10px;border-radius:8px;white-space:nowrap;
  opacity:0;pointer-events:none;transition:opacity .2s;
}
.ck-float-btn:hover::before{opacity:1;}

/* Pulse animation on WhatsApp */
@keyframes ckWaPulse{
  0%{box-shadow:0 0 0 0 rgba(37,211,102,.5);}
  70%{box-shadow:0 0 0 14px rgba(37,211,102,0);}
  100%{box-shadow:0 0 0 0 rgba(37,211,102,0);}
}
.ck-float-btn-wa{animation:ckWaPulse 2.4s infinite;}

@media(max-width:576px){
  #ckFloatingCTA{bottom:18px;right:12px;gap:10px;}
  #ckFloatingCTA.compare-bar-open{bottom:80px;}
  .ck-float-btn{width:46px;height:46px;font-size:1.2rem;}
}
</style>

<div id="ckFloatingCTA" aria-label="Quick contact buttons">
  <a href="https://wa.me/911800123456?text=Hi%2C+I+want+to+know+about+online+degrees"
     class="ck-float-btn ck-float-btn-wa"
     target="_blank" rel="noopener"
     data-tip="Chat on WhatsApp"
     aria-label="Chat on WhatsApp">
    <i class="bi bi-whatsapp"></i>
  </a>
  <div class="ck-float-label">Chat</div>

  <a href="tel:+911800123456"
     class="ck-float-btn ck-float-btn-call"
     data-tip="Call us free"
     aria-label="Call us">
    <i class="bi bi-telephone-fill"></i>
  </a>
  <div class="ck-float-label">Call</div>
</div>
