# How to Apply Point Deduction

## Overview
Point deduction allows Technical Admin to deduct points from a contestant's score. This feature **ALWAYS requires the organizer key**, even if the judge has granted permission to edit the score.

## Requirements
1. **User Role:** Super Admin, Event Admin, Event Technical Admin, or Tabulator
2. **Organizer Key:** Must be provided by the Event Organizer
3. **Access:** Must have access to the event

## Step-by-Step Instructions

### Step 1: Access Score Management
1. Log in as **Technical Admin** (or Super Admin/Tabulator)
2. Navigate to **Score Management** from the main menu
3. Select the **Event** you want to manage
4. Select the **Round** you want to manage

### Step 2: Find the Score to Edit
1. In the Score Management page, you'll see a list of all scores for that round
2. Find the contestant whose score you want to edit
3. Click the **Edit** button (or pencil icon) next to the score

### Step 3: Access the Edit Score Page
- URL format: `/tabulation/score-management/edit/{scoreId}`
- The edit page will show:
  - Contestant information
  - Current scores for all criteria
  - Score editing form

### Step 4: Enter Organizer Key (If Required)
1. If the judge has **NOT granted permission**, you'll see an **Organizer Key** field
2. Enter the organizer key provided by the Event Organizer
3. **Note:** Even if judge granted permission, you still need organizer key for deductions

### Step 5: Apply Point Deduction
1. Scroll down to the **"Point Deduction (Optional)"** section
2. Enter the **Deduction Amount** (e.g., 2.5, 5.0, etc.)
   - This is the number of points to deduct from the final score
   - Can be a decimal value (up to 3 decimal places)
3. Enter the **Reason for Deduction** (required when deduction > 0)
   - Examples:
     - "Rule violation: Late arrival"
     - "Time penalty: Exceeded time limit"
     - "Dress code violation"
     - "Disqualification penalty"

### Step 6: Update the Score
1. Review all changes
2. Click the **"Update Score"** button
3. Confirm the update in the popup dialog

### Step 7: Verification
- The system will:
  1. Verify the organizer key
  2. Apply the deduction to the score
  3. Log the deduction with reason and timestamp
  4. Notify the judge that their score was edited
  5. Update the final score (original score - deduction)

## Important Notes

### ⚠️ Organizer Key Requirement
- **Point deduction ALWAYS requires organizer key**
- Even if judge granted permission to edit scores
- The organizer key must match the event's organizer key
- Contact the Event Organizer to obtain the key

### 📝 Deduction Display
- In reports and print views:
  - Original score shown with strikethrough
  - Final score (after deduction) shown in red
  - Deduction amount and reason displayed

### 🔐 Security
- All deductions are logged with:
  - Who applied the deduction
  - When it was applied
  - Reason for deduction
  - Organizer key usage (audit trail)

### 📊 Calculation
- **Final Score = Original Total Score - Point Deduction**
- Example:
  - Original Score: 85.500
  - Deduction: 5.000
  - Final Score: 80.500

## Example Workflow

```
1. Technical Admin logs in
2. Goes to Score Management → Event → Round
3. Clicks "Edit" on Contestant #5's score
4. Sees judge has NOT granted permission
5. Enters organizer key: "EVENT2026KEY"
6. Scrolls to Point Deduction section
7. Enters Deduction Amount: 2.500
8. Enters Reason: "Time penalty: 30 seconds over limit"
9. Clicks "Update Score"
10. System verifies key and applies deduction
11. Final score updated: 83.000 → 80.500
12. Judge receives notification
```

## Troubleshooting

### "Organizer Key Required" Error
- **Problem:** You entered a deduction but didn't provide organizer key
- **Solution:** Enter the organizer key in the field above the deduction section

### "Invalid Organizer Key" Error
- **Problem:** The organizer key doesn't match
- **Solution:** Contact Event Organizer to verify the correct key

### "No Organizer Key Set" Error
- **Problem:** Event organizer hasn't set a key yet
- **Solution:** Contact Event Organizer to set the organizer key first

### Deduction Not Showing in Reports
- **Problem:** Deduction applied but not visible
- **Solution:** 
  - Check if deduction was saved (should see in score details)
  - Refresh the report page
  - Check print view (deductions show with strikethrough)

## Access URLs

- **Score Management Index:** `/tabulation/score-management/round/{roundId}`
- **Edit Score:** `/tabulation/score-management/edit/{scoreId}`
- **Update Score:** POST to `/tabulation/score-management/{scoreId}/update`

## Permissions Summary

| Action | Judge Permission | Organizer Key Required |
|--------|------------------|----------------------|
| Edit Scores | Yes | No |
| Edit Scores | No | Yes |
| Apply Deduction | Yes | **Yes** |
| Apply Deduction | No | **Yes** |

**Key Point:** Deductions ALWAYS require organizer key, regardless of judge permission status.

---

**Need Help?** Contact the Event Organizer to obtain the organizer key or set one up for your event.
