#!/usr/bin/env node

const fetch = require('node-fetch');
const { execSync } = require('child_process');

const LOT = process.argv[2];
if (!LOT) {
  console.error('Usage: fetch-copart-lot.cjs <lotId>');
  process.exit(1);
}

const API = `https://www.copart.com/public/data/lotdetails/solr/${LOT}`;

async function requestLot() {
  try {
    const res = await fetch(API, {
      headers: { cookie: process.env.COPART_COOKIES || '' },
    });

    if (res.status === 200) {
      console.log('✔ LOT OK');
      return res.json();
    }

    if (res.status === 403) {
      console.log('⚠  LOT DENIED — refreshing cookies...');
      execSync(
        `
PLAYWRIGHT_BROWSERS_PATH="$HOME/.cache/ms-playwright" \\
CHROME_PROFILE_DIR="$HOME/.cache/chrome-playwright" \\
node scraper/fetch-copart-cookies-firefox.cjs --debug
`,
        { stdio: 'inherit' }
      );

      console.log('⏳ retrying...');
      return requestLot(); // повторная попытка
    }

    console.log('❌ Unexpected HTTP:', res.status);
    return null;
  } catch (e) {
    console.log('🔥 ERROR:', e.message);
    return null;
  }
}

(async () => {
  const data = await requestLot();
  console.log(JSON.stringify(data, null, 2));
})();
