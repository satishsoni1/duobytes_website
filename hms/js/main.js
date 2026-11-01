/* ===== DuoBytes HMS – site data ===== */

// ---- EDIT THESE: pricing (₹) & contact ----
const CONFIG = {
  product: 'DuoBytes HMS',
  rates: { clinic: 1999, hospital: 299, multi: 449 },   // clinic = flat per month; others per bed per month
  min:   { hospital: 4999, multi: 9999 },               // minimum monthly bill
  annualDiscount: 0.15,
  whatsapp: '919000000000'
};

const FLOW = [
  { icon:'calendar-clock', name:'Book', title:'Online appointments & queue tokens',
    desc:'Patients book from your website, WhatsApp or the patient app. Reception sees the day\'s schedule, and a token display keeps the waiting room calm.',
    pts:['Doctor-wise slots','WhatsApp booking & reminders','Token display / kiosk','Walk-in & tele-consult'] },
  { icon:'id-card', name:'Register', title:'One UHID for life',
    desc:'Register in under a minute with ABHA, Aadhaar-based auto-fill or a returning patient search. Every visit links to the same record.',
    pts:['ABHA creation & linking','Duplicate detection','Referral doctor capture','Patient ID card / barcode'] },
  { icon:'stethoscope', name:'Consult', title:'Fast, structured consultations',
    desc:'Doctors see history, vitals and past reports at a glance, then write e-prescriptions and order tests with favourites and templates.',
    pts:['EMR with history & vitals','E-prescription templates','Order lab / radiology','Specialty-specific forms'] },
  { icon:'flask-conical', name:'Diagnose', title:'Lab and radiology, connected',
    desc:'Orders flow to the lab with barcodes; analyser results post automatically; reports reach the doctor and patient instantly.',
    pts:['Sample barcoding','Machine interfacing','Radiology reporting & PACS','Reports on WhatsApp'] },
  { icon:'bed-double', name:'Admit', title:'IPD, beds and nursing',
    desc:'Admit from OPD, allot beds, record nursing notes and medication administration, and schedule OT — all on the same file.',
    pts:['Live bed board','Nursing station & MAR','OT scheduling & notes','ICU charting'] },
  { icon:'receipt', name:'Bill', title:'Accurate billing, every time',
    desc:'Every service, medicine and bed day posts to the bill automatically. Packages, TPA, deposits and refunds handled with approvals.',
    pts:['Auto charge posting','Packages & TPA billing','Deposits & refunds','Pharmacy POS'] },
  { icon:'heart-handshake', name:'Discharge', title:'Smooth discharge & follow-up',
    desc:'Discharge summaries from templates, final settlement, and follow-up reminders that bring patients back.',
    pts:['Discharge summary templates','Final bill & settlement','Follow-up reminders','Patient feedback'] }
];

const ROLES = [
  { id:'front', icon:'concierge-bell', name:'Reception', title:'Front Desk',
    desc:'Register, book and bill in seconds — with a clear view of every doctor\'s schedule.',
    pts:['Quick registration','Appointment calendar','Queue tokens','OPD billing','Deposit collection','Enquiry desk'],
    mock:{h:'Front Desk',s:'Today',rows:[['Appointments','148','info'],['Walk-ins','66','info'],['Waiting now','12','warn'],['Collected','₹1,24,600','ok']]} },
  { id:'doctor', icon:'stethoscope', name:'Doctor', title:'Doctor Workspace',
    desc:'History, vitals and reports on one screen; prescriptions in a few clicks — on desktop, tablet or phone.',
    pts:['Patient history & EMR','E-prescriptions','Order tests','IPD rounds notes','Tele-consultation','My revenue share'],
    mock:{h:'Dr. Mehta · OPD',s:'Cardiology',rows:[['Patients seen','24 / 32','info'],['Reports to review','5','warn'],['IPD patients','7','info'],['Follow-ups today','9','ok']]} },
  { id:'nurse', icon:'syringe', name:'Nursing', title:'Nursing Station',
    desc:'Ward-wise patient list, vitals charting, medication schedules and handover notes.',
    pts:['Ward patient list','Vitals & I/O charts','Medication administration','Indent to pharmacy','Shift handover','Incident reporting'],
    mock:{h:'Ward 2 · Female',s:'Night shift',rows:[['Patients','18','info'],['Medications due','11','warn'],['Vitals pending','4','warn'],['Discharges','2','ok']]} },
  { id:'pharma', icon:'pill', name:'Pharmacy', title:'Pharmacy',
    desc:'OPD sales, IPD indents, purchases and stock with batch and expiry control.',
    pts:['Pharmacy POS','IPD indents','Purchase & GRN','Batch & expiry','Reorder alerts','Narcotics register'],
    mock:{h:'Pharmacy',s:'Today',rows:[['Sales','₹84,300','ok'],['Indents pending','7','warn'],['Near expiry','23 items','warn'],['Below reorder','6','info']]} },
  { id:'lab', icon:'microscope', name:'Lab', title:'Laboratory & Radiology',
    desc:'Sample tracking, machine results, validation and report delivery in one flow.',
    pts:['Sample collection','Barcode labels','Machine interfacing','Result validation','Report templates','Outsourced tests'],
    mock:{h:'Laboratory',s:'Live',rows:[['Samples collected','142','info'],['In process','28','warn'],['Validated','110','ok'],['Avg. TAT','2.4 hrs','ok']]} },
  { id:'billing', icon:'shield-check', name:'Billing & TPA', title:'Billing & Insurance Desk',
    desc:'IPD bills, packages, TPA pre-auth and claim tracking without chasing paper files.',
    pts:['IPD running bill','Package billing','TPA pre-auth','Claim tracking','Scheme billing','Refunds & approvals'],
    mock:{h:'TPA Desk',s:'This week',rows:[['Pre-auth pending','3','warn'],['Claims submitted','14','info'],['Settled','₹6.8L','ok'],['Rejected','1','warn']]} },
  { id:'admin', icon:'crown', name:'Management', title:'Management Dashboard',
    desc:'Occupancy, revenue, doctor performance and pending collections — live, on your phone.',
    pts:['Live occupancy','Department revenue','Doctor-wise OPD','Outstanding & TPA dues','Pharmacy margins','Branch comparison'],
    mock:{h:'Hospital Overview',s:'This month',rows:[['Revenue','₹1.62 Cr','ok'],['Bed occupancy','82%','info'],['TPA outstanding','₹18.4L','warn'],['New patients','2,140','ok']]} }
];

const MODULES = [
  ['Front Office','calendar-clock','Appointments','Doctor-wise slots, online booking, reminders and rescheduling.'],
  ['Front Office','id-card','Registration & UHID','Quick registration, ABHA linking and patient ID cards.'],
  ['Front Office','hash','Queue & Token','Token generation, display screens and self-service kiosk.'],
  ['Front Office','video','Tele-consultation','Video consults with e-prescription and online payment.'],
  ['Front Office','users','Referral Management','Referral doctors, sources and payout calculation.'],
  ['Front Office','smartphone','Patient App','Booking, reports, bills and reminders on mobile.'],
  ['Clinical','stethoscope','OPD & EMR','Structured case sheets, vitals, history and e-prescriptions.'],
  ['Clinical','bed-double','IPD & Bed Management','Admissions, transfers, live bed board and discharge.'],
  ['Clinical','syringe','Nursing Station','Vitals charting, MAR, care plans and shift handover.'],
  ['Clinical','activity','ICU / Critical Care','Hourly charting, ventilator and infusion records.'],
  ['Clinical','scissors','Operation Theatre','OT scheduling, checklists, anaesthesia and surgery notes.'],
  ['Clinical','baby','Maternity & Labour','ANC, partograph, delivery and newborn records.'],
  ['Clinical','droplet','Blood Bank','Donor, component, cross-match and issue tracking.'],
  ['Clinical','file-text','Discharge Summary','Specialty templates with auto-filled clinical data.'],
  ['Diagnostics','flask-conical','Laboratory (LIS)','Sample barcoding, machine interfacing and validation.'],
  ['Diagnostics','scan','Radiology (RIS)','Scheduling, reporting templates and PACS link.'],
  ['Diagnostics','send','Report Delivery','Reports on WhatsApp, email, app and portal.'],
  ['Diagnostics','building','Outsourced Tests','Track tests sent to partner labs and their results.'],
  ['Billing','receipt','OPD & IPD Billing','Auto charge posting, deposits, discounts and refunds.'],
  ['Billing','package','Packages','Surgical and health check-up packages with inclusions.'],
  ['Billing','shield-check','TPA & Insurance','Pre-auth, cashless, reimbursement and claim tracking.'],
  ['Billing','landmark','Government Schemes','PM-JAY / state scheme billing and documentation.'],
  ['Billing','credit-card','Online Payments','UPI, cards and payment links for bills and deposits.'],
  ['Billing','calculator','Accounts & Tally','Ledgers, day book and Tally export.'],
  ['Operations','pill','Pharmacy','POS, IPD indents, purchase, batch and expiry control.'],
  ['Operations','warehouse','Inventory & Purchase','Central store, departments, vendors and purchase orders.'],
  ['Operations','briefcase','HR & Payroll','Staff records, rosters, attendance and salary.'],
  ['Operations','sparkles','Housekeeping & CSSD','Bed cleaning, linen and sterilisation tracking.'],
  ['Operations','clipboard-check','Quality & NABH','Indicators, incident reports, audits and consents.'],
  ['Operations','badge-check','ABDM Integration','ABHA creation, health record sharing and consent.'],
  ['Operations','bar-chart-3','MIS & Dashboards','100+ reports on revenue, occupancy and performance.'],
  ['Operations','lock-keyhole','Roles & Security','Role-based access, audit trail and daily backups.']
];

const FAQS = [
  ['Is DuoBytes HMS suitable for a small clinic?','Yes. The Clinic plan covers appointments, queue tokens, EMR, e-prescriptions and billing, and can grow into a full hospital setup later without changing software.'],
  ['Is it ABDM / ABHA compliant?','DuoBytes HMS supports ABHA creation and linking and consent-based health record sharing as part of India\'s Ayushman Bharat Digital Mission.'],
  ['Can it handle TPA, insurance and government schemes?','Yes — pre-authorisation, cashless and reimbursement claims, package billing and scheme billing such as PM-JAY and state schemes.'],
  ['Can you connect our lab machines?','Yes. We interface common lab analysers so results post directly into reports, removing manual entry.'],
  ['Is it cloud-based or installed on our server?','It runs on secure cloud by default, so you can use it from any device. On-premise or private-cloud setup is available for larger hospitals.'],
  ['Can you migrate data from our current software?','Yes. Our team imports patient records, drug master, stock and tariffs from your existing software or Excel.'],
  ['How long does implementation take?','Clinics go live in 1–2 days; hospitals typically in 10–15 days, including configuration, data migration and on-site training.'],
  ['Is patient data secure?','Data is encrypted in transit and at rest, with role-based access, a full audit trail and daily backups on Indian servers.'],
  ['Do you provide on-site support?','Yes. We provide on-site go-live support and training, plus phone and WhatsApp support afterwards.']
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
