# 📄 JSON File Based Database

Aplikasi sekarang menggunakan JSON files sebagai data storage tanpa perlu MySQL database.

## 📁 Structure

```
data/
├── users.json        # Users, roles, dan permissions
├── settings.json     # Pengaturan aplikasi dan pricing factors
└── penawaran.json    # Data penawaran/proposals
```

## 🔑 Default Users

| Username | Password | Role |
|----------|----------|------|
| admin | admin2026 | Administrator |
| manager | parama2026 | Manager |
| staff | staff123 | Staff |

## 🚀 Setup

Tidak perlu database setup! Cukup:

1. Pastikan folder `data/` writable
```bash
chmod 755 data/
```

2. Akses aplikasi: `http://localhost/`

3. Login dengan credentials di atas

## 📝 Menambah User Baru

**Via CLI:**
```bash
php init-auth.php
```

**Via Code:**
```php
require_once 'config/db.php';
$db = getDB();

// Add role jika belum ada
$db->addRole('custom_role', 'Custom Role Label', ['permission1', 'permission2']);

// Add user
$db->addUser(
    'new_user',                              // username
    password_hash('password123', PASSWORD_BCRYPT), // password (hashed)
    'User Name',                             // nama
    'email@example.com',                     // email
    1                                        // role_id
);
```

## 🔧 Direct JSON Edit

Bisa juga edit files langsung di `data/users.json`:

```json
{
  "roles": [...],
  "users": [
    {
      "id": 1,
      "username": "admin",
      "password": "$2y$10$...",
      "name": "Administrator",
      "email": "admin@parama.studio",
      "role_id": 1,
      "is_active": true,
      "created_at": "2026-04-06T00:00:00+00:00",
      "updated_at": "2026-04-06T00:00:00+00:00"
    }
  ]
}
```

## ✅ Check Status

Akses: `http://localhost/api/debug.php`

```json
{
  "database_connection": "✓ Success",
  "json_files": {
    "users.json": "✓ Exists",
    "settings.json": "✓ Exists",
    "penawaran.json": "✓ Exists"
  },
  "users": {
    "count": 3,
    "list": [...]
  }
}
```

## 🔐 Security Notes

- Folder `data/` harus writable untuk save data baru
- Password disimpan ter-hash (bcrypt)
- Pastikan `data/` tidak accessible dari web browser
- Add file `.htaccess` untuk proteksi:

```apache
<FilesMatch "\.json$">
    Deny from all
</FilesMatch>
```

## ⚙️ API Classes

### JSONDb Class

Available methods:

```php
// User methods
$db->getUserByUsername($username)   // Get user by username
$db->getUserById($id)                // Get user by ID
$db->getAllUsers()                   // Get all users
$db->addUser(...)                    // Create new user

// Role methods
$db->getRoleById($id)                // Get role by ID
$db->getRoleByName($name)            // Get role by name
$db->addRole(...)                    // Create new role

// Settings methods
$db->getSettings()                   // Get all settings
$db->getPenawaran()                  // Get all penawaran
$db->addPenawaran(...)               // Add new penawaran
```

## 📚 Example Usage

```php
<?php
require_once 'config/db.php';

$db = getDB();

// Get user
$user = $db->getUserByUsername('admin');
echo "User: " . $user['name'];
echo "Role: " . $user['role'];

// Check password
if (password_verify('admin2026', $user['password'])) {
    echo "Password valid!";
}

// Get all users
$users = $db->getAllUsers();
foreach ($users as $u) {
    echo $u['username'] . " - " . $u['role'];
}

// Add penawaran
$penawaran_id = $db->addPenawaran([
    'client_name' => 'PT Parama',
    'student_count' => 100,
    'total_price' => 5000000,
    'status' => 'pending'
]);
echo "Penawaran ID: " . $penawaran_id;
?>
```

---

**No database required! Simple, fast, and easy to deploy.** ✨
