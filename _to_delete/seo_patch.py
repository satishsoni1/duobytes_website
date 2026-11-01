#!/usr/bin/env python3
"""Adds SEO tags, structured data, tracking script and legal links to all DuoBytes sites.
Safe to re-run: previous SEO block is replaced.  Change SITE if your domain differs."""
import re, json, os, sys
SITE = 'https://www.duobytes.in'
W = sys.argv[1] if len(sys.argv) > 1 else os.path.expanduser('~/mnt/website')
TODAY = '2026-11-01'

ORG = {"@type": "Organization", "@id": SITE + "/#org", "name": "DuoBytes Technologies Pvt Ltd",
       "alternateName": "DuoBytes", "url": SITE + "/", "logo": SITE + "/assets/company-full.png",
       "email": "info@duobytes.in", "telephone": "+91-9000000000",
       "address": {"@type": "PostalAddress", "addressRegion": "Maharashtra", "addressCountry": "IN"},
       "sameAs": []}

SITES = {
  '': dict(product='DuoBytes Technologies', path='/', img='assets/company-full.png', icon='assets/company-mark.png',
           title='DuoBytes Technologies Pvt Ltd | School ERP, Hospital, Sales & Field Force Software',
           desc=None, price=None),
  'school-erp': dict(product='DuoBytes School ERP', path='/school-erp/', img='assets/logo-full.png', icon='assets/logo-mark.png',
           cat='EducationalApplication', price='6', unit='per student per month',
           kw='school ERP, school management software, CBSE HPC, school fee software, report card software, Maharashtra'),
  'sales-erp': dict(product='DuoBytes Sales ERP', path='/sales-erp/', img='assets/logo-full.png', icon='assets/logo-mark.png',
           cat='BusinessApplication', price='499', unit='per user per month',
           kw='sales CRM India, lead management software, IndiaMART CRM, order management software, helpdesk software, WhatsApp CRM'),
  'hms': dict(product='DuoBytes HMS', path='/hms/', img='assets/logo-full.png', icon='assets/logo-mark.png',
           cat='MedicalApplication', price='299', unit='per bed per month',
           kw='hospital management software, HMS India, clinic software, EMR, pharmacy software, ABDM ABHA, TPA billing'),
  'ffrs': dict(product='DuoBytes FFRS', path='/ffrs/', img='assets/logo-full.png', icon='assets/logo-mark.png',
           cat='BusinessApplication', price='299', unit='per user per month',
           kw='field force reporting system, field force automation, MR reporting app, DCR software, pharma SFA, GPS attendance app'),
}

def faqs_from_js(js):
    m = re.search(r'const FAQS\s*=\s*\[(.*?)\];', js, re.S)
    if not m: return []
    out = []
    for q, a in re.findall(r"\[\s*'((?:[^'\\]|\\.)*)'\s*,\s*'((?:[^'\\]|\\.)*)'\s*\]", m.group(1)):
        out.append((q.replace("\\'", "'"), a.replace("\\'", "'")))
    return out

def esc(s): return s.replace('&', '&amp;').replace('"', '&quot;')

for folder, cfg in SITES.items():
    base = os.path.join(W, folder)
    f = os.path.join(base, 'index.html'); h = open(f, encoding='utf-8').read()
    pre = '' if folder == '' else '../'
    url = SITE + cfg['path']
    title = re.search(r'<title>(.*?)</title>', h, re.S).group(1)
    desc = re.search(r'<meta name="description" content="(.*?)">', h, re.S).group(1)
    ogt = re.search(r'<meta property="og:title" content="(.*?)">', h)
    ogt = ogt.group(1) if ogt else title

    # clean old tags / previous run
    h = re.sub(r'\n?<!-- SEO:start -->.*?<!-- SEO:end -->', '', h, flags=re.S)
    h = re.sub(r'\n?<meta property="og:(title|image)"[^>]*>', '', h)
    h = re.sub(r'\n?<link rel="icon"[^>]*>', '', h)
    h = re.sub(r'<html[^>]*>', f'<html lang="en-IN" data-product="{cfg["product"]}">', h, count=1)

    # structured data
    graph = [ORG]
    if folder == '':
        graph.append({"@type": "WebSite", "@id": SITE + "/#website", "url": SITE + "/", "name": "DuoBytes Technologies",
                      "publisher": {"@id": SITE + "/#org"}, "inLanguage": "en-IN"})
        graph.append({"@type": "ItemList", "name": "DuoBytes Products", "itemListElement": [
            {"@type": "ListItem", "position": i + 1, "name": SITES[k]['product'], "url": SITE + SITES[k]['path']}
            for i, k in enumerate(['school-erp', 'sales-erp', 'hms', 'ffrs'])]})
    else:
        graph.append({"@type": "SoftwareApplication", "name": cfg['product'], "url": url,
                      "image": url + cfg['img'], "description": desc,
                      "applicationCategory": cfg['cat'], "operatingSystem": "Web, Android, iOS",
                      "publisher": {"@id": SITE + "/#org"},
                      "offers": {"@type": "Offer", "price": cfg['price'], "priceCurrency": "INR",
                                 "description": "Starting price " + cfg['unit'] + ", excluding GST", "url": url + "#pricing"}})
        graph.append({"@type": "BreadcrumbList", "itemListElement": [
            {"@type": "ListItem", "position": 1, "name": "DuoBytes Technologies", "item": SITE + "/"},
            {"@type": "ListItem", "position": 2, "name": cfg['product'], "item": url}]})
        js = open(os.path.join(base, 'js', 'main.js'), encoding='utf-8').read()
        fq = faqs_from_js(js)
        if fq:
            graph.append({"@type": "FAQPage", "mainEntity": [
                {"@type": "Question", "name": q, "acceptedAnswer": {"@type": "Answer", "text": a}} for q, a in fq]})
    ld = json.dumps({"@context": "https://schema.org", "@graph": graph}, ensure_ascii=False, indent=1)

    kw = f'\n<meta name="keywords" content="{cfg["kw"]}">' if cfg.get('kw') else ''
    verify = ('\n<!-- Paste Google Search Console & Bing verification tags here if you use the "HTML tag" method -->'
              '\n<!-- <meta name="google-site-verification" content="XXXX"> -->'
              '\n<!-- <meta name="msvalidate.01" content="XXXX"> -->') if folder == '' else ''
    block = f'''
<!-- SEO:start -->
<meta name="robots" content="index, follow, max-image-preview:large">{kw}
<link rel="canonical" href="{url}">
<link rel="alternate" hreflang="en-IN" href="{url}">
<link rel="icon" type="image/png" href="{cfg['icon']}">
<link rel="apple-touch-icon" href="{cfg['icon']}">
<meta property="og:type" content="website">
<meta property="og:site_name" content="DuoBytes Technologies">
<meta property="og:locale" content="en_IN">
<meta property="og:url" content="{url}">
<meta property="og:title" content="{esc(ogt)}">
<meta property="og:description" content="{desc}">
<meta property="og:image" content="{url}{cfg['img']}">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{esc(ogt)}">
<meta name="twitter:description" content="{desc}">
<meta name="twitter:image" content="{url}{cfg['img']}">{verify}
<script type="application/ld+json">
{ld}
</script>
<script src="{pre}js/track.js"></script>
<!-- SEO:end -->'''
    h = re.sub(r'(<meta name="description" content=".*?">)', lambda m: m.group(1) + block, h, count=1, flags=re.S)

    # legal links in footer
    h = re.sub(r'<span><a href="[^"]*">Privacy Policy</a> · <a href="[^"]*">Terms</a> · <a href="[^"]*">Refund Policy</a></span>',
               f'<span><a href="{pre}privacy-policy.html">Privacy Policy</a> · <a href="{pre}terms.html">Terms</a> · <a href="{pre}terms.html#refund">Refund Policy</a></span>', h)
    # images: lazy-load footer logos, alt for decorative marks already empty
    h = h.replace('<div class="foot-logo"><img ', '<div class="foot-logo"><img loading="lazy" ')
    open(f, 'w', encoding='utf-8').write(h)
    print('patched', folder or 'root', '| FAQ schema:', 'yes' if folder and fq else '-')

# sitemap
pages = [('/', '1.0'), ('/school-erp/', '0.9'), ('/sales-erp/', '0.9'), ('/hms/', '0.9'), ('/ffrs/', '0.9'),
         ('/privacy-policy.html', '0.3'), ('/terms.html', '0.3')]
sm = '<?xml version="1.0" encoding="UTF-8"?>\n<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">\n' + ''.join(
    f'  <url><loc>{SITE}{p}</loc><lastmod>{TODAY}</lastmod><changefreq>monthly</changefreq><priority>{pr}</priority></url>\n'
    for p, pr in pages) + '</urlset>\n'
open(os.path.join(W, 'sitemap.xml'), 'w').write(sm)
open(os.path.join(W, 'robots.txt'), 'w').write(
    f'User-agent: *\nAllow: /\nDisallow: /_to_delete/\nDisallow: /*.zip$\n\nSitemap: {SITE}/sitemap.xml\n')
print('sitemap.xml + robots.txt written')
