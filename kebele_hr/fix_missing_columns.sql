-- Fix missing columns in employees table
-- Run this in phpMyAdmin or MySQL command line

USE ehealth;

-- Add missing columns to employees table
ALTER TABLE employees ADD COLUMN education_file TEXT COMMENT 'JSON array of education document filenames';
ALTER TABLE employees ADD COLUMN employment_agreement TEXT COMMENT 'JSON array of employment agreement filenames';
ALTER TABLE employees ADD COLUMN photo VARCHAR(255) DEFAULT NULL COMMENT 'Employee photo filename';
ALTER TABLE employees ADD COLUMN guarantor_photo VARCHAR(255) DEFAULT NULL COMMENT 'Guarantor photo filename';
ALTER TABLE employees ADD COLUMN criminal_record_details TEXT DEFAULT NULL COMMENT 'Criminal record details';
ALTER TABLE employees ADD COLUMN national_id_details TEXT DEFAULT NULL COMMENT 'National ID details';
ALTER TABLE employees ADD COLUMN credit_status VARCHAR(50) DEFAULT 'good' COMMENT 'Credit status';
ALTER TABLE employees ADD COLUMN credit_details TEXT DEFAULT NULL COMMENT 'Credit details';
ALTER TABLE employees ADD COLUMN other_bank_name VARCHAR(100) DEFAULT NULL COMMENT 'Other bank name';
ALTER TABLE employees ADD COLUMN other_department TEXT DEFAULT NULL COMMENT 'Other department description';
ALTER TABLE employees ADD COLUMN other_job_level TEXT DEFAULT NULL COMMENT 'Other job level description';
ALTER TABLE employees ADD COLUMN other_marital_status VARCHAR(50) DEFAULT NULL COMMENT 'Other marital status description';
ALTER TABLE employees ADD COLUMN other_language VARCHAR(100) DEFAULT NULL COMMENT 'Other language spoken';
ALTER TABLE employees ADD COLUMN other_citizenship VARCHAR(100) DEFAULT NULL COMMENT 'Other citizenship description';
ALTER TABLE employees ADD COLUMN warranty_woreda VARCHAR(50) DEFAULT NULL COMMENT 'Warranty person woreda';
ALTER TABLE employees ADD COLUMN warranty_kebele VARCHAR(50) DEFAULT NULL COMMENT 'Warranty person kebele';
ALTER TABLE employees ADD COLUMN working_woreda VARCHAR(50) DEFAULT NULL COMMENT 'Working woreda';
ALTER TABLE employees ADD COLUMN working_kebele VARCHAR(50) DEFAULT NULL COMMENT 'Working kebele';
ALTER TABLE employees ADD COLUMN language VARCHAR(100) DEFAULT NULL COMMENT 'Languages spoken';
ALTER TABLE employees ADD COLUMN religion VARCHAR(50) DEFAULT NULL COMMENT 'Religion';

-- Verify table structure
DESCRIBE employees;
