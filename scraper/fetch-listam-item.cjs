#!/usr/bin/env node

/**
 * Простая выгрузка данных объявления с List.am через Puppeteer.
 * Забираем заголовок, цену, описание и ссылки на изображения, обходя Cloudflare-челлендж.
 */

const puppeteer = require('puppeteer');

const targetUrl = process.argv[2];
if (!targetUrl) {
    console.error('Usage: fetch-listam-item.cjs <url>');
    process.exit(1);
}

const USER_AGENT =
    process.env.LISTAM_USER_AGENT ||
    'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36';

const sleep = (ms) => new Promise((resolve) => setTimeout(resolve, ms));

function normalizeImageUrl(src, base) {
    if (!src || typeof src !== 'string') {
        return null;
    }

    const trimmed = src.trim();
    if (trimmed === '') {
        return null;
    }

    try {
        return new URL(trimmed, base).toString();
    } catch (error) {
        return null;
    }
}

function dedupe(list) {
    const seen = new Set();
    const result = [];

    for (const item of list) {
        if (!item || typeof item !== 'string') {
            continue;
        }
        const key = item.trim().toLowerCase();
        if (key === '' || seen.has(key)) {
            continue;
        }
        seen.add(key);
        result.push(item.trim());
    }

    return result;
}

function isAllowedListingImage(url) {
    try {
        const parsed = new URL(url);
        const host = parsed.hostname.toLowerCase();
        const path = parsed.pathname.toLowerCase();
        if (!host.endsWith('list.am')) return false;

        // Явные запреты: любые svg/иконки/редизайн/label
        if (path.endsWith('.svg')) return false;
        if (path.includes('/img/') || path.includes('/icons/') || path.includes('/redesign/')) return false;
        if (path.includes('breadcrumb') || path.includes('chevron') || path.includes('favorite') || path.includes('star')) return false;

        // Разрешённые пути с фото лотов
        if (/\/(f|r)\/\d+\/\d+\.(jpe?g|png|webp)$/i.test(path)) return true;
        if (/\/images\/\d+\/\d+\.(jpe?g|png|webp)$/i.test(path)) return true;
        if (/\/mphotos\/.+\.(jpe?g|png|webp)$/i.test(path)) return true;
    } catch (_) {
        return false;
    }
    return false;
}

async function main() {
    // По умолчанию используем headless режим, чтобы работать на серверах/локали без дисплея.
    // Можно переопределить переменной LISTAM_HEADLESS=false и запускать через xvfb-run.
    const headlessEnv = process.env.LISTAM_HEADLESS;
    const headless = headlessEnv === 'false' ? false : true;

    const browser = await puppeteer.launch({
        headless,
        args: [
            '--no-sandbox',
            '--disable-setuid-sandbox',
            '--disable-blink-features=AutomationControlled',
            '--window-size=1920,1080',
            '--disable-dev-shm-usage',
        ],
    });

    try {
        const page = await browser.newPage();
        const networkImages = new Map(); // url -> { size?: number }
        page.on('response', async (response) => {
            try {
                const req = response.request();
                const url = req.url();
                const type = req.resourceType();
                const ct = response.headers()['content-type'] || '';
                if (type === 'image' || ct.startsWith('image/')) {
                    const sizeHeader = response.headers()['content-length'];
                    const size = sizeHeader ? parseInt(sizeHeader, 10) : null;
                    networkImages.set(url, { size: Number.isFinite(size) ? size : null });
                }
            } catch (_) {
                // ignore network errors
            }
        });
        await page.evaluateOnNewDocument(() => {
            Object.defineProperty(navigator, 'webdriver', { get: () => false });
        });
        await page.setUserAgent(USER_AGENT);
        await page.setExtraHTTPHeaders({
            'Accept-Language': 'ru,en;q=0.9',
            Referer: 'https://www.list.am/',
        });

        await page.goto(targetUrl, {
            waitUntil: 'networkidle2',
            timeout: 60000,
        });

        // Плавно пролистываем страницу вниз, чтобы загрузить все ленивая картинки
        await page.evaluate(async () => {
            await new Promise((resolve) => {
                let totalHeight = 0;
                const distance = 500;
                const timer = setInterval(() => {
                    window.scrollBy(0, distance);
                    totalHeight += distance;
                    if (totalHeight >= document.body.scrollHeight) {
                        clearInterval(timer);
                        resolve();
                    }
                }, 100);
            });
        });

        // Попытка открыть слайдер: кликаем по главной картинке и пролистываем до 30 кадров
        try {
            const mainImageSelector = 'img[itemprop="image"], .gl img, img[src*="/f/"], img[data-src*="/f/"], img[src*="/r/"], img[data-src*="/r/"]';
            const mainImg = await page.$(mainImageSelector);
            if (mainImg) {
                await mainImg.click({ delay: 50 });
                for (let i = 0; i < 25; i++) {
                    await page.keyboard.press('ArrowRight');
                    await sleep(120);
                }
            }
        } catch (_) {
            // best effort
        }

        await sleep(700);

        const raw = await page.evaluate((itemId) => {
            const textContent = (selector) => {
                const el = document.querySelector(selector);
                return el?.innerText?.trim() || el?.textContent?.trim() || null;
            };

            const meta = (name) => {
                return (
                    document.querySelector(`meta[property="${name}"]`)?.getAttribute('content') ||
                    document.querySelector(`meta[name="${name}"]`)?.getAttribute('content') ||
                    null
                );
            };

            const title = meta('og:title') || textContent('h1');
            const description = meta('og:description') || textContent('[itemprop="description"]');
            const priceRaw =
                meta('product:price:amount') ||
                document.querySelector('[itemprop="price"]')?.getAttribute('content') ||
                textContent('[itemprop="price"]');
            const currency = meta('product:price:currency') || document.querySelector('[itemprop="priceCurrency"]')?.getAttribute('content');

            const imageCandidates = [];
            const push = (val) => {
                if (val && typeof val === 'string') {
                    imageCandidates.push(val.trim());
                }
            };

            push(meta('og:image'));

            // Попытка достать точный список картинок из JSON-LD
            const ldNode = document.querySelector('script[type="application/ld+json"]');
            const ldImagesCollected = [];
            if (ldNode?.textContent) {
                try {
                    const parsed = JSON.parse(ldNode.textContent.trim());
                    const data = Array.isArray(parsed) ? parsed.find((d) => d && typeof d === 'object' && (d.image || d.images)) : parsed;
                    const ldImages = data?.images || data?.image;
                    if (Array.isArray(ldImages)) {
                        ldImages.forEach((img) => {
                            ldImagesCollected.push(img);
                            push(img);
                        });
                    } else if (typeof ldImages === 'string') {
                        ldImagesCollected.push(ldImages);
                        push(ldImages);
                    }
                } catch (e) {
                    // ignore json errors
                }
            }

            const hrefIncludesItem = (el) => {
                const href = el?.getAttribute?.('href') || '';
                return itemId && href.includes(`/item/${itemId}`);
            };

            document.querySelectorAll('img').forEach((img) => {
                const src = img.getAttribute('data-src') || img.getAttribute('src');
                if (!src) {
                    return;
                }
                const link = img.closest('a');
                if (link) {
                    const href = link.getAttribute('href') || '';
                    if (href && !hrefIncludesItem(link)) {
                        return; // пропускаем картинки из блоков «похожие»
                    }
                }
                // Если картинка лежит в блоке с data-section (похожие) — пропускаем
                const section = img.closest('[data-section]');
                if (section && !section.hasAttribute('data-section-current')) {
                    return;
                }
                if (!/(list\.am|images\/|mphotos\/)/i.test(src)) {
                    return;
                }
                push(src);
            });
            document.querySelectorAll('[style*="background-image"]').forEach((el) => {
                const link = el.closest('a');
                if (link && !hrefIncludesItem(link)) {
                    return;
                }
                const style = el.style.backgroundImage;
                const match = style.match(/url\\(["']?([^"')]+)["']?\\)/i);
                if (match) {
                    push(match[1]);
                }
            });

            let mileage = null;
            const textBlocks = Array.from(document.querySelectorAll('div,li,span')).map((el) => el.textContent || '');
            for (const chunk of textBlocks) {
                if (mileage) break;
                if (/пробег/i.test(chunk) || /odometer/i.test(chunk) || /կմ/i.test(chunk)) {
                    const match = chunk.match(/([\\d\\s.,]+)\s*(км|km|кմ)/i);
                    if (match) {
                        mileage = match[1].replace(/[^\\d]/g, '');
                        break;
                    }
                }
            }

            return {
                title,
                description,
                priceRaw,
                currency,
                images: imageCandidates,
                ldImages: ldImagesCollected,
                mileage: mileage || null,
            };
        }, (targetUrl.match(/item\/(\d+)/) || [])[1] || null);

        // Предпочитаем изображения из JSON-LD — обычно там только фото текущего объявления
        let images = dedupe((raw.ldImages || []).map((src) => normalizeImageUrl(src, targetUrl)).filter(Boolean));

        // Если JSON-LD пустой, используем собранные на странице
        if (images.length === 0) {
            images = (raw.images || []).map((src) => normalizeImageUrl(src, targetUrl)).filter(Boolean);

            // Оставляем только фото, относящиеся к текущему объявлению (по ID из URL)
            const itemId = (targetUrl.match(/item\/(\d+)/) || [])[1] || null;
            if (itemId) {
                const idPattern = new RegExp(itemId.replace(/[^\\d]/g, ''), 'i');
                const filtered = images.filter((src) => idPattern.test(src));
                if (filtered.length > 0) {
                    images = filtered;
                }
            }

            images = dedupe(images);
        }

        // Убираем очевидные служебные иконки/логотипы
        const iconPatterns = [
            'logo',
            'favicon',
            'sprite',
            'heart',
            'like',
            'arrow',
            'prev',
            'next',
            'star',
            'favorite',
            'fav',
        ];
        images = images.filter((url) => {
            try {
                const parsed = new URL(url);
                const filename = (parsed.pathname.split('/').pop() || '').toLowerCase();
                if (filename.endsWith('.svg')) return false;
                if (iconPatterns.some((p) => filename.includes(p))) return false;
            } catch (_) {
                // ignore URL parse errors
            }
            return true;
        });

        // Разрешаем только «фото объявления» на list.am (основные CDN пути f/r/mphotos/images)
        images = images.filter(isAllowedListingImage);

        // Убираем дубли одного и того же кадра (jpg/webp/разные размеры)
        const seenBases = new Set();
        images = images.filter((url) => {
            try {
                const parsed = new URL(url);
                const filename = (parsed.pathname.split('/').pop() || '').toLowerCase();
                const base = filename.replace(/\.(jpe?g|png|webp)$/i, '');
                if (base && seenBases.has(base)) {
                    return false;
                }
                if (base) {
                    seenBases.add(base);
                }
            } catch (_) {
                // ignore URL parse errors
            }
            return true;
        });

        process.stdout.write(
            JSON.stringify({
                ok: true,
                url: targetUrl,
                title: raw.title || null,
                description: raw.description || null,
                price: raw.priceRaw || null,
                currency: raw.currency || null,
                mileage: raw.mileage || null,
                images,
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
