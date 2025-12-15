-- MySQL Initialization Script
-- This script runs automatically when the MySQL container is first created
-- It sets up database-level configurations and permissions

-- Ensure the database uses utf8mb4 character set
ALTER DATABASE IF EXISTS financial_tracker CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Grant additional privileges to the application user if needed
-- Note: The main database and user are created via environment variables in docker-compose.yml
-- This script is for any additional setup

-- Create additional users or grant specific privileges if needed
-- Example: Read-only user for reporting
-- CREATE USER IF NOT EXISTS 'readonly_user'@'%' IDENTIFIED BY 'readonly_password';
-- GRANT SELECT ON financial_tracker.* TO 'readonly_user'@'%';

-- Set global variables for better Laravel compatibility
SET GLOBAL sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''));

-- Flush privileges to ensure all changes take effect
FLUSH PRIVILEGES;

-- Log initialization completion
SELECT 'MySQL initialization completed successfully' AS Status;
