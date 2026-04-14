#!/bin/bash
# Quick verification script untuk Overhead CRUD Master Data System

echo "=== Overhead CRUD System Verification ==="
echo ""

# Check files exist
echo "1. Checking files..."
files=(
    "pages/pengaturan.php"
    "data/settings.json"
    "api/master-data.php"
    "assets/js/app.js"
    "includes/header.php"
)

for file in "${files[@]}"; do
    if [ -f "$file" ]; then
        echo "   ✓ $file exists"
    else
        echo "   ✗ $file MISSING"
    fi
done
echo ""

# Check key functions in pengaturan.php
echo "2. Checking functions in pengaturan.php..."
grep -q "function saveOH()" pages/pengaturan.php && echo "   ✓ saveOH() found" || echo "   ✗ saveOH() missing"
grep -q "function addOHItem()" pages/pengaturan.php && echo "   ✓ addOHItem() found" || echo "   ✗ addOHItem() missing"
grep -q "function deleteOHItem()" pages/pengaturan.php && echo "   ✓ deleteOHItem() found" || echo "   ✗ deleteOHItem() missing"
grep -q "function updateOHTotal()" pages/pengaturan.php && echo "   ✓ updateOHTotal() found" || echo "   ✗ updateOHTotal() missing"
echo ""

# Check API endpoint
echo "3. Checking API endpoint..."
grep -q "function getMasterOverhead" api/master-data.php && echo "   ✓ getMasterOverhead() found" || echo "   ✗ getMasterOverhead() missing"
grep -q "function updateOverhead" api/master-data.php && echo "   ✓ updateOverhead() found" || echo "   ✗ updateOverhead() missing"
echo ""

# Check settings.json structure
echo "4. Checking settings.json structure..."
if grep -q '"overhead"' data/settings.json; then
    echo "   ✓ overhead key exists in settings.json"
    items=$(grep -o '"[^"]*": [0-9]*' data/settings.json | grep -v '"settings"' | grep -v '"app_' | grep -v '"last_' | wc -l)
    echo "   ✓ Found $items overhead items"
else
    echo "   ✗ overhead key missing in settings.json"
fi
echo ""

# Check header.php loads DB_SETTINGS
echo "5. Checking header.php initialization..."
grep -q "DB_SETTINGS" includes/header.php && echo "   ✓ DB_SETTINGS loaded in header.php" || echo "   ✗ DB_SETTINGS not in header.php"
grep -q "const DB_SETTINGS" includes/header.php && echo "   ✓ DB_SETTINGS as JavaScript constant" || echo "   ✗ DB_SETTINGS not in JS"
echo ""

# Check app.js has OH variable
echo "6. Checking app.js..."
grep -q "let OH" assets/js/app.js && echo "   ✓ OH global variable found" || echo "   ✗ OH variable missing"
grep -q "DB_SETTINGS\['oh'\]" assets/js/app.js && echo "   ✓ OH loads from DB_SETTINGS" || echo "   ✗ OH not loading from DB_SETTINGS"
echo ""

echo "=== Verification Complete ==="
echo ""
echo "To test the system:"
echo "1. Open http://localhost:8000/pages/pengaturan.php"
echo "2. Find 'Overhead & Gaji Tim' card"
echo "3. Test operations:"
echo "   - Edit values and check total updates"
echo "   - Click '+ Tambah' to add new item"
echo "   - Click '✕' to delete item"
echo "   - Click 'Simpan Overhead' to persist"
echo "   - Reload page to verify persistence"
echo ""
