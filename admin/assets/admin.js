/* DuoBytes Admin UI */
const CSRF = document.querySelector('meta[name=csrf]')?.content || '';
const $ = s => document.querySelector(s);
async function api(data) {
  const r = await fetch('api.php', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF': CSRF }, body: JSON.stringify(data) });
  if (r.status === 401) { location.href = 'login.php?expired=1'; throw new Error('Logged out'); }
  return r.json();
}
const sleep = ms => new Promise(r => setTimeout(r, ms));

/* ---------- Template / campaign editor: live preview + test send ---------- */
if ($('#prev')) {
  let t;
  const refresh = async () => {
    const res = await api({ act: 'preview', subject: $('#subj').value, html: $('#html').value, campaign: window.CAMPAIGN_ID || 0 });
    if (res.error) return;
    $('#subjPrev').textContent = res.subject;
    $('#prev').srcdoc = res.html;
  };
  ['#subj', '#html'].forEach(s => $(s).addEventListener('input', () => { clearTimeout(t); t = setTimeout(refresh, 500); }));
  refresh();
  $('#testForm')?.addEventListener('submit', async e => {
    e.preventDefault(); const m = $('#testMsg'); m.className = 'small'; m.textContent = 'Sending…';
    const res = await api({ act: 'send_test', to: $('#testTo').value, subject: $('#subj').value, html: $('#html').value });
    m.className = 'small ' + (res.error ? 'err-t' : 'ok-t'); m.textContent = res.error || res.msg;
  });
}

/* ---------- Settings: test SMTP ---------- */
$('#testSmtp')?.addEventListener('click', async () => {
  const m = $('#smtpMsg'); m.className = 'small'; m.textContent = 'Connecting… (save settings first if you changed them)';
  const res = await api({ act: 'test_smtp' });
  m.className = 'small ' + (res.error ? 'err-t' : 'ok-t'); m.textContent = res.error || res.msg;
});

/* ---------- Campaign runner ---------- */
const run = $('#run');
if (run) {
  const id = +run.dataset.id, dmin = +run.dataset.dmin, dmax = +run.dataset.dmax;
  let running = false, timer = null;
  const badge = s => ({ draft: 'grey', running: 'blue', paused: 'amber', completed: 'green' }[s] || 'grey');
  const msg = (t, cls = '') => { const m = $('#runMsg'); m.className = 'small ' + cls; m.textContent = t; };
  const log = (t, cls = '') => { const d = document.createElement('div'); d.className = cls; d.textContent = new Date().toLocaleTimeString() + '  ' + t; $('#log').prepend(d); };
  const paint = s => {
    if (!s || s.total === undefined) return;
    document.querySelectorAll('#kv b').forEach(b => b.textContent = s[b.dataset.k] ?? b.textContent);
    $('#barFill').style.width = (s.total ? Math.round((s.sent + s.failed + s.skipped) * 100 / s.total) : 0) + '%';
    $('#status').innerHTML = `<span class="badge ${badge(s.status)}">${s.status[0].toUpperCase() + s.status.slice(1)}</span>`;
    const q = document.querySelector('.quota b'); if (q && s.today !== undefined) q.textContent = s.today;
  };
  const stop = (text, cls) => { running = false; clearTimeout(timer); msg(text, cls); $('#btnStart').disabled = false; $('#btnStart').textContent = 'Resume sending'; window.onbeforeunload = null; };

  async function loop() {
    if (!running) return;
    let res;
    try { res = await api({ act: 'send_next', id }); } catch (e) { log('Network error – retrying in 30s', 'err-t'); timer = setTimeout(loop, 30000); return; }
    if (res.error && !res.last) { stop(res.error, 'err-t'); return; }
    paint(res);
    if (res.last) log(`${res.last.status.toUpperCase()}  ${res.last.email}${res.last.error ? ' – ' + res.last.error : ''}`, res.last.status === 'sent' ? 'ok-t' : res.last.status === 'failed' ? 'err-t' : '');
    if (res.stopped) {
      if (res.stopped === 'completed') { stop('✔ Campaign completed.', 'ok-t'); $('#btnStart').disabled = true; $('#btnStart').textContent = 'Completed'; }
      else if (res.stopped === 'limit') stop(`Daily limit reached (${res.today}/${res.limit}). Paused – press Resume tomorrow.`, 'warn-t');
      else if (res.stopped === 'error') stop('Paused because of an account/SMTP problem: ' + res.error, 'err-t');
      else stop('Paused.', '');
      return;
    }
    const wait = (dmin + Math.random() * (dmax - dmin)) * 1000;
    msg(`Sending… next email in ${Math.round(wait / 1000)}s. Keep this tab open. (${res.queued} left, ${res.today}/${res.limit} today)`);
    timer = setTimeout(loop, wait);
  }

  $('#btnStart').addEventListener('click', async () => {
    if (!confirm('Start sending this campaign now?')) return;
    const res = await api({ act: 'start', id });
    if (res.error) { msg(res.error, 'err-t'); return; }
    paint(res); running = true; $('#btnStart').disabled = true; $('#btnStart').textContent = 'Sending…';
    window.onbeforeunload = () => 'Sending is in progress. Leaving will pause it.';
    log('Started'); loop();
  });
  $('#btnPause').addEventListener('click', async () => {
    running = false; clearTimeout(timer);
    paint(await api({ act: 'pause', id })); stop('Paused. Press Resume to continue – already-sent contacts will not get it again.');
    log('Paused');
  });
  // if page reloaded while campaign marked running, show it as paused-able
  setInterval(async () => { if (!running) paint(await api({ act: 'stats', id })); }, 30000);
}
