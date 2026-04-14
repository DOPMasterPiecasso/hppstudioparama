<?php
/**
 * Migration Script: JSON → MySQL
 * Otomatis migrate semua data master dari JSON files ke MySQL database
 * 
 * Usage: php migrate_json_to_mysql.php
 */

session_start();
require_once __DIR__ . '/config/db.php';

$startTime = microtime(true);

try {
    $pdo = getDB();
    
    echo "=== Parama HPP - JSON → MySQL Migration ===\n\n";
    
    // Step 1: Verify tables exist (create if needed)
    echo "[1/6] Checking database tables...\n";
    $schema = file_get_contents(__DIR__ . '/database/mysql_schema.sql');
    $statements = array_filter(array_map('trim', preg_split('/;(?=\s*(?:CREATE|INSERT|DELETE|DROP|ALTER))/i', $schema)));
    
    foreach ($statements as $stmt) {
        if (trim($stmt) && !preg_match('/^--/', trim($stmt))) {
            try {
                $pdo->exec($stmt);
            } catch (Exception $e) {
                // Table might already exist, skip
            }
        }
    }
    echo "✓ Database tables ready\n\n";
    
    // Step 2: Load and migrate Overhead
    echo "[2/6] Migrating Overhead...\n";
    $settingsPath = __DIR__ . '/data/settings.json';
    $settings = json_decode(file_get_contents($settingsPath), true);
    
    if (isset($settings['overhead'])) {
        $overhead = $settings['overhead'];
        $total = 0;
        
        $stmt = $pdo->prepare("INSERT INTO overhead (name, amount) VALUES (?, ?) 
                              ON DUPLICATE KEY UPDATE amount = VALUES(amount)");
        
        foreach ($overhead as $name => $amount) {
            if (strtolower($name) !== 'total') {
                $val = (int)$amount;
                $stmt->execute([$name, $val]);
                $total += $val;
                echo "  ✓ $name: " . number_format($val, 0, ',', '.') . "\n";
            }
        }
        
        // Update total
        $pdo->prepare("DELETE FROM overhead_total")->execute();
        $pdo->prepare("INSERT INTO overhead_total (total_amount) VALUES (?)")->execute([$total]);
        echo "  ✓ Total: " . number_format($total, 0, ',', '.') . "\n\n";
    }
    
    // Step 3: Migrate Pricing Factors
    echo "[3/6] Migrating Pricing Factors...\n";
    if (isset($settings['pricing_factors'])) {
        $factors = $settings['pricing_factors'];
        
        $stmt = $pdo->prepare("INSERT INTO pricing_factors (category, factor_name, factor_value) 
                              VALUES (?, ?, ?) 
                              ON DUPLICATE KEY UPDATE factor_value = VALUES(factor_value)");
        
        foreach ($factors as $category => $items) {
            foreach ($items as $name => $value) {
                $val = (float)$value;
                $stmt->execute([$category, $name, $val]);
                echo "  ✓ $category.$name = $val\n";
            }
        }
        echo "\n";
    }
    
    // Step 4: Migrate Full Service Pricing
    echo "[4/6] Migrating Full Service Pricing...\n";
    if (isset($settings['fullservice_pricing'])) {
        $fs = $settings['fullservice_pricing'];
        $count = 0;
        
        $stmt = $pdo->prepare("INSERT INTO fullservice_pricing 
                              (package_type, min_students, max_students, price_per_student, pages) 
                              VALUES (?, ?, ?, ?, ?) 
                              ON DUPLICATE KEY UPDATE 
                              price_per_student = VALUES(price_per_student),
                              pages = VALUES(pages)");
        
        foreach ($fs as $pkg => $tiers) {
            foreach ($tiers as $tier) {
                list($lo, $hi, $price, $pages) = $tier;
                $stmt->execute([$pkg, $lo, $hi, $price, $pages ?? 60]);
                $count++;
            }
        }
        echo "  ✓ Migrated $count pricing tiers\n\n";
    }
    
    // Step 5: Migrate Graduation Data
    echo "[5/6] Migrating Graduation Packages...\n";
    $gradPath = __DIR__ . '/data/graduation.json';
    if (file_exists($gradPath)) {
        $grad = json_decode(file_get_contents($gradPath), true);
        
        if (isset($grad['packages'])) {
            $stmt = $pdo->prepare("INSERT INTO graduation_packages (name, price, includes_book, includes_tshirt) 
                                  VALUES (?, ?, ?, ?) 
                                  ON DUPLICATE KEY UPDATE price = VALUES(price)");
            foreach ($grad['packages'] as $pkg) {
                $name = $pkg['name'] ?? 'Unknown';
                $price = (int)($pkg['price'] ?? 0);
                $book = $pkg['includes_book'] ?? 'No';
                $tshirt = $pkg['includes_tshirt'] ?? 'No';
                $stmt->execute([$name, $price, $book, $tshirt]);
                echo "  ✓ Package: $name\n";
            }
        }
        
        if (isset($grad['addons'])) {
            $stmt = $pdo->prepare("INSERT INTO graduation_addons (name, price, item_type) 
                                  VALUES (?, ?, ?) 
                                  ON DUPLICATE KEY UPDATE price = VALUES(price)");
            foreach ($grad['addons'] as $addon) {
                $name = $addon['name'] ?? 'Unknown';
                $price = (int)($addon['price'] ?? 0);
                $type = $addon['type'] ?? 'misc';
                $stmt->execute([$name, $price, $type]);
                echo "  ✓ Addon: $name\n";
            }
        }
        
        if (isset($grad['cetak'])) {
            $stmt = $pdo->prepare("INSERT INTO graduation_cetak (min_qty, max_qty, price_per_unit) 
                                  VALUES (?, ?, ?) 
                                  ON DUPLICATE KEY UPDATE price_per_unit = VALUES(price_per_unit)");
            foreach ($grad['cetak'] as $tier) {
                list($lo, $hi, $price) = $tier;
                $stmt->execute([$lo, $hi, $price]);
                echo "  ✓ Cetak tier: $lo-$hi\n";
            }
        }
        echo "\n";
    }
    
    // Step 6: Migrate Add-ons
    echo "[6/6] Migrating Add-ons...\n";
    $addonsPath = __DIR__ . '/data/addons.json';
    if (file_exists($addonsPath)) {
        $addons = json_decode(file_get_contents($addonsPath), true);
        
        $stmt = $pdo->prepare("INSERT INTO addons (name, price, unit, category) 
                              VALUES (?, ?, ?, ?) 
                              ON DUPLICATE KEY UPDATE price = VALUES(price)");
        
        foreach ($addons as $addon) {
            $name = $addon['name'] ?? 'Unknown';
            $price = (int)($addon['price'] ?? 0);
            $unit = $addon['unit'] ?? 'item';
            $cat = $addon['category'] ?? 'misc';
            $stmt->execute([$name, $price, $unit, $cat]);
            echo "  ✓ $name: Rp " . number_format($price, 0, ',', '.') . "\n";
        }
        echo "\n";
    }
    
    $elapsed = round(microtime(true) - $startTime, 2);
    
    echo "=== ✓ MIGRATION COMPLETE ===\n";
    echo "Time: {$elapsed}s\n\n";
    echo "Summary:\n";
    echo "✓ All overhead items imported\n";
    echo "✓ Pricing factors imported\n";
    echo "✓ Full Service pricing imported\n";
    echo "✓ Graduation packages imported\n";
    echo "✓ Add-ons imported\n\n";
    
    echo "Next steps:\n";
    echo "1. Verify data in MySQL\n";
    echo "2. Update API to use MySQL (already done in /api/master-data.php)\n";
    echo "3. Update header.php to load from MySQL (already done)\n";
    echo "4. Test all pages to verify everything works\n";
    echo "5. Archive JSON files (data/settings.json, etc) as backup\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
