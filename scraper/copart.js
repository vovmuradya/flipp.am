const puppeteer = require('puppeteer');
require('dotenv').config();

(async () => {
    const browser = await puppeteer.launch({
        headless: false, // Отключаем headless для отладки
        args: ['--no-sandbox', '--disable-setuid-sandbox']
    });

    const page = await browser.newPage();

    // Установка User-Agent
    await page.setUserAgent(process.env.COPART_USER_AGENT || 'Mozilla/5.0');

    // Установка Referer и Origin
    await page.setExtraHTTPHeaders({
        'Referer': process.env.COPART_REFERER || 'https://www.copart.com',
        'Origin': process.env.COPART_ORIGIN || 'https://www.copart.com'
    });

    // Установка cookies из .env
    const rawCookies = process.env.COPART_COOKIES;
    const cookies = rawCookies.split(';').map(cookie => {
        const [name, ...rest] = cookie.trim().split('=');
        return {
            name,
            value: rest.join('='),
            domain: '.copart.com',
            path: '/',
            httpOnly: false,
            secure: true
        };
    });
    await page.setCookie(...cookies);

    // Переход на лот
    const url = 'https://www.copart.com/lot/68510663';
    await page.goto(url, { waitUntil: 'networkidle2' });

    // Подожди немного
    await page.waitForTimeout(5000); // или page.waitForSelector(...) если точно знаешь

    // Попробуй найти изображения
    const images = await page.evaluate(() => {
        return Array.from(document.querySelectorAll('.carousel-item img'))
            .map(img => img.src);
    });

    if (images.length === 0) {
        console.log('❌ Не удалось найти изображения');
    } else {
        console.log('✅ Найдено изображений:', images.length);
        console.log(images);
    }

    await browser.close();
})();
