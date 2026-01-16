#!/usr/bin/env node

const puppeteer = require('puppeteer-extra');
const StealthPlugin = require('puppeteer-extra-plugin-stealth');

puppeteer.use(StealthPlugin());

async function fetchCopartLot(lotId) {
  let browser;

  try {
    browser = await puppeteer.launch({
      headless: 'new',
      executablePath: process.env.PUPPETEER_EXECUTABLE_PATH || '/usr/bin/google-chrome',
      args: process.env.PUPPETEER_ARGS
        ? process.env.PUPPETEER_ARGS.split(' ')
        : [
            '--no-sandbox',
            '--disable-setuid-sandbox',
            '--disable-blink-features=AutomationControlled',
            '--window-size=1920,1080',
            '--disable-gpu',
            '--disable-dev-shm-usage',
          ],
    });

    const page = await browser.newPage();
    await page.setViewport({ width: 1920, height: 1080 });

    // Hide webdriver
    await page.evaluateOnNewDocument(() => {
      Object.defineProperty(navigator, 'webdriver', { get: () => false });
      window.chrome = { runtime: {} };
    });

    const url = `https://www.copart.com/lot/${lotId}`;
    console.error(`[DEBUG] Loading: ${url}`);

    await page.goto(url, {
      waitUntil: 'networkidle2',
      timeout: 60000,
    });

    // allow scripts to populate data
    await page.waitForSelector('body', { timeout: 4000 });

    const images = await page.evaluate(() => {
      const results = [];

      const galleryImages = document.querySelectorAll(
        '.lot-image-gallery img, .image-gallery img, .lot-images img, [class*="image"] img'
      );
      galleryImages.forEach((img) => {
        const src = img.src || img.getAttribute('data-src') || img.getAttribute('data-lazy');
        if (src && src.includes('copart') && !results.includes(src)) {
          results.push(src);
        }
      });

      const dataImages = document.querySelectorAll('[data-images], [data-image-urls]');
      dataImages.forEach((el) => {
        const data = el.getAttribute('data-images') || el.getAttribute('data-image-urls');
        if (data) {
          try {
            const parsed = JSON.parse(data);
            if (Array.isArray(parsed)) {
              parsed.forEach((url) => {
                if (url && !results.includes(url)) {
                  results.push(url);
                }
              });
            }
          } catch (_) {}
        }
      });

      if (window.__NEXT_DATA__?.props?.pageProps?.lotDetails?.images) {
        const nextImages = window.__NEXT_DATA__.props.pageProps.lotDetails.images;
        if (Array.isArray(nextImages)) {
          nextImages.forEach((img) => {
            const url = typeof img === 'string' ? img : img.url || img.src;
            if (url && !results.includes(url)) {
              results.push(url);
            }
          });
        }
      }

      const allImages = document.querySelectorAll('img[src*="copart"]');
      allImages.forEach((img) => {
        const src = img.src;
        if (src && !src.includes('logo') && !src.includes('icon') && !results.includes(src)) {
          results.push(src);
        }
      });

      return results;
    });

    console.error(`[DEBUG] Found ${images.length} images`);

    const lotData = await page.evaluate((id) => {
      if (window.__NEXT_DATA__?.props?.pageProps?.lotDetails) {
        return window.__NEXT_DATA__.props.pageProps.lotDetails;
      }

      return {
        lotId: id,
        vin: document.querySelector('[data-uname="lotsearchLotvin"]')?.textContent?.trim(),
        odometer: document.querySelector('[data-uname="lotsearchLotodometer"]')?.textContent?.trim(),
        make: document.querySelector('[data-uname="lotsearchLotmake"]')?.textContent?.trim(),
        model: document.querySelector('[data-uname="lotsearchLotmodel"]')?.textContent?.trim(),
        year: document.querySelector('[data-uname="lotsearchLotyear"]')?.textContent?.trim(),
      };
    }, lotId);

    const cookies = await page.cookies();
    const cookieObj = {};
    cookies.forEach((cookie) => {
      cookieObj[cookie.name] = cookie.value;
    });

    const result = {
      success: true,
      data: {
        ...lotData,
        images: images.length > 0 ? images : null,
      },
      cookies: cookieObj,
    };

    console.log(JSON.stringify(result));
  } catch (error) {
    console.error(
      JSON.stringify({
        success: false,
        error: error.message,
        stack: error.stack,
      })
    );
    process.exit(1);
  } finally {
    if (browser) {
      await browser.close();
    }
  }
}

const lotId = process.argv[2];
if (!lotId) {
  console.error(JSON.stringify({ success: false, error: 'Lot ID required' }));
  process.exit(1);
}

fetchCopartLot(lotId);
