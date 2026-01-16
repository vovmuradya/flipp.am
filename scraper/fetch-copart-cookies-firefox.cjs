#!/usr/bin/env node

/**
 * Fetch Copart cookies via Puppeteer + Stealth (Firefox profile name kept for BC).
 * Output: {"success":true,"cookies":"k=v; ...","count":N}
 */

const puppeteer = require('puppeteer-extra');
const StealthPlugin = require('puppeteer-extra-plugin-stealth');

puppeteer.use(StealthPlugin());

const DEFAULT_EXEC_PATH = '/usr/bin/google-chrome';
const USER_AGENT =
  process.env.COPART_USER_AGENT ||
  'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36';

const sleep = (ms) => new Promise((resolve) => setTimeout(resolve, ms));

async function fetchCopartCookies() {
  const browser = await puppeteer.launch({
    headless: true,
    executablePath: process.env.PUPPETEER_EXECUTABLE_PATH || DEFAULT_EXEC_PATH,
    args: (process.env.PUPPETEER_ARGS ? process.env.PUPPETEER_ARGS.split(' ') : [
      '--no-sandbox',
      '--disable-setuid-sandbox',
      '--disable-blink-features=AutomationControlled',
      '--disable-crash-reporter',
      '--disable-breakpad',
      '--enable-crashpad=0',
      '--no-zygote',
      '--single-process',
      '--window-size=1920,1080',
    ]),
  });

  const page = await browser.newPage();
  await page.setUserAgent(USER_AGENT);
  await page.setViewport({ width: 1920, height: 1080 });
  await page.setExtraHTTPHeaders({ 'accept-language': 'en-US,en;q=0.9' });
  await page.evaluateOnNewDocument(() => {
    Object.defineProperty(navigator, 'webdriver', { get: () => false });
  });

  try {
    await page.goto('https://www.copart.com/', { waitUntil: 'networkidle2', timeout: 120000 });
    await sleep(5000);

    const cookies = await page.cookies();
    const cookiePairs = cookies
      .filter((c) => (c.domain || '').includes('copart.com') && c.name && c.value)
      .map((c) => `${c.name}=${c.value}`);

    console.log(
      JSON.stringify({
        success: true,
        cookies: cookiePairs.join('; '),
        count: cookiePairs.length,
      })
    );
  } catch (error) {
    console.error(
      JSON.stringify({
        success: false,
        error: error.message,
      })
    );
    process.exit(1);
  } finally {
    await browser.close();
  }
}

fetchCopartCookies();
