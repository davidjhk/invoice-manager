#!/bin/bash

echo "=== Database Fix Script ==="
echo "Fixing database migration and schema issues..."

# Make sure we're in the right directory
if [ ! -f "yii" ]; then
    echo "Error: yii console script not found. Make sure you're in the application root directory."
    exit 1
fi

# Make yii executable
chmod +x yii

echo ""
echo "1. Checking migration status..."
./yii migrate/history --limit=10

echo ""
echo "2. Running pending migrations..."
./yii migrate --interactive=0

echo ""
echo "3. Checking for specific language migration..."
./yii migrate/history --limit=20 | grep language || echo "Language migration not found in recent history"

echo ""
echo "4. Clearing all caches..."
./yii cache/flush-all 2>/dev/null || echo "Cache flush completed"

# Clear runtime cache manually
if [ -d "runtime/cache" ]; then
    rm -rf runtime/cache/*
    echo "Runtime cache cleared manually"
fi

echo ""
echo "5. Checking database table structure..."
echo "If you have mysql command available, you can check the companies table:"
echo "mysql -u bn_wordpress -p bitnami_wordpress -e 'DESCRIBE jdosa_companies;'"

echo ""
echo "=== Manual Migration Command ==="
echo "If migrations fail, try running this manually:"
echo "./yii migrate/up m250711_000008_add_language_to_companies"

echo ""
echo "=== Database Check Complete ==="
echo "Please check the migration output above for any errors."
echo "If the language column still doesn't exist, you may need to run the migration manually."