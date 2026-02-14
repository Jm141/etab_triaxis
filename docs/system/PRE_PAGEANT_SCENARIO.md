# Pre-Pageant + Final Score Scenario

This guide explains how to configure the system for scenarios where pre-pageant scores are added to final scores.

---

## 📋 Scenario Overview

**Use Case:** Pre-Pageant scores are added to Final scores to determine overall winners.

**Example:**
- Pre-Pageant Level: 2 rounds (Interview, Presentation)
- Final Level: 2 rounds (Final Q&A, Final Walk)
- **Formula:** Pre-Pageant Total + Final Total = Overall Winner

---

## 🎯 Setup Steps

### Step 1: Create Levels

Create levels in this order:

1. **Pre-Pageant** (Order 1)
2. **Preliminary** (Order 2) - Optional
3. **Semi-Final** (Order 3) - Optional, with elimination
4. **Final** (Order 4)

### Step 2: Create Rounds

**Pre-Pageant Level:**
- Round 1: Pre-Pageant Interview
- Round 2: Pre-Pageant Presentation

**Final Level:**
- Round 1: Final Q&A
- Round 2: Final Walk

### Step 3: Configure Formula

1. Go to **Reports → Manage Formulas** (Tech Admin only)
2. Click **"Add New Formula"** or edit existing
3. **Formula Type:** Select **"Final"**
4. **Formula Expression:** Enter one of these:

#### Option A: Simple Addition
```
SUM(level_totals)
```
Then select only **Pre-Pageant** and **Final** levels.

#### Option B: Weighted Combination
```
(level_totals[0] * 0.3) + (level_totals[3] * 0.7)
```
- Pre-Pageant (Level 0): 30% weight
- Final (Level 3): 70% weight

#### Option C: Specific Round Combination
```
round_totals[0] + round_totals[1] + round_totals[5] + round_totals[6]
```
- Pre-Pageant Round 1 + Round 2
- Final Round 5 + Round 6

### Step 4: Select Levels/Rounds

**For Level-Based Formula:**
- ✅ Check **Pre-Pageant** level
- ✅ Check **Final** level
- ❌ Uncheck other levels (Preliminary, Semi-Final)

**For Round-Based Formula:**
- ✅ Check all Pre-Pageant rounds
- ✅ Check all Final rounds
- ❌ Uncheck other rounds

### Step 5: Save and Test

1. Click **"Save Formula"**
2. Generate **Finals Report**
3. Verify calculation shows: Pre-Pageant Total + Final Total

---

## 📊 Example Calculation

### Contestant C1 Scores:

**Pre-Pageant Level:**
- Round 1 (Interview): 450.00
- Round 2 (Presentation): 450.00
- **Pre-Pageant Total: 900.00**

**Final Level:**
- Round 5 (Final Q&A): 457.14
- Round 6 (Final Walk): 457.14
- **Final Total: 914.28**

**Overall Winner Calculation:**
- Using `SUM(level_totals)` with Pre-Pageant + Final selected:
- **Final Total: 900.00 + 914.28 = 1,814.28** 🏆

---

## 🔧 Formula Options

### 1. Simple Addition (Pre-Pageant + Final)
```
Formula: SUM(level_totals)
Levels Selected: Pre-Pageant, Final
Result: Pre-Pageant Total + Final Total
```

### 2. Weighted Combination
```
Formula: (level_totals[0] * 0.3) + (level_totals[3] * 0.7)
Levels Selected: Pre-Pageant, Final
Result: (Pre-Pageant × 30%) + (Final × 70%)
```

### 3. Round-Specific Combination
```
Formula: round_totals[0] + round_totals[1] + round_totals[5] + round_totals[6]
Rounds Selected: Pre-Pageant Rounds 1-2, Final Rounds 5-6
Result: Sum of specific rounds
```

### 4. Average of Selected Levels
```
Formula: AVG(level_totals)
Levels Selected: Pre-Pageant, Final
Result: (Pre-Pageant Total + Final Total) ÷ 2
```

---

## ✅ Verification

### Checklist:
- [ ] Pre-Pageant level created
- [ ] Pre-Pageant rounds created and scored
- [ ] Final level created
- [ ] Final rounds created and scored
- [ ] Formula configured with Pre-Pageant + Final selected
- [ ] Formula saved and active
- [ ] Finals report shows correct calculation
- [ ] Winners match expected results

### Expected Results:
- Pre-Pageant scores are included in final calculation
- Only selected levels/rounds are used
- Other levels (Preliminary, Semi-Final) are excluded
- Final ranking reflects Pre-Pageant + Final totals

---

## 🎓 Real-World Example

**Miss Universe Style:**
- **Pre-Pageant:** Interview, Presentation (scores carry forward)
- **Preliminary:** Talent, Q&A (for elimination)
- **Semi-Final:** Evening Gown, Swimsuit (top 10 advance)
- **Final:** Final Q&A, Final Walk (top 5 compete)

**Formula:** Pre-Pageant (30%) + Final (70%)
```
(level_totals[0] * 0.3) + (level_totals[3] * 0.7)
```

**Result:** Contestants with highest combined Pre-Pageant + Final scores win.

---

## 💡 Tips

1. **Test First:** Create test event with sample data to verify formula
2. **Document Formula:** Add clear description when saving formula
3. **Verify Reports:** Check Finals report shows correct totals
4. **Backup Default:** Keep default formula as backup
5. **Level Order:** Ensure levels are in correct order (Pre-Pageant before Final)

---

**The system is flexible and can handle any combination of levels/rounds for final calculation!** 🎯
