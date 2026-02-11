#!/usr/bin/env bash
set -e

echo "Running post-create setup..."

# Install PHP dependencies if composer.json exists
if [ -f "composer.json" ]; then
    echo "Installing Composer dependencies..."
    composer install --no-interaction --prefer-dist
fi

# Install Node dependencies if package.json exists
if [ -f "package.json" ]; then
    echo "Installing Node dependencies..."
    npm install
fi

# Laravel-specific setup
if [ -f "artisan" ]; then
    # Copy .env if it doesn't exist
    if [ ! -f ".env" ]; then
        echo "Creating .env file..."
        cp .env.example .env
        php artisan key:generate
    fi

    echo "Running migrations..."
    php artisan migrate --force || true

    echo "Clearing caches..."
    php artisan config:clear
    php artisan cache:clear
    php artisan route:clear
    php artisan view:clear
fi

echo "Post-create setup complete!"
