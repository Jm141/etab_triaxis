# Flexible Formula System Guide

The tabulation system supports **dynamic formulas** that allow you to combine scores from specific levels and rounds, making it flexible for any pageant scenario.

---

## 🎯 Key Features

✅ **Combine Specific Levels:** Select which levels to include in final calculation  
✅ **Combine Specific Rounds:** Select which rounds to include  
✅ **Weighted Combinations:** Apply different weights to different levels/rounds  
✅ **Custom Formulas:** Write your own formula expressions  
✅ **Pre-Pageant Support:** Add pre-pageant scores to final scores  

---

## 📋 Common Scenarios

### Scenario 1: Pre-Pageant + Final

**Use Case:** Pre-pageant scores are added to final scores.

**Setup:**
1. Create Pre-Pageant level with rounds
2. Create Final level with rounds
3. Configure formula:
   - Formula: `SUM(level_totals)`
   - Select Levels: ✅ Pre-Pageant, ✅ Final
   - Uncheck: Preliminary, Semi-Final

**Result:** Final Total = Pre-Pageant Total + Final Total

**Example:**
- Pre-Pageant: 900.00
- Final: 914.28
- **Winner Total: 1,814.28**

---

### Scenario 2: Weighted Pre-Pageant + Final

**Use Case:** Pre-pageant has 30% weight, Final has 70% weight.

**Setup:**
1. Formula: `(level_totals[0] * 0.3) + (level_totals[3] * 0.7)`
2. Select Levels: Pre-Pageant (index 0), Final (index 3)

**Result:** (Pre-Pageant × 30%) + (Final × 70%)

**Example:**
- Pre-Pageant: 900.00 × 0.3 = 270.00
- Final: 914.28 × 0.7 = 639.996
- **Winner Total: 909.996**

---

### Scenario 3: Combine Specific Rounds

**Use Case:** Only certain rounds count toward final.

**Setup:**
1. Formula: `round_totals[0] + round_totals[1] + round_totals[5] + round_totals[6]`
2. Select Rounds: Pre-Pageant Rounds 1-2, Final Rounds 5-6

**Result:** Sum of selected rounds only

---

### Scenario 4: Average of Selected Levels

**Use Case:** Average pre-pageant and final scores.

**Setup:**
1. Formula: `AVG(level_totals)`
2. Select Levels: Pre-Pageant, Final

**Result:** (Pre-Pageant Total + Final Total) ÷ 2

---

## 🔧 How to Configure

### Step 1: Access Formula Management

1. Login as **Event Technical Admin** or **Super Admin**
2. Go to Event → **Reports**
3. Click **"Manage Formulas"**

### Step 2: Create/Edit Formula

1. Click **"Add New Formula"** or edit existing
2. **Formula Type:** Select **"Final"**
3. **Formula Expression:** Enter your formula
4. **Select Levels:** Check levels to include
5. **Select Rounds:** Check rounds to include (optional)
6. **Description:** Add clear description
7. Click **"Save Formula"**

### Step 3: Verify

1. Generate **Finals Report**
2. Check that formula is displayed
3. Verify calculations match expected results

---

## 📊 Formula Variables

### Available Variables:

- `round_totals` - Array of total scores per round
- `round_averages` - Array of average scores per round
- `level_totals` - Array of total scores per level
- `round_scores` - Array of all judge scores

### Array Access:

- `round_totals[0]` - First round total
- `level_totals[0]` - First level total
- `round_totals[1]` - Second round total

**Note:** Array indices start at 0. Check level/round order to determine correct index.

---

## 🧮 Formula Functions

### Basic Functions:

- `SUM(array)` - Sum all values
- `AVG(array)` - Average of values
- `COUNT(array)` - Count of items
- `MAX(array)` - Maximum value
- `MIN(array)` - Minimum value

### Examples:

```
SUM(level_totals)                    // Sum all selected levels
AVG(level_totals)                    // Average of selected levels
(level_totals[0] * 0.3) + (level_totals[1] * 0.7)  // Weighted combination
round_totals[0] + round_totals[1]   // Sum specific rounds
```

---

## ✅ Verification Checklist

- [ ] Formula saved successfully
- [ ] Correct levels/rounds selected
- [ ] Formula expression is valid
- [ ] Finals report shows formula
- [ ] Calculations match expected results
- [ ] Winners are correct
- [ ] Rankings are accurate

---

## 💡 Tips

1. **Test First:** Create test event to verify formula before production
2. **Document:** Add clear description when saving formula
3. **Backup:** Keep default formula as backup
4. **Verify:** Always check Finals report after changing formula
5. **Level Order:** Remember level order determines array index

---

## 📖 Related Documents

- `docs/PRE_PAGEANT_SCENARIO.md` - Detailed pre-pageant guide
- `docs/SCORING_FORMULAS.md` - All available formulas
- `docs/HOW_WINNERS_ARE_CALCULATED.md` - Calculation explanation

---

**The system is flexible and can handle any combination of levels/rounds for final calculation!** 🎯
