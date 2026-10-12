(function () {
  const root = document.documentElement;
  const saved = localStorage.getItem('travel-india-theme');
  const preferred = matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
  function apply(theme) {
    root.dataset.theme = theme;
    const icon = document.querySelector('.theme-icon');
    const button = document.querySelector('.theme-toggle');
    if (icon) icon.className = 'theme-icon fa-solid ' + (theme === 'dark' ? 'fa-sun' : 'fa-moon');
    if (button) {
      const label = theme === 'dark' ? 'Switch to light mode' : 'Switch to dark mode';
      button.setAttribute('aria-label', label);
      button.setAttribute('title', label);
    }
  }
  apply(saved || preferred);

  const backToTop = document.querySelector('.back-to-top');
  function updateBackToTop() {
    if (backToTop) backToTop.classList.toggle('is-visible', window.scrollY > 450);
  }
  window.addEventListener('scroll', updateBackToTop, { passive: true });
  updateBackToTop();

  const currentPage = location.pathname.split('/').pop() || 'main.php';
  document.querySelectorAll('.nav-menu a').forEach(function (link) {
    if (link.getAttribute('href').split('/').pop() === currentPage) link.classList.add('is-active');
  });

  const revealItems = document.querySelectorAll('.box, .service-box, .feature .card, .team_member, .contactinfo, .contactForm, .view-form, .footer');
  if ('IntersectionObserver' in window) {
    revealItems.forEach(function (item) { item.classList.add('reveal-ready'); });
    const observer = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          entry.target.classList.add('is-visible');
          observer.unobserve(entry.target);
        }
      });
    }, { threshold: 0.12 });
    revealItems.forEach(function (item) { observer.observe(item); });
  }

  if (window.jQuery) {
    window.jQuery('.flash-message').delay(4500).fadeOut(350);
  }

  const copy = document.querySelector('[data-typewriter-copy]');
  if (copy && !matchMedia('(prefers-reduced-motion: reduce)').matches) {
    const paragraphs = Array.from(copy.querySelectorAll('p'));
    const texts = paragraphs.map(function (paragraph) { return paragraph.textContent.trim(); });
    paragraphs.forEach(function (paragraph) { paragraph.textContent = ''; });
    copy.classList.add('is-typing');

    function typeParagraph(paragraphIndex) {
      if (paragraphIndex >= paragraphs.length) {
        copy.classList.remove('is-typing');
        return;
      }
      const paragraph = paragraphs[paragraphIndex];
      const content = texts[paragraphIndex];
      let characterIndex = 0;
      paragraph.classList.add('is-active');
      function typeCharacter() {
        paragraph.textContent = content.slice(0, characterIndex += 1);
        if (characterIndex < content.length) {
          setTimeout(typeCharacter, 8);
        } else {
          paragraph.classList.remove('is-active');
          setTimeout(function () { typeParagraph(paragraphIndex + 1); }, 220);
        }
      }
      typeCharacter();
    }

    setTimeout(function () { typeParagraph(0); }, 2300);
  }
  document.addEventListener('click', function (event) {
    const themeButton = event.target.closest('.theme-toggle');
    const navButton = event.target.closest('.nav-toggle');
    const topButton = event.target.closest('.back-to-top');
    const passwordButton = event.target.closest('[data-password-toggle]');
    const notificationButton = event.target.closest('.notification-toggle');

    document.querySelectorAll('.notification-panel').forEach(function (panel) {
      if (!event.target.closest('.notification-menu')) panel.hidden = true;
    });
    if (notificationButton) {
      const panel = notificationButton.nextElementSibling;
      const opening = panel.hidden;
      panel.hidden = !opening;
      notificationButton.setAttribute('aria-expanded', String(opening));
    }

    if (topButton) window.scrollTo({ top: 0, behavior: 'smooth' });
    if (passwordButton) {
      const field = document.getElementById(passwordButton.dataset.passwordToggle);
      if (field) {
        const showing = field.type === 'text';
        field.type = showing ? 'password' : 'text';
        passwordButton.setAttribute('aria-pressed', String(!showing));
        passwordButton.setAttribute('aria-label', showing ? 'Show password' : 'Hide password');
        passwordButton.setAttribute('title', showing ? 'Show password' : 'Hide password');
        const icon = passwordButton.querySelector('i');
        if (icon) {
          icon.classList.toggle('fa-eye', showing);
          icon.classList.toggle('fa-eye-slash', !showing);
        }
      }
    }
    if (themeButton) {
      const next = root.dataset.theme === 'dark' ? 'light' : 'dark';
      localStorage.setItem('travel-india-theme', next);
      apply(next);
    }
    if (navButton) {
      const menu = document.querySelector('.nav-menu');
      const open = menu.classList.toggle('is-open');
      navButton.setAttribute('aria-expanded', String(open));
    }

    const packageLink = event.target.closest('.feature .card a[href="#"]');
    if (packageLink) {
      event.preventDefault();
      window.location.href = 'view.php';
    }
  });
})();
