# Installation Guide

## Quick Start (XAMPP on Windows)

### Step 1: Database Setup

1. Open phpMyAdmin: `http://localhost/phpmyadmin`

2. Create a new database:
   - Click "New" in the left sidebar
   - Database name: `tabulation_system`
   - Collation: `utf8mb4_unicode_ci`
   - Click "Create"

3. Import the schema:
   - Select `tabulation_system` database
   - Click "Import" tab
   - Choose file: `database/schema.sql`
   - Click "Go"

### Step 2: Configure Database

Edit `config/database.php`:
```php
'username' => 'root',
'password' => '1412',  // Your MySQL password
```

### Step 3: Place Files

Copy the entire `tabulation` folder to:
```
C:\xampp\htdocs\tabulation
```

### Step 4: Access System

1. Start XAMPP (Apache + MySQL)

2. Open browser:
   ```
   http://localhost/tabulation
   ```

3. Login with:
   - Username: `admin`
   - Password: `admin123`

### Step 5: Create Judge Users

1. Go to phpMyAdmin
2. Select `tabulation_system` database
3. Go to `users` table
4. Insert new judge:
```sql
INSERT INTO users (username, email, password_hash, full_name, role_id)
VALUES ('judge1', 'judge1@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Judge One', 4);
```

Password for judge1: `admin123` (change after first login)

## Troubleshooting

### "Database connection failed"
- Check MySQL is running in XAMPP
- Verify password in `config/database.php`
- Ensure database `tabulation_system` exists

### "404 Not Found"
- Check `.htaccess` file exists
- Enable mod_rewrite in Apache
- Verify base URL in `config/app.php`

### "Access Denied"
- Check file permissions
- Ensure PHP can read all files

## Next Steps

1. **Change Admin Password**
   - Login as admin
   - Update password in database (use password_hash)

2. **Create Your First Event**
   - Go to Events → Create Event
   - Fill in details

3. **Set Up Competition Structure**
   - Add Levels (Preliminaries, Finals, etc.)
   - Add Rounds (Talent, Q&A, etc.)
   - Define Criteria
   - Assign Weights

4. **Add Contestants**
   - Manual entry or CSV import

5. **Assign Judges**
   - Create judge users
   - Assign to event
   - Assign to specific rounds

6. **Start Scoring!**
   - Judges login and score contestants
   - Calculate rankings
   - Release results

## Production Deployment

For production:

1. **Security**
   - Change all default passwords
   - Use HTTPS
   - Disable debug mode in `config/app.php`
   - Set proper file permissions

2. **Performance**
   - Enable PHP OPcache
   - Use MySQL query caching
   - Add database indexes if needed

3. **Backup**
   - Regular database backups
   - Backup uploaded files (if any)

