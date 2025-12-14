#!/bin/bash

# Start Laravel Queue Worker
cd /home/vov/flipp-am

echo "🚀 Starting Laravel Queue Worker..."

# Kill existing workers
pkill -f "queue:work" || true

# Start new worker in background
nohup php artisan queue:work --tries=1 --timeout=120 > /tmp/queue_worker.log 2>&1 &

WORKER_PID=$!
echo "✅ Queue worker started with PID: $WORKER_PID"
echo "📋 Logs: tail -f /tmp/queue_worker.log"
