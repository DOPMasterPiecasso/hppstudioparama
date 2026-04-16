#!/bin/bash
# Quick Migration Script for Graduation Data
# Jalankan script ini untuk migrasi graduation data dari JSON ke MySQL

echo "========================================"
echo "Parama HPP - Graduation Data Migration"
echo "========================================"
echo ""

cd "$(dirname "$0")"

# Check if init_database.php has been run
echo "1️⃣  Inisialisasi Database..."
php init_database.php

if [ $? -ne 0 ]; then
    echo "❌ Database initialization gagal!"
    exit 1
fi

echo ""
echo "2️⃣  Migrasi Graduation Data dari JSON..."
php migrate_graduation_data.php

if [ $? -ne 0 ]; then
    echo "❌ Migration gagal!"
    exit 1
fi

echo ""
echo "✅ Selesai! Database sudah siap dengan data graduation."
echo "Buka API: http://localhost:8000/api/master-data.php?action=get_graduation"
