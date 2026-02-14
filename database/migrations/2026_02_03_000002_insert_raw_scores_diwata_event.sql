-- Migration: Insert raw scores for all judges in Diwata event
-- Populates score and score_details so report sheets show full data.
-- Run after setting your event name if different (see @event_id below).

SET @event_id = (SELECT id FROM events WHERE LOWER(TRIM(name)) = 'diwata' LIMIT 1);

-- 1) Insert missing score rows (one per judge × contestant × round where judge is assigned)
INSERT INTO scores (judge_id, contestant_id, round_id, total_score, is_submitted, submitted_at, ip_address)
SELECT j.id, c.id, r.id, 0, 1, NOW(), '127.0.0.1'
FROM judges j
JOIN judge_assignments ja ON ja.judge_id = j.id AND ja.is_active = 1
JOIN rounds r ON r.id = ja.round_id
JOIN event_levels el ON el.id = r.level_id
JOIN contestants c ON c.event_id = el.event_id AND c.status = 'Active'
WHERE j.event_id = @event_id AND el.event_id = @event_id AND j.is_active = 1
AND NOT EXISTS (
  SELECT 1 FROM scores s
  WHERE s.judge_id = j.id AND s.contestant_id = c.id AND s.round_id = r.id
);

-- 2) Insert raw score details for scores that are missing them (placeholder: 70% of max per criterion)
INSERT INTO score_details (score_id, criteria_id, raw_score, weighted_score)
SELECT s.id, cw.criteria_id, LEAST(c.max_score, ROUND(c.max_score * 0.7, 3)), 0
FROM scores s
JOIN rounds r ON r.id = s.round_id
JOIN event_levels el ON el.id = r.level_id
JOIN criteria_weights cw ON cw.round_id = r.id AND cw.is_active = 1
JOIN criteria c ON c.id = cw.criteria_id
WHERE el.event_id = @event_id
AND NOT EXISTS (
  SELECT 1 FROM score_details sd WHERE sd.score_id = s.id AND sd.criteria_id = cw.criteria_id
);
