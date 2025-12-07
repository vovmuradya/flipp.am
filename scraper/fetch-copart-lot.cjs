#!/usr/bin/env node

/**
 * Fallback загрузка данных лота Copart через Playwright Chromium с профилем.
 * Используется, когда прямой API возвращает антибот/HTML.
 *
 * Запуск: node fetch-copart-lot.cjs <lotId>
 * Выход: JSON { lotId, lotUrl, apiUrl, pageStatus, response: { ok, status, json|null, raw|null } }
 */

const { chromium } = require('playwright');
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

const PROFILE_DIR = process.env.CHROME_PROFILE_DIR || '/home/admin/chrome-profile';
const LOT_URL = `https://www.copart.com/lot/${lotId}`;
const API_URL = `https://www.copart.com/public/data/lotdetails/solr/${lotId}`;

async function main() {
    try {
        fs.mkdirSync(PROFILE_DIR, { recursive: true, mode: 0o755 });
    } catch (_) {
        // ignore
    }

    const browser = await chromium.launchPersistentContext(PROFILE_DIR, {
        headless: true,
        userAgent: USER_AGENT,
        viewport: { width: 1280, height: 800 },
        args: [
            '--no-sandbox',
            '--disable-setuid-sandbox',
            '--disable-dev-shm-usage',
            '--disable-gpu',
            '--disable-software-rasterizer',
            '--disable-crashpad',
            '--disable-logging',
            '--log-level=3',
            '--mute-audio',
            '--no-zygote',
        ],
    });

    try {
        const page = await browser.newPage();
        await page.setExtraHTTPHeaders({
            'Accept-Language': 'en-US,en;q=0.9',
        });

        const res = await page.goto(LOT_URL, { waitUntil: 'domcontentloaded', timeout: 60000 });
        const status = res?.status?.() || 0;

        // ждём чтобы reese84/incapsula установились
        await page.waitForTimeout(2800);

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
