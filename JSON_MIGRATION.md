# ✨ Migration to JSON File Database

Aplikasi sudah berhasil di-migrate dari MySQL ke JSON File Based Database!

## 🎯 What Changed

| Aspek | Before | Now |
|-------|--------|-----|
| Database | MySQL | JSON Files |
| Config | DB_HOST, DB_USER, etc | Folder `/data` |
| Setup | Complex (create DB, tables) | Simple (just run) |
| Deployment | Memerlukan MySQL server | No dependencies |
| Performance | Optimized untuk large scale | Optimized untuk learning/demo |

## 📁 File Structure

```
parama_hpp/
├── config/
│   └── db.php              ← JSON database handler (bukan MySQL)
├── data/                   ← Data storage
│   ├── users.json          ← Users & roles
│   ├── settings.json       ← Application settings
│   ├── penawaran.json      ← Proposals/offers
│   └── .htaccess           ← Protect JSON files
├── auth/
│   └── login.php           ← Updated untuk JSON
├── api/
│   └── debug.php           ← Updated untuk JSON
└── init-auth.php           ← Initialize users (updated)
```

## 🚀 Getting Started

### Step 1: Ensure Permissions

Make sure `data/` folder is writable:

```bash
chmod 755 data/
chmod 644 data/*.json
```

### Step 2: Test Setup

```bash
php test-json-db.php
```

Expected output:
```
✓ Database loaded
✓ Found 3 users
✓ Login test passed
✓ All tests passed!
```

### Step 3: Access Application

1. Open: `http://localhost/` (or your domain)
2. Login with:
   - Username: `admin`
   - Password: `admin2026`

## 📊 Default Users

Pre-configured users in `data/users.json`:

```
Admin:     admin / admin2026
Manager:   manager / parama2026
Staff:     staff / staff123
```

## 🔍 Check Status

Access debug endpoint:
```
http://localhost/api/debug.php
```

Shows:
- Database connection status
- JSON files status
- All users
- Settings
- File permissions

## ✏️ Adding Users

### Via CLI

```bash
# Edit init-auth.php or create custom script
php init-auth.php
```

### Via Code

```php
require_once 'config/db.php';
$db = getDB();

$db->addUser(
    'newuser',
    password_hash('password123', PASSWORD_BCRYPT),
    'New User',
    'newuser@parama.studio',
    1  // role_id
);
```

## 🔐 Security

### Files are Protected

`data/.htaccess` prevents web access:
```apache
<FilesMatch "\.json$">
    Deny from all
</FilesMatch>
```

### Password Hashing

All passwords are bcrypt hashed:
```php
// Stored in data/users.json
"password": "$2y$10$8qXVI0lpQ4Q0EnvZLlRCyOJXPBXdRFKLZMHHJ6Fzp0XfSFKQQzaRm"
```

## 📝 Data Files

### users.json
- Roles (admin, manager, staff)
- Users with hashed passwords
- Permissions

### settings.json
- Overhead costs
- Pricing factors
- App configuration

### penawaran.json
- Proposal/offer history
- Client information
- Pricing details

## 🔧 API Usage

### JSONDb Class Methods

```php
$db = getDB();

// User operations
$user = $db->getUserByUsername('admin');
$user = $db->getUserById(1);
$users = $db->getAllUsers();
$db->addUser(...);

// Role operations
$role = $db->getRoleById(1);
$role = $db->getRoleByName('admin');
$db->addRole(...);

// Settings
$settings = $db->getSettings();
$penawaran = $db->getPenawaran();
$db->addPenawaran(...);
```

## 📚 Examples

### Login Check

```php
require_once 'config/db.php';

$db = getDB();
$user = $db->getUserByUsername('admin');

if ($user && password_verify('admin2026', $user['password'])) {
    echo "Valid!";
    echo "Role: " . $user['role'];
}
```

### List All Users

```php
$db = getDB();
$users = $db->getAllUsers();

foreach ($users as $user) {
    echo $user['username'] . " - " . $user['role'];
}
```

### Create New Proposal

```php
$db = getDB();
$id = $db->addPenawaran([
    'client_name' => 'PT Parama',
    'package' => 'Full Service',
    'student_count' => 100,
    'total_price' => 5000000,
    'status' => 'pending'
]);
```

## ⚠️ Limitations vs MySQL

| Feature | JSON | MySQL |
|---------|------|-------|
| Concurrent writes | Limited | ✓ |
| Query performance | Good (small data) | ✓ Great |
| Large datasets | OK | ✓ Better |
| Complex joins | Manual | ✓ Native |
| Scaling | Limited | ✓ Scalable |

**Recommendation**: Use JSON for development/demo, migrate to MySQL for production with large data.

## 🔄 Migrate to MySQL Later

When ready, you can:
1. Keep JSON files as backup
2. Export JSON to MySQL
3. Update `config/db.php` to use PDO
4. Update API endpoints

All business logic remains the same!

## 🐛 Troubleshooting

### Error: File not found

Make sure `data/` directory exists with JSON files

### Error: Permission denied

```bash
chmod 755 data/
chmod 666 data/*.json
```

### Error: Can't write to file

Check folder permissions are writable by web server

### Login fails

Check password hashes in `data/users.json` are valid bcrypt hashes

## 📞 Need Help?

1. Check `JSON_DB.md` for detailed documentation
2. Access `/api/debug.php` to see current status
3. Run `php test-json-db.php` for diagnostics
4. Check error_log for detailed errors

---

**Simple, fast, deployment-friendly database solution!** 🎉
