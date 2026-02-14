# How to Update Scores - Tech Admin Guide

## Overview
Technical Admins can update/edit scores submitted by judges. This guide explains the complete process from accessing the score management interface to updating scores.

## Requirements
1. **User Role:** Super Admin, Event Admin, Event Technical Admin, or Tabulator
2. **Access:** Must have access to the event
3. **Permission:** Judge must grant permission OR you need organizer key

## Step-by-Step Guide

### Step 1: Access Score Management

**Option A: Via Menu Navigation**
1. Log in as **Technical Admin** (or Super Admin/Tabulator)
2. Navigate to the main menu
3. Look for **"Score Management"** or **"Results"** section
4. Select the **Event** you want to manage
5. Select the **Round** you want to manage

**Option B: Direct URL**
- URL format: `/tabulation/score-management/round/{roundId}`
- Example: `/tabulation/score-management/round/14`
- To filter by judge: `/tabulation/score-management/round/14/{judgeId}`

### Step 2: View Score Management Page

The Score Management page shows:
- **Event and Round Information** at the top
- **Filter by Judge** dropdown (optional)
- **Print Scores** button
- **Scores grouped by Judge** in tables
- Each table shows:
  - Contestant number and name
  - Scores for each criteria (raw score and weighted score)
  - Total score
  - **Edit button** (if permission granted or organizer key available)

### Step 3: Find the Score to Edit

1. **Filter by Judge (Optional):**
   - Use the dropdown to filter scores by a specific judge
   - Or view all judges at once

2. **Locate the Contestant:**
   - Find the contestant whose score you want to edit
   - Look at the row showing their scores

3. **Check Edit Availability:**
   - If judge granted permission: Edit button will be visible
   - If judge hasn't granted permission: You'll need organizer key (see Step 4)

### Step 4: Access Edit Score Page

**Method 1: Click Edit Button**
- Click the **Edit** button (pencil icon) next to the score
- URL: `/tabulation/score-management/edit/{scoreId}`

**Method 2: Direct URL**
- If you know the score ID, go directly to:
- `/tabulation/score-management/edit/{scoreId}`

### Step 5: Edit Score Page Overview

The Edit Score page has two main sections:

**Left Side:**
- Contestant information (name, number)
- Score information (round, judge, current total)
- Warning about score changes

**Right Side:**
- Edit form with:
  - Score inputs for each criteria
  - Organizer key field (if needed)
  - Point deduction section
  - Update button

### Step 6: Update Scores

#### A. Edit Individual Criteria Scores

1. **Find the Criteria:**
   - Each criterion is shown in its own section
   - Shows: Name, description, weight, max score

2. **Enter New Score:**
   - Click in the score input field
   - Enter the new score (0 to max score)
   - Supports decimals (up to 3 decimal places)
   - Example: `8.500`, `9.250`, `10.000`

3. **Validation:**
   - Score must be between 0 and max score
   - Automatically formats to 3 decimal places
   - Shows warning if value exceeds max

#### B. Handle Permission Requirements

**If Judge Granted Permission:**
- You can edit scores directly
- No organizer key needed (unless applying deduction)
- Green alert shows: "Permission Granted"

**If Judge Hasn't Granted Permission:**
- You'll see: "Organizer Key Required"
- Enter the organizer key in the field
- Key must match the event's organizer key
- Contact Event Organizer to obtain the key

#### C. Apply Point Deduction (Optional)

1. **Scroll to Point Deduction Section:**
   - Located below the criteria scores

2. **Enter Deduction Amount:**
   - Enter the number of points to deduct
   - Example: `2.500`, `5.000`
   - Can be decimal (up to 3 decimal places)

3. **Enter Reason:**
   - Required when deduction > 0
   - Examples:
     - "Time penalty: 30 seconds over limit"
     - "Rule violation: Late arrival"
     - "Dress code violation"

4. **Important:** 
   - Deduction ALWAYS requires organizer key
   - Even if judge granted permission
   - Enter organizer key before applying deduction

### Step 7: Submit Changes

1. **Review All Changes:**
   - Check all score values
   - Verify deduction (if any)
   - Confirm organizer key is entered (if needed)

2. **Click "Update Score" Button:**
   - Button is at the bottom of the form
   - Shows confirmation dialog

3. **Confirm Update:**
   - Click "Yes, update" in the confirmation popup
   - Or "Cancel" to go back

4. **Wait for Processing:**
   - Button shows "Updating..." during processing
   - Don't refresh or close the page

### Step 8: Verify Update

After successful update:
- You'll be redirected back to Score Management page
- Updated scores will be visible
- Judge will receive a notification
- Changes are logged in audit trail

## Complete Workflow Example

```
1. Tech Admin logs in
   ↓
2. Navigates to: Score Management → Event → Round
   ↓
3. Views scores grouped by judge
   ↓
4. Finds Contestant #5's score from Judge #2
   ↓
5. Clicks "Edit" button
   ↓
6. Edit page opens
   ↓
7. Sees judge hasn't granted permission
   ↓
8. Enters organizer key: "EVENT2026KEY"
   ↓
9. Updates Performance Quality: 7.500 → 8.250
   ↓
10. Updates Stage Presence: 8.000 → 8.500
   ↓
11. (Optional) Applies deduction: 1.000 points
   Reason: "Time penalty"
   ↓
12. Clicks "Update Score"
   ↓
13. Confirms in popup
   ↓
14. Redirected to Score Management
   ↓
15. Updated scores visible
   ↓
16. Judge receives notification
```

## Permission Scenarios

### Scenario 1: Judge Granted Permission
- ✅ Can edit scores directly
- ✅ No organizer key needed (for regular edits)
- ⚠️ Still need organizer key for deductions

### Scenario 2: Judge Hasn't Granted Permission
- ⚠️ Need organizer key to edit
- ⚠️ Enter key before making changes
- ⚠️ Key must match event's organizer key

### Scenario 3: Applying Deduction
- ⚠️ ALWAYS requires organizer key
- ⚠️ Even if judge granted permission
- ⚠️ Must enter key before deduction

## Important Notes

### ⚠️ Permission Requirements
- **Regular Edit:** Judge permission OR organizer key
- **Point Deduction:** ALWAYS requires organizer key

### 📝 Score Validation
- Scores must be between 0 and max score
- Decimals supported (up to 3 places)
- Automatically formatted on blur

### 🔐 Security
- All changes are logged
- Judge receives notification
- Organizer key usage is audited

### 📊 Calculation
- Weighted scores recalculated automatically
- Total score updated
- Deduction applied to final score

## Troubleshooting

### "Edit Button Not Visible"
- **Problem:** Judge hasn't granted permission
- **Solution:** Use organizer key to edit

### "Organizer Key Required" Error
- **Problem:** Key not entered or invalid
- **Solution:** 
  - Enter organizer key in the field
  - Verify key with Event Organizer
  - Check if event has organizer key set

### "Score Out of Range" Error
- **Problem:** Score exceeds max score
- **Solution:** Enter score between 0 and max score

### "Invalid Organizer Key" Error
- **Problem:** Key doesn't match
- **Solution:** Contact Event Organizer for correct key

### Changes Not Saving
- **Problem:** Form validation failed
- **Solution:**
  - Check all required fields
  - Verify scores are within range
  - Ensure organizer key is correct (if needed)

## Access URLs Summary

- **Score Management Index:** `/tabulation/score-management/round/{roundId}`
- **Score Management (Filtered):** `/tabulation/score-management/round/{roundId}/{judgeId}`
- **Edit Score:** `/tabulation/score-management/edit/{scoreId}`
- **Update Score:** POST to `/tabulation/score-management/{scoreId}/update`

## Quick Reference

| Action | Judge Permission | Organizer Key |
|--------|------------------|---------------|
| View Scores | ✅ Always | ❌ No |
| Edit Scores | ✅ Yes | ✅ Yes (if no permission) |
| Edit Scores | ❌ No | ✅ Required |
| Apply Deduction | ✅ Yes | ✅ Required |
| Apply Deduction | ❌ No | ✅ Required |

---

**Need Help?** 
- Contact Event Organizer for organizer key
- Check event settings for organizer key configuration
- Review audit logs for change history
