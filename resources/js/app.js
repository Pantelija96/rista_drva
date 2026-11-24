/* EcoSync frontend demonstrations. No persistence, form requests or API calls. */
(() => {
 'use strict';
 const q=(s,r=document)=>r.querySelector(s), qa=(s,r=document)=>[...r.querySelectorAll(s)];
 const reduced=matchMedia('(prefers-reduced-motion: reduce)').matches;
 qa('[data-year]').forEach(el=>el.textContent=new Date().getFullYear());
 addEventListener('load',()=>qa('a.scroll-to-top').forEach(a=>a.setAttribute('href','#header')));
 // Porto's bundled Bootstrap handles the focus trap and Escape.
 const modal=(id)=>window.bootstrap?.Modal.getOrCreateInstance(q(id));
 let modalOpener;
 qa('.modal').forEach(el=>{
  el.addEventListener('show.bs.modal',event=>{modalOpener=event.relatedTarget||document.activeElement;});
  el.addEventListener('hidden.bs.modal',()=>{if(modalOpener?.isConnected)modalOpener.focus();modalOpener=null;});
 });
 qa('[data-demo-notice]').forEach(btn=>btn.addEventListener('click',()=>{
  q('#demo-title').textContent=btn.dataset.demoTitle||(document.documentElement.lang === 'en' ? 'Privacy' : 'Privatnost');
  q('#demo-message').textContent=btn.dataset.demoNotice;
  modal('#demo-modal')?.show(btn);
 }));
 // Porto owns the mobile collapse; synchronize its accessible expanded state.
 const menu=q('#ngo-nav'),toggle=q('.header-btn-collapse-nav');
 if(menu&&toggle){
  new MutationObserver(()=>toggle.setAttribute('aria-expanded',menu.classList.contains('show')?'true':'false')).observe(menu,{attributes:true,attributeFilter:['class']});
 }
 qa('[data-gallery-filter]').forEach(button=>button.addEventListener('click',()=>{
  qa('[data-gallery-filter]').forEach(b=>{const active=b===button;b.classList.toggle('active',active);b.setAttribute('aria-pressed',String(active));});
  let count=0;qa('[data-album-category]').forEach(card=>{const visible=button.dataset.galleryFilter==='all'||card.dataset.albumCategory===button.dataset.galleryFilter;card.hidden=!visible;if(visible)count++;});
  q('#album-count').textContent=`${count} ${document.documentElement.lang === 'en' ? 'albums' : 'albuma'}`;
 }));
 if (window.jQuery?.magnificPopup) {
  window.jQuery('[data-gallery-image]').magnificPopup({type:'image',gallery:{enabled:true,tPrev:document.documentElement.lang === 'en' ? 'Previous' : 'Prethodna',tNext:document.documentElement.lang === 'en' ? 'Next' : 'Sledeća',tCounter:document.documentElement.lang === 'en' ? '%curr% of %total%' : '%curr% od %total%'},image:{titleSrc: element => window.jQuery('<div>').text(element.el.attr('title') || '').html()}});
 }
 // Values remain correct without JS; animation is optional and respects reduced motion.
 const counters=qa('[data-count]');
 if(!reduced&&'IntersectionObserver' in window){
  const observer=new IntersectionObserver(entries=>entries.forEach(entry=>{
   if(!entry.isIntersecting)return;
   const el=entry.target,target=Number(el.dataset.count),start=performance.now();
   const tick=now=>{const p=Math.min(1,(now-start)/1000);el.textContent=p===1?el.dataset.final:Math.round(target*(1-(1-p)**3)).toLocaleString('sr-Latn');if(p<1)requestAnimationFrame(tick);};
   requestAnimationFrame(tick);observer.unobserve(el);
  }),{threshold:.3});counters.forEach(el=>observer.observe(el));
 }
})();

const secureForm = document.querySelector('[data-secure-token]');
if (secureForm) {
    const token = new URLSearchParams(location.hash.slice(1)).get('token');
    if (token && /^[a-f0-9]{64}$/.test(token)) secureForm.querySelector('[name="token"]').value = token;
    history.replaceState(null, '', location.pathname);
}

let publicFormDirty = false;
document.querySelectorAll('main form').forEach(form => form.addEventListener('input', () => { publicFormDirty = true; }));
document.querySelectorAll('[data-locale-switch]').forEach(link => {
    if (location.hash && !location.hash.includes('token=')) link.hash = location.hash;
    link.addEventListener('click', event => {
        if (publicFormDirty && !confirm(document.body.dataset.draftWarning)) event.preventDefault();
    });
});
