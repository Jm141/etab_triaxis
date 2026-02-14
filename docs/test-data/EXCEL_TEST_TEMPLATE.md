# Excel Test Data Template for Tabulation System

This guide provides a complete Excel template with formulas to manually test and verify the tabulation system calculations.

---

## Setup Overview

- **Contestants:** 10 (C1 to C10)
- **Judges:** 5 (J1 to J5)
- **Levels:** 3 (Preliminary, Semi-Final, Final)
- **Rounds:** 6 total
  - Preliminary: Round 1 (Talent), Round 2 (Q&A)
  - Semi-Final: Round 3 (Evening Gown), Round 4 (Swimsuit) - **ELIMINATION ROUND**
  - Final: Round 5 (Final Q&A), Round 6 (Final Walk)
- **Criteria per Round:** 3 criteria each
- **Max Scores:** 10, 15, 10 (low values for easy testing)

---

## Excel Sheet Structure

### Sheet 1: SETUP
Contains all configuration data.

### Sheet 2: RAW_SCORES
Contains all raw scores from judges.

### Sheet 3: CALCULATIONS
Contains all formulas for calculations.

### Sheet 4: RESULTS
Contains final rankings and winners.

---

## Sheet 1: SETUP

### A1: Event Configuration

| Column A | Column B |
|----------|----------|
| Event Name | Test Beauty Pageant |
| Number of Contestants | 10 |
| Number of Judges | 5 |
| Default Formula | SUM(round_totals) |

### A5: Levels Setup

| Level ID | Level Name | Order | Type |
|----------|------------|-------|------|
| L1 | Preliminary | 1 | Normal |
| L2 | Semi-Final | 2 | Elimination |
| L3 | Final | 3 | Normal |

### A10: Rounds Setup

| Round ID | Round Name | Level ID | Order | Criteria Count |
|----------|------------|----------|-------|----------------|
| R1 | Talent Competition | L1 | 1 | 3 |
| R2 | Q&A Session | L1 | 2 | 3 |
| R3 | Evening Gown | L2 | 1 | 3 |
| R4 | Swimsuit | L2 | 2 | 3 |
| R5 | Final Q&A | L3 | 1 | 3 |
| R6 | Final Walk | L3 | 2 | 3 |

### A20: Criteria Setup

| Criteria ID | Criteria Name | Round ID | Max Score |
|-------------|----------------|----------|-----------|
| C1 | Performance Quality | R1 | 10 |
| C2 | Originality | R1 | 15 |
| C3 | Stage Presence | R1 | 10 |
| C4 | Communication | R2 | 10 |
| C5 | Intelligence | R2 | 15 |
| C6 | Poise | R2 | 10 |
| C7 | Elegance | R3 | 10 |
| C8 | Confidence | R3 | 15 |
| C9 | Presentation | R3 | 10 |
| C10 | Fitness | R4 | 10 |
| C11 | Confidence | R4 | 15 |
| C12 | Poise | R4 | 10 |
| C13 | Communication | R5 | 10 |
| C14 | Intelligence | R5 | 15 |
| C15 | Overall Impact | R5 | 10 |
| C16 | Final Walk | R6 | 10 |
| C17 | Stage Presence | R6 | 15 |
| C18 | Overall Impact | R6 | 10 |

---

## Sheet 2: RAW_SCORES

### Structure:
- Row 1: Headers
- Row 2-11: Contestants (C1 to C10)
- Columns: Judge, Round, Criteria, Raw Score

### Sample Data (First 20 rows shown):

| Contestant | Judge | Round | Criteria | Raw Score |
|------------|-------|-------|----------|-----------|
| C1 | J1 | R1 | C1 | 9 |
| C1 | J1 | R1 | C2 | 14 |
| C1 | J1 | R1 | C3 | 9 |
| C1 | J1 | R2 | C4 | 8 |
| C1 | J1 | R2 | C5 | 13 |
| C1 | J1 | R2 | C6 | 9 |
| C1 | J1 | R3 | C7 | 9 |
| C1 | J1 | R3 | C8 | 14 |
| C1 | J1 | R3 | C9 | 9 |
| C1 | J1 | R4 | C10 | 8 |
| C1 | J1 | R4 | C11 | 13 |
| C1 | J1 | R4 | C12 | 9 |
| C1 | J1 | R5 | C13 | 9 |
| C1 | J1 | R5 | C14 | 14 |
| C1 | J1 | R5 | C15 | 9 |
| C1 | J1 | R6 | C16 | 9 |
| C1 | J1 | R6 | C17 | 14 |
| C1 | J1 | R6 | C18 | 9 |

### Complete Test Data (All 10 Contestants, 5 Judges, 6 Rounds):

**Contestant C1 (High Performer):**
- All judges give scores: 8-9 for 10-point criteria, 13-14 for 15-point criteria
- Total expected: High ranking

**Contestant C2 (Average Performer):**
- All judges give scores: 6-7 for 10-point criteria, 10-11 for 15-point criteria
- Total expected: Middle ranking

**Contestant C3 (Low Performer):**
- All judges give scores: 4-5 for 10-point criteria, 7-8 for 15-point criteria
- Total expected: Low ranking

**Contestants C4-C10:**
- Varied scores to create realistic distribution

---

## Sheet 3: CALCULATIONS

### Section A: Individual Judge Scores (per Contestant per Round)

**Formula for Total Score per Judge per Round:**
```
=SUMIFS(RAW_SCORES!E:E, RAW_SCORES!B:B, Judge, RAW_SCORES!C:C, Round, RAW_SCORES!A:A, Contestant)
```

**Formula for Max Score per Round:**
```
=SUMIFS(SETUP!D:D, SETUP!C:C, Round)
```

**Formula for Percentage Score:**
```
=(Total_Raw_Score / Total_Max_Score) * 100
```

### Section B: Round Totals (Sum of All Judges)

**Formula for Round Total:**
```
=SUM(All_Judge_Scores_for_Round)
```

**Formula for Round Average:**
```
=AVERAGE(All_Judge_Scores_for_Round)
```

### Section C: Level Totals

**Formula for Level Total:**
```
=SUM(All_Round_Totals_in_Level)
```

**Formula for Level Average:**
```
=AVERAGE(All_Round_Totals_in_Level)
```

### Section D: Final Total

**Formula for Final Total (Default):**
```
=SUM(All_Level_Totals)
```

**Alternative Formula (Weighted Average):**
```
=SUM(All_Round_Totals) / COUNT(All_Round_Totals)
```

---

## Sheet 4: RESULTS

### Section A: Round Winners

**Formula to find Round Winner:**
```
=INDEX(Contestants, MATCH(MAX(Round_Totals), Round_Totals, 0))
```

**Formula for Round Ranking:**
```
=RANK(Round_Total, Round_Totals, 0)
```

### Section B: Level Winners

**Formula to find Level Winner:**
```
=INDEX(Contestants, MATCH(MAX(Level_Totals), Level_Totals, 0))
```

**Formula for Level Ranking:**
```
=RANK(Level_Total, Level_Totals, 0)
```

### Section C: Final Winners

**Formula to find Final Winner:**
```
=INDEX(Contestants, MATCH(MAX(Final_Totals), Final_Totals, 0))
```

**Formula for Final Ranking (with tie handling):**
```
=IF(COUNTIF($Final_Totals, Final_Total) > 1, 
    RANK(Final_Total, $Final_Totals, 0) & " (Tie)", 
    RANK(Final_Total, $Final_Totals, 0))
```

---

## Complete Test Data Values

### Contestant C1 (Expected Winner)

**Round 1 (Talent):**
- J1: C1=9, C2=14, C3=9 → Total Raw: 32, Max: 35 → Score: 91.43
- J2: C1=9, C2=14, C3=9 → Score: 91.43
- J3: C1=9, C2=15, C3=9 → Score: 94.29
- J4: C1=8, C2=14, C3=9 → Score: 88.57
- J5: C1=9, C2=14, C3=9 → Score: 91.43
- **Round Total: 457.15**

**Round 2 (Q&A):**
- J1: C4=8, C5=13, C6=9 → Total Raw: 30, Max: 35 → Score: 85.71
- J2: C4=9, C5=14, C6=9 → Score: 91.43
- J3: C4=9, C5=13, C6=9 → Score: 88.57
- J4: C4=8, C5=13, C6=9 → Score: 85.71
- J5: C4=9, C5=14, C6=9 → Score: 91.43
- **Round Total: 442.85**

**Round 3 (Evening Gown):**
- J1: C7=9, C8=14, C9=9 → Score: 91.43
- J2: C7=9, C8=15, C9=9 → Score: 94.29
- J3: C7=9, C8=14, C9=9 → Score: 91.43
- J4: C7=8, C8=14, C9=9 → Score: 88.57
- J5: C7=9, C8=14, C9=9 → Score: 91.43
- **Round Total: 457.15**

**Round 4 (Swimsuit) - ELIMINATION:**
- J1: C10=8, C11=13, C12=9 → Score: 85.71
- J2: C10=9, C11=14, C12=9 → Score: 91.43
- J3: C10=9, C11=13, C12=9 → Score: 88.57
- J4: C10=8, C11=13, C12=9 → Score: 85.71
- J5: C10=9, C11=14, C12=9 → Score: 91.43
- **Round Total: 442.85**

**Round 5 (Final Q&A):**
- J1: C13=9, C14=14, C15=9 → Score: 91.43
- J2: C13=9, C14=15, C15=9 → Score: 94.29
- J3: C13=9, C14=14, C15=9 → Score: 91.43
- J4: C13=8, C14=14, C15=9 → Score: 88.57
- J5: C13=9, C14=14, C15=9 → Score: 91.43
- **Round Total: 457.15**

**Round 6 (Final Walk):**
- J1: C16=9, C17=14, C18=9 → Score: 91.43
- J2: C16=9, C17=15, C18=9 → Score: 94.29
- J3: C16=9, C17=14, C18=9 → Score: 91.43
- J4: C16=8, C17=14, C18=9 → Score: 88.57
- J5: C16=9, C17=14, C18=9 → Score: 91.43
- **Round Total: 457.15**

**Final Total:**
- Preliminary (R1+R2): 457.15 + 442.85 = 900.00
- Semi-Final (R3+R4): 457.15 + 442.85 = 900.00
- Final (R5+R6): 457.15 + 457.15 = 914.30
- **Grand Total: 2,714.30** → **Rank 1** 🏆

---

### Contestant C2 (Average Performer)

**Round 1:**
- All judges: C1=6, C2=10, C3=6 → Score: 62.86 each
- **Round Total: 314.30**

**Round 2:**
- All judges: C4=6, C5=10, C6=6 → Score: 62.86 each
- **Round Total: 314.30**

**Round 3:**
- All judges: C7=6, C8=10, C9=6 → Score: 62.86 each
- **Round Total: 314.30**

**Round 4:**
- All judges: C10=6, C11=10, C12=6 → Score: 62.86 each
- **Round Total: 314.30**

**Round 5:**
- All judges: C13=6, C14=10, C15=6 → Score: 62.86 each
- **Round Total: 314.30**

**Round 6:**
- All judges: C16=6, C17=10, C18=6 → Score: 62.86 each
- **Round Total: 314.30**

**Final Total:**
- Preliminary: 314.30 + 314.30 = 628.60
- Semi-Final: 314.30 + 314.30 = 628.60
- Final: 314.30 + 314.30 = 628.60
- **Grand Total: 1,885.80** → **Rank 5**

---

### Contestant C3 (Low Performer)

**Round 1:**
- All judges: C1=4, C2=7, C3=4 → Score: 42.86 each
- **Round Total: 214.30**

**Round 2:**
- All judges: C4=4, C5=7, C6=4 → Score: 42.86 each
- **Round Total: 214.30**

**Round 3:**
- All judges: C7=4, C8=7, C9=4 → Score: 42.86 each
- **Round Total: 214.30**

**Round 4:**
- All judges: C10=4, C11=7, C12=4 → Score: 42.86 each
- **Round Total: 214.30**

**Round 5:**
- All judges: C13=4, C14=7, C15=4 → Score: 42.86 each
- **Round Total: 214.30**

**Round 6:**
- All judges: C16=4, C17=7, C18=4 → Score: 42.86 each
- **Round Total: 214.30**

**Final Total:**
- Preliminary: 214.30 + 214.30 = 428.60
- Semi-Final: 214.30 + 214.30 = 428.60
- Final: 214.30 + 214.30 = 428.60
- **Grand Total: 1,285.80** → **Rank 10**

---

## Excel Formulas Reference

### Calculate Individual Score (per Judge per Round)

**Cell B2 (Judge Score):**
```
=ROUND((SUMIFS(RAW_SCORES!E:E, RAW_SCORES!A:A, A2, RAW_SCORES!B:B, B2, RAW_SCORES!C:C, C2) / 
        SUMIFS(SETUP!D:D, SETUP!C:C, C2)) * 100, 2)
```

### Calculate Round Total (Sum of All Judges)

**Cell D2 (Round Total):**
```
=SUMIFS(CALCULATIONS!B:B, CALCULATIONS!C:C, C2, CALCULATIONS!A:A, A2)
```

### Calculate Level Total

**Cell E2 (Level Total):**
```
=SUMIFS(CALCULATIONS!D:D, CALCULATIONS!F:F, F2, CALCULATIONS!A:A, A2)
```

### Calculate Final Total

**Cell G2 (Final Total):**
```
=SUMIFS(CALCULATIONS!E:E, CALCULATIONS!A:A, A2)
```

### Ranking Formula (with Tie Handling)

**Cell H2 (Rank):**
```
=IF(COUNTIF($G$2:$G$11, G2) > 1, 
    RANK(G2, $G$2:$G$11, 0) & " (Tie)", 
    RANK(G2, $G$2:$G$11, 0))
```

---

## Verification Checklist

- [ ] Individual scores calculated correctly: (Raw Sum / Max Sum) × 100
- [ ] Round totals = Sum of all judge scores for that round
- [ ] Level totals = Sum of all round totals in that level
- [ ] Final total = Sum of all level totals
- [ ] Rankings assigned correctly (highest score = Rank 1)
- [ ] Ties handled correctly (same score = same rank)
- [ ] Elimination round (Semi-Final) scores reset to zero for eliminated contestants
- [ ] Winners per round match system output
- [ ] Winners per level match system output
- [ ] Overall final winner matches system output

---

## Notes

1. **Elimination Round:** In Semi-Final, contestants who don't advance have scores reset to 0 for subsequent rounds.

2. **Formula:** Default formula is `SUM(round_totals)` - simple sum of all round totals.

3. **Tie-Breaking:** Standard competition ranking - same scores get same rank, next rank skips positions.

4. **Precision:** All calculations rounded to 2 decimal places for display, but system uses 3 decimal places internally.

---

## Expected Results Summary

**Round Winners:**
- Round 1: C1 (457.15)
- Round 2: C1 (442.85)
- Round 3: C1 (457.15)
- Round 4: C1 (442.85)
- Round 5: C1 (457.15)
- Round 6: C1 (457.15)

**Level Winners:**
- Preliminary: C1 (900.00)
- Semi-Final: C1 (900.00)
- Final: C1 (914.30)

**Overall Winner:**
- 🏆 C1 with Final Total: 2,714.30

---

**Use this Excel template to manually verify all calculations match the system output!**
