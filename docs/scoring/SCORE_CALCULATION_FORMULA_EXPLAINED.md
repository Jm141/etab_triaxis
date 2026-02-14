# Score Calculation Formula - Complete Guide

## Overview

The tabulation system uses **two types of formulas**:

1. **Individual Score Calculation** (per judge, per contestant) - Always uses the default formula
2. **Final Ranking Calculation** (for reports/winners) - Can use custom formulas

---

## 1. Individual Score Calculation (Default Formula)

### Formula Used

**This is the formula used to calculate each judge's score for each contestant:**

```
Final Score = (Sum of All Raw Scores / Sum of All Max Scores) × 100
```

### How It Works

1. **Judge enters raw scores** for each criterion
   - Example: Criterion 1 (Max 10): Judge gives **8**
   - Example: Criterion 2 (Max 15): Judge gives **12**
   - Example: Criterion 3 (Max 5): Judge gives **4**

2. **System calculates totals:**
   - Total Raw Score = 8 + 12 + 4 = **24**
   - Total Max Score = 10 + 15 + 5 = **30**

3. **System calculates final score:**
   - Final Score = (24 / 30) × 100 = **80.000 points** (out of 100)

### Where This Formula Is Used

- **Location:** `core/ScoringEngine.php` → `calculateScore()` method
- **When:** Automatically calculated when:
  - Judge submits scores
  - Admin edits scores
  - Scores are recalculated
- **Cannot be changed:** This formula is hardcoded and always used for individual score calculations

### Auto-Weighting

The system automatically calculates weights based on each criterion's maximum score:

```
Weight = (Criterion Max Score / Total Max Score of All Criteria) × 100
```

**Example:**
- Criterion 1: Max 10, Weight = (10/30) × 100 = **33.33%**
- Criterion 2: Max 15, Weight = (15/30) × 100 = **50.00%**
- Criterion 3: Max 5, Weight = (5/30) × 100 = **16.67%**
- **Total = 100%** ✓

---

## 2. Final Ranking Calculation (Custom Formulas)

### Formula Management

**You can select and change formulas for calculating final rankings/winners.**

### How to Access Formula Management

1. Go to **Reports → Manage Formulas** (Event Technical Admin only)
2. View available standard formulas
3. Create or edit custom formulas
4. Select which rounds/levels to include

### Available Formula Types

#### Standard Formulas

1. **Average Score (Basic)**
   - Formula: `AVG(round_scores)`
   - Description: Average of all judge scores

2. **Weighted Average (Most Common)**
   - Formula: `(round_totals[0] * 0.4) + (round_totals[1] * 0.3) + (round_totals[2] * 0.3)`
   - Description: Each round has a percentage weight

3. **Sum All Rounds (Default)**
   - Formula: `SUM(round_totals)`
   - Description: Sum of all round totals (most common)

4. **Pre-Pageant + Final Combined**
   - Formula: `SUM(level_totals)`
   - Description: Combine scores from selected levels

### When Custom Formulas Are Used

- **Location:** `views/reports/formula.php` (Formula Management)
- **When:** Used when generating:
  - Final rankings
  - Level totals
  - Winner calculations
- **Can be changed:** Yes, via Formula Management interface

### Default Formula for Rankings

If no custom formula is set, the system uses:
```
Final Ranking = SUM(round_totals)
```

This sums all round totals for each contestant.

---

## Summary

### Individual Score Calculation (Per Judge, Per Contestant)

- **Formula:** `(Sum of Raw Scores / Sum of Max Scores) × 100`
- **Location:** `core/ScoringEngine.php`
- **Can be changed?** ❌ No - This is the default and always used
- **Used for:** Calculating each judge's score for each contestant

### Final Ranking Calculation (Winners/Reports)

- **Formula:** Customizable (default: `SUM(round_totals)`)
- **Location:** Formula Management (`/events/{id}/reports/formula`)
- **Can be changed?** ✅ Yes - Via Formula Management interface
- **Used for:** Calculating final rankings, winners, and reports

---

## Example Flow

1. **Judge scores contestant:**
   - Criterion 1 (Max 10): 8
   - Criterion 2 (Max 15): 12
   - Criterion 3 (Max 5): 4
   - **Individual Score = (24/30) × 100 = 80.000** ← Uses default formula

2. **System calculates round totals:**
   - All judges' scores for all contestants
   - **Round Total = Average of all judge scores** ← Uses default formula

3. **System calculates final rankings:**
   - **Final Ranking = SUM(round_totals)** ← Uses custom formula (if set) or default

---

## FAQ

### Q: Can I change the formula for individual score calculations?

**A:** No. The individual score calculation formula `(Sum of Raw Scores / Sum of Max Scores) × 100` is hardcoded and always used. This ensures consistency across all scores.

### Q: How do I change the formula for final rankings?

**A:** 
1. Go to **Reports → Manage Formulas**
2. Select a standard formula or create a custom one
3. Choose which rounds/levels to include
4. Save the formula

### Q: What happens if I don't set a custom formula?

**A:** The system uses the default formula: `SUM(round_totals)` - which sums all round totals for each contestant.

### Q: Can I use different formulas for different events?

**A:** Yes! Each event can have its own formula configuration. Formulas are stored per event in the `scoring_formula` table.

---

## Related Documentation

- `docs/scoring/SCORING_FORMULA.md` - Detailed formula explanation
- `docs/scoring/SCORING_FORMULAS.md` - List of all available formulas
- `docs/scoring/FLEXIBLE_FORMULAS_GUIDE.md` - How to use flexible formulas
- `docs/scoring/HOW_WINNERS_ARE_CALCULATED.md` - Complete winner calculation process
