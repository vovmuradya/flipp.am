#!/bin/sh

# Check if we're running on Railway
if [ ! -z "$RAILWAY_ENVIRONMENT" ]; then
    echo "Running on Railway - performing setup..."

    # Generate app key if not set
    if [ -z "$APP_KEY" ] || [ "$APP_KEY" = "" ]; then
        echo "Generating APP_KEY..."
        php artisan key:generate --force
    fi

    # Wait for database to be ready
    echo "Waiting for database..."
    while ! nc -z $MYSQLHOST $MYSQLPORT; do
      sleep 1
    done
    echo "Database is ready!"

    # Run migrations
    echo "Running migrations..."
    php artisan migrate --force

    # Link storage
    php artisan storage:link
fi

# Start the application
exec "$@"