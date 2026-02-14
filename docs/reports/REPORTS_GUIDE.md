# Tabulation System - Reports Guide

## 📍 Where to Access Reports

Reports can be accessed from:
1. **Event Page** → Click "Reports" button
2. **Results Page** → Click "Reports" button  
3. **Direct URL**: `/tabulation/events/{eventId}/reports`

## 📊 Available Reports (20 Total)

### A. Core Pageant Tabulation Reports (MUST-HAVE)

#### 1. Overall Ranking / Final Results
- **URL**: `/tabulation/events/{eventId}/reports/overall-ranking` or `/tabulation/events/{eventId}/reports/overall-ranking/{roundId}`
- **Description**: Shows candidate number & name, total score per judge, grand total, and final rank
- **Use Case**: Final results display with all judge scores

#### 2. Per-Judge Score Sheet
- **URL**: `/tabulation/events/{eventId}/reports/per-judge/{roundId}` or `/tabulation/events/{eventId}/reports/per-judge/{roundId}/{judgeId}`
- **Description**: Shows judge name, candidate scores per criterion, and total score
- **Use Case**: Individual judge scoring breakdown

#### 3. Consolidated Scores Report
- **URL**: `/tabulation/events/{eventId}/reports/consolidated/{roundId}`
- **Description**: Shows candidate, Judge 1-N scores, Average/Total, and Ranking
- **Use Case**: Complete score matrix for a round

#### 4. Criteria Breakdown Report
- **URL**: `/tabulation/events/{eventId}/reports/criteria-breakdown/{roundId}`
- **Description**: Shows candidate, scores per criterion, total per criterion
- **Use Case**: Performance analysis by criteria

#### 5. Category Winners Report
- **URL**: `/tabulation/events/{eventId}/reports/category-winners/{roundId}`
- **Description**: Best in Evening Gown, Talent, Swimsuit, etc.
- **Use Case**: Category-specific winners

#### 6. Top N Finalists Report
- **URL**: `/tabulation/events/{eventId}/reports/top-n/{roundId}` or `/tabulation/events/{eventId}/reports/top-n/{roundId}/{topN}`
- **Description**: Top 5/10/15 candidates with total scores
- **Use Case**: Quick view of top performers

---

### B. Round-Based Reports

#### 7. Preliminary Round Results
- **URL**: `/tabulation/events/{eventId}/reports/preliminary`
- **Description**: Summary of all preliminary rounds and level rankings
- **Use Case**: **SUMMARY PER ROUNDS PER LEVEL** - Preliminary level

#### 8. Semi-Final Results
- **URL**: `/tabulation/events/{eventId}/reports/semi-final`
- **Description**: Summary of all semi-final rounds and level rankings
- **Use Case**: **SUMMARY PER ROUNDS PER LEVEL** - Semi-Final level

#### 9. Final Round Results
- **URL**: `/tabulation/events/{eventId}/reports/final`
- **Description**: Summary of all final rounds and level rankings
- **Use Case**: **SUMMARY PER ROUNDS PER LEVEL** - Final level

#### 10. Elimination Summary Report
- **URL**: `/tabulation/events/{eventId}/reports/elimination-summary/{levelId}`
- **Description**: Shows qualified vs eliminated contestants per level
- **Use Case**: Advancement tracking

---

### C. Transparency & Audit Reports

#### 11. Judge Attendance & Submission
- **URL**: `/tabulation/events/{eventId}/reports/judge-attendance/{roundId}`
- **Description**: Shows which judges submitted scores and when
- **Use Case**: Track judge participation

#### 12. Score Change / Edit Log
- **URL**: `/tabulation/events/{eventId}/reports/score-edit-log/{roundId}`
- **Description**: Audit trail of all score edits
- **Use Case**: Transparency and accountability

#### 13. Tie-Breaker Computation
- **URL**: `/tabulation/events/{eventId}/reports/tie-breaker/{roundId}`
- **Description**: Shows how ties were resolved
- **Use Case**: Verification of tie-breaking logic

#### 14. Weighted Criteria Computation
- **URL**: `/tabulation/events/{eventId}/reports/weighted-computation/{roundId}`
- **Description**: Shows how criteria weights are calculated
- **Use Case**: Understanding scoring methodology

---

### D. Judge & System Reports

#### 16. Judge Performance Summary
- **URL**: `/tabulation/events/{eventId}/reports/judge-performance/{roundId}`
- **Description**: Statistics per judge (avg, min, max, std dev scores)
- **Use Case**: **SUMMARY PER JUDGE** - Performance metrics

#### 18. Event Summary Report
- **URL**: `/tabulation/events/{eventId}/reports/event-summary`
- **Description**: Overall event statistics, winners, and key metrics
- **Use Case**: **OVERALL SUMMARY** - Complete event overview

---

### E. Export & Format Reports

#### 19. Excel Export
- **URL**: `/tabulation/events/{eventId}/reports/export/excel/{roundId}`
- **Description**: Download scores as Excel file
- **Use Case**: Data export for analysis

#### 20. CSV Export
- **URL**: `/tabulation/events/{eventId}/reports/export/csv/{roundId}`
- **Description**: Download scores as CSV file
- **Use Case**: Data export for analysis

---

## 🎯 Summary Reports You Requested

### ✅ Summary Per Rounds Per Level
**Available Reports:**
- **Report #7**: Preliminary Round Results - Shows all rounds in Preliminary level
- **Report #8**: Semi-Final Results - Shows all rounds in Semi-Final level  
- **Report #9**: Final Round Results - Shows all rounds in Final level

**How to Access:**
1. Go to `/tabulation/events/{eventId}/reports`
2. Under "B. Round-Based Reports" section
3. Click the respective report button

### ✅ Overall Summary
**Available Reports:**
- **Report #18**: Event Summary Report - Complete event overview with statistics and winners
- **Report #1**: Overall Ranking - Final results with all judge scores

**How to Access:**
1. Go to `/tabulation/events/{eventId}/reports`
2. For Event Summary: Click "18. Event Summary Report" under "D. Judge & System Reports"
3. For Overall Ranking: Click "1. Overall Ranking / Final Results" under "A. Core Pageant Tabulation Reports"

### ✅ Summary Per Judge
**Available Reports:**
- **Report #16**: Judge Performance Summary - Statistics per judge (avg, min, max, std dev)
- **Report #2**: Per-Judge Score Sheet - Detailed scoring breakdown per judge
- **Report #11**: Judge Attendance & Submission - Submission status per judge

**How to Access:**
1. Go to `/tabulation/events/{eventId}/reports`
2. Select a round from the dropdown
3. Click the respective report button:
   - "16. Judge Performance Summary" for statistics
   - "2. Per-Judge Score Sheet" for detailed scores
   - "11. Judge Attendance & Submission" for submission status

---

## 📝 Notes

- Most reports require selecting a **Round** from a dropdown
- Some reports (like Preliminary/Semi-Final/Final) are level-based and don't require round selection
- Reports are restricted to authorized users (not accessible to Judges)
- All reports can be printed using browser print functionality

---

## 🔍 Quick Reference

| What You Need | Report Number | Report Name |
|--------------|---------------|-------------|
| Summary per rounds per level | #7, #8, #9 | Preliminary/Semi-Final/Final Results |
| Overall summary | #18, #1 | Event Summary / Overall Ranking |
| Summary per judge | #16, #2, #11 | Judge Performance / Per-Judge Score Sheet / Judge Attendance |
