<?php
/**
 * Database Configuration & Handler
 * Support untuk JSON (legacy) dan MySQL (new)
 * 
 * Configuration dimuat dari .env file (production-safe)
 */

// ============================================================
// .ENV FILE LOADER
// ============================================================

function loadEnvFile($filePath = null) {
    if ($filePath === null) {
        $filePath = dirname(__DIR__) . '/.env';
    }
    
    if (!file_exists($filePath)) {
        // .env tidak wajib ada, gunakan default
        return [];
    }
    
    $env = [];
    $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    
    foreach ($lines as $line) {
        // Skip comments
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        
        // Parse KEY=VALUE atau KEY="VALUE" atau KEY='VALUE'
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            
            // Remove quotes if present
            if (preg_match('/^["\'](.*)["\']$/', $value, $matches)) {
                $value = $matches[1];
            }
            
            $env[$key] = $value;
        }
    }
    
    return $env;
}

// Load .env file
$envVars = loadEnvFile();

// ============================================================
// MYSQL PDO CONNECTION
// ============================================================

$MySQL_Config = [
    'host' => $envVars['DB_HOST'] ?? getenv('DB_HOST') ?: 'localhost',
    'port' => $envVars['DB_PORT'] ?? getenv('DB_PORT') ?: 3306,
    'name' => $envVars['DB_NAME'] ?? getenv('DB_NAME') ?: 'parama_hpp',
    'user' => $envVars['DB_USER'] ?? getenv('DB_USER') ?: 'root',
    'pass' => $envVars['DB_PASS'] ?? getenv('DB_PASS') ?: '',
    'charset' => 'utf8mb4'
];

function getMySQLConnection() {
    global $MySQL_Config;
    
    try {
        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=%s',
            $MySQL_Config['host'],
            $MySQL_Config['port'],
            $MySQL_Config['name'],
            $MySQL_Config['charset']
        );
        
        $pdo = new PDO(
            $dsn,
            $MySQL_Config['user'],
            $MySQL_Config['pass'],
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
            ]
        );
        
        return $pdo;
    } catch (PDOException $e) {
        error_log("MySQL Connection Error: " . $e->getMessage());
        return null;
    }
}

// ============================================================
// JSON File Based Database Handler (Legacy)
// Storage: data/ folder with JSON files
// ============================================================

class JSONDb {
    private $dataPath;
    private $cache = [];
    
    public function __construct($dataDir = __DIR__ . '/../data') {
        $this->dataPath = $dataDir;
        if (!is_dir($this->dataPath)) {
            mkdir($this->dataPath, 0755, true);
        }
    }
    
    /**
     * Load JSON file with caching
     */
    private function loadFile($filename) {
        $filepath = $this->dataPath . '/' . $filename;
        
        if (!file_exists($filepath)) {
            throw new Exception("File not found: $filepath");
        }
        
        $content = file_get_contents($filepath);
        return json_decode($content, true);
    }
    
    /**
     * Save JSON file
     */
    public function saveFile($filename, $data) {
        $filepath = $this->dataPath . '/' . $filename;
        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        if (file_put_contents($filepath, $json) === false) {
            throw new Exception("Failed to save: $filepath");
        }
    }
    
    /**
     * Get user by username
     */
    public function getUserByUsername($username) {
        $data = $this->loadFile('users.json');
        foreach ($data['users'] as $user) {
            if ($user['username'] === $username) {
                // Attach role info
                $role = $this->getRoleById($user['role_id']);
                $user['role'] = $role['name'];
                $user['role_label'] = $role['label'];
                $user['permissions'] = $role['permissions'];
                return $user;
            }
        }
        return null;
    }
    
    /**
     * Get user by ID
     */
    public function getUserById($id) {
        $data = $this->loadFile('users.json');
        foreach ($data['users'] as $user) {
            if ($user['id'] === $id) {
                $role = $this->getRoleById($user['role_id']);
                $user['role'] = $role['name'];
                $user['role_label'] = $role['label'];
                $user['permissions'] = $role['permissions'];
                return $user;
            }
        }
        return null;
    }
    
    /**
     * Get all users
     */
    public function getAllUsers() {
        $data = $this->loadFile('users.json');
        $users = [];
        foreach ($data['users'] as $user) {
            $role = $this->getRoleById($user['role_id']);
            $user['role'] = $role['name'];
            $user['role_label'] = $role['label'];
            $users[] = $user;
        }
        return $users;
    }
    
    /**
     * Get role by ID
     */
    public function getRoleById($id) {
        $data = $this->loadFile('users.json');
        foreach ($data['roles'] as $role) {
            if ($role['id'] === $id) {
                return $role;
            }
        }
        return null;
    }
    
    /**
     * Get role by name
     */
    public function getRoleByName($name) {
        $data = $this->loadFile('users.json');
        foreach ($data['roles'] as $role) {
            if ($role['name'] === $name) {
                return $role;
            }
        }
        return null;
    }
    
    /**
     * Add role
     */
    public function addRole($name, $label, $permissions = []) {
        $data = $this->loadFile('users.json');
        $nextId = count($data['roles']) > 0 ? max(array_column($data['roles'], 'id')) + 1 : 1;
        $data['roles'][] = [
            'id' => $nextId,
            'name' => $name,
            'label' => $label,
            'permissions' => $permissions
        ];
        $this->saveFile('users.json', $data);
        return $nextId;
    }
    
    /**
     * Add user
     */
    public function addUser($username, $password, $name, $email, $role_id) {
        $data = $this->loadFile('users.json');
        $nextId = count($data['users']) > 0 ? max(array_column($data['users'], 'id')) + 1 : 1;
        $now = date('c');
        
        $data['users'][] = [
            'id' => $nextId,
            'username' => $username,
            'password' => $password,
            'name' => $name,
            'email' => $email,
            'role_id' => $role_id,
            'is_active' => true,
            'last_login' => null,
            'created_at' => $now,
            'updated_at' => $now
        ];
        $this->saveFile('users.json', $data);
        return $nextId;
    }
    
    /**
     * Get settings
     */
    public function getSettings() {
        $data = $this->loadFile('settings.json');
        return $data;
    }
    
    /**
     * Get full service pricing data
     */
    public function getFullServicePricing() {
        $data = $this->loadFile('settings.json');
        return $data['fullservice_pricing'] ?? [];
    }
    
    /**
     * Get add-ons data
     */
    public function getAddons() {
        $data = $this->loadFile('addons.json');
        return $data;
    }
    
    /**
     * Get cetak base pricing data
     */
    public function getCetakBase() {
        $data = $this->loadFile('cetak_base.json');
        return $data;
    }
    
    /**
     * Get graduation pricing data
     */
    public function getGraduation() {
        $data = $this->loadFile('graduation.json');
        return $data;
    }
    
    /**
     * Get overhead data
     */
    public function getOverhead() {
        $data = $this->loadFile('settings.json');
        return $data['overhead'] ?? [];
    }
    
    /**
     * Get pricing factors
     */
    public function getPricingFactors() {
        $data = $this->loadFile('settings.json');
        return $data['pricing_factors'] ?? [];
    }
    
    /**
     * Get penawaran
     */
    public function getPenawaran() {
        $data = $this->loadFile('penawaran.json');
        return $data['penawaran'] ?? [];
    }
    
    /**
     * Add penawaran
     */
    public function addPenawaran($penawaran) {
        $data = $this->loadFile('penawaran.json');
        $nextId = count($data['penawaran']) > 0 ? max(array_column($data['penawaran'], 'id')) + 1 : 1;
        $penawaran['id'] = $nextId;
        $penawaran['created_at'] = date('c');
        $penawaran['updated_at'] = date('c');
        $data['penawaran'][] = $penawaran;
        $this->saveFile('penawaran.json', $data);
        return $nextId;
    }
    
    /**
     * Save penawaran (for updating or bulk operations)
     */
    public function savePenawaran($penawarans) {
        $data = $this->loadFile('penawaran.json');
        $data['penawaran'] = $penawarans;
        $this->saveFile('penawaran.json', $data);
    }
    
    /**
     * Get all users
     */
    public function getUsers() {
        $data = $this->loadFile('users.json');
        return $data['users'] ?? [];
    }
    
    /**
     * Save settings
     */
    public function saveSettings($settings) {
        $this->saveFile('settings.json', $settings);
    }
    
    /**
     * Get roles with permissions
     */
    public function getRoles() {
        $data = $this->loadFile('users.json');
        return $data['roles'] ?? [];
    }
}

// Global DB instance
$GLOBALS['jsonDb'] = new JSONDb();

/**
 * MySQL Master Data Handler
 * Untuk reads/writes master data dari MySQL tables
 */
class MySQLMasterData {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    // ========== OVERHEAD ==========
    public function getOverhead() {
        $stmt = $this->pdo->query("SELECT name, amount FROM overhead WHERE active = 1 ORDER BY id");
        $result = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $result[$row['name']] = (int)$row['amount'];
        }
        
        // Add total
        $totalStmt = $this->pdo->query("SELECT total_amount FROM overhead_total LIMIT 1");
        $totalRow = $totalStmt->fetch(PDO::FETCH_ASSOC);
        $result['total'] = $totalRow ? (int)$totalRow['total_amount'] : array_sum($result);
        
        return $result;
    }
    
    public function updateOverhead($data) {
        $this->pdo->beginTransaction();
        try {
            // Update individual items
            $stmt = $this->pdo->prepare("INSERT INTO overhead (name, amount) VALUES (?, ?) 
                                        ON DUPLICATE KEY UPDATE amount = VALUES(amount)");
            $total = 0;
            
            foreach ($data as $name => $amount) {
                if (strtolower($name) !== 'total') {
                    $val = (int)$amount;
                    $stmt->execute([$name, $val]);
                    $total += $val;
                }
            }
            
            // Update total
            $this->pdo->exec("DELETE FROM overhead_total");
            $this->pdo->prepare("INSERT INTO overhead_total (total_amount) VALUES (?)")->execute([$total]);
            
            $this->pdo->commit();
            return $this->getOverhead();
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }
    
    // ========== PRICING FACTORS ==========
    public function getPricingFactors() {
        $stmt = $this->pdo->query("SELECT category, factor_name, factor_value FROM pricing_factors WHERE 1=1 ORDER BY category, factor_name");
        $result = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if (!isset($result[$row['category']])) {
                $result[$row['category']] = [];
            }
            $result[$row['category']][$row['factor_name']] = (float)$row['factor_value'];
        }
        return $result;
    }
    
    public function updateCetakFactors($data) {
        return $this->updatePricingFactors('cetak', $data);
    }
    
    public function updateAlaCarteFactors($data) {
        return $this->updatePricingFactors('alacarte', $data);
    }
    
    private function updatePricingFactors($category, $data) {
        $stmt = $this->pdo->prepare("INSERT INTO pricing_factors (category, factor_name, factor_value) 
                                    VALUES (?, ?, ?) 
                                    ON DUPLICATE KEY UPDATE factor_value = VALUES(factor_value)");
        
        foreach ($data as $name => $value) {
            $val = (float)$value;
            // Convert percentage to decimal if needed (>1 means percentage)
            if ($val > 1) {
                $val = $val / 100;
            }
            $stmt->execute([$category, $name, $val]);
        }
        
        return $this->getPricingFactors();
    }
    
    // ========== FULL SERVICE ==========
    public function getFullService() {
        $stmt = $this->pdo->query("SELECT package_type, min_students, max_students, price_per_student, pages 
                                  FROM fullservice_pricing WHERE active = 1 
                                  ORDER BY package_type, min_students");
        $result = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $pkg = $row['package_type'];
            if (!isset($result[$pkg])) {
                $result[$pkg] = [];
            }
            $result[$pkg][] = [
                (int)$row['min_students'],
                (int)$row['max_students'],
                (int)$row['price_per_student'],
                (int)$row['pages']
            ];
        }
        return $result;
    }
    
    public function updateFullService($data) {
        $this->pdo->beginTransaction();
        try {
            // Delete existing and insert new
            $this->pdo->exec("DELETE FROM fullservice_pricing");
            
            $stmt = $this->pdo->prepare("INSERT INTO fullservice_pricing 
                                        (package_type, min_students, max_students, price_per_student, pages) 
                                        VALUES (?, ?, ?, ?, ?)");
            
            foreach ($data as $pkg => $tiers) {
                foreach ($tiers as $tier) {
                    list($lo, $hi, $price, $pages) = $tier;
                    $stmt->execute([$pkg, $lo, $hi, $price, $pages ?? 60]);
                }
            }
            
            $this->pdo->commit();
            return $this->getFullService();
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }
    
    // ========== CETAK BASE ==========
    public function getCetakBase() {
        $stmt = $this->pdo->query("SELECT min_students, max_students, price FROM cetak_base 
                                  WHERE active = 1 ORDER BY min_students");
        $result = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $result[] = [
                (int)$row['min_students'],
                (int)$row['max_students'],
                (int)$row['price']
            ];
        }
        return $result;
    }
    
    public function updateCetakBase($data) {
        $this->pdo->beginTransaction();
        try {
            $this->pdo->exec("DELETE FROM cetak_base");
            
            $stmt = $this->pdo->prepare("INSERT INTO cetak_base (min_students, max_students, price) 
                                        VALUES (?, ?, ?)");
            
            foreach ($data as $tier) {
                list($lo, $hi, $price) = $tier;
                $stmt->execute([(int)$lo, (int)$hi, (int)$price]);
            }
            
            $this->pdo->commit();
            return $this->getCetakBase();
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }
    
    // ========== ADD-ONS ==========
    public function getAddons() {
        $stmt = $this->pdo->query("SELECT name, price, unit, category FROM addons WHERE active = 1");
        $result = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $result[] = [
                'name' => $row['name'],
                'price' => (int)$row['price'],
                'unit' => $row['unit'],
                'category' => $row['category']
            ];
        }
        return $result;
    }
    
    public function updateAddons($data) {
        $this->pdo->beginTransaction();
        try {
            $this->pdo->exec("DELETE FROM addons");
            
            $stmt = $this->pdo->prepare("INSERT INTO addons (name, price, unit, category) 
                                        VALUES (?, ?, ?, ?)");
            
            foreach ($data as $addon) {
                $stmt->execute([
                    $addon['name'],
                    (int)$addon['price'],
                    $addon['unit'] ?? 'item',
                    $addon['category'] ?? 'misc'
                ]);
            }
            
            $this->pdo->commit();
            return $this->getAddons();
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }
    
    // ========== GRADUATION ==========
    public function getGraduation() {
        $packages = [];
        $stmt = $this->pdo->query("SELECT name, price, includes_book, includes_tshirt FROM graduation_packages WHERE active = 1");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $packages[] = [
                'name' => $row['name'],
                'price' => (int)$row['price'],
                'includes_book' => $row['includes_book'],
                'includes_tshirt' => $row['includes_tshirt']
            ];
        }
        
        $addons = [];
        $stmt = $this->pdo->query("SELECT name, price, item_type FROM graduation_addons WHERE active = 1");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $addons[] = [
                'name' => $row['name'],
                'price' => (int)$row['price'],
                'type' => $row['item_type']
            ];
        }
        
        $cetak = [];
        $stmt = $this->pdo->query("SELECT min_qty, max_qty, price_per_unit FROM graduation_cetak WHERE active = 1");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $cetak[] = [
                (int)$row['min_qty'],
                (int)$row['max_qty'],
                (int)$row['price_per_unit']
            ];
        }
        
        return [
            'packages' => $packages,
            'addons' => $addons,
            'cetak' => $cetak
        ];
    }
    
    // ========== PAYMENT TERMS ==========
    public function getPaymentTerms() {
        $stmt = $this->pdo->query("SELECT term_name FROM payment_terms WHERE active = 1 ORDER BY id");
        $result = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $result[] = $row['term_name'];
        }
        return ['terms' => $result];
    }
    
    // ========== UPDATE GRADUATION AND PAYMENT TERMS ==========
    
    public function updateGraduation($data) {
        $this->pdo->beginTransaction();
        try {
            // Update packages
            if (isset($data['packages'])) {
                $this->pdo->exec("DELETE FROM graduation_packages");
                $stmt = $this->pdo->prepare("INSERT INTO graduation_packages (name, price, includes_book, includes_tshirt) 
                                            VALUES (?, ?, ?, ?)");
                foreach ($data['packages'] as $pkg) {
                    $stmt->execute([
                        $pkg['name'],
                        (int)$pkg['price'],
                        $pkg['includes_book'] ?? 'No',
                        $pkg['includes_tshirt'] ?? 'No'
                    ]);
                }
            }
            
            // Update addons
            if (isset($data['addons'])) {
                $this->pdo->exec("DELETE FROM graduation_addons");
                $stmt = $this->pdo->prepare("INSERT INTO graduation_addons (name, price, item_type) 
                                            VALUES (?, ?, ?)");
                foreach ($data['addons'] as $addon) {
                    $stmt->execute([
                        $addon['name'],
                        (int)$addon['price'],
                        $addon['type'] ?? 'misc'
                    ]);
                }
            }
            
            // Update cetak
            if (isset($data['cetak'])) {
                $this->pdo->exec("DELETE FROM graduation_cetak");
                $stmt = $this->pdo->prepare("INSERT INTO graduation_cetak (min_qty, max_qty, price_per_unit) 
                                            VALUES (?, ?, ?)");
                foreach ($data['cetak'] as $tier) {
                    list($lo, $hi, $price) = $tier;
                    $stmt->execute([(int)$lo, (int)$hi, (int)$price]);
                }
            }
            
            $this->pdo->commit();
            return $this->getGraduation();
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }
    
    public function updatePaymentTerms($data) {
        // $data should be array of term names or wrapped in 'terms' key
        $terms = is_array($data) && isset($data['terms']) ? $data['terms'] : $data;
        
        $this->pdo->beginTransaction();
        try {
            $this->pdo->exec("DELETE FROM payment_terms");
            
            $stmt = $this->pdo->prepare("INSERT INTO payment_terms (term_name) VALUES (?)");
            foreach ($terms as $term) {
                if (is_array($term)) {
                    $term = $term['term_name'] ?? $term['name'] ?? '';
                }
                if (!empty($term)) {
                    $stmt->execute([(string)$term]);
                }
            }
            
            $this->pdo->commit();
            return $this->getPaymentTerms();
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }
}

/**
 * Get DB instance for compatibility with existing code
 */
function getDB() {
    return $GLOBALS['jsonDb'];
}

/**
 * Get MySQL Master Data instance
 */
function getMySQLMasterData($pdo) {
    return new MySQLMasterData($pdo);
}


