# Tie Handling in Tabulation System

## How Ties Are Calculated

The system uses **Standard Competition Ranking** (also known as "1224" ranking) to handle ties.

---

## 📊 Ranking Rules

### 1. **Same Score = Same Rank**

When two or more contestants have the **exact same score** (within 0.001 tolerance), they receive the **same rank**.

**Example:**
```
Rank 1: Contestant A - 95.50
Rank 2: Contestant B - 92.30
Rank 2: Contestant C - 92.30  ← Tied for 2nd place
Rank 4: Contestant D - 90.10  ← Skips rank 3
Rank 5: Contestant E - 88.50
```

### 2. **Next Rank Skips Tied Positions**

After a tie, the next contestant receives a rank that **skips** the tied positions.

**Example:**
- If 2 contestants tie for Rank 2, the next contestant gets Rank 4 (not Rank 3)
- If 3 contestants tie for Rank 1, the next contestant gets Rank 4 (not Rank 2 or 3)

---

## 🎯 Tie Tolerance

The system considers scores as "tied" if they differ by **less than 0.001** (0.0001).

**Examples:**
- `95.500` and `95.501` = **NOT tied** (difference = 0.001)
- `95.500` and `95.499` = **Tied** (difference = 0.001)
- `92.30` and `92.30` = **Tied** (exact match)

---

## 🏆 Elimination with Ties

### Current Behavior

When a level has `advance_count` set (e.g., top 5 advance):

1. **System calculates rankings** using standard competition ranking
2. **Takes top N contestants** based on `advance_count`
3. **If there's a tie at the cutoff point**, the system includes **ALL tied contestants**

**Example Scenario:**
- **Semi Finals** has `advance_count = 5`
- **Rankings:**
  - Rank 1: Contestant A - 95.50
  - Rank 2: Contestant B - 92.30
  - Rank 3: Contestant C - 90.10
  - Rank 4: Contestant D - 88.50
  - Rank 5: Contestant E - 87.20
  - Rank 5: Contestant F - 87.20  ← Tied for 5th place
  - Rank 7: Contestant G - 85.00

**Result:** 
- ✅ Contestants A, B, C, D, E, and **F** all advance (6 contestants, not 5)
- ❌ Contestant G does NOT advance

### Why Include All Tied Contestants?

This is the **fair standard** in competitions:
- If multiple contestants have the **exact same score** at the cutoff point, they all deserve to advance
- It's unfair to eliminate someone who has the same score as someone who advances

---

## 📈 How Ties Are Calculated

### Step 1: Calculate Scores

For each contestant, the system calculates:
- **Round Total** = Sum of all judge scores in that round
- **Level Average** = Average of all round totals in the level
- **Final Total** = Sum of all level totals (or custom formula)

### Step 2: Sort by Score

Contestants are sorted by score in **descending order** (highest first).

### Step 3: Assign Ranks

```php
$rank = 1;
$prevScore = null;

foreach ($contestants as $index => $contestant) {
    // If score is different from previous, update rank
    if ($prevScore !== null && $contestant['score'] < $prevScore) {
        $rank = $index + 1;  // Rank equals position
    }
    
    // If score is same (within 0.001), keep same rank (tie)
    $contestant['rank'] = $rank;
    $prevScore = $contestant['score'];
}
```

### Step 4: Determine Advancement

For elimination levels:
```php
// Get all contestants with rank <= advance_count
// OR all contestants tied with the contestant at advance_count position
$cutoffScore = $rankings[$advance_count - 1]['score'];
$qualified = [];

foreach ($rankings as $ranking) {
    if ($ranking['score'] >= $cutoffScore) {
        $qualified[] = $ranking;  // Include all tied contestants
    }
}
```

---

## 🔍 Examples

### Example 1: Simple Tie

**Scores:**
- Contestant A: 95.50
- Contestant B: 92.30
- Contestant C: 92.30  ← Tie
- Contestant D: 90.10

**Rankings:**
- Rank 1: A (95.50)
- Rank 2: B (92.30)
- Rank 2: C (92.30)  ← Tied
- Rank 4: D (90.10)

**If advance_count = 3:**
- ✅ A, B, and C advance (3 contestants)

**If advance_count = 2:**
- ✅ A, B, and C advance (3 contestants - all tied at cutoff)

---

### Example 2: Tie at Cutoff Point

**Scores:**
- Contestant A: 95.50
- Contestant B: 92.30
- Contestant C: 90.10
- Contestant D: 88.50
- Contestant E: 87.20
- Contestant F: 87.20  ← Tie at 5th place
- Contestant G: 85.00

**Rankings:**
- Rank 1: A
- Rank 2: B
- Rank 3: C
- Rank 4: D
- Rank 5: E
- Rank 5: F  ← Tied
- Rank 7: G

**If advance_count = 5:**
- ✅ A, B, C, D, E, and **F** advance (6 contestants)
- ❌ G does NOT advance

---

### Example 3: Multiple Ties

**Scores:**
- Contestant A: 95.50
- Contestant B: 95.50  ← Tie for 1st
- Contestant C: 92.30
- Contestant D: 90.10
- Contestant E: 90.10  ← Tie for 4th
- Contestant F: 90.10  ← Tie for 4th
- Contestant G: 88.50

**Rankings:**
- Rank 1: A
- Rank 1: B  ← Tied
- Rank 3: C
- Rank 4: D
- Rank 4: E  ← Tied
- Rank 4: F  ← Tied
- Rank 7: G

**If advance_count = 4:**
- ✅ A, B, C, D, E, and **F** advance (6 contestants - all tied at cutoff)

---

## ⚠️ Important Notes

### 1. **Tolerance for Ties**

The system uses a **0.001 tolerance** to determine ties. This means:
- Scores that differ by less than 0.001 are considered tied
- This accounts for floating-point precision issues

### 2. **Advancement May Exceed advance_count**

If there's a tie at the cutoff point, **more contestants may advance** than the `advance_count` specifies.

**Example:**
- `advance_count = 5`
- But 6 contestants advance because 2 are tied for 5th place

This is **intentional and fair** - all tied contestants deserve to advance.

### 3. **No Tie-Breaker (Currently)**

The system does **NOT** use tie-breakers (e.g., highest score in a specific round). All tied contestants receive the same rank and all advance if at the cutoff.

### 4. **Display in Reports**

In reports, tied contestants will show:
- **Same rank number** (e.g., both show "Rank 2")
- **Same score** (if displayed)
- **Both advance** (if at cutoff point)

---

## 🔧 Technical Details

### Code Location

Tie handling is implemented in:
- `core/ScoringEngine.php` - `calculateRankings()` method (line 175-190)
- `core/ScoringEngine.php` - `calculateLevelRankings()` method (line 390-405)
- `controllers/ReportsController.php` - Various report methods (line 209-229, 620-644)

### Key Logic

```php
// Check if scores are tied (within 0.001)
if (abs($currentScore - $prevScore) <= 0.001) {
    // Same rank (tie)
    $rank = $prevRank;
} else {
    // Different rank
    $rank = $position;
}
```

---

## 📝 Summary

✅ **Ties are handled fairly** - same score = same rank  
✅ **All tied contestants advance** if at cutoff point  
✅ **Next rank skips** tied positions (standard competition ranking)  
✅ **0.001 tolerance** for floating-point precision  
✅ **No tie-breakers** - all tied contestants treated equally  

---

**Last Updated:** 2024  
**Version:** 1.0.0
