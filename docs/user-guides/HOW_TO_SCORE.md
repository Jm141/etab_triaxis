# How to Score Contestants - Judge Guide

## 📋 Quick Start

### Step 1: Access Your Assigned Rounds

1. **Login as Judge**
   - Go to: `http://localhost/tabulation/login`
   - Username: Your judge username
   - Password: Your password

2. **View Your Rounds**
   - After login, you'll see the Dashboard
   - Click on **"My Assigned Rounds"** section
   - Or go to: **Judge → My Rounds**

### Step 2: Select a Round to Score

1. **Choose a Round**
   - You'll see a list of rounds assigned to you
   - Each round shows:
     - Event name
     - Level name
     - Round name
     - Progress (how many contestants you've scored)
     - Status (Active/Pending)

2. **Click on a Round**
   - Click the **"Score"** button or round name
   - You'll see two options:
     - **Individual View** - Score one contestant at a time
     - **Table View** - Score all contestants in one table (RECOMMENDED)

### Step 3: Use Table View (Recommended)

1. **Open Table View**
   - Click the **"Table View"** button
   - Or go directly to: `/tabulation/judge/rounds/{roundId}/table`

2. **Understanding the Table**
   - **Contestant #** column (left) - Shows contestant numbers only (no names for anonymity)
   - **Criteria columns** - Each criterion has:
     - Criterion name at the top
     - Max score shown below
   - **Total Score** column - Automatically calculated
   - **Status** column - Shows if score is submitted

3. **Scoring Process**
   - **Type scores directly in the table**
   - Scores **auto-save** after 1 second of no typing
   - You'll see:
     - "Auto-saving..." indicator while saving
     - "Saved" confirmation when done
   - **Decimal support**: You can enter scores with up to 3 decimal places (e.g., 8.500, 9.750)

4. **Submit Scores**
   - Once all scores are filled, the **"Submit All Scores"** button becomes active
   - Click **"Submit All Scores"**
   - Confirm the submission
   - **After submission**: Scores become **read-only** (locked)

### Step 4: Editing Submitted Scores

If you need to edit a submitted score:

1. **Request Permission**
   - Click the **"Request Edit"** button next to the submitted score
   - Admin/Tabulator will receive a notification
   - Wait for approval

2. **If Admin Requests to Edit Your Score**
   - You'll see: **"Admin wants to edit"** message
   - Click **"Grant"** to allow or **"Deny"** to refuse
   - You'll receive a notification when admin requests permission

---

## 🎯 Scoring Tips

### Best Practices

1. **Use Table View**
   - Faster and more efficient
   - See all contestants and criteria at once
   - Easy to compare scores

2. **Auto-Save Feature**
   - Don't worry about losing data
   - Scores save automatically as you type
   - You can close the page and come back later

3. **Decimal Precision**
   - System supports 3 decimal places
   - Example: 8.500, 9.750, 10.000
   - System automatically rounds to 3 decimals

4. **Submit When Ready**
   - You can work on scores over time (they auto-save as drafts)
   - Submit only when you're completely done
   - Once submitted, you need permission to edit

### Understanding Status Indicators

- **🟡 Draft** - Score is saved but not submitted (you can still edit)
- **🟢 Submitted** - Score is locked and read-only
- **🔵 Permission Requested** - You've requested to edit a submitted score
- **🟠 Admin Wants to Edit** - Admin has requested permission to edit your score

---

## 📊 Scoring Interface Features

### Table View Features

1. **Sticky Headers**
   - Contestant # column stays visible when scrolling
   - Criteria headers stay at top

2. **Auto-Calculation**
   - Total score updates automatically as you type
   - Based on the scoring formula

3. **Visual Feedback**
   - Submitted rows are highlighted in green
   - Locked scores show a lock icon
   - Status badges show current state

4. **Validation**
   - System prevents scores above max score
   - System prevents negative scores
   - Decimal places automatically formatted

---

## ❓ Common Questions

### Q: Can I score contestants one at a time?
**A:** Yes, but Table View is recommended. You can still use the individual scoring page if you prefer.

### Q: What if I make a mistake before submitting?
**A:** No problem! Just change the score - it will auto-save. You can edit freely until you submit.

### Q: What if I need to change a submitted score?
**A:** Click "Request Edit" button. Admin/Tabulator will be notified and can grant permission.

### Q: How do I know if my scores are saved?
**A:** Look for the "Saved" badge at the top of the page. It appears after auto-save completes.

### Q: Can I see contestant names?
**A:** No, for fairness and anonymity, only contestant numbers are shown during scoring.

### Q: What happens if I don't submit all scores?
**A:** Scores remain as drafts. You can continue working on them. Submit when ready.

---

## 🔒 Security & Permissions

### Judge Permissions
- ✅ Can score assigned rounds
- ✅ Can edit own scores (before submission)
- ✅ Can request permission to edit submitted scores
- ✅ Can grant/deny admin edit requests
- ❌ Cannot edit other judges' scores
- ❌ Cannot access admin functions

### After Submission
- Scores become read-only
- Cannot edit without admin permission
- Can request permission to edit
- Can respond to admin permission requests

---

## 📱 Step-by-Step Visual Guide

### 1. Login
```
Login Page → Enter Credentials → Dashboard
```

### 2. Select Round
```
Dashboard → My Assigned Rounds → Click "Score" button
```

### 3. Open Table View
```
Round Page → Click "Table View" button
```

### 4. Score Contestants
```
Table View → Type scores in cells → Auto-saves → Submit All
```

### 5. Submit
```
Fill all scores → "Submit All Scores" button activates → Click → Confirm
```

---

## 🆘 Troubleshooting

### Problem: "No contestants registered"
**Solution:** Contact administrator - contestants need to be added to the event first.

### Problem: "No criteria configured"
**Solution:** Contact administrator - criteria need to be set up for this round.

### Problem: Can't submit scores
**Solution:** Make sure all criteria have scores entered for all contestants.

### Problem: Scores not saving
**Solution:** Check your internet connection. Scores auto-save every 1 second after typing stops.

### Problem: Can't edit submitted score
**Solution:** Click "Request Edit" button to request permission from admin.

---

## 📞 Need Help?

If you encounter any issues:
1. Check this guide first
2. Contact your Event Administrator
3. Check the system notifications for any messages

---

**Remember:** The Table View is the fastest and most efficient way to score all contestants! 🎯

