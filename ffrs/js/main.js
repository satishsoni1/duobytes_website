/* ===== DuoBytes FFRS – site data ===== */

// ---- EDIT THESE: pricing (₹ per user per month) & contact ----
const CONFIG = {
  product: 'DuoBytes FFRS',
  rates: { basic: 299, pro: 499, ent: 799 },
  annualDiscount: 0.15,
  whatsapp: '919000000000'
};

const FLOW = [
  { icon:'calendar-range', name:'Plan', title:'Monthly tour plans, approved upfront',
    desc:'Reps plan their month by area, doctor or beat. Managers approve in one tap and see deviations the moment they happen.',
    pts:['Monthly / weekly tour plan','Joint-work planning','Manager approvals','Deviation alerts'] },
  { icon:'scan-face', name:'Start', title:'Verified attendance',
    desc:'Day start with selfie and GPS; mock-location detection stops fake check-ins. Leave and holidays handled in the same app.',
    pts:['Selfie + GPS punch','Fake-GPS detection','Leave requests','Working-with tracking'] },
  { icon:'map-pin', name:'Visit', title:'Geo-tagged visits',
    desc:'Check in at each doctor, chemist, retailer or dealer — time, location and duration recorded automatically.',
    pts:['Geo-fenced check-in','Visit duration','Photos & notes','Next-visit reminders'] },
  { icon:'presentation', name:'Detail', title:'Products, samples and feedback',
    desc:'Record products detailed, samples and inputs given, competitor activity and customer feedback during the visit.',
    pts:['Product detailing','Sample & gift issue','Competitor / RCPA notes','Digital visual aids'] },
  { icon:'shopping-cart', name:'Order', title:'Orders booked on the spot',
    desc:'Book orders with schemes and send them straight to the stockist or distributor — with a copy to the customer on WhatsApp.',
    pts:['Order booking (POB)','Schemes & price lists','Stockist routing','Order status'] },
  { icon:'clipboard-check', name:'Report', title:'DCR in under two minutes',
    desc:'The daily call report builds itself from the day\'s visits. Reps review, add remarks and submit — managers see it instantly.',
    pts:['Auto-built DCR','Missed-call reasons','Manager review','Lock & reminders'] },
  { icon:'wallet', name:'Claim', title:'Expenses calculated, not typed',
    desc:'TA / DA computed from distance travelled and your policy. Bills attached as photos; multi-level approvals with payroll export.',
    pts:['Distance-based TA','HQ / ex-HQ / OS DA','Bill photo upload','Approval workflow'] }
];

const ROLES = [
  { id:'rep', icon:'user-round', name:'Field Rep', title:'Rep Mobile App',
    desc:'Everything a rep needs for the day — plan, visits, orders, samples and expenses — in one simple app that works offline.',
    pts:['Today\'s plan','One-tap check-in','Order booking','Sample stock','DCR submit','Expense claim'],
    mock:{h:'My Day',s:'Amit · Pune HQ',rows:[['Planned calls','16','info'],['Completed','12','ok'],['Orders booked','₹42,600','ok'],['DCR','Pending','warn']]} },
  { id:'abm', icon:'users', name:'Area Manager', title:'First-line Manager',
    desc:'See your team live, approve plans and claims, and plan joint work where it matters.',
    pts:['Live team map','Tour plan approvals','DCR review','Joint-work reports','Expense approvals','Coverage gaps'],
    mock:{h:'Pune Area Team',s:'8 reps',rows:[['In field','8 / 8','ok'],['Call average','11.4','info'],['Plans to approve','3','warn'],['Claims to approve','5','warn']]} },
  { id:'rbm', icon:'map', name:'Regional Head', title:'Regional / Zonal Dashboard',
    desc:'Territory-wise coverage, call averages, sales and productivity across all areas.',
    pts:['Territory performance','Coverage & frequency','Sales vs target','Rep productivity','Route replay','Exception reports'],
    mock:{h:'West Zone',s:'This month',rows:[['Coverage','86%','ok'],['Call average','10.8','info'],['Target achieved','74%','warn'],['Inactive reps','2','warn']]} },
  { id:'ho', icon:'building-2', name:'Head Office', title:'Head Office & MIS',
    desc:'Company-wide field MIS, masters, policies and integrations managed centrally.',
    pts:['Company-wide MIS','Masters & hierarchy','Expense policy setup','Sample allocation','ERP / payroll export','Audit logs'],
    mock:{h:'All India',s:'Live',rows:[['Field users','1,240','info'],['Present today','94%','ok'],['DCR compliance','91%','ok'],['Claims pending','₹3.2L','warn']]} },
  { id:'acct', icon:'indian-rupee', name:'Accounts', title:'Expense & Payroll Desk',
    desc:'Policy-checked claims, approval status and one-click payroll export.',
    pts:['Policy-validated claims','Bill verification','Approval status','Payroll export','Advance tracking','Expense analytics'],
    mock:{h:'Expenses',s:'October',rows:[['Claims received','1,184','info'],['Auto-approved','82%','ok'],['Flagged','46','warn'],['Paid','₹28.6L','ok']]} }
];

const MODULES = [
  ['Field','scan-face','GPS & Selfie Attendance','Day start / end with selfie, location and mock-GPS detection.'],
  ['Field','calendar-range','Tour Plan','Monthly and weekly plans with approvals and deviation alerts.'],
  ['Field','map-pin','Visit Check-in','Geo-fenced check-in and check-out with duration and photos.'],
  ['Field','route','Beat / Route Planning','Beats, patches and optimised routes for every rep.'],
  ['Field','clipboard-check','Daily Call Report','Auto-built DCR from visits with remarks and lock rules.'],
  ['Field','presentation','Digital Detailing','Product visual aids on the app with view tracking.'],
  ['Field','wifi-off','Offline Mode','Full reporting without network; auto-sync later.'],
  ['Field','calendar-x','Leave & Holidays','Leave requests, holiday calendar and approvals.'],
  ['Customers','stethoscope','Doctor / Customer Master','Doctors, chemists, retailers and dealers with categories.'],
  ['Customers','list-checks','MCL & Call Frequency','Must-call lists, visit frequency and coverage tracking.'],
  ['Customers','gift','Samples & Inputs','Allocation, issue and balance of samples, gifts and literature.'],
  ['Customers','search-check','RCPA & Competitor','Retail prescription audits and competitor activity.'],
  ['Customers','camera','Merchandising Photos','Shelf and display photos with audit checklists.'],
  ['Customers','users-round','Meetings & Events','CMEs, farmer meetings and dealer meets with attendance.'],
  ['Sales','shopping-cart','Order Booking (POB)','Orders with schemes, routed to stockist or distributor.'],
  ['Sales','store','Stockist & Distributor','Stockist mapping, order status and stock statements.'],
  ['Sales','trending-up','Secondary Sales','Secondary sales upload and analysis by territory.'],
  ['Sales','target','Targets & Achievement','Product-wise and territory-wise targets.'],
  ['Sales','award','Incentives','Incentive rules calculated from sales and activity.'],
  ['Sales','credit-card','Collections','Payment collection and outstanding follow-up.'],
  ['Expenses','wallet','TA / DA Claims','Distance and policy-based travel and daily allowance.'],
  ['Expenses','receipt','Bill Upload','Photo upload of tickets, hotel and other bills.'],
  ['Expenses','check-check','Multi-level Approval','Manager, HR and accounts approvals with comments.'],
  ['Expenses','file-spreadsheet','Payroll Export','One-click export to payroll or accounting.'],
  ['Management','map','Live Team Map','Real-time location of every field user.'],
  ['Management','history','Route Replay','Replay any rep\'s route and visits for a day.'],
  ['Management','network','Hierarchy & Territory','Unlimited levels, HQs, areas, regions and zones.'],
  ['Management','bar-chart-3','Dashboards & MIS','Coverage, call average, productivity and sales.'],
  ['Management','bell-ring','Alerts & Exceptions','Off-route, late start, missed DCR and low coverage.'],
  ['Management','megaphone','Announcements','Circulars, product updates and quizzes for reps.'],
  ['Management','languages','Multi-language App','English and regional language interface.'],
  ['Management','lock-keyhole','Roles & Security','Role-based access, device binding and audit logs.']
];

const FAQS = [
  ['What is a Field Force Reporting System?','It\'s software that records what your field team does every day — attendance, visits, orders, samples and expenses — through a mobile app, and gives managers live dashboards and reports.'],
  ['Does it work for pharma medical reps?','Yes. FFRS supports doctor and chemist masters, MCL, call planning, detailing, samples, RCPA, stockist orders and pharma-style DCR formats.'],
  ['Will it work where there is no network?','Yes. The app works offline and syncs automatically when the phone reconnects.'],
  ['How do you stop fake attendance or fake locations?','Selfie verification, geo-fenced check-ins and mock-location detection flag suspicious entries for managers.'],
  ['Does GPS tracking drain the phone battery?','Tracking is optimised to record location only at key events and intervals during working hours, keeping battery use low.'],
  ['Can expense rules match our company policy?','Yes. HQ, ex-HQ and outstation rules, per-km rates, fare caps and grade-wise limits are configured to your policy.'],
  ['Can it integrate with our ERP or payroll?','Yes. We integrate with Tally, SAP and other ERPs, and export expenses to payroll.'],
  ['How long does it take to go live?','Most teams go live in about 7 days, including master import, configuration and training.'],
  ['Is there an iOS app?','Yes — Android and iOS apps, plus a web dashboard for managers and head office.']
];

// ---------- Shared render engine (DuoBytes product sites) ----------
const $ = s => document.querySelector(s);
const icons = () => window.lucide && lucide.createIcons();
const inr = n => '₹'+Math.round(n).toLocaleString('en-IN');

// Flow
if ($('#flowSteps')) {
  const renderFlow = i => {
    const f = FLOW[i];
    document.querySelectorAll('.fstep').forEach((s,j)=>{s.classList.toggle('on',j===i);s.classList.toggle('done',j<i);});
    $('#flowDetail').innerHTML = `<div class="fd-num">Step ${i+1} of ${FLOW.length}</div>
      <div class="fd-grid"><div><h3>${f.title}</h3><p>${f.desc}</p></div>
      <ul>${f.pts.map(p=>`<li><i data-lucide="check"></i>${p}</li>`).join('')}</ul></div>`;
    icons();
  };
  $('#flowSteps').innerHTML = FLOW.map((f,i)=>`<button class="fstep" data-i="${i}"><span class="fic"><i data-lucide="${f.icon}"></i></span><b>${f.name}</b></button>`).join('');
  let idx = 0, timer;
  $('#flowSteps').addEventListener('click',e=>{const b=e.target.closest('.fstep');if(!b)return;idx=+b.dataset.i;renderFlow(idx);clearInterval(timer);});
  renderFlow(0);
  if(!matchMedia('(prefers-reduced-motion:reduce)').matches) timer=setInterval(()=>{idx=(idx+1)%FLOW.length;renderFlow(idx);},4500);
}

// Role tabs
if ($('#roleTabs')) {
  const renderRole = r => {
    $('#rolePanel').innerHTML = `<div class="tp-copy"><h3>${r.title}</h3><p>${r.desc}</p>
        <ul>${r.pts.map(p=>`<li><i data-lucide="check"></i>${p}</li>`).join('')}</ul></div>
      <div class="tp-mock"><div class="mh"><b>${r.mock.h}</b><span>${r.mock.s}</span></div>
        ${r.mock.rows.map(([a,b,c])=>`<div class="mrow"><span>${a}</span><em class="${c}">${b}</em></div>`).join('')}</div>`;
    document.querySelectorAll('.tab').forEach(t=>t.classList.toggle('on',t.dataset.id===r.id));
    icons();
  };
  $('#roleTabs').innerHTML = ROLES.map(r=>`<button class="tab" role="tab" data-id="${r.id}"><i data-lucide="${r.icon}"></i>${r.name}</button>`).join('');
  $('#roleTabs').addEventListener('click',e=>{const b=e.target.closest('.tab');if(b)renderRole(ROLES.find(r=>r.id===b.dataset.id));});
  renderRole(ROLES[0]);
}

// Modules
if ($('#modGrid')) {
  const cats = ['All',...new Set(MODULES.map(m=>m[0]))];
  $('#modCount').textContent = MODULES.length;
  $('#modFilters').innerHTML = cats.map((c,i)=>`<button class="filter${i?'':' on'}" data-c="${c}">${c}</button>`).join('');
  const renderMods = c => {
    $('#modGrid').innerHTML = MODULES.filter(m=>c==='All'||m[0]===c).map((m,i)=>
      `<div class="mod" style="animation-delay:${i*25}ms"><div class="ic"><i data-lucide="${m[1]}"></i></div><h3>${m[2]}</h3><p>${m[3]}</p></div>`).join('');
    icons();
  };
  $('#modFilters').addEventListener('click',e=>{const b=e.target.closest('.filter');if(!b)return;
    document.querySelectorAll('.filter').forEach(f=>f.classList.toggle('on',f===b));renderMods(b.dataset.c);});
  renderMods('All');
}

// FAQ
if ($('#faqList')) {
  $('#faqList').innerHTML = FAQS.map(([q,a])=>`<div class="qa"><button>${q}<i data-lucide="plus"></i></button><div class="ans"><p>${a}</p></div></div>`).join('');
  $('#faqList').addEventListener('click',e=>{const b=e.target.closest('button');if(!b)return;
    const qa=b.parentElement, ans=qa.querySelector('.ans'), open=qa.classList.toggle('open');
    ans.style.maxHeight = open ? ans.scrollHeight+'px' : 0;});
}

// Pricing calculator
if ($('#usr')) {
  document.querySelectorAll('[data-plan]').forEach(el=>el.textContent=inr(CONFIG.rates[el.dataset.plan]));
  let billing='annual', plan=document.querySelector('#planPick button.on').dataset.p;
  const calc = () => {
    const u=+$('#usr').value;
    $('#usrVal').textContent=u.toLocaleString('en-IN');
    const rate=CONFIG.rates[plan], monthly=Math.max(u*rate,(CONFIG.min||{})[plan]||0);
    $('#rate').textContent=inr(rate); $('#monthly').textContent=inr(monthly);
    if(billing==='annual'){$('#totLbl').textContent=`Annual (${CONFIG.annualDiscount*100}% off)`;$('#annual').textContent=inr(monthly*12*(1-CONFIG.annualDiscount));}
    else{$('#totLbl').textContent='Annual (billed monthly)';$('#annual').textContent=inr(monthly*12);}
  };
  $('#usr').addEventListener('input',calc);
  document.querySelectorAll('.bill button').forEach(b=>b.addEventListener('click',()=>{
    billing=b.dataset.bill;document.querySelectorAll('.bill button').forEach(x=>x.classList.toggle('on',x===b));calc();}));
  document.querySelectorAll('#planPick button').forEach(b=>b.addEventListener('click',()=>{
    plan=b.dataset.p;document.querySelectorAll('#planPick button').forEach(x=>x.classList.toggle('on',x===b));calc();}));
  calc();
}

// Demo form -> WhatsApp (uses each field's label)
if ($('#demoForm')) {
  $('#demoForm').addEventListener('submit',e=>{
    e.preventDefault();
    const f=e.target; let ok=true;
    f.querySelectorAll('[required]').forEach(i=>{const bad=!i.checkValidity();i.classList.toggle('err',bad);if(bad)ok=false;});
    const msg=$('#formMsg');
    if(!ok){msg.style.color='#DC2626';msg.textContent='Please fill the required fields.';return;}
    const lines=[...f.querySelectorAll('input,select,textarea')].filter(i=>i.name).map(i=>{
      const lbl=(i.closest('label')?.childNodes[0]?.textContent||i.name).replace('*','').trim();
      return `${lbl}: ${i.value||'-'}`;});
    const text=(CONFIG.subject||`Demo request – ${CONFIG.product}`)+'\n'+lines.join('\n');
    msg.style.color='#15803D';msg.textContent='Thank you! Opening WhatsApp to confirm…';
    window.open(`https://wa.me/${CONFIG.whatsapp}?text=${encodeURIComponent(text)}`,'_blank');
    f.reset();
  });
}

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
