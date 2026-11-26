import { resolve } from 'node:path';
import { startBrowser } from './support/chrome.mjs';
import { startSite } from './support/site.mjs';

const [destination, ...options] = process.argv.slice(2);
if (!destination || options.some(option => !['--details', '--stable'].includes(option))) {
  throw new Error('Usage: node tests/snapshots.mjs <output-directory> [--details] [--stable]');
}
const details = options.includes('--details');
const stable = options.includes('--stable');
const output = resolve(destination);
const site = await startSite();
let browser;
try {
  browser = await startBrowser();
  await browser.call('Emulation.setScrollbarsHidden', { hidden: true });
  const views = [
    ['about', '/o-kancelarii/'], ['services', '/specjalizacje/'],
    ['service', '/specjalizacje/' + site.data.services[0].slug + '/'],
    ['team', '/zespol/'], ['career', '/kariera/'], ['archive', '/artykuly/'],
    ['article', '/' + site.data.articles[0].slug + '/'], ['contact', '/kontakt/'],
  ];
  let count = 0;
  async function settledScreenshot(name) {
    await browser.evaluate(async () => {
      const active = img => {
        const bounds = img.getBoundingClientRect();
        return bounds.bottom > 0 && bounds.top < innerHeight;
      };
      await Promise.all([...document.images].filter(active).map(img => img.decode()));
      // A reveal can start a second transition when it reaches the floating Contact button.
      for (let pass = 0; pass < 3; pass++) {
        await new Promise(resolve => requestAnimationFrame(() => requestAnimationFrame(resolve)));
        const animations = document.getAnimations().filter(animation => animation.effect.getComputedTiming().endTime !== Infinity);
        await Promise.allSettled(animations.map(animation => animation.finished));
      }
    });
    await browser.screenshot(output, name);
    count++;
  }

  for (const [width, height, mobile] of [[1440, 900, false], [390, 844, true]]) {
    await browser.viewport(width, height, mobile);
    await browser.call('Emulation.setEmulatedMedia', { features: [{ name: 'prefers-reduced-motion', value: 'no-preference' }] });
    await browser.navigate(site.origin + '/');
    const stages = await browser.evaluate(() => [...document.querySelectorAll('[data-home-stage]')].map(node => ({
      name: node.dataset.homeStage,
      top: node.getBoundingClientRect().top + scrollY + .28 * (parseFloat(node.style.getPropertyValue('--stage-hold')) || 0),
    })));
    for (const { name, top } of stages) {
      await browser.evaluate(y => scrollTo({ top: y, behavior: 'instant' }), top);
      await settledScreenshot(width + '-home-' + name);
    }
    if (stable) {
      await browser.call('Emulation.setEmulatedMedia', { features: [{ name: 'prefers-reduced-motion', value: 'reduce' }] });
    }
    for (const [name, route] of views) {
      await browser.navigate(site.origin + route);
      await settledScreenshot(width + '-' + name);
    }
    if (details) {
      await browser.evaluate(() => {
        const form = document.querySelector('#formularz');
        scrollTo({ top: scrollY + form.getBoundingClientRect().top - 80, behavior: 'instant' });
      });
      await settledScreenshot(width + '-contact-form');
      await browser.navigate(site.origin + '/o-kancelarii/');
      await browser.evaluate(() => scrollTo({ top: document.documentElement.scrollHeight, behavior: 'instant' }));
      await settledScreenshot(width + '-footer');
      if (mobile) {
        await browser.navigate(site.origin + '/o-kancelarii/');
        await browser.evaluate(() => document.querySelector('.menu-button').click());
        await settledScreenshot(width + '-mobile-menu');
      }
    }
  }
  console.log('Captured ' + count + ' PNGs in ' + output);
} finally {
  try {
    await browser?.close();
  } finally {
    await site.stop();
  }
}
