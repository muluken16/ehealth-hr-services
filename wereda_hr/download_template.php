<?php
// Generate sample employee import template
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="employee_import_template.csv"');

$output = fopen('php://output', 'w');

// Add UTF-8 BOM for Excel compatibility
fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

// Write header row
fputcsv($output, [
    'First Name',
    'Middle Name',
    'Last Name',
    'Gender',
    'Email',
    'Phone Number',
    'Position',
    'Department Assigned',
    'Employment Type',
    'Salary',
    'Join Date',
    'Status',
    'Working Kebele',
    'Date of Birth',
    'Address'
]);

// Write sample data rows
fputcsv($output, [
    'Abebe',
    'Kebede',
    'Tesfaye',
    'male',
    'abebe.tesfaye@example.com',
    '+251911234567',
    'Senior Nurse',
    'Outpatient Department (OPD)',
    'full-time',
    '15000',
    '2024-01-15',
    'active',
    'Kebele 01',
    '1990-05-20',
    'Addis Ababa, Bole'
]);

fputcsv($output, [
    'Tigist',
    'Hailu',
    'Alemayehu',
    'female',
    'tigist.alemayehu@example.com',
    '+251922345678',
    'Laboratory Technician',
    'Laboratory',
    'contract',
    '12000',
    '2024-02-01',
    'active',
    'Kebele 02',
    '1992-08-15',
    'Addis Ababa, Kirkos'
]);

fputcsv($output, [
    'Dawit',
    '',
    'Negash',
    'male',
    'dawit.negash@example.com',
    '+251933456789',
    'Pharmacist',
    'Pharmacy',
    'full-time',
    '18000',
    '2024-03-10',
    'active',
    'Kebele 03',
    '1988-12-05',
    'Addis Ababa, Yeka'
]);

fclose($output);
exit;
