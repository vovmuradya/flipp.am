#!/bin/bash

export PUPPETEER_EXECUTABLE_PATH="/opt/google/chrome/chrome"
export PUPPETEER_ARGS="--no-sandbox --disable-setuid-sandbox --disable-dev-shm-usage --disable-gpu --single-process"

RAW=$(node scraper/fetch-copart-cookies-auto.cjs | jq -r '.cookies')
CLEAN=$(node -e "console.log(require('./scraper/filter-cookies.js')('$RAW'))")

if [ ! -z "$CLEAN" ]; then
  sed -i '/COPART_COOKIES/d' .env
  echo "COPART_COOKIES=\"$CLEAN\"" >> .env
  php artisan config:clear
  echo "✔ Cookies обновлены: $CLEAN"
else
  echo "❌ Cookies фильтрация не прошла!"
fi
