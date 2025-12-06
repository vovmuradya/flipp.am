#!/usr/bin/env node

/**
 * Обновляет cookies Copart через Chromium (headless=new) с профилем /home/admin/chrome-profile.
 * Выходной формат: {"cookies":"k=v; k2=v2;","count":N,"visited":[{"url": "...","status": 200}, ...]}
 */

const puppeteer = require('puppeteer');
const fs = require('fs');
const path = require('path');
const USER_AGENT =
    process.env.COPART_USER_AGENT ||
    'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36';

const EXECUTABLE_PATH =
    process.env.PUPPETEER_EXECUTABLE_PATH ||
    '/home/admin/chrome-cache/chrome/linux-142.0.7444.61/chrome-linux64/chrome';

const PROFILE_DIR = '/home/admin/chrome-profile';
const CRASHPAD_DIR = process.env.CHRASHPAD_DIR || '/tmp/chrome-crashpad';
const TARGET_URLS = [
    'https://www.copart.com',
    'https://www.copart.com/lot/91559035',
    'https://www.copart.com/public/data/lotdetails/solr/lotImages/1',
];

async function main() {
    try {
        fs.mkdirSync(CRASHPAD_DIR, { recursive: true, mode: 0o755 });
    } catch (_) {
        // ignore
    }
    const crashpadHandler = path.join(path.dirname(EXECUTABLE_PATH), 'chrome_crashpad_handler');

    const browser = await puppeteer.launch({
        headless: 'new',
        executablePath: EXECUTABLE_PATH,
        userDataDir: PROFILE_DIR,
        args: [
            '--no-sandbox',
            '--disable-setuid-sandbox',
            '--disable-dev-shm-usage',
            '--disable-gpu',
            '--disable-extensions',
            '--no-zygote',
            `--crashpad-handler=${crashpadHandler}`,
            `--database=${CRASHPAD_DIR}`,
            `--metrics-dir=${CRASHPAD_DIR}`,
            '--disable-logging',
            '--log-level=3',
        ],
        env: {
            ...process.env,
        },
    });

    const page = await browser.newPage();
    await page.setUserAgent(USER_AGENT);
    await page.setExtraHTTPHeaders({
        'Accept-Language': 'en-US,en;q=0.9',
    });

    const visited = [];

    for (const url of TARGET_URLS) {
        try {
            const res = await page.goto(url, { waitUntil: 'domcontentloaded', timeout: 60000 });
            visited.push({ url, status: res?.status?.() || 0 });
            await page.waitForTimeout(1000);
        } catch (e) {
            visited.push({ url, status: 0, error: e.message });
        }
    }

    const cookies = await page.cookies();
    await browser.close();

    const cookiePairs = cookies
        .filter((c) => (c.domain || '').includes('copart.com') && c.name && c.value)
        .map((c) => `${c.name}=${c.value}`);

    process.stdout.write(
        JSON.stringify({
            cookies: cookiePairs.join('; '),
            count: cookiePairs.length,
            visited,
        })
    );
}

main().catch((err) => {
    console.error(err?.stack || err?.message || String(err));
    process.exit(1);
});
