# Which Accounts Can View All Scores?

## Roles That Can View All Scores

The following roles can view and manage all scores for rounds:

### 1. **Super Admin**
- **Username**: `admin`
- **Password**: `admin123`
- **Access**: Can view ALL scores from ALL events
- **Features**:
  - View all scores for any round
  - Edit scores (with judge permission)
  - Print scores
  - Request permission to edit scores

### 2. **Event Admin**
- **Access**: Can view scores for events they are assigned to
- **Features**:
  - View all scores for assigned events
  - Edit scores (with judge permission)
  - Print scores
  - Request permission to edit scores

### 3. **Event Organizer**
- **Access**: Can view scores for events they are assigned to
- **Features**:
  - View all scores for assigned events
  - Edit scores (with judge permission)
  - Print scores
  - Request permission to edit scores

### 4. **Tabulator**
- **Access**: Can view scores for events they are assigned to
- **Features**:
  - View all scores for assigned events
  - Edit scores (with judge permission)
  - Print scores
  - Request permission to edit scores

## How to View All Scores

### Step 1: Login
Login with one of the accounts above (e.g., Super Admin: `admin` / `admin123`)

### Step 2: Navigate to Score Management
1. Go to **Events** → Select an event
2. Go to **Rounds** → Select a round
3. Click **"Manage Scores"** or **"View Scores"** button
   
   OR
   
   Navigate directly to: `/tabulation/score-management/round/{roundId}`

### Step 3: View Scores
You'll see a table showing:
- All contestants
- All judges
- Individual criterion scores
- Total scores
- Submission status
- Permission status

## Features Available

### View Scores
- See all scores for all contestants and judges
- Filter by contestant or judge
- See which scores are submitted vs. draft

### Edit Scores
- Edit any judge's score (if permission is granted)
- Request permission from judge if needed
- See edit history

### Print Scores
- Print all scores for a round
- Grouped by contestant and judge
- Print-friendly format

## Judge Role
- **Cannot** view all scores
- Can only view and edit their own scores
- Can see scores they've submitted

## Quick Access URLs

- **Score Management**: `/tabulation/score-management/round/{roundId}`
- **Print Scores**: `/tabulation/score-management/round/{roundId}/print`

## Example Workflow

1. **Super Admin** logs in (`admin` / `admin123`)
2. Navigates to: **Events** → **Miss Universe 2024** → **Rounds** → **Talent Competition**
3. Clicks **"Manage Scores"** button
4. Sees all scores from all 7 judges for all 25 contestants
5. Can edit scores (if judge granted permission)
6. Can print scores for records

## Notes

- **Event Access**: Event Admin, Event Organizer, and Tabulator can only see scores for events they're assigned to
- **Super Admin**: Can see scores from ALL events (no filtering)
- **Permission Required**: To edit scores, the judge must grant permission (or admin must request it)
- **Submitted Scores Only**: The score management view typically shows only submitted scores (not drafts)

