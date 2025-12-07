#!/usr/bin/env node

/**
 * Fallback загрузка данных лота Copart через Playwright Chromium с профилем.
 * Запуск: node fetch-copart-lot.cjs <lotId>
 * Выход: JSON { lotId, lotUrl, apiUrl, pageStatus, response, logs? }
 */

const { chromium } = require('playwright');
const fs = require('fs');

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
const ENV_COOKIES = process.env.COPART_COOKIES || '';

function parseEnvCookies(raw) {
    if (!raw || typeof raw !== 'string') return [];
    return raw
        .split(';')
        .map((pair) => pair.trim())
        .filter(Boolean)
        .map((pair) => {
            const idx = pair.indexOf('=');
            if (idx === -1) return null;
            const name = pair.slice(0, idx).trim();
            const value = pair.slice(idx + 1).trim();
            if (!name) return null;
            return {
                name,
                value,
                domain: '.copart.com',
                path: '/',
                httpOnly: false,
                secure: true,
            };
        })
        .filter(Boolean);
}

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
        const preloadCookies = parseEnvCookies(ENV_COOKIES);
        if (preloadCookies.length) {
            await browser.addCookies(preloadCookies);
        }

        const page = await browser.newPage();
        await page.setExtraHTTPHeaders({
            'Accept-Language': 'en-US,en;q=0.9',
        });

        // Прогрев главной, чтобы инициировать reese/incapsula
        try {
            await page.goto('https://www.copart.com', { waitUntil: 'networkidle', timeout: 60000 });
            await page.waitForTimeout(3000);
        } catch (_) {
            // игнорируем, продолжим попытки на лоте
        }

        let status = 0;
        const logs = [];
        const MAX_ATTEMPTS = 7;
        const WAIT_AFTER_LOAD = 4000;
        const EXTRA_WAIT = 3000;

        for (let i = 0; i < MAX_ATTEMPTS; i++) {
            const res = await page.goto(LOT_URL, { waitUntil: 'networkidle', timeout: 90000 });
            status = res?.status?.() || 0;
            await page.waitForTimeout(WAIT_AFTER_LOAD);

            let probe = { anti: true, hasReese: false };
            let hasReeseCookie = false;
            for (let inner = 0; inner < 10; inner++) {
                probe = await page.evaluate(() => {
                    const robots = document.querySelector('meta[name="ROBOTS"]');
                    const iframe = document.querySelector('iframe[src*="_Incapsula_Resource"]');
                    const hasReese = document.cookie.split(';').some((c) => c.trim().startsWith('reese84='));
                    const bodyText = (document.body?.innerText || '').toLowerCase();
                    const anti = Boolean(robots || iframe || bodyText.includes('incapsula') || bodyText.includes('access denied'));
                    return { anti, hasReese };
                });
                const cookiesNow = await page.context().cookies();
                hasReeseCookie = cookiesNow.some((c) => c.name === 'reese84');

                if (!probe.anti && (probe.hasReese || hasReeseCookie) && status !== 403) {
                    break;
                }
                await page.waitForTimeout(1000);
            }

            logs.push({ attempt: i + 1, status, probe, hasReeseCookie });

            if (!probe.anti && (probe.hasReese || hasReeseCookie) && status !== 403) {
                break;
            }

            if (i === MAX_ATTEMPTS - 1) {
                console.error(JSON.stringify({ error: 'Incapsula challenge not passed', logs }, null, 2));
                process.exit(1);
            }
            await page.waitForTimeout(EXTRA_WAIT);
        }

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
                logs,
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
