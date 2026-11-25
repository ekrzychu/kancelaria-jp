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

  if (homepage) {
    initHomeStages();
    initHomeNavigation();
    initHomeContact();
    initHomeImageMotion();
    initHomeCursor();
  }

  initReveals(homepage);
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
    const heroHeading = document.querySelector('.home-hero-heading');
    header.classList.add('is-scroll-aware');

    if ('ResizeObserver' in window) {
      // The expanded top menu ends at the photo rather than crossing the headline.
      new ResizeObserver(() => {
        header.style.setProperty('--home-menu-cover', `${heroHeading.getBoundingClientRect().height}px`);
      }).observe(heroHeading);
    }

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

  function homeSurfaceFor(element) {
    return element?.closest('[data-home-surface]')?.dataset.homeSurface ?? 'light';
  }

  function initHomeContact() {
    const contact = document.querySelector('.home-contact-float');
    const face = contact.querySelector('.home-contact-face');
    const faceSurfaces = { light: 'beige', beige: 'burgundy', burgundy: 'light', photo: 'burgundy' };
    const motion = window.matchMedia('(prefers-reduced-motion: no-preference)');
    const canAnimateTone = typeof face.animate === 'function' && window.CSS?.supports('clip-path', 'inset(0)');
    let frame = 0;
    let requestedTone;
    let toneAnimation;

    const overlaps = (first, second) =>
      first.right > second.left && first.left < second.right &&
      first.bottom > second.top && first.top < second.bottom;

    function applyTone(tone) {
      contact.dataset.controlTone = tone;
      contact.dataset.homeSurface = faceSurfaces[tone];
      contact.dispatchEvent(new Event('homesurfacechange', { bubbles: true }));
    }

    function renderTone() {
      if (toneAnimation || requestedTone === contact.dataset.controlTone) return;
      if (!motion.matches || !canAnimateTone) {
        applyTone(requestedTone);
        return;
      }

      // Reveal one complete face so the label always keeps its own contrasting background.
      const nextTone = requestedTone;
      face.dataset.controlTone = nextTone;
      face.hidden = false;
      const animation = face.animate([
        { clipPath: 'inset(100% 0 0)' },
        { clipPath: 'inset(0)' },
      ], { duration: 230, easing: 'cubic-bezier(.22, .61, .36, 1)', fill: 'both' });
      toneAnimation = animation;
      animation.onfinish = () => {
        applyTone(nextTone);
        face.hidden = true;
        toneAnimation = undefined;
        animation.cancel();
        renderTone();
      };
    }

    function updateTone() {
      frame = 0;
      const box = contact.getBoundingClientRect();
      const sample = (x, y) => homeSurfaceFor(
        document.elementsFromPoint(x, y).find(element => !contact.contains(element))
      );
      const center = sample(box.left + box.width / 2, box.top + box.height / 2);

      // Keep the current face until the button clears the boundary between two surfaces.
      if (
        !requestedTone ||
        (sample(box.left + 14, box.top + 7) === center && sample(box.right - 14, box.bottom - 7) === center)
      ) {
        requestedTone = center;
        renderTone();
      }
    }

    function scheduleTone() {
      if (!frame) frame = requestAnimationFrame(updateTone);
    }

    window.addEventListener('scroll', scheduleTone, { passive: true });
    window.addEventListener('resize', scheduleTone, { passive: true });
    contact.addEventListener('transitionend', scheduleTone);
    motion.addEventListener('change', () => {
      if (!motion.matches) toneAnimation?.finish();
    });

    document.addEventListener('focusin', ({ target }) => {
      if (target === contact || !target.matches(':is(a, button):focus-visible') || !target.closest('main, .home-contact, footer')) return;

      if (overlaps(contact.getBoundingClientRect(), target.getBoundingClientRect())) {
        target.scrollIntoView({ behavior: 'instant', block: 'center', inline: 'nearest' });
      }
    });

    updateTone();
  }


  function initReveals(homepage) {
    const motion = window.matchMedia('(prefers-reduced-motion: no-preference)');
    if (!motion.matches || !('IntersectionObserver' in window)) return;

    const targets = document.querySelectorAll('[data-reveal]');
    const listeners = new AbortController();
    const threshold = .15;
    const observer = new IntersectionObserver(entries => {
      for (const { target, isIntersecting, intersectionRatio } of entries) {
        if (!isIntersecting || intersectionRatio < threshold) continue;
        observer.unobserve(target);
        if (homepage && target.matches(':focus-within')) continue;

        const reveal = () => {
          target.classList.add('is-revealed');
          if (target.dataset.reveal === 'experience') countExperience(target, listeners.signal);
        };
        const image = homepage && target.dataset.reveal === 'image' && target.querySelector('img');
        if (image && !image.complete) {
          image.addEventListener('load', reveal, { once: true, signal: listeners.signal });
        } else {
          reveal();
        }
      }
    }, { threshold, rootMargin: homepage ? '0px' : '0px 0px -24px' });

    targets.forEach(target => observer.observe(target));

    if (homepage) {
      document.addEventListener('focusin', event => {
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

  function countExperience(target, signal) {
    const number = target.querySelector('[data-final-value]');
    const value = number.firstElementChild;
    const finalValue = number.dataset.finalValue;
    const duration = 660;
    let frame;
    let start;

    function finish() {
      cancelAnimationFrame(frame);
      value.textContent = finalValue;
      signal.removeEventListener('abort', finish);
    }

    function step(time) {
      start ??= time;
      const elapsed = time - start;
      if (elapsed >= duration) {
        finish();
        return;
      }

      const next = String(Math.floor(Number(finalValue) * elapsed / duration));
      if (value.textContent !== next) value.textContent = next;
      frame = requestAnimationFrame(step);
    }

    value.textContent = '0';
    signal.addEventListener('abort', finish, { once: true });
    frame = requestAnimationFrame(step);
  }

  function initHomeStages() {
    const desktop = window.matchMedia(
      '(min-width: 64rem) and (min-height: 44rem) and (hover: hover) and (pointer: fine) and (prefers-reduced-motion: no-preference)'
    );
    if (!window.CSS?.supports('overflow', 'clip') || !CSS.supports('height', '100dvh') ||
        !CSS.supports('selector(:has(*))')) return;

    const stages = Array.from(document.querySelectorAll('[data-home-stage]'), section => ({
      section,
      viewport: section.querySelector('.home-stage-viewport'),
      content: section.querySelector('.home-stage-content'),
      layout: section.querySelector('.home-frame'),
    }));
    const opening = stages[0];
    const practices = stages.find(stage => stage.section.id === 'specjalizacje');
    const practiceRail = practices.layout.querySelector('.home-practices-rail');
    const practiceHeading = practices.layout.querySelector('.home-practices-intro');
    const clamp = value => Math.max(0, Math.min(value, 1));

    let active = false;
    let height;
    let frame = 0;

    function setProgress(stage, property, value) {
      if (stage[property] === value) return;
      stage[property] = value;
      stage.section.style.setProperty(property, value);
    }

    function draw() {
      frame = 0;
      const positions = stages.map(({ section }) => section.getBoundingClientRect().top);
      const motionDistance = height * .34;
      // Ease the elements in while their section enters, then continue their motion as it scrolls past the top.
      stages.forEach((stage, index) => {
        const top = positions[index];
        const arrival = stage === opening ? 1 : clamp(1 - top / height);
        setProgress(stage, '--stage-entry', arrival);
        setProgress(stage, '--stage-progress', clamp(-top / motionDistance));
      });
    }

    function schedule() {
      if (active && !frame) frame = requestAnimationFrame(draw);
    }

    function setFullscreen(enabled) {
      active = enabled;
      for (const stage of stages) stage.section.toggleAttribute('data-fullscreen', enabled);
    }

    function fits(stage, practiceWindow) {
      if (stage === practices) {
        return practiceHeading.offsetHeight <= practiceWindow &&
          practiceRail.firstElementChild.scrollHeight <= practiceWindow + 1;
      }
      const { paddingTop, paddingBottom } = getComputedStyle(stage.content);
      const room = height - parseFloat(paddingTop) - parseFloat(paddingBottom);
      return stage.layout.offsetHeight <= room + 1;
    }

    function measure() {
      if (frame) cancelAnimationFrame(frame);
      frame = 0;
      if (!desktop.matches) {
        setFullscreen(false);
        return;
      }

      setFullscreen(true);
      height = opening.viewport.clientHeight;
      const practiceWindow = practiceRail.clientHeight - 12;
      if (!stages.every(stage => fits(stage, practiceWindow))) {
        setFullscreen(false);
        return;
      }

      const headingTravel = Math.max(0, practiceWindow - practiceHeading.offsetHeight - height * .055);
      practices.section.style.setProperty('--heading-travel', `${headingTravel}px`);
      draw();
    }

    window.addEventListener('scroll', schedule, { passive: true });
    window.addEventListener('resize', measure, { passive: true });
    desktop.addEventListener('change', measure);
    document.fonts?.ready.then(measure);
    measure();
  }

  function initHomeImageMotion() {
    if (navigator.maxTouchPoints > 0 || !('IntersectionObserver' in window) || !window.CSS?.supports('translate', '0 1%')) return;

    const motion = window.matchMedia(
      '(min-width: 57rem) and (hover: hover) and (pointer: fine) and (prefers-reduced-motion: no-preference)'
    );
    const photos = document.querySelectorAll('[data-scroll-photo]');
    const visible = new Set();
    let frame = 0;

    const observer = new IntersectionObserver(entries => {
      for (const { target, isIntersecting } of entries) {
        if (isIntersecting) visible.add(target);
        else visible.delete(target);
      }
      schedule();
    });

    function draw() {
      frame = 0;
      const viewport = window.innerHeight;
      const offsets = Array.from(visible, photo => {
        const { top, height } = photo.getBoundingClientRect();
        const progress = Math.max(0, Math.min(1, (viewport - top) / (viewport + height)));
        return [photo, (progress - .5) * 16];
      });
      for (const [photo, offset] of offsets) {
        photo.style.setProperty('--photo-scroll-offset', `${offset.toFixed(3)}%`);
      }
    }

    function schedule() {
      if (visible.size && !frame) frame = requestAnimationFrame(draw);
    }

    function sync() {
      document.body.classList.toggle('has-photo-scroll', motion.matches);
      if (motion.matches) {
        photos.forEach(photo => observer.observe(photo));
        window.addEventListener('scroll', schedule, { passive: true });
        window.addEventListener('resize', schedule, { passive: true });
        return;
      }

      observer.disconnect();
      visible.clear();
      cancelAnimationFrame(frame);
      frame = 0;
      photos.forEach(photo => photo.style.removeProperty('--photo-scroll-offset'));
      window.removeEventListener('scroll', schedule);
      window.removeEventListener('resize', schedule);
    }

    motion.addEventListener('change', sync);
    sync();
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
    let refreshTarget = false;
    let pendingTone;
    let toneTimer;

    function hide() {
      cancelAnimationFrame(frame);
      window.clearTimeout(toneTimer);
      frame = lastTime = slowFrames = 0;
      refreshTarget = false;
      pendingTone = undefined;
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
      if (refreshTarget) {
        refreshTarget = false;
        if (!trackTarget(document.elementFromPoint(targetX, targetY), true)) {
          hide();
          return;
        }
      }

      const elapsed = lastTime ? time - lastTime : 16;
      slowFrames = elapsed > 32 ? slowFrames + 1 : 0;
      if (slowFrames >= 5) {
        capability.removeEventListener('change', sync);
        stop();
        return;
      }

      const ease = 1 - Math.exp(-elapsed / 23);
      x += (targetX - x) * ease;
      y += (targetY - y) * ease;
      const distance = Math.hypot(x - targetX, y - targetY);
      if (distance > 12) {
        x = targetX + (x - targetX) * 12 / distance;
        y = targetY + (y - targetY) * 12 / distance;
      }
      const moving = distance > .35;
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

    function trackTarget(element, refresh = false) {
      if (!(element instanceof Element) || !document.body.contains(element) || element.closest(nativeControls)) return false;
      if (target === element && !refresh) return true;

      target = element;
      cursor.classList.toggle('is-interactive', Boolean(target.closest(interactive)));
      const tone = homeSurfaceFor(target);
      if (tone !== pendingTone) {
        window.clearTimeout(toneTimer);
        pendingTone = tone;
        if (!nativeCursorTarget) cursor.dataset.controlTone = tone;
        else if (cursor.dataset.controlTone !== tone) {
          toneTimer = window.setTimeout(() => { cursor.dataset.controlTone = tone; }, 48);
        }
      }

      return true;
    }

    function scheduleTargetRefresh() {
      if (!target) return;
      refreshTarget = true;
      if (!frame) frame = requestAnimationFrame(draw);
    }

    function follow(event) {
      if (event.pointerType !== 'mouse' || event.buttons || !document.hasFocus() || !trackTarget(event.target)) {
        hide();
        return;
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
      document.addEventListener('homesurfacechange', scheduleTargetRefresh, options);
      window.addEventListener('scroll', scheduleTargetRefresh, options);
      window.addEventListener('resize', scheduleTargetRefresh, options);
      window.addEventListener('blur', hide, options);
    }

    capability.addEventListener('change', sync);
    sync();
  }
})();
