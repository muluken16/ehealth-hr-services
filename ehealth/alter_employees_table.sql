-- Alter employees table to add created_by column
USE ehealth;

ALTER TABLE employees ADD COLUMN created_by VARCHAR(50) AFTER documents;

-- Verify
DESCRIBE employees;