/* ===== helpers & store ===== */
const $ = s => document.querySelector(s);
const CE = (t,cls) => { const e=document.createElement(t); if(cls) e.className=cls; return e; }
const LS = window.LS || {set(k,v){try{localStorage.setItem(k,JSON.stringify(v))}catch(e){}},get(k,d){try{const v=JSON.parse(localStorage.getItem(k));return v??d}catch(e){return d}}};
const SERVER = location.protocol==='file:' ? 'https://katindir.agency' : '';
const escapeRegExp = s => s.replace(/[.*+?^${}()|[\]\\]/g,'\\$&');
const pinField = $('#pinField');

const rootEl = document.documentElement;
const settingsEl = document.querySelector('.settings');
const ACCENTS = {
  aqua:['#00d4ff','#6afc9c'],
  pink:['#ff5fb0','#ffd166'],
  violet:['#a78bfa','#60a5fa'],
  lime:['#b7ff5a','#5efce8'],
  vanilla:['#ffd6a5','#caffbf'],
};
function applyAccent(name){
  const [a1,a2]=ACCENTS[name]||ACCENTS.aqua;
  rootEl.style.setProperty('--accent',a1);
  rootEl.style.setProperty('--accent2',a2);
  LS.set('ki_accent',name);
  document.querySelectorAll('[data-accent]').forEach(b=>b.classList.toggle('active',b.dataset.accent===name));
  syncThemeColorWithBg();
}
document.querySelectorAll('[data-accent]').forEach(b=>b.addEventListener('click',()=>applyAccent(b.dataset.accent)));
$('#settingsBtn').onclick = ()=> settingsEl.classList.toggle('show');
applyAccent(LS.get('ki_accent','aqua'));

function getPinValue(){
  return Array.from(document.querySelectorAll('#pinBox input[type="password"]')).map(i=>i.value).join('');
}
function setPinValue(v){
  const arr=document.querySelectorAll('#pinBox input[type="password"]');
  arr.forEach((inp,idx)=>inp.value=v[idx]||'');
}

document.querySelectorAll('#pinBox input').forEach((inp,idx,arr)=>{
  inp.addEventListener('input',()=>{
    if(inp.value && idx<3) arr[idx+1].focus();
    saveDraft();
  });
});
$('#resetPin').onclick=()=>{ setPinValue(''); saveDraft(); };

function generateIIN(data){
  const birth = data.birth&&/^\d{4}-\d{2}-\d{2}$/.test(data.birth)?data.birth.slice(2,4)+data.birth.slice(5,7)+data.birth.slice(8,10):'000000';
  let gender='0';
  if(data.gender==='Мужской') gender='1';
  else if(data.gender==='Женский') gender='2';
  const now=new Date();
  const current=now.toISOString().slice(2,10).replace(/-/g,'');
  let seq=LS.get('iin_seq',{date:'',num:0});
  if(seq.date===current) seq.num++; else seq={date:current,num:1};
  LS.set('iin_seq',seq);
  return birth+gender+current+String(seq.num).padStart(2,'0');
}

function toast(msg){ const el=CE('div'); el.textContent=msg; el.style.cssText='position:fixed;left:50%;transform:translateX(-50%);bottom:26px;background:linear-gradient(90deg,var(--accent),var(--accent2));color:#031018;padding:10px 14px;border-radius:999px;font-weight:900;box-shadow:0 10px 24px rgba(0,0,0,.35);z-index:9999;transition:opacity .3s'; document.body.appendChild(el); setTimeout(()=>el.style.opacity='0',1800); setTimeout(()=>el.remove(),2200); }
function pinToast(ok){
  const el=CE('div');
  el.style.cssText='position:fixed;left:50%;transform:translateX(-50%);bottom:26px;padding:10px 14px;border-radius:999px;font-weight:900;box-shadow:0 10px 24px rgba(0,0,0,.35);z-index:9999;display:flex;align-items:center;gap:8px;transition:opacity .3s;background:'+(ok?'linear-gradient(90deg,#34d399,#059669)':'linear-gradient(90deg,#f87171,#dc2626)')+';color:#031018';
  const span=document.createElement('span');
  span.textContent=ok?'ура!':'ERROR, ERROR, ERROR';
  el.appendChild(span);
  if(!ok){
    const cat=document.createElement('span');
    cat.textContent='😿';
    cat.style.fontSize='24px';
    cat.animate([{transform:'translateX(-4px)'},{transform:'translateX(4px)'}],{duration:400,iterations:Infinity,direction:'alternate'});
    el.appendChild(cat);
  }
  document.body.appendChild(el);
  setTimeout(()=>el.style.opacity='0',1800);
  setTimeout(()=>el.remove(),2200);
}

function deleteToast(name){
  const el=CE('div');
  el.style.cssText='position:fixed;left:50%;transform:translateX(-50%);bottom:26px;padding:10px 14px;border-radius:999px;font-weight:900;box-shadow:0 10px 24px rgba(0,0,0,.35);z-index:9999;display:flex;align-items:center;gap:8px;transition:opacity .3s;background:linear-gradient(90deg,#fcd34d,#fbbf24);color:#031018';
  const span=document.createElement('span');
  span.textContent=`${name} удалён!!`;
  el.appendChild(span);
  const cat=document.createElement('span');
  cat.textContent='🐈';
  cat.style.fontSize='24px';
  cat.animate([{transform:'translateX(-4px)'},{transform:'translateX(4px)'}],{duration:400,iterations:Infinity,direction:'alternate'});
  el.appendChild(cat);
  document.body.appendChild(el);
  setTimeout(()=>el.style.opacity='0',1800);
  setTimeout(()=>el.remove(),2200);
}

/* ===== skills ===== */
const skillsEl = $('#skills'); const skillInput = $('#skillInput');
$('#addSkill').addEventListener('click', addSkillFromInput);
skillInput.addEventListener('keydown', e=>{ if(e.key==='Enter'){ e.preventDefault(); addSkillFromInput(); }});
function addSkillFromInput(){ const v=(skillInput.value||'').trim(); if(!v) return; addSkill(v); skillInput.value=''; saveDraft(); }
function addSkill(text){ const t=CE('span','tag'); t.textContent='#'+text; const x=CE('span','x'); x.textContent='×'; x.title='Удалить'; x.onclick=()=>{ t.remove(); saveDraft(); }; t.appendChild(x); skillsEl.appendChild(t); }

/* ===== languages ===== */
const langEl = $('#languages'); const langInput = $('#langInput'); const langLevel = $('#langLevel');
$('#addLang').addEventListener('click', addLangFromInput);
langInput.addEventListener('keydown', e=>{ if(e.key==='Enter'){ e.preventDefault(); addLangFromInput(); }});
function addLangFromInput(){ const l=langInput.value.trim(); if(!l) return; addLang(l, langLevel.value); langInput.value=''; saveDraft(); }
function addLang(lang, level){
  const t=CE('span','tag'); t.textContent=`${lang} — ${level}`; t.dataset.lang=lang; t.dataset.level=level;
  const x=CE('span','x'); x.textContent='×'; x.title='Удалить'; x.onclick=()=>{ t.remove(); saveDraft(); };
  t.appendChild(x); langEl.appendChild(t);
}

/* ===== photo ===== */
let photoData = '';
const photoInput = $('#photoInput'); const photoPreview = $('#photoPreview');
photoInput.addEventListener('change', e=>{
  const f=e.target.files?.[0]; if(!f) return;
  const r=new FileReader(); r.onload=()=>{ photoData=r.result; photoPreview.src=photoData; photoPreview.style.display='block'; saveDraft(); }; r.readAsDataURL(f);
});

/* ===== dynamic lists: career & edu ===== */
const careerList = $('#careerList'), eduList = $('#eduList');

function careerItem(data={}){
  const el=CE('div','item');
  el.innerHTML = `
    <div class="bar">
      <h3>Место работы</h3>
      <div>
        <button class="btn ghost" data-move-up>↑</button>
        <button class="btn ghost" data-move-down>↓</button>
        <button class="btn danger" data-del>Удалить</button>
      </div>
    </div>
    <div class="two">
      <div><label>Компания</label><input data-key="company" type="text" placeholder="Название"></div>
      <div><label>Должность</label><input data-key="position" type="text" placeholder="Роль"></div>
    </div>
    <div class="three">
      <div><label>Город</label><input data-key="city" type="text" placeholder="Локация"></div>
      <div><label>Период: начало</label><input data-key="from" type="text" placeholder="напр. 04.2022"></div>
      <div><label>Период: конец</label><input data-key="to" type="text" placeholder="по н.в."></div>
    </div>
    <div>
      <label>Обязанности</label>
      <textarea data-key="duties" placeholder="Каждая строка — отдельный пункт"></textarea>
    </div>
    <div>
      <label>Достижения (опционально)</label>
      <textarea data-key="achievements" placeholder="Каждая строка — отдельный пункт"></textarea>
    </div>
  `;
  // fill
  Object.entries(data).forEach(([k,v])=>{ const i=el.querySelector(`[data-key="${k}"]`); if(i) i.value=v||''; });
  // events
  el.addEventListener('input', saveDraft);
  el.querySelector('[data-del]').onclick = ()=>{ el.remove(); saveDraft(); };
  el.querySelector('[data-move-up]').onclick = ()=>{ const prev=el.previousElementSibling; if(prev) careerList.insertBefore(el, prev); saveDraft(); };
  el.querySelector('[data-move-down]').onclick = ()=>{ const next=el.nextElementSibling; if(next) careerList.insertBefore(next, el); saveDraft(); };
  return el;
}

function eduItem(data={}){
  const el=CE('div','item');
  el.innerHTML = `
    <div class="bar">
      <h3>Образование / Курс</h3>
      <div>
        <button class="btn ghost" data-move-up>↑</button>
        <button class="btn ghost" data-move-down>↓</button>
        <button class="btn danger" data-del>Удалить</button>
      </div>
    </div>
    <div class="two">
      <div><label>Учреждение</label><input data-key="school" type="text" placeholder="ВУЗ / Платформа"></div>
      <div><label>Специальность / Программа</label><input data-key="program" type="text" placeholder="Социология / Менеджмент / Курс ..."></div>
    </div>
    <div class="three">
      <div><label>Уровень</label><input data-key="degree" type="text" placeholder="Бакалавр, Магистр, Курс"></div>
      <div><label>Годы: начало</label><input data-key="from" type="text" placeholder="2019"></div>
      <div><label>Годы: конец</label><input data-key="to" type="text" placeholder="2023"></div>
    </div>
    <div>
      <label>Комментарий (опционально)</label>
      <textarea data-key="notes" placeholder="курсовые, средний балл, ключевые предметы и т.д."></textarea>
    </div>
  `;
  Object.entries(data).forEach(([k,v])=>{ const i=el.querySelector(`[data-key="${k}"]`); if(i) i.value=v||''; });
  el.addEventListener('input', saveDraft);
  el.querySelector('[data-del]').onclick = ()=>{ el.remove(); saveDraft(); };
  el.querySelector('[data-move-up]').onclick = ()=>{ const prev=el.previousElementSibling; if(prev) eduList.insertBefore(el, prev); saveDraft(); };
  el.querySelector('[data-move-down]').onclick = ()=>{ const next=el.nextElementSibling; if(next) eduList.insertBefore(next, el); saveDraft(); };
  return el;
}

$('#addCareer').onclick = ()=>{ careerList.appendChild(careerItem()); saveDraft(); };
$('#addEdu').onclick = ()=>{ eduList.appendChild(eduItem()); saveDraft(); };

/* ===== draft ===== */
function collectDraft(){
  return {
    fio: $('#fio').value||'',
    birth: $('#birth').value||'',
    gender: $('#gender').value||'',
    citizenship: $('#citizenship').value||'',
    location: $('#location').value||'',
    email: $('#email').value||'',
    phone: $('#phone').value||'',
    telegram: $('#telegram').value||'',
    site: $('#site').value||'',
    about: $('#about').value||'',
    pin: getPinValue(),
    interests: $('#interests').value||'',
    photo: photoData||'',
    skills: Array.from(document.querySelectorAll('#skills .tag')).map(t=>t.textContent.replace(/^#/,'').replace('×','').trim()),
    languages: Array.from(document.querySelectorAll('#languages .tag')).map(t=>({name:t.dataset.lang,level:t.dataset.level})),
    career: Array.from(careerList.children).map(item=>{
      const g=k=> item.querySelector(`[data-key="${k}"]`)?.value||'';
      return {company:g('company'),position:g('position'),city:g('city'),from:g('from'),to:g('to'),duties:g('duties'),achievements:g('achievements')};
    }),
    education: Array.from(eduList.children).map(item=>{
      const g=k=> item.querySelector(`[data-key="${k}"]`)?.value||'';
      return {school:g('school'),program:g('program'),degree:g('degree'),from:g('from'),to:g('to'),notes:g('notes')};
    })
  };
}
function saveDraft(){ LS.set('cv_draft', collectDraft()); updateProgress(); }
function loadDraft(d){
  if(!d) return;
  $('#fio').value=d.fio||'';
  $('#birth').value=d.birth||'';
  $('#gender').value=d.gender||'';
  $('#citizenship').value=d.citizenship||'';
  $('#location').value=d.location||'';
  $('#email').value=d.email||'';
  $('#phone').value=d.phone||'';
  $('#telegram').value=d.telegram||'';
  $('#site').value=d.site||'';
  $('#about').value=d.about||'';
  setPinValue(d.pin||'');
  pinField.style.display = d.pin ? 'block' : 'none';
  $('#interests').value=d.interests||'';
  skillsEl.innerHTML='';
  (d.skills||[]).forEach(addSkill);
  langEl.innerHTML='';
  (d.languages||[]).forEach(l=>addLang(l.name,l.level));
  photoData=d.photo||'';
  photoPreview.src = photoData || '';
  photoPreview.style.display = photoData ? 'block' : 'none';
  careerList.innerHTML=''; (d.career||[]).forEach(x=>careerList.appendChild(careerItem(x)));
  eduList.innerHTML=''; (d.education||[]).forEach(x=>eduList.appendChild(eduItem(x)));
  updateProgress();
}
const loadInput = document.getElementById('loadFile');
$('#loadCv').onclick = () => loadInput.click();
loadInput.addEventListener('change', e=>{
  const file = e.target.files[0];
  if(!file) return;
  const reader = new FileReader();
  reader.onload = ev=>{
    try{
      const text = ev.target.result;
      const dataMatch = text.match(/const\s+resumeData=(\{[\s\S]*?\});/);
      if(!dataMatch) return toast('Не удалось прочитать файл');
      const data = JSON.parse(dataMatch[1]);
      const idMatch = text.match(/const\s+resumeId='([^']*)'/);
      if(idMatch) LS.set('cv_id', idMatch[1]);
      loadDraft(data);
      LS.set('cv_draft', data);
      toast('CV загружен');
    }catch(err){
      console.error(err);
      toast('Ошибка загрузки');
    }
  };
  reader.readAsText(file);
  loadInput.value = '';
});
$('#clearDraft').onclick = ()=>{
  localStorage.removeItem('cv_draft');
  localStorage.removeItem('cv_id');
  loadDraft({});
  toast('CV удалён');
};
document.addEventListener('input', e=>{
  // авто-сейв на любые вводы
  if(e.target.matches('input,textarea,select')) saveDraft();
});

function updateProgress(){
  const fields=['#fio','#birth','#gender','#citizenship','#location','#email','#phone','#telegram','#site','#about'];
  let filled=0;
  if(photoData) filled++;
  fields.forEach(sel=>{ const v=$(sel).value.trim(); if(v) filled++; });
  if(getPinValue().length===4) filled++;
  if(skillsEl.children.length) filled++;
  if(langEl.children.length) filled++;
  const total=fields.length+4; // photo, pin, skills, languages
  const percent=Math.round(filled/total*100);
  const bar=document.getElementById('profileProgress');
  if(bar) bar.style.width=percent+'%';
}

/* ===== generator ===== */
function linesToList(text){
  const arr=(text||'').split(/\r?\n/).map(s=>s.trim()).filter(Boolean);
  if(!arr.length) return '';
  return '<ul>'+arr.map(s=>`<li>${escapeHtml(s)}</li>`).join('')+'</ul>';
}
function escapeHtml(s){ return (s||'').replace(/[&<>"]/g, c=>({ '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;' }[c])); }

function buildResumeHTML(data, placeholders=false){
  const iin = placeholders ? '{{IIN}}' : (data.iin || generateIIN(data));
  data.iin = iin;
  const skills = (data.skills||[]).map(s=>`<span class="tag">#${escapeHtml(s)}</span>`).join('');
  const languages = (data.languages||[]).map(l=>`<span class="tag">${escapeHtml(l.name)} — ${escapeHtml(l.level)}</span>`).join('');
  const career = (data.career||[]).map(c=>`
    <article class="cv-item">
      <div class="cv-head">
        <div class="cv-title">${escapeHtml(c.position||'')}</div>
        <div class="cv-meta">${escapeHtml(c.company||'')}${c.city? ' · '+escapeHtml(c.city):''}</div>
        <div class="cv-dates">${escapeHtml(c.from||'')} — ${escapeHtml(c.to||'')}</div>
      </div>
      ${linesToList(c.duties)}
      ${c.achievements? `<div class="cv-sub">Достижения</div>${linesToList(c.achievements)}`:''}
    </article>
  `).join('');

  const edu = (data.education||[]).map(e=>`
    <article class="cv-item">
      <div class="cv-head">
        <div class="cv-title">${escapeHtml(e.school||'')}</div>
        <div class="cv-meta">${escapeHtml(e.program||'')}</div>
      <div class="cv-dates">${escapeHtml(e.from||'')} — ${escapeHtml(e.to||'')}</div>
      </div>
      ${e.degree? `<div class="cv-sub">${escapeHtml(e.degree)}</div>`:''}
      ${e.notes? linesToList(e.notes):''}
    </article>
  `).join('');

  const interests = data.interests ? `<section class="section"><h2 class="title">Интересы</h2><div>${escapeHtml(data.interests)}</div></section>` : '';

  const photo = data.photo ? `<img class="avatar-img" src="${data.photo}" alt="${escapeHtml(data.fio||'Фото')}">` : '';

  const contactRow = `
    ${data.email? `<a href="mailto:${escapeHtml(data.email)}">✉️ ${escapeHtml(data.email)}</a>`:''}
    ${data.phone? `<a href="tel:${escapeHtml(data.phone)}">📞 ${escapeHtml(data.phone)}</a>`:''}
    ${data.telegram? `<a href="https://t.me/${escapeHtml(data.telegram).replace(/^@/,'')}" target="_blank" rel="noopener">💬 ${escapeHtml(data.telegram)}</a>`:''}
    ${data.site? `<a href="${escapeHtml(data.site)}" target="_blank" rel="noopener">🌐 ${escapeHtml(data.site)}</a>`:''}
  `.replace(/\s+/g,' ').trim();

  const resumeJson = JSON.stringify(data).replace(/</g,'\\u003c').replace(/>/g,'\\u003e');

  // Минималистичный стиль CV, совместимый с главным
  return `<!doctype html>
<html lang="ru">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <title>CV — ${escapeHtml(data.fio||'')}</title>
  <meta name="theme-color" content="#0a0e14">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
  <style>
    :root{color-scheme:dark;--bg:#0a0e14;--panel:#0f1723;--ink:#eaf2ff;--muted:#a9b9d0;--accent:#00d4ff;--accent2:#6afc9c;--gold:#ffd166;--r:14px;--shadow:0 10px 40px rgba(5,10,25,.5);font-family:'Montserrat',system-ui,-apple-system,Segoe UI,Roboto,Arial}
    *,*::before,*::after{box-sizing:border-box}
    html,body{height:100%;margin:0;background:var(--bg)}
    body{min-height:100svh;color:var(--ink);-webkit-font-smoothing:antialiased;text-rendering:optimizeLegibility;padding-left:env(safe-area-inset-left);padding-right:env(safe-area-inset-right);padding-bottom:env(safe-area-inset-bottom)}
    body::before{content:"";position:fixed;inset:0;z-index:-1;background:
      radial-gradient(120% 140% at 10% 10%, #101a2a 0%, var(--bg) 60%),
      linear-gradient(180deg,#0a0f19 0%, #0b0f15 100%)}
    .wrap{max-width:980px;margin:0 auto;padding:18px 16px;display:flex;flex-direction:column;gap:14px}
    header{display:flex;align-items:center;gap:12px;justify-content:space-between}
    .brand{display:flex;align-items:center;gap:10px}
    .logo{width:56px;height:56px;border-radius:14px;display:grid;place-items:center;color:#071018;font-weight:900;background:conic-gradient(from 210deg,#00d4ff,#6afc9c,#ff5fb0);box-shadow:0 0 22px rgba(0,212,255,.25)}
    h1{margin:0;font-size:24px}
    .subtitle{opacity:.85;font-size:14px}
    .section{background:linear-gradient(180deg,rgba(255,255,255,.04),rgba(255,255,255,.01));border:1px solid rgba(255,255,255,.06);border-radius:18px;padding:16px;box-shadow:0 10px 40px rgba(2,6,23,.6)}
    .title{font-size:20px;margin:0 0 10px}
    .card{display:flex;gap:14px;align-items:flex-start;background:linear-gradient(180deg,var(--panel),rgba(255,255,255,.02));border:1px solid rgba(255,255,255,.06);border-radius:14px;padding:12px}
    .avatar{width:140px;height:140px;border-radius:12px;overflow:hidden;border:2px solid rgba(255,255,255,.08);background:#0a0f14;flex:0 0 auto}
    .avatar-img{width:100%;height:100%;object-fit:cover;display:block}
    .info{flex:1;min-width:0}
    .name{font-size:22px;margin:0 0 6px}
    .grid{display:grid;grid-template-columns:1fr 1fr;gap:10px}
    .grid > div:not(.row){display:flex;flex-direction:column;gap:4px}
    @media(max-width:800px){.grid{grid-template-columns:1fr}}
    .row{display:flex;gap:8px;flex-wrap:wrap;align-items:center}
    .muted{color:var(--muted);font-size:13px}
    .tag{display:inline-flex;align-items:center;padding:6px 10px;border-radius:999px;border:1px solid rgba(255,255,255,.18);margin:4px 6px 0 0;background:linear-gradient(180deg,rgba(255,255,255,.08),rgba(255,255,255,.02));font-size:12px}
    @media print{body{background:var(--bg);-webkit-print-color-adjust:exact;print-color-adjust:exact}}
    .cv-item{background:linear-gradient(180deg,rgba(255,255,255,.03),rgba(255,255,255,.01));border:1px solid rgba(255,255,255,.06);border-radius:12px;padding:12px;margin:8px 0}
    .cv-head{display:grid;grid-template-columns:1fr auto;gap:4px}
    .cv-title{font-weight:800}
    .cv-meta{color:var(--muted);font-size:13px}
    .cv-dates{font-size:13px;text-align:right;color:var(--muted)}
    .cv-sub{margin-top:6px;font-weight:800}
    ul{margin:6px 0 0 18px}
    footer{padding:12px;text-align:center;font-size:12px;color:#b9c4d6;background:rgba(0,0,0,.35);margin-top:12px}
    a{color:inherit;text-decoration:none;border-bottom:1px dashed rgba(255,255,255,.25)}
    .btn{cursor:pointer;border:1px solid rgba(255,255,255,.18);background:linear-gradient(180deg,rgba(255,255,255,.08),rgba(255,255,255,.02));color:var(--ink);padding:9px 12px;border-radius:12px;font-weight:700;transition:.2s}
    .btn.primary{border:none;background:linear-gradient(90deg,var(--accent),var(--accent2));color:#041018;box-shadow:0 0 18px rgba(0,212,255,.2)}
    .modal{position:fixed;inset:0;display:none;align-items:center;justify-content:center;background:rgba(0,0,0,.45);backdrop-filter:blur(6px);z-index:5000;padding:16px}
    .modal.show{display:flex}
    .modal-card{width:min(280px,90vw);background:linear-gradient(180deg,rgba(255,255,255,.06),rgba(255,255,255,.02));border:1px solid rgba(255,255,255,.12);border-radius:16px;box-shadow:0 20px 60px rgba(0,0,0,.5);padding:16px}
    .modal-card h3{margin:0 0 10px}
    .pin-inputs{display:flex;gap:10px;justify-content:center;margin:10px 0}
    .pin-inputs input{width:40px;height:50px;font-size:24px;text-align:center;border:1px solid rgba(255,255,255,.18);border-radius:8px;background:rgba(255,255,255,.06);color:var(--ink)}
    .edit-corner{position:absolute;top:0;right:0;width:48px;height:48px;border:none;border-bottom-left-radius:12px;background:#34d399;color:#031018;font-size:20px;display:flex;align-items:center;justify-content:center;cursor:pointer;box-shadow:0 4px 12px rgba(0,0,0,.4)}
    .iin{position:absolute;bottom:4px;right:4px;font-size:10px;color:var(--muted);writing-mode:vertical-rl;text-orientation:mixed}
  </style>
</head>
<body>
  <div class="wrap">
    <header>
      <div class="brand">
        <div class="logo">КА</div>
        <div>
          <h1>CV</h1>
          <div class="subtitle">Катиндирнет — CV</div>
        </div>
      </div>
      <div class="muted">${escapeHtml(new Date().toLocaleDateString('ru-RU'))}</div>
    </header>

    <section class="section" style="position:relative">
      ${data.pin? `<button id="editBtn" class="edit-corner">✎</button>`:''}
      <div class="iin">${iin}</div>
      <div class="card">
        <div class="avatar">${photo||''}</div>
        <div class="info">
          <h2 class="name">${escapeHtml(data.fio||'')}</h2>
          <div class="grid">
            <div>
              <div><span class="muted">Дата рождения:</span> ${escapeHtml(data.birth||'')}</div>
              <div><span class="muted">Пол:</span> ${escapeHtml(data.gender||'')}</div>
              <div><span class="muted">Гражданство:</span> ${escapeHtml(data.citizenship||'')}</div>
              <div><span class="muted">Локация:</span> ${escapeHtml(data.location||'')}</div>
            </div>
            <div class="row">
              ${contactRow}
            </div>
          </div>
          ${skills? `<div style="margin-top:8px">${skills}</div>`:''}
          ${languages? `<div style="margin-top:8px">${languages}</div>`:''}
          ${data.about? `<div style="margin-top:10px" class="muted">${escapeHtml(data.about)}</div>`:''}
        </div>
      </div>
    </section>

    ${career? `<section class="section"><h2 class="title">Карьера</h2>${career}</section>`:''}
    ${edu? `<section class="section"><h2 class="title">Образование и курсы</h2>${edu}</section>`:''}
  ${interests}

  </div>
  <footer>© Цой Артём TSOY.IN Project 2025 y.</footer>
  ${data.pin? `<div class="modal" id="pinModal" aria-hidden="true"><div class="modal-card"><h3 id="pinTitle">PIN</h3><div class="pin-inputs"><input type="password" maxlength="1" inputmode="numeric"/><input type="password" maxlength="1" inputmode="numeric"/><input type="password" maxlength="1" inputmode="numeric"/><input type="password" maxlength="1" inputmode="numeric"/></div><div class="actions" style="display:flex;gap:8px;justify-content:flex-end"><button class="btn" id="pinCancel">Отмена</button><button class="btn primary" id="pinOk">OK</button></div></div></div><script>const resumeData=${resumeJson};const pin='${data.pin}';const resumeId='${placeholders ? '{{ID}}' : LS.get('cv_id','')}';async function askPin(title){return new Promise((resolve,reject)=>{const modal=document.getElementById('pinModal');const inputs=modal.querySelectorAll('input');document.getElementById('pinTitle').textContent=title;inputs.forEach(i=>i.value='');modal.classList.add('show');inputs[0].focus();function finish(ok){modal.classList.remove('show');ok?resolve(Array.from(inputs).map(i=>i.value).join('')):reject();okBtn.removeEventListener('click',okHandler);cancelBtn.removeEventListener('click',cancelHandler);}const okBtn=document.getElementById('pinOk');const cancelBtn=document.getElementById('pinCancel');const okHandler=()=>{const pin=Array.from(inputs).map(i=>i.value).join('');if(pin.length===4)finish(true);};const cancelHandler=()=>finish(false);okBtn.addEventListener('click',okHandler);cancelBtn.addEventListener('click',cancelHandler);inputs.forEach((inp,idx)=>{inp.addEventListener('input',()=>{if(inp.value && idx<3)inputs[idx+1].focus();else if(inp.value && idx===3)okHandler();});inp.addEventListener('keydown',e=>{if(e.key==='Enter')okHandler();});});});}function pinToast(ok){const el=document.createElement('div');el.style.cssText='position:fixed;left:50%;transform:translateX(-50%);bottom:26px;padding:10px 14px;border-radius:999px;font-weight:900;box-shadow:0 10px 24px rgba(0,0,0,.35);z-index:9999;display:flex;align-items:center;gap:8px;transition:opacity .3s;background:'+(ok?'linear-gradient(90deg,#34d399,#059669)':'linear-gradient(90deg,#f87171,#dc2626)')+';color:#031018';const s=document.createElement('span');s.textContent=ok?"ура!":"ERROR, ERROR, ERROR";el.appendChild(s);if(!ok){const cat=document.createElement('span');cat.textContent='😿';cat.style.fontSize='24px';cat.animate([{transform:'translateX(-4px)'},{transform:'translateX(4px)'}],{duration:400,iterations:Infinity,direction:'alternate'});el.appendChild(cat);}document.body.appendChild(el);setTimeout(()=>el.style.opacity='0',1800);setTimeout(()=>el.remove(),2200);}document.getElementById('editBtn').addEventListener('click',async()=>{try{const p=await askPin('Введите PIN');if(p===pin){pinToast(true);localStorage.setItem('cv_draft',JSON.stringify(resumeData));LS.set('cv_id',resumeId);var dest=location.protocol==='file:'?'https://katindir.agency/cv.php':'/cv.php';location.href=dest;}else pinToast(false);}catch(e){}});<\/script>`:''}
</body>
</html>`;
}

/* ===== modal & save ===== */
const codeModal = $('#codeModal'); const codeBox = $('#codeBox');
function openCodeModal(html){ codeBox.value = html; codeModal.classList.add('show'); }
$('#closeModal').onclick = ()=> codeModal.classList.remove('show');
$('#copyCode').onclick = async ()=>{ try{ await navigator.clipboard.writeText(codeBox.value); toast('Скопировано'); }catch(e){ toast('Не удалось скопировать'); } };
$('#openPreview').onclick = ()=>{ const w=window.open(); w.document.open(); w.document.write(codeBox.value); w.document.close(); };

$('#previewBtn').onclick = ()=>{
  const html = buildResumeHTML(collectDraft());
  openCodeModal(html);
};
$('#saveBtn').onclick = async ()=>{
  if(getPinValue().length !== 4){
    const pin = await askPin('Придумайте PIN');
    setPinValue(pin);
    pinField.style.display = 'block';
  }
  const data = collectDraft();
  const html = buildResumeHTML(data);
  const blob = new Blob([html], {type:'text/html;charset=utf-8'});
  const a = document.createElement('a');
  const name = (data.fio||'cv').trim().replace(/\s+/g,'_');
  a.href = URL.createObjectURL(blob);
  a.download = `cv-${name}.html`;
  a.click();
  setTimeout(()=>URL.revokeObjectURL(a.href), 2000);
  toast('HTML сохранён');
};

/* ===== init: автозагрузка черновика однажды ===== */
(function init(){
  const d=LS.get('cv_draft',null);
  if(d) loadDraft(d);
  updateProgress();
  if(LS.get('cv_id','')) document.getElementById('deleteServerBtn').style.display='inline-block';
})();

/* ===== server saving & loading ===== */
async function askPin(title){
  return new Promise((resolve,reject)=>{
    const modal=document.getElementById('pinModal');
    const inputs=modal.querySelectorAll('input');
    document.getElementById('pinTitle').textContent=title;
    inputs.forEach(i=>{i.value='';});
    modal.classList.add('show');
    inputs[0].focus();
    function finish(ok){
      modal.classList.remove('show');
      ok?resolve(Array.from(inputs).map(i=>i.value).join('')):reject();
      okBtn.removeEventListener('click',okHandler);
      cancelBtn.removeEventListener('click',cancelHandler);
    }
    const okBtn=document.getElementById('pinOk');
    const cancelBtn=document.getElementById('pinCancel');
    const okHandler=()=>{ const pin=Array.from(inputs).map(i=>i.value).join(''); if(pin.length===4) finish(true); };
    const cancelHandler=()=>finish(false);
    okBtn.addEventListener('click',okHandler);
    cancelBtn.addEventListener('click',cancelHandler);
    inputs.forEach((inp,idx)=>{
      inp.addEventListener('input',()=>{
        if(inp.value && idx<3) inputs[idx+1].focus();
        else if(inp.value && idx===3) okHandler();
      });
      inp.addEventListener('keydown',e=>{ if(e.key==='Enter') okHandler(); });
    });
  });
}

async function saveServer(){
  try{
    let pin=getPinValue();
    if(pin.length!==4){
      pin=await askPin('Придумайте PIN');
      setPinValue(pin);
      pinField.style.display='block';
    }
    const data=collectDraft();
    const id=LS.get('cv_id','');
    const payload={data,pin};
    if(id) payload.id=id;
    const resp=await fetch(SERVER+'/save_cv.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(payload)}).then(r=>r.json());
    if(resp.ok){
      const newId=resp.id||id;
      LS.set('cv_id',newId);
      document.getElementById('deleteServerBtn').style.display='inline-block';
      const viewLink=`${SERVER}/view_cv.php?id=${newId}`;
      window.open(viewLink,'_blank');
      try{ await navigator.clipboard.writeText(viewLink); toast('Ссылка скопирована'); }
      catch(err){ toast('Сохранено'); }
    }else toast('Ошибка сохранения');
  }catch(e){ toast('Ошибка сохранения'); }
}

let cvCache = null;
async function openSearch(){
  try{
    if(!cvCache) cvCache = await fetch(SERVER+'/list_cv.php').then(r=>r.json());
    document.getElementById('searchInput').value='';
    renderSearch('');
    document.getElementById('searchModal').classList.add('show');
    document.getElementById('searchInput').focus();
  }catch(e){ toast('Нет сохранений'); }
}

function renderSearch(q){
  const box=document.getElementById('searchResults');
  box.innerHTML='';
  if(q.length<3){
    const div=document.createElement('div');
    div.className='search-placeholder';
    div.innerHTML='<div>укажите ваше ФИО</div><div class="cat">🐈</div>';
    box.appendChild(div);
    return;
  }
  const regex=new RegExp(escapeRegExp(q),'i');
  const list=(cvCache||[]).filter(it=>{
    const name=it.name||'';
    return name.toLowerCase().includes(q.toLowerCase()) || regex.test(name);
  });
  if(!list.length){ const div=document.createElement('div'); div.className='muted'; div.textContent='не найдено ☹️'; box.appendChild(div); return; }
  list.forEach(item=>{
    const card=document.createElement('div'); card.className='search-card';
    if(item.photo){ const img=document.createElement('img'); img.src=item.photo; card.appendChild(img); }
    const nm=document.createElement('div'); nm.className='name'; nm.textContent=item.name||item.id; card.appendChild(nm);
    const btn=document.createElement('button'); btn.className='btn'; btn.textContent='Открыть';
    btn.onclick=()=>openCv(item.id);
    card.appendChild(btn);
    box.appendChild(card);
  });
}

async function openCv(id){
  try{
    const pin=await askPin('Введите PIN');
    const res=await fetch(`${SERVER}/get_cv.php?id=${id}&pin=${pin}`).then(r=>r.json());
    if(res.ok){
      pinToast(true);
      window.open(`${SERVER}/view_cv.php?id=${id}`,'_blank');
    }else pinToast(false);
  }catch(e){}
}

document.getElementById('searchCv').onclick=openSearch;
document.getElementById('closeSearch').onclick=()=>document.getElementById('searchModal').classList.remove('show');
document.getElementById('searchInput').addEventListener('input',e=>renderSearch(e.target.value));
document.getElementById('saveServerBtn').onclick = saveServer;
document.getElementById('deleteServerBtn').onclick = async()=>{
  const id=LS.get('cv_id','');
  if(!id) return;
  const pin=getPinValue();
  if(pin.length!==4) return toast('PIN?');
  if(!confirm('Удалить CV?')) return;
  try{
    const resp=await fetch(SERVER+'/delete_cv.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({id,pin})}).then(r=>r.json());
    if(resp.ok){ deleteToast(`${id}.json`); localStorage.removeItem('cv_id'); document.getElementById('deleteServerBtn').style.display='none'; }
    else toast('Ошибка удаления');
  }catch(e){ toast('Ошибка удаления'); }
};
