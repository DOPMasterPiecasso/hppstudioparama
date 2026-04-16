#!/bin/bash
# Complete Setup & Migration Script
# 1. Initialize DB Schema
# 2. Migrate ALL JSON data to MySQL

echo ""
echo "╔══════════════════════════════════════════════════════════╗"
echo "║       Parama HPP - Complete Database Setup              ║"
echo "║       Schema Init + Complete Data Migration             ║"
echo "╚══════════════════════════════════════════════════════════╝"
echo ""

cd "$(dirname "$0")"

# Step 1: Initialize Database Schema
echo "📋 Step 1/2: Initializing database schema..."
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
php init_database.php

if [ $? -ne 0 ]; then
    echo ""
    echo "❌ Database initialization failed!"
    exit 1
fi

echo ""
echo ""

# Step 2: Migrate All Data
echo "📚 Step 2/2: Migrating all data from JSON files..."
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
php migrate_all_data.php

if [ $? -ne 0 ]; then
    echo ""
    echo "❌ Data migration failed!"
    exit 1
fi

echo ""
echo "╔══════════════════════════════════════════════════════════╗"
echo "║              ✅ SETUP COMPLETE!                          ║"
echo "╠══════════════════════════════════════════════════════════╣"
echo "║  Database is now ready with all data imported.           ║"
echo "║                                                          ║"
echo "║  Next steps:                                             ║"
echo "║  • Start server: php -S localhost:8000                  ║"
echo "║  • API endpoint: http://localhost:8000/api/...         ║"
echo "║  • Dashboard: http://localhost:8000/pages/dashboard.php║"
echo "║                                                          ║"
echo "╚══════════════════════════════════════════════════════════╝"
echo ""
