#!/bin/bash

# Fix app/customer to customer translations
find views -name "*.php" -type f -exec sed -i '' 's/app\/customer/customer/g' {} \;
find models -name "*.php" -type f -exec sed -i '' 's/app\/customer/customer/g' {} \;
find controllers -name "*.php" -type f -exec sed -i '' 's/app\/customer/customer/g' {} \;

echo "Translation namespace fixed: app/customer -> customer"