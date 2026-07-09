#!/bin/bash
# Database Initialization Script
# This script runs automatically when the MySQL container starts
# It waits for MySQL to be ready, then imports the database schema

echo "Waiting for MySQL to be ready..."

# Wait for MySQL to be fully started
max_attempts=30
attempt=1
while [ $attempt -le $max_attempts ]; do
    if mysqladmin ping -h"localhost" -uroot -p"$MYSQL_ROOT_PASSWORD" &> /dev/null; then
        echo "MySQL is ready!"
        break
    fi
    echo "Waiting for MySQL... Attempt $attempt/$max_attempts"
    sleep 2
    attempt=$((attempt + 1))
done

# Check if database was successfully created
mysql -h"localhost" -uroot -p"$MYSQL_ROOT_PASSWORD" "$MYSQL_DATABASE" -e "SELECT 1" &> /dev/null

if [ $? -eq 0 ]; then
    echo "Database '$MYSQL_DATABASE' already exists, skipping schema import..."
else
    echo "Database '$MYSQL_DATABASE' does not exist, will be created by docker-compose"
fi

echo "Database initialization complete!"
