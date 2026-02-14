# Manual Testing Guide

## ✅ System Cleaned

All test data has been removed from the system. You can now manually input data to test all modules.

---

## 📁 Files Created

### 1. `MANUAL_TEST_DATA.md`
**Complete test data guide** with:
- Event setup instructions
- Level and round configurations
- Criteria with max scores
- Contestant list
- Judge user credentials
- Complete score data for all rounds
- Expected results
- Verification checklist
- Elimination testing scenarios

### 2. `clean_test_data.php`
**Script to clean test data:**
```bash
php test_data/clean_test_data.php [event_id]
```

---

## 🚀 Quick Start

1. **Open:** `test_data/MANUAL_TEST_DATA.md`
2. **Follow the guide step-by-step:**
   - Step 1: Create Event
   - Step 2: Create Levels
   - Step 3: Create Rounds
   - Step 4: Create Criteria
   - Step 5: Create Contestants
   - Step 6: Create Judge Users
   - Step 7: Assign Judges
   - Step 8: Assign Judges to Rounds
   - Step 9: Input Test Scores
   - Step 10: Verify Results

---

## 🎯 Testing Focus

### Primary Test: Elimination Functionality

The guide includes specific test scenarios to verify:
- ✅ Only top 5 contestants advance after Semi-Final
- ✅ Eliminated contestants cannot be scored in Final rounds
- ✅ Rankings show elimination correctly
- ✅ Reports reflect elimination

### Secondary Tests: All Modules

- ✅ Event management
- ✅ Level and round management
- ✅ Criteria management
- ✅ Contestant management
- ✅ Judge management
- ✅ Scoring functionality
- ✅ Calculation accuracy
- ✅ Reporting functionality

---

## 📊 Test Data Summary

- **10 Contestants:** C1 to C10
- **5 Judges:** J1 to J5
- **3 Levels:** Preliminary, Semi-Final (Elimination), Final
- **6 Rounds:** 2 per level
- **18 Criteria:** 3 per round (max scores: 10, 15, 10)
- **Expected Winner:** C1

---

## 🔍 Verification

After inputting all data:

1. **Check Elimination:**
   - After Round 4 (Swimsuit), verify only top 5 contestants appear
   - Try to score eliminated contestants in Round 5 - should fail
   - Generate Semi-Final report - verify top 5

2. **Check Calculations:**
   - Individual scores: (Raw Sum / Max Sum) × 100
   - Round totals: Sum of judge scores
   - Level totals: Sum of round totals
   - Final total: Sum of level totals

3. **Check Reports:**
   - Round reports show correct winners
   - Level reports show correct winners
   - Finals report shows correct overall winner

---

## 📖 Related Files

- `MANUAL_TEST_DATA.md` - Complete test data guide
- `COMPLETE_TEST_DATA.csv` - All 900 score entries (for reference)
- `EXCEL_SETUP_GUIDE.md` - Excel verification formulas
- `VERIFICATION_GUIDE.md` - Verification instructions

---

**Start with `MANUAL_TEST_DATA.md` and follow it step-by-step!** 🎯
