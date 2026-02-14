# Scoring System Guide
## How Scores Are Managed and Calculated

This guide explains how the tabulation system manages scores, calculates weighted totals, and generates rankings.

---

## 📊 Overview

The scoring system uses a **weighted scoring algorithm** that:
1. Stores raw scores from judges for each criterion
2. Normalizes scores to percentages
3. Applies criteria weights
4. Calculates weighted totals
5. Averages scores across all judges
6. Generates final rankings

---

## 🗄️ Database Structure

### Tables Involved

#### 1. `scores` Table
Stores the main score record for each judge-contestant-round combination.

**Key Fields:**
- `judge_id` - Which judge gave the score
- `contestant_id` - Which contestant was scored
- `round_id` - Which round the score is for
- `total_score` - **Calculated weighted total** (0-100)
- `is_submitted` - Whether judge has submitted the score
- `is_locked` - Whether score can be modified
- `admin_edit_allowed` - Judge permission for admin to edit
- `admin_edited_by` - Who edited the score (if admin edited)
- `admin_edited_at` - When it was edited

#### 2. `score_details` Table
Stores individual criterion scores within each main score.

**Key Fields:**
- `score_id` - Links to main score record
- `criteria_id` - Which criterion this score is for
- `raw_score` - **Judge's raw score** (0 to max_score)
- `weighted_score` - **Calculated weighted contribution** to total

#### 3. `criteria_weights` Table
Defines how much each criterion contributes to the final score.

**Key Fields:**
- `round_id` - Which round
- `criteria_id` - Which criterion
- `weight` - **Percentage weight** (e.g., 50 = 50%)
- `is_active` - Whether this weight is currently used

---

## 📝 Score Submission Flow

### Step 1: Judge Submits Scores

When a judge scores a contestant:

1. **Judge enters raw scores** for each criterion
   - Example: Performance Quality: 85 (out of 100)
   - Example: Originality: 42 (out of 50)
   - Example: Stage Presence: 45 (out of 50)

2. **System stores raw scores** in `score_details` table
   - Each criterion score is stored separately
   - Raw scores are preserved exactly as entered

3. **System calculates weighted total** automatically
   - Uses `ScoringEngine::calculateScore()`
   - Applies normalization and weighting
   - Stores result in `scores.total_score`

4. **Score is marked as submitted**
   - `is_submitted = 1`
   - `submitted_at = current timestamp`

---

## 🧮 Scoring Calculation Algorithm

### Simplified Formula (Auto-Calculated Weights)

**The system automatically calculates weights based on each criterion's max_score proportionally. No manual weight entry needed!**

```
Final Score = (Sum of all raw scores / Sum of all max scores) × 100
```

### How Auto-Weighting Works

**Weights are automatically calculated based on max_score:**
```
Weight = (criterion_max_score / total_max_score_of_all_criteria) × 100
```

**Example Setup:**
- Criterion 1: Max Score = 10
- Criterion 2: Max Score = 15
- Criterion 3: Max Score = 5
- **Total Max Score = 30**

**Auto-Calculated Weights:**
- Criterion 1 Weight = (10 / 30) × 100 = **33.33%**
- Criterion 2 Weight = (15 / 30) × 100 = **50.00%**
- Criterion 3 Weight = (5 / 30) × 100 = **16.67%**
- **Total = 100%** ✓

### Detailed Calculation Steps

#### Step 1: Judge Enters Raw Scores

Judges enter scores for each criterion based on the max_score.

**Example:**
- Criterion 1 (Max 10): Judge gives **8**
- Criterion 2 (Max 15): Judge gives **12**
- Criterion 3 (Max 5): Judge gives **4**

#### Step 2: Calculate Total Raw and Total Max

**Formula:**
```
Total Raw Score = Sum of all raw scores
Total Max Score = Sum of all max scores
```

**Example:**
- Total Raw: 8 + 12 + 4 = **24**
- Total Max: 10 + 15 + 5 = **30**

#### Step 3: Calculate Final Percentage

**Formula:**
```
Final Score = (Total Raw Score / Total Max Score) × 100
```

**Example:**
- Final Score = (24 / 30) × 100 = **80.00 points** (out of 100)

### Why This Is Better

1. **Simpler**: No need to manually set weights
2. **Automatic**: Weights are calculated proportionally
3. **Intuitive**: Higher max_score = higher weight automatically
4. **Error-proof**: Weights always sum to 100%
5. **Easy to understand**: Just sum scores and divide by total max

---

## 📈 Complete Calculation Example

### Scenario: Talent Competition Round

**Criteria Setup (Auto-Weighted):**
- Performance Quality: Max Score = **100**
- Originality: Max Score = **50**
- Stage Presence: Max Score = **50**
- **Total Max Score = 200**

**Auto-Calculated Weights:**
- Performance Quality: (100 / 200) × 100 = **50%**
- Originality: (50 / 200) × 100 = **25%**
- Stage Presence: (50 / 200) × 100 = **25%**
- **Total = 100%** ✓

**Judge Scores Contestant:**
- Performance Quality: **85** (out of 100)
- Originality: **42** (out of 50)
- Stage Presence: **45** (out of 50)

**Calculation (Simplified Method):**

1. **Sum Raw Scores:**
   - Total Raw = 85 + 42 + 45 = **172**

2. **Sum Max Scores:**
   - Total Max = 100 + 50 + 50 = **200**

3. **Calculate Final Score:**
   - Final Score = (172 / 200) × 100 = **86.00 points** (out of 100)

**Result:** This contestant receives **86.00 points** from this judge for this round.

**Note:** The system automatically handles the weighting - you don't need to think about it!

---

## 🏆 Ranking Calculation

### Step 1: Collect All Judge Scores

For each contestant, collect all submitted scores from all judges.

**Example:**
- Contestant #1 (Emma Rodriguez):
  - Judge 1: 85.7 points
  - Judge 2: 88.2 points
  - Judge 3: 82.5 points
  - Judge 4: 90.1 points
  - Judge 5: 87.3 points
  - Judge 6: 84.9 points
  - Judge 7: 86.4 points

### Step 2: Calculate Average

**Formula:**
```
average_score = sum(all_judge_scores) / number_of_judges
```

**Example:**
- Sum: 85.7 + 88.2 + 82.5 + 90.1 + 87.3 + 84.9 + 86.4 = **605.1**
- Count: 7 judges
- Average: 605.1 / 7 = **86.44 points**

### Step 3: Sort by Average

Contestants are sorted by average score in **descending order** (highest first).

**Example Rankings:**
1. Contestant #5: 92.15 points (average)
2. Contestant #1: 86.44 points (average)
3. Contestant #3: 84.22 points (average)
4. Contestant #2: 81.88 points (average)
5. Contestant #4: 79.33 points (average)

### Step 4: Assign Ranks

Ranks are assigned based on sorted order, with **tie-breaking**:

**Tie-Breaking Rules:**
- If two contestants have the **exact same average**, they receive the **same rank**
- The next contestant receives a rank that skips the tied positions

**Example:**
- Rank 1: Contestant #5 (92.15)
- Rank 2: Contestant #1 (86.44)
- Rank 3: Contestant #3 (84.22)
- Rank 4: Contestant #2 (81.88)
- Rank 5: Contestant #4 (79.33)

**Tie Example:**
- Rank 1: Contestant #5 (92.15)
- Rank 2: Contestant #1 (86.44)
- Rank 2: Contestant #7 (86.44) ← **Tied for 2nd**
- Rank 4: Contestant #3 (84.22) ← **Skips rank 3**

---

## 🔄 Score Recalculation

### When Scores Are Recalculated

1. **When judge submits/updates scores**
   - Individual score is recalculated immediately
   - Rankings are NOT automatically updated

2. **When admin edits scores** (with permission)
   - Score is recalculated
   - Rankings need manual recalculation

3. **When "Calculate Results" is clicked**
   - All scores for the round are recalculated
   - Rankings are regenerated
   - Old rankings are replaced

### Recalculation Process

```
1. Get all scores for the round
2. For each score:
   - Recalculate weighted total
   - Update score_details.weighted_score
   - Update scores.total_score
3. Calculate new rankings:
   - Group by contestant
   - Calculate averages
   - Sort by average
   - Assign ranks
4. Save rankings to database
```

---

## ✏️ Admin Score Editing

### Permission System

Judges can grant permission for admins to edit their scores:

1. **Judge submits score**
   - `admin_edit_allowed = 0` (default: no permission)

2. **Judge grants permission**
   - Toggle switch in scoring interface
   - Sets `admin_edit_allowed = 1`

3. **Admin can now edit**
   - Only if `admin_edit_allowed = 1`
   - System tracks who edited (`admin_edited_by`)
   - System tracks when edited (`admin_edited_at`)

### Editing Process

1. **Admin navigates to Score Management**
   - Events → Results → Manage Scores
   - Or: Score Management → Select Round

2. **View all submitted scores**
   - Shows which scores have edit permission
   - Only scores with permission can be edited

3. **Edit score**
   - Modify raw scores for each criterion
   - System recalculates weighted total automatically
   - Audit trail is logged

4. **Recalculate rankings**
   - After editing, click "Calculate Results"
   - Rankings are updated with new scores

---

## 📊 Score Management Features

### Viewing Scores

**For Judges:**
- Can only see their own scores
- Can view submitted scores
- Can grant/revoke edit permission

**For Admins/Tabulators:**
- Can view all scores for a round
- Can see which scores have edit permission
- Can edit scores (if permission granted)
- Can see edit history (who edited, when)

### Score Status

**Pending:**
- Judge hasn't submitted yet
- `is_submitted = 0`

**Submitted:**
- Judge has submitted
- `is_submitted = 1`
- Score is locked from further judge edits (unless unlocked)

**Locked:**
- Score cannot be modified
- `is_locked = 1`
- Usually set after round completion

---

## 🎯 Key Concepts

### Raw Score vs. Weighted Score

**Raw Score:**
- The actual number the judge enters
- Example: 85 out of 100
- Stored in `score_details.raw_score`

**Weighted Score:**
- The contribution to the total after normalization and weighting
- Example: 42.5 points (from 85 raw with 50% weight)
- Stored in `score_details.weighted_score`

**Total Score:**
- Sum of all weighted scores
- Range: 0-100 (percentage)
- Stored in `scores.total_score`

### Normalization

**Why normalize?**
- Different criteria have different max scores
- Example: One criterion max is 100, another is 50
- Normalization makes them comparable

**How it works:**
- Converts all scores to percentages (0-100%)
- Then applies weights
- Ensures fair comparison

### Weight Distribution (Auto-Calculated)

**No Manual Configuration Needed!**
- Weights are **automatically calculated** based on max_score
- Weights **always sum to 100%** automatically
- No need to manually set or verify weights

**How It Works:**
- System calculates: `weight = (max_score / total_max_score) × 100`
- Example: Max scores 10, 15, 5 → Weights 33.33%, 50%, 16.67%
- **Always adds up to 100%** - guaranteed!

---

## 🔍 Example: Complete Round Calculation

### Setup

**Round:** Talent Competition
**Contestants:** 3 (for simplicity)
**Judges:** 3 (for simplicity)

**Criteria (Auto-Weighted):**
- Performance Quality: Max Score = 100
- Originality: Max Score = 50
- Stage Presence: Max Score = 50
- **Total Max Score = 200**
- **Auto Weights:** 50%, 25%, 25% (calculated automatically)

### Judge 1 Scores

**Contestant A:**
- Performance: 90
- Originality: 45
- Stage Presence: 48
- **Total Raw:** 183
- **Final Score:** (183 / 200) × 100 = **91.50 points**

**Contestant B:**
- Performance: 75
- Originality: 35
- Stage Presence: 40
- **Total Raw:** 150
- **Final Score:** (150 / 200) × 100 = **75.00 points**

**Contestant C:**
- Performance: 85
- Originality: 40
- Stage Presence: 42
- **Total Raw:** 167
- **Final Score:** (167 / 200) × 100 = **83.50 points**

### Judge 2 Scores

**Contestant A:**
- Performance: 88
- Originality: 44
- Stage Presence: 46
- **Total Raw:** 178
- **Final Score:** (178 / 200) × 100 = **89.00 points**

**Contestant B:**
- Performance: 80
- Originality: 38
- Stage Presence: 42
- **Total Raw:** 160
- **Final Score:** (160 / 200) × 100 = **80.00 points**

**Contestant C:**
- Performance: 90
- Originality: 45
- Stage Presence: 48
- **Total Raw:** 183
- **Final Score:** (183 / 200) × 100 = **91.50 points**

### Judge 3 Scores

**Contestant A:**
- Performance: 92
- Originality: 46
- Stage Presence: 47
- **Total Raw:** 185
- **Final Score:** (185 / 200) × 100 = **92.50 points**

**Contestant B:**
- Performance: 78
- Originality: 36
- Stage Presence: 41
- **Total Raw:** 155
- **Final Score:** (155 / 200) × 100 = **77.50 points**

**Contestant C:**
- Performance: 87
- Originality: 42
- Stage Presence: 45
- **Total Raw:** 174
- **Final Score:** (174 / 200) × 100 = **87.00 points**

### Final Rankings

**Contestant A:**
- Judge 1: 91.50
- Judge 2: 89.00
- Judge 3: 92.50
- **Average: 91.00** → **Rank 1** 🥇

**Contestant C:**
- Judge 1: 83.50
- Judge 2: 91.50
- Judge 3: 87.00
- **Average: 87.33** → **Rank 2** 🥈

**Contestant B:**
- Judge 1: 75.00
- Judge 2: 80.00
- Judge 3: 77.50
- **Average: 77.50** → **Rank 3** 🥉

---

## 🛠️ Technical Details

### Scoring Engine Methods

#### `calculateScore($scoreId)`
- Calculates weighted total for a single score
- Normalizes raw scores
- Applies criteria weights
- Updates `scores.total_score`

#### `calculateRankings($roundId)`
- Groups scores by contestant
- Calculates averages
- Sorts by average (descending)
- Assigns ranks (handles ties)

#### `saveRankings($roundId, $rankings)`
- Saves rankings to `rankings` table
- Replaces old rankings for the round

#### `recalculateRound($roundId)`
- Recalculates all scores in a round
- Regenerates rankings
- Used when criteria weights change or scores are edited

---

## ⚠️ Important Notes

### Score Editing

1. **Changing Max Score:**
   - If you change a criterion's max_score after scores are submitted
   - Existing scores will be recalculated with new max
   - This may change totals significantly
   - **Warning:** Use with caution!

2. **Changing Weights:**
   - If you change criteria weights after scores are submitted
   - You must recalculate the round
   - All scores will be recalculated with new weights

3. **Admin Editing:**
   - Only works if judge granted permission
   - All edits are logged
   - Judge can see who edited and when

### Data Integrity

1. **Raw scores are preserved:**
   - Original judge scores are never lost
   - Weighted scores are recalculated from raw scores
   - You can always see what the judge originally entered

2. **Audit trail:**
   - All score submissions are logged
   - Admin edits are logged
   - IP addresses are recorded

3. **Lock mechanism:**
   - Scores can be locked to prevent changes
   - Useful after round completion
   - Locked scores cannot be modified

---

## 📋 Quick Reference

### Score Calculation Formula (Simplified)

```
Total Raw Score = Sum of all raw scores
Total Max Score = Sum of all max scores

Final Score = (Total Raw Score / Total Max Score) × 100

Weights are auto-calculated:
  weight = (criterion_max_score / total_max_score) × 100
```

### Ranking Formula

```
For each contestant:
  average = sum(all_judge_scores) / count(judges)
  
Sort by average (descending)
Assign ranks (handle ties)
```

### Score Storage

```
scores table:
  - total_score: Final weighted total (0-100)
  
score_details table:
  - raw_score: Judge's original score
  - weighted_score: Contribution to total
```

---

## 🎓 Best Practices

1. **Set weights before scoring starts**
   - Avoid changing weights mid-competition
   - Ensures consistency

2. **Verify weight totals**
   - Always ensure weights sum to 100%
   - System handles non-100% but 100% is clearer

3. **Lock scores after round completion**
   - Prevents accidental changes
   - Maintains data integrity

4. **Recalculate after changes**
   - If you edit scores or change weights
   - Always recalculate rankings
   - Don't assume rankings update automatically

5. **Review audit logs**
   - Check who edited scores
   - Verify all changes are legitimate
   - Maintain transparency

---

## 🔗 Related Documentation

- **TEST_GUIDE.md** - Step-by-step testing guide
- **JUDGE_SETUP_GUIDE.md** - How to set up judges
- **INSTALL_DATABASE.md** - Database setup

---

## 💡 Tips

1. **Test calculations manually:**
   - Use a calculator to verify weighted scores
   - Compare with system results
   - Ensures accuracy

2. **Monitor score submissions:**
   - Check which judges have submitted
   - Identify missing scores early
   - Contact judges if needed

3. **Use score management:**
   - Review scores before calculating rankings
   - Check for outliers or errors
   - Edit if needed (with permission)

4. **Understand the algorithm:**
   - Read this guide thoroughly
   - Understand normalization and weighting
   - Helps troubleshoot issues

---

**Need Help?** If calculations seem incorrect:
1. Verify criteria weights sum to 100%
2. Check that all judges have submitted
3. Verify max scores are correct
4. Recalculate the round
5. Review audit logs for edits

---

**Happy Scoring! 🎯**

