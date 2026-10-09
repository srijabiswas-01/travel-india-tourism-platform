</main></div><script>
document.querySelector('.admin-menu')?.addEventListener('click',()=>document.querySelector('.admin-sidebar')?.classList.toggle('open'));
const adminNotificationToggle=document.querySelector('.admin-notification-toggle');
const adminNotificationPanel=document.querySelector('.admin-notification-panel');
adminNotificationToggle?.addEventListener('click',()=>{const open=adminNotificationPanel.hidden;adminNotificationPanel.hidden=!open;adminNotificationToggle.setAttribute('aria-expanded',String(open))});
document.addEventListener('click',event=>{if(adminNotificationPanel&&!adminNotificationPanel.hidden&&!event.target.closest('.admin-notification-menu')){adminNotificationPanel.hidden=true;adminNotificationToggle?.setAttribute('aria-expanded','false')}});
document.addEventListener('keydown',event=>{if(event.key==='Escape'&&adminNotificationPanel&&!adminNotificationPanel.hidden){adminNotificationPanel.hidden=true;adminNotificationToggle?.setAttribute('aria-expanded','false');adminNotificationToggle?.focus()}});
const adminThemeButton=document.querySelector('.admin-theme-toggle');
const renderAdminTheme=()=>{const dark=document.documentElement.dataset.adminTheme==='dark';if(adminThemeButton){adminThemeButton.innerHTML='<i class="fa-solid fa-'+(dark?'sun':'moon')+'"></i>';adminThemeButton.title=dark?'Switch to light mode':'Switch to dark mode';adminThemeButton.setAttribute('aria-label',adminThemeButton.title)}};
adminThemeButton?.addEventListener('click',()=>{const next=document.documentElement.dataset.adminTheme==='dark'?'light':'dark';document.documentElement.dataset.adminTheme=next;localStorage.setItem('travelIndiaAdminTheme',next);renderAdminTheme()});renderAdminTheme();
</script></body></html>
