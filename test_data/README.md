# Test Data for Tabulation System Verification

This directory contains all the files needed to manually test and verify the tabulation system calculations using Excel.

---

## 📁 Files Included

### 1. `COMPLETE_TEST_DATA.csv`
- **Complete test data** with all scores
- 10 Contestants (C1 to C10)
- 5 Judges (J1 to J5)
- 6 Rounds (R1 to R6)
- 3 Criteria per round
- Max scores: 10, 15, 10 (low values for easy testing)
- **Total: 900 score entries**

### 2. `EXCEL_TEST_TEMPLATE.md`
- **Complete Excel template structure**
- All formulas explained
- Sample calculations
- Expected results

### 3. `EXCEL_SETUP_GUIDE.md`
- **Step-by-step Excel setup instructions**
- Column-by-column formula guide
- Verification checklist

### 4. `generate_excel_test_data.php`
- **PHP script** to regenerate test data
- Run: `php generate_excel_test_data.php`
- Generates fresh CSV with randomized variations

---

## 🚀 Quick Start

### Option 1: Use Pre-Generated Data

1. Open `COMPLETE_TEST_DATA.csv` in Excel
2. Follow `EXCEL_SETUP_GUIDE.md` to set up formulas
3. Compare results with system output

### Option 2: Generate Fresh Data

1. Run: `php generate_excel_test_data.php`
2. This creates a new `COMPLETE_TEST_DATA.csv`
3. Follow `EXCEL_SETUP_GUIDE.md` to set up formulas

---

## 📊 Test Data Structure

### Levels:
- **L1: Preliminary** (Rounds R1, R2)
- **L2: Semi-Final** (Rounds R3, R4) - **ELIMINATION ROUND**
- **L3: Final** (Rounds R5, R6)

### Rounds:
- **R1:** Talent Competition
- **R2:** Q&A Session
- **R3:** Evening Gown
- **R4:** Swimsuit (Elimination)
- **R5:** Final Q&A
- **R6:** Final Walk

### Criteria:
- Each round has 3 criteria
- Max scores: 10, 15, 10 (pattern repeats)
- Total: 18 criteria

### Contestants:
- **C1:** High performer (expected winner)
- **C2-C10:** Varied performance levels

---

## 🧮 Calculation Formula

### Default Formula:
```
Final Score = SUM(round_totals)
```

### Individual Score Calculation:
```
Score = (Sum of Raw Scores / Sum of Max Scores) × 100
```

### Example:
- Raw Scores: 9 + 15 + 8 = 32
- Max Scores: 10 + 15 + 10 = 35
- **Score = (32 / 35) × 100 = 91.43**

---

## ✅ Verification Steps

1. **Import Data**
   - Open `COMPLETE_TEST_DATA.csv` in Excel

2. **Set Up Calculations**
   - Follow `EXCEL_SETUP_GUIDE.md`
   - Create calculation sheets with formulas

3. **Calculate Results**
   - Round totals
   - Level totals
   - Final totals
   - Rankings

4. **Compare with System**
   - Generate reports in the system
   - Compare Excel results with system output
   - Verify all calculations match

---

## 📋 Expected Results

### Round Winners:
- All rounds: **C1** (highest performer)

### Level Winners:
- Preliminary: **C1**
- Semi-Final: **C1**
- Final: **C1**

### Overall Winner:
- 🏆 **C1** with highest grand total

---

## 🔍 What to Verify

- [ ] Individual scores calculated correctly
- [ ] Round totals = Sum of judge scores
- [ ] Level totals = Sum of round totals
- [ ] Final total = Sum of level totals
- [ ] Rankings assigned correctly
- [ ] Ties handled properly
- [ ] Round winners match system
- [ ] Level winners match system
- [ ] Overall winner matches system

---

## 📖 Documentation

- `EXCEL_TEST_TEMPLATE.md` - Complete template structure
- `EXCEL_SETUP_GUIDE.md` - Step-by-step setup
- `../docs/HOW_WINNERS_ARE_CALCULATED.md` - Calculation explanation
- `../docs/SCORING_FORMULAS.md` - Available formulas

---

## 💡 Tips

1. **Use Excel's Data Validation** to ensure scores are within max limits
2. **Use Conditional Formatting** to highlight winners
3. **Create Pivot Tables** for quick analysis
4. **Save multiple versions** as you test different scenarios
5. **Document any discrepancies** between Excel and system

---

## 🐛 Troubleshooting

### Formula Errors:
- Check cell references
- Verify data types (numbers vs text)
- Ensure all data is imported correctly

### Calculation Mismatches:
- Check rounding (system uses 3 decimals, Excel can use 2)
- Verify elimination logic (scores reset to 0)
- Confirm formula matches system default

### Data Issues:
- Regenerate CSV if needed
- Check for missing values
- Verify all contestants have scores

---

**Happy Testing! 🎉**
