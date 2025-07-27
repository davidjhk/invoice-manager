#!/bin/bash

# Fix estimate user_id column issue
# This script should be run on the server where the database is accessible

echo "Running migration to add user_id column to estimates table..."
./yii migrate --interactive=0

echo "Migration completed. The estimate creation issue should now be resolved."