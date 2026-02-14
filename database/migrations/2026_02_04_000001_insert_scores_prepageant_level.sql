-- Migration: Insert scores for Pre Pageant level only (for checking Total Average Score)
-- Use this to populate the Pre Pageant level so you can verify Total Average Score on the level report.
--
-- If 0 rows affected: run these to see your event and levels, then set @level_id manually if needed:
--   SELECT id, name FROM events;
--   SELECT el.id, el.name, el.`order` FROM event_levels el WHERE el.event_id = <event_id>;
-- Then use: SET @level_id = <id of your Prepagaent level>;

SET @event_id = (SELECT id FROM events WHERE LOWER(TRIM(name)) = 'diwata' LIMIT 1);

-- Pre Pageant level: match common names (Pre Pageant, Prepagaent, Prepageant, Pre-Pageant).
-- Fallback: if no name match, use the first level (order 1) which is often Pre Pageant.
-- To target a specific level only: SET @level_id = <your_level_id>;
SET @level_id = (
  SELECT id FROM event_levels
  WHERE event_id = @event_id
  AND (
    LOWER(TRIM(name)) LIKE '%pre%pageant%'
    OR LOWER(TRIM(name)) LIKE '%prepagaent%'
    OR LOWER(TRIM(name)) = 'prepageant'
    OR LOWER(TRIM(name)) = 'prepagaent'
    OR LOWER(TRIM(name)) = 'pre pageant'
    OR LOWER(TRIM(name)) = 'pre pagaent'
    OR LOWER(TRIM(name)) = 'pre-pageant'
  )
  ORDER BY `order` ASC
  LIMIT 1
);

-- If still NULL (no name match), use first level in event
SET @level_id = COALESCE(@level_id, (SELECT id FROM event_levels WHERE event_id = @event_id ORDER BY `order` ASC LIMIT 1));

-- Only run if we found both event and level
INSERT INTO scores (judge_id, contestant_id, round_id, total_score, is_submitted, submitted_at, ip_address)
SELECT j.id, c.id, r.id, 0, 1, NOW(), '127.0.0.1'
FROM judges j
JOIN judge_assignments ja ON ja.judge_id = j.id AND ja.is_active = 1
JOIN rounds r ON r.id = ja.round_id AND r.level_id = @level_id
JOIN event_levels el ON el.id = r.level_id
JOIN contestants c ON c.event_id = el.event_id AND c.status = 'Active'
WHERE j.event_id = @event_id AND el.event_id = @event_id AND j.is_active = 1
AND NOT EXISTS (
  SELECT 1 FROM scores s
  WHERE s.judge_id = j.id AND s.contestant_id = c.id AND s.round_id = r.id
);

INSERT INTO score_details (score_id, criteria_id, raw_score, weighted_score)
SELECT s.id, cw.criteria_id, LEAST(c.max_score, ROUND(c.max_score * 0.7, 3)), 0
FROM scores s
JOIN rounds r ON r.id = s.round_id AND r.level_id = @level_id
JOIN event_levels el ON el.id = r.level_id
JOIN criteria_weights cw ON cw.round_id = r.id AND cw.is_active = 1
JOIN criteria c ON c.id = cw.criteria_id
WHERE el.event_id = @event_id
AND NOT EXISTS (
  SELECT 1 FROM score_details sd WHERE sd.score_id = s.id AND sd.criteria_id = cw.criteria_id
);

-- Optional: recalculate total_score for this level's scores (run ScoringEngine or use a simple update)
-- If your app recalculates on page load, you can skip this. Otherwise run: php recalculate_rounds.php (if you have such a script)
