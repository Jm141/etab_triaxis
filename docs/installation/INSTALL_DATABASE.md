# Database Installation Guide

This guide shows you how to run the `schema.sql` file to create all database tables.

## Prerequisites

1. **XAMPP is running** (Apache and MySQL services)
2. **Database exists** (or create it first)
3. **Database credentials** match `config/database.php`

---

## Method 1: Using MySQL Command Line (Recommended)

### Step 1: Open Command Prompt/Terminal

Navigate to your project directory:
```bash
cd C:\xampp1\htdocs\tabulation
```

### Step 2: Run MySQL Command

**Option A: If MySQL is in your PATH:**
```bash
mysql -u root -p1412 tabulation_system < database/schema.sql
```

**Option B: Using XAMPP MySQL path:**
```bash
C:\xampp\mysql\bin\mysql.exe -u root -p1412 tabulation_system < database/schema.sql
```

**Option C: If you need to create the database first:**
```bash
# Create database
C:\xampp\mysql\bin\mysql.exe -u root -p1412 -e "CREATE DATABASE IF NOT EXISTS tabulation_system;"

# Import schema
C:\xampp\mysql\bin\mysql.exe -u root -p1412 tabulation_system < database/schema.sql
```

---

## Method 2: Using phpMyAdmin (Easiest for Beginners)

### Step 1: Open phpMyAdmin

1. Start XAMPP
2. Open browser: `http://localhost/phpmyadmin`
3. Login with:
   - Username: `root`
   - Password: `1412` (or your MySQL password)

### Step 2: Create Database (if not exists)

1. Click **"New"** in the left sidebar
2. Database name: `tabulation_system`
3. Collation: `utf8mb4_general_ci`
4. Click **"Create"**

### Step 3: Import Schema

1. Select the `tabulation_system` database from left sidebar
2. Click **"Import"** tab at the top
3. Click **"Choose File"** button
4. Navigate to: `C:\xampp1\htdocs\tabulation\database\schema.sql`
5. Click **"Go"** button at the bottom
6. Wait for success message

---

## Method 3: Using MySQL Workbench

### Step 1: Open MySQL Workbench

1. Connect to your MySQL server (localhost, root, password: 1412)

### Step 2: Create Database

```sql
CREATE DATABASE IF NOT EXISTS tabulation_system;
USE tabulation_system;
```

### Step 3: Import Schema

1. Go to **File → Open SQL Script**
2. Select `database/schema.sql`
3. Click **Execute** (lightning bolt icon) or press `Ctrl+Shift+Enter`

---

## Method 4: Using PHP Script (Quick Install)

Create a simple install script:

### Step 1: Create `install_database.php`

```php
<?php
/**
 * Quick Database Installer
 * Run this once to install the database schema
 */

require_once __DIR__ . '/config/database.php';

$config = require __DIR__ . '/config/database.php';

try {
    // Connect without database name first
    $pdo = new PDO(
        "mysql:host={$config['host']};charset={$config['charset']}",
        $config['username'],
        $config['password'],
        $config['options']
    );
    
    // Create database if not exists
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$config['dbname']}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "✓ Database created/exists\n";
    
    // Select database
    $pdo->exec("USE `{$config['dbname']}`");
    
    // Read and execute schema
    $schema = file_get_contents(__DIR__ . '/database/schema.sql');
    
    // Split by semicolon and execute each statement
    $statements = array_filter(
        array_map('trim', explode(';', $schema)),
        function($stmt) {
            return !empty($stmt) && 
                   !preg_match('/^--/', $stmt) && 
                   !preg_match('/^SET/', $stmt);
        }
    );
    
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, 0);
    
    foreach ($statements as $statement) {
        if (!empty(trim($statement))) {
            try {
                $pdo->exec($statement);
            } catch (PDOException $e) {
                // Ignore "table already exists" errors
                if (strpos($e->getMessage(), 'already exists') === false) {
                    echo "⚠ Warning: " . $e->getMessage() . "\n";
                }
            }
        }
    }
    
    echo "✓ Schema installed successfully!\n";
    echo "✓ Default admin user created:\n";
    echo "   Username: admin\n";
    echo "   Password: admin123\n";
    echo "\n";
    echo "⚠ IMPORTANT: Change the admin password after first login!\n";
    
} catch (PDOException $e) {
    die("❌ Error: " . $e->getMessage() . "\n");
}
```

### Step 2: Run the Installer

```bash
php install_database.php
```

---

## Verification

After running the schema, verify installation:

### Check Tables

```sql
USE tabulation_system;
SHOW TABLES;
```

You should see tables like:
- `roles`
- `users`
- `events`
- `user_event_assignments`
- `judges`
- `contestants`
- `scores`
- etc.

### Check Default Admin User

```sql
SELECT username, email, full_name, is_active 
FROM users 
WHERE username = 'admin';
```

Should return:
- username: `admin`
- email: `admin@tabulation.local`
- full_name: `System Administrator`
- is_active: `1`

### Test Login

1. Go to: `http://localhost/tabulation/login`
2. Login with:
   - Username: `admin`
   - Password: `admin123`

---

## Troubleshooting

### Error: "Access denied for user 'root'@'localhost'"

**Solution:** Check your MySQL password in `config/database.php`

### Error: "Unknown database 'tabulation_system'"

**Solution:** Create the database first:
```sql
CREATE DATABASE tabulation_system;
```

### Error: "Table already exists"

**Solution:** This is normal if tables already exist. The schema uses `CREATE TABLE IF NOT EXISTS`, so it's safe to run multiple times.

### Error: "Cannot connect to MySQL"

**Solution:** 
1. Make sure XAMPP MySQL is running
2. Check MySQL port (default: 3306)
3. Verify credentials in `config/database.php`

---

## Fresh Install (Drop All Tables)

If you want to start fresh and drop all existing tables:

### Using MySQL Command Line:

```bash
# Drop database and recreate
C:\xampp\mysql\bin\mysql.exe -u root -p1412 -e "DROP DATABASE IF EXISTS tabulation_system;"
C:\xampp\mysql\bin\mysql.exe -u root -p1412 -e "CREATE DATABASE tabulation_system;"
C:\xampp\mysql\bin\mysql.exe -u root -p1412 tabulation_system < database/schema.sql
```

### Using phpMyAdmin:

1. Select `tabulation_system` database
2. Click **"Operations"** tab
3. Scroll to **"Drop the database"**
4. Click **"Drop the database"**
5. Recreate and import schema (Method 2 above)

---

## Next Steps

After successful installation:

1. ✅ **Login** with admin credentials
2. ✅ **Change admin password** (Security → Change Password)
3. ✅ **Create your first event**
4. ✅ **Follow TEST_GUIDE.md** for testing

---

## Quick Reference

| Method | Difficulty | Speed | Best For |
|--------|-----------|-------|----------|
| phpMyAdmin | ⭐ Easy | Medium | Beginners |
| MySQL CLI | ⭐⭐ Medium | Fast | Developers |
| PHP Script | ⭐⭐ Medium | Fast | Automated |
| MySQL Workbench | ⭐⭐ Medium | Medium | GUI Users |

---

**Need Help?** Check the error message and verify:
- ✅ XAMPP MySQL is running
- ✅ Database credentials are correct
- ✅ Database exists
- ✅ File path to schema.sql is correct

