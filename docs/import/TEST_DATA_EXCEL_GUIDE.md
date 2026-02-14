# Excel Test Data Guide for Manual Verification

This guide provides Excel formulas and test data to manually verify the tabulation system calculations.

---

## Excel File Structure

Create an Excel file with the following sheets:
1. **Setup** - Event configuration
2. **Levels** - Level information
3. **Rounds** - Round information with weights
4. **Criteria** - Criteria with max scores
5. **Scores** - All judge scores
6. **Round Calculations** - Per-round totals and rankings
7. **Level Calculations** - Per-level totals and rankings
8. **Final Rankings** - Overall winners

---

## Sheet 1: Setup

| Field | Value |
|-------|-------|
| Event Name | Test Beauty Pageant |
| Number of Contestants | 10 |
| Number of Judges | 5 |
| Number of Levels | 3 |
| Number of Rounds | 6 |

---

## Sheet 2: Levels

| Level ID | Level Name | Order | Type | Notes |
|----------|------------|-------|------|-------|
| 1 | Preliminary | 1 | Normal | All 10 contestants |
| 2 | Semi-Final | 2 | Elimination | Top 6 advance |
| 3 | Final | 3 | Normal | Top 3 compete |

---

## Sheet 3: Rounds

| Round ID | Round Name | Level ID | Order | Weight % | Notes |
|----------|------------|----------|-------|----------|-------|
| 1 | Talent Competition | 1 | 1 | 30% | Preliminary |
| 2 | Q&A Session | 1 | 2 | 20% | Preliminary |
| 3 | Evening Gown | 1 | 3 | 50% | Preliminary |
| 4 | Talent Competition | 2 | 1 | 40% | Semi-Final |
| 5 | Q&A Session | 2 | 2 | 60% | Semi-Final |
| 6 | Final Q&A | 3 | 1 | 100% | Final |

---

## Sheet 4: Criteria

| Criteria ID | Criteria Name | Max Score | Round ID | Notes |
|-------------|---------------|-----------|----------|-------|
| 1 | Performance Quality | 10 | 1 | Talent Round |
| 2 | Originality | 10 | 1 | Talent Round |
| 3 | Stage Presence | 10 | 1 | Talent Round |
| 4 | Answer Quality | 15 | 2 | Q&A Round |
| 5 | Communication | 10 | 2 | Q&A Round |
| 6 | Elegance | 15 | 3 | Evening Gown |
| 7 | Poise | 10 | 3 | Evening Gown |
| 8 | Performance Quality | 10 | 4 | Semi-Final Talent |
| 9 | Originality | 10 | 4 | Semi-Final Talent |
| 10 | Answer Quality | 15 | 5 | Semi-Final Q&A |
| 11 | Communication | 10 | 5 | Semi-Final Q&A |
| 12 | Final Answer | 20 | 6 | Final Q&A |

---

## Sheet 5: Scores (All Judge Scores)

**Format:** Judge scores for each contestant in each round

### Round 1: Talent Competition (Preliminary)
**Criteria:** Performance Quality (10), Originality (10), Stage Presence (10)

| Contestant | Judge 1 | Judge 2 | Judge 3 | Judge 4 | Judge 5 |
|------------|---------|---------|---------|---------|---------|
| C1 | 8,7,9 | 9,8,8 | 8,9,9 | 9,7,8 | 8,8,9 |
| C2 | 7,6,8 | 8,7,7 | 7,8,8 | 8,6,7 | 7,7,8 |
| C3 | 9,8,9 | 9,9,9 | 9,8,10 | 9,9,9 | 9,9,9 |
| C4 | 6,5,7 | 7,6,6 | 6,7,7 | 7,5,6 | 6,6,7 |
| C5 | 8,9,8 | 9,9,8 | 8,10,9 | 9,9,8 | 8,9,9 |
| C6 | 7,8,7 | 8,8,7 | 7,9,8 | 8,8,7 | 7,8,8 |
| C7 | 9,9,10 | 10,9,9 | 9,10,10 | 10,9,9 | 9,10,10 |
| C8 | 5,6,5 | 6,6,5 | 5,7,6 | 6,6,5 | 5,6,6 |
| C9 | 8,8,8 | 9,8,8 | 8,9,9 | 9,8,8 | 8,8,9 |
| C10 | 6,7,6 | 7,7,6 | 6,8,7 | 7,7,6 | 6,7,7 |

**Calculation for each judge:**
- Total Raw = Sum of 3 criteria scores
- Max Possible = 30 (10+10+10)
- Score % = (Total Raw / 30) × 100

---

## Excel Formulas

### For Round Score Calculation (Per Judge):

**Cell Formula (for Judge 1, Contestant 1, Round 1):**
```
=((SUM(criteria_scores)) / SUM(max_scores)) * 100
```

**Example:**
```
=((8+7+9) / (10+10+10)) * 100 = (24/30)*100 = 80.00
```

### For Round Total (Sum of All Judges):

**Formula:**
```
=SUM(Judge1_Score, Judge2_Score, Judge3_Score, Judge4_Score, Judge5_Score)
```

### For Round Average:

**Formula:**
```
=Round_Total / 5
```

### For Level Total:

**Formula:**
```
=SUM(Round1_Total, Round2_Total, Round3_Total)
```

### For Final Total (Default Formula):

**Formula:**
```
=SUM(Level1_Total, Level2_Total, Level3_Total)
```

### For Ranking:

**Formula (Standard Competition Ranking):**
```
=IF(COUNTIF($Final_Totals,">"&Current_Total)+1=COUNTIF($Final_Totals,Current_Total),
    COUNTIF($Final_Totals,">"&Current_Total)+1,
    COUNTIF($Final_Totals,">"&Current_Total)+1)
```

**Simpler Formula:**
```
=RANK.EQ(Final_Total, $Final_Totals_Range, 0)
```
(Note: This doesn't handle ties the same way - use the complex formula above for standard competition ranking)

---

## Complete Test Data

See the attached CSV files or Excel template for complete test data.
