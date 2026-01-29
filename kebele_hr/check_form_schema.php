<?php
$conn = new mysqli("localhost", "root", "","ehealth");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$res = $conn->query("DESCRIBE employees");
$db_cols = [];
while($row = $res->fetch_assoc()) $db_cols[] = $row['Field'];

$form_content = file_get_contents('add_employee.php');
preg_match_all('/name="([^"]+)"/', $form_content, $matches);
$form_fields = array_unique($matches[1]);

echo "Comparing form fields with database columns...\n";
$missing = [];
foreach ($form_fields as $field) {
    if ($field === 'documents[]' || $field === 'education_files[]' || $field === 'employment_agreements[]') continue;
    
    // Normalize array names
    $clean_field = str_replace('[]', '', $field);
    
    // Some fields map to different columns
    $map = [
        'photo' => 'photo',
        'registration_date' => 'created_at', // Assuming
        'documents' => 'documents',
        'education_files' => 'education_file',
        'employment_agreements' => 'employment_agreement'
    ];
    
    $check_field = $map[$clean_field] ?? $clean_field;
    
    if (!in_array($check_field, $db_cols)) {
        $missing[] = $check_field;
    }
}

if (empty($missing)) {
    echo "No missing columns found in database.\n";
} else {
    echo "Missing columns: " . implode(", ", $missing) . "\n";
}

$conn->close();
?>
