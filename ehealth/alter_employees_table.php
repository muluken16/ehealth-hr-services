<?php
include 'db.php';

$conn = getDBConnection();

// Array of ALTER statements
$alter_queries = [
    "ALTER TABLE employees ADD COLUMN other_bank_name VARCHAR(100) AFTER bank_name",
    "ALTER TABLE employees ADD COLUMN person_relationship VARCHAR(50) AFTER person_name",
    "ALTER TABLE employees ADD COLUMN warranty_region VARCHAR(50) AFTER person_relationship",
    "ALTER TABLE employees ADD COLUMN warranty_zone VARCHAR(50) AFTER warranty_region",
    "ALTER TABLE employees ADD COLUMN warranty_email VARCHAR(100) AFTER warranty_kebele",
    "ALTER TABLE employees ADD COLUMN warranty_amount DECIMAL(10,2) AFTER warranty_type",
    "ALTER TABLE employees ADD COLUMN warranty_address TEXT AFTER warranty_amount",
    "ALTER TABLE employees ADD COLUMN warranty_start_date DATE AFTER warranty_address",
    "ALTER TABLE employees ADD COLUMN warranty_end_date DATE AFTER warranty_start_date",
    "ALTER TABLE employees ADD COLUMN warranty_notes TEXT AFTER warranty_end_date",
    "ALTER TABLE employees ADD COLUMN criminal_type VARCHAR(50) AFTER criminal_status",
    "ALTER TABLE employees ADD COLUMN criminal_date DATE AFTER criminal_type",
    "ALTER TABLE employees ADD COLUMN criminal_location VARCHAR(100) AFTER criminal_date",
    "ALTER TABLE employees ADD COLUMN criminal_court VARCHAR(100) AFTER criminal_location",
    "ALTER TABLE employees ADD COLUMN criminal_description TEXT AFTER criminal_court",
    "ALTER TABLE employees ADD COLUMN criminal_sentence TEXT AFTER criminal_description",
    "ALTER TABLE employees ADD COLUMN criminal_status_current VARCHAR(20) AFTER criminal_sentence",
    "ALTER TABLE employees ADD COLUMN criminal_additional_docs VARCHAR(255) AFTER criminal_file",
    "ALTER TABLE employees ADD COLUMN criminal_notes TEXT AFTER criminal_additional_docs",
    "ALTER TABLE employees ADD COLUMN loan_type VARCHAR(50) AFTER loan_status",
    "ALTER TABLE employees ADD COLUMN loan_amount DECIMAL(10,2) AFTER loan_type",
    "ALTER TABLE employees ADD COLUMN loan_lender VARCHAR(100) AFTER loan_amount",
    "ALTER TABLE employees ADD COLUMN loan_account VARCHAR(100) AFTER loan_lender",
    "ALTER TABLE employees ADD COLUMN loan_start_date DATE AFTER loan_account",
    "ALTER TABLE employees ADD COLUMN loan_end_date DATE AFTER loan_start_date",
    "ALTER TABLE employees ADD COLUMN monthly_payment DECIMAL(10,2) AFTER loan_end_date",
    "ALTER TABLE employees ADD COLUMN remaining_balance DECIMAL(10,2) AFTER monthly_payment",
    "ALTER TABLE employees ADD COLUMN loan_status_current VARCHAR(20) AFTER remaining_balance",
    "ALTER TABLE employees ADD COLUMN loan_collateral TEXT AFTER loan_status_current",
    "ALTER TABLE employees ADD COLUMN loan_purpose TEXT AFTER loan_collateral",
    "ALTER TABLE employees ADD COLUMN loan_payment_proof VARCHAR(255) AFTER loan_file",
    "ALTER TABLE employees ADD COLUMN loan_notes TEXT AFTER loan_payment_proof",
    "ALTER TABLE employees ADD COLUMN leave_type VARCHAR(50) AFTER leave_request",
    "ALTER TABLE employees ADD COLUMN leave_duration INT AFTER leave_type",
    "ALTER TABLE employees ADD COLUMN leave_start_date DATE AFTER leave_duration",
    "ALTER TABLE employees ADD COLUMN leave_end_date DATE AFTER leave_start_date",
    "ALTER TABLE employees ADD COLUMN leave_reason TEXT AFTER leave_end_date",
    "ALTER TABLE employees ADD COLUMN leave_contact VARCHAR(100) AFTER leave_reason",
    "ALTER TABLE employees ADD COLUMN leave_supervisor VARCHAR(100) AFTER leave_contact",
    "ALTER TABLE employees ADD COLUMN leave_address TEXT AFTER leave_supervisor",
    "ALTER TABLE employees ADD COLUMN leave_medical_cert VARCHAR(255) AFTER leave_address",
    "ALTER TABLE employees ADD COLUMN leave_supporting_docs VARCHAR(255) AFTER leave_medical_cert",
    "ALTER TABLE employees ADD COLUMN leave_notes TEXT AFTER leave_supporting_docs",
    "ALTER TABLE employees ADD COLUMN language VARCHAR(50) AFTER emergency_contact",
    "ALTER TABLE employees ADD COLUMN other_language VARCHAR(50) AFTER language"
];

foreach ($alter_queries as $sql) {
    if ($conn->query($sql) === TRUE) {
        echo "✅ Column added successfully: " . substr($sql, strpos($sql, 'ADD COLUMN') + 11) . "<br>";
    } else {
        echo "❌ Error adding column: " . $conn->error . "<br>";
    }
}

$conn->close();
echo "Employees table alteration completed.";
?>