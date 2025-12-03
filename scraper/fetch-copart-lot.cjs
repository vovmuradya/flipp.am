#!/usr/bin/env node

/**
 * Вспомогательный скрипт для выгрузки данных Copart через полноценный браузер.
 * Используется как fallback, когда прямые HTTP-запросы блокируются Incapsula/Akamai.
 */

const puppeteer = require('puppeteer');

const lotId = process.argv[2];
if (!lotId) {
    console.error('Usage: fetch-copart-lot.cjs <lotId>');
    process.exit(1);
}

const USER_AGENT =
    process.env.COPART_USER_AGENT ||
    'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36';

const sleep = (ms) => new Promise((resolve) => setTimeout(resolve, ms));

async function fetchJsonOverPage(page, suffix) {
    return page.evaluate(
        async ({ suffix }) => {
            const endpoint = `https://www.copart.com/public/data/lotdetails/solr/${suffix}`;
            try {
                const response = await fetch(endpoint, {
                    credentials: 'include',
                    headers: {
                        Accept: 'application/json, text/plain, */*',
                    },
                });

                const text = await response.text();
                let data = null;
                try {
                    data = JSON.parse(text);
                } catch (error) {
                    // оставляем raw, чтобы было понятно, что вернулось
                }

                return {
                    ok: response.ok,
                    status: response.status,
                    data,
                    raw: data ? null : text.slice(0, 500),
                };
            } catch (error) {
                return {
                    ok: false,
                    status: 0,
                    data: null,
                    raw: null,
                    error: error?.message || String(error),
                };
            }
        },
        { suffix }
    );
}

async function main() {
    const browser = await puppeteer.launch({
        headless: true,
        executablePath: process.env.PUPPETEER_EXECUTABLE_PATH || undefined,
        args: [
            '--no-sandbox',
            '--disable-setuid-sandbox',
            '--disable-blink-features=AutomationControlled',
            '--disable-dev-shm-usage',
            '--single-process',
            '--no-zygote',
            '--disable-gpu',
        ],
    });

    try {
        const page = await browser.newPage();
        await page.setUserAgent(USER_AGENT);
        await page.setExtraHTTPHeaders({
            'Accept-Language': 'en-US,en;q=0.9',
            Accept: 'text/html,application/json;q=0.9,*/*;q=0.8',
            Referer: 'https://www.copart.com/',
            Origin: 'https://www.copart.com',
        });

        await page.goto(`https://www.copart.com/lot/${lotId}`, {
            waitUntil: 'domcontentloaded',
            timeout: 60000,
        });
        await sleep(1500);

        const details = await fetchJsonOverPage(page, lotId);
        await sleep(500);
        const images = await fetchJsonOverPage(page, `lotImages/${lotId}`);

        const cookies = await page.cookies();
        const cookiePairs = cookies
            .filter((cookie) => (cookie.domain || '').includes('copart.com') && cookie.name && cookie.value)
            .map((cookie) => `${cookie.name.trim()}=${cookie.value.trim()}`);

        process.stdout.write(
            JSON.stringify({
                lotId,
                details,
                images,
                cookies: cookiePairs.join('; '),
                cookieCount: cookiePairs.length,
            })
        );
    } finally {
        await browser.close();
    }
}

main().catch((error) => {
    console.error(error?.stack || error?.message || String(error));
    process.exit(1);
});
