-- Add missing columns to fix "education_file" error
-- Run this in phpMyAdmin -> select ehealth database -> SQL tab

ALTER TABLE employees ADD COLUMN education_file TEXT;
ALTER TABLE employees ADD COLUMN employment_agreement TEXT;
ALTER TABLE employees ADD COLUMN guarantor_photo VARCHAR(255);

-- Verify columns were added
SELECT COLUMN_NAME FROM information_schema.COLUMNS 
WHERE TABLE_SCHEMA = 'ehealth' 
AND TABLE_NAME = 'employees' 
AND COLUMN_NAME IN ('education_file', 'employment_agreement', 'guarantor_photo');
