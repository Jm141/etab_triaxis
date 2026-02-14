# CSV Import Guide for Judges

## Overview

The tabulation system allows you to import multiple judges at once using a CSV (Comma-Separated Values) file. This is much faster than creating judges one by one.

---

## 📥 Download Template

1. Go to **Judge Management** page
2. Click **"Download CSV Template"** button
3. The template file will download with sample data and proper formatting

---

## 📋 CSV Format

### Required Columns

The CSV file must have the following columns in this exact order:

1. **username** (Required) - Unique username for login (e.g., "judge1", "john_smith")
2. **email** (Required) - Valid email address
3. **full_name** (Required) - Full name of the judge
4. **password** (Required) - Password (minimum 6 characters)
5. **event_id** (Optional) - Event ID to assign judge to (leave empty if not assigning immediately)
6. **judge_number** (Optional) - Judge number/identifier (e.g., "J1", "Judge-1")
7. **specialty** (Optional) - Judge specialty or area of expertise

### Header Row

The first row must be the header row with column names:

```csv
username,email,full_name,password,event_id,judge_number,specialty
```

### Example CSV File

```csv
username,email,full_name,password,event_id,judge_number,specialty
judge1,judge1@example.com,John Smith,judge123,1,J1,Performance
judge2,judge2@example.com,Jane Doe,judge123,1,J2,Technical
judge3,judge3@example.com,Robert Johnson,judge123,1,J3,Creative
judge4,judge4@example.com,Mary Williams,judge123,,J4,Presentation
judge5,judge5@example.com,David Brown,judge123,,J5,Overall
```

---

## 📝 Step-by-Step Import Process

### Step 1: Get Event IDs

Before importing, you need to know the Event IDs if you want to assign judges to events:

1. Go to **Events** page
2. Note the Event ID from the URL when viewing an event: `/tabulation/events/{id}`
3. Or check the database directly

**Note:** You can leave `event_id` empty and assign judges to events later.

### Step 2: Prepare Your CSV File

1. **Download the template** (recommended) or create your own CSV file
2. **Open in Excel, Google Sheets, or any text editor**
3. **Fill in the data:**
   - Ensure `username`, `email`, `full_name`, and `password` are filled (required)
   - Fill `event_id` if you want to assign judge to an event immediately
   - Fill optional fields as needed
   - Remove sample data if using template

### Step 3: Save as CSV

1. **If using Excel:**
   - File → Save As
   - Choose "CSV (Comma delimited) (*.csv)"
   - Click Save

2. **If using Google Sheets:**
   - File → Download → Comma-separated values (.csv)

3. **If using a text editor:**
   - Save with `.csv` extension
   - Ensure commas separate values

### Step 4: Import the File

1. Go to **Judge Management** page
2. Scroll to **"Import Judges from CSV"** section
3. Click **"Choose File"** and select your CSV file
4. Click **"Import Judges"** button
5. Wait for the import to complete
6. You'll see a success message showing how many judges were imported

---

## ✅ Import Rules

### What Gets Imported

- ✅ New judge users with unique usernames/emails
- ✅ All required fields (username, email, full_name, password)
- ✅ Optional fields (event_id, judge_number, specialty)
- ✅ Automatic event assignment if event_id is provided
- ✅ Automatic user-event assignment for event filtering

### What Gets Skipped

- ❌ Rows with missing required fields
- ❌ Duplicate usernames or emails (already exist)
- ❌ Invalid email formats
- ❌ Passwords shorter than 6 characters
- ❌ Invalid event_id (event doesn't exist)
- ❌ Events you don't have access to
- ❌ Empty rows

### Import Results

After import, you'll see a message like:
- **"Imported 25 judges. 2 skipped."**

If there are errors, they will be listed (up to 10 errors shown).

---

## 💡 Tips and Best Practices

### 1. Use the Template

- Always download and use the provided template
- Ensures correct format and column order
- Includes sample data for reference

### 2. Check for Duplicates

- Before importing, check that usernames and emails are unique
- The system will skip duplicates, but it's better to avoid them

### 3. Required Fields

- **username**, **email**, **full_name**, and **password** are mandatory
- Empty values in these fields will cause the row to be skipped

### 4. Password Requirements

- Minimum 6 characters
- Can be any combination of letters, numbers, and symbols
- Consider using a default password that judges can change later

### 5. Event Assignment

- You can assign judges to events during import (provide event_id)
- Or leave event_id empty and assign later
- Judges can be assigned to multiple events (create separate rows)

### 6. Special Characters

- If your data contains commas, wrap the field in quotes:
  ```csv
  judge1,judge1@example.com,"Smith, John",judge123,1,J1,Performance
  ```
- If your data contains quotes, use double quotes:
  ```csv
  judge1,judge1@example.com,John Doe,judge123,1,J1,"He said ""Hello"""
  ```

### 7. Encoding

- Save your CSV file as **UTF-8** encoding
- This ensures special characters (accents, etc.) display correctly

### 8. Large Imports

- The system can handle large imports (50+ judges)
- If you have 100+ judges, consider splitting into multiple files

---

## 🔍 Finding Event IDs

### Method 1: From URL

1. Go to **Events** page
2. Click on an event
3. Look at the URL: `/tabulation/events/3`
4. The number `3` is the event_id

### Method 2: From Database

1. Open phpMyAdmin or MySQL client
2. Select your database
3. Run: `SELECT id, name FROM events`
4. Note the `id` column

### Method 3: Leave Empty

- You can leave `event_id` empty in the CSV
- Import judges without event assignment
- Assign them to events later using the interface

---

## 🔍 Troubleshooting

### Problem: "File upload failed"

**Solutions:**
- Check file size (should be under 10MB)
- Ensure file is saved as `.csv` format
- Try a different browser
- Check server upload limits

### Problem: "Could not open file"

**Solutions:**
- Ensure file is not open in another program
- Check file permissions
- Try saving the file again

### Problem: "0 judges imported"

**Solutions:**
- Check that your CSV has a header row
- Verify required fields (username, email, full_name, password) are filled
- Ensure file format is correct (comma-separated)
- Check for special characters or encoding issues
- Review error messages in the import result

### Problem: "Some judges skipped"

**Solutions:**
- Check for duplicate usernames/emails
- Verify all rows have required fields
- Check password length (minimum 6 characters)
- Verify email format is correct
- Check event_id is valid (if provided)
- Review error messages in the import result

### Problem: "Invalid event_id"

**Solutions:**
- Verify the event exists in the system
- Check that you have access to the event
- Use the correct event_id number
- Or leave event_id empty and assign later

### Problem: "Special characters not displaying correctly"

**Solutions:**
- Save CSV as UTF-8 encoding
- Use Excel's "Save As" → "CSV UTF-8" option
- Or use a text editor and save with UTF-8 encoding

---

## 📊 CSV File Examples

### Example 1: Simple Import (Required Fields Only)

```csv
username,email,full_name,password,event_id,judge_number,specialty
judge1,judge1@example.com,John Smith,judge123,,,
judge2,judge2@example.com,Jane Doe,judge123,,,
judge3,judge3@example.com,Robert Johnson,judge123,,,
```

### Example 2: Full Import (All Fields)

```csv
username,email,full_name,password,event_id,judge_number,specialty
judge1,judge1@example.com,John Smith,judge123,1,J1,Performance
judge2,judge2@example.com,Jane Doe,judge123,1,J2,Technical
judge3,judge3@example.com,Robert Johnson,judge123,1,J3,Creative
```

### Example 3: Mixed Data (Some with Events, Some Without)

```csv
username,email,full_name,password,event_id,judge_number,specialty
judge1,judge1@example.com,John Smith,judge123,1,J1,Performance
judge2,judge2@example.com,Jane Doe,judge123,,J2,Technical
judge3,judge3@example.com,Robert Johnson,judge123,1,J3,
```

---

## 🎯 Common Use Cases

### Use Case 1: Import Judges for One Event

```csv
username,email,full_name,password,event_id,judge_number,specialty
judge1,judge1@example.com,John Smith,judge123,1,J1,Performance
judge2,judge2@example.com,Jane Doe,judge123,1,J2,Technical
judge3,judge3@example.com,Robert Johnson,judge123,1,J3,Creative
```

### Use Case 2: Import Judges Without Event Assignment

```csv
username,email,full_name,password,event_id,judge_number,specialty
judge1,judge1@example.com,John Smith,judge123,,J1,Performance
judge2,judge2@example.com,Jane Doe,judge123,,J2,Technical
judge3,judge3@example.com,Robert Johnson,judge123,,J3,Creative
```

**Then assign to events later using the interface.**

### Use Case 3: Import Judges for Multiple Events

Create separate rows for each event assignment:

```csv
username,email,full_name,password,event_id,judge_number,specialty
judge1,judge1@example.com,John Smith,judge123,1,J1,Performance
judge1,judge1@example.com,John Smith,judge123,2,J1,Performance
judge2,judge2@example.com,Jane Doe,judge123,1,J2,Technical
judge2,judge2@example.com,Jane Doe,judge123,2,J2,Technical
```

**Note:** The same user can be assigned to multiple events.

---

## 📋 Quick Checklist

Before importing, make sure:

- [ ] CSV file has header row with column names
- [ ] Required fields (username, email, full_name, password) are filled
- [ ] Usernames and emails are unique
- [ ] Passwords are at least 6 characters
- [ ] Email addresses are valid
- [ ] Event IDs are correct (if provided)
- [ ] File is saved as `.csv` format
- [ ] File is saved with UTF-8 encoding (if using special characters)
- [ ] File is not open in another program

---

## 🔗 Related Documentation

- **CSV_IMPORT_GUIDE.md** - Guide for importing contestants
- **JUDGE_SETUP_GUIDE.md** - How to set up and assign judges
- **TEST_GUIDE.md** - Complete testing guide

---

## 💬 Need Help?

If you encounter issues:

1. **Check the troubleshooting section** above
2. **Verify your CSV format** matches the template
3. **Try importing a small test file** first (2-3 judges)
4. **Check the import results message** for details on what was skipped
5. **Review error messages** in the import result

---

## ⚠️ Important Notes

1. **Passwords are stored securely** using bcrypt hashing
2. **Duplicate usernames/emails are skipped** - each judge must have unique credentials
3. **Event assignment is optional** - you can assign judges to events later
4. **All imported judges get the "Judge" role** automatically
5. **Judges are automatically assigned to events** (for filtering) if event_id is provided

---

**Happy Importing!** 📥

