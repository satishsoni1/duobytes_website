/* ===== DuoBytes Sales ERP – site script ===== */

// ---- EDIT THESE: pricing (₹ per user per month) & contact ----
const CONFIG = {
  rates: { starter: 499, growth: 899, ent: 1499 },
  annualDiscount: 0.15,
  whatsapp: '919000000000'                   // demo form submits to this WhatsApp number
};

// ---- Lifecycle flow ----
const FLOW = [
  { icon:'magnet', name:'Capture', title:'Capture every lead automatically',
    desc:'Enquiries from IndiaMART, Justdial, TradeIndia, Facebook, Google Ads, your website and WhatsApp land in one inbox — de-duplicated and assigned in seconds.',
    pts:['Auto-import from 10+ sources','Round-robin / city / product assignment','Instant WhatsApp auto-reply','Duplicate detection'] },
  { icon:'phone-call', name:'Engage', title:'Follow up before competitors do',
    desc:'Click-to-call, auto-dialler, WhatsApp templates and email sequences — every interaction logged on the lead timeline.',
    pts:['Follow-up reminders','Call recording & dispositions','WhatsApp & email templates','Lead scoring'] },
  { icon:'kanban-square', name:'Close', title:'Move deals through your pipeline',
    desc:'Drag-and-drop pipelines with stages that match your process, win probability and forecasts that update themselves.',
    pts:['Multiple pipelines','Deal value & forecasting','Approval workflows','Win / loss reasons'] },
  { icon:'file-text', name:'Quote', title:'GST quotations in one click',
    desc:'Pick products from your price list, apply schemes and discounts, and send a branded PDF on WhatsApp or email.',
    pts:['Price lists & schemes','Auto CGST / SGST / IGST','Discount approvals','Quote versions & e-sign'] },
  { icon:'shopping-cart', name:'Order', title:'Convert quotes to orders instantly',
    desc:'Approved quotes become sales orders with stock check, dispatch planning and delivery tracking — no re-typing.',
    pts:['Sales orders & stock check','Dispatch & delivery status','Distributor / secondary orders','Credit-limit control'] },
  { icon:'receipt', name:'Invoice', title:'Invoice and collect faster',
    desc:'Generate GST invoices from orders, send payment links, track outstanding and push entries to Tally.',
    pts:['GST invoices & e-invoice ready','UPI / Razorpay payment links','Outstanding & ageing reports','Tally export'] },
  { icon:'life-buoy', name:'Support', title:'Resolve issues and win repeat business',
    desc:'Tickets from WhatsApp, email, phone and portal — linked to the customer\'s orders, with SLAs and feedback.',
    pts:['Multi-channel ticketing','SLA timers & escalations','Warranty / AMC tracking','CSAT feedback'] }
];

// ---- Role tabs ----
const ROLES = [
  { id:'rep', icon:'user-round', name:'Sales Rep', title:'Sales Rep Workspace',
    desc:'Today\'s follow-ups, hot leads and quotes in one view — so reps spend time selling, not searching.',
    pts:['My leads & follow-ups','Click-to-call & WhatsApp','Quotes in one click','Mobile app with offline mode','Targets vs achievement','Activity timeline'],
    mock:{h:'My Day',s:'Priya · Inside Sales',rows:[['Follow-ups due','14','warn'],['Hot leads','6','info'],['Quotes sent','4','ok'],['Target achieved','78%','ok']]} },
  { id:'tele', icon:'headphones', name:'Telecaller', title:'Telecaller Console',
    desc:'Auto-dialler queues, scripts and disposition codes for high-volume calling teams.',
    pts:['Auto-dialler queues','Call scripts','Call recording','Disposition codes','Callback scheduling','Agent productivity'],
    mock:{h:'Calling Queue',s:'Shift 1',rows:[['Calls made','112','ok'],['Connected','68%','info'],['Interested','19','ok'],['Callbacks','23','warn']]} },
  { id:'field', icon:'map-pinned', name:'Field Sales', title:'Field Sales App',
    desc:'Beat plans, GPS check-ins, order booking and expenses for reps on the road.',
    pts:['Beat & route planning','GPS attendance & check-in','Order booking at outlet','Catalogue & schemes','Collections','Expense claims'],
    mock:{h:'Pune West Beat',s:'Rahul · Today',rows:[['Visits planned','18','info'],['Completed','11','ok'],['Orders booked','₹1,42,600','ok'],['Pending visits','7','warn']]} },
  { id:'mgr', icon:'users', name:'Sales Manager', title:'Sales Manager Dashboard',
    desc:'Pipeline health, team performance and forecasts — live, without asking for reports.',
    pts:['Pipeline & forecast','Team leaderboard','Lead source ROI','Stuck-deal alerts','Approval queue','Live field map'],
    mock:{h:'Team Overview',s:'November',rows:[['Pipeline value','₹48.6L','info'],['Won this month','₹21.4L','ok'],['Deals stuck >7 days','12','warn'],['Conversion rate','14.8%','ok']]} },
  { id:'order', icon:'package-check', name:'Order Desk', title:'Order & Dispatch Desk',
    desc:'Every order from reps, distributors and the web in one queue — approved, dispatched and invoiced.',
    pts:['Order approval queue','Stock availability','Dispatch planning','Delivery tracking','GST invoicing','Returns & credit notes'],
    mock:{h:'Orders',s:'Today',rows:[['New orders','34','info'],['Awaiting approval','6','warn'],['Dispatched','22','ok'],['Invoices raised','₹8.9L','ok']]} },
  { id:'support', icon:'life-buoy', name:'Support Agent', title:'Support Helpdesk',
    desc:'Tickets from every channel with the customer\'s full order history beside them.',
    pts:['Unified ticket inbox','SLA timers','Canned replies','Order & warranty lookup','Escalation rules','Customer feedback'],
    mock:{h:'Helpdesk',s:'Live',rows:[['Open tickets','9','info'],['SLA breached','0','ok'],['Avg. resolution','3.2 hrs','ok'],['CSAT','4.7 / 5','ok']]} },
  { id:'owner', icon:'crown', name:'Owner', title:'Owner\'s Dashboard',
    desc:'Revenue, collections, orders and customer satisfaction — on one screen, on your phone.',
    pts:['Revenue & collections','Top customers & products','Branch / region comparison','Outstanding ageing','Support health','Daily WhatsApp summary'],
    mock:{h:'Business Overview',s:'This month',rows:[['Revenue','₹62.8L','ok'],['Collections','₹48.1L','info'],['Outstanding >60 days','₹6.2L','warn'],['Repeat customers','41%','ok']]} }
];

// ---- Modules ----
const MODULES = [
  ['Leads','inbox','Lead Capture','IndiaMART, Justdial, TradeIndia, Facebook, Google, website and WhatsApp leads in one place.'],
  ['Leads','shuffle','Auto Assignment','Round-robin, territory, product or load-based lead distribution.'],
  ['Leads','bell-ring','Follow-up Reminders','Scheduled tasks and alerts so no lead goes cold.'],
  ['Leads','gauge','Lead Scoring','Rank leads by source, activity and fit to focus on buyers.'],
  ['Leads','phone-call','Calling & Auto-dialler','Click-to-call, auto-dialler, recording and IVR integration.'],
  ['Leads','message-circle','WhatsApp Inbox','Two-way WhatsApp chats, templates and broadcasts.'],
  ['Leads','mail','Email & Sequences','Email sync, templates, tracking and drip follow-ups.'],
  ['Leads','megaphone','Campaigns','Bulk WhatsApp, SMS and email campaigns with tracking.'],
  ['Sales','kanban-square','Pipeline & Deals','Drag-and-drop pipelines with stages, values and probability.'],
  ['Sales','building','Accounts & Contacts','360° customer view with every call, chat, order and ticket.'],
  ['Sales','file-text','Quotations','GST quotes from price lists with schemes, discounts and approvals.'],
  ['Sales','tags','Products & Price Lists','Catalogue, variants, customer-wise prices and schemes.'],
  ['Sales','target','Targets & Incentives','Team targets, achievement tracking and incentive calculation.'],
  ['Sales','line-chart','Forecasting','Revenue forecasts by rep, product and region.'],
  ['Sales','map-pinned','Field Force App','GPS attendance, beat plans, check-ins and visit reports.'],
  ['Sales','wallet','Expense Claims','TA / DA and expense claims with approval workflow.'],
  ['Orders','shopping-cart','Sales Orders','Orders from quotes, reps, distributors or web with approval.'],
  ['Orders','warehouse','Stock & Warehouse','Stock visibility across warehouses and reserved quantity.'],
  ['Orders','truck','Dispatch & Delivery','Dispatch planning, LR / tracking numbers and delivery status.'],
  ['Orders','receipt','GST Invoicing','Invoices, e-invoice ready, credit notes and returns.'],
  ['Orders','credit-card','Payments & Collections','Payment links, receipts, outstanding and ageing.'],
  ['Orders','network','Distributor Portal','Dealers and distributors place orders and view ledger online.'],
  ['Orders','store','Secondary Sales','Retailer orders through distributors with scheme tracking.'],
  ['Orders','calculator','Tally Integration','Push invoices, receipts and masters to Tally.'],
  ['Support','life-buoy','Ticketing','Tickets from WhatsApp, email, phone and portal in one inbox.'],
  ['Support','timer','SLA & Escalation','Response and resolution timers with auto-escalation.'],
  ['Support','shield-check','Warranty & AMC','Warranty, AMC contracts, renewals and service visits.'],
  ['Support','wrench','Field Service','Assign technicians, track visits and capture job sheets.'],
  ['Support','book-open','Knowledge Base','Help articles and canned replies for faster answers.'],
  ['Support','smile','Customer Feedback','CSAT / NPS surveys after every resolved ticket.'],
  ['Support','bar-chart-3','Reports & Dashboards','100+ ready reports with Excel / PDF export.'],
  ['Support','lock-keyhole','Roles & Security','Role-based access, field-level permissions and audit logs.']
];

// ---- FAQ ----
const FAQS = [
  ['How is DuoBytes Sales ERP different from a normal CRM?','A normal CRM stops once a deal is won. DuoBytes continues into quotations, sales orders, dispatch, GST invoices, collections and customer support — so your whole customer journey lives in one system.'],
  ['Can it capture leads from IndiaMART and Justdial automatically?','Yes. IndiaMART, Justdial, TradeIndia, Facebook Lead Ads, Google Ads and website forms are connected out of the box, and leads are assigned automatically.'],
  ['Does it include WhatsApp and calling?','Yes. WhatsApp Business API, click-to-call, auto-dialler and call recording are built in. You can use your own SIM through the mobile app or a cloud telephony provider.'],
  ['Can we create GST quotations and invoices?','Yes. Quotations and invoices calculate CGST / SGST / IGST automatically from your product master, and can be exported to Tally.'],
  ['Is there a mobile app for field sales?','Yes — Android and iOS apps with GPS attendance, beat plans, check-ins, order booking, collections and expense claims. Key screens work offline.'],
  ['Can we use only the CRM now and add orders or support later?','Yes. Start with any plan and switch on modules as you grow. Your data stays in one place, so there is no migration.'],
  ['How long does implementation take?','Most teams go live in about 7 days, including configuration, data import, integrations and training.'],
  ['Can you customise fields, stages and print formats?','Yes. Our in-house team configures your pipelines, custom fields, quotation and invoice formats and reports.'],
  ['Is our data secure?','Data is hosted on secure Indian cloud servers with SSL encryption, role-based access, audit logs and daily backups. You can export your data anytime.']
];

// ---------- Render ----------
const $ = s => document.querySelector(s);
const icons = () => window.lucide && lucide.createIcons();

// Flow
function renderFlow(i){
  const f = FLOW[i];
  document.querySelectorAll('.fstep').forEach((s,j)=>{s.classList.toggle('on',j===i);s.classList.toggle('done',j<i);});
  $('#flowDetail').innerHTML = `
    <div class="fd-num">Step ${i+1} of ${FLOW.length}</div>
    <div class="fd-grid"><div><h3>${f.title}</h3><p>${f.desc}</p></div>
    <ul>${f.pts.map(p=>`<li><i data-lucide="check"></i>${p}</li>`).join('')}</ul></div>`;
  icons();
}
$('#flowSteps').innerHTML = FLOW.map((f,i)=>`<button class="fstep" data-i="${i}"><span class="fic"><i data-lucide="${f.icon}"></i></span><b>${f.name}</b></button>`).join('');
let flowIdx = 0, flowTimer;
const startFlow = () => { clearInterval(flowTimer); flowTimer = setInterval(()=>{flowIdx=(flowIdx+1)%FLOW.length;renderFlow(flowIdx);},4500); };
$('#flowSteps').addEventListener('click',e=>{const b=e.target.closest('.fstep');if(!b)return;flowIdx=+b.dataset.i;renderFlow(flowIdx);clearInterval(flowTimer);});
renderFlow(0);
if(!matchMedia('(prefers-reduced-motion:reduce)').matches) startFlow();

// Tabs
function renderRole(r){
  $('#rolePanel').innerHTML = `
    <div class="tp-copy"><h3>${r.title}</h3><p>${r.desc}</p>
      <ul>${r.pts.map(p=>`<li><i data-lucide="check"></i>${p}</li>`).join('')}</ul></div>
    <div class="tp-mock"><div class="mh"><b>${r.mock.h}</b><span>${r.mock.s}</span></div>
      ${r.mock.rows.map(([a,b,c])=>`<div class="mrow"><span>${a}</span><em class="${c}">${b}</em></div>`).join('')}</div>`;
  document.querySelectorAll('.tab').forEach(t=>t.classList.toggle('on',t.dataset.id===r.id));
  icons();
}
$('#roleTabs').innerHTML = ROLES.map(r=>`<button class="tab" role="tab" data-id="${r.id}"><i data-lucide="${r.icon}"></i>${r.name}</button>`).join('');
$('#roleTabs').addEventListener('click',e=>{const b=e.target.closest('.tab');if(b)renderRole(ROLES.find(r=>r.id===b.dataset.id));});
renderRole(ROLES[0]);

// Modules
const cats = ['All',...new Set(MODULES.map(m=>m[0]))];
$('#modCount').textContent = MODULES.length;
$('#modFilters').innerHTML = cats.map((c,i)=>`<button class="filter${i?'':' on'}" data-c="${c}">${c}</button>`).join('');
function renderMods(c){
  $('#modGrid').innerHTML = MODULES.filter(m=>c==='All'||m[0]===c).map((m,i)=>
    `<div class="mod" style="animation-delay:${i*25}ms"><div class="ic"><i data-lucide="${m[1]}"></i></div><h3>${m[2]}</h3><p>${m[3]}</p></div>`).join('');
  icons();
}
$('#modFilters').addEventListener('click',e=>{const b=e.target.closest('.filter');if(!b)return;
  document.querySelectorAll('.filter').forEach(f=>f.classList.toggle('on',f===b));renderMods(b.dataset.c);});
renderMods('All');

// FAQ
$('#faqList').innerHTML = FAQS.map(([q,a])=>`<div class="qa"><button>${q}<i data-lucide="plus"></i></button><div class="ans"><p>${a}</p></div></div>`).join('');
$('#faqList').addEventListener('click',e=>{const b=e.target.closest('button');if(!b)return;
  const qa=b.parentElement, ans=qa.querySelector('.ans'), open=qa.classList.toggle('open');
  ans.style.maxHeight = open ? ans.scrollHeight+'px' : 0;});

// Pricing
const inr = n => '₹'+Math.round(n).toLocaleString('en-IN');
document.querySelectorAll('[data-plan]').forEach(el=>el.textContent=inr(CONFIG.rates[el.dataset.plan]));
let billing='annual', plan='starter';
function calc(){
  const u=+$('#usr').value;
  $('#usrVal').textContent=u.toLocaleString('en-IN');
  const rate = CONFIG.rates[plan], monthly = u*rate;
  $('#rate').textContent=inr(rate); $('#monthly').textContent=inr(monthly);
  if(billing==='annual'){$('#totLbl').textContent=`Annual (${CONFIG.annualDiscount*100}% off)`;$('#annual').textContent=inr(monthly*12*(1-CONFIG.annualDiscount));}
  else{$('#totLbl').textContent='Annual (billed monthly)';$('#annual').textContent=inr(monthly*12);}
}
$('#usr').addEventListener('input',calc);
document.querySelectorAll('.bill button').forEach(b=>b.addEventListener('click',()=>{
  billing=b.dataset.bill;document.querySelectorAll('.bill button').forEach(x=>x.classList.toggle('on',x===b));calc();}));
document.querySelectorAll('#planPick button').forEach(b=>b.addEventListener('click',()=>{
  plan=b.dataset.p;document.querySelectorAll('#planPick button').forEach(x=>x.classList.toggle('on',x===b));calc();}));
calc();

// Demo form -> WhatsApp
$('#demoForm').addEventListener('submit',e=>{
  e.preventDefault();
  const f=e.target; let ok=true;
  f.querySelectorAll('[required]').forEach(i=>{const bad=!i.checkValidity();i.classList.toggle('err',bad);if(bad)ok=false;});
  const msg=$('#formMsg');
  if(!ok){msg.style.color='#DC2626';msg.textContent='Please fill the required fields.';return;}
  const d=Object.fromEntries(new FormData(f));
  const text=`Demo request – DuoBytes Sales ERP\nName: ${d.name}\nCompany: ${d.company}\nPhone: ${d.phone}\nEmail: ${d.email||'-'}\nIndustry: ${d.industry}\nTeam size: ${d.team}\nInterested in: ${d.need}`;
  msg.style.color='#15803D';msg.textContent='Thank you! Opening WhatsApp to confirm your demo…';
  window.open(`https://wa.me/${CONFIG.whatsapp}?text=${encodeURIComponent(text)}`,'_blank');
  f.reset();
});

// Header, menu, reveal, counters
const header=$('#header');
addEventListener('scroll',()=>header.classList.toggle('scrolled',scrollY>10),{passive:true});
$('#burger').addEventListener('click',()=>$('#menu').classList.toggle('open'));
document.querySelectorAll('#menu a').forEach(a=>a.addEventListener('click',()=>$('#menu').classList.remove('open')));

const io=new IntersectionObserver(es=>es.forEach(en=>{if(en.isIntersecting){en.target.classList.add('in');io.unobserve(en.target);}}),{threshold:.12});
document.querySelectorAll('.reveal').forEach(el=>io.observe(el));

document.querySelectorAll('[data-count]').forEach(el=>{
  const end=+el.dataset.count,t0=performance.now(),dur=1400;
  const step=t=>{const p=Math.min((t-t0)/dur,1);el.textContent=Math.round(end*(1-Math.pow(1-p,3))).toLocaleString('en-IN');if(p<1)requestAnimationFrame(step);};
  requestAnimationFrame(step);
});

$('#yr').textContent=new Date().getFullYear();
icons();
