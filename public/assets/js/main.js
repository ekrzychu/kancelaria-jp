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

  const homepage = document.body.classList.contains('homepage');
  initReveals(homepage);
  if (homepage) initHomeCursor();

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

  function initReveals(homepage) {
    const motion = window.matchMedia('(prefers-reduced-motion: no-preference)');
    if (!motion.matches || !('IntersectionObserver' in window)) return;

    const targets = document.querySelectorAll('[data-reveal]');
    const listeners = new AbortController();
    const threshold = homepage ? 0 : .15;
    const bottomMargin = homepage ? 16 : -24;
    let initialObservation = homepage;
    const observer = new IntersectionObserver(entries => {
      let rowOrder = 0;
      for (const { target, isIntersecting, intersectionRatio, boundingClientRect, rootBounds } of entries) {
        if (!isIntersecting || intersectionRatio < threshold) continue;
        observer.unobserve(target);
        const alreadyVisible = initialObservation && rootBounds && boundingClientRect.top < rootBounds.bottom - bottomMargin;
        if (alreadyVisible || (homepage && target.matches(':focus-within'))) continue;

        if (target.dataset.reveal === 'row') {
          target.style.setProperty('--row-order', rowOrder++);
        }

        const reveal = () => target.classList.add('is-revealed');
        const image = homepage && target.dataset.reveal === 'image' && target.querySelector('img');
        if (image && !image.complete) {
          image.addEventListener('load', reveal, { once: true, signal: listeners.signal });
        } else {
          reveal();
        }
      }
      initialObservation = false;
    }, { threshold, rootMargin: `0px 0px ${bottomMargin}px` });

    targets.forEach(target => observer.observe(target));

    if (homepage) {
      document.getElementById('main').addEventListener('focusin', event => {
        if (!event.target.matches(':focus-visible')) return;
        const target = event.target.closest('[data-reveal]');
        if (!target) return;
        observer.unobserve(target);
        target.classList.remove('is-revealed');
      }, { signal: listeners.signal });
    }

    motion.addEventListener('change', () => {
      observer.disconnect();
      listeners.abort();
      targets.forEach(target => target.classList.remove('is-revealed'));
    }, { once: true });
  }

  function initHomeCursor() {
    if (navigator.maxTouchPoints > 0 || !('PointerEvent' in window)) return;

    const capability = window.matchMedia(
      '(hover: hover) and (pointer: fine) and (prefers-reduced-motion: no-preference) and (forced-colors: none)'
    );
    const cursor = document.createElement('div');
    cursor.className = 'home-cursor';
    cursor.setAttribute('aria-hidden', 'true');

    let listeners;
    let frame = 0;
    let lastTime = 0;
    let slowFrames = 0;
    let x, y, targetX, targetY;

    function hide() {
      cancelAnimationFrame(frame);
      frame = lastTime = slowFrames = 0;
      cursor.classList.remove('is-visible');
    }

    function stop() {
      listeners?.abort();
      listeners = undefined;
      hide();
      cursor.remove();
    }

    function draw(time) {
      const elapsed = lastTime ? time - lastTime : 16;
      slowFrames = elapsed > 32 ? slowFrames + 1 : 0;
      if (slowFrames >= 5) {
        capability.removeEventListener('change', sync);
        stop();
        return;
      }

      const ease = 1 - Math.exp(-elapsed / 30);
      x += (targetX - x) * ease;
      y += (targetY - y) * ease;
      const moving = Math.abs(targetX - x) > .35 || Math.abs(targetY - y) > .35;
      if (!moving) {
        x = targetX;
        y = targetY;
        slowFrames = 0;
      }

      cursor.style.transform = `translate3d(${x}px, ${y}px, 0)`;
      cursor.classList.add('is-visible');
      lastTime = moving ? time : 0;
      frame = moving ? requestAnimationFrame(draw) : 0;
    }

    function follow(event) {
      if (
        event.pointerType !== 'mouse' || event.buttons ||
        event.target.closest?.('input, textarea, select, label, [contenteditable], [role="textbox"]')
      ) {
        hide();
        return;
      }

      cursor.classList.toggle('is-interactive', Boolean(event.target.closest?.(
        '.home-hero-action, .home-practice-link, .home-section .text-action, .footer-callout .text-action'
      )));
      targetX = event.clientX + (event.clientX < window.innerWidth - 28 ? 16 : -16);
      targetY = event.clientY + (event.clientY < window.innerHeight - 28 ? 16 : -16);
      if (!cursor.classList.contains('is-visible')) {
        x = targetX;
        y = targetY;
      }
      if (!frame) frame = requestAnimationFrame(draw);
    }

    function sync() {
      if (!capability.matches) {
        stop();
        return;
      }

      listeners = new AbortController();
      const options = { passive: true, signal: listeners.signal };
      document.body.append(cursor);
      document.addEventListener('pointermove', follow, options);
      document.addEventListener('pointerover', follow, options);
      document.addEventListener('pointerout', event => {
        if (!event.relatedTarget) hide();
      }, options);
      document.addEventListener('pointerdown', hide, options);
      document.addEventListener('keydown', hide, options);
      document.addEventListener('visibilitychange', hide, options);
      window.addEventListener('blur', hide, options);
    }

    capability.addEventListener('change', sync);
    sync();
  }
})();
