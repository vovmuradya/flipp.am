#!/usr/bin/env node

/**
 * Copart усилил защиту (Incapsula + Akamai), поэтому простых HTTPS-запросов
 * уже недостаточно — антибот выдаёт HTML вместо JSON, и парсер считает,
 * что нас заблокировали. Здесь запускаем полноценный Chromium через
 * Puppeteer, дожидаемся, пока страница выполнит JS и выставит все нужные
 * cookies (ak_bmsc, bm_sv, visid_incap_* и т.д.), затем собираем их в
 * одном заголовке.
 */

const puppeteer = require('puppeteer');

const USER_AGENT =
    process.env.COPART_USER_AGENT ||
    'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36';

/**
 * Минимальный набор URL, который имитирует рабочий сценарий:
 *  - главная Copart (возвращает Incapsula + Akamai cookie)
 *  - произвольный lot (даёт bm_sz/bm_sv после JS)
 *  - JSON API для фотографий (убедиться, что cookie подходят и для API)
 */
const TARGETS = [
    'https://www.copart.com',
    process.env.COPART_SAMPLE_LOT || 'https://www.copart.com/lot/91559035',
    'https://www.copart.com/public/data/lotdetails/solr/lotImages/1',
];

const SLEEP = (ms) => new Promise((resolve) => setTimeout(resolve, ms));

async function visit(page, url) {
    let status = null;
    try {
        const response = await page.goto(url, {
            waitUntil: 'networkidle2',
            timeout: 60000,
        });
        status = response?.status() ?? null;
        await SLEEP(1000);
    } catch (error) {
        return { url, status, error: error.message };
    }

    return { url, status };
}

function buildCookieHeader(entries) {
    return entries.map(([key, value]) => `${key}=${value}`).join('; ');
}

async function collectCookiesFromPage(page) {
    const jar = await page.cookies();
    const pairs = new Map();

    for (const cookie of jar) {
        if (!cookie?.name || !cookie?.value) {
            continue;
        }

        const domain = cookie.domain || '';
        if (!domain.includes('copart.com')) {
            continue;
        }

        pairs.set(cookie.name.trim(), cookie.value.trim());
    }

    return pairs;
}

async function main() {
    const browser = await puppeteer.launch({
        headless: true,
        args: ['--no-sandbox', '--disable-setuid-sandbox'],
    });

    const page = await browser.newPage();
    await page.setUserAgent(USER_AGENT);
    await page.setExtraHTTPHeaders({
        'Accept-Language': 'en-US,en;q=0.9',
        Accept: 'text/html,application/json;q=0.9,*/*;q=0.8',
        Referer: 'https://www.copart.com/',
        Origin: 'https://www.copart.com',
    });

    const visited = [];
    const aggregated = new Map();

    try {
        for (const target of TARGETS) {
            const result = await visit(page, target);
            visited.push(result);

            const pairs = await collectCookiesFromPage(page);
            for (const [key, value] of pairs.entries()) {
                aggregated.set(key, value);
            }
        }

        const cookies = buildCookieHeader(Array.from(aggregated.entries()));
        process.stdout.write(
            JSON.stringify({
                cookies,
                count: aggregated.size,
                visited,
            }),
        );
    } finally {
        await browser.close();
    }
}

main().catch((error) => {
    console.error(error?.stack || error?.message || String(error));
    process.exit(1);
});
