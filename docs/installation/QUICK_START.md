# Quick Start Guide

## 5-Minute Setup

### 1. Database Setup (2 minutes)

**Option A: Using install.php**
```bash
php install.php
```

**Option B: Manual (phpMyAdmin)**
1. Open phpMyAdmin: http://localhost/phpmyadmin
2. Create database: `tabulation_system`
3. Import: `database/schema.sql`

### 2. Configure (30 seconds)

Edit `config/database.php`:
```php
'password' => '1412',  // Your MySQL password
```

### 3. Access (30 seconds)

1. Start XAMPP (Apache + MySQL)
2. Open: http://localhost/tabulation
3. Login: `admin` / `admin123`

## Creating Your First Competition

### Step 1: Create Event (1 minute)
- Events → Create Event
- Name: "Beauty Pageant 2024"
- Type: Pageant
- Date: Today
- Status: Ongoing

### Step 2: Add Levels (1 minute)
- Click Event → Manage Levels
- Add: "Preliminaries"
- Add: "Finals"

### Step 3: Add Rounds (1 minute)
- Click Level → Manage Rounds
- For Preliminaries: Add "Talent", "Q&A"
- For Finals: Add "Final Q&A"

### Step 4: Define Criteria (2 minutes)
- Event → Manage Criteria
- Add: "Creativity" (Max: 100)
- Add: "Presentation" (Max: 100)
- Add: "Content" (Max: 50)

### Step 5: Set Weights (2 minutes)
- Round → Set Weights
- For "Talent" round:
  - Creativity: 40%
  - Presentation: 40%
  - Content: 20%
- Total must = 100%

### Step 6: Add Contestants (2 minutes)
- Event → Manage Contestants
- Add manually or import CSV
- CSV format: `1,John Doe,Team A,Category 1`

### Step 7: Assign Judges (2 minutes)
- Create judge user in database (see INSTALL.md)
- Event → Manage Judges
- Select judge user
- Assign to specific rounds

### Step 8: Score! (5 minutes)
- Judge logs in
- Sees "My Rounds"
- Clicks round → Scores each contestant
- Submits scores

### Step 9: Calculate Results (1 minute)
- Admin → Results
- Click "Calculate" for each round
- View rankings

### Step 10: Release (30 seconds)
- Click "Release" to make public
- Share public URL: `/tabulation/display/round/{id}`

## Common Tasks

### Create Judge User
```sql
INSERT INTO users (username, email, password_hash, full_name, role_id)
VALUES ('judge1', 'judge1@example.com', 
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 
        'Judge Name', 4);
```
Password: `admin123` (change after login)

### Change Admin Password
```sql
UPDATE users 
SET password_hash = '$2y$10$NEW_HASH_HERE' 
WHERE username = 'admin';
```
Generate hash: `php -r "echo password_hash('newpassword', PASSWORD_BCRYPT);"`

### Export Results to CSV
Use phpMyAdmin to export `rankings` table filtered by round_id.

## Tips

1. **Weight Validation**: System warns if weights don't total 100%
2. **Score Locking**: Judges can't modify submitted scores
3. **Real-time**: Rankings update when you click "Calculate"
4. **Audit Trail**: All actions logged in `audit_logs` table
5. **Mobile Friendly**: Judge interface works on tablets/phones

## Troubleshooting

**Judges can't see rounds?**
- Check judge is assigned to event
- Check judge is assigned to specific round
- Verify round status is "Active"

**Rankings not calculating?**
- Ensure all judges submitted scores
- Check criteria weights are set
- Verify contestants are "Active"

**CSV import fails?**
- Check format: Number, Name, Team, Category
- Ensure no duplicate numbers
- Verify file is UTF-8 encoded

## Support

- Check `README.md` for full documentation
- Check `INSTALL.md` for detailed installation
- Review code comments for technical details


