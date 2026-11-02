<?php
/* Default email drafts – seeded once into the database, then editable from Admin › Templates.
   Placeholders: {{name|fallback}} {{institution|fallback}} {{city}} {{email}} {{sender_name}} {{sender_phone}}
                 {{sender_email}} {{site_url}} {{campaign}} {{unsubscribe_url}} */

function email_shell(string $accent, string $logo, string $inner, string $ctaText, string $ctaUrl): string {
    return <<<HTML
<!DOCTYPE html><html><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"></head>
<body style="margin:0;padding:0;background:#F2F5FA;font-family:Arial,Helvetica,sans-serif;color:#1F2A44">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#F2F5FA;padding:24px 12px"><tr><td align="center">
<table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;background:#ffffff;border-radius:12px;overflow:hidden;border:1px solid #E4E9F2">
<tr><td style="height:5px;background:{$accent}"></td></tr>
<tr><td style="padding:26px 32px 6px"><img src="{{site_url}}/{$logo}" alt="DuoBytes" width="150" style="display:block;width:150px;height:auto;border:0"></td></tr>
<tr><td style="padding:10px 32px 8px;font-size:15px;line-height:1.65">
{$inner}
</td></tr>
<tr><td style="padding:8px 32px 26px">
<a href="{$ctaUrl}" style="display:inline-block;background:{$accent};color:#ffffff;text-decoration:none;font-weight:bold;font-size:15px;padding:13px 26px;border-radius:8px">{$ctaText}</a>
</td></tr>
<tr><td style="padding:0 32px 26px;font-size:15px;line-height:1.6">
Warm regards,<br><b>{{sender_name}}</b><br>DuoBytes Technologies Pvt Ltd<br>
<a href="tel:{{sender_tel}}" style="color:#0A72E8;text-decoration:none">{{sender_phone}}</a> · <a href="{{site_url}}/?utm_source=email&utm_medium=email&utm_campaign={{campaign}}" style="color:#0A72E8;text-decoration:none">duobytes.in</a>
</td></tr>
<tr><td style="background:#F7F9FC;padding:16px 32px;font-size:12px;line-height:1.5;color:#7A859E;border-top:1px solid #E4E9F2">
You received this email because {{email}} is listed as a public contact for {{institution|your organisation}}. If you'd prefer not to hear from us, <a href="{{unsubscribe_url}}" style="color:#7A859E">unsubscribe here</a> and we won't email you again.<br>
DuoBytes Technologies Pvt Ltd · Maharashtra, India
</td></tr>
</table></td></tr></table></body></html>
HTML;
}

function default_templates(): array {
    $u = fn(string $path) => "{{site_url}}/{$path}?utm_source=email&utm_medium=email&utm_campaign={{campaign}}#demo";
    $li = fn(array $items) => '<ul style="padding-left:20px;margin:6px 0 14px">' . implode('', array_map(fn($i) => "<li style=\"margin-bottom:5px\">$i</li>", $items)) . '</ul>';

    return [
    [ 'slug' => 'school-erp-schools', 'name' => 'School ERP – Schools', 'product' => 'School ERP',
      'subject' => 'Save your school office 10+ hours a week, {{institution|}}',
      'html' => email_shell('#F36F21', 'school-erp/assets/logo-full.png', "
<p>Dear {{name|Principal}},</p>
<p>Most school offices still spend hours every week on attendance registers, fee follow-ups, report cards and certificates. <b>DuoBytes School ERP</b> puts all of it into one simple system, so {{institution|your school}} can get that time back.</p>
{$li(['<b>Fees:</b> online payment, automatic reminders and digital receipts', '<b>Exams:</b> marks entry to print-ready report cards in minutes, including the CBSE Holistic Progress Card (HPC)', '<b>Certificates:</b> Bonafide and Leaving Certificates in English &amp; Marathi in one click', '<b>Parent app:</b> attendance, homework, notices and bus tracking', '<b>Free data migration</b> and on-site training – live in 7–15 days'])}
<p>Can I show you a 30-minute live demo set up for your board and your formats? Just reply with a convenient time, or book below.</p>",
      'Book a free demo', $u('school-erp/')) ],

    [ 'slug' => 'school-erp-colleges', 'name' => 'School ERP – Colleges', 'product' => 'School ERP',
      'subject' => 'Admissions, fees & exams for {{institution|your college}} – in one system',
      'html' => email_shell('#F36F21', 'school-erp/assets/logo-full.png', "
<p>Dear {{name|Sir/Madam}},</p>
<p>Managing streams, divisions, semester exams and scholarships across spreadsheets is hard work. <b>DuoBytes ERP</b> helps junior and degree colleges run admissions, fees, attendance and results from one dashboard.</p>
{$li(['Online admission forms and merit lists', 'Stream / division-wise fee structures with instalments and online payment', 'Semester exams, internal assessment and marksheets', 'Scholarship tracking, Leaving Certificates and bonafides', 'Student &amp; faculty app, plus a college website connected to the ERP'])}
<p>Would a short demo for {{institution|your college}} be useful this week? Reply with a time that suits you.</p>",
      'See a 30-minute demo', $u('school-erp/')) ],

    [ 'slug' => 'sales-erp', 'name' => 'Sales ERP – Businesses', 'product' => 'Sales ERP',
      'subject' => 'Stop losing IndiaMART & WhatsApp leads, {{name|}}',
      'html' => email_shell('#F36F21', 'sales-erp/assets/logo-full.png', "
<p>Hi {{name|there}},</p>
<p>For most growing businesses, leads arrive from IndiaMART, Justdial, the website and WhatsApp – and many never get a timely follow-up. Orders then get re-typed into Excel, and complaints get lost in chats.</p>
<p><b>DuoBytes Sales ERP</b> joins it all into one flow – <b>Lead → Quote → Order → Invoice → Support</b>:</p>
{$li(['Leads auto-captured from IndiaMART, Justdial &amp; your website and assigned instantly', 'Follow-up reminders, click-to-call and WhatsApp built in', 'GST quotations and sales orders in one click', 'Support tickets linked to each customer’s orders', 'Field sales app with GPS check-ins and order booking'])}
<p>Plans start at ₹499 per user per month. Can I show you how it would work for {{institution|your team}}?</p>",
      'Book a free demo', $u('sales-erp/')) ],

    [ 'slug' => 'hms', 'name' => 'HMS – Hospitals & Clinics', 'product' => 'HMS',
      'subject' => 'A paperless OPD, IPD & billing system for {{institution|your hospital}}',
      'html' => email_shell('#0E9F7E', 'hms/assets/logo-full.png', "
<p>Dear {{name|Doctor}},</p>
<p>Long reception queues, handwritten case papers and services that never make it to the bill cost hospitals time and revenue every day. <b>DuoBytes HMS</b> connects every step of patient care on one record.</p>
{$li(['Online appointments, queue tokens and 1-minute registration', 'EMR and e-prescriptions for doctors', 'IPD, bed management, nursing station and OT', 'Pharmacy, lab (with analyser interfacing) and radiology', 'Automatic charge posting, TPA / insurance claims and ABDM (ABHA) readiness'])}
<p>Clinics can go live in a day and hospitals in 10–15 days, with on-site training. May I arrange a short demo for {{institution|your team}}?</p>",
      'Book a free demo', $u('hms/')) ],

    [ 'slug' => 'ffrs', 'name' => 'FFRS – Pharma / FMCG field teams', 'product' => 'FFRS',
      'subject' => 'Do you know what your field team did today?',
      'html' => email_shell('#7C3AED', 'ffrs/assets/logo-full.png', "
<p>Dear {{name|Sir/Madam}},</p>
<p>When daily reports come in over WhatsApp and Excel, managers can't verify visits, expense claims are hard to check, and month-end MIS arrives late. <b>DuoBytes FFRS (Field Force Reporting System)</b> gives you verified field data in real time.</p>
{$li(['GPS + selfie attendance with fake-location detection', 'Tour plans, geo-tagged doctor / retailer visits and DCR in under 2 minutes', 'Order booking, sample &amp; input tracking', 'Policy-based TA / DA claims with approvals', 'Live team map and dashboards – works offline too'])}
<p>Plans start at ₹299 per user per month and teams go live in about a week. Would you like a demo for {{institution|your company}}?</p>",
      'Book a free demo', $u('ffrs/')) ],

    [ 'slug' => 'company-intro', 'name' => 'DuoBytes – Company introduction', 'product' => 'Company',
      'subject' => 'Software for {{institution|your organisation}} – from DuoBytes Technologies',
      'html' => email_shell('#0B3A8C', 'assets/company-full.png', "
<p>Dear {{name|Sir/Madam}},</p>
<p>I'm reaching out from <b>DuoBytes Technologies Pvt Ltd</b>. We build ready-to-use software for Indian institutions and businesses, along with custom software, mobile apps and websites:</p>
{$li(['<b>School ERP</b> – admissions, fees, exams, certificates and a parent app', '<b>HMS</b> – hospital &amp; clinic management with pharmacy, lab and billing', '<b>Sales ERP</b> – leads, quotations, orders and customer support', '<b>FFRS</b> – field force attendance, visits, reporting and expenses'])}
<p>If any of these could help {{institution|your team}}, I'd be happy to share a short demo. Simply reply to this email.</p>",
      'Explore DuoBytes', '{{site_url}}/?utm_source=email&utm_medium=email&utm_campaign={{campaign}}#products') ],
    ];
}
