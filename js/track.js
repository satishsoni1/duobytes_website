/* =====================================================================
   DuoBytes – Analytics & Ads tracking (shared by all 5 sites)
   ---------------------------------------------------------------------
   PASTE YOUR IDs BELOW. Anything left as 'XXXX' is simply skipped.
   ===================================================================== */
window.DB_TRACK = {
  GA4_ID:        'G-LZXYK47WPB',        // Google Analytics 4  → analytics.google.com
  GOOGLE_ADS_ID: 'AW-XXXXXXXXXX',       // Google Ads tag      → ads.google.com › Tools › Conversions
  ADS_LEAD_LABEL:'XXXXXXXXXXXXXXXXXXX', // Conversion label for "Lead / Demo request"
  ADS_CALL_LABEL:'XXXXXXXXXXXXXXXXXXX', // Conversion label for "Click to call / WhatsApp" (optional)
  META_PIXEL_ID: 'XXXXXXXXXXXXXXX',     // Meta (Facebook/Instagram) Pixel → business.facebook.com › Events Manager
  CLARITY_ID:    'XXXXXXXXXX',          // Microsoft Clarity (free heatmaps) → clarity.microsoft.com — optional
  LINKEDIN_ID:   'XXXXXXX'              // LinkedIn Insight Tag partner ID — optional (B2B ads)
};

(function () {
  const C = window.DB_TRACK, set = v => v && !/X{4,}/.test(v);
  const load = (src) => { const s = document.createElement('script'); s.async = true; s.src = src; document.head.appendChild(s); };
  const product = document.documentElement.dataset.product || 'DuoBytes';

  // ---- Google tag (GA4 + Ads share one gtag.js) ----
  window.dataLayer = window.dataLayer || [];
  window.gtag = function () { dataLayer.push(arguments); };
  if (set(C.GA4_ID) || set(C.GOOGLE_ADS_ID)) {
    load('https://www.googletagmanager.com/gtag/js?id=' + (set(C.GA4_ID) ? C.GA4_ID : C.GOOGLE_ADS_ID));
    gtag('js', new Date());
    if (set(C.GA4_ID)) gtag('config', C.GA4_ID, { product_line: product });
    if (set(C.GOOGLE_ADS_ID)) gtag('config', C.GOOGLE_ADS_ID);
  }

  // ---- Meta Pixel ----
  if (set(C.META_PIXEL_ID)) {
    !function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?n.callMethod.apply(n,arguments):n.queue.push(arguments)};
    if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;t.src=v;
    s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,document,'script','https://connect.facebook.net/en_US/fbevents.js');
    fbq('init', C.META_PIXEL_ID); fbq('track', 'PageView');
  }

  // ---- Microsoft Clarity ----
  if (set(C.CLARITY_ID)) {
    (function(c,l,a,r,i,t,y){c[a]=c[a]||function(){(c[a].q=c[a].q||[]).push(arguments)};
    t=l.createElement(r);t.async=1;t.src="https://www.clarity.ms/tag/"+i;y=l.getElementsByTagName(r)[0];y.parentNode.insertBefore(t,y);
    })(window, document, "clarity", "script", C.CLARITY_ID);
  }

  // ---- LinkedIn Insight ----
  if (set(C.LINKEDIN_ID)) {
    window._linkedin_partner_id = C.LINKEDIN_ID;
    window._linkedin_data_partner_ids = (window._linkedin_data_partner_ids || []).concat(C.LINKEDIN_ID);
    load('https://snap.licdn.com/li.lms-analytics/insight.min.js');
  }

  // ---- Remember where the visitor came from (UTM / gclid / fbclid) ----
  const KEYS = ['utm_source','utm_medium','utm_campaign','utm_term','utm_content','gclid','fbclid'];
  const qs = new URLSearchParams(location.search);
  try {
    if (KEYS.some(k => qs.get(k))) {
      const src = {}; KEYS.forEach(k => qs.get(k) && (src[k] = qs.get(k)));
      src.landing = location.pathname; sessionStorage.setItem('db_src', JSON.stringify(src));
    } else if (!sessionStorage.getItem('db_src') && document.referrer && !document.referrer.includes(location.host)) {
      sessionStorage.setItem('db_src', JSON.stringify({ referrer: new URL(document.referrer).hostname, landing: location.pathname }));
    }
  } catch (e) {}
  const source = () => { try { return JSON.parse(sessionStorage.getItem('db_src') || 'null'); } catch (e) { return null; } };

  // Add lead source to every WhatsApp demo message, so sales knows which ad brought the lead
  const _open = window.open;
  window.open = function (url, ...rest) {
    try {
      if (typeof url === 'string' && url.includes('wa.me') && url.includes('text=')) {
        const s = source(), u = new URL(url);
        const tag = s ? (s.utm_source ? `${s.utm_source}/${s.utm_medium || '-'}/${s.utm_campaign || '-'}` : s.gclid ? 'Google Ads' : s.fbclid ? 'Meta Ads' : s.referrer || 'direct') : 'direct';
        u.searchParams.set('text', u.searchParams.get('text') + `\nPage: ${location.pathname}\nSource: ${tag}`);
        url = u.toString();
      }
    } catch (e) {}
    return _open.call(window, url, ...rest);
  };

  // ---- Conversion events ----
  const fire = (name, params = {}) => {
    params = Object.assign({ product_line: product, page_path: location.pathname }, source() || {}, params);
    if (window.gtag) gtag('event', name, params);
    return params;
  };
  const adsConv = (label, value) => set(C.GOOGLE_ADS_ID) && set(label) &&
    gtag('event', 'conversion', { send_to: `${C.GOOGLE_ADS_ID}/${label}`, value: value || 1.0, currency: 'INR' });

  // Demo / enquiry form submitted (only when valid)
  document.addEventListener('submit', e => {
    const f = e.target; if (!f.matches('#demoForm')) return;
    const valid = [...f.querySelectorAll('[required]')].every(i => i.checkValidity());
    if (!valid) return;
    fire('generate_lead', { form: 'demo', currency: 'INR', value: 1 });
    adsConv(C.ADS_LEAD_LABEL);
    if (window.fbq) fbq('track', 'Lead', { content_name: product });
    if (window.lintrk && set(C.LINKEDIN_ID)) window.lintrk('track');
  }, true);

  // Call / WhatsApp / email clicks + key CTA clicks
  document.addEventListener('click', e => {
    const a = e.target.closest('a'); if (!a) return;
    const h = a.getAttribute('href') || '';
    if (h.startsWith('tel:'))         { fire('click_call');     adsConv(C.ADS_CALL_LABEL); window.fbq && fbq('track', 'Contact', { method: 'call' }); }
    else if (h.includes('wa.me'))     { fire('click_whatsapp'); adsConv(C.ADS_CALL_LABEL); window.fbq && fbq('track', 'Contact', { method: 'whatsapp' }); }
    else if (h.startsWith('mailto:')) { fire('click_email'); }
    else if (h === '#demo' || h.endsWith('#demo') || h === '#contact') fire('cta_click', { cta: a.textContent.trim().slice(0, 40) });
    else if (/^(\.\.\/)?(school-erp|sales-erp|hms|ffrs)\/?/.test(h)) fire('view_product_site', { target: h });
  });

  // Pricing section seen (good remarketing audience)
  const pr = document.getElementById('pricing');
  if (pr && 'IntersectionObserver' in window) {
    const io = new IntersectionObserver(es => { if (es[0].isIntersecting) { fire('view_pricing'); window.fbq && fbq('track', 'ViewContent', { content_name: product + ' pricing' }); io.disconnect(); } }, { threshold: .3 });
    io.observe(pr);
  }
})();
