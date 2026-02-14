# Manual Test Data Guide

This document contains all test data you need to manually input into the system to verify all modules are working correctly, including elimination functionality.

---

## 📋 Test Data Overview

- **Event:** Test Beauty Pageant
- **Contestants:** 10 (C1 to C10)
- **Judges:** 5 (J1 to J5)
- **Levels:** 4 (Pre-Pageant, Preliminary, Semi-Final, Final)
- **Rounds:** 8 (2 per level)
- **Criteria:** 24 (3 per round)
- **Formula:** Custom formula to combine Pre-Pageant + Final scores
- **Max Scores:** 10, 15, 10 (low values for easy testing)

---

## Step 1: Create Event

**Event Details:**
- **Name:** Test Beauty Pageant
- **Type:** Pageant
- **Status:** Ongoing
- **Date:** Today's date
- **Venue:** Test Venue

---

## Step 2: Create Levels

Create these levels in order:

| Order | Level Name | Description |
|-------|------------|-------------|
| 1 | Pre-Pageant | **PRE-PAGEANT** - Scores will be added to final |
| 2 | Preliminary | First level - all contestants participate |
| 3 | Semi-Final | **ELIMINATION LEVEL** - Top 5 advance |
| 4 | Final | Final level - Top 3 compete |

**Note:** 
- **Pre-Pageant:** Scores from this level will be added to the final score (configure in formula)
- **Semi-Final:** Is an elimination level. After Semi-Final, only top 5 contestants advance to Final

---

## Step 3: Create Rounds

### Pre-Pageant Level (Order 1)

| Order | Round Name | Description |
|-------|-------------|-------------|
| 1 | Pre-Pageant Interview | Pre-pageant scoring round |
| 2 | Pre-Pageant Presentation | Pre-pageant presentation round |

**Note:** These scores will be added to the final score using a custom formula.

### Preliminary Level (Order 2)

| Order | Round Name | Description |
|-------|------------|-------------|
| 1 | Talent Competition | First round |
| 2 | Q&A Session | Second round |

### Semi-Final Level (Order 3) - **ELIMINATION**

| Order | Round Name | Description |
|-------|------------|-------------|
| 1 | Evening Gown | Third round |
| 2 | Swimsuit | Fourth round - **ELIMINATION ROUND** |

**Note:** After Swimsuit round, only top 5 contestants advance to Final.

### Final Level (Order 4)

| Order | Round Name | Description |
|-------|------------|-------------|
| 1 | Final Q&A | Fifth round |
| 2 | Final Walk | Sixth round |

---

## Step 4: Create Criteria

### Round 1: Talent Competition

| Criteria Name | Max Score |
|---------------|-----------|
| Performance Quality | 10 |
| Originality | 15 |
| Stage Presence | 10 |

### Round 2: Q&A Session

| Criteria Name | Max Score |
|---------------|-----------|
| Communication | 10 |
| Intelligence | 15 |
| Poise | 10 |

### Round 3: Evening Gown

| Criteria Name | Max Score |
|---------------|-----------|
| Elegance | 10 |
| Confidence | 15 |
| Presentation | 10 |

### Round 4: Swimsuit (Elimination Round)

| Criteria Name | Max Score |
|---------------|-----------|
| Fitness | 10 |
| Confidence | 15 |
| Poise | 10 |

### Round 5: Final Q&A

| Criteria Name | Max Score |
|---------------|-----------|
| Communication | 10 |
| Intelligence | 15 |
| Overall Impact | 10 |

### Round 6: Final Walk

| Criteria Name | Max Score |
|---------------|-----------|
| Final Walk | 10 |
| Stage Presence | 15 |
| Overall Impact | 10 |

---

## Step 5: Create Contestants

Create 10 contestants:

| Contestant Number | Name |
|-------------------|------|
| 01 | Contestant C1 |
| 02 | Contestant C2 |
| 03 | Contestant C3 |
| 04 | Contestant C4 |
| 05 | Contestant C5 |
| 06 | Contestant C6 |
| 07 | Contestant C7 |
| 08 | Contestant C8 |
| 09 | Contestant C9 |
| 10 | Contestant C10 |

**Status:** All Active

---

## Step 6: Create Judge Users

Create 5 judge users:

| Username | Email | Full Name | Password |
|----------|-------|-----------|----------|
| test_judge1 | judge1@test.local | Test Judge 1 | judge123 |
| test_judge2 | judge2@test.local | Test Judge 2 | judge123 |
| test_judge3 | judge3@test.local | Test Judge 3 | judge123 |
| test_judge4 | judge4@test.local | Test Judge 4 | judge123 |
| test_judge5 | judge5@test.local | Test Judge 5 | judge123 |

**Role:** Judge

---

## Step 7: Assign Judges to Event

After creating judge users, assign them to the event:

- Judge 1 (J1)
- Judge 2 (J2)
- Judge 3 (J3)
- Judge 4 (J4)
- Judge 5 (J5)

**Judge Numbers:** J1, J2, J3, J4, J5

---

## Step 8: Assign Judges to Rounds

Assign all 5 judges to all 6 rounds:

- Round 1: Talent Competition → Assign J1, J2, J3, J4, J5
- Round 2: Q&A Session → Assign J1, J2, J3, J4, J5
- Round 3: Evening Gown → Assign J1, J2, J3, J4, J5
- Round 4: Swimsuit → Assign J1, J2, J3, J4, J5
- Round 5: Final Q&A → Assign J1, J2, J3, J4, J5
- Round 6: Final Walk → Assign J1, J2, J3, J4, J5

---

## Step 9: Test Scores Data

### Round 1: Talent Competition

**Contestant C1 (High Performer - Expected Winner):**

| Judge | Performance Quality (10) | Originality (15) | Stage Presence (10) |
|-------|---------------------------|------------------|---------------------|
| J1 | 9 | 15 | 8 |
| J2 | 10 | 15 | 8 |
| J3 | 10 | 13 | 8 |
| J4 | 8 | 14 | 9 |
| J5 | 9 | 14 | 9 |

**Calculation:**
- J1: (9+15+8)/(10+15+10) × 100 = 32/35 × 100 = 91.43
- J2: (10+15+8)/(10+15+10) × 100 = 33/35 × 100 = 94.29
- J3: (10+13+8)/(10+15+10) × 100 = 31/35 × 100 = 88.57
- J4: (8+14+9)/(10+15+10) × 100 = 31/35 × 100 = 88.57
- J5: (9+14+9)/(10+15+10) × 100 = 32/35 × 100 = 91.43
- **Round Total: 451.29**

**Contestant C2 (Average Performer):**

| Judge | Performance Quality (10) | Originality (15) | Stage Presence (10) |
|-------|---------------------------|------------------|---------------------|
| J1 | 6 | 10 | 6 |
| J2 | 6 | 10 | 6 |
| J3 | 6 | 10 | 6 |
| J4 | 6 | 10 | 6 |
| J5 | 6 | 10 | 6 |

**Calculation:**
- All judges: (6+10+6)/(10+15+10) × 100 = 22/35 × 100 = 62.86
- **Round Total: 314.30**

**Contestant C3 (Low Performer):**

| Judge | Performance Quality (10) | Originality (15) | Stage Presence (10) |
|-------|---------------------------|------------------|---------------------|
| J1 | 4 | 7 | 4 |
| J2 | 4 | 7 | 4 |
| J3 | 4 | 7 | 4 |
| J4 | 4 | 7 | 4 |
| J5 | 4 | 7 | 4 |

**Calculation:**
- All judges: (4+7+4)/(10+15+10) × 100 = 15/35 × 100 = 42.86
- **Round Total: 214.30**

**Contestants C4-C10:** Use similar patterns with varied scores between C2 and C3 levels.

---

### Round 2: Q&A Session

**Contestant C1:**

| Judge | Communication (10) | Intelligence (15) | Poise (10) |
|-------|---------------------|-------------------|------------|
| J1 | 8 | 14 | 10 |
| J2 | 9 | 14 | 8 |
| J3 | 9 | 13 | 8 |
| J4 | 8 | 13 | 9 |
| J5 | 9 | 14 | 9 |

**Round Total: ~442.86**

**Contestant C2:**

| Judge | Communication (10) | Intelligence (15) | Poise (10) |
|-------|---------------------|-------------------|------------|
| J1 | 6 | 10 | 6 |
| J2 | 6 | 10 | 6 |
| J3 | 6 | 10 | 6 |
| J4 | 6 | 10 | 6 |
| J5 | 6 | 10 | 6 |

**Round Total: 314.30**

---

### Round 3: Evening Gown

**Contestant C1:**

| Judge | Elegance (10) | Confidence (15) | Presentation (10) |
|-------|---------------|-----------------|-------------------|
| J1 | 8 | 14 | 9 |
| J2 | 10 | 14 | 10 |
| J3 | 9 | 14 | 9 |
| J4 | 8 | 14 | 9 |
| J5 | 9 | 14 | 9 |

**Round Total: ~457.14**

---

### Round 4: Swimsuit (ELIMINATION ROUND)

**Contestant C1:**

| Judge | Fitness (10) | Confidence (15) | Poise (10) |
|-------|--------------|-----------------|------------|
| J1 | 9 | 13 | 10 |
| J2 | 10 | 15 | 8 |
| J3 | 9 | 15 | 8 |
| J4 | 8 | 13 | 9 |
| J5 | 9 | 14 | 9 |

**Round Total: ~442.86**

**After this round, only top 5 contestants advance to Final.**

**Expected Top 5 after Semi-Final:**
1. C1 (highest total)
2. C2 (second highest)
3. C4 (third highest)
4. C7 (fourth highest)
5. C6 (fifth highest)

**Eliminated (C3, C5, C8, C9, C10):**
- These contestants should NOT appear in Final rounds
- Their scores for Final rounds should be 0 or not accessible

---

### Round 5: Final Q&A (Only Top 5)

**Only contestants C1, C2, C4, C7, C6 can be scored.**

**Contestant C1:**

| Judge | Communication (10) | Intelligence (15) | Overall Impact (10) |
|-------|---------------------|-------------------|---------------------|
| J1 | 8 | 13 | 10 |
| J2 | 10 | 15 | 10 |
| J3 | 10 | 14 | 10 |
| J4 | 8 | 14 | 9 |
| J5 | 9 | 14 | 10 |

**Round Total: ~457.14**

---

### Round 6: Final Walk (Only Top 5)

**Only contestants C1, C2, C4, C7, C6 can be scored.**

**Contestant C1:**

| Judge | Final Walk (10) | Stage Presence (15) | Overall Impact (10) |
|-------|-----------------|---------------------|----------------------|
| J1 | 9 | 14 | 10 |
| J2 | 9 | 15 | 9 |
| J3 | 9 | 14 | 10 |
| J4 | 8 | 14 | 9 |
| J5 | 9 | 14 | 10 |

**Round Total: ~457.14**

---

## Step 10: Expected Results

### Round Winners

| Round | Winner | Expected Total |
|-------|--------|----------------|
| R1: Talent Competition | C1 | ~451.29 |
| R2: Q&A Session | C1 | ~442.86 |
| R3: Evening Gown | C1 | ~457.14 |
| R4: Swimsuit | C1 | ~442.86 |
| R5: Final Q&A | C1 | ~457.14 |
| R6: Final Walk | C1 | ~457.14 |

### Level Winners

| Level | Winner | Expected Total |
|-------|--------|----------------|
| Preliminary (R1+R2) | C1 | ~894.15 |
| Semi-Final (R3+R4) | C1 | ~900.00 |
| Final (R5+R6) | C1 | ~914.28 |

### Overall Winner

🏆 **C1 (Contestant C1)** with highest grand total: ~2,708.43

---

## Step 11: Verification Checklist

### Basic Functionality
- [ ] Event created successfully
- [ ] Levels created in correct order
- [ ] Rounds created and assigned to levels
- [ ] Criteria created with correct max scores
- [ ] Contestants created (10 total)
- [ ] Judge users created (5 total)
- [ ] Judges assigned to event
- [ ] Judges assigned to all rounds

### Scoring Functionality
- [ ] Judges can login
- [ ] Judges see assigned rounds
- [ ] Judges can enter scores for all criteria
- [ ] Scores calculate correctly: (Raw Sum / Max Sum) × 100
- [ ] Scores can be submitted
- [ ] Scores are saved correctly

### Elimination Functionality
- [ ] After Round 4 (Swimsuit), only top 5 contestants visible
- [ ] Eliminated contestants (C3, C5, C8, C9, C10) cannot be scored in Final rounds
- [ ] Only top 5 contestants appear in Round 5 and Round 6
- [ ] Rankings show elimination correctly

### Reporting Functionality
- [ ] Round reports generate correctly
- [ ] Level reports generate correctly
- [ ] Finals report generates correctly
- [ ] Winners per round match expected
- [ ] Winners per level match expected
- [ ] Overall winner matches expected (C1)

### Calculation Verification
- [ ] Individual scores: (Raw Sum / Max Sum) × 100
- [ ] Round totals: Sum of all judge scores
- [ ] Level totals: Sum of round totals in level
- [ ] Final total: Sum of all level totals
- [ ] Rankings assigned correctly (highest = Rank 1)
- [ ] Ties handled correctly

---

## Step 12: Testing Elimination

### Test Scenario 1: Verify Elimination After Semi-Final

1. Complete scoring for Round 4 (Swimsuit)
2. Generate Semi-Final report
3. Check top 5 contestants
4. Verify eliminated contestants (C3, C5, C8, C9, C10) are NOT in top 5
5. Try to score eliminated contestants in Round 5 - should NOT be possible

### Test Scenario 2: Verify Only Top 5 in Final

1. Go to Round 5 (Final Q&A)
2. Verify only 5 contestants are listed (C1, C2, C4, C7, C6)
3. Verify eliminated contestants are NOT listed
4. Complete scoring for Round 5
5. Complete scoring for Round 6
6. Generate Finals report
7. Verify only top 5 contestants appear

### Test Scenario 3: Verify Rankings

1. Generate Round 4 report
2. Check rankings - top 5 should be ranked 1-5
3. Generate Semi-Final report
4. Verify top 5 contestants advance
5. Generate Finals report
6. Verify final rankings show only top 5

---

## Step 13: Complete Score Data

For complete score data for all contestants, see:
- `test_data/COMPLETE_TEST_DATA.csv` - All 900 score entries
- `test_data/EXCEL_SETUP_GUIDE.md` - Excel formulas for verification

---

## Step 14: Pre-Pageant + Final Scenario

### Scenario: Pre-Pageant Scores Added to Final

This scenario demonstrates how pre-pageant scores can be added to final scores.

### Setup:

1. **Create Pre-Pageant Level** (Order 1)
   - Round 1: Pre-Pageant Interview
   - Round 2: Pre-Pageant Presentation

2. **Create Final Level** (Order 4)
   - Round 1: Final Q&A
   - Round 2: Final Walk

3. **Configure Formula (Detailed Steps):**

   **Step 3.1: Navigate to Formula Management**
   - Login as **Event Technical Admin** or **Super Admin**
   - Go to your event page
   - Click **"Reports"** button
   - Click **"Manage Formulas"** link (Tech Admin only)

   **Step 3.2: Create New Formula**
   - Click **"Add New Formula"** button
   - You'll see a form with the following fields:

   **Step 3.3: Fill in Formula Details**
   - **Formula Type:** Select **"Final"** from dropdown
   - **Formula Expression:** Enter: `SUM(level_totals)`
   - **Description:** Enter: "Pre-Pageant scores added to Final scores"

   **Step 3.4: Select Levels to Combine**
   - Scroll down to **"Select Levels to Combine"** section
   - You'll see checkboxes for each level:
     - ✅ **Pre-Pageant** (Order 1) - CHECK THIS
     - ❌ **Preliminary** (Order 2) - UNCHECK THIS
     - ❌ **Semi-Final** (Order 3) - UNCHECK THIS
     - ✅ **Final** (Order 4) - CHECK THIS
   
   **Note:** Only checked levels will be included in the calculation.

   **Step 3.5: Select Rounds (Optional)**
   - If you want to select specific rounds instead of entire levels:
     - Scroll to **"Select Rounds to Combine"** section
     - Check only the rounds you want:
       - ✅ Pre-Pageant Interview
       - ✅ Pre-Pageant Presentation
       - ✅ Final Q&A
       - ✅ Final Walk
     - Uncheck all other rounds

   **Step 3.6: Save Formula**
   - Click **"Save Formula"** button
   - You should see a success message: "Formula saved successfully."

   **Step 3.7: Verify Formula is Active**
   - The formula should appear in the formulas list
   - Make sure **"Is Active"** checkbox is checked
   - If editing existing formula, ensure it's set to active

4. **Expected Result:**
   - Final Total = Pre-Pageant Total + Final Total
   - Other levels (Preliminary, Semi-Final) are excluded
   - When you generate Finals Report, it will show the custom formula being used

### Step 15: Verify Formula Configuration

**Step 15.1: Check Formula is Saved**
- Go back to **Reports → Manage Formulas**
- You should see your formula in the list
- Verify:
  - Formula Type: **Final**
  - Formula Expression: `SUM(level_totals)`
  - Is Active: ✅ (checked)
  - Description matches what you entered

**Step 15.2: Generate Finals Report**
- Go to **Reports → Finals Report**
- At the top of the report, you should see:
  - **"Custom Formula Applied"** alert box
  - Formula: `SUM(level_totals)`
  - Description: "Pre-Pageant scores added to Final scores"
  - Note: "Only selected levels/rounds are included in the calculation."

**Step 15.3: Verify Calculation**
- Check that the Final Total column shows:
  - Pre-Pageant Total + Final Total (for each contestant)
  - Other levels (Preliminary, Semi-Final) are NOT included
- Example: If Pre-Pageant = 900 and Final = 914.28, Final Total should be 1,814.28

**Step 15.4: Test with Sample Scores**
- Enter scores for Pre-Pageant rounds
- Enter scores for Final rounds
- Generate Finals Report
- Verify the calculation matches: Pre-Pageant Total + Final Total

### Example Calculation:

**Contestant C1:**
- Pre-Pageant Total: 900.00
- Final Total: 914.28
- **Combined Total: 1,814.28** (Pre-Pageant + Final)

**Contestant C2:**
- Pre-Pageant Total: 628.60
- Final Total: 628.60
- **Combined Total: 1,257.20** (Pre-Pageant + Final)

**Winner:** C1 with 1,814.28 🏆

### Step 16: Alternative Formula Options

If you want to use a different formula, here are some options:

**Option 1: Weighted Pre-Pageant (30%) + Final (70%)**
- Formula Expression: `(level_totals[0] * 0.3) + (level_totals[3] * 0.7)`
- Select Levels: Pre-Pageant (index 0), Final (index 3)
- Result: (Pre-Pageant × 30%) + (Final × 70%)

**Option 2: Average of Pre-Pageant and Final**
- Formula Expression: `AVG(level_totals)`
- Select Levels: Pre-Pageant, Final
- Result: (Pre-Pageant Total + Final Total) ÷ 2

**Option 3: Sum of Specific Rounds**
- Formula Expression: `round_totals[0] + round_totals[1] + round_totals[5] + round_totals[6]`
- Select Rounds: Pre-Pageant Interview, Pre-Pageant Presentation, Final Q&A, Final Walk
- Result: Sum of selected rounds only

**Note:** When using array indices like `level_totals[0]`, remember:
- Index 0 = First level (Pre-Pageant, Order 1)
- Index 1 = Second level (Preliminary, Order 2)
- Index 2 = Third level (Semi-Final, Order 3)
- Index 3 = Fourth level (Final, Order 4)

---

## Notes

1. **Elimination Logic:** After Semi-Final (Round 4), only top 5 contestants advance. The system should automatically filter out eliminated contestants in Final rounds.

2. **Score Calculation:** Default formula is `SUM(round_totals)` - simple sum of all round totals. Custom formulas can combine specific levels/rounds.

3. **Pre-Pageant Scenario:** Use custom formula to add pre-pageant scores to final scores. Select only the levels/rounds you want to include.

4. **Ranking:** Standard competition ranking - same scores get same rank, next rank skips positions.

5. **Testing:** Use this data to verify:
   - All modules work correctly
   - Elimination functionality works
   - Calculations are accurate
   - Reports generate correctly
   - Custom formulas work correctly
   - Pre-pageant + final combination works

---

**Use this guide to manually input all test data and verify the system is working correctly!** 🎯

**See `docs/PRE_PAGEANT_SCENARIO.md` for detailed pre-pageant scenario guide.**
