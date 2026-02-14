# Auto-Save Debugging Guide

## How Auto-Save Works

The auto-save system uses **AJAX (Asynchronous JavaScript and XML)** to save scores to the database without refreshing the page.

### Mechanism:

1. **Event Listener**: Listens for `input` events on score input fields
2. **Debounce Timer**: Waits 1 second after you stop typing before saving
3. **AJAX Request**: Sends a POST request to the server
4. **Server Processing**: PHP controller saves to database
5. **Response**: JavaScript updates the UI to show "Saved" status

### Technical Details:

- **Technology**: jQuery AJAX (`$.ajax()`)
- **Endpoint**: `/tabulation/judge/rounds/{roundId}/contestants/{contestantId}/auto-save`
- **Method**: POST
- **Data Sent**: 
  - `csrf_token`: Security token
  - `criteria_id`: Which criterion is being scored
  - `raw_score`: The score value

## Debugging Steps

### Step 1: Check if JavaScript is Loading

Open browser console (F12) and look for:
```
Score table script loading...
jQuery ready - initializing auto-save...
Round ID: 1 Judge ID: 1
Found score inputs: 25
[Auto-Save] Auto-save system initialized
```

**If you don't see these messages:**
- JavaScript might not be loading
- Check for syntax errors in console
- Verify jQuery is loaded

### Step 2: Check if Input Events are Firing

When you type in a score field, you should see:
```
Input event triggered on score-input
Input values: {contestantId: 1, criteriaId: 1, inputValue: "85.5", max: 100}
[Auto-Save] Starting auto-save timer for contestant 1, criteria 1, value: 85.5
```

**If you don't see these messages:**
- Input fields might not have the `score-input` class
- Event handlers might not be attached
- Check if inputs are `readonly`

### Step 3: Check if Timer is Firing

After 1 second, you should see:
```
[Auto-Save] Auto-save timer triggered, calling saveScore
Timer fired, calling saveScore with value: 85.5
```

**If you don't see this:**
- Timer might be getting cleared
- Check if value validation is failing

### Step 4: Check AJAX Request

You should see:
```
=== AUTO-SAVE AJAX REQUEST ===
URL: /tabulation/judge/rounds/1/contestants/1/auto-save
Data: {csrf_token: "...", criteria_id: 1, raw_score: 85.5}
```

**Then check Network tab (F12 → Network):**
- Look for POST request to `/auto-save`
- Check status code (should be 200)
- Check response (should be `{"success": true, "message": "Score saved"}`)

### Step 5: Check Server Response

You should see:
```
=== AUTO-SAVE SUCCESS ===
Response: {success: true, message: "Score saved"}
[Auto-Save] Auto-save successful
```

## Common Issues

### Issue 1: No Console Messages at All

**Possible Causes:**
- JavaScript syntax error preventing execution
- jQuery not loaded
- Script not included in page

**Solution:**
- Check browser console for syntax errors
- Verify jQuery is loaded: `console.log(typeof $)`
- Check if script tag is in the HTML

### Issue 2: Input Events Not Firing

**Possible Causes:**
- Inputs are `readonly`
- Inputs don't have `score-input` class
- Event handlers not attached

**Solution:**
- Check HTML: `<input class="score-input ...">`
- Verify inputs are not `readonly`
- Check data attributes: `data-contestant-id`, `data-criteria-id`

### Issue 3: AJAX Request Not Sending

**Possible Causes:**
- Invalid URL
- Missing CSRF token
- Network error

**Solution:**
- Check Network tab for failed requests
- Verify route exists in `routes/web.php`
- Check CSRF token is valid

### Issue 4: Server Returns Error

**Possible Causes:**
- Judge not assigned to round
- Invalid score value
- Database error

**Solution:**
- Check server response in Network tab
- Check server error logs
- Verify judge assignment

## Manual Test

1. **Open Console** (F12)
2. **Type in a score field**: `85.500`
3. **Wait 1 second**
4. **Check console** for messages
5. **Check Network tab** for AJAX request
6. **Verify** "Saved" badge appears

## Quick Test Button

Click the **"Test Auto-Save"** button to manually trigger auto-save and see all debug messages.

## What Gets Saved

- **Table**: `scores` (as draft)
- **Table**: `score_details` (individual criterion scores)
- **Status**: `is_draft = 1`, `is_submitted = 0`
- **Recalculation**: Total score is automatically recalculated

## Verification

After auto-save, verify in database:
```sql
SELECT * FROM scores WHERE is_draft = 1 ORDER BY created_at DESC LIMIT 5;
SELECT * FROM score_details WHERE score_id IN (SELECT id FROM scores WHERE is_draft = 1) ORDER BY created_at DESC LIMIT 5;
```

