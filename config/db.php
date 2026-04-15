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
        $stmt = $this->pdo->query("SELECT category, amount FROM overhead ORDER BY id");
        $result = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $result[$row['category']] = (int)$row['amount'];
        }
        $result['total'] = array_sum($result);
        return $result;
    }
    
    public function updateOverhead($data) {
        error_log("=== updateOverhead START ===");
        error_log("Data received: " . json_encode($data));
        
        $this->pdo->beginTransaction();
        try {
            // Delete all existing overhead items first
            error_log("Step 1: DELETE FROM overhead");
            $deleted = $this->pdo->exec("DELETE FROM overhead");
            error_log("Deleted rows: " . $deleted);
            
            // Insert only the items in the new data
            error_log("Step 2: INSERT new items");
            $stmt = $this->pdo->prepare("INSERT INTO overhead (category, amount) VALUES (?, ?)");
            
            $insertCount = 0;
            foreach ($data as $category => $amount) {
                if (strtolower($category) !== 'total') {
                    error_log("  Inserting: $category = " . (int)$amount);
                    $stmt->execute([$category, (int)$amount]);
                    $insertCount++;
                }
            }
            error_log("Total inserted: " . $insertCount);
            
            $this->pdo->commit();
            error_log("Transaction committed successfully");
            
            $result = $this->getOverhead();
            error_log("getOverhead() result: " . json_encode($result));
            error_log("=== updateOverhead END ===");
            
            return $result;
        } catch (Exception $e) {
            error_log("ERROR in updateOverhead: " . $e->getMessage());
            error_log("Backtrace: " . $e->getTraceAsString());
            $this->pdo->rollBack();
            throw $e;
        }
    }
    
    // ========== PRICING FACTORS ==========
    public function getPricingFactors() {
        // Get cetak factors
        $result = ['cetak' => [], 'alacarte' => []];
        
        $stmt = $this->pdo->query("SELECT package_type, factor FROM cetak_factors ORDER BY package_type");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $result['cetak'][$row['package_type']] = (float)$row['factor'];
        }
        
        // Get alacarte factors
        $stmt = $this->pdo->query("SELECT package_code, factor FROM alacarte_factors ORDER BY package_code");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $result['alacarte'][$row['package_code']] = (float)$row['factor'];
        }
        
        return $result;
    }
    
    public function updateCetakFactors($data) {
        $stmt = $this->pdo->prepare("UPDATE cetak_factors SET factor = ? WHERE package_type = ?");
        foreach ($data as $type => $factor) {
            $stmt->execute([(float)$factor, $type]);
        }
        return $this->getPricingFactors()['cetak'];
    }
    
    public function updateAlaCarteFactors($data) {
        $stmt = $this->pdo->prepare("UPDATE alacarte_factors SET factor = ? WHERE package_code = ?");
        foreach ($data as $code => $factor) {
            $stmt->execute([(float)$factor, $code]);
        }
        return $this->getPricingFactors()['alacarte'];
    }
    
    // ========== FULL SERVICE (packages_fullservice) ==========
    public function getFullService() {
        $stmt = $this->pdo->query("SELECT package_type, min_students, max_students, price_per_book, max_pages 
                                  FROM packages_fullservice ORDER BY package_type, min_students");
        $result = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $pkg = $row['package_type'];
            if (!isset($result[$pkg])) {
                $result[$pkg] = [];
            }
            $result[$pkg][] = [
                (int)$row['min_students'],
                (int)$row['max_students'],
                (int)$row['price_per_book'],
                (int)$row['max_pages']
            ];
        }
        return $result;
    }
    
    public function updateFullService($data) {
        $this->pdo->beginTransaction();
        try {
            // Delete existing and insert new
            $this->pdo->exec("DELETE FROM packages_fullservice");
            
            $stmt = $this->pdo->prepare("INSERT INTO packages_fullservice 
                                        (package_type, min_students, max_students, price_per_book, max_pages) 
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
        $stmt = $this->pdo->query("SELECT min_students, max_students, pages_count, base_price FROM cetak_base 
                                  ORDER BY min_students");
        $result = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $result[] = [
                'lo' => (int)$row['min_students'],
                'hi' => (int)$row['max_students'],
                'pages' => [(int)$row['pages_count']],
                'price' => (int)$row['base_price']
            ];
        }
        return $result;
    }
    
    public function updateCetakBase($data) {
        $this->pdo->beginTransaction();
        try {
            // Delete existing and insert new
            $this->pdo->exec("DELETE FROM cetak_base");
            
            $stmt = $this->pdo->prepare("INSERT INTO cetak_base (min_students, max_students, pages_count, base_price, range_label) 
                                        VALUES (?, ?, ?, ?, ?)");
            
            foreach ($data as $tier) {
                $lo = $tier['lo'] ?? 0;
                $hi = $tier['hi'] ?? 0;
                $price = $tier['price'] ?? 0;
                $pages = is_array($tier['pages']) ? $tier['pages'][0] : $tier['pages'] ?? 1;
                $label = "$lo–$hi siswa";
                
                $stmt->execute([$lo, $hi, $pages, $price, $label]);
            }
            
            $this->pdo->commit();
            return $this->getCetakBase();
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }
    
    // ========== ADD-ONS (tbl_addons) ==========
    public function getAddons() {
        $stmt = $this->pdo->query("SELECT category, sub_id, name, type, price, min_qty, max_qty FROM tbl_addons ORDER BY category, sub_id");
        $result = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $result[] = [
                'id' => $row['sub_id'],
                'name' => $row['name'],
                'price' => (int)$row['price'],
                'category' => $row['category'],
                'type' => $row['type'],
                'min_qty' => (int)$row['min_qty'],
                'max_qty' => (int)$row['max_qty']
            ];
        }
        return $result;
    }
    
    public function updateAddons($data) {
        $this->pdo->beginTransaction();
        try {
            // Delete existing and insert new
            $this->pdo->exec("DELETE FROM tbl_addons");
            
            $stmt = $this->pdo->prepare("INSERT INTO tbl_addons (category, sub_id, name, type, price, min_qty, max_qty) 
                                        VALUES (?, ?, ?, ?, ?, ?, ?)");
            
            foreach ($data as $addon) {
                $stmt->execute([
                    $addon['category'] ?? 'misc',
                    $addon['id'] ?? $addon['sub_id'] ?? '',
                    $addon['name'] ?? '',
                    $addon['type'] ?? 'addon',
                    (int)($addon['price'] ?? 0),
                    (int)($addon['min_qty'] ?? 1),
                    (int)($addon['max_qty'] ?? 999)
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
        $stmt = $this->pdo->query("SELECT package_key, name, price FROM packages_graduation ORDER BY display_order");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $packages[] = [
                'id' => $row['package_key'],
                'name' => $row['name'],
                'price' => (int)$row['price']
            ];
        }
        
        $addons = [];
        $stmt = $this->pdo->query("SELECT addon_key, name, price, addon_type FROM graduation_addons ORDER BY addon_key");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $addons[] = [
                'id' => $row['addon_key'],
                'name' => $row['name'],
                'price' => (int)$row['price'],
                'type' => $row['addon_type']
            ];
        }
        
        // Graduation cetak pricing
        $cetak = [];
        
        return [
            'packages' => $packages,
            'addons' => $addons,
            'cetak' => $cetak
        ];
    }
    
    // ========== PAYMENT TERMS ==========
    public function getPaymentTerms() {
        // No payment_terms table in current schema
        // Return empty terms for now
        return ['terms' => []];
    }
    
    public function updateGraduation($data) {
        $this->pdo->beginTransaction();
        try {
            if (isset($data['packages'])) {
                $this->pdo->exec("DELETE FROM packages_graduation");
                $stmt = $this->pdo->prepare("INSERT INTO packages_graduation (package_key, name, price) 
                                            VALUES (?, ?, ?)");
                foreach ($data['packages'] as $pkg) {
                    $stmt->execute([
                        $pkg['id'] ?? '',
                        $pkg['name'] ?? '',
                        (int)($pkg['price'] ?? 0)
                    ]);
                }
            }
            
            if (isset($data['addons'])) {
                $this->pdo->exec("DELETE FROM graduation_addons");
                $stmt = $this->pdo->prepare("INSERT INTO graduation_addons (addon_key, name, price, addon_type) 
                                            VALUES (?, ?, ?, ?)");
                foreach ($data['addons'] as $addon) {
                    $stmt->execute([
                        $addon['id'] ?? '',
                        $addon['name'] ?? '',
                        (int)($addon['price'] ?? 0),
                        $addon['type'] ?? 'addon'
                    ]);
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
        // No payment_terms table in current schema
        return $this->getPaymentTerms();
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


