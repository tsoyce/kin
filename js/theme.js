(function(){
  const root=document.documentElement;
  window.LS = window.LS || {
    set(k,v){ try{ localStorage.setItem(k, JSON.stringify(v)); }catch(e){} },
    get(k,d){ try{ const v=JSON.parse(localStorage.getItem(k)); return v==null?d:v; }catch(e){ return d; } }
  };
  function syncThemeColorWithBg(){
    const bg=getComputedStyle(root).getPropertyValue('--bg').trim()||'#0a0e14';
    document.querySelectorAll('meta[name="theme-color"]').forEach(m=>m.setAttribute('content',bg));
  }
  function applyTheme(theme){
    const acc=root.style.getPropertyValue('--accent');
    const acc2=root.style.getPropertyValue('--accent2');
    root.removeAttribute('style');
    if(acc) root.style.setProperty('--accent',acc);
    if(acc2) root.style.setProperty('--accent2',acc2);
    root.classList.remove('theme-light','theme-classic-plus','theme-neon-plus');
    if(theme==='classic'){
      root.style.setProperty('--bg','#0e0e11');
      root.style.setProperty('--panel','#14141a');
      root.style.setProperty('--ink','#eaf2ff');
      root.style.setProperty('--muted','#a9b9d0');
      root.style.setProperty('--logo-bg','linear-gradient(135deg,#555,#222)');
      root.style.setProperty('--logo-ink','#eaf2ff');
      root.style.setProperty('--bg-grad1','#111418');
      root.style.setProperty('--bg-grad2','#0a0d12');
      root.style.setProperty('--bg-grad3','#0a0d12');
      root.style.setProperty('--panel-border','rgba(255,255,255,.1)');
      root.style.setProperty('--footer-bg','rgba(0,0,0,.35)');
      root.style.setProperty('color-scheme','dark');
    } else if(theme==='light'){
      root.classList.add('theme-light');
    } else if(theme==='classic-plus'){
      root.classList.add('theme-classic-plus');
    } else if(theme==='neon-plus'){
      root.classList.add('theme-neon-plus');
    } else { // neon
      root.style.setProperty('--bg','#0a0e14');
      root.style.setProperty('--panel','#0f1723');
      root.style.setProperty('--ink','#eaf2ff');
      root.style.setProperty('--muted','#a9b9d0');
      root.style.setProperty('--logo-bg','conic-gradient(from 210deg,var(--accent),var(--accent2),#ff5fb0)');
      root.style.setProperty('--logo-ink','#071018');
      root.style.setProperty('--bg-grad1','#101a2a');
      root.style.setProperty('--bg-grad2','#0a0f19');
      root.style.setProperty('--bg-grad3','#0b0f15');
      root.style.setProperty('--panel-border','rgba(255,255,255,.1)');
      root.style.setProperty('--footer-bg','rgba(0,0,0,.35)');
      root.style.setProperty('color-scheme','dark');
    }
    window.LS.set('ki_theme',theme);
    document.querySelectorAll('[data-theme]').forEach(b=>b.classList.toggle('active',b.dataset.theme===theme));
    syncThemeColorWithBg();
  }
  document.addEventListener('DOMContentLoaded',()=>{
    document.querySelectorAll('[data-theme]').forEach(b=>b.addEventListener('click',()=>applyTheme(b.dataset.theme)));
    const saved=window.LS.get('ki_theme');
    if(saved){
      applyTheme(saved);
    } else if(window.matchMedia && window.matchMedia('(prefers-color-scheme: light)').matches){
      applyTheme('light');
    } else {
      syncThemeColorWithBg();
    }
  });
  window.applyTheme=applyTheme;
  window.syncThemeColorWithBg=syncThemeColorWithBg;
})();
