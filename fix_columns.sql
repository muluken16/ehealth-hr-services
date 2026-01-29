USE ehealth;
ALTER TABLE employees ADD COLUMN IF NOT EXISTS education_file TEXT;
ALTER TABLE employees ADD COLUMN IF NOT EXISTS employment_agreement TEXT;
ALTER TABLE employees ADD COLUMN IF NOT EXISTS guarantor_photo VARCHAR(255);
ALTER TABLE employees ADD COLUMN IF NOT EXISTS photovvvv VARCHAR(255); -- Just to test if it reflects
ALTER TABLE employees DROP COLUMN IF EXISTS photovvvv;
