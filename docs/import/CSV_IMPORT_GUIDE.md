# CSV Import Guide for Contestants

## Overview

The tabulation system allows you to import multiple contestants at once using a CSV (Comma-Separated Values) file. This is much faster than adding contestants one by one.

---

## 📥 Download Template

1. Go to **Events** → Select your event → **Contestants**
2. Click **"Download CSV Template"** button
3. The template file will download with sample data and proper formatting

---

## 📋 CSV Format

### Required Columns

The CSV file must have the following columns in this exact order:

1. **contestant_number** (Required) - Unique number for the contestant (e.g., "1", "2", "A1")
2. **name** (Required) - Full name of the contestant
3. **team_name** (Optional) - Team or group name
4. **category** (Optional) - Category classification (e.g., "Senior", "Junior", "Open")
5. **bio** (Optional) - Biography or description of the contestant

### Header Row

The first row must be the header row with column names:

```csv
contestant_number,name,team_name,category,bio
```

### Example CSV File

```csv
contestant_number,name,team_name,category,bio
1,Emma Rodriguez,Team Alpha,Senior,Outstanding performer with 5 years of experience
2,Michael Chen,Team Beta,Senior,Creative and innovative talent
3,Sophia Martinez,Team Alpha,Junior,Young rising star
4,James Wilson,Team Gamma,Senior,Experienced competitor
5,Olivia Brown,Team Beta,Junior,Promising newcomer
```

---

## 📝 Step-by-Step Import Process

### Step 1: Prepare Your CSV File

1. **Download the template** (recommended) or create your own CSV file
2. **Open in Excel, Google Sheets, or any text editor**
3. **Fill in the data:**
   - Ensure `contestant_number` and `name` are filled (required)
   - Fill optional fields as needed
   - Remove sample data if using template

### Step 2: Save as CSV

1. **If using Excel:**
   - File → Save As
   - Choose "CSV (Comma delimited) (*.csv)"
   - Click Save

2. **If using Google Sheets:**
   - File → Download → Comma-separated values (.csv)

3. **If using a text editor:**
   - Save with `.csv` extension
   - Ensure commas separate values

### Step 3: Import the File

1. Go to **Events** → Select your event → **Contestants**
2. Scroll to **"Import from CSV"** section
3. Click **"Choose File"** and select your CSV file
4. Click **"Import Contestants"** button
5. Wait for the import to complete
6. You'll see a success message showing how many contestants were imported

---

## ✅ Import Rules

### What Gets Imported

- ✅ New contestants with unique numbers
- ✅ All required fields (number, name)
- ✅ Optional fields (team, category, bio)

### What Gets Skipped

- ❌ Rows with missing `contestant_number` or `name`
- ❌ Contestants with duplicate numbers (already exist)
- ❌ Empty rows
- ❌ Rows with less than 2 columns

### Import Results

After import, you'll see a message like:
- **"Imported 25 contestants. 2 skipped."**

This means:
- 25 new contestants were successfully added
- 2 rows were skipped (duplicates or invalid data)

---

## 💡 Tips and Best Practices

### 1. Use the Template

- Always download and use the provided template
- Ensures correct format and column order
- Includes sample data for reference

### 2. Check for Duplicates

- Before importing, check that contestant numbers are unique
- The system will skip duplicates, but it's better to avoid them

### 3. Required Fields

- **contestant_number** and **name** are mandatory
- Empty values in these fields will cause the row to be skipped

### 4. Optional Fields

- Leave empty or omit columns if not needed
- Empty values are handled gracefully

### 5. Special Characters

- If your data contains commas, wrap the field in quotes:
  ```csv
  1,"Smith, John",Team A,Senior,"Bio with, commas"
  ```
- If your data contains quotes, use double quotes:
  ```csv
  1,John Doe,Team A,Senior,"He said ""Hello"""
  ```

### 6. Encoding

- Save your CSV file as **UTF-8** encoding
- This ensures special characters (accents, etc.) display correctly

### 7. Large Imports

- The system can handle large imports (100+ contestants)
- If you have 500+ contestants, consider splitting into multiple files

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

### Problem: "0 contestants imported"

**Solutions:**
- Check that your CSV has a header row
- Verify required fields (number, name) are filled
- Ensure file format is correct (comma-separated)
- Check for special characters or encoding issues

### Problem: "Some contestants skipped"

**Solutions:**
- Check for duplicate contestant numbers
- Verify all rows have required fields
- Review the CSV file for empty rows

### Problem: "Special characters not displaying correctly"

**Solutions:**
- Save CSV as UTF-8 encoding
- Use Excel's "Save As" → "CSV UTF-8" option
- Or use a text editor and save with UTF-8 encoding

---

## 📊 CSV File Examples

### Example 1: Simple Import (Required Fields Only)

```csv
contestant_number,name,team_name,category,bio
1,Emma Rodriguez,,,
2,Michael Chen,,,
3,Sophia Martinez,,,
```

### Example 2: Full Import (All Fields)

```csv
contestant_number,name,team_name,category,bio
1,Emma Rodriguez,Team Alpha,Senior,Outstanding performer with 5 years of experience
2,Michael Chen,Team Beta,Senior,Creative and innovative talent
3,Sophia Martinez,Team Alpha,Junior,Young rising star
```

### Example 3: Mixed Data

```csv
contestant_number,name,team_name,category,bio
1,Emma Rodriguez,Team Alpha,Senior,Outstanding performer
2,Michael Chen,,Senior,
3,Sophia Martinez,Team Alpha,,
```

---

## 🎯 Common Use Cases

### Use Case 1: Pageant Contestants

```csv
contestant_number,name,team_name,category,bio
1,Emma Rodriguez,Miss Universe,Senior,Representing USA
2,Michael Chen,Miss Universe,Senior,Representing China
3,Sophia Martinez,Miss Universe,Senior,Representing Spain
```

### Use Case 2: Talent Competition

```csv
contestant_number,name,team_name,category,bio
1,Emma Rodriguez,Team Alpha,Senior,Singing performance
2,Michael Chen,Team Beta,Junior,Dance routine
3,Sophia Martinez,Team Alpha,Senior,Magic show
```

### Use Case 3: Academic Competition

```csv
contestant_number,name,team_name,category,bio
1,Emma Rodriguez,School A,Senior,Science project
2,Michael Chen,School B,Junior,Math competition
3,Sophia Martinez,School A,Senior,Essay writing
```

---

## 📋 Quick Checklist

Before importing, make sure:

- [ ] CSV file has header row with column names
- [ ] Required fields (contestant_number, name) are filled
- [ ] Contestant numbers are unique
- [ ] File is saved as `.csv` format
- [ ] File is saved with UTF-8 encoding (if using special characters)
- [ ] File is not open in another program

---

## 🔗 Related Documentation

- **TEST_GUIDE.md** - Complete testing guide
- **JUDGE_SETUP_GUIDE.md** - How to set up judges
- **SCORING_GUIDE.md** - How scoring works

---

## 💬 Need Help?

If you encounter issues:

1. **Check the troubleshooting section** above
2. **Verify your CSV format** matches the template
3. **Try importing a small test file** first (2-3 contestants)
4. **Check the import results message** for details on what was skipped

---

**Happy Importing!** 📥

