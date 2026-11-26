import { resolve } from 'node:path';
import { startBrowser } from './support/chrome.mjs';
import { startSite } from './support/site.mjs';

const destination = process.argv[2];
if (!destination) throw new Error('Usage: node tests/snapshots.mjs <output-directory>');
const output = resolve(destination);
const site = await startSite();
let browser;
try {
  browser = await startBrowser();
  const views = [
    ['about', '/o-kancelarii/'], ['services', '/specjalizacje/'],
    ['service', '/specjalizacje/' + site.data.services[0].slug + '/'],
    ['team', '/zespol/'], ['career', '/kariera/'], ['archive', '/artykuly/'],
    ['article', '/' + site.data.articles[0].slug + '/'], ['contact', '/kontakt/'],
  ];
  async function settledScreenshot(name) {
    await browser.evaluate(async () => {
      const active = img => {
        const bounds = img.getBoundingClientRect();
        return bounds.bottom > 0 && bounds.top < innerHeight;
      };
      await Promise.all([...document.images].filter(active).map(img => img.decode()));
      await new Promise(resolve => requestAnimationFrame(() => requestAnimationFrame(resolve)));
      await Promise.allSettled(document.getAnimations().map(animation => animation.finished));
      await new Promise(requestAnimationFrame);
    });
    await browser.screenshot(output, name);
  }

  for (const [width, height, mobile] of [[1440, 900, false], [390, 844, true]]) {
    await browser.viewport(width, height, mobile);
    await browser.navigate(site.origin + '/');
    const stages = await browser.evaluate(() => [...document.querySelectorAll('[data-home-stage]')].map(node => ({
      name: node.dataset.homeStage,
      top: node.getBoundingClientRect().top + scrollY + .28 * (parseFloat(node.style.getPropertyValue('--stage-hold')) || 0),
    })));
    for (const { name, top } of stages) {
      await browser.evaluate(y => scrollTo({ top: y, behavior: 'instant' }), top);
      await settledScreenshot(width + '-home-' + name);
    }
    for (const [name, route] of views) {
      await browser.navigate(site.origin + route);
      await settledScreenshot(width + '-' + name);
    }
  }
  console.log('Captured 28 PNGs in ' + output);
} finally {
  try {
    await browser?.close();
  } finally {
    await site.stop();
  }
}
