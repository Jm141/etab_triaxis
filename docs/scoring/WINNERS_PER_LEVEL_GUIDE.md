# How to Generate Winners Per Level

## 📍 Where to Generate Winners Per Level

You can generate winners per level using the **Level-Based Reports** in the Reports section.

### Access Reports
1. Go to **Event Page** → Click **"Reports"** button
2. Or go directly to: `/tabulation/events/{eventId}/reports`

---

## 🏆 Reports for Winners Per Level

### Report #7: Preliminary Round Results
**URL**: `/tabulation/events/{eventId}/reports/preliminary`

- Shows all rounds in the **Preliminary Level**
- Displays level rankings (winners across all preliminary rounds)
- Shows contestant numbers, names, teams, and average scores
- **Use Case**: See who won/advanced from Preliminary level

**How to Access:**
1. Go to Reports page
2. Under "B. Round-Based Reports" section
3. Click **"7. Preliminary Round Results"** button
4. No round selection needed - automatically shows Preliminary level

---

### Report #8: Semi-Final Results
**URL**: `/tabulation/events/{eventId}/reports/semi-final`

- Shows all rounds in the **Semi-Final Level**
- Displays level rankings (winners across all semi-final rounds)
- Shows contestant numbers, names, teams, and average scores
- **Use Case**: See who won/advanced from Semi-Final level

**How to Access:**
1. Go to Reports page
2. Under "B. Round-Based Reports" section
3. Click **"8. Semi-Final Results"** button
4. No round selection needed - automatically shows Semi-Final level

---

### Report #9: Final Round Results
**URL**: `/tabulation/events/{eventId}/reports/final`

- Shows all rounds in the **Final Level**
- Displays level rankings (winners across all final rounds)
- Shows contestant numbers, names, teams, and average scores
- **Use Case**: See who won/advanced from Final level (overall winners)

**How to Access:**
1. Go to Reports page
2. Under "B. Round-Based Reports" section
3. Click **"9. Final Round Results"** button
4. No round selection needed - automatically shows Final level

---

## 📊 What These Reports Show

Each level-based report displays:
- **Level Rankings**: Overall rankings for the entire level (combining all rounds in that level)
- **Contestant Information**: Number, name, team
- **Average Score**: Calculated across all rounds in that level
- **Rank**: Final position in that level

---

## 🎯 Quick Summary

| Level | Report Number | Report Name | URL Pattern |
|-------|--------------|-------------|-------------|
| Preliminary | #7 | Preliminary Round Results | `/tabulation/events/{eventId}/reports/preliminary` |
| Semi-Final | #8 | Semi-Final Results | `/tabulation/events/{eventId}/reports/semi-final` |
| Final | #9 | Final Round Results | `/tabulation/events/{eventId}/reports/final` |

---

## 📝 Notes

- These reports automatically calculate level rankings using `calculateLevelRankings()`
- Rankings are based on average scores across all rounds in that level
- Reports show all contestants, not just winners (you can see full rankings)
- All reports can be printed using browser print functionality

---

## 🔍 Alternative: Round-Specific Winners

If you want winners for a **specific round** (not the entire level), use:
- **Report #5**: Category Winners Report - Shows winners for a specific round/category
- **Report #1**: Overall Ranking - Shows final results for a specific round
