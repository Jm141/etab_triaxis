# Route Fix Summary

## Issue
When clicking "Score" or "Score Contestants", the table view was not showing.

## Solution

### 1. Route Order Fixed
Routes are now ordered with more specific routes first:
```php
// More specific route first (table view)
$router->get('judge/rounds/{roundId}/table', 'JudgeScoring@roundTable');
// Less specific route last (redirects to table)
$router->get('judge/rounds/{roundId}', 'JudgeScoring@round');
```

### 2. All Links Updated
All links now point directly to the table view:
- ✅ Dashboard: `/tabulation/judge/rounds/{roundId}/table`
- ✅ Rounds List: `/tabulation/judge/rounds/{roundId}/table`
- ✅ Round View: `/tabulation/judge/rounds/{roundId}/table`

### 3. Redirect in Place
The `round()` method redirects to table view as a fallback:
```php
$this->redirect('/tabulation/judge/rounds/' . $roundId . '/table');
```

## How It Works Now

1. **Direct Links**: All "Score" buttons link directly to `/table` view
2. **Fallback Redirect**: If someone accesses `/judge/rounds/{roundId}` directly, they're redirected to `/table`
3. **Route Priority**: More specific routes are checked first

## Testing

To test:
1. Click "Score" or "Score Contestants" button
2. Should go directly to table view showing:
   - Contestant numbers (ascending)
   - All criteria in columns
   - Auto-save functionality
   - Submit all button

