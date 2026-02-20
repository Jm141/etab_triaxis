-- Fix contestants table unique constraint for MR & MISS events
-- The current constraint prevents duplicate contestant numbers, which is needed for MR & MISS events

-- 1. Drop the existing unique constraint
ALTER TABLE contestants DROP INDEX unique_event_number;

-- 2. Create a new constraint that allows duplicates for MR & MISS events
-- This constraint will only apply to single-gender events
ALTER TABLE contestants ADD CONSTRAINT unique_event_number_single 
UNIQUE (event_id, contestant_number, 
        CASE 
            WHEN EXISTS (
                SELECT 1 FROM events e 
                WHERE e.id = contestants.event_id 
                AND e.gender_mode = 'single'
            ) THEN 1
            ELSE NULL
        END
);

-- 3. Add an index for MR & MISS events to ensure performance
ALTER TABLE contestants ADD INDEX idx_mr_miss_event_number (event_id, contestant_number);

-- 4. Add a partial unique index for single-gender events
ALTER TABLE contestants ADD UNIQUE INDEX idx_single_event_number 
(event_id, contestant_number) 
WHERE EXISTS (
    SELECT 1 FROM events e 
    WHERE e.id = contestants.event_id 
    AND e.gender_mode = 'single'
);
