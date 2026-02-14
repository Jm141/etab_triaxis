# Score Import Migration Guide

## Overview
This migration imports scores from `SCORE_INPUT_TABLE.txt` into the database.

## File Location
- **Migration Script:** `database/migrations/2026_01_11_000001_import_scores_from_table.php`
- **Input File:** `SCORE_INPUT_TABLE.txt` (in project root)

## How to Run

### Method 1: Command Line
```bash
php database/migrations/2026_01_11_000001_import_scores_from_table.php
```

### Method 2: Via Browser (if configured)
Navigate to: `/tabulation/database/migrations/2026_01_11_000001_import_scores_from_table.php`

## What It Does

1. **Reads** `SCORE_INPUT_TABLE.txt` from the project root
2. **Parses** the file to extract:
   - Round information (Round ID, Round Name)
   - Judge information (Judge ID, Judge Name, Username)
   - Contestant numbers
   - Criteria names and max scores
   - Score values for each criteria
3. **Validates** that all required data exists:
   - Round exists in database
   - Judge exists in database
   - Contestant exists in database
   - Criteria exists for the round
4. **Imports** scores:
   - Creates or updates score records
   - Creates score_details records
   - Calculates weighted scores
   - Marks scores as submitted

## File Format

The migration expects the following format in `SCORE_INPUT_TABLE.txt`:

```
ROUND: [Round Name]
Round ID: [Round ID]

Contestant | Criteria1 (Max: X.XX) | Criteria2 (Max: X.XX) | ...

JUDGE: [Judge Name] (ID: [Judge ID], Username: [Username])
#1 (Contestant C1) | Score1 | Score2 | Score3
#2 (Contestant C2) | Score1 | Score2 | Score3
...
```

## Requirements

Before running the migration, ensure:

1. ✅ **Database connection** is configured
2. ✅ **SCORE_INPUT_TABLE.txt** exists in project root
3. ✅ **Rounds** exist in database with correct IDs
4. ✅ **Judges** exist in database with correct IDs and usernames
5. ✅ **Contestants** exist in database with correct numbers
6. ✅ **Criteria** are assigned to rounds with correct names and max scores

## Output

The migration will display:
- Processing status for each round
- Number of criteria found
- Processing status for each judge
- Import progress (every 10 scores)
- Final summary:
  - Total imported
  - Total skipped
  - Total errors

## Example Output

```
=== Importing Scores from SCORE_INPUT_TABLE.txt ===

Processing Round: Premiminary - Talent Competition (ID: 28)
  Found 3 criteria
  Processing Judge: John Anderson (ID: 28, Username: judge1)
  Processing Judge: Maria Garcia (ID: 29, Username: judge2)
  ...

=== Parsing Complete ===
Total score entries found: 300

=== Importing Scores ===
  Imported 10 scores...
  Imported 20 scores...
  ...

=== Import Complete ===
Imported: 300
Skipped: 0
Errors: 0

✓ Successfully imported 300 scores!
```

## Troubleshooting

### "SCORE_INPUT_TABLE.txt not found"
- **Solution:** Ensure the file exists in the project root directory
- Check file path: `C:\xampp1\htdocs\tabulation\SCORE_INPUT_TABLE.txt`

### "Round ID X not found"
- **Solution:** Verify the round exists in the database
- Check the round ID in the file matches the database

### "Judge ID X not found"
- **Solution:** Verify the judge exists in the database
- Check the judge ID and username match the database

### "Contestant #X not found"
- **Solution:** Verify the contestant exists for the event
- Check the contestant number matches the database

### "Criteria 'X' not found"
- **Solution:** Verify the criteria is assigned to the round
- Check criteria name and max_score match the database
- The migration tries multiple matching strategies:
  1. Exact match
  2. Case-insensitive match
  3. Match by max_score only (if unique)

### "No scores found in file"
- **Solution:** Check the file format matches the expected format
- Ensure the file is not empty
- Verify line endings are correct (Windows: CRLF, Unix: LF)

## Notes

- **Existing Scores:** If a score already exists, it will be updated (score_details deleted and recreated)
- **Weighted Scores:** Automatically calculated using ScoringEngine
- **Submission Status:** All imported scores are marked as `is_submitted = 1`
- **Idempotent:** Can be run multiple times (will update existing scores)

## Safety

- The migration does **NOT** delete existing scores unless they're being updated
- All operations are logged to console
- Errors are caught and reported without stopping the entire import
- Each score is validated before import

---

**Last Updated:** 2026-01-11
**Status:** ✓ Working - Successfully imported 300 scores
