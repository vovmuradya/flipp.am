#!/usr/bin/env node

/**
 * Обновляет cookies Copart через Chromium (headless=new) с профилем /home/admin/chrome-profile.
 * Выходной формат: {"cookies":"k=v; k2=v2;","count":N,"visited":[{"url": "...","status": 200}, ...]}
 */

const puppeteer = require('puppeteer');
const USER_AGENT =
    process.env.COPART_USER_AGENT ||
    'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36';

const EXECUTABLE_PATH =
    process.env.PUPPETEER_EXECUTABLE_PATH ||
    '/usr/bin/chromium-browser';

const PROFILE_DIR = '/home/admin/chrome-profile';
const TARGET_URLS = [
    'https://www.copart.com',
    'https://www.copart.com/lot/91559035',
    'https://www.copart.com/public/data/lotdetails/solr/lotImages/1',
];

async function main() {
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
            '--disable-crashpad',
            '--disable-crash-reporter',
            '--disable-features=Crashpad2,UseChromeOSCrashReporter,SendFeedbackEmail,CrashpadDebugMode,Breakpad',
            '--disable-logging',
            '--log-level=3',
        ],
        env: {
            ...process.env,
            PUPPETEER_DISABLE_CRASH_REPORTER: '1',
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
