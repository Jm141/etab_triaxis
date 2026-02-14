# Reports Testing Checklist

## ✅ Pre-Testing Requirements

Before testing reports, ensure:
- [ ] You have an active event with at least one level
- [ ] The event has at least one round
- [ ] There are contestants added to the event
- [ ] There are judges assigned to the event
- [ ] At least one judge has submitted scores for a round
- [ ] Rankings have been calculated (via Results page → Calculate)

---

## 🔍 Testing Report Access

### 1. Test Reports Page Access
- [ ] Navigate to Event page
- [ ] Click "Reports" button in header (should be visible)
- [ ] Click "View Reports" button in sidebar (should be visible)
- [ ] Verify Reports index page loads showing all 20 reports

### 2. Test Permission Restrictions
- [ ] As Judge role: Should NOT see Reports button (restricted)
- [ ] As Admin/Tabulator: Should see Reports button (allowed)
- [ ] As Super Admin: Should see Reports button (allowed)

---

## 📊 Test Core Reports (Round-Based)

### Report 1: Overall Ranking
**URL**: `/tabulation/events/{eventId}/reports/overall-ranking/{roundId}`

- [ ] Select a round from dropdown
- [ ] Click "Generate Report"
- [ ] Verify report displays:
  - [ ] Event name and round info
  - [ ] Contestant numbers and names
  - [ ] Scores from each judge
  - [ ] Grand total for each contestant
  - [ ] Final rank (1st, 2nd, 3rd, etc.)
- [ ] Test without roundId (should use final round)
- [ ] Test with round that has no rankings (should show "No rankings available")

### Report 2: Per-Judge Score Sheet
**URL**: `/tabulation/events/{eventId}/reports/per-judge/{roundId}`

- [ ] Select a round from dropdown
- [ ] Click "Generate Report"
- [ ] Verify report displays:
  - [ ] Judge name and number
  - [ ] All contestants with their scores
  - [ ] Criteria breakdown for each score
  - [ ] Total score per contestant
- [ ] Test with round that has no scores (should show empty or message)

### Report 3: Consolidated Scores
**URL**: `/tabulation/events/{eventId}/reports/consolidated/{roundId}`

- [ ] Select a round from dropdown
- [ ] Click "Generate Report"
- [ ] Verify report displays:
  - [ ] All contestants
  - [ ] Scores from all judges in columns
  - [ ] Average score
  - [ ] Total score
  - [ ] Ranking

### Report 4: Criteria Breakdown
**URL**: `/tabulation/events/{eventId}/reports/criteria-breakdown/{roundId}`

- [ ] Select a round from dropdown
- [ ] Click "Generate Report"
- [ ] Verify report displays:
  - [ ] All criteria for the round
  - [ ] Average score per criterion per contestant
  - [ ] Total per criterion

### Report 5: Category Winners
**URL**: `/tabulation/events/{eventId}/reports/category-winners/{roundId}`

- [ ] Select a round from dropdown
- [ ] Click "Generate Report"
- [ ] Verify report displays winners for that round/category

### Report 6: Top N Finalists
**URL**: `/tabulation/events/{eventId}/reports/top-n/{roundId}/{topN}`

- [ ] Select a round from dropdown
- [ ] Enter N value (e.g., 10)
- [ ] Click "Generate Report"
- [ ] Verify report shows only top N contestants

---

## 📋 Test Level-Based Reports

### Report 7: Preliminary Round Results
**URL**: `/tabulation/events/{eventId}/reports/preliminary`

- [ ] Click "Generate Report" (no dropdown needed)
- [ ] Verify report displays:
  - [ ] All rounds in Preliminary level
  - [ ] Level rankings (if calculated)
- [ ] Test with no preliminary level (should show error or message)

### Report 8: Semi-Final Results
**URL**: `/tabulation/events/{eventId}/reports/semi-final`

- [ ] Click "Generate Report"
- [ ] Verify report displays Semi-Final level results
- [ ] Test with no semi-final level (should handle gracefully)

### Report 9: Final Round Results
**URL**: `/tabulation/events/{eventId}/reports/final`

- [ ] Click "Generate Report"
- [ ] Verify report displays Final level results
- [ ] Test with no final level (should handle gracefully)

### Report 10: Elimination Summary
**URL**: `/tabulation/events/{eventId}/reports/elimination-summary/{levelId}`

- [ ] Select a level from dropdown
- [ ] Click "Generate Report"
- [ ] Verify report displays:
  - [ ] Qualified contestants
  - [ ] Eliminated contestants
  - [ ] Cutoff score (if applicable)

---

## 🔐 Test Audit & Transparency Reports

### Report 11: Judge Attendance & Submission
**URL**: `/tabulation/events/{eventId}/reports/judge-attendance/{roundId}`

- [ ] Select a round from dropdown
- [ ] Click "Generate Report"
- [ ] Verify report displays:
  - [ ] All assigned judges
  - [ ] Submission count per judge
  - [ ] Total contestants
  - [ ] Last submission time

### Report 12: Score Edit Log
**URL**: `/tabulation/events/{eventId}/reports/score-edit-log/{roundId}`

- [ ] Select a round from dropdown
- [ ] Click "Generate Report"
- [ ] Verify report displays all score edits (if any)
- [ ] Test with no edits (should show empty or message)

### Report 13: Tie-Breaker Computation
**URL**: `/tabulation/events/{eventId}/reports/tie-breaker/{roundId}`

- [ ] Select a round from dropdown
- [ ] Click "Generate Report"
- [ ] Verify report shows tie resolution logic

### Report 14: Weighted Criteria Computation
**URL**: `/tabulation/events/{eventId}/reports/weighted-computation/{roundId}`

- [ ] Select a round from dropdown
- [ ] Click "Generate Report"
- [ ] Verify report displays:
  - [ ] All criteria
  - [ ] Weight per criterion
  - [ ] Calculation method

---

## 👨‍⚖️ Test Judge Reports

### Report 16: Judge Performance Summary
**URL**: `/tabulation/events/{eventId}/reports/judge-performance/{roundId}`

- [ ] Select a round from dropdown
- [ ] Click "Generate Report"
- [ ] Verify report displays:
  - [ ] All judges
  - [ ] Average score per judge
  - [ ] Min/Max scores
  - [ ] Standard deviation
  - [ ] Total scores submitted

---

## 📈 Test Summary Reports

### Report 18: Event Summary
**URL**: `/tabulation/events/{eventId}/reports/event-summary`

- [ ] Click "Generate Report" (no dropdown needed)
- [ ] Verify report displays:
  - [ ] Event details (name, type, venue, date)
  - [ ] Statistics (judges, contestants, levels, rounds)
  - [ ] Top 10 winners (if final round completed)
- [ ] Test with event that has no winners yet

---

## 📥 Test Export Reports

### Report 19: Excel Export
**URL**: `/tabulation/events/{eventId}/reports/export/excel/{roundId}`

- [ ] Select a round from dropdown
- [ ] Click "Export Excel"
- [ ] Verify file downloads
- [ ] Open file and verify data is correct

### Report 20: CSV Export
**URL**: `/tabulation/events/{eventId}/reports/export/csv/{roundId}`

- [ ] Select a round from dropdown
- [ ] Click "Export CSV"
- [ ] Verify file downloads
- [ ] Open file and verify data is correct

---

## 🐛 Common Issues to Check

### Error Handling
- [ ] Test with invalid eventId (should show error)
- [ ] Test with invalid roundId (should show error)
- [ ] Test with event that has no rounds (should handle gracefully)
- [ ] Test with round that has no contestants (should show empty message)
- [ ] Test with round that has no scores (should show empty message)

### Data Display Issues
- [ ] Check if all numeric values are properly formatted (3 decimal places)
- [ ] Check if null/empty values are handled (show "-" or "N/A")
- [ ] Check if special characters in names are escaped properly
- [ ] Check if long names/team names wrap properly in tables

### Print Functionality
- [ ] Test print button on each report
- [ ] Verify print layout is readable
- [ ] Check if buttons are hidden when printing

### Navigation
- [ ] Test "Back" button on each report (should return to reports index)
- [ ] Test browser back button
- [ ] Verify all links work correctly

---

## ✅ Expected Behavior Summary

### When Reports Should Work:
- ✅ Event exists and user has access
- ✅ Round exists and has scores/rankings
- ✅ User has proper permissions (not Judge)

### When Reports Should Show Empty/Message:
- ⚠️ No rankings calculated yet → "No rankings available"
- ⚠️ No scores submitted → "No scores available"
- ⚠️ No judges assigned → "No judges assigned"
- ⚠️ No contestants → "No contestants"

### When Reports Should Show Error:
- ❌ Invalid eventId → "Event not found"
- ❌ Invalid roundId → "Round not found"
- ❌ No access to event → "Access denied"
- ❌ Judge role → "Access denied"

---

## 🔧 Quick Test Script

To quickly test all reports, you can:

1. **Create a test event** with:
   - 1 level (Preliminary)
   - 1 round
   - 3 contestants
   - 2 judges
   - Submit scores for all contestants
   - Calculate rankings

2. **Test each report category**:
   - Start with Report #1 (Overall Ranking) - most basic
   - Then Report #18 (Event Summary) - overall view
   - Then Report #16 (Judge Performance) - judge summary
   - Then level-based reports (#7, #8, #9)

3. **Check for errors**:
   - Open browser console (F12)
   - Look for JavaScript errors
   - Look for PHP errors in page source
   - Check server error logs

---

## 📝 Notes

- All reports use `restrictJudges()` - Judges cannot access
- All reports use `requireEventAccess()` - Must have event access
- Reports that need roundId will show error if round doesn't exist
- Level-based reports will show error if level doesn't exist
- Empty data is handled with friendly messages, not errors
