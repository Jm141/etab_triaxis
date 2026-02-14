# How Winners Are Calculated - Complete Guide

This document explains the complete process of how the tabulation system calculates winners from raw scores to final rankings.

---

## 📋 Table of Contents

1. [Overview](#overview)
2. [Step 1: Judge Scoring](#step-1-judge-scoring)
3. [Step 2: Individual Score Calculation](#step-2-individual-score-calculation)
4. [Step 3: Round Totals](#step-3-round-totals)
5. [Step 4: Level Totals](#step-4-level-totals)
6. [Step 5: Final Ranking](#step-5-final-ranking)
7. [Default Formula](#default-formula)
8. [Complete Example](#complete-example)
9. [Tie-Breaking Rules](#tie-breaking-rules)

---

## Overview

The tabulation system uses a **weighted average formula** as the default calculation method:

```
Final Score = (Sum of All Raw Scores / Sum of All Max Scores) × 100
```

This formula automatically weights each criterion based on its maximum score, ensuring fair and proportional scoring.

---

## Step 1: Judge Scoring

### What Happens

1. **Judge enters raw scores** for each criterion
   - Each criterion has a maximum score (e.g., 10, 15, 50, 100)
   - Judge enters a score between 0 and the maximum

2. **System stores raw scores** in `score_details` table
   - Each criterion score is stored separately
   - Raw scores are preserved exactly as entered

### Example

**Round:** Talent Competition  
**Criteria:**
- Performance Quality: Max = 100
- Originality: Max = 50
- Stage Presence: Max = 50

**Judge 1 scores Contestant A:**
- Performance Quality: **90** (out of 100)
- Originality: **45** (out of 50)
- Stage Presence: **48** (out of 50)

**Raw Scores Stored:**
```
Performance Quality: 90
Originality: 45
Stage Presence: 48
Total Raw: 183
```

---

## Step 2: Individual Score Calculation

### Default Formula (Weighted Average)

For each judge's score entry, the system calculates:

```
Total Score = (Sum of Raw Scores / Sum of Max Scores) × 100
```

### How It Works

1. **Sum all raw scores** from the judge
2. **Sum all max scores** for the criteria
3. **Divide** raw total by max total
4. **Multiply by 100** to get percentage score

### Example Calculation

**From Step 1:**
- Raw Scores: 90 + 45 + 48 = **183**
- Max Scores: 100 + 50 + 50 = **200**

**Calculation:**
```
Total Score = (183 / 200) × 100 = 91.50 points
```

**Result:** Judge 1 gives Contestant A a score of **91.50** (out of 100).

### Why This Formula?

This formula automatically weights each criterion:
- Performance Quality (100 max) = 50% weight
- Originality (50 max) = 25% weight
- Stage Presence (50 max) = 25% weight

**No manual weight configuration needed!** The system calculates weights automatically based on max scores.

---

## Step 3: Round Totals

### What Happens

After all judges submit scores for a round:

1. **For each contestant**, sum all judge scores
2. **Calculate average** per judge (optional, for display)
3. **Total** = Sum of all judge scores

### Example

**Round:** Talent Competition  
**Contestant A** receives scores from 5 judges:

| Judge | Score |
|-------|-------|
| Judge 1 | 91.50 |
| Judge 2 | 89.25 |
| Judge 3 | 92.00 |
| Judge 4 | 88.75 |
| Judge 5 | 90.00 |

**Calculation:**
```
Round Total = 91.50 + 89.25 + 92.00 + 88.75 + 90.00 = 451.50
Round Average = 451.50 ÷ 5 = 90.30
```

**Result:** Contestant A has a **Round Total of 451.50** and **Round Average of 90.30**.

---

## Step 4: Level Totals

### What Happens

A level may contain multiple rounds. To calculate level totals:

1. **Sum all round totals** for each contestant
2. **Calculate average per round** (optional, for display)
3. **Level Total** = Sum of all round totals in that level

### Example

**Level:** Preliminary Round  
**Rounds:** Talent Competition, Q&A Session, Evening Gown

**Contestant A's scores:**

| Round | Round Total |
|-------|-------------|
| Talent Competition | 451.50 |
| Q&A Session | 425.00 |
| Evening Gown | 465.25 |

**Calculation:**
```
Level Total = 451.50 + 425.00 + 465.25 = 1,341.75
Average per Round = 1,341.75 ÷ 3 = 447.25
```

**Result:** Contestant A has a **Level Total of 1,341.75** and **Average per Round of 447.25**.

---

## Step 5: Final Ranking

### What Happens

To determine final winners:

1. **Calculate final total** for each contestant
   - Sum all level totals, OR
   - Use custom formula (if configured)

2. **Sort contestants** by final total (descending)

3. **Assign ranks** using standard competition ranking:
   - Same scores get same rank
   - Next different score gets next rank number

### Default Final Calculation

**Default Formula:**
```
Final Total = Sum of All Round Totals
```

This means all rounds contribute equally to the final score.

### Example

**Event:** Beauty Pageant  
**Contestants:** A, B, C, D, E

**Final Totals:**

| Contestant | Final Total | Rank |
|------------|-------------|------|
| Contestant A | 1,341.75 | 1 |
| Contestant B | 1,325.50 | 2 |
| Contestant C | 1,325.50 | 2 (tie) |
| Contestant D | 1,310.25 | 4 |
| Contestant E | 1,298.00 | 5 |

**Note:** Contestants B and C have the same score (1,325.50), so they both get Rank 2. The next contestant (D) gets Rank 4 (not Rank 3).

---

## Default Formula

### Formula Expression

```
SUM(round_totals)
```

### Mathematical Formula

```
Final Score = Total₁ + Total₂ + ... + Totalₙ
```

Where:
- Total₁ = Sum of all judge scores in Round 1
- Total₂ = Sum of all judge scores in Round 2
- Totalₙ = Sum of all judge scores in Round n

### When to Use

- **Default:** All rounds have equal weight
- **Simple:** Easy to understand and verify
- **Fair:** No bias toward any specific round

### Custom Formulas

Event Technical Admins can configure custom formulas:
- **Weighted Average:** Different rounds have different weights
- **Average per Round:** Average of round totals
- **Tie-Breaker:** Use highest criterion score

See `docs/SCORING_FORMULAS.md` for all available formulas.

---

## Complete Example

### Scenario

**Event:** Regional Beauty Pageant  
**Levels:** Preliminary, Semi-Final, Final  
**Rounds per Level:** 2 rounds each  
**Judges:** 5 judges  
**Contestants:** 3 contestants (for simplicity)

### Step-by-Step Calculation

#### Round 1: Talent Competition (Preliminary)

**Contestant A:**
- Judge 1: 91.50
- Judge 2: 89.25
- Judge 3: 92.00
- Judge 4: 88.75
- Judge 5: 90.00
- **Round Total: 451.50**

**Contestant B:**
- Judge 1: 85.00
- Judge 2: 87.50
- Judge 3: 86.25
- Judge 4: 88.00
- Judge 5: 85.75
- **Round Total: 432.50**

**Contestant C:**
- Judge 1: 88.00
- Judge 2: 90.25
- Judge 3: 89.50
- Judge 4: 87.75
- Judge 5: 91.00
- **Round Total: 446.50**

#### Round 2: Q&A Session (Preliminary)

**Contestant A:**
- **Round Total: 425.00**

**Contestant B:**
- **Round Total: 410.00**

**Contestant C:**
- **Round Total: 435.00**

#### Level Total (Preliminary)

**Contestant A:**
- Round 1: 451.50
- Round 2: 425.00
- **Level Total: 876.50**

**Contestant B:**
- Round 1: 432.50
- Round 2: 410.00
- **Level Total: 842.50**

**Contestant C:**
- Round 1: 446.50
- Round 2: 435.00
- **Level Total: 881.50**

#### Final Ranking (All Levels Combined)

**Contestant C:**
- Preliminary: 881.50
- Semi-Final: 890.25
- Final: 895.00
- **Final Total: 2,666.75** → **Rank 1** 🏆

**Contestant A:**
- Preliminary: 876.50
- Semi-Final: 885.00
- Final: 880.50
- **Final Total: 2,642.00** → **Rank 2**

**Contestant B:**
- Preliminary: 842.50
- Semi-Final: 850.00
- Final: 845.25
- **Final Total: 2,537.75** → **Rank 3**

---

## Tie-Breaking Rules

### Standard Competition Ranking

When contestants have the same final score:

1. **Same rank assigned** to all tied contestants
2. **Next rank skipped** (e.g., if 2 contestants tie for Rank 2, next contestant gets Rank 4)
3. **Rank continues** from the next available number

### Example

**Final Totals:**
- Contestant A: 1,500.00 → Rank 1
- Contestant B: 1,475.50 → Rank 2
- Contestant C: 1,475.50 → Rank 2 (tie)
- Contestant D: 1,450.00 → Rank 4 (not Rank 3)
- Contestant E: 1,425.00 → Rank 5

### Tie-Breaker Formula (Optional)

If a custom tie-breaker formula is configured:
- Uses highest criterion score (e.g., Q&A round)
- Or uses specific round score
- Or uses judge chairperson score

See `docs/SCORING_FORMULAS.md` for tie-breaker formulas.

---

## Summary

### The Complete Flow

```
1. Judge enters raw scores
   ↓
2. System calculates: (Sum Raw / Sum Max) × 100
   ↓
3. Round Total = Sum of all judge scores
   ↓
4. Level Total = Sum of all round totals
   ↓
5. Final Total = Sum of all level totals (or custom formula)
   ↓
6. Rank contestants by Final Total (descending)
   ↓
7. Assign ranks (handle ties)
   ↓
8. Winners determined! 🏆
```

### Key Points

✅ **Default Formula:** `SUM(round_totals)` - Simple sum of all round totals  
✅ **Automatic Weighting:** Weights calculated from max scores  
✅ **Fair Ranking:** Standard competition ranking with tie handling  
✅ **Customizable:** Tech Admins can configure custom formulas  
✅ **Transparent:** All calculations visible in reports  

---

## Related Documents

- `docs/SCORING_FORMULAS.md` - All available formulas
- `docs/SCORING_GUIDE.md` - Detailed scoring guide
- `docs/REPORTS_GUIDE.md` - How to generate reports

---

**Last Updated:** 2024  
**Version:** 1.0.0
