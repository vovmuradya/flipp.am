#!/usr/bin/env node

/**
 * Загружает данные лота Copart через Chromium с пользовательским профилем.
 * Используется как fallback, когда прямой API возвращает антибот/HTML.
 *
 * Вызывает: node fetch-copart-lot.cjs <lotId>
 */

const puppeteer = require('puppeteer');
const fs = require('fs');
const path = require('path');
const lotId = process.argv[2];
if (!lotId) {
    console.error('Usage: fetch-copart-lot.cjs <lotId>');
    process.exit(1);
}

const USER_AGENT =
    process.env.COPART_USER_AGENT ||
    'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36';

const EXECUTABLE_PATH =
    process.env.PUPPETEER_EXECUTABLE_PATH ||
    '/home/admin/chrome-cache/chrome/linux-142.0.7444.61/chrome-linux64/chrome';

const PROFILE_DIR = '/home/admin/chrome-profile';
const CRASHPAD_DIR = process.env.CHRASHPAD_DIR || '/tmp/chrome-crashpad';
const LOT_URL = `https://www.copart.com/lot/${lotId}`;
const API_URL = `https://www.copart.com/public/data/lotdetails/solr/${lotId}`;

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

    try {
        const page = await browser.newPage();
        await page.setUserAgent(USER_AGENT);
        await page.setExtraHTTPHeaders({
            'Accept-Language': 'en-US,en;q=0.9',
        });

        // Открываем страницу лота, ждём чтобы reese84 и инкапсула установились
        const res = await page.goto(LOT_URL, { waitUntil: 'domcontentloaded', timeout: 60000 });
        const status = res?.status?.() || 0;
        await page.waitForTimeout(2700); // 2.5–3.0s по ТЗ

        // Пробуем получить JSON напрямую из страницы, чтобы использовать текущие cookies
        const apiPayload = await page.evaluate(
            async ({ apiUrl }) => {
                try {
                    const r = await fetch(apiUrl, {
                        credentials: 'include',
                        headers: { Accept: 'application/json, text/plain, */*' },
                    });
                    const text = await r.text();
                    try {
                        return { ok: r.ok, status: r.status, json: JSON.parse(text), raw: null };
                    } catch (_) {
                        return { ok: r.ok, status: r.status, json: null, raw: text.slice(0, 800) };
                    }
                } catch (error) {
                    return { ok: false, status: 0, json: null, raw: error?.message || String(error) };
                }
            },
            { apiUrl: API_URL }
        );

        process.stdout.write(
            JSON.stringify({
                lotId,
                lotUrl: LOT_URL,
                apiUrl: API_URL,
                pageStatus: status,
                response: apiPayload,
            })
        );
    } finally {
        await browser.close();
    }
}

main().catch((err) => {
    console.error(err?.stack || err?.message || String(err));
    process.exit(1);
});
