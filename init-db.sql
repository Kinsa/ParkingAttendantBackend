-- Create test database
CREATE DATABASE IF NOT EXISTS mydb_test;

-- Grant all privileges on all databases to the admin user
GRANT ALL PRIVILEGES ON *.* TO 'admin'@'%' WITH GRANT OPTION;
FLUSH PRIVILEGES;

-- Add Levenshtein function
use mydb;
source /docker-entrypoint-initdb.d/init-levenshtein.sql

use mydb_test;
source /docker-entrypoint-initdb.d/init-levenshtein.sql
