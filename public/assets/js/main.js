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
    initHomeStages();
    initHomeNavigation();
    initHomeContact();
    initHomeImageMotion();
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
    const interactive = element?.closest('[data-home-surface-interactive]:is(:hover, :focus-visible, :active)');
    return interactive?.dataset.homeSurfaceInteractive
      ?? element?.closest('[data-home-surface]')?.dataset.homeSurface
      ?? 'light';
  }

  function initHomeContact() {
    const contact = document.querySelector('.home-contact-float');
    const face = contact.querySelector('.home-contact-face');
    const faceSurfaces = { light: 'beige', beige: 'burgundy', burgundy: 'light', photo: 'burgundy' };
    const motion = window.matchMedia('(prefers-reduced-motion: no-preference)');
    const canAnimateTone = typeof face.animate === 'function' && window.CSS?.supports('clip-path', 'inset(0)');
    const entranceScroll = 64;
    let pending = motion.matches && window.scrollY < entranceScroll;
    let frame = 0;
    let entranceTimer;
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
      if (pending || !contact.dataset.controlTone || !motion.matches || !canAnimateTone) {
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

    function show() {
      if (!pending) return;
      pending = false;
      contact.classList.replace('is-contact-pending', 'is-contact-ready');
      window.clearTimeout(entranceTimer);
      contact.removeEventListener('focus', show);
      motion.removeEventListener('change', show);
      scheduleTone();
    }

    window.addEventListener('scroll', () => {
      if (window.scrollY >= entranceScroll) show();
      scheduleTone();
    }, { passive: true });
    window.addEventListener('resize', scheduleTone, { passive: true });
    contact.addEventListener('transitionend', scheduleTone);
    motion.addEventListener('change', () => {
      if (!motion.matches) toneAnimation?.finish();
    });

    if (pending) {
      contact.addEventListener('focus', show);
      motion.addEventListener('change', show);
      entranceTimer = window.setTimeout(show, 1450);
      contact.classList.add('is-contact-pending');
    }

    document.addEventListener('focusin', ({ target }) => {
      if (target === contact || !target.matches(':is(a, button):focus-visible') || !target.closest('main, footer')) return;

      if (!target.closest('[data-pinned]') && overlaps(contact.getBoundingClientRect(), target.getBoundingClientRect())) {
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
    const threshold = homepage ? 0 : .15;
    const bottomMargin = homepage ? 16 : -24;
    let initialObservation = homepage;
    const observer = new IntersectionObserver(entries => {
      for (const { target, isIntersecting, intersectionRatio, boundingClientRect, rootBounds } of entries) {
        const type = target.dataset.reveal;
        if (!isIntersecting || intersectionRatio < threshold) continue;
        observer.unobserve(target);

        const alreadyVisible = initialObservation && rootBounds && boundingClientRect.top < rootBounds.bottom - bottomMargin;
        if (alreadyVisible || (homepage && target.matches(':focus-within'))) continue;

        const reveal = () => target.classList.add('is-revealed');
        const image = homepage && type === 'image' && target.querySelector('img');
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

  function initHomeStages() {
    const desktop = window.matchMedia(
      '(min-width: 64rem) and (min-height: 44rem) and (prefers-reduced-motion: no-preference)'
    );
    if (!window.CSS?.supports('overflow', 'clip') || !CSS.supports('height', '100dvh') ||
        !CSS.supports('position', 'sticky') || !CSS.supports('selector(:has(*))')) return;

    const editorial = document.querySelector('.home-editorial-stages');
    const headerBar = document.querySelector('.header-bar');
    const stages = Array.from(document.querySelectorAll('[data-home-stage]'), section => ({
      section,
      kind: section.dataset.homeStage,
      viewport: section.querySelector('.home-stage-viewport'),
      content: section.querySelector('.home-stage-content'),
      layout: section.querySelector('.home-frame'),
    }));
    const practices = stages.find(stage => stage.kind === 'practices');
    const publications = stages.find(stage => stage.kind === 'publications');
    const about = stages.find(stage => stage.kind === 'profile');
    const rail = practices.layout.querySelector('.home-practices-rail');
    const list = rail.firstElementChild;
    const practiceIntro = practices.layout.querySelector('.home-practices-intro');
    const clamp = (value, max = 1) => Math.max(0, Math.min(value, max));

    let active = [];
    let height;
    let frame = 0;
    let expansion = 0;

    function setProgress(stage, property, value) {
      if (stage[property] === value) return;
      stage[property] = value;
      stage.section.style.setProperty(property, value);
    }

    function draw() {
      frame = 0;
      const positions = active.map(stage => stage.section.getBoundingClientRect().top);
      active.forEach((stage, index) => {
        const top = positions[index];
        const entry = stage === about && expansion ? clamp(-top / expansion) : clamp(1 - top / height);
        const travel = -top - (stage === about ? expansion : 0);
        setProgress(stage, '--stage-entry', entry);
        setProgress(stage, '--stage-progress', clamp(travel / stage.hold));
        if (stage === practices) {
          setProgress(stage, '--practice-offset', `${clamp(travel, stage.listTravel)}px`);
        }
      });
    }

    function schedule() {
      if (active.length && !frame) frame = requestAnimationFrame(draw);
    }

    function measure() {
      if (frame) cancelAnimationFrame(frame);
      frame = 0;

      for (const stage of stages) stage.section.toggleAttribute('data-pinned', desktop.matches);
      if (!desktop.matches) {
        active = [];
        expansion = 0;
        editorial.classList.remove('is-expanding');
        return;
      }

      height = stages[0].viewport.clientHeight;
      const practiceWindow = rail.clientHeight - 12; // The rail leaves 6px around focus outlines.
      practices.listTravel = Math.max(0, list.offsetHeight - practiceWindow);
      const headingTravel = Math.max(0, practiceWindow - practiceIntro.offsetHeight - height * .055);
      practices.section.style.setProperty('--heading-travel', `${headingTravel}px`);

      active = stages.filter(stage => {
        if (stage === practices) {
          return practiceIntro.offsetHeight <= practiceWindow && Array.from(list.children)
            .every(item => item.offsetHeight <= practiceWindow);
        }
        const style = getComputedStyle(stage.content);
        const room = stage.content.clientHeight - parseFloat(style.paddingTop) - parseFloat(style.paddingBottom);
        return stage.layout.offsetHeight <= room + 1;
      });

      for (const stage of stages) stage.section.toggleAttribute('data-pinned', active.includes(stage));
      const holds = {
        hero: height * .32,
        practices: practices.listTravel + height * .16,
        publications: height * .36,
        profile: height * .31,
        clients: height * .36,
      };
      for (const stage of active) {
        stage.hold = Math.round(holds[stage.kind]);
        stage.section.style.setProperty('--stage-hold', `${stage.hold}px`);
      }

      // Overlap the viewports during the reveal, then release Publications behind the beige surface.
      expansion = active.includes(publications) && active.includes(about) ? Math.round(height * .48) : 0;
      if (expansion) {
        editorial.style.setProperty('--publication-hold', `${publications.hold}px`);
        editorial.style.setProperty('--about-entry', `${expansion}px`);
        editorial.style.setProperty('--about-hold', `${about.hold}px`);
      }
      editorial.classList.toggle('is-expanding', Boolean(expansion));
      draw();
    }

    document.getElementById('main').addEventListener('focusin', ({ target }) => {
      if (!target.matches('a:focus-visible')) return;
      const stage = active.find(({ section }) => section.contains(target));
      if (!stage) return;

      const box = target.getBoundingClientRect();
      const view = stage.viewport.getBoundingClientRect();
      const railLink = stage === practices && rail.contains(target);
      const bounds = railLink ? rail.getBoundingClientRect() : view;
      const safeTop = Math.max(bounds.top + 6, headerBar.getBoundingClientRect().bottom + 8);
      const safeBottom = Math.min(bounds.bottom - 6, height - 8);
      const hit = document.elementFromPoint(box.left + box.width / 2, box.top + box.height / 2);
      if (box.top >= safeTop && box.bottom <= safeBottom && target.contains(hit)) return;

      let offset = stage.hold * .4;
      if (railLink) {
        const center = box.top - list.getBoundingClientRect().top + box.height / 2;
        offset = clamp(center - rail.clientHeight / 2, practices.listTravel);
      } else if (stage === about) {
        offset += expansion;
      }

      window.scrollTo({ top: window.scrollY + stage.section.getBoundingClientRect().top + offset, behavior: 'instant' });
      if (frame) cancelAnimationFrame(frame);
      draw();
    });

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
