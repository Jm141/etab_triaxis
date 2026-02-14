# How to Verify Auto-Save is Working

## Visual Indicators

### 1. Status Badges (Top of Page)

When you're on the scoring page, you'll see status badges that show the auto-save state:

- **"Draft"** badge (gray) - Default state, shows scores are being saved as drafts
- **"Auto-saving..."** badge (blue with spinner) - Appears when you type a score and it's waiting to save
- **"Saved"** badge (green) - Appears after a score is successfully saved, shows the time it was saved
- **"Save failed"** badge (red) - Appears if there's an error saving

### 2. Save Counter

Below the status badges, you'll see:
- **(X scores saved)** - Shows how many times auto-save has successfully saved

### 3. Real-Time Feedback

1. **Type a score** in any input field
2. **Wait 1 second** (stop typing)
3. You should see:
   - "Auto-saving..." badge appears (blue, with spinning icon)
   - Then "Saved" badge appears (green, with checkmark and timestamp)
   - Save counter increments
   - After 2 seconds, it returns to "Draft" status

## Browser Console (Advanced)

Open your browser's Developer Tools (F12) and check the Console tab. You'll see detailed logs:

```
[Auto-Save] Starting auto-save timer for contestant 1, criteria 1
[Auto-Save] Auto-save timer triggered, calling saveScore
[Auto-Save] Sending auto-save request {contestantId: 1, criteriaId: 1, score: 85.5, roundId: 1}
[Auto-Save] Auto-save successful {contestantId: 1, criteriaId: 1, score: 85.5, totalSaves: 1}
```

## Simple Test Steps

### Test 1: Visual Verification
1. Go to a scoring round
2. Enter a score (e.g., `85.500`) in any field
3. Stop typing and wait 1-2 seconds
4. **Expected**: You should see "Auto-saving..." then "Saved" badges appear

### Test 2: Persistence Test
1. Enter scores for 2-3 contestants
2. Wait for "Saved" badges to appear
3. Click "Back" to go to dashboard
4. Return to the same round
5. **Expected**: Your scores should still be there

### Test 3: Console Verification
1. Open Browser Console (F12 → Console tab)
2. Enter a score
3. **Expected**: You should see `[Auto-Save]` log messages showing the save process

### Test 4: Network Verification
1. Open Browser Developer Tools (F12 → Network tab)
2. Enter a score and wait
3. **Expected**: You should see a POST request to `/judge/rounds/{roundId}/contestants/{contestantId}/auto-save` with status 200

## Troubleshooting

### If auto-save doesn't seem to work:

1. **Check Console for Errors**
   - Open F12 → Console
   - Look for red error messages
   - Common issues: Network errors, validation errors

2. **Check Network Tab**
   - Open F12 → Network tab
   - Enter a score
   - Look for the auto-save request
   - Check if it returns 200 (success) or an error code

3. **Verify Database**
   - Check if scores are actually being saved to the database
   - Query: `SELECT * FROM scores WHERE is_draft = 1`
   - Query: `SELECT * FROM score_details WHERE score_id IN (SELECT id FROM scores WHERE is_draft = 1)`

4. **Check Browser Console Logs**
   - Look for `[Auto-Save]` messages
   - If you see "Auto-save failed" messages, check the error details

## What Gets Saved

- **When**: 1 second after you stop typing
- **What**: Individual score for each criterion
- **Where**: `scores` table (as draft) and `score_details` table
- **Status**: `is_draft = 1`, `is_submitted = 0`

## Disabling Console Logging

If you want to disable the console logging (for production), edit `views/judge/score_table.php` and change:

```javascript
const DEBUG_AUTOSAVE = true;
```

to:

```javascript
const DEBUG_AUTOSAVE = false;
```

