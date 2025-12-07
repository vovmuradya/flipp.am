#!/usr/bin/env node

const { execSync } = require('child_process');

console.log('🔄  Auto-refresh Copart cookies started...');

try {
    execSync(
        `
PLAYWRIGHT_BROWSERS_PATH="$HOME/.cache/ms-playwright" \
CHROME_PROFILE_DIR="$HOME/.cache/chrome-playwright" \
node scraper/fetch-copart-cookies-firefox.cjs --debug
`,
        { stdio: 'inherit' }
    );

    console.log('✔ Cookies refreshed');
} catch (e) {
    console.log('❌ Refresh failed', e.message);
    process.exitCode = 1;
}
