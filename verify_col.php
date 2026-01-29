<?php
require_once 'db.php';
$conn = getDBConnection();
$cols = [
    'education_file TEXT',
    'employment_agreement TEXT',
    'guarantor_photo VARCHAR(255)'
];
foreach ($cols as $c) {
    list($name, $type) = explode(' ', $c, 2);
    $check = $conn->query("SHOW COLUMNS FROM employees LIKE '$name'");
    if ($check->num_rows == 0) {
        $conn->query("ALTER TABLE employees ADD COLUMN $name $type");
        echo "Added $name\n";
    } else {
        echo "$name already exists\n";
    }
}
echo "VERIFICATION:\n";
$r = $conn->query("DESCRIBE employees education_file");
print_r($r->fetch_assoc());
?>