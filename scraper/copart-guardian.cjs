#!/usr/bin/env node

const { execSync } = require('child_process');

const TEST_URL = 'https://www.copart.com/public/data/lotdetails/solr/lotImages/1';

async function testCookies() {
    try {
        const res = await fetch(TEST_URL, {
            headers: { cookie: process.env.COPART_COOKIES || '' },
        });
        return res.status === 200;
    } catch (e) {
        return false;
    }
}

async function refreshCookies() {
    console.log('🔄 Cookies expired — refreshing clearance...');
    execSync(
        `
PLAYWRIGHT_BROWSERS_PATH="$HOME/.cache/ms-playwright" \
CHROME_PROFILE_DIR="$HOME/.cache/chrome-playwright" \
node scraper/fetch-copart-cookies-firefox.cjs --debug
`,
        { stdio: 'inherit' }
    );
    console.log('✔ Cookies refreshed');
}

(async () => {
    console.log('🔍 Checking Copart cookie validity...');
    const ok = await testCookies();

    if (!ok) {
        await refreshCookies();
    } else {
        console.log('✔ Cookies valid — scraper can work');
    }
})();
