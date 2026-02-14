# Judge Setup Guide

## Problem: Judge sees "No rounds assigned" when logging in

If a judge logs in and sees an empty rounds list, it means they haven't been properly set up yet. Follow these steps:

## Step-by-Step Setup Process

### Step 1: Create Judge User Account

1. **Login as Super Admin or Event Admin**
2. Go to **Users** → **Add New User**
3. Fill in:
   - Full Name
   - Username (for login)
   - Email
   - Password
   - **Role: Judge**
4. Click **Create User**

### Step 2: Assign Judge to an Event

1. Go to **Judges** (in sidebar)
2. Find the judge user in the "All Judge Users" section
3. Click **"Assign to Event"** button
4. Select the event from dropdown
5. (Optional) Enter Judge Number and Specialty
6. Click **Assign**

**OR** you can assign during judge creation:
- When creating a new judge, select an event in the "Event Assignment" section

### Step 3: Assign Judge to Specific Rounds

1. In **Judges** page, find the judge in the "All Judges" table
2. Click the **"Assign Rounds"** button (list icon)
3. Check the rounds you want to assign to this judge
4. Click **"Save Assignments"**

### Step 4: Verify Setup

1. **Logout** as admin
2. **Login as the judge** (using the judge's username and password)
3. Go to **"My Rounds"** in the sidebar
4. You should now see the assigned rounds!

## Important Notes

- **Judges cannot assign themselves** - only Super Admin or Event Admin can do this
- **Two-step process required:**
  1. Assign to Event (creates judge record)
  2. Assign to Rounds (creates round assignments)
- **Both steps are necessary** - just assigning to an event isn't enough
- Judges can only see rounds they are assigned to

## Troubleshooting

### Judge still sees no rounds after setup?

1. **Check if judge is assigned to event:**
   - Go to Judges page
   - Look in "All Judges" table
   - If judge is not there, assign them to an event first

2. **Check if rounds exist for the event:**
   - Go to Events → Select Event
   - Check if Levels and Rounds are created
   - If no rounds exist, create them first

3. **Check if judge is assigned to rounds:**
   - Go to Judges page
   - Click "Assign Rounds" for the judge
   - Verify rounds are checked
   - If not, assign them

4. **Check database directly:**
   ```sql
   -- Check if judge exists
   SELECT * FROM judges WHERE user_id = [judge_user_id];
   
   -- Check if judge has round assignments
   SELECT ja.*, r.name as round_name 
   FROM judge_assignments ja
   JOIN judges j ON ja.judge_id = j.id
   JOIN rounds r ON ja.round_id = r.id
   WHERE j.user_id = [judge_user_id] AND ja.is_active = 1;
   ```

## Quick Setup Checklist

- [ ] Judge user account created with "Judge" role
- [ ] Judge assigned to an event
- [ ] Event has levels and rounds created
- [ ] Judge assigned to specific rounds
- [ ] Judge can login and see "My Rounds"

## Database Structure

The system uses two tables for judge assignments:

1. **`judges`** - Links user to event
   - `user_id` → User account
   - `event_id` → Event
   - `is_active` → Must be 1

2. **`judge_assignments`** - Links judge to specific rounds
   - `judge_id` → Judge record
   - `round_id` → Round
   - `is_active` → Must be 1

**Both must be active for judge to see rounds!**

