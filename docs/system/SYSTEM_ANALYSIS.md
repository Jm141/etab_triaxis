# System Analysis Report

## Overview
This document analyzes the current tabulation system's capabilities regarding:
1. Levels and Rounds with Elimination
2. Permission System After Edit
3. Event Start/End Date Handling

---

## 1. LEVELS AND ROUNDS WITH ELIMINATION

### Current State ✅
- **System HAS levels and rounds structure:**
  - `event_levels` table with `order` field for sequencing
  - `rounds` table linked to levels via `level_id`
  - Both have `status` fields (Pending, Active, Completed)

### Missing Capabilities ❌
- **NO elimination round configuration:**
  - No field in `event_levels` or `rounds` to mark a round as "elimination round"
  - No field to specify how many contestants advance (e.g., "top 10")
  
- **NO automatic contestant filtering:**
  - Currently, ALL active contestants are shown for ALL rounds regardless of level
  - In `JudgeScoringController::roundTable()` (line 101-107), it gets ALL active contestants:
    ```php
    $contestants = $this->db->fetchAll(
        "SELECT c.* FROM contestants c
         WHERE c.event_id = ? AND c.status = 'Active'"
    );
    ```
  - No logic to filter contestants based on previous level rankings

- **NO advancement mechanism:**
  - No system to automatically determine which contestants qualify for the next level
  - No way to mark contestants as "qualified" or "eliminated" based on rankings

### What Needs to be Added:
1. **Database Schema Changes:**
   - Add `is_elimination_round` BOOLEAN to `rounds` table
   - Add `advance_count` INT to `event_levels` table (how many advance to next level)
   - Add `qualified_for_level_id` INT to `contestants` table (which level they qualified for)

2. **Logic Changes:**
   - When calculating rankings for an elimination round, mark top N contestants as qualified
   - Filter contestants in next level to only show those who qualified
   - Add UI to configure elimination settings per level

---

## 2. PERMISSION SYSTEM AFTER EDIT

### Current State ✅
- **System HAS permission request/grant mechanism:**
  - `permission_request_status` field in `scores` table
  - `admin_edit_allowed` field to track if admin can edit
  - Judge can request permission via `requestEditPermission()`
  - Admin can grant/deny via `NotificationController::respondToPermissionRequest()`

### Issue Found ❌
- **Permission is NOT reset after edit:**
  - In `ScoreManagementController::update()` (line 128-240), when admin edits a score:
    - It updates the score details
    - It sets `admin_edited_by` and `admin_edited_at`
    - **BUT it does NOT reset `admin_edit_allowed` or `permission_request_status`**
  
  - This means:
    - After first edit is done, `admin_edit_allowed` stays as `1`
    - Admin can edit again without requesting permission
    - Judge doesn't get notified that another edit is needed

### Current Code Flow:
```php
// ScoreManagementController::update() - Line 220-223
$this->db->query(
    "UPDATE scores SET admin_edited_by = ?, admin_edited_at = NOW() WHERE id = ?",
    [Session::get('user_id'), $scoreId]
);
// ❌ Missing: Reset permission status
```

### What Needs to be Fixed:
1. **After edit is completed, reset permission:**
   ```php
   "UPDATE scores SET 
    admin_edited_by = ?, 
    admin_edited_at = NOW(),
    admin_edit_allowed = 0,
    permission_request_status = 'none'
    WHERE id = ?"
   ```

2. **Notify judge that edit was completed:**
   - Create notification to judge that their score was edited
   - This allows judge to review and request another edit if needed

---

## 3. EVENT START/END DATE HANDLING

### Current State ✅
- **System HAS event date fields:**
  - `event_date` DATE
  - `start_time` TIME
  - `end_time` TIME
  - `status` ENUM('Draft', 'Ongoing', 'Finished', 'Archived')

### Missing Capabilities ❌
- **NO automatic status update:**
  - No cron job or scheduled task to check event dates
  - No automatic status change from 'Draft' → 'Ongoing' → 'Finished'
  - Status must be manually changed by admin

- **Live display shows ALL events:**
  - In `DisplayController::lineup()` (line 77-78), it shows ALL events:
    ```php
    $events = $this->db->fetchAll(
        "SELECT * FROM events ORDER BY event_date DESC, created_at DESC"
    );
    ```
  - No filtering by status or date
  - Past events are still shown in live display

### What Needs to be Added:
1. **Automatic Status Update:**
   - Add method to check event dates and update status:
     - If `event_date` + `start_time` <= NOW() → set status to 'Ongoing'
     - If `event_date` + `end_time` < NOW() → set status to 'Finished'
   - Can be called:
     - On every page load (lightweight check)
     - Via cron job (better performance)
     - Before displaying events

2. **Filter Live Display:**
   - In `DisplayController::lineup()`, filter to show only ongoing events:
     ```php
     "SELECT * FROM events 
      WHERE status = 'Ongoing' 
      ORDER BY event_date DESC"
     ```
   - Or filter by date:
     ```php
     "SELECT * FROM events 
      WHERE (event_date = CURDATE() AND (start_time IS NULL OR start_time <= CURTIME()))
        OR (event_date < CURDATE() AND (end_time IS NULL OR event_date + INTERVAL 1 DAY > CURDATE()))
      ORDER BY event_date DESC"
     ```

3. **Add Helper Method:**
   - Create `updateEventStatuses()` method in `EventController`
   - Call it before displaying events or via scheduled task

---

## SUMMARY

| Feature | Current State | Missing | Priority |
|---------|--------------|---------|----------|
| **Elimination Rounds** | ❌ Not implemented | Database fields, filtering logic, advancement mechanism | HIGH |
| **Permission Reset** | ❌ Not reset after edit | Reset `admin_edit_allowed` and `permission_request_status` after edit | MEDIUM |
| **Event Date Handling** | ❌ Manual only | Automatic status update, live display filtering | MEDIUM |

---

## RECOMMENDATIONS

1. **For Elimination Rounds:**
   - Add database migration for new fields
   - Create level advancement logic
   - Update contestant filtering in scoring controllers
   - Add UI for configuring elimination settings

2. **For Permission System:**
   - Update `ScoreManagementController::update()` to reset permissions
   - Add notification to judge after edit
   - Test the full permission flow

3. **For Event Dates:**
   - Add `updateEventStatuses()` method
   - Call it in `EventController::index()` and `DisplayController::lineup()`
   - Consider adding cron job for production
   - Filter live display to show only ongoing events
