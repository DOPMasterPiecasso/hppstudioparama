<?php
/**
 * JSON File Based Database Handler
 * Storage: data/ folder with JSON files
 */

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
 * Get DB instance for compatibility with existing code
 */
function getDB() {
    return $GLOBALS['jsonDb'];
}

