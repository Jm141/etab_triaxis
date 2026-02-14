-- Allow fractional ranks (e.g. 3.5 for tied 3rd place). Olympic ranking: tied get average of positions, next rank skips.
ALTER TABLE `rankings` MODIFY COLUMN `rank` DECIMAL(5,2) NOT NULL;
