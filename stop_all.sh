#!/bin/bash

echo "🛑 Stopping Flipp.am Development Environment..."
echo ""

GREEN='\033[0;32m'
NC='\033[0m'

# Stop Flutter
echo "1️⃣ Stopping Flutter app..."
FLUTTER_PID=$(ps aux | grep "flipp_am" | grep bundle | grep -v grep | awk '{print $2}')
if [ -n "$FLUTTER_PID" ]; then
    kill $FLUTTER_PID 2>/dev/null
    echo -e "${GREEN}✅ Flutter app stopped${NC}"
else
    echo "   Flutter app not running"
fi

# Stop Queue Worker
echo ""
echo "2️⃣ Stopping Queue Worker..."
QUEUE_PID=$(ps aux | grep "queue:work" | grep -v grep | awk '{print $2}')
if [ -n "$QUEUE_PID" ]; then
    kill $QUEUE_PID 2>/dev/null
    echo -e "${GREEN}✅ Queue worker stopped${NC}"
else
    echo "   Queue worker not running"
fi

# Stop Laravel Server
echo ""
echo "3️⃣ Stopping Laravel server..."
LARAVEL_PID=$(ps aux | grep "php artisan serve" | grep -v grep | awk '{print $2}')
if [ -n "$LARAVEL_PID" ]; then
    kill $LARAVEL_PID 2>/dev/null
    echo -e "${GREEN}✅ Laravel server stopped${NC}"
else
    echo "   Laravel server not running"
fi

echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo -e "${GREEN}✅ All services stopped!${NC}"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""
