(() => {
  const menuButton = document.querySelector('.menu-button');
  const mainNav = document.getElementById('main-nav');

  if (menuButton && mainNav) {
    const compactHeader = window.matchMedia('(max-width: 56rem)');
    const syncMenu = () => {
      const closed = compactHeader.matches && menuButton.getAttribute('aria-expanded') === 'false';
      if (closed && mainNav.contains(document.activeElement)) menuButton.focus();
      mainNav.inert = closed;
    };

    const setMenuOpen = (open) => {
      mainNav.parentElement.classList.add('is-interactive');
      menuButton.setAttribute('aria-expanded', String(open));
      syncMenu();
    };

    menuButton.addEventListener('click', () => {
      setMenuOpen(menuButton.getAttribute('aria-expanded') === 'false');
    });

    document.addEventListener('keydown', event => {
      if (event.key === 'Escape' && menuButton.offsetWidth && menuButton.getAttribute('aria-expanded') === 'true') {
        setMenuOpen(false);
        menuButton.focus();
      }
    });

    compactHeader.addEventListener('change', syncMenu);
    menuButton.hidden = false;
    syncMenu();
  }

  if (window.matchMedia('(prefers-reduced-motion: no-preference)').matches) {
    const heroImage = document.querySelector('.home-hero-photo img');
    if (heroImage) {
      const revealImage = () => heroImage.classList.add('is-revealed');
      if (heroImage.complete && heroImage.naturalWidth) revealImage();
      else heroImage.addEventListener('load', revealImage, { once: true });
    }

    if ('IntersectionObserver' in window) {
      const threshold = .15;
      const observer = new IntersectionObserver((entries) => {
        for (const entry of entries) {
          if (entry.intersectionRatio < threshold) continue;
          entry.target.classList.add('is-revealed');
          observer.unobserve(entry.target);
        }
      }, { threshold, rootMargin: '0px 0px -24px' });
      document.querySelectorAll('[data-reveal]').forEach(element => observer.observe(element));
    }
  }

  document.querySelector('.form-status.error')?.focus();

  const search = document.querySelector('[data-service-search]');
  const rows = document.querySelectorAll('[data-service-row]');
  const searchStatus = document.querySelector('[data-service-status]');
  if (search && rows.length) {
    search.addEventListener('input', () => {
      const q = search.value.trim().toLocaleLowerCase('pl');
      let visible = 0;
      rows.forEach(row => {
        const matches = !q || (row.dataset.title || '').includes(q);
        row.hidden = !matches;
        if (matches) visible += 1;
      });

      if (searchStatus) {
        searchStatus.textContent = q
          ? (visible ? `Liczba wyników: ${visible}.` : 'Brak specjalizacji pasujących do wyszukiwania.')
          : '';
      }
    });
  }
})();
