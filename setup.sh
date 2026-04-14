#!/bin/bash
# Setup script untuk Parama HPP Database Migration

set -e

echo "========================================="
echo "Parama HPP - Database Setup"
echo "========================================="
echo ""

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Step 1: Check PHP
echo "1. Checking PHP..."
if ! command -v php &> /dev/null; then
    echo -e "${RED}✗ PHP not found. Please install PHP first.${NC}"
    exit 1
fi
echo -e "${GREEN}✓ PHP found: $(php -v | head -n 1)${NC}"
echo ""

# Step 2: Create database
echo "2. Creating database..."
MYSQL_HOST=${MYSQL_HOST:-localhost}
MYSQL_USER=${MYSQL_USER:-root}
MYSQL_PASS=${MYSQL_PASS:-rahasia123}
MYSQL_DB="parama_hpp"

# Create database if not exists
mysql -h "$MYSQL_HOST" -u "$MYSQL_USER" -p"$MYSQL_PASS" <<EOF
CREATE DATABASE IF NOT EXISTS $MYSQL_DB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE $MYSQL_DB;
$(cat database/schema.sql)
EOF

echo -e "${GREEN}✓ Database schema created${NC}"
echo ""

# Step 3: Run migration
echo "3. Running data migration..."
php migrate_to_database.php

if [ $? -eq 0 ]; then
    echo -e "${GREEN}✓ Migration completed successfully${NC}"
else
    echo -e "${RED}✗ Migration failed${NC}"
    exit 1
fi
echo ""

# Step 4: Summary
echo "========================================="
echo -e "${GREEN}Setup Complete!${NC}"
echo "========================================="
echo ""
echo "Next steps:"
echo "1. Update your HTML to use the new API endpoints"
echo "2. API endpoint: api/pricing.php?action=get_all"
echo "3. Check api/pricing.php for available actions"
echo "4. Implement the JavaScript integration"
echo ""
echo "Database is ready to use!"
