# Standard Pageant Scoring Formulas

This document lists all standard formulas available in the tabulation system for calculating winners.

---

## 1. Average Score (Basic Pageant Formula)

**Formula Expression:** `AVG(round_scores)`

**Mathematical Formula:**
```
Average Score = (Score₁ + Score₂ + Score₃ + ... + Scoreₙ) ÷ n
```

**Description:** Average of all judge scores across selected rounds.

**Example:**
- Scores: 90, 88, 92
- Calculation: (90 + 88 + 92) ÷ 3 = 90

**When to Use:** Basic pageants where all criteria have equal weight.

---

## 2. Weighted Average (Most Common in Pageants)

**Formula Expression:** `(round_totals[0] * 0.4) + (round_totals[1] * 0.3) + (round_totals[2] * 0.3)`

**Mathematical Formula:**
```
Weighted Average = (S₁ × W₁) + (S₂ × W₂) + ... + (Sₙ × Wₙ)
```
Where:
- S = score
- W = weight (in decimal)

**Description:** Each round/criterion has a percentage weight. Adjust weights as needed.

**Example:**
- Round 1 (Beauty) - 40% (0.40) → Score: 90
- Round 2 (Poise) - 30% (0.30) → Score: 85
- Round 3 (Intelligence) - 30% (0.30) → Score: 88

Calculation: (90 × 0.40) + (85 × 0.30) + (88 × 0.30) = 88.9

**When to Use:** Most common in professional pageants where different criteria have different importance.

---

## 3. Average per Judge (Multiple Judges)

**Formula Expression:** `AVG(round_averages)`

**Mathematical Formula:**
```
Final Score = Σ(Judge Scores) ÷ Number of Judges
```

**Description:** Average of round averages (already calculated per judge).

**Example:**
- Judges' scores: 89, 91, 90, 88, 92
- Calculation: (89 + 91 + 90 + 88 + 92) ÷ 5 = 90

**When to Use:** When you want the average across all judges for each round.

---

## 4. Final Pageant Score (Criteria + Judges) - Professional

**Formula Expression:** `SUM(round_totals) / COUNT(round_totals)`

**Mathematical Formula:**
```
Final Score = Σ(Judge Weighted Scores) ÷ Number of Judges
```

**Description:** Sum of all weighted scores divided by number of rounds. Most professional pageant formula. This combines weighted criteria scores across all judges.

**When to Use:** Professional pageants with weighted criteria and multiple judges.

---

## 5. Sum All Rounds (Simple Total)

**Formula Expression:** `SUM(round_totals)`

**Mathematical Formula:**
```
Total = Total₁ + Total₂ + ... + Totalₙ
```

**Description:** Simple sum of all round totals. Used when all rounds have equal weight.

**When to Use:** When all rounds contribute equally to the final score.

---

## 6. Tie-Breaker Formula

**Formula Expression:** `MAX(round_totals)` or `round_totals[0]` (for specific round)

**Mathematical Formula:**
```
Tie Score = Highest Criterion Score (e.g., Q&A)
```
or
```
Tie Score = Judge Chairperson Score
```

**Description:** Uses highest criterion score (e.g., Q&A round) or specific round score to break ties.

**Example:** If Q&A is the first round (index 0), use `round_totals[0]`

**When to Use:** When contestants have the same final score, use this to determine the winner.

---

## Available Variables

- `round_scores` - Array of all judge scores in selected rounds
- `round_totals` - Array of total scores per round (sum of judge scores)
- `round_averages` - Array of average scores per round (total ÷ judges)
- `level_totals` - Array of total scores per level

## Available Functions

- `SUM(array)` - Sum all values
- `AVG(array)` - Average of values
- `COUNT(array)` - Count of items
- `MAX(array)` - Maximum value
- `MIN(array)` - Minimum value
- `ROUND(value, decimals)` - Round to decimals

## Custom Formula Examples

### Weighted Average with 3 Rounds
```
(round_totals[0] * 0.4) + (round_totals[1] * 0.3) + (round_totals[2] * 0.3)
```

### Weighted Average with 4 Rounds
```
(round_totals[0] * 0.3) + (round_totals[1] * 0.25) + (round_totals[2] * 0.25) + (round_totals[3] * 0.2)
```

### Average of Top 2 Rounds
```
(round_totals[0] + round_totals[1]) / 2
```

### Pre-Pageant + Final Combined
```
SUM(level_totals)
```
**Setup:** Select only "Pre-Pageant" and "Final" levels. This adds pre-pageant scores to final scores.

**Example:**
- Pre-Pageant Total: 900.00
- Final Total: 914.28
- **Result: 1,814.28**

### Pre-Pageant Weighted (30%) + Final Weighted (70%)
```
(level_totals[0] * 0.3) + (level_totals[3] * 0.7)
```
**Setup:** Select only "Pre-Pageant" (Level 0) and "Final" (Level 3) levels.

**Example:**
- Pre-Pageant: 900.00 × 0.3 = 270.00
- Final: 914.28 × 0.7 = 639.996
- **Result: 909.996**

### Combine Specific Rounds
```
round_totals[0] + round_totals[1] + round_totals[5] + round_totals[6]
```
**Setup:** Select specific rounds (e.g., Pre-Pageant rounds 0-1, Final rounds 5-6).

### Phase 1 Top 5 (Interview 30% + Coronation 70%)
```
(round_averages[0] * 0.3) + (((round_averages[1] + round_averages[2] + round_averages[3] + round_averages[4]) / 4) * 0.7)
```
**Setup:** This is a **Level** formula. Order the rounds in the level like:
1) Closed-Door Interview  
2) Production Number  
3) Swimsuit Competition  
4) Casual Wear  
5) Evening Gown  

The formula multiplies Interview by 30% and the average of the 4 Coronation categories by 70%.

---

## Default Formula (System Default)

**The system automatically uses this as the default formula:**

**Formula Expression:** `SUM(round_totals)`

**Mathematical Formula:**
```
Final Score = Total₁ + Total₂ + ... + Totalₙ
```

**Description:** 
This is the **weighted average formula**: (Sum of all raw scores) ÷ (Number of scores)

**How it works:**
- Each round total = Sum of all judge scores for that round
- Final total = Sum of all round totals
- This ensures all rounds contribute equally to the final score

**When to Use:**
- **Default:** Automatically applied when no custom formula is set
- **Simple:** Easy to understand and verify
- **Fair:** No bias toward any specific round

---

## Recommended Formula for Flexible Tabulation System

For a flexible pageant tabulation system:

```
Final Score = Σ(Σ(Score_jc × Weight_c)) ÷ J
```

Where:
- J = number of judges
- C = number of criteria

**Formula Expression:** `SUM(round_totals) / COUNT(round_totals)`

This is equivalent to the **Final Pageant Score (Formula #4)** above, but the default uses `SUM(round_totals)` which is simpler and more commonly used.

---

## How to Use Formulas

1. Go to **Reports → Manage Formulas** (Tech Admin only)
2. Click on a formula in the accordion to see details
3. Click **"Use This Formula"** to auto-fill the form
4. Select which rounds/levels to include (optional)
5. Adjust weights or formula as needed
6. Save the formula

The system will use this formula when calculating final rankings.
