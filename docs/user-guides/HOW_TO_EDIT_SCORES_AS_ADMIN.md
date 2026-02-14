# How to Edit Scores as Admin

## Overview
Technical Admin can edit scores submitted by judges. There are two ways to get permission:
1. **Judge grants permission** - Judge can toggle permission in their interface
2. **Use organizer key** - Tech admin can provide organizer key to edit without judge permission

## Step-by-Step Guide

### Step 1: Access Score Management
1. Log in as **Technical Admin** (or Super Admin/Tabulator)
2. Navigate to: **Results** → Select **Event** → Click **"Manage Scores"** button
3. Or direct URL: `/tabulation/score-management/round/{roundId}`

### Step 2: Find the Score to Edit
1. Scores are grouped by judge
2. Find the contestant whose score you want to edit
3. Look for an **Edit** button or link next to the score
4. If no Edit button is visible, you can access directly via URL (see below)

### Step 3: Request Permission (If Needed)

**Option A: Judge Grants Permission**
- Judge can grant permission in their scoring interface
- They toggle a setting to allow admin editing
- Once granted, you can edit directly without organizer key

**Option B: Use Organizer Key**
- If judge hasn't granted permission, you need organizer key
- Enter organizer key in the edit page
- Key must match the event's organizer key

### Step 4: Access Edit Page

**Method 1: Via Edit Button**
- Click the **Edit** button next to the score (if visible)

**Method 2: Direct URL**
- URL format: `/tabulation/score-management/edit/{scoreId}`
- To find score ID, check the database or inspect the page

### Step 5: Edit Scores

1. **If Judge Granted Permission:**
   - You'll see: "Permission Granted: Judge has granted permission"
   - You can edit scores directly
   - No organizer key needed (unless applying deduction)

2. **If Judge Hasn't Granted Permission:**
   - You'll see: "Organizer Key Required"
   - Enter the organizer key in the field
   - Then you can edit scores

3. **Update Scores:**
   - Change individual criteria scores
   - Scores must be between 0 and max score
   - Supports decimals (up to 3 decimal places)

4. **Apply Deduction (Optional):**
   - Scroll to "Point Deduction" section
   - Enter deduction amount
   - Enter reason
   - **Note:** Deduction ALWAYS requires organizer key (even if judge granted permission)

### Step 6: Submit Changes
1. Click **"Update Score"** button
2. Confirm in popup
3. Changes are saved and judge is notified

## Permission System

### Regular Score Editing

| Judge Permission | Organizer Key | Can Edit? |
|------------------|---------------|-----------|
| ✅ Granted | ❌ Not needed | ✅ Yes |
| ❌ Not granted | ✅ Provided | ✅ Yes |
| ❌ Not granted | ❌ Not provided | ❌ No |

### Point Deduction

| Judge Permission | Organizer Key | Can Deduct? |
|------------------|---------------|-------------|
| ✅ Granted | ✅ Required | ✅ Yes |
| ❌ Not granted | ✅ Required | ✅ Yes |
| ✅/❌ Any | ❌ Not provided | ❌ No |

**Key Point:** Deductions ALWAYS require organizer key, regardless of judge permission.

## How Judge Grants Permission

1. Judge logs into their scoring interface
2. Finds their submitted score
3. Toggles "Allow Admin Edit" or similar setting
4. Permission is granted immediately
5. Tech admin can then edit without organizer key

## Workflow Example

```
1. Tech Admin wants to edit a score
   ↓
2. Checks if judge granted permission
   ↓
3a. If YES: Edit directly
3b. If NO: Enter organizer key → Edit
   ↓
4. Make changes to scores
   ↓
5. (Optional) Apply deduction (requires organizer key)
   ↓
6. Submit changes
   ↓
7. Judge receives notification
```

## Access URLs

- **Score Management:** `/tabulation/score-management/round/{roundId}`
- **Edit Score:** `/tabulation/score-management/edit/{scoreId}`
- **Update Score:** POST to `/tabulation/score-management/{scoreId}/update`

## Important Notes

- **Permission is per score** - Judge grants permission for each score individually
- **Organizer key** - Contact Event Organizer to obtain
- **Deductions** - Always require organizer key, even if judge granted permission
- **Notifications** - Judge is notified when their score is edited
- **Audit trail** - All changes are logged

---

**Need Help?**
- Contact Event Organizer for organizer key
- Check if judge has granted permission in score management page
- Review audit logs for change history
