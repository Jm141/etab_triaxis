# Test Data Verification Guide

## ✅ Test Data Successfully Inserted!

All test data has been inserted into the system. Here's what was created:

### Event Created
- **Event ID:** 7
- **Name:** Test Beauty Pageant - [timestamp]

### Users & Judges
- **5 Judge Users Created:**
  - `test_judge1` / `judge123`
  - `test_judge2` / `judge123`
  - `test_judge3` / `judge123`
  - `test_judge4` / `judge123`
  - `test_judge5` / `judge123`

### Contestants
- **10 Contestants:** C1 to C10 (Contestant C1 to Contestant C10)

### Structure
- **3 Levels:**
  - Preliminary (Rounds: Talent Competition, Q&A Session)
  - Semi-Final (Rounds: Evening Gown, Swimsuit)
  - Final (Rounds: Final Q&A, Final Walk)

- **6 Rounds:**
  - R1: Talent Competition
  - R2: Q&A Session
  - R3: Evening Gown
  - R4: Swimsuit
  - R5: Final Q&A
  - R6: Final Walk

- **18 Criteria** (3 per round):
  - Max scores: 10, 15, 10 (pattern repeats)

### Scores
- **900 score entries** inserted
- All scores marked as submitted
- Rankings calculated for all rounds

---

## 🔍 How to Verify

### Step 1: Login as Judge

1. Go to: `http://localhost/tabulation/login`
2. Login with: `test_judge1` / `judge123`
3. You should see assigned rounds
4. Click on a round to view scores

### Step 2: Check Reports

1. Login as Admin: `admin` / `admin123`
2. Go to Events → Select Event ID 7
3. Click "Reports" button
4. Generate reports:
   - **Report Per Round** - Check each round
   - **Report Per Level** - Check each level
   - **Finals Report** - Check overall winner

### Step 3: Compare with Excel

1. Open `test_data/COMPLETE_TEST_DATA.csv` in Excel
2. Follow `test_data/EXCEL_SETUP_GUIDE.md` to set up formulas
3. Compare Excel results with system reports:
   - Round totals should match
   - Level totals should match
   - Final rankings should match

---

## 📊 Expected Results

### Round Winners (Expected)
- **R1 (Talent Competition):** C1
- **R2 (Q&A Session):** C1
- **R3 (Evening Gown):** C1
- **R4 (Swimsuit):** C1
- **R5 (Final Q&A):** C1
- **R6 (Final Walk):** C1

### Level Winners (Expected)
- **Preliminary:** C1
- **Semi-Final:** C1
- **Final:** C1

### Overall Winner (Expected)
- 🏆 **C1** (Contestant C1) with highest grand total

---

## 🧮 Calculation Verification

### Individual Score Formula
```
Score = (Sum of Raw Scores / Sum of Max Scores) × 100
```

**Example:**
- Raw: 9 + 15 + 8 = 32
- Max: 10 + 15 + 10 = 35
- Score: (32 / 35) × 100 = 91.43

### Round Total
```
Round Total = Sum of all judge scores for that round
```

### Level Total
```
Level Total = Sum of all round totals in that level
```

### Final Total (Default Formula)
```
Final Total = SUM(round_totals)
```

---

## ✅ Verification Checklist

- [ ] All 5 judges can login
- [ ] All 10 contestants visible
- [ ] All 6 rounds have scores
- [ ] Round reports generate correctly
- [ ] Level reports generate correctly
- [ ] Finals report generates correctly
- [ ] Round totals match Excel calculations
- [ ] Level totals match Excel calculations
- [ ] Final rankings match Excel calculations
- [ ] Winners per round match expected
- [ ] Winners per level match expected
- [ ] Overall winner matches expected (C1)

---

## 🔄 Re-run Script

If you need to insert test data again:

```bash
# Insert into new event
php test_data/insert_test_data_to_system.php

# Insert into existing event (specify event ID)
php test_data/insert_test_data_to_system.php 7
```

**Note:** The script will:
- Skip existing users/judges
- Skip existing contestants
- Skip existing levels/rounds
- Update or create new scores

---

## 📖 Related Files

- `test_data/COMPLETE_TEST_DATA.csv` - All test scores
- `test_data/EXCEL_SETUP_GUIDE.md` - Excel setup instructions
- `test_data/EXCEL_TEST_TEMPLATE.md` - Excel template structure
- `docs/HOW_WINNERS_ARE_CALCULATED.md` - Calculation explanation

---

## 🐛 Troubleshooting

### Scores Not Showing
- Check if scores are marked as submitted (`is_submitted = 1`)
- Verify judge assignments to rounds
- Check criteria assignments to rounds

### Calculations Don't Match
- Verify formula in Reports → Manage Formulas
- Check rounding (system uses 3 decimals)
- Compare step-by-step with Excel

### Reports Not Generating
- Ensure scores are submitted
- Check event access permissions
- Verify rounds have criteria assigned

---

**Happy Testing! 🎉**
