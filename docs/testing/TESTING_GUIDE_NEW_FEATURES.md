# Testing Guide for New Features

This guide provides step-by-step instructions to test all three new features:
1. Elimination Round Functionality
2. Permission Reset After Edit
3. Automatic Event Status Updates & Live Display Filtering

---

## Prerequisites

- Database migration has been run (already done ✅)
- You have admin access to the system
- You have at least one event created
- You have judges and contestants set up

---

## TEST 1: Elimination Round Functionality

### Step 1: Create Levels with Advance Count

1. **Navigate to Events:**
   - Go to: Events → [Select an Event] → Manage Levels

2. **Create Level 1 (Preliminaries):**
   - Click "Add New Level"
   - Name: `Preliminaries`
   - Description: `Initial competition`
   - **Advance Count: `20`** ← Enter this number
   - Click "Add"
   - ✅ Verify: Badge shows "Top 20" in the table

3. **Create Level 2 (Semi-Finals):**
   - Name: `Semi-Finals`
   - Description: `Top 20 compete`
   - **Advance Count: `10`** ← Enter this number
   - Click "Add"
   - ✅ Verify: Badge shows "Top 10"

4. **Create Level 3 (Finals):**
   - Name: `Finals`
   - Description: `Final competition`
   - **Advance Count: Leave empty** (all advance)
   - Click "Add"
   - ✅ Verify: Badge shows "All Advance"

### Step 2: Create Rounds and Mark Elimination Rounds

1. **Go to Preliminaries Level:**
   - Click "Manage Rounds" on Preliminaries level

2. **Create Round 1 (Talent - Elimination Round):**
   - Name: `Talent Competition`
   - Description: `Contestants showcase talents`
   - **Check "Elimination Round"** ✅
   - Click "Add"
   - ✅ Verify: Badge shows "Elimination" in Type column

3. **Create Round 2 (Regular Round):**
   - Name: `Swimsuit Competition`
   - Description: `Physical fitness`
   - **Leave "Elimination Round" unchecked**
   - Click "Add"
   - ✅ Verify: Badge shows "Regular" in Type column

4. **Repeat for Semi-Finals:**
   - Go to Semi-Finals level
   - Create rounds (mark one as elimination if needed)

### Step 3: Assign Judges and Set Up Scoring

1. **Assign Judges:**
   - Go to Events → [Event] → Assign Judges
   - Assign at least 2-3 judges to the rounds

2. **Assign Criteria:**
   - Go to Rounds → [Round] → Set Weights
   - Add criteria to each round

### Step 4: Test Contestant Filtering

**Before Elimination (Preliminaries Level):**

1. **Login as Judge:**
   - Go to Judge Dashboard → Select Round (Preliminaries - Talent)
   - ✅ Verify: You see ALL active contestants

2. **Submit Scores:**
   - Score all contestants
   - Submit all scores

3. **Calculate Rankings:**
   - Go to Results → [Event]
   - Rankings should be calculated automatically
   - ✅ Verify: Rankings show all contestants

**After Elimination (Semi-Finals Level):**

1. **Check Rankings:**
   - Go to Results → [Event] → View Round (Preliminaries - Talent)
   - Note the top 20 contestants

2. **Login as Judge for Semi-Finals:**
   - Go to Judge Dashboard → Select Round (Semi-Finals)
   - ✅ **VERIFY: You should ONLY see the top 20 contestants**
   - ✅ **VERIFY: Contestants not in top 20 are NOT shown**

3. **Check Database (Optional):**
   ```sql
   SELECT c.name, c.qualified_for_level_id, el.name as qualified_level
   FROM contestants c
   LEFT JOIN event_levels el ON c.qualified_for_level_id = el.id
   WHERE c.event_id = [YOUR_EVENT_ID]
   ORDER BY c.qualified_for_level_id, c.contestant_number;
   ```
   - ✅ Verify: Top 20 have `qualified_for_level_id` set to Semi-Finals level ID
   - ✅ Verify: Others have `qualified_for_level_id` as NULL

### Step 5: Test Full Elimination Flow

1. **Complete Preliminaries:**
   - All judges submit scores for Preliminaries rounds
   - Rankings calculated automatically

2. **Check Semi-Finals:**
   - Only top 20 should appear
   - Complete Semi-Finals scoring

3. **Check Finals:**
   - Only top 10 from Semi-Finals should appear
   - ✅ Verify: Correct contestants are filtered

---

## TEST 2: Permission Reset After Edit

### Step 1: Set Up Test Scenario

1. **Login as Judge:**
   - Go to Judge Dashboard
   - Select a round
   - Score a contestant
   - **Submit the score**

2. **Grant Permission:**
   - On the score submission page
   - **Check "Allow Admin/Tabulator to Edit This Score"** ✅
   - ✅ Verify: Permission is granted

### Step 2: Admin Edits Score

1. **Login as Admin/Tabulator:**
   - Go to Score Management → [Round]
   - Find the score you just submitted
   - ✅ Verify: "Edit" button is available (permission granted)

2. **Edit the Score:**
   - Click "Edit"
   - Change some scores
   - Click "Save"

3. **Check Permission Status:**
   - ✅ **VERIFY: Success message says "Permission reset"**
   - Go back to Score Management
   - Try to edit the same score again
   - ✅ **VERIFY: Edit button is disabled OR requires new permission**

### Step 3: Verify Judge Notification

1. **Login as Judge:**
   - Go to Notifications
   - ✅ **VERIFY: You see notification "Score Edited by Admin"**
   - ✅ **VERIFY: Notification shows which contestant was edited**

### Step 4: Test Permission Request Again

1. **As Admin:**
   - Try to edit the same score again
   - ✅ **VERIFY: System says "Judge has not granted permission"**

2. **As Judge:**
   - Go to your score page
   - Grant permission again
   - ✅ **VERIFY: Permission can be granted again**

3. **As Admin:**
   - Edit score again
   - ✅ **VERIFY: Permission is reset again after edit**

### Step 5: Check Database (Optional)

```sql
SELECT s.id, s.admin_edit_allowed, s.permission_request_status, 
       s.admin_edited_by, s.admin_edited_at
FROM scores s
WHERE s.id = [SCORE_ID];
```

- ✅ Verify: `admin_edit_allowed = 0` after edit
- ✅ Verify: `permission_request_status = 'none'` after edit
- ✅ Verify: `admin_edited_by` and `admin_edited_at` are set

---

## TEST 3: Automatic Event Status Updates

### Step 1: Create Test Events

1. **Create Event 1 (Future Event - Should be Draft):**
   - Go to Events → Create Event
   - Name: `Test Event - Future`
   - Event Date: **Tomorrow's date**
   - Start Time: `10:00:00`
   - End Time: `18:00:00`
   - Status: `Draft`
   - Save
   - ✅ **VERIFY: Status is "Draft"**

2. **Create Event 2 (Today's Event - Should become Ongoing):**
   - Name: `Test Event - Today`
   - Event Date: **Today's date**
   - Start Time: **Time 1 hour ago** (e.g., if now is 3 PM, set to 2 PM)
   - End Time: **Time 2 hours from now** (e.g., if now is 3 PM, set to 5 PM)
   - Status: `Draft`
   - Save
   - ✅ **VERIFY: Status changes to "Ongoing" automatically**

3. **Create Event 3 (Past Event - Should be Finished):**
   - Name: `Test Event - Past`
   - Event Date: **Yesterday's date**
   - Start Time: `10:00:00`
   - End Time: `18:00:00`
   - Status: `Ongoing`
   - Save
   - ✅ **VERIFY: Status changes to "Finished" automatically**

### Step 2: Test Status Updates on Page Load

1. **Refresh Events Page:**
   - Go to Events → View All Events
   - ✅ **VERIFY: Event 1 (Future) = Draft**
   - ✅ **VERIFY: Event 2 (Today) = Ongoing**
   - ✅ **VERIFY: Event 3 (Past) = Finished**

2. **Check Multiple Times:**
   - Refresh the page several times
   - ✅ **VERIFY: Statuses update correctly each time**

### Step 3: Test Live Display Filtering

1. **Public View (Not Logged In):**
   - Logout (or use incognito/private window)
   - Go to Live Display → Lineup Selector
   - ✅ **VERIFY: Only "Ongoing" events are shown**
   - ✅ **VERIFY: Draft and Finished events are NOT shown**

2. **Admin View (Logged In):**
   - Login as admin
   - Go to Live Display → Lineup Selector
   - ✅ **VERIFY: ALL events are shown** (Draft, Ongoing, Finished)

### Step 4: Test Time-Based Status Changes

1. **Create Event with Start Time:**
   - Event Date: Today
   - Start Time: **5 minutes from now**
   - Status: Draft
   - Save
   - ✅ **VERIFY: Status is still "Draft"**

2. **Wait 5+ Minutes:**
   - Wait for start time to pass
   - Refresh Events page
   - ✅ **VERIFY: Status automatically changed to "Ongoing"**

3. **Create Event with End Time:**
   - Event Date: Today
   - Start Time: 1 hour ago
   - End Time: **5 minutes from now**
   - Status: Ongoing
   - Save
   - ✅ **VERIFY: Status is "Ongoing"**

4. **Wait for End Time:**
   - Wait for end time to pass
   - Refresh Events page
   - ✅ **VERIFY: Status automatically changed to "Finished"**

### Step 5: Test Events Without Times

1. **Create Event with Date Only:**
   - Event Date: Today
   - Start Time: **Leave empty**
   - End Time: **Leave empty**
   - Status: Draft
   - Save
   - ✅ **VERIFY: Status becomes "Ongoing"** (date matches today)

2. **Create Past Event:**
   - Event Date: Yesterday
   - Start Time: Leave empty
   - End Time: Leave empty
   - Status: Ongoing
   - Save
   - ✅ **VERIFY: Status becomes "Finished"**

---

## Quick Verification Checklist

### Elimination Rounds ✅
- [ ] Levels have "Advance Count" field
- [ ] Rounds have "Elimination Round" checkbox
- [ ] Top N contestants appear in next level
- [ ] Non-qualified contestants are filtered out
- [ ] Database shows `qualified_for_level_id` set correctly

### Permission Reset ✅
- [ ] Admin can edit after judge grants permission
- [ ] Permission resets after edit
- [ ] Judge receives notification
- [ ] Admin needs new permission for next edit
- [ ] Database shows `admin_edit_allowed = 0` after edit

### Event Status Updates ✅
- [ ] Events change from Draft → Ongoing automatically
- [ ] Events change from Ongoing → Finished automatically
- [ ] Status updates on page load
- [ ] Live display shows only Ongoing events (public)
- [ ] Live display shows all events (admin)

---

## Troubleshooting

### Elimination Rounds Not Working?

1. **Check Database:**
   ```sql
   SELECT * FROM event_levels WHERE event_id = [ID];
   SELECT * FROM rounds WHERE level_id = [ID];
   ```
   - Verify `advance_count` is set
   - Verify `is_elimination_round` is checked

2. **Check Rankings:**
   - Make sure rankings are calculated
   - Check if `qualified_for_level_id` is set in contestants table

3. **Check Round Order:**
   - Make sure rounds are in correct order
   - Elimination round should be the last round of the level

### Permission Reset Not Working?

1. **Check Score Status:**
   ```sql
   SELECT admin_edit_allowed, permission_request_status FROM scores WHERE id = [ID];
   ```

2. **Check Notifications:**
   - Verify notification was created
   - Check notifications table

### Event Status Not Updating?

1. **Check Dates:**
   - Verify event_date format (YYYY-MM-DD)
   - Verify time format (HH:MM:SS)

2. **Check Timezone:**
   - Make sure server timezone matches your expectations
   - Check MySQL timezone settings

3. **Manual Check:**
   ```sql
   SELECT id, name, event_date, start_time, end_time, status 
   FROM events 
   WHERE id = [ID];
   ```

---

## Test Data Examples

### Example Event Setup:
```
Event: "Beauty Pageant 2024"
├── Level 1: Preliminaries (Advance: 20)
│   ├── Round 1: Talent (Elimination: ✓)
│   ├── Round 2: Swimsuit
│   └── Round 3: Evening Gown
├── Level 2: Semi-Finals (Advance: 10)
│   ├── Round 4: Interview (Elimination: ✓)
│   └── Round 5: Swimsuit (Semi-Finals)
└── Level 3: Finals (Advance: All)
    └── Round 6: Final Q&A
```

### Expected Flow:
1. **Preliminaries:** All 50 contestants compete
2. **After Round 1 (Elimination):** Top 20 qualify for Semi-Finals
3. **Semi-Finals:** Only 20 contestants appear
4. **After Round 4 (Elimination):** Top 10 qualify for Finals
5. **Finals:** Only 10 contestants appear

---

## Success Criteria

✅ **All tests pass if:**
- Elimination rounds correctly filter contestants
- Permissions reset after each edit
- Event statuses update automatically
- Live display shows only ongoing events (public)

If any test fails, check the troubleshooting section or review the implementation code.
