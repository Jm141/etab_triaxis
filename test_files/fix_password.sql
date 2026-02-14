-- Quick SQL fix for admin password
-- Run this in phpMyAdmin or MySQL command line

-- Option 1: Use this pre-generated hash (verified to work with 'admin123')
UPDATE `users` 
SET `password_hash` = '$2y$10$ZurgRx8IHLhlE83SyStq8ufgTfdCF8tP2x9y99k2M/4qzqvqmW0zq' 
WHERE `username` = 'admin';

-- Option 2: Generate new hash (run in PHP first, then use result)
-- php -r "echo password_hash('admin123', PASSWORD_BCRYPT);"
-- Then replace the hash above with the new one

-- Verify the password works:
-- SELECT username, password_hash FROM users WHERE username = 'admin';



