/* ===== DuoBytes School ERP – site script ===== */

// ---- EDIT THESE: pricing (₹ per student per month) & contact ----
const CONFIG = {
  rates: { starter: 10, pro: 8, ent: 6 },   // <=500, 501–1000, 1000+
  annualDiscount: 0.10,
  whatsapp: '919000000000'                   // demo form submits to this WhatsApp number
};

// ---- Role tabs ----
const ROLES = [
  { id:'admin', icon:'shield-check', name:'Admin', title:'Admin & Office',
    desc:'Control admissions, fees, staff and settings from a single console — with role-based access for every user.',
    pts:['Online admission & enquiry','Fee structure & concessions','Certificates & GR register','Role-based user access','SMS / WhatsApp broadcasts','Academic year rollover'],
    mock:{h:'Office Desk',s:'Today',rows:[['New admissions','12 pending','warn'],['Leaving Certificates issued','5','ok'],['Bonafide requests','14','info'],['SMS sent','1,184','ok']]} },
  { id:'teacher', icon:'presentation', name:'Teacher', title:'Teacher Workspace',
    desc:'Attendance, homework, marks entry and lesson plans — on mobile or desktop, in a few taps.',
    pts:['Class-wise attendance','Homework & assignments','Marks entry & grading','HPC assessments','Lesson planning','Timetable & substitutions'],
    mock:{h:'Class 7-A',s:'Period 3 · Science',rows:[['Attendance','38 / 40','ok'],['Homework submitted','31','info'],['Unit Test marks','Pending','warn'],['Next period','8-B Maths','info']]} },
  { id:'parent', icon:'heart-handshake', name:'Parent App', title:'Parent Mobile App',
    desc:'Parents stay informed without calling the school — attendance, homework, fees and notices on their phone.',
    pts:['Daily attendance alerts','Online fee payment','Homework & circulars','Report cards download','Live bus tracking','Leave requests'],
    mock:{h:'Aarav Sharma',s:'Class 5-B',rows:[['Attendance this month','96%','ok'],['Term 2 fees','₹12,500 due','warn'],['New circular','Annual Day','info'],['Bus ETA','6 min','ok']]} },
  { id:'student', icon:'graduation-cap', name:'Student', title:'Student Portal',
    desc:'Timetable, homework, online tests, study material and results in one student login.',
    pts:['Timetable & calendar','Online exams & quizzes','Study material','Results & report cards','Library books issued','Notices'],
    mock:{h:'My Day',s:'Class 9-C',rows:[['Maths quiz','Today 4 PM','info'],['Science project','Due Fri','warn'],['Library book','Return in 2 days','warn'],['Unit Test I','82%','ok']]} },
  { id:'accounts', icon:'indian-rupee', name:'Accounts', title:'Fees & Accounts',
    desc:'Collect fees online and at the counter, track dues and reconcile accounts without spreadsheets.',
    pts:['Online & counter collection','Auto fee reminders','Defaulter reports','Daily collection report','Expense & income ledger','Tally export'],
    mock:{h:'Collections',s:'October 2026',rows:[['Collected today','₹1,84,200','ok'],['Online payments','64%','info'],['Defaulters','86 students','warn'],['Receipts issued','212','ok']]} },
  { id:'principal', icon:'user-round-check', name:'Principal', title:"Principal's Dashboard",
    desc:'A live view of the whole school — attendance, finances, results and staff — to take decisions faster.',
    pts:['Live school KPIs','Class-wise results analysis','Staff attendance & leave','Fee collection trends','Approval workflows','Branch comparison'],
    mock:{h:'School Overview',s:'Live',rows:[['Student attendance','96.4%','ok'],['Staff on leave','3','warn'],['Fees collected','81%','info'],['Pass percentage (UT-II)','94%','ok']]} },
  { id:'hr', icon:'briefcase', name:'HR & Payroll', title:'HR & Payroll',
    desc:'Staff records, rosters, biometric attendance, leave and salary — processed in minutes each month.',
    pts:['Staff profiles & documents','Duty roster','Biometric attendance','Leave management','Salary slips (PF / PT)','Appraisals'],
    mock:{h:'Payroll',s:'Oct 2026',rows:[['Staff on payroll','65','info'],['Salary processed','Ready','ok'],['Leave applications','4 pending','warn'],['Late marks','7','warn']]} }
];

// ---- Modules ----
const MODULES = [
  ['Academics','user-plus','Admission & Enquiry','Online forms, enquiry follow-up, document upload and admission confirmation.'],
  ['Academics','users','Student Information','Complete student profile, GR number, siblings, documents and history.'],
  ['Academics','calendar-check','Attendance','Daily / period-wise attendance with instant parent alerts and reports.'],
  ['Academics','clock','Timetable','Class & teacher timetable with clash detection and substitutions.'],
  ['Academics','clipboard-list','Exams & Marks','Exam schedules, marks entry, grading rules and result processing.'],
  ['Academics','file-text','Report Cards','Board-wise report card and marksheet templates, bulk PDF printing.'],
  ['Academics','award','CBSE PARAKH HPC','Holistic Progress Cards for Foundational & Preparatory stages.'],
  ['Academics','book-open','Homework & Lesson Plan','Daily homework, assignments and teacher lesson planning.'],
  ['Academics','monitor-play','Online Exams','MCQ tests, auto-evaluation and instant results.'],
  ['Academics','library','Library','Book catalogue, issue/return, fines and barcode support.'],
  ['Finance','indian-rupee','Fee Management','Fee structures, instalments, concessions and late fees.'],
  ['Finance','credit-card','Online Payments','UPI, cards and net banking via payment gateway.'],
  ['Finance','receipt','Receipts & Dues','Digital receipts, due reminders and defaulter lists.'],
  ['Finance','wallet','Accounts & Expenses','Income, expense, ledger and daily cash book.'],
  ['Finance','banknote','Payroll','Salary structure, PF/PT deductions and salary slips.'],
  ['Finance','badge-percent','Scholarships','Scholarship schemes, eligibility and disbursement tracking.'],
  ['Operations','file-badge-2','Certificates','Bonafide, Leaving / Transfer, character certificates in English & Marathi.'],
  ['Operations','id-card','ID Cards','Student and staff ID card design and bulk printing.'],
  ['Operations','briefcase','HR & Staff','Staff records, documents, roster and leave management.'],
  ['Operations','fingerprint','Biometric Attendance','Integration with biometric / RFID devices for staff and students.'],
  ['Operations','bus','Transport','Routes, stops, vehicles, transport fees and GPS tracking.'],
  ['Operations','bed-double','Hostel','Rooms, allocation, hostel fees and visitor log.'],
  ['Operations','package','Inventory & Purchase','Stock, vendors, purchase orders and issue register.'],
  ['Operations','shirt','Uniform & Book Sale','Counter sale of uniforms, books and kits linked to students.'],
  ['Operations','user-check','Visitor & Gate Pass','Visitor log, student gate pass and early-leave approvals.'],
  ['Communication','smartphone','Parent & Student App','Android / iOS app with attendance, fees, homework and notices.'],
  ['Communication','message-square','SMS & WhatsApp','Bulk and automated messages with templates.'],
  ['Communication','megaphone','Notice Board & Circulars','Circulars, events and holiday calendar.'],
  ['Communication','globe','School Website','Mobile-friendly website with admissions and gallery, linked to ERP.'],
  ['Communication','bar-chart-3','Reports & Analytics','100+ ready reports with Excel / PDF export.'],
  ['Communication','building-2','Multi-Branch','Manage multiple schools with consolidated reports.'],
  ['Communication','lock-keyhole','Roles & Security','Role-based permissions, activity logs and daily backups.']
];

// ---- FAQ ----
const FAQS = [
  ['How much does DuoBytes School ERP cost?','Pricing is per student per month, with all modules included. Use the calculator above for an estimate, or book a demo for an exact quote for your school.'],
  ['How long does implementation take?','Most schools go live in 7–15 days, including setup of classes, fee structure, report card formats and data migration.'],
  ['Can you migrate data from our existing software or Excel?','Yes. Our team imports students, parents, staff, fee history and other records from Excel or your current software at no extra cost.'],
  ['Do you support CBSE HPC and Maharashtra State Board formats?','Yes. We support CBSE PARAKH Holistic Progress Cards, State Board marksheets, and certificates such as Bonafide and Leaving Certificate in English and Marathi.'],
  ['Can you customise report cards and certificates to our format?','Yes. Our in-house development team builds your exact formats, so printed documents match what your school already uses.'],
  ['Is there a mobile app for parents and teachers?','Yes — Android and iOS apps for parents, students and teachers, with attendance, homework, fees, notices and bus tracking.'],
  ['Is our school data secure?','Data is hosted on secure cloud servers with SSL encryption, role-based access, activity logs and daily backups. You can export your data anytime.'],
  ['What support do you provide?','Phone, WhatsApp and email support, plus on-site or online training for office staff and teachers.'],
  ['Do we need any special hardware?','No. The ERP runs in any browser on a computer, tablet or phone. Biometric devices, ID card printers and barcode scanners are optional.']
];

// ---------- Render ----------
const $ = s => document.querySelector(s);
const icons = () => window.lucide && lucide.createIcons();

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
document.querySelectorAll('[data-plan]').forEach(el=>el.textContent='₹'+CONFIG.rates[el.dataset.plan]);
let billing='annual';
const inr = n => '₹'+Math.round(n).toLocaleString('en-IN');
function calc(){
  const s=+$('#stu').value, b=+$('#br').value;
  $('#stuVal').textContent=s.toLocaleString('en-IN'); $('#brVal').textContent=b;
  const rate = s<=500?CONFIG.rates.starter:s<=1000?CONFIG.rates.pro:CONFIG.rates.ent;
  const total = s*b, monthly = total*rate;
  $('#rate').textContent='₹'+rate; $('#monthly').textContent=inr(monthly);
  if(billing==='annual'){$('#totLbl').textContent=`Annual (${CONFIG.annualDiscount*100}% off)`;$('#annual').textContent=inr(monthly*12*(1-CONFIG.annualDiscount));}
  else{$('#totLbl').textContent='Annual (billed monthly)';$('#annual').textContent=inr(monthly*12);}
}
['#stu','#br'].forEach(s=>$(s).addEventListener('input',calc));
document.querySelectorAll('.toggle button').forEach(b=>b.addEventListener('click',()=>{
  billing=b.dataset.bill;document.querySelectorAll('.toggle button').forEach(x=>x.classList.toggle('on',x===b));calc();}));
calc();

// Demo form -> WhatsApp
$('#demoForm').addEventListener('submit',e=>{
  e.preventDefault();
  const f=e.target; let ok=true;
  f.querySelectorAll('[required]').forEach(i=>{const bad=!i.checkValidity();i.classList.toggle('err',bad);if(bad)ok=false;});
  const msg=$('#formMsg');
  if(!ok){msg.style.color='#DC2626';msg.textContent='Please fill the required fields.';return;}
  const d=Object.fromEntries(new FormData(f));
  const text=`Demo request – DuoBytes School ERP%0AName: ${d.name}%0ASchool: ${d.school}%0APhone: ${d.phone}%0AEmail: ${d.email||'-'}%0ABoard: ${d.board}%0AStrength: ${d.strength}%0ATime: ${d.slot}`;
  msg.style.color='#15803D';msg.textContent='Thank you! Opening WhatsApp to confirm your demo…';
  window.open(`https://wa.me/${CONFIG.whatsapp}?text=${encodeURIComponent(decodeURIComponent(text))}`,'_blank');
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
