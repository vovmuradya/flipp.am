#!/usr/bin/env node

/**
 * Получает cookies Copart через Playwright Firefox.
 * Выход: {"cookies":"k=v; ...","count":N,"visited":[{url,status},...]}
 */

const { chromium } = require('playwright');
const fs = require('fs');

const USER_AGENT =
  process.env.COPART_USER_AGENT ||
  'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:119.0) Gecko/20100101 Firefox/119.0';

const PROFILE_DIR = process.env.FIREFOX_PROFILE_DIR || process.env.CHROME_PROFILE_DIR || '/home/admin/chrome-profile';
const TARGET_URLS = [
  'https://www.copart.com',
  'https://www.copart.com/lot/91559035',
  'https://www.copart.com/public/data/lotdetails/solr/lotImages/1',
];

async function main() {
  try {
    fs.mkdirSync(PROFILE_DIR, { recursive: true, mode: 0o755 });
  } catch (_) {
    // ignore
  }

  const browser = await chromium.launchPersistentContext(PROFILE_DIR, {
    headless: false,
    userAgent: USER_AGENT,
    viewport: { width: 1280, height: 800 },
    args: [
      '--no-sandbox',
      '--disable-setuid-sandbox',
      '--disable-dev-shm-usage',
      '--disable-gpu',
      '--disable-software-rasterizer',
      '--mute-audio',
      '--disable-crashpad',
      '--disable-features=Crashpad2,UseChromeOSCrashReporter,SendFeedbackEmail,CrashpadDebugMode,Breakpad',
      '--disable-web-security',
      '--disable-blink-features=AutomationControlled',
      '--disable-features=IsolateOrigins,site-per-process',
      '--disable-infobars',
      '--window-size=1280,800',
    ],
  });

  const page = await browser.newPage();
  const visited = [];

  for (const url of TARGET_URLS) {
    try {
      if (url === 'https://www.copart.com') {
        const res = await page.goto('https://www.copart.com/', { waitUntil: 'networkidle', timeout: 180000 });
        visited.push({ url, status: res?.status() || 0 });
        await page.waitForTimeout(20000);

        // Дополнительный прогрев: открываем лот и API в той же сессии
        try {
          const lotRes = await page.goto('https://www.copart.com/lot/91559035', {
            waitUntil: 'domcontentloaded',
            timeout: 120000,
          });
          visited.push({ url: 'https://www.copart.com/lot/91559035', status: lotRes?.status() || 0 });
          await page.waitForTimeout(5000);

          const apiRes = await page.goto('https://www.copart.com/public/data/lotdetails/solr/lotImages/1', {
            waitUntil: 'domcontentloaded',
            timeout: 120000,
          });
          visited.push({ url: 'https://www.copart.com/public/data/lotdetails/solr/lotImages/1', status: apiRes?.status() || 0 });
        } catch (e) {
          visited.push({ url: 'https://www.copart.com/lot/91559035', status: 0, error: e.message });
        }
      } else {
        const res = await page.goto(url, { waitUntil: 'domcontentloaded', timeout: 60000 });
        visited.push({ url, status: res?.status() || 0 });
        await page.waitForTimeout(1200);
      }
    } catch (e) {
      visited.push({ url, status: 0, error: e.message });
    }
  }

  const contextCookies = await browser.cookies();

  const cookiePairs = contextCookies
    .filter((c) => (c.domain || '').includes('copart.com') && c.name && c.value)
    .map((c) => `${c.name}=${c.value}`);

  process.stdout.write(
    JSON.stringify({
      cookies: cookiePairs.join('; '),
      count: cookiePairs.length,
      visited,
    })
  );

  // Держим контекст открытым ещё 60 секунд (для локальной отладки clearance)
  await new Promise((r) => setTimeout(r, 60000));
}

main().catch((err) => {
  console.error(err?.stack || err?.message || String(err));
  process.exit(1);
});
