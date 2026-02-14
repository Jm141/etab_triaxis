# Elimination System Guide

## How Elimination Works

The elimination system is now **level-based** and uses **overall rankings** across all rounds in a level.

---

## Key Concepts

### 1. **Level Advance Count**
- Set `advance_count` on a **level** (not round)
- Example: Set `advance_count = 10` means only top 10 contestants proceed to next level
- If `advance_count` is **NULL** (empty), **ALL contestants proceed** (no elimination)

### 2. **Overall Level Rankings**
- System calculates rankings by **averaging scores across ALL rounds** in the level
- Not based on a single round - uses the overall performance in the entire level

### 3. **Automatic Processing**
- When **all rounds in a level are completed**, system automatically:
  1. Calculates overall level rankings
  2. Takes top N contestants (based on `advance_count`)
  3. Marks them as qualified for next level

---

## How to Set Up

### Step 1: Create Levels with Advance Count

1. Go to: **Events → [Event] → Manage Levels**
2. When creating/editing a level:
   - **Name**: e.g., "Preliminaries"
   - **Advance Count**: Enter `10` (or any number)
   - **Leave empty** if you want all contestants to proceed

### Step 2: Create Rounds

1. Go to: **Levels → [Level] → Manage Rounds**
2. Create rounds normally
3. **No need to mark "Elimination Round"** - elimination is automatic based on level

### Step 3: Complete Scoring

1. Judges score all rounds in the level
2. System automatically calculates rankings for each round
3. When **all rounds are complete**, system calculates overall level rankings
4. Top N contestants (based on `advance_count`) are automatically qualified

---

## Example Flow

### Setup:
```
Level 1: Preliminaries (Advance Count: 20)
├── Round 1: Talent Competition
├── Round 2: Swimsuit Competition
└── Round 3: Evening Gown

Level 2: Semi-Finals (Advance Count: 10)
├── Round 4: Interview
└── Round 5: Swimsuit (Semi-Finals)

Level 3: Finals (Advance Count: NULL - all proceed)
└── Round 6: Final Q&A
```

### Process:

1. **Preliminaries Level:**
   - All 50 contestants compete in all 3 rounds
   - System calculates rankings for each round
   - When all 3 rounds are complete:
     - System calculates **overall average** across all 3 rounds
     - Top 20 contestants (rank 1-20) are marked as qualified
     - Others are eliminated

2. **Semi-Finals Level:**
   - Only 20 qualified contestants appear
   - They compete in 2 rounds
   - When both rounds are complete:
     - System calculates overall average
     - Top 10 contestants (rank 1-10) are marked as qualified

3. **Finals Level:**
   - All 10 contestants appear (no elimination, advance_count = NULL)
   - Final round determines the winner

---

## How Rankings Are Calculated

### For Each Round:
- System averages scores from all judges
- Creates round rankings (rank 1, 2, 3...)

### For Overall Level:
- System takes **average score from ALL rounds** in the level
- Example:
  ```
  Contestant #5:
  - Round 1 average: 85.500
  - Round 2 average: 90.000
  - Round 3 average: 88.250
  - Overall Level Average: (85.500 + 90.000 + 88.250) / 3 = 87.917
  ```

### Ranking Order:
- Ranked by **overall average** (descending - higher is better)
- Rank 1 = Highest average
- Rank 2 = Second highest
- etc.

---

## Important Notes

### ✅ What Happens:
- **If `advance_count` is set** (e.g., 10):
  - Only top 10 contestants proceed
  - Others are filtered out in next level

- **If `advance_count` is NULL** (empty):
  - **ALL contestants proceed** to next level
  - No elimination happens

### ❌ What Doesn't Happen:
- Elimination is **NOT** based on individual rounds
- You **don't need** to mark rounds as "elimination rounds"
- Elimination happens **automatically** when level is complete

---

## Checking Qualification Status

### View Qualified Contestants:
```sql
SELECT c.contestant_number, c.name, el.name as qualified_level
FROM contestants c
JOIN event_levels el ON c.qualified_for_level_id = el.id
WHERE c.event_id = [EVENT_ID]
ORDER BY el.order, c.contestant_number;
```

### View Level Rankings:
- Go to: **Results → [Event] → View Round**
- Rankings show round-by-round results
- Overall level rankings are calculated automatically

---

## Troubleshooting

### Problem: All contestants appear in next level (no elimination)

**Check:**
1. Is `advance_count` set on the previous level?
   ```sql
   SELECT id, name, advance_count FROM event_levels WHERE event_id = [ID];
   ```
2. Are all rounds in the level complete?
   - Check if all rounds have rankings calculated

### Problem: Wrong contestants qualified

**Check:**
1. Verify all rounds have scores submitted
2. Check overall level rankings calculation
3. Verify `advance_count` is correct

### Problem: No contestants appear in next level

**Check:**
1. Is previous level complete?
2. Are there qualified contestants?
   ```sql
   SELECT COUNT(*) FROM contestants 
   WHERE qualified_for_level_id = [NEXT_LEVEL_ID];
   ```

---

## Summary

- **Set `advance_count` on level** = Top N proceed
- **Leave `advance_count` empty** = All proceed
- **Elimination is automatic** when level is complete
- **Based on overall level rankings**, not individual rounds
- **No need to mark rounds** as elimination rounds
