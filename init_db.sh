#!/bin/bash
# Database Initialization Script
# Menjalankan PHP init script untuk setup database

echo "========================================="
echo "Parama HPP - Database Initialization"
echo "========================================="
echo ""

# Check if PHP is installed
if ! command -v php &> /dev/null; then
    echo "❌ Error: PHP tidak terinstall"
    exit 1
fi

echo "▶ Menjalankan database initialization..."
php init_database.php

echo ""
echo "========================================="
echo "✅ Selesai!"
echo "========================================="
