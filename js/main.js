/* ===== DuoBytes Technologies – site script ===== */
const CONFIG = { product:'DuoBytes Technologies', subject:'Website enquiry – DuoBytes Technologies', whatsapp:'919000000000' };

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
