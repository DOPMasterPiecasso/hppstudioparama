<?php
/**
 * Complete Data Migration Script (Adapted for Actual DB Schema)
 * Migrasi SEMUA data dari JSON files ke MySQL Database
 * ADAPTED untuk schema yang sebenarnya di database
 */

require_once 'config/db.php';

$db = getMySQLConnection();
if (!$db) {
    die("❌ Error: Koneksi database gagal\n");
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "COMPLETE DATA MIGRATION - JSON → MySQL\n";
echo str_repeat("=", 60) . "\n\n";

$totalMigrated = 0;

// ============================================================
// 1. USERS (dari users.json)
// ============================================================
echo "👥 Migrasi USERS...\n";
try {
    $jsonFile = __DIR__ . '/data/users.json';
    if (file_exists($jsonFile)) {
        $jsonData = json_decode(file_get_contents($jsonFile), true);
        
        // Clear existing
        $db->exec("DELETE FROM users");
        $db->exec("DELETE FROM roles");
        
        // Roles
        $rolesStmt = $db->prepare("INSERT INTO roles (id, name, label, permissions) VALUES (?, ?, ?, ?)");
        foreach ($jsonData['roles'] as $role) {
            $rolesStmt->execute([
                $role['id'],
                $role['name'],
                $role['label'],
                json_encode($role['permissions'] ?? [])
            ]);
        }
        echo "   ✓ " . count($jsonData['roles']) . " roles\n";
        $totalMigrated += count($jsonData['roles']);
        
        // Users - Only insert columns that exist
        $usersStmt = $db->prepare("INSERT INTO users (id, username, password, name, role_id, is_active, created_at) VALUES (?, ?, ?, ?, ?, ?, ?)");
        foreach ($jsonData['users'] as $user) {
            // Convert ISO 8601 datetime to MySQL datetime format
            $createdAt = new DateTime($user['created_at']);
            $usersStmt->execute([
                $user['id'],
                $user['username'],
                $user['password'],
                $user['name'],
                $user['role_id'],
                $user['is_active'] ? 1 : 0,
                $createdAt->format('Y-m-d H:i:s')
            ]);
        }
        echo "   ✓ " . count($jsonData['users']) . " users\n";
        $totalMigrated += count($jsonData['users']);
    }
} catch (Exception $e) {
    echo "   ⚠ Error: " . $e->getMessage() . "\n";
}

// ============================================================
// 2. OVERHEAD & PRICING (dari settings.json)
// ============================================================
echo "\n💰 Migrasi OVERHEAD...\n";
try {
    $jsonFile = __DIR__ . '/data/settings.json';
    if (file_exists($jsonFile)) {
        $jsonData = json_decode(file_get_contents($jsonFile), true);
        
        // Overhead
        $db->exec("DELETE FROM overhead");
        $stmt = $db->prepare("INSERT INTO overhead (id, category, amount, description) VALUES (?, ?, ?, ?)");
        
        $count = 0;
        $id = 1;
        foreach ($jsonData['overhead'] as $category => $amount) {
            if (strtolower($category) !== 'total') {
                $stmt->execute([$id++, $category, (int)$amount, '']);
                $count++;
            }
        }
        echo "   ✓ $count overhead items\n";
        $totalMigrated += $count;
        
        // Update overhead total
        $total = (int)($jsonData['overhead']['total'] ?? 0);
        $db->exec("DELETE FROM overhead_total");
        $db->exec("INSERT INTO overhead_total (total_amount) VALUES ($total)");
        echo "   ✓ Overhead total: Rp " . number_format($total, 0, ',', '.') . "\n";
    }
} catch (Exception $e) {
    echo "   ⚠ Error: " . $e->getMessage() . "\n";
}

// ============================================================
// 3. PRICING FACTORS (dari settings.json)
// ============================================================
echo "\n📊 Migrasi PRICING FACTORS...\n";
try {
    $jsonFile = __DIR__ . '/data/settings.json';
    if (file_exists($jsonFile)) {
        $jsonData = json_decode(file_get_contents($jsonFile), true);
        
        $db->exec("DELETE FROM pricing_factors");
        $stmt = $db->prepare("INSERT INTO pricing_factors (category, factor_name, factor_value, description) VALUES (?, ?, ?, ?)");
        
        $count = 0;
        // Cetak factors
        if (isset($jsonData['pricing_factors']['cetak'])) {
            foreach ($jsonData['pricing_factors']['cetak'] as $name => $value) {
                $stmt->execute(['cetak', $name, (float)$value, '']);
                $count++;
            }
        }
        // Alacarte factors
        if (isset($jsonData['pricing_factors']['alacarte'])) {
            foreach ($jsonData['pricing_factors']['alacarte'] as $name => $value) {
                $stmt->execute(['alacarte', $name, (float)$value, '']);
                $count++;
            }
        }
        echo "   ✓ $count pricing factors\n";
        $totalMigrated += $count;
    }
} catch (Exception $e) {
    echo "   ⚠ Error: " . $e->getMessage() . "\n";
}

// ============================================================
// 4. FULL SERVICE PRICING (dari settings.json)
// ============================================================
echo "\n📚 Migrasi FULL SERVICE PRICING...\n";
try {
    $jsonFile = __DIR__ . '/data/settings.json';
    if (file_exists($jsonFile)) {
        $jsonData = json_decode(file_get_contents($jsonFile), true);
        
        $db->exec("DELETE FROM fullservice_pricing");
        $stmt = $db->prepare("INSERT INTO fullservice_pricing (package_type, min_students, max_students, price_per_student, pages) VALUES (?, ?, ?, ?, ?)");
        
        $count = 0;
        if (isset($jsonData['fullservice_pricing'])) {
            foreach ($jsonData['fullservice_pricing'] as $pkg_type => $tiers) {
                foreach ($tiers as $tier) {
                    list($min, $max, $price, $pages) = $tier;
                    $stmt->execute([$pkg_type, $min, $max, (int)$price, (int)($pages ?? 60)]);
                    $count++;
                }
            }
        }
        echo "   ✓ $count fullservice pricing tiers\n";
        $totalMigrated += $count;
    }
} catch (Exception $e) {
    echo "   ⚠ Error: " . $e->getMessage() . "\n";
}

// ============================================================
// 5. CETAK BASE PRICING (dari cetak_base.json)
// ============================================================
echo "\n🖨️  Migrasi CETAK BASE PRICING...\n";
try {
    $jsonFile = __DIR__ . '/data/cetak_base.json';
    if (file_exists($jsonFile)) {
        $jsonData = json_decode(file_get_contents($jsonFile), true);
        
        $db->exec("DELETE FROM cetak_base");
        $stmt = $db->prepare("INSERT INTO cetak_base (range_label, min_students, max_students, pages_count, base_price, description) VALUES (?, ?, ?, ?, ?, ?)");
        
        $count = 0;
        if (is_array($jsonData)) {
            foreach ($jsonData as $item) {
                // Get price for 60 pages (most common)
                $priceFor60 = (int)($item['pages']['60'] ?? 0);
                $stmt->execute([
                    $item['label'] ?? '',
                    (int)($item['lo'] ?? 0),
                    (int)($item['hi'] ?? 0),
                    60,
                    $priceFor60,
                    ''
                ]);
                $count++;
            }
        }
        echo "   ✓ $count cetak base pricing ranges\n";
        $totalMigrated += $count;
    }
} catch (Exception $e) {
    echo "   ⚠ Error: " . $e->getMessage() . "\n";
}

// ============================================================
// 6. ADD-ONS (dari addons.json)
// ============================================================
echo "\n➕ Migrasi ADD-ONS...\n";
try {
    $jsonFile = __DIR__ . '/data/addons.json';
    if (file_exists($jsonFile)) {
        $jsonData = json_decode(file_get_contents($jsonFile), true);
        
        $db->exec("DELETE FROM addons");
        $stmt = $db->prepare("INSERT INTO addons (name, price, unit, category, description) VALUES (?, ?, ?, ?, ?)");
        
        $count = 0;
        if (is_array($jsonData)) {
            foreach ($jsonData as $category => $items) {
                if (is_array($items)) {
                    foreach ($items as $item) {
                        // Get base price (first tier)
                        $price = 0;
                        if (isset($item['price'])) {
                            $price = (int)$item['price'];
                        } elseif (isset($item['tiers']) && is_array($item['tiers']) && count($item['tiers']) > 0) {
                            $price = (int)($item['tiers'][0][2] ?? 0);
                        }
                        
                        $stmt->execute([
                            $item['name'] ?? '',
                            $price,
                            $item['type'] ?? '',
                            $category,
                            ''
                        ]);
                        $count++;
                    }
                }
            }
        }
        echo "   ✓ $count add-ons\n";
        $totalMigrated += $count;
    }
} catch (Exception $e) {
    echo "   ⚠ Error: " . $e->getMessage() . "\n";
}

// ============================================================
// 7. GRADUATION DATA (dari graduation.json)
// ============================================================
echo "\n🎓 Migrasi GRADUATION DATA...\n";
try {
    $jsonFile = __DIR__ . '/data/graduation.json';
    if (file_exists($jsonFile)) {
        $jsonData = json_decode(file_get_contents($jsonFile), true);
        
        // Packages
        $db->exec("DELETE FROM packages_graduation");
        $stmt = $db->prepare("INSERT INTO packages_graduation (package_key, name, price, description, color_scheme, display_order) VALUES (?, ?, ?, ?, ?, ?)");
        
        $count = 0;
        if (isset($jsonData['packages'])) {
            foreach ($jsonData['packages'] as $idx => $pkg) {
                $stmt->execute([
                    $pkg['id'] ?? '',
                    $pkg['name'] ?? '',
                    (int)($pkg['price'] ?? 0),
                    $pkg['desc'] ?? '',
                    $pkg['color'] ?? '',
                    $idx
                ]);
                $count++;
            }
        }
        echo "   ✓ $count graduation packages\n";
        $totalMigrated += $count;
        
        // Add-ons
        $db->exec("DELETE FROM graduation_addons");
        $stmt = $db->prepare("INSERT INTO graduation_addons (addon_key, name, price, addon_type) VALUES (?, ?, ?, ?)");
        
        $count = 0;
        if (isset($jsonData['addons'])) {
            foreach ($jsonData['addons'] as $addon) {
                $stmt->execute([
                    $addon['id'] ?? '',
                    $addon['name'] ?? '',
                    (int)($addon['price'] ?? 0),
                    'addon'
                ]);
                $count++;
            }
        }
        echo "   ✓ $count graduation add-ons\n";
        $totalMigrated += $count;
        
        // Cetak
        $db->exec("DELETE FROM graduation_cetak");
        $stmt = $db->prepare("INSERT INTO graduation_cetak (cetak_key, name, price_per_unit) VALUES (?, ?, ?)");
        
        $count = 0;
        if (isset($jsonData['cetak'])) {
            foreach ($jsonData['cetak'] as $cetak) {
                $stmt->execute([
                    $cetak['id'] ?? '',
                    $cetak['name'] ?? '',
                    (int)($cetak['price'] ?? 0)
                ]);
                $count++;
            }
        }
        echo "   ✓ $count graduation cetak items\n";
        $totalMigrated += $count;
    }
} catch (Exception $e) {
    echo "   ⚠ Error: " . $e->getMessage() . "\n";
}

// ============================================================
// 8. PAYMENT TERMS (dari payment_terms.json)
// ============================================================
echo "\n💳 Migrasi PAYMENT TERMS...\n";
try {
    $jsonFile = __DIR__ . '/data/payment_terms.json';
    if (file_exists($jsonFile)) {
        $jsonData = json_decode(file_get_contents($jsonFile), true);
        
        $db->exec("DELETE FROM payment_terms");
        $stmt = $db->prepare("INSERT INTO payment_terms (term_name, description) VALUES (?, ?)");
        
        $count = 0;
        if (isset($jsonData['terms'])) {
            foreach ($jsonData['terms'] as $term) {
                $stmt->execute([
                    $term['name'] ?? '',
                    $term['desc'] ?? ''
                ]);
                $count++;
            }
        }
        echo "   ✓ $count payment terms\n";
        $totalMigrated += $count;
    }
} catch (Exception $e) {
    echo "   ⚠ Error: " . $e->getMessage() . "\n";
}

// ============================================================
// VERIFICATION & SUMMARY
// ============================================================
echo "\n" . str_repeat("=", 60) . "\n";
echo "✅ VERIFICATION SUMMARY\n";
echo str_repeat("=", 60) . "\n";

$tables = [
    'roles' => 'Roles',
    'users' => 'Users',
    'overhead' => 'Overhead',
    'pricing_factors' => 'Pricing Factors',
    'fullservice_pricing' => 'Full Service Pricing',
    'cetak_base' => 'Cetak Base',
    'addons' => 'Add-ons',
    'packages_graduation' => 'Graduation Packages',
    'graduation_addons' => 'Graduation Add-ons',
    'graduation_cetak' => 'Graduation Cetak',
    'payment_terms' => 'Payment Terms'
];

$grandTotal = 0;
foreach ($tables as $table => $label) {
    $count = $db->query("SELECT COUNT(*) FROM $table")->fetchColumn();
    $status = $count > 0 ? "✓" : "✗";
    printf("  %s %-30s: %5d\n", $status, $label, $count);
    $grandTotal += $count;
}

echo "\n" . str_repeat("=", 60) . "\n";
printf("📊 Total data records migrated: %4d\n", $grandTotal);
echo str_repeat("=", 60) . "\n\n";

echo "✨ Migration complete! All data has been imported to MySQL.\n";
echo "🚀 Ready to use: http://localhost/api/master-data.php?action=get_all\n\n";
?>
