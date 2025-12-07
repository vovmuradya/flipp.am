#!/usr/bin/env node

const { firefox } = require('playwright');

async function run() {
  for (let attempt = 1; attempt <= 7; attempt++) {
    let browser;
    try {
      browser = await firefox.launch({
        headless: true,
        args: [
          '--no-sandbox',
          '--disable-setuid-sandbox',
          '--disable-dev-shm-usage',
          '--disable-gpu',
          '--disable-software-rasterizer',
        ],
      });
    } catch (e) {
      console.log(`❗ Launch failed (${attempt}/7): ${e.message || e}`);
      continue;
    }

    let context;
    try {
      context = await browser.newContext({
        userAgent:
          'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/123 Safari/537.36',
        viewport: { width: 1280, height: 900 },
      });
    } catch (e) {
      console.log(`❗ Context failed (${attempt}/7): ${e.message || e}`);
      await browser.close();
      continue;
    }

    let page;
    try {
      page = await context.newPage();
    } catch (e) {
      console.log(`❗ New page failed (${attempt}/7): ${e.message || e}`);
      await browser.close();
      continue;
    }

    try {
      await page.goto('https://www.copart.com', { waitUntil: 'networkidle', timeout: 60000 });

      // Give JS time to set Incapsula cookies
      await page.waitForTimeout(8000);

      const cookies = await context.cookies();
      const cookieString = cookies.map((c) => `${c.name}=${c.value}`).join('; ');

      if (/reese84|nlbi|visid_incap|incap_ses/.test(cookieString)) {
        console.log(JSON.stringify({ success: true, cookies: cookieString }));
        await browser.close();
        return;
      }

      console.log(`⛔ No reese84 yet (${attempt}/7). Retrying...`);
      await browser.close();
      await new Promise((r) => setTimeout(r, 3000));
    } catch (e) {
      console.log('❗ Failed:', e.message || String(e));
    }
  }

  console.log(JSON.stringify({ success: false, error: 'Incapsula challenge not passed' }));
}

run();
