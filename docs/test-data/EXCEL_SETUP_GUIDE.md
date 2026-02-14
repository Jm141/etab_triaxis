# Excel Setup Guide - Complete Step-by-Step Instructions

This guide will help you set up an Excel file with all formulas to manually verify the tabulation system calculations.

---

## Step 1: Import Data

1. Open Excel
2. Go to **Data** → **Get Data** → **From File** → **From Text/CSV**
3. Select `COMPLETE_TEST_DATA.csv`
4. Click **Load**

---

## Step 2: Create Calculation Sheet

### Sheet Name: `CALCULATIONS`

### Column Headers (Row 1):
| A | B | C | D | E | F | G | H |
|---|---|---|---|---|---|---|---|
| Contestant | Judge | Round | Criteria | Raw_Score | Max_Score | Total_Raw | Calculated_Score |

### Formulas:

**Column E (Raw_Score):**
```
=VLOOKUP(A2&"|"&B2&"|"&C2&"|"&D2, COMPLETE_TEST_DATA!A:G, 5, FALSE)
```

**Column F (Max_Score):**
```
=VLOOKUP(A2&"|"&B2&"|"&C2&"|"&D2, COMPLETE_TEST_DATA!A:G, 6, FALSE)
```

**Column G (Total_Raw per Judge per Round):**
```
=SUMIFS(E:E, A:A, A2, B:B, B2, C:C, C2)
```

**Column H (Calculated_Score per Judge per Round):**
```
=ROUND((G2 / SUMIFS(F:F, C:C, C2)) * 100, 2)
```

---

## Step 3: Create Round Totals Sheet

### Sheet Name: `ROUND_TOTALS`

### Structure:

| Contestant | Round | Judge_1 | Judge_2 | Judge_3 | Judge_4 | Judge_5 | Round_Total | Round_Average |
|------------|-------|---------|---------|---------|---------|---------|-------------|---------------|

### Formulas:

**Judge Columns (C to G):**
```
=SUMIFS(CALCULATIONS!H:H, CALCULATIONS!A:A, $A2, CALCULATIONS!B:B, C$1, CALCULATIONS!C:C, $B2)
```

**Round Total (Column H):**
```
=SUM(C2:G2)
```

**Round Average (Column I):**
```
=AVERAGE(C2:G2)
```

---

## Step 4: Create Level Totals Sheet

### Sheet Name: `LEVEL_TOTALS`

### Structure:

| Contestant | Level | Round_1_Total | Round_2_Total | Level_Total | Level_Average |
|------------|-------|---------------|---------------|-------------|---------------|

### Formulas:

**Round Totals:**
```
=SUMIFS(ROUND_TOTALS!H:H, ROUND_TOTALS!A:A, $A2, ROUND_TOTALS!B:B, "R1")
```

**Level Total:**
```
=SUM(C2:D2)
```

**Level Average:**
```
=AVERAGE(C2:D2)
```

---

## Step 5: Create Final Results Sheet

### Sheet Name: `RESULTS`

### Structure:

| Contestant | Preliminary | Semi_Final | Final | Grand_Total | Rank |
|------------|-------------|------------|-------|-------------|------|

### Formulas:

**Preliminary Total:**
```
=SUMIFS(LEVEL_TOTALS!D:D, LEVEL_TOTALS!A:A, A2, LEVEL_TOTALS!B:B, "L1")
```

**Semi-Final Total:**
```
=SUMIFS(LEVEL_TOTALS!D:D, LEVEL_TOTALS!A:A, A2, LEVEL_TOTALS!B:B, "L2")
```

**Final Total:**
```
=SUMIFS(LEVEL_TOTALS!D:D, LEVEL_TOTALS!A:A, A2, LEVEL_TOTALS!B:B, "L3")
```

**Grand Total:**
```
=SUM(B2:D2)
```

**Rank (with tie handling):**
```
=IF(COUNTIF($E$2:$E$11, E2) > 1, 
    RANK(E2, $E$2:$E$11, 0) & " (Tie)", 
    RANK(E2, $E$2:$E$11, 0))
```

---

## Step 6: Create Winners Sheet

### Sheet Name: `WINNERS`

### Round Winners:

| Round | Winner | Total_Score |
|-------|--------|-------------|

**Formula for Winner:**
```
=INDEX(ROUND_TOTALS!A:A, MATCH(MAX(ROUND_TOTALS!H:H), ROUND_TOTALS!H:H, 0))
```

**Formula for Total Score:**
```
=MAX(ROUND_TOTALS!H:H)
```

### Level Winners:

| Level | Winner | Total_Score |
|-------|--------|-------------|

**Formula for Winner:**
```
=INDEX(LEVEL_TOTALS!A:A, MATCH(MAX(LEVEL_TOTALS!D:D), LEVEL_TOTALS!D:D, 0))
```

**Formula for Total Score:**
```
=MAX(LEVEL_TOTALS!D:D)
```

### Overall Winner:

| Position | Contestant | Total_Score |
|----------|------------|--------------|

**Formula for Contestant:**
```
=INDEX(RESULTS!A:A, MATCH(1, RESULTS!F:F, 0))
```

**Formula for Total Score:**
```
=MAX(RESULTS!E:E)
```

---

## Quick Formula Reference

### Calculate Individual Score:
```
=(Sum of Raw Scores / Sum of Max Scores) × 100
```

### Calculate Round Total:
```
=SUM(All Judge Scores for Round)
```

### Calculate Level Total:
```
=SUM(All Round Totals in Level)
```

### Calculate Final Total:
```
=SUM(All Level Totals)
```

### Ranking Formula:
```
=RANK(Score, Score_Range, 0)
```

---

## Verification Checklist

- [ ] Individual scores calculated: (Raw Sum / Max Sum) × 100
- [ ] Round totals = Sum of all judge scores
- [ ] Level totals = Sum of all round totals in level
- [ ] Final total = Sum of all level totals
- [ ] Rankings assigned correctly (highest = Rank 1)
- [ ] Ties handled correctly
- [ ] Round winners match system
- [ ] Level winners match system
- [ ] Overall winner matches system

---

## Expected Results

**Round Winners:**
- R1: C1
- R2: C1
- R3: C1
- R4: C1
- R5: C1
- R6: C1

**Level Winners:**
- Preliminary: C1
- Semi-Final: C1
- Final: C1

**Overall Winner:**
- 🏆 C1

---

**Use this Excel file to manually verify all calculations!**
