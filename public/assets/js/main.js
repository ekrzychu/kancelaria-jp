(() => {
  const menuButton = document.querySelector('.menu-button');
  const mainNav = document.getElementById('main-nav');
  const homepage = document.body.classList.contains('homepage');

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

    compactHeader.addEventListener('change', () => {
      if (compactHeader.matches) syncMenu();
      else setMenuOpen(false);
    });

    if (homepage) {
      const header = menuButton.closest('.site-header');
      const closeOutside = event => {
        if (!header.contains(event.target) && menuButton.getAttribute('aria-expanded') === 'true') {
          setMenuOpen(false);
        }
      };
      document.addEventListener('focusin', closeOutside);
      document.addEventListener('pointerdown', closeOutside, { passive: true });
    }

    menuButton.hidden = false;
    syncMenu();
  }

  initReveals(homepage);
  if (homepage) {
    initHomeNavigation();
    initHomeCursor();
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

  function initHomeNavigation() {
    const header = document.querySelector('.site-header');
    header.classList.add('is-scroll-aware');

    let compact = false;
    let visible = false;
    let turnPoint = 0;

    function reserveHeaderSpace() {
      const height = header.getBoundingClientRect().height;
      header.style.setProperty('--full-header-block', `${height}px`);
    }

    function update() {
      const y = Math.max(0, window.scrollY);
      if (y <= 1) {
        if (compact) header.classList.remove('is-compact', 'is-nav-visible', 'can-slide');
        compact = visible = false;
        return;
      }

      if (!compact) {
        if (y < header.offsetHeight + 24) return;
        reserveHeaderSpace();
        header.classList.add('is-compact');
        compact = true;
        turnPoint = y;
        return;
      }

      const distance = y - turnPoint;
      if (visible ? distance >= 18 : distance <= -18) {
        visible = !visible;
        turnPoint = y;
        header.classList.add('can-slide');
        header.classList.toggle('is-nav-visible', visible);
      } else {
        turnPoint = visible ? Math.min(turnPoint, y) : Math.max(turnPoint, y);
      }
    }

    window.addEventListener('scroll', update, { passive: true });
    window.addEventListener('resize', () => {
      if (!compact) return;
      header.classList.remove('is-compact');
      reserveHeaderSpace();
      header.classList.add('is-compact');
    }, { passive: true });
    update();
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

        if (target.dataset.reveal === 'count') {
          animateExperience(target, listeners.signal);
          continue;
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

  function animateExperience(experience, signal) {
    const number = experience.querySelector('[data-experience]');
    const value = number.firstElementChild;
    const finalValue = number.dataset.experience;
    let startedAt;
    let frame;

    function finish() {
      cancelAnimationFrame(frame);
      value.textContent = finalValue;
      experience.classList.remove('is-counting');
      signal.removeEventListener('abort', finish);
    }

    function tick(time) {
      startedAt ??= time;
      const progress = (time - startedAt) / 1000;
      if (progress >= 1) {
        finish();
        return;
      }

      const current = String(Math.floor(progress * Number(finalValue)));
      if (value.textContent !== current) value.textContent = current;
      frame = requestAnimationFrame(tick);
    }

    value.textContent = '0';
    experience.classList.add('is-counting');
    signal.addEventListener('abort', finish, { once: true });
    frame = requestAnimationFrame(tick);
  }

  function initHomeCursor() {
    if (navigator.maxTouchPoints > 0 || !('PointerEvent' in window)) return;

    const capability = window.matchMedia(
      '(hover: hover) and (pointer: fine) and (prefers-reduced-motion: no-preference) and (forced-colors: none)'
    );
    const cursor = document.createElement('div');
    cursor.className = 'home-cursor';
    cursor.setAttribute('aria-hidden', 'true');
    const dot = document.createElement('span');
    dot.className = 'home-cursor-dot';
    const ring = document.createElement('span');
    ring.className = 'home-cursor-ring';
    cursor.append(ring, dot);

    const interactive = 'a[href], button:not(:disabled), summary, [role="button"], [role="link"]';
    const nativeControls = 'input, textarea, select, label, [contenteditable], [role="textbox"], iframe';

    let listeners;
    let frame = 0;
    let lastTime = 0;
    let slowFrames = 0;
    let target, nativeCursorTarget;
    let x, y, targetX, targetY;

    function hide() {
      cancelAnimationFrame(frame);
      frame = lastTime = slowFrames = 0;
      nativeCursorTarget?.classList.remove('home-cursor-target');
      nativeCursorTarget = target = undefined;
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

      dot.style.transform = `translate3d(${targetX}px, ${targetY}px, 0)`;
      ring.style.transform = `translate3d(${x}px, ${y}px, 0)`;
      if (nativeCursorTarget !== target) {
        nativeCursorTarget?.classList.remove('home-cursor-target');
        cursor.classList.add('is-visible');
        target.classList.add('home-cursor-target');
        nativeCursorTarget = target;
      }
      lastTime = moving ? time : 0;
      frame = moving ? requestAnimationFrame(draw) : 0;
    }

    function follow(event) {
      const element = event.target;
      if (
        event.pointerType !== 'mouse' || event.buttons || !document.hasFocus() ||
        !(element instanceof Element) || !document.body.contains(element) || element.closest(nativeControls)
      ) {
        hide();
        return;
      }

      if (target !== element) {
        target = element;
        cursor.classList.toggle('is-interactive', Boolean(target.closest(interactive)));
      }
      targetX = event.clientX;
      targetY = event.clientY;
      if (!nativeCursorTarget) {
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

      document.body.append(cursor);
      const appearance = getComputedStyle(cursor);
      if (appearance.position !== 'fixed' || appearance.pointerEvents !== 'none' || appearance.display === 'none') {
        cursor.remove();
        return;
      }

      listeners = new AbortController();
      const options = { passive: true, signal: listeners.signal };
      document.addEventListener('pointermove', follow, options);
      document.addEventListener('pointerover', follow, options);
      document.addEventListener('pointerout', event => {
        if (!event.relatedTarget) hide();
      }, options);
      document.addEventListener('pointerdown', event => {
        if (!event.target.closest?.(interactive)) hide();
      }, options);
      document.addEventListener('pointercancel', hide, options);
      document.addEventListener('dragstart', hide, options);
      document.addEventListener('keydown', hide, options);
      document.addEventListener('visibilitychange', hide, options);
      window.addEventListener('blur', hide, options);
    }

    capability.addEventListener('change', sync);
    sync();
  }
})();
