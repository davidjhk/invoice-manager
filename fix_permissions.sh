#!/bin/bash

# Fix permissions for Yii2 application
echo "Fixing permissions for Yii2 application..."

# Make sure the script is executable
chmod +x "$0"

# Set proper ownership (adjust if needed)
# chown -R www-data:www-data .

# Set directory permissions
echo "Setting directory permissions..."
find . -type d -exec chmod 755 {} \;

# Set file permissions
echo "Setting file permissions..."
find . -type f -exec chmod 644 {} \;

# Make console script executable
if [ -f "yii" ]; then
    chmod +x yii
    echo "Made yii console script executable"
fi

# Set writable permissions for runtime directories
echo "Setting writable permissions for runtime directories..."
if [ -d "runtime" ]; then
    chmod -R 777 runtime
    echo "Set runtime directory to 777"
fi

if [ -d "web/assets" ]; then
    chmod -R 777 web/assets
    echo "Set web/assets directory to 777"
fi

# Set writable permissions for specific cache directories
if [ -d "runtime/cache" ]; then
    chmod -R 777 runtime/cache
    echo "Set runtime/cache directory to 777"
fi

if [ -d "runtime/logs" ]; then
    chmod -R 777 runtime/logs
    echo "Set runtime/logs directory to 777"
fi

# Clear Yii2 cache
echo "Clearing Yii2 cache..."
if [ -f "yii" ]; then
    ./yii cache/flush-all 2>/dev/null || echo "Cache flush failed or not available"
fi

# Clear specific cache files
if [ -d "runtime/cache" ]; then
    rm -rf runtime/cache/*
    echo "Cleared runtime cache files"
fi

echo "Permission fix complete!"
echo ""
echo "If you're using Apache, make sure mod_rewrite is enabled."
echo "If you're using Nginx, make sure your configuration supports URL rewriting."
echo ""
echo "Test the language switcher in the browser console for debugging information."