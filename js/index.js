// заглушки
window.haptic = window.haptic || function(){try{navigator.vibrate && navigator.vibrate(12)}catch(e){}}
window.toast  = window.toast  || function(msg){try{console.log('[toast]', msg)}catch(e){}}
window.bling  = window.bling  || function(btn){try{btn&&btn.classList&&btn.classList.toggle('primary'); if(typeof toast==='function'){toast('✨')}}catch(e){}}
window.confetti = window.confetti || function(){}

/* ===== helpers ===== */
const $ = s => document.querySelector(s);
function notify(text){ const el=document.createElement('div'); el.textContent=text; el.style.cssText='position:fixed;left:50%;transform:translateX(-50%);bottom:26px;background:linear-gradient(90deg,var(--accent),var(--accent2));color:#031018;padding:10px 14px;border-radius:999px;font-weight:900;box-shadow:0 10px 24px rgba(0,0,0,.35);z-index:9999;transition:opacity .3s'; document.body.appendChild(el); setTimeout(()=>el.style.opacity='0',1800); setTimeout(()=>el.remove(),2200); }
const toast = notify;
const LS = window.LS || { set(k,v){try{localStorage.setItem(k,JSON.stringify(v))}catch(e){}}, get(k,def){try{const v=JSON.parse(localStorage.getItem(k));return v==null?def:v}catch(e){return def}} };

/* ===== SKY: только котики ===== */
const sky = $('#sky');
const emojis = ['🐱','😺','😻','😹','🙀','🐈'];
let catsTimer=null; let catsDelay = LS.get('ki_cats_density', 260);

function animateFaller(el){
  const start=performance.now(); const dur=4000+Math.random()*3000; const rot=(Math.random()*360)|0;
  function tick(t){ const p=Math.min(1,(t-start)/dur); const y=p*(window.innerHeight+100); el.style.transform=`translateY(${y}px) rotate(${rot*p}deg)`; if(p<1) requestAnimationFrame(tick); else el.remove(); }
  requestAnimationFrame(tick);
}
function dropEmoji(){
  const el=document.createElement('div'); el.className='cat';
  el.textContent=emojis[(Math.random()*emojis.length)|0];
  el.style.left=Math.random()*100+'vw'; el.style.top='-40px';
  sky.appendChild(el); animateFaller(el); playSfx();
}
function startCats(){ if(!catsTimer){ catsTimer=setInterval(dropEmoji, catsDelay); } }
function stopCats(){ if(catsTimer){ clearInterval(catsTimer); catsTimer=null; } }

/* ===== TICKER ===== */
const tickData = [['KatyaCoin','+200%','up'],['IndiraNFT','−7 смешинок','down'],['LOL-ETF','+69%','up'],['Meme100','to the moon','up'],['GiggleBond','стабильно ха-ха','up'],['CringeFutures','волатильно','down'],['HaHaDAO','ATH!','up'],['SmileSwap','апрув 🙂','up']];
const ticker = $('#ticker');
(function buildTicker(){
  const row=document.createElement('div'); row.style.display='flex'; row.style.gap='24px'; row.style.paddingRight='24px';
  tickData.concat(tickData).forEach(([n,v,d])=>{ const el=document.createElement('div'); el.className='tick '+(d==='up'?'up':'down'); el.innerHTML=`<span>📊</span><span>${n}</span><span>${v}</span>`; row.appendChild(el); });
  ticker.appendChild(row);
})();

/* ===== SLOGANS & CAPTIONS ===== */
const slogans = ['Долистались до капитала смеха','Котировки шуток растут быстрее кофеина','Улыбка добавлена в корзину','Портфель мемов диверсифицирован','Лол-ликвидность обеспечена','Ванильная ликвидность подтверждена 🍦'];
const sloganEl = $('#slogan'); let sIdx=0; setInterval(()=>{ sIdx=(sIdx+1)%slogans.length; if(sloganEl) sloganEl.textContent=slogans[sIdx]; }, 3500);

const captions = [
  'Долистались до капитала смеха',
  'Котировки шуток растут быстрее кофеина',
  'Улыбка добавлена в корзину',
  'Портфель мемов диверсифицирован',
  'Лол-ликвидность обеспечена',
  'Ванильная ликвидность подтверждена 🍦',
  'Смех-банк: депозит в смешинки',
  'Синергия рофлов: +100 к карме',
  'Инсайд: завтра смешнее, чем сегодня',
  'Безрисковая доходность? Ха-ха-ха — уже начислено',
  'Дельта-улыбки положительная, gamma — тоже 😉',
  'Наш KPI: хохот до слёз',
  'Buy the dip? Buy the meme!',
  'APY по хихиканью: бесконечность %',
  'Стоп-лосс — на уровне «ещё по одной»',
  'Тезерим шутки, стейкаем лайки',
  'Волатильность рофлов одобрена регулятором котиков',
  'Мемоверс расширяется: to the meow 🌜',
  'Премия за риск — в котах',
  'Сарказм — наш базовый актив',
  'Дивиденды в виде «ахаха»',
  'Ленн ап — ирония, сарказм, самоирония',
  'Маржин-колл? Только если без смайла нельзя 😼',
  'HODL: Hold On, Drop Laugh',
  'Смешной кэш-флоу: бесконечный',
  'Наше ESG: Ем Сметану, Глажу-котов',
  'Бета к рынку уныния — отрицательная',
  'Бычий тренд на мимими',
  'Фундаментал от слова «фан»',
  'Вход по рынку: «ещё один мем!»',
  'Reinvest laughter, repeat',
  'Налог на роскошь: улыбка шире',
  'Снижаем токсичность портфеля котятами',
  'Нормируем юмор: по ГОСТ ржомба-2025',
  'Индекс счастья закрыт зелёным',
  'Бесконечная ликвидность доброты'
];
const memeInput=$('#memeInput'), memeText=$('#memeText');
function applyCaption(text){ if(!memeText||!memeInput) return; memeText.textContent='«'+text.replace(/\"/g,'“')+'»'; memeInput.value=text; LS.set('ki_caption',text); }
$('#applyCaption')?.addEventListener('click',()=>{ applyCaption(memeInput.value||''); toast('Подпись обновлена'); });
$('#randCaption')?.addEventListener('click',()=>applyCaption(captions[(Math.random()*captions.length)|0]));
$('#randCaption2')?.addEventListener('click',()=>applyCaption(captions[(Math.random()*captions.length)|0]));
const savedCap=LS.get('ki_caption',null); if(savedCap) applyCaption(savedCap);

/* ===== IMAGE INPUTS ===== */
function bindImageInput(imgId,fileId){
  const img=document.getElementById(imgId), input=document.getElementById(fileId);
  if(!img||!input) return;
  img.addEventListener('click',()=>input.click());
  img.addEventListener('keydown',e=>{ if(e.key==='Enter'||e.key===' ') input.click(); });
  input.addEventListener('change',e=>{
    const f=e.target.files?.[0]; if(!f) return;
    const r=new FileReader(); r.onload=()=>img.src=r.result; r.readAsDataURL(f);
  });
}
bindImageInput('img-katya','file-katya');
bindImageInput('img-indira','file-indira');
bindImageInput('img-joint','file-joint');

/* ===== GALLERY ===== */
let extraImages = LS.get('ki_extra_images', []); if(!Array.isArray(extraImages)) extraImages=[];
const thumbs=$('#thumbs'), addThumbBtn=$('#addThumbBtn'), addThumbInput=$('#addThumbInput');
function renderThumbs(){
  if(!thumbs) return; thumbs.innerHTML='';
  (extraImages||[]).forEach((src,i)=>{
    const d=document.createElement('div'); d.className='thumb';
    const im=new Image(); im.src=src; d.appendChild(im);
    const x=document.createElement('button'); x.className='close'; x.textContent='×';
    x.addEventListener('click',ev=>{ ev.stopPropagation(); extraImages.splice(i,1); LS.set('ki_extra_images',extraImages); renderThumbs(); toast('Фото удалено'); });
    d.appendChild(x); thumbs.appendChild(d);
  });
  const add=document.createElement('div'); add.className='thumb add-thumb'; add.textContent='+'; add.title='Добавить фото';
  add.addEventListener('click',()=>addThumbInput.click()); thumbs.appendChild(add);
}
addThumbBtn?.addEventListener('click',()=>addThumbInput.click());
addThumbInput?.addEventListener('change',e=>{
  const arr=Array.from(e.target.files||[]); if(!arr.length) return;
  let done=0; arr.forEach(f=>{ const r=new FileReader(); r.onload=()=>{ extraImages.push(r.result); if(++done===arr.length){ LS.set('ki_extra_images',extraImages); renderThumbs(); toast('Фото добавлены'); } }; r.readAsDataURL(f); });
  e.target.value='';
});
$('#resetThumbs')?.addEventListener('click',()=>{ if(!extraImages.length) return toast('Нечего очищать'); if(confirm('Убрать все доп. фото?')){ extraImages.length=0; localStorage.removeItem('ki_extra_images'); renderThumbs(); toast('Галерея очищена 🧹'); }});
renderThumbs();

/* ===== CAROUSEL ===== */
const carTrack=$('#carTrack'), carWrap=$('#friendsCarousel');
let carCards = LS.get('ki_carousel_cards', []);
function uid(){return 'id-'+Math.random().toString(36).slice(2,9)}
function carCardHTML(c){
  return `<article class="c-card" data-id="${c.id}">
    <label class="avatar"><img src="${c.img}" alt="${c.name}"/></label>
    <div class="info">
      <div class="person-head">
        <h3 class="name">${c.name}</h3>
        <div class="badge-row">${c.medal?`<span class="badge">${c.medal}</span>`:''}</div>
      </div>
      <p class="role">${c.role||''}</p>
      <div class="controls compact">
        <button class="btn" onclick="toast('${c.name}: мемы выведены 💸')">Кэш</button>
        <button class="btn primary" onclick="toast('${c.name}: IPO улыбки! 🎉')">IPO</button>
        <button class="btn danger" data-del data-id="${c.id}">Удалить</button>
      </div>
    </div>
  </article>`;
}
function renderCarousel(){
  if(!carTrack) return;
  carTrack.innerHTML='';
  const list=(carCards||[]).slice();
  if(!list.length){ carTrack.innerHTML='<div class="hint">Добавь кого-нибудь выше — и они появятся здесь 🎠</div>'; return; }
  list.concat(list).forEach(c=>carTrack.insertAdjacentHTML('beforeend',carCardHTML(c)));
}
renderCarousel();
carTrack?.addEventListener('click',e=>{
  const btn = e.target.closest('[data-del]'); if(!btn) return;
  const id=btn.getAttribute('data-id'); const idx=carCards.findIndex(c=>c.id===id);
  if(idx>-1 && confirm('Удалить из карусели?')){ carCards.splice(idx,1); LS.set('ki_carousel_cards',carCards); renderCarousel(); toast('Удалено из карусели'); }
});
let carOffset=0, carSpeed=0.6, carRAF=null;
function carStep(){
  if(!carTrack || !carTrack.firstElementChild) return;
  carOffset-=carSpeed; carTrack.style.transform=`translateX(${carOffset}px)`;
  const first=carTrack.firstElementChild;
  const fw=first.getBoundingClientRect().width+14;
  if(Math.abs(carOffset)>=fw){ carTrack.appendChild(first); carOffset+=fw; }
  carRAF=requestAnimationFrame(carStep);
}
function carPause(){ if(carRAF){ cancelAnimationFrame(carRAF); carRAF=null; } }
function carResume(){ if(!carRAF) carRAF=requestAnimationFrame(carStep); }
carWrap?.addEventListener('mouseenter',carPause);
carWrap?.addEventListener('mouseleave',carResume);
carResume();

// Добавление с пресетами
const carRoleSel = document.getElementById('carRoleSel');
const carRoleCustom = document.getElementById('carRoleCustom');
const carMedalSel = document.getElementById('carMedalSel');
const carMedalCustom = document.getElementById('carMedalCustom');
const carPhoto = document.getElementById('carPhoto');
const carAddBtn = document.getElementById('carAddBtn');
let carPendingPhoto = null;

function roleValue(){ return (carRoleSel?.value === '__custom__') ? (carRoleCustom.value||'') : (carRoleSel?.value||''); }
function medalValue(){ return (carMedalSel?.value === '__custom__') ? (carMedalCustom.value||'') : (carMedalSel?.value||''); }

carRoleSel?.addEventListener('change', ()=>{ carRoleCustom.style.display = carRoleSel.value === '__custom__' ? 'inline-block' : 'none'; });
carMedalSel?.addEventListener('change', ()=>{ carMedalCustom.style.display = carMedalSel.value === '__custom__' ? 'inline-block' : 'none'; });
carPhoto?.addEventListener('change', e=>{ const f=e.target.files?.[0]; if(!f) return; const r=new FileReader(); r.onload=()=>carPendingPhoto=r.result; r.readAsDataURL(f); });
carAddBtn?.addEventListener('click',()=>{
  const name=($('#carName').value||'').trim(); if(!name) return toast('Введите имя');
  const role=(roleValue()||'').trim(); const medal=(medalValue()||'').trim();
  const img=carPendingPhoto||'images/3.png';
  carCards.push({id:uid(), name, role, medal, img});
  LS.set('ki_carousel_cards',carCards);
  carPendingPhoto=null; if(carPhoto) carPhoto.value='';
  renderCarousel(); toast('Карточка добавлена');
});

/* ===== SHARE / COPY & EXPORT ===== */
$('#copyLink')?.addEventListener('click',async()=>{ try{ await navigator.clipboard.writeText(location.href); toast('Ссылка скопирована 📋'); }catch(e){ toast('Не удалось скопировать'); } });
$('#shareBtn')?.addEventListener('click',async()=>{ if(navigator.share){ try{ await navigator.share({title:document.title,text:'Смотри: Катиндирнет',url:location.href}); }catch(e){} } else toast('Шеринг недоступен — используй копирование'); });
$('#exportMeme')?.addEventListener('click',()=>{ try{
  const stage=$('#memeStage'); if(!stage) return toast('Нет блока для скрина');
  const rect=stage.getBoundingClientRect();
  const canvas=document.createElement('canvas'); canvas.width=Math.floor(rect.width*2); canvas.height=Math.floor(rect.height*2);
  const ctx=canvas.getContext('2d'); ctx.scale(2,2);
  const img=$('#img-joint'); if(!img) return toast('Нет изображения');
  ctx.fillStyle='#0a0f14'; ctx.fillRect(0,0,rect.width,rect.height);
  ctx.drawImage(img,0,0,rect.width,rect.height);
  const text=(memeText && memeText.textContent)||'';
  ctx.font='900 18px Inter, system-ui, Arial'; ctx.fillStyle='rgba(0,0,0,.45)';
  const padX=12; const tw=ctx.measureText(text).width; const th=22;
  ctx.fillRect((rect.width-tw)/2 - padX, rect.height-10-th, tw+padX*2, th);
  ctx.fillStyle='#fff'; ctx.fillText(text, (rect.width-tw)/2, rect.height-13);
  const url=canvas.toDataURL('image/png'); const a=document.createElement('a'); a.href=url; a.download='katindirnet-meme.png'; a.click();
  toast('PNG сохранён');
}catch(e){ toast('Не удалось сделать скрин'); }});

/* ===== TOOLS MODAL ===== */
const toolsModal=$('#toolsModal');
$('#openTools')?.addEventListener('click',()=>toolsModal.classList.add('show'));
$('#toolsClose')?.addEventListener('click',()=>toolsModal.classList.remove('show'));
toolsModal?.addEventListener('click',e=>{ if(e.target===toolsModal) toolsModal.classList.remove('show'); });

/* ===== CONTACTS MODAL ===== */
const contactsModal=$('#contactsModal');
$('#openContacts')?.addEventListener('click',()=>contactsModal.classList.add('show'));
$('#contactsClose')?.addEventListener('click',()=>contactsModal.classList.remove('show'));
contactsModal?.addEventListener('click',e=>{ if(e.target===contactsModal) contactsModal.classList.remove('show'); });

/* ===== BIO ===== */
const bioModal=$('#bioModal'), bioTitle=$('#bioTitle'), bioAbout=$('#bioAbout'), bioTags=$('#bioTags'), bioPhoto=$('#bioPhoto');
function openBio(cfg){
  bioTitle.textContent=(cfg.name||'BIO')+' — BIO';
  bioAbout.textContent=cfg.about||'';
  bioTags.innerHTML='';
  (cfg.skills||[]).forEach(s=>{ const t=document.createElement('span'); t.className='tag'; t.textContent='#'+s; bioTags.appendChild(t); });
  if(cfg.photo){ bioPhoto.src=cfg.photo; bioPhoto.style.display='block'; bioPhoto.alt=cfg.name||'BIO'; }
  else { bioPhoto.style.display='none'; bioPhoto.removeAttribute('src'); }
  bioModal.classList.add('show');
}
$('#bioClose')?.addEventListener('click',()=>bioModal.classList.remove('show'));
bioModal?.addEventListener('click',e=>{ if(e.target===bioModal) bioModal.classList.remove('show'); });
// Remove legacy static founder bio.  The founder button now carries a
// data‑bio attribute in the HTML, so the generic [data-bio] handler below
// will parse and display the biography.  Leaving this blank keeps
// compatibility with older browsers without causing duplicate handlers.
document.querySelectorAll('[data-bio]')?.forEach(btn=>{
  btn.addEventListener('click',()=>{
    try{
      const cfg = JSON.parse(btn.getAttribute('data-bio')||'{}');
      if(!cfg.photo){
        const card = btn.closest('.card');
        const img = card?.querySelector('.avatar img');
        if(img?.src) cfg.photo = img.src;
      }
      openBio(cfg);
    }catch(e){}
  });
});

/* ===== SETTINGS ===== */
const panel=$('#panel');
function togglePanel(force){ const show = (typeof force==='boolean')? force : !panel.classList.contains('show'); panel.classList.toggle('show', show); }
$('#fab')?.addEventListener('click',()=>togglePanel());
$('#openSettings')?.addEventListener('click',()=>togglePanel());
$('#closePanel')?.addEventListener('click',()=>togglePanel(false));

// Тема/акцент
const rootEl = document.documentElement;
const ACCENTS = {
  aqua:   ['#00d4ff','#6afc9c'],
  pink:   ['#ff5fb0','#ffd166'],
  violet: ['#a78bfa','#60a5fa'],
  lime:   ['#b7ff5a','#5efce8'],
  vanilla:['#ffd6a5','#caffbf'],
};
function applyAccent(name){
  const [a1,a2] = ACCENTS[name] || ACCENTS.aqua;
  rootEl.style.setProperty('--accent', a1);
  rootEl.style.setProperty('--accent2', a2);
  LS.set('ki_accent', name);
  document.querySelectorAll('[data-accent]').forEach(b=> b.classList.toggle('active', b.dataset.accent===name));
  syncThemeColorWithBg();
}
document.querySelectorAll('[data-accent]').forEach(b=> b.addEventListener('click', ()=>applyAccent(b.dataset.accent)));

// Zen
function applyZen(on){
  document.body.classList.toggle('zen', !!on);
  LS.set('ki_zen', !!on);
  $('#zenOn')?.classList.toggle('active', !!on);
  $('#zenOff')?.classList.toggle('active', !on);
  syncThemeColorWithBg();
}
$('#zenOn')?.addEventListener('click', ()=>applyZen(true));
$('#zenOff')?.addEventListener('click', ()=>applyZen(false));

// Sound
let audioCtx=null; let audioEnabled=LS.get('ki_sound',false); let volume=LS.get('ki_volume',0.6);
const sfx=document.getElementById('sfx'); if(sfx){ sfx.volume=volume; sfx.loop=true; }
$('#vol')?.addEventListener('input',e=>{ volume=parseFloat(e.target.value)||0; LS.set('ki_volume',volume); if(sfx) sfx.volume=volume; });
function ensureCtx(){ if(!audioCtx){ try{ audioCtx=new (window.AudioContext||window.webkitAudioContext)(); }catch(e){ audioCtx=null; } } return audioCtx; }
function startMusic(){ if(!sfx) return; if(sfx.paused){ const p=sfx.play(); if(p?.catch) p.catch(()=>{}); } }
function stopMusic(){ if(!sfx) return; try{sfx.pause();}catch(e){} }
function playSfx(){ if(audioEnabled) startMusic(); }
function setSound(on){ audioEnabled=on; LS.set('ki_sound',on); $('#soundOn')?.classList.toggle('active',on); $('#soundOff')?.classList.toggle('active',!on); if(on){ startMusic(); ensureCtx() && audioCtx.state==='suspended' && audioCtx.resume(); toast('Музыка ▶️'); } else { stopMusic(); toast('Музыка ⏸'); } }
$('#soundOn')?.addEventListener('click',()=>setSound(true));
$('#soundOff')?.addEventListener('click',()=>setSound(false));

// Cats toggle + density
function setCats(on){ on?startCats():stopCats(); LS.set('ki_cats',on); $('#catsOn')?.classList.toggle('active',on); $('#catsOff')?.classList.toggle('active',!on); }
$('#catsOn')?.addEventListener('click',()=>setCats(true));
$('#catsOff')?.addEventListener('click',()=>setCats(false));
$('#catsDensity')?.addEventListener('input',e=>{
  catsDelay=+e.target.value||260; LS.set('ki_cats_density',catsDelay);
  if(catsTimer){ stopCats(); startCats(); }
});

// Ticker speed
function setTickerSpeed(sec){
  const s = Math.max(8, Math.min(40, +sec||24));
  const track = document.querySelector('.ticker-track');
  if(track) track.style.animationDuration = s + 's';
  LS.set('ki_ticker_speed', s);
}
$('#tickerSpeed')?.addEventListener('input', e=> setTickerSpeed(e.target.value));

// Reset
$('#resetSettings')?.addEventListener('click', ()=>{
  if(!confirm('Сбросить все настройки?')) return;
  ['ki_theme','ki_accent','ki_zen','ki_cats','ki_cats_density','ki_sound','ki_volume','ki_ticker_speed'].forEach(k=> localStorage.removeItem(k));
  applyTheme('neon'); applyAccent('aqua'); applyZen(false); setCats(true); setSound(false); setTickerSpeed(24);
  $('#catsDensity').value = 260; catsDelay=260;
  $('#vol').value = 0.6;
  $('#tickerSpeed').value = 24;
  toast('Настройки сброшены');
});

/* ===== INIT ===== */
(function init(){
  try{
    applyAccent(LS.get('ki_accent','aqua'));
    applyZen(LS.get('ki_zen', false));
    setCats(LS.get('ki_cats', true)); if(LS.get('ki_cats', true)) startCats();
    setSound(LS.get('ki_sound', false));

    const catsDensityEl = $('#catsDensity'); if(catsDensityEl){ catsDensityEl.value = LS.get('ki_cats_density', 260); catsDelay = +catsDensityEl.value; }
    const volEl = $('#vol'); if(volEl){ volEl.value = LS.get('ki_volume', 0.6); }
    const speedEl = $('#tickerSpeed'); if(speedEl){ speedEl.value = LS.get('ki_ticker_speed', 24); }
    setTickerSpeed(LS.get('ki_ticker_speed', 24));

    syncThemeColorWithBg();
  }catch(e){ console.error(e); toast('Init error'); }
})();
