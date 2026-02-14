# Quick Test Checklist

## 🎯 Test 1: Elimination Rounds (5 minutes)

### Setup:
1. ✅ Create Level: "Preliminaries" → Advance Count: **20**
2. ✅ Create Level: "Semi-Finals" → Advance Count: **10**
3. ✅ Create Round in Preliminaries → Check **"Elimination Round"**

### Test:
1. ✅ Score all contestants in Preliminaries
2. ✅ Check Semi-Finals round → Should see **only top 20**

**Expected:** Only qualified contestants appear in next level ✅

---

## 🔐 Test 2: Permission Reset (3 minutes)

### Setup:
1. ✅ Judge submits score
2. ✅ Judge grants permission to edit

### Test:
1. ✅ Admin edits score
2. ✅ Try to edit again → Should require **new permission**
3. ✅ Check judge notifications → Should see **"Score Edited"** notification

**Expected:** Permission resets after each edit ✅

---

## 📅 Test 3: Event Status Updates (2 minutes)

### Setup:
1. ✅ Create event: Date = **Today**, Start Time = **1 hour ago**
2. ✅ Create event: Date = **Yesterday**

### Test:
1. ✅ View Events page → First event = **"Ongoing"**, Second = **"Finished"**
2. ✅ Logout → View Live Display → Should see **only "Ongoing"** events

**Expected:** Status updates automatically, live display filters correctly ✅

---

## 🚨 Quick Fixes

**Elimination not working?**
- Check: `advance_count` is set in level
- Check: `is_elimination_round` is checked
- Check: Rankings are calculated

**Permission not resetting?**
- Check: Score was actually edited
- Check: Database: `admin_edit_allowed = 0` after edit

**Status not updating?**
- Refresh page (updates on page load)
- Check: Date format is correct (YYYY-MM-DD)
- Check: Time format is correct (HH:MM:SS)

---

**All tests pass?** ✅ System is working correctly!
