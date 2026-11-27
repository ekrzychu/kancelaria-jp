import assert from 'node:assert/strict';
import { test } from 'node:test';
import { startBrowser } from './support/chrome.mjs';
import { routes, startSite, until } from './support/site.mjs';

test('browser regression against the Laravel application', async t => {
  const app = await startSite();
  t.after(app.stop);
  const browser = await startBrowser();
  t.after(browser.close);
  const { origin, data } = app;
  const { evaluate } = browser;
  const visit = path => browser.navigate(origin + path);
  const pathOf = item => '/' + item.slug + '/';
  const urls = routes(data);
  const errors = [];
  browser.on('Runtime.exceptionThrown', ({ exceptionDetails: error }) => errors.push(error.exception?.description || error.text));
  const reduced = value => browser.call('Emulation.setEmulatedMedia', { features: [{ name: 'prefers-reduced-motion', value }] });
  const scroll = y => evaluate(y => scrollTo({ top: y, behavior: 'instant' }), y);

  await t.test('current routes have local links, assets, and readable layouts', async () => {
    const references = new Set();
    for (const [w, h, mobile] of [[1440, 900, false], [390, 844, true]]) {
      await browser.viewport(w, h, mobile);
      for (const path of w === 1440 ? urls : ['/', '/o-kancelarii/', '/specjalizacje/', '/artykuly/', '/kontakt/']) {
        await visit(path);
        const state = await evaluate(() => {
          const refs = [...document.querySelectorAll('a[href],link[href],script[src],img[src],source[srcset],img[srcset]')].flatMap(element => [
            element.getAttribute('href'), element.getAttribute('src'),
            ...(element.getAttribute('srcset') || '').split(',').map(candidate => candidate.trim().split(/\s+/)[0]),
          ]).filter(Boolean).map(value => new URL(value, location.href)).filter(url => url.origin === location.origin);
          const localFragments = refs.filter(url => url.pathname === location.pathname && url.hash).map(url => decodeURIComponent(url.hash.slice(1)));
          return {
            overflow: document.documentElement.scrollWidth > document.documentElement.clientWidth,
            headings: document.querySelectorAll('h1').length,
            brokenFragments: localFragments.filter(id => !document.getElementById(id)),
            paths: [...new Set(refs.map(url => url.pathname))],
          };
        });
        assert(!state.overflow, path + ' at ' + w);
        assert.equal(state.headings, 1, path);
        assert.deepEqual(state.brokenFragments, [], path);
        state.paths.forEach(value => references.add(value));
      }
    }
    for (const path of references) {
      if (urls.includes(path)) continue;
      assert(path.startsWith('/assets/') || path === '/favicon.ico', 'Unknown internal link ' + path);
      assert((await fetch(origin + path, { method: 'HEAD' })).ok, 'Missing local asset ' + path);
    }
  });

  await t.test('first Tab reveals the skip link and moves focus into main', async () => {
    for (const [w, h, mobile] of [[1440, 900, false], [390, 844, true]]) {
      await browser.viewport(w, h, mobile);
      for (const path of ['/', '/o-kancelarii/', '/artykuly/']) {
        await visit(path);
        await browser.key('Tab', 9);
        assert(await evaluate(() => {
          const link = document.querySelector('.skip-link');
          const r = link.getBoundingClientRect();
          return document.activeElement === link && getComputedStyle(link).outlineStyle !== 'none'
            && link.contains(document.elementFromPoint(r.x + r.width / 2, r.y + r.height / 2));
        }), 'Skip link obscured at ' + path + ' / ' + w);
        await browser.key('Enter', 13);
        assert(await evaluate(() => location.hash === '#main' && document.activeElement.id === 'main'));
      }
    }
  });

  await t.test('desktop navigation and accessible floating Contact', async () => {
    await browser.viewport(1440, 900);
    await visit('/o-kancelarii/');
    assert(await evaluate(() => {
      const nav = document.querySelector('#main-nav');
      const button = document.querySelector('.home-contact-float');
      return nav.querySelector('a[aria-current="page"]').pathname === '/o-kancelarii/'
        && !nav.inert && button.pathname === '/kontakt/' && !!button.getAttribute('aria-label')
        && button.tabIndex === 0;
    }));
    const distance = await evaluate(() => Math.min(550, document.documentElement.scrollHeight - innerHeight - 25));
    await scroll(distance);
    await until(() => evaluate(() => document.querySelector('.site-header').classList.contains('is-compact')
      && document.querySelector('.header-bar').getBoundingClientRect().bottom <= 1), 'header hides while scrolling down');
    await scroll(distance - 55);
    await until(() => evaluate(() => Math.abs(document.querySelector('.header-bar').getBoundingClientRect().top) < 1), 'header returns while scrolling up');
  });

  await t.test('mobile menu supports Enter, Tab, Escape, and focus return', async () => {
    await browser.viewport(390, 844, true);
    await visit('/zespol/');
    await evaluate(() => document.querySelector('.menu-button').focus());
    await browser.key('Enter', 13);
    assert(await evaluate(() => document.activeElement.getAttribute('aria-expanded') === 'true' && !document.querySelector('#main-nav').inert));
    await browser.key('Tab', 9);
    assert(await evaluate(() => !!document.activeElement.closest('#main-nav')));
    await browser.key('Escape', 27);
    assert(await evaluate(() => document.activeElement.matches('.menu-button') && document.activeElement.getAttribute('aria-expanded') === 'false' && document.querySelector('#main-nav').inert));
  });

  await t.test('homepage stages progress forward and backward and survive resize and reload', async () => {
    await browser.viewport(1440, 900);
    await visit('/');
    const stages = () => evaluate(() => [...document.querySelectorAll('[data-home-stage]')].map(el => ({
      id: el.dataset.homeStage, start: el.getBoundingClientRect().top + scrollY,
      hold: parseFloat(el.style.getPropertyValue('--stage-hold')) || 0,
      pinned: el.hasAttribute('data-pinned'),
    })));
    const initial = await stages();
    assert.deepEqual(initial.map(stage => stage.id), ['hero', 'practices', 'publications', 'profile', 'office', 'clients']);
    const desktopCapabilities = await evaluate(() => ({
      hover: matchMedia('(hover: hover)').matches,
      finePointer: matchMedia('(pointer: fine)').matches,
      viewport: [innerWidth, innerHeight],
    }));
    assert(initial.every(stage => stage.pinned && stage.hold > 0), 'Expected full-screen stages with ' + JSON.stringify(desktopCapabilities));
    assert(await evaluate(() => {
      const photo = document.querySelector('.home-hero-photo');
      return photo.offsetParent.matches('.home-stage-content')
        && Math.abs(photo.getBoundingClientRect().width / photo.offsetParent.clientWidth - .57) < .01;
    }), 'the hero photograph stays anchored to the full stage width');
    const measureMotion = selector => evaluate(selector => [...document.querySelectorAll(selector)].map(element => {
      const { x, y, width, height } = element.getBoundingClientRect();
      return [x, y, width, height].map(value => Math.round(value * 100) / 100);
    }), selector);
    const sample = async (y, selector) => {
      await scroll(y);
      await evaluate(() => new Promise(resolve => requestAnimationFrame(() => requestAnimationFrame(resolve))));
      return measureMotion(selector);
    };
    const sceneTargets = {
      hero: '.home-hero-heading, .home-hero-intro, .home-hero-photo',
      practices: '.home-practices-intro, .home-practices-rail',
      publications: '.home-publications-heading, .home-publications-list li',
      profile: '.home-profile-statement, .home-profile-detail, .home-experience, .home-team',
      office: '.home-office-photo img, .home-office-copy',
      clients: '.home-clients h2, .home-client-logos',
    };
    const readOffset = 162;
    for (const stage of initial) {
      const selector = sceneTargets[stage.id];
      const readingPosition = stage.start + stage.hold * .24;
      const reading = await sample(readingPosition, selector);
      if (stage.id === 'practices') {
        const forward = await sample(stage.start + stage.hold * .72, selector);
        assert(forward[0][1] - reading[0][1] > 30 && forward[1][1] < reading[1][1],
          'specializations retain their signature motion');
      } else {
        assert.deepEqual(await sample(stage.start + stage.hold * .45, selector), reading, stage.id + ' has a still reading beat');
        const leavingPosition = stage.start + stage.hold + readOffset;
        const leaving = await sample(leavingPosition, selector);
        const travel = leaving.map((box, index) => {
          const original = reading[index];
          assert.deepEqual([box[0], ...box.slice(2)], [original[0], ...original.slice(2)], stage.id + ' preserves horizontal position and size');
          return original[1] - box[1] - readOffset;
        });
        assert(travel.every(distance => distance > 25) && Math.max(...travel) > 80,
          stage.id + ' adds substantial upward travel beyond the section scroll: ' + travel);
        if (stage.id === 'hero') {
          assert(Math.abs(travel[0] - travel[1]) < .03 && travel[0] - travel[2] > 80, 'hero copy leads the photograph together');
        }
        if (stage.id === 'office') {
          assert(travel[1] - travel[0] > 20, 'office copy leads the image crop');
        }
        if (stage.id === 'publications' || stage.id === 'office') {
          const approach = await sample(stage.start - 360, selector);
          assert(approach[1][1] - reading[1][1] > approach[0][1] - reading[0][1],
            stage.id + ' sequences its leading and following layers');
        }
      }
      assert.deepEqual(await sample(readingPosition, selector), reading, stage.id + ' reverses to the same composition');

      for (const boundary of [stage.start, stage.start + stage.hold]) {
        const before = await sample(boundary - 2, selector);
        const after = await sample(boundary + 2, selector);
        assert(after.every((box, index) => Math.abs(box[1] - before[index][1]) < 7),
          stage.id + ' has no position jump at the sticky boundary');
      }
    }
    const office = initial.find(stage => stage.id === 'office');
    const officePhoto = '.home-office-photo';
    await sample(office.start - 360, officePhoto);
    const officeClip = () => evaluate(() => getComputedStyle(document.querySelector('.home-office-photo')).clipPath);
    const approachingClip = await officeClip();
    await sample(office.start + office.hold * .3, officePhoto);
    assert.notEqual(approachingClip, await officeClip(), 'the frame crops the doorway on approach');
    assert.equal(await officeClip(), 'inset(0px 0px 0%)', 'the doorway is fully visible at the reading position');
    assert(await evaluate(() => {
      const number = document.querySelector('.home-experience-number');
      return number.textContent.trim() === number.dataset.finalValue;
    }), 'the experience figure remains still while reading');
    await browser.key('Tab', 9);
    await evaluate(() => document.querySelector('.home-publications-heading a').focus({ preventScroll: true }));
    await until(() => evaluate(() => {
      const link = document.activeElement;
      const { x, y, width, height } = link.getBoundingClientRect();
      return link.matches('.home-publications-heading a:focus-visible')
        && link.contains(document.elementFromPoint(x + width / 2, y + height / 2));
    }), 'focusing a publication link brings it into view');
    await scroll(0);
    await evaluate(() => document.querySelector('.home-hero-action').click());
    await until(() => evaluate(() => location.hash === '#specjalizacje' && Math.abs(document.querySelector('#specjalizacje').getBoundingClientRect().top) < 100), 'homepage specialization link');
    const restored = await evaluate(() => scrollY);
    await browser.reload();
    await until(async () => Math.abs(await evaluate(() => scrollY) - restored) < 100, 'reload restores scroll position');
    await browser.viewport(960, 844);
    await until(async () => (await stages()).every(stage => !stage.pinned), 'narrow desktop fallback');
    await browser.viewport(1440, 900);
    await until(async () => (await stages()).every(stage => stage.pinned), 'desktop layout restores');
    await reduced('reduce');
    await until(async () => (await stages()).every(stage => !stage.pinned), 'reduced motion fallback');
    assert(await evaluate(() => !document.querySelector('.home-cursor,.is-awaiting-reveal')));
    await reduced('no-preference');

    await browser.viewport(390, 844, true);
    await visit('/?viewport=touch');
    assert((await stages()).every(stage => !stage.pinned), 'Touch layout uses normal document flow');
    await browser.viewport(1440, 900);
  });

  const contactState = () => evaluate(() => {
    const button = document.querySelector('.home-contact-float');
    const rect = button.getBoundingClientRect();
    const { opacity, pointerEvents, visibility } = getComputedStyle(button);
    return {
      inert: button.inert, opacity: Number(opacity), pointerEvents, visibility,
      clickable: button.contains(document.elementFromPoint(rect.x + rect.width / 2, rect.y + rect.height / 2)),
      fading: button.getAnimations().some(animation => animation.transitionProperty === 'opacity'),
    };
  });
  const nextPaint = () => evaluate(() => new Promise(resolve => requestAnimationFrame(() => requestAnimationFrame(resolve))));
  const scrollToFooter = async () => scroll(await evaluate(() => document.documentElement.scrollHeight));

  await t.test('floating Contact stays usable over internal page content and on short pages', async () => {
    for (const [width, height, mobile] of [[1440, 900, false], [390, 844, true]]) {
      await browser.viewport(width, height, mobile);
      await visit('/dokumenty/');
      assert((await contactState()).clickable, 'a footer visible at the top must leave Kontakt available');
      await scrollToFooter();
      await until(async () => (await contactState()).inert, 'footer hides Kontakt on a short page');
      await scroll(0);
      await until(async () => (await contactState()).clickable, 'Kontakt returns on a short page');

      await visit('/specjalizacje/');
      await scroll(await evaluate(() => {
        const row = document.querySelector('.services-directory .editorial-row').getBoundingClientRect();
        const button = document.querySelector('.home-contact-float').getBoundingClientRect();
        return row.y + scrollY + row.height / 2 - button.y - button.height / 2;
      }));
      await nextPaint();
      assert((await contactState()).clickable, 'specialization links must not hide Kontakt');
      const point = await evaluate(() => {
        const { x, y, width, height } = document.querySelector('.home-contact-float').getBoundingClientRect();
        return { x: x + width / 2, y: y + height / 2 };
      });
      for (const type of ['mousePressed', 'mouseReleased']) {
        await browser.call('Input.dispatchMouseEvent', { type, ...point, button: 'left', clickCount: 1 });
      }
      await until(() => evaluate(() => location.pathname === '/kontakt/' && !!document.querySelector('.contact-form')), 'Kontakt opens the contact page');
      await evaluate(() => document.querySelector('.home-contact-float').focus());
      await browser.key('Enter', 13);
      await until(() => evaluate(() => location.hash === '#formularz'), 'keyboard activation opens the form');
    }
  });

  await t.test('floating Contact stays visible through rapid homepage transitions and fades at the footer', async () => {
    for (const [width, height, mobile] of [[1440, 900, false], [390, 844, true]]) {
      await browser.viewport(width, height, mobile);
      await visit('/');
      const boundaries = await evaluate(() => {
        const practices = document.querySelector('#specjalizacje').getBoundingClientRect();
        return [practices.top + scrollY, practices.bottom + scrollY];
      });
      for (const [direction, edges] of [[1, boundaries], [-1, [...boundaries].reverse()]]) {
        for (const edge of edges) {
          await scroll(edge - direction * 140);
          await browser.call('Input.dispatchMouseEvent', { type: 'mouseWheel', x: 120, y: 200, deltaX: 0, deltaY: 340 * direction });
          await until(() => evaluate(({ edge, direction }) => direction * (scrollY - edge) > 0, { edge, direction }), 'cross specialization boundary');
          await nextPaint();
          assert((await contactState()).clickable, 'Kontakt stays usable across the specialization boundary');
        }
      }
      assert(await evaluate(async ([start, end]) => {
        const button = document.querySelector('.home-contact-float');
        for (const y of [start + 200, end + 100, start - 100, end - 120, start + 180]) {
          scrollTo({ top: y, behavior: 'instant' });
          await new Promise(resolve => requestAnimationFrame(() => requestAnimationFrame(resolve)));
          if (button.inert || button.querySelector('.home-contact-face').getAnimations().length > 1) return false;
        }
        return true;
      }, boundaries), 'rapid reversals keep Kontakt visible with only one colour wipe');
      await browser.viewport(width, height - 44, mobile);
      await nextPaint();
      assert((await contactState()).clickable, 'Kontakt remains usable after resizing in Specjalizacje');

      await scrollToFooter();
      await until(async () => {
        const state = await contactState();
        return state.inert && state.fading;
      }, 'footer starts a Kontakt exit animation');
      assert.equal((await contactState()).pointerEvents, 'none');
      assert(await evaluate(() => {
        const button = document.querySelector('.home-contact-float');
        button.focus();
        return document.activeElement !== button;
      }));
      await until(async () => (await contactState()).visibility === 'hidden', 'Kontakt exit completes');

      await scroll(0);
      await until(async () => {
        const state = await contactState();
        return !state.inert && state.fading;
      }, 'reverse scroll starts a Kontakt entrance animation');
      await until(async () => (await contactState()).opacity === 1, 'Kontakt entrance completes');
      await reduced('reduce');
      await scrollToFooter();
      await until(async () => (await contactState()).inert, 'reduced-motion footer');
      assert.equal((await contactState()).opacity, 0);
      assert.equal((await contactState()).fading, false);
      await reduced('no-preference');
    }
  });

  await t.test('homepage and internal footers share their content, geometry and typography', async () => {
    const footer = () => evaluate(() => {
      const root = document.querySelector('.site-footer');
      const origin = root.getBoundingClientRect();
      return {
        direct: root.previousElementSibling === document.querySelector('#main'),
        markup: root.innerHTML,
        geometry: [root, ...root.querySelectorAll('*')].map(el => {
          const { x, y, width, height } = el.getBoundingClientRect();
          const { fontFamily, fontSize, fontWeight, lineHeight } = getComputedStyle(el);
          return [x - origin.x, y - origin.y, width, height, fontFamily, fontSize, fontWeight, lineHeight];
        }),
      };
    });
    await reduced('reduce');
    for (const [width, height, mobile] of [[1440, 900, false], [390, 844, true]]) {
      await browser.viewport(width, height, mobile);
      await visit('/o-kancelarii/');
      const internal = await footer();
      await visit('/');
      const homepage = await footer();
      assert(homepage.direct, 'the shared footer immediately follows the main content');
      assert.deepEqual(homepage, internal);
      assert(await evaluate(() => document.querySelector('.site-footer').classList.contains('site-footer-dark')
        && document.querySelector('.footer-callout h2').textContent.trim() === 'Porozmawiajmy o sprawie.'));
    }
    await reduced('no-preference');
    await browser.viewport(1440, 900);
  });

  await t.test('specialization search folds Polish accents, clears, and reports zero results', async () => {
    await visit('/specjalizacje/');
    const matching = data.services.find(service => service.slug === 'bankowosc-finanse');
    assert(matching, 'Missing specialization search reference record');
    const result = value => evaluate(value => {
      const input = document.querySelector('[data-service-search]');
      input.value = value;
      input.dispatchEvent(new Event('input', { bubbles: true }));
      return [...document.querySelectorAll('[data-service-row]:not([hidden])')].map(row => row.pathname);
    }, value);
    for (const value of [matching.title, 'bankowość', 'bankowosc', 'BANKOWOŚĆ', 'BANKOWOSC', 'BANKOWOŚĆ'.normalize('NFD')]) {
      assert((await result(value)).includes('/specjalizacje/' + matching.slug + '/'), value);
    }
    assert.deepEqual(await result('zz-fixture-no-match-zz'), []);
    assert(await evaluate(() => {
      const clear = document.querySelector('[data-service-clear]');
      clear.click();
      return clear.hidden && document.activeElement.matches('[data-service-search]');
    }));
    assert.equal(await evaluate(() => document.querySelectorAll('[data-service-row]:not([hidden])').length), data.services.length);
  });

  await t.test('archive search, category, year, combinations, sorting and reset', async () => {
    await visit('/artykuly/');
    const archive = data.articles.filter(item => item.title.trim());
    const first = archive.find(item => item.date && item.category);
    assert(first, 'A dated categorized article is needed for this test');
    const set = (name, value) => evaluate((name, value) => {
      const control = document.querySelector('[data-publication-filters]').elements[name];
      control.value = value;
      control.dispatchEvent(new Event(name === 'query' ? 'input' : 'change', { bubbles: true }));
      return [...document.querySelectorAll('#publication-list .publication-entry:not([hidden])')].map(row => row.pathname);
    }, name, value);
    assert((await set('query', first.title)).includes(pathOf(first)));
    assert((await set('query', 'razace niedbalstwo')).includes(pathOf(archive.find(article => article.slug.startsWith('razace-niedbalstwo-')))));
    assert((await set('query', 'socjotechnika')).includes(pathOf(archive.find(article => article.slug.startsWith('razace-niedbalstwo-')))));
    assert.deepEqual(await set('query', 'zz-fixture-no-match-zz'), []);
    await set('query', '');
    assert.deepEqual((await set('category', first.category)).sort(), archive.filter(item => item.category === first.category).map(pathOf).sort());
    const year = first.date.slice(0, 4);
    assert.deepEqual((await set('year', year)).sort(), archive.filter(item => item.category === first.category && item.date?.startsWith(year)).map(pathOf).sort());
    await set('category', '');
    assert.deepEqual((await set('year', year)).sort(), archive.filter(item => item.date?.startsWith(year)).map(pathOf).sort());
    await evaluate(() => document.querySelector('[data-publication-reset]').click());
    const newest = [...archive].sort((a,b) => (b.date || '').localeCompare(a.date || ''));
    const oldest = [...archive].sort((a,b) => Number(Boolean(b.date)) - Number(Boolean(a.date)) || (a.date || '').localeCompare(b.date || ''));
    const byTitle = new Intl.Collator('pl', { sensitivity: 'base', ignorePunctuation: true });
    for (const [sort, expected] of [
      ['newest', newest], ['oldest', oldest],
      ['az', [...newest].sort((a,b) => byTitle.compare(a.title,b.title))],
      ['za', [...newest].sort((a,b) => byTitle.compare(b.title,a.title))],
    ]) assert.deepEqual(await set('sort', sort), expected.map(pathOf));
    assert(await evaluate(() => !document.querySelector('[data-publication-archive] img')));
    const destination = await evaluate(() => {
      const link = document.querySelector('#publication-list a');
      link.focus();
      return link.pathname;
    });
    await browser.key('Enter', 13);
    await until(() => evaluate(path => location.pathname === path && !!document.querySelector('.article-body'), destination), 'archive entry navigation');
  });

  await t.test('article contents, reading progress, figures, and related entries', async () => {
    await browser.viewport(1440, 900);
    for (const article of data.articles) {
      await visit(pathOf(article));
      const articleState = await evaluate(async () => {
        const links = [...document.querySelectorAll('.article-contents a')];
        const img = [...document.querySelectorAll('.article-body img')];
        const figures = document.querySelectorAll('.article-body figure').length;
        await Promise.all(img.map(image => {
          image.loading = 'eager';
          return image.decode();
        }));
        return {
          related: [...document.querySelectorAll('.article-related a.editorial-row')].map(a => a.pathname),
          anchors: links.map(a => decodeURIComponent(a.hash.slice(1))),
          imagesOK: img.every(image => image.naturalWidth > 0 && !!image.alt),
          imageCount: img.length, figures,
        };
      });
      assert(articleState.related.length <= 2 && articleState.related.every(path => path !== pathOf(article) && urls.includes(path)));
      assert.equal(articleState.anchors.length, new Set(articleState.anchors).size);
      assert(articleState.imagesOK);
      assert.equal(articleState.imageCount, [...article.body_html.matchAll(/<img\b/gi)].length);
      assert.equal(articleState.figures, [...article.body_html.matchAll(/<figure\b/gi)].length);
      if (!articleState.anchors.length) continue;
      const id = await evaluate(() => {
        const a = document.querySelector('.article-contents a');
        a.focus();
        return decodeURIComponent(a.hash.slice(1));
      });
      await browser.key('Enter', 13);
      await until(() => evaluate(id => document.activeElement.id === id && document.querySelector('.article-contents [aria-current="location"]')?.hash === location.hash, id), 'article section focus');
      await scroll(await evaluate(() => document.querySelector('.article-body').getBoundingClientRect().bottom + scrollY - innerHeight + 3));
      await until(() => evaluate(() => Number(document.querySelector('.article-progress').style.getPropertyValue('--article-progress')) === 1), 'reading progress reaches the end');
    }
  });

  for (const [path, name] of [['/o-kancelarii/', 'about'], ['/kariera/', 'career']]) {
    await t.test(name + ' timeline tracks markers and retains sequential reveals', async () => {
      await visit(path);
      const query = '[data-' + name + '-process]';
      const markers = await evaluate(query => [...document.querySelector(query).children].map(step => {
        const r = step.querySelector('[class$="-step-marker"]').getBoundingClientRect();
        return r.top + r.height / 2 + scrollY;
      }), query);
      assert(markers.length > 1);
      for (const index of [0, markers.length - 1, 0]) {
        await scroll(markers[index] - 900 * .78 + 3);
        await until(() => evaluate((query, count) => document.querySelector(query).querySelectorAll('.is-reached').length === count, query, index + 1), 'timeline marker count');
        assert(await evaluate((query, index, span) => {
          const list = document.querySelector(query);
          const pixels = parseFloat(list.style.getPropertyValue('--process-progress'));
          return [...list.children].slice(0, index + 1).every(step => step.classList.contains('is-revealed'))
            && pixels > 0 && pixels <= span + 1;
        }, query, index, markers.at(-1) - markers[0]));
      }
    });
  }

  await t.test('server-rendered directories and article remain usable without JavaScript', async () => {
    await browser.viewport(390, 844, true);
    await browser.call('Emulation.setScriptExecutionDisabled', { value: true });
    try {
      await visit('/specjalizacje/');
      assert(await evaluate(count => {
        const input = document.querySelector('[data-service-search]');
        return input.getBoundingClientRect().width === 0 && document.querySelectorAll('[data-service-row]:not([hidden])').length === count;
      }, data.services.length), 'Search input must be hidden until its handlers are attached');
      await visit('/artykuly/');
      assert(await evaluate(count => document.querySelector('[data-publication-filters]').hidden
        && document.querySelectorAll('#publication-list a:not([hidden])').length === count, data.articles.length));
      await visit(pathOf(data.articles[0]));
      assert(await evaluate(() => !!document.querySelector('.article-body').innerText.trim()));
    } finally {
      await browser.call('Emulation.setScriptExecutionDisabled', { value: false });
    }
  });

  await t.test('browser reports no unhandled JavaScript exceptions', () => assert.deepEqual(errors, []));
});
