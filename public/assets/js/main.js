(() => {
  document.documentElement.classList.add('js');

  const header = document.querySelector('[data-header]');
  const menuButton = document.querySelector('[data-menu-button]');
  const menu = document.querySelector('[data-menu]');

  const onScroll = () => {
    if (header) header.classList.toggle('is-compact', window.scrollY > 28);
  };
  onScroll();
  window.addEventListener('scroll', onScroll, { passive: true });

  if (menuButton && menu) {
    const closeMenu = () => {
      menu.classList.remove('is-open');
      menuButton.setAttribute('aria-expanded', 'false');
      menuButton.textContent = 'Menu';
    };

    menuButton.addEventListener('click', () => {
      const open = menu.classList.toggle('is-open');
      menuButton.setAttribute('aria-expanded', String(open));
      menuButton.textContent = open ? 'Zamknij' : 'Menu';
    });

    menu.addEventListener('click', event => {
      if (event.target.closest('a')) closeMenu();
    });

    document.addEventListener('keydown', event => {
      if (event.key === 'Escape' && menu.classList.contains('is-open')) {
        closeMenu();
        menuButton.focus();
      }
    });
  }

  const reveal = document.querySelectorAll('.page-reveal');
  if ('IntersectionObserver' in window) {
    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.classList.add('is-visible');
          observer.unobserve(entry.target);
        }
      });
    }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });
    reveal.forEach(el => observer.observe(el));
  } else {
    reveal.forEach(el => el.classList.add('is-visible'));
  }

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
