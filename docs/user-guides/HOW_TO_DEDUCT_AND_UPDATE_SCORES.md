# How to Deduct Scores and Update Scores as Technical Admin

## Overview
This guide explains how to:
1. **Update scores** - Change individual criteria scores
2. **Apply point deductions** - Deduct points from the total score (requires organizer key)

---

## Part 1: Updating Scores

### Step 1: Access Score Management
1. Log in as **Technical Admin** (or Super Admin/Tabulator)
2. Navigate to: **Results** → Select **Event** → Click **"Manage Scores"** button
3. Or direct URL: `/tabulation/score-management/round/{roundId}`

### Step 2: Find the Score to Edit
1. Scores are grouped by judge
2. Find the contestant whose score you want to edit
3. Click the **"Edit"** button in the **Actions** column (rightmost column)

### Step 3: Check Permission Status

**Option A: Judge Has Granted Permission**
- You'll see a green badge: **"Permission Granted: Judge has granted permission"**
- You can edit directly without organizer key
- Skip to Step 5

**Option B: Judge Hasn't Granted Permission**
- You'll see: **"Organizer Key Required"**
- You need to enter the organizer key to proceed
- Continue to Step 4

### Step 4: Enter Organizer Key (If Needed)
1. Scroll to the **"Organizer Key"** section
2. Enter the organizer key provided by the Event Organizer
3. Click **"Verify Key"** button
4. If correct, you'll see: **"Key verified successfully"**
5. You can now edit scores

### Step 5: Update Individual Criteria Scores
1. In the **"Edit Scores"** section, you'll see all criteria for this round
2. Each criterion shows:
   - **Name** and description
   - **Weight** percentage
   - **Max Score** allowed
   - **Current Score** input field

3. **To update a score:**
   - Click in the score input field for the criterion
   - Enter the new score (must be between 0 and max score)
   - Supports decimals (up to 3 decimal places)
   - Example: If max is 15, you can enter 14.5, 12.75, etc.

4. **Validation:**
   - Score must be ≥ 0
   - Score must be ≤ max score
   - Invalid values will show an error

### Step 6: Save Score Updates
1. After updating all desired scores, scroll down
2. Click **"Update Score"** button
3. Confirm in the popup dialog
4. Wait for success message
5. You'll be redirected back to the score management page

---

## Part 2: Applying Point Deductions

### Important: Deduction Rules
- **Point deductions ALWAYS require organizer key**, even if judge granted permission
- Deductions are applied to the **total score** (not individual criteria)
- Deductions are logged and visible to the judge

### Step 1: Access Edit Page
1. Follow Steps 1-2 from Part 1 to access the edit page
2. Make sure you're on the score edit page

### Step 2: Enter Organizer Key for Deduction
1. Scroll to the **"Point Deduction (Optional)"** section
2. Enter the **Organizer Key** in the "Deduction Key" field
   - This is different from the regular organizer key field
   - Deduction key is required even if judge granted permission
3. Click **"Verify Deduction Key"** button
4. If correct, the deduction fields will be enabled

### Step 3: Enter Deduction Details
1. **Point Deduction Amount:**
   - Enter the number of points to deduct
   - Example: If you want to deduct 2.5 points, enter `2.5`
   - This will be subtracted from the total score

2. **Deduction Reason:**
   - Enter a clear reason for the deduction
   - Example: "Late arrival", "Rule violation", "Time penalty"
   - This reason will be visible to the judge

### Step 4: Apply Deduction
1. Review the deduction amount and reason
2. Click **"Update Score"** button at the bottom
3. Confirm in the popup dialog
4. Wait for success message

### Step 5: Verify Deduction Applied
1. After saving, you'll see:
   - **Original Total Score** (struck through)
   - **Final Total Score** (after deduction, shown in red)
   - **Deduction Amount** and **Reason** displayed

2. The deduction is now part of the score record

---

## Complete Workflow Example

### Scenario: Update Score and Apply Deduction

```
1. Access Score Management
   ↓
2. Find Contestant #5, Judge #2
   ↓
3. Click "Edit" button
   ↓
4. Check Permission:
   - Judge granted permission? → Edit directly
   - No permission? → Enter organizer key
   ↓
5. Update Criteria Scores:
   - Beauty: 14.5 (was 15.0)
   - Talent: 12.75 (was 13.0)
   - Intelligence: 9.25 (was 10.0)
   ↓
6. Apply Deduction (Optional):
   - Enter deduction key
   - Deduction: 1.5 points
   - Reason: "Late arrival"
   ↓
7. Click "Update Score"
   ↓
8. Confirm and save
   ↓
9. Verify changes applied
```

---

## Permission Matrix

### Regular Score Editing

| Judge Permission | Organizer Key | Can Edit? |
|------------------|---------------|-----------|
| ✅ Granted | ❌ Not needed | ✅ Yes |
| ❌ Not granted | ✅ Provided | ✅ Yes |
| ❌ Not granted | ❌ Not provided | ❌ No |

### Point Deduction

| Judge Permission | Deduction Key | Can Deduct? |
|------------------|---------------|-------------|
| ✅ Granted | ✅ Required | ✅ Yes |
| ❌ Not granted | ✅ Required | ✅ Yes |
| ✅/❌ Any | ❌ Not provided | ❌ No |

**Key Point:** Deductions ALWAYS require organizer key, regardless of judge permission.

---

## Visual Guide

### Edit Page Layout

```
┌─────────────────────────────────────────┐
│  Contestant Information                 │
│  - Name, Number, Round, Judge           │
│  - Current Total Score                 │
└─────────────────────────────────────────┘

┌─────────────────────────────────────────┐
│  Permission Status                      │
│  - "Permission Granted" or              │
│  - "Organizer Key Required"             │
└─────────────────────────────────────────┘

┌─────────────────────────────────────────┐
│  Organizer Key (if needed)              │
│  [Enter Key] [Verify Key]               │
└─────────────────────────────────────────┘

┌─────────────────────────────────────────┐
│  Edit Scores                            │
│  ┌─────────────────────────────────┐   │
│  │ Criterion 1: Beauty              │   │
│  │ Max: 15 | Weight: 30%           │   │
│  │ [Score Input: 14.5]             │   │
│  └─────────────────────────────────┘   │
│  ┌─────────────────────────────────┐   │
│  │ Criterion 2: Talent             │   │
│  │ Max: 10 | Weight: 25%           │   │
│  │ [Score Input: 9.25]             │   │
│  └─────────────────────────────────┘   │
│  ... (more criteria)                   │
└─────────────────────────────────────────┘

┌─────────────────────────────────────────┐
│  Point Deduction (Optional)            │
│  [Deduction Key Input]                  │
│  [Deduction Amount: 1.5]                │
│  [Reason: "Late arrival"]                │
└─────────────────────────────────────────┘

┌─────────────────────────────────────────┐
│  [Update Score] Button                  │
└─────────────────────────────────────────┘
```

---

## Important Notes

### Score Updates
- ✅ You can update multiple criteria at once
- ✅ Changes are saved immediately when you click "Update Score"
- ✅ Judge receives a notification when their score is edited
- ✅ All changes are logged in the audit trail

### Point Deductions
- ⚠️ Deductions are **subtracted from total score**
- ⚠️ Deductions are **permanent** (can be adjusted by editing again)
- ⚠️ Deduction reason is **visible to judge**
- ⚠️ Deduction key is **always required**, even if judge granted permission

### Validation Rules
- Score must be between 0 and max score (inclusive)
- Supports up to 3 decimal places
- Invalid values will prevent saving
- Empty scores are not allowed

### Access Requirements
- **Technical Admin** can edit scores
- **Super Admin** can edit scores
- **Tabulator** can edit scores
- **Event Organizer** CANNOT edit scores (restricted)
- **Judge** can only edit their own scores before submission

---

## Troubleshooting

### Problem: "Organizer Key Required" but I don't have it
**Solution:** Contact the Event Organizer to obtain the organizer key

### Problem: "Invalid organizer key"
**Solution:** 
- Double-check the key (case-sensitive)
- Contact Event Organizer to verify the correct key
- Make sure you're using the key for the correct event

### Problem: "Score must be between 0 and max"
**Solution:**
- Check the max score for that criterion
- Enter a value within the allowed range
- Remember: max score is inclusive (you can enter exactly the max)

### Problem: "Deduction key required" even after entering key
**Solution:**
- Make sure you entered the key in the "Deduction Key" field (not the regular organizer key field)
- Verify the key is correct
- Try refreshing the page and entering again

### Problem: Changes not saving
**Solution:**
- Check browser console for errors (F12)
- Make sure all required fields are filled
- Verify you have permission to edit
- Try refreshing and editing again

---

## Quick Reference URLs

- **Score Management:** `/tabulation/score-management/round/{roundId}`
- **Edit Score:** `/tabulation/score-management/edit/{scoreId}`
- **Update Score:** POST to `/tabulation/score-management/{scoreId}/update`

---

## Summary Checklist

### For Score Updates:
- [ ] Access score management page
- [ ] Find and click "Edit" button
- [ ] Check permission status
- [ ] Enter organizer key (if needed)
- [ ] Update criteria scores
- [ ] Click "Update Score"
- [ ] Confirm and verify changes

### For Point Deductions:
- [ ] Access edit page
- [ ] Enter deduction key
- [ ] Enter deduction amount
- [ ] Enter deduction reason
- [ ] Click "Update Score"
- [ ] Confirm and verify deduction applied

---

**Need Help?**
- Contact Event Organizer for organizer key
- Check permission status in score edit page
- Review audit logs for change history
- Contact system administrator for technical issues
