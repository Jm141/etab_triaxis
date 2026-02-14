# Tabulation System

> **📚 Documentation**: All documentation has been organized in the [`docs/`](./docs/) directory. See [Documentation Index](./docs/README.md) for a complete guide.

A comprehensive, competition-grade tabulation system built with PHP and MySQL. Perfect for pageants, hackathons, quiz bees, sports events, and other judged competitions.

## Features

### Core Modules
- **User & Role Management** - Super Admin, Event Admin, Tabulator, Judge, Auditor, Host roles
- **Event Management** - Create and manage multiple events
- **Competition Structure** - Multi-level competitions with rounds
- **Criteria & Scoring** - Flexible criteria with weighted scoring
- **Judge Management** - Assign judges to specific rounds
- **Contestant Management** - Individual or team support with CSV import
- **Scoring Engine** - Automatic weighted calculations and rankings
- **Result Management** - Calculate, preview, and release results
- **Real-Time Display** - Public scoreboard view
- **Audit Logging** - Complete action tracking

### Security Features
- Password hashing (bcrypt)
- CSRF protection
- SQL injection prevention (PDO prepared statements)
- Session management
- Role-based access control (RBAC)
- Input validation

## Installation

### Prerequisites
- PHP 7.4 or higher
- MySQL 5.7+ or MariaDB 10.2+
- Apache with mod_rewrite enabled
- XAMPP (recommended for Windows)

### Step 1: Database Setup

1. Create a new MySQL database:
```sql
CREATE DATABASE tabulation_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

2. Import the schema:
```bash
mysql -u root -p tabulation_system < database/schema.sql
```

Or using phpMyAdmin:
- Open phpMyAdmin
- Select the `tabulation_system` database
- Go to Import tab
- Choose `database/schema.sql` and click Go

### Step 2: Configuration

1. Update database credentials in `config/database.php`:
```php
'username' => 'root',
'password' => '1412',  // Your MySQL password
```

2. Update base URL in `config/app.php` if needed:
```php
'base_url' => '/tabulation',  // Adjust if your path is different
```

### Step 3: Web Server Setup

1. Place the project in your web root:
   - XAMPP: `C:\xampp\htdocs\tabulation`
   - WAMP: `C:\wamp\www\tabulation`
   - Linux: `/var/www/html/tabulation`

2. Ensure `.htaccess` is working (mod_rewrite enabled)

3. Set proper file permissions (Linux):
```bash
chmod -R 755 tabulation
chmod -R 777 tabulation/uploads  # If you add file uploads
```

### Step 4: Access the System

1. Open your browser and navigate to:
   ```
   http://localhost/tabulation
   ```

2. Default login credentials:
   - **Username:** `admin`
   - **Password:** `admin123`
   
   ⚠️ **IMPORTANT:** Change the default password immediately after first login!

## Usage Guide

### Creating an Event

1. Login as Admin
2. Go to **Events** → **Create Event**
3. Fill in event details (name, type, date, venue)
4. Set status to "Ongoing" when ready

### Setting Up Competition Structure

1. **Create Levels** (e.g., Preliminaries, Semi-finals, Finals)
   - Go to Event → Manage Levels
   - Add level name and description

2. **Create Rounds** (e.g., Talent, Q&A, Interview)
   - Go to Level → Manage Rounds
   - Add round name and description

3. **Define Criteria**
   - Go to Event → Manage Criteria
   - Add criteria with max scores (e.g., Creativity: 100, Presentation: 50)

4. **Assign Weights to Rounds**
   - Go to Round → Set Weights
   - Assign criteria to round with percentage weights
   - Total weight should equal 100%

### Managing Contestants

1. Go to Event → Manage Contestants
2. Add contestants manually or import via CSV
3. CSV format: `Number, Name, Team, Category`

### Assigning Judges

1. Go to Event → Manage Judges
2. Select a user with "Judge" role
3. Assign judge number and specialty (optional)
4. Judges will see assigned rounds in their dashboard

### Scoring Process

1. **Judge Login**
   - Judges see "My Rounds" in dashboard
   - Click on a round to see contestants

2. **Score Contestants**
   - Click "Score" for each contestant
   - Enter scores for all criteria
   - Submit scores (locked after submission)

3. **Calculate Rankings**
   - Admin goes to Results
   - Click "Calculate" for each round
   - System computes weighted averages and rankings

4. **Release Results**
   - Preview results
   - Click "Release" to make public
   - Public can view at `/tabulation/display/round/{id}`

## Database Schema

Key tables:
- `users` - User accounts
- `roles` - User roles and permissions
- `events` - Competition events
- `event_levels` - Competition levels
- `rounds` - Competition rounds
- `criteria` - Scoring criteria
- `criteria_weights` - Criteria weights per round
- `judges` - Judge assignments
- `contestants` - Contestant information
- `scores` - Judge scores
- `score_details` - Individual criterion scores
- `rankings` - Calculated rankings
- `audit_logs` - System audit trail

## API Endpoints

- `GET /api/rankings/round/{id}` - Get rankings for a round (JSON)

## Security Best Practices

1. **Change Default Password** - Immediately after installation
2. **Use HTTPS** - In production environments
3. **Regular Backups** - Backup database regularly
4. **Update PHP** - Keep PHP version updated
5. **File Permissions** - Restrict file access appropriately
6. **Session Security** - Sessions expire after 1 hour

## Troubleshooting

### Database Connection Error
- Check MySQL service is running
- Verify credentials in `config/database.php`
- Ensure database exists

### 404 Errors
- Check `.htaccess` is present
- Verify mod_rewrite is enabled
- Check base URL in `config/app.php`

### Session Issues
- Check PHP session directory is writable
- Verify session configuration in `config/app.php`

### Permission Denied
- Check file permissions
- Verify user role has required permissions

## Development

### Project Structure
```
tabulation/
├── config/          # Configuration files
├── controllers/     # MVC Controllers
├── core/           # Core classes (Database, Router, etc.)
├── database/       # SQL schema
├── routes/         # Route definitions
├── views/          # View templates
└── index.php       # Entry point
```

### Adding New Features

1. Create controller in `controllers/`
2. Add route in `routes/web.php`
3. Create view in `views/`
4. Update database schema if needed

## License

This project is open source and available for educational and commercial use.

## Support

For issues or questions, please check the code comments or create an issue in the repository.

---

**Built with ❤️ for fair and transparent competition judging**



