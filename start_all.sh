#!/bin/bash

echo "🚀 Starting Flipp.am Development Environment..."
echo ""

# Colors for output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

cd /home/vov/flipp-am

# 1. Check and start Laravel server
echo "1️⃣ Checking Laravel server..."
if ps aux | grep -v grep | grep "php artisan serve" > /dev/null; then
    echo -e "${GREEN}✅ Laravel server already running${NC}"
else
    echo "   Starting Laravel server..."
    php artisan serve > /tmp/laravel_server.log 2>&1 &
    sleep 2
    echo -e "${GREEN}✅ Laravel server started on http://localhost:8000${NC}"
fi

# 2. Check and start Queue Worker
echo ""
echo "2️⃣ Checking Queue Worker..."
if ps aux | grep -v grep | grep "queue:work" > /dev/null; then
    echo -e "${GREEN}✅ Queue worker already running${NC}"
else
    echo "   Starting Queue worker..."
    nohup php artisan queue:work --tries=1 --timeout=120 > /tmp/queue_worker.log 2>&1 &
    echo -e "${GREEN}✅ Queue worker started${NC}"
fi

# 3. Check and start Flutter app
echo ""
echo "3️⃣ Checking Flutter app..."
if ps aux | grep -v grep | grep "flipp_am" | grep bundle > /dev/null; then
    echo -e "${GREEN}✅ Flutter app already running${NC}"
else
    echo "   Starting Flutter app (this may take 30-60 seconds)..."
    cd mobile-app
    flutter run -d linux > flutter_output.log 2>&1 &
    FLUTTER_PID=$!
    echo -e "${YELLOW}   Flutter is building... (PID: $FLUTTER_PID)${NC}"
    cd ..
fi

echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo -e "${GREEN}✅ All services started!${NC}"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""
echo "📋 Service Status:"
echo "   • Laravel API:    http://localhost:8000"
echo "   • Queue Worker:   Running (logs: tail -f /tmp/queue_worker.log)"
echo "   • Flutter App:    Building/Running (logs: tail -f mobile-app/flutter_output.log)"
echo ""
echo "💡 Useful commands:"
echo "   • Check status:   ps aux | grep -E 'artisan|queue|flipp'"
echo "   • View logs:      tail -f /tmp/queue_worker.log"
echo "   • Stop all:       ./stop_all.sh"
echo ""
