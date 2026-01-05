<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>🔍 File Upload Debug Test</h2>";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    echo "<h3>📤 POST Data Received</h3>";
    
    echo "<h4>🗂️ All $_FILES contents:</h4>";
    echo "<pre>";
    print_r($_FILES);
    echo "</pre>";
    
    echo "<h4>📋 Specific checks:</h4>";
    
    // Check documents array
    if (isset($_FILES['documents'])) {
        echo "✅ \$_FILES['documents'] exists<br>";
        echo "Type: " . gettype($_FILES['documents']) . "<br>";
        
        if (is_array($_FILES['documents'])) {
            echo "✅ \$_FILES['documents'] is array<br>";
            
            if (isset($_FILES['documents']['name'])) {
                echo "✅ \$_FILES['documents']['name'] exists<br>";
                echo "Name type: " . gettype($_FILES['documents']['name']) . "<br>";
                
                if (is_array($_FILES['documents']['name'])) {
                    echo "✅ \$_FILES['documents']['name'] is array<br>";
                    echo "Count: " . count($_FILES['documents']['name']) . "<br>";
                    
                    foreach ($_FILES['documents']['name'] as $i => $name) {
                        echo "File $i: $name (Error: " . $_FILES['documents']['error'][$i] . ")<br>";
                    }
                } else {
                    echo "❌ \$_FILES['documents']['name'] is not array: " . $_FILES['documents']['name'] . "<br>";
                }
            } else {
                echo "❌ \$_FILES['documents']['name'] does not exist<br>";
            }
        } else {
            echo "❌ \$_FILES['documents'] is not array<br>";
        }
    } else {
        echo "❌ \$_FILES['documents'] does not exist<br>";
    }
    
    // Check single files
    $single_files = ['scan_file', 'criminal_file', 'fin_scan', 'loan_file', 'leave_document'];
    foreach ($single_files as $file_field) {
        if (isset($_FILES[$file_field])) {
            echo "✅ \$_FILES['$file_field'] exists - " . $_FILES[$file_field]['name'] . " (Error: " . $_FILES[$file_field]['error'] . ")<br>";
        } else {
            echo "⚪ \$_FILES['$file_field'] not uploaded<br>";
        }
    }
    
    // Test upload directory
    $upload_dir = 'uploads/employees/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    echo "<h4>📁 Upload Directory Test:</h4>";
    echo "Directory: $upload_dir<br>";
    echo "Exists: " . (is_dir($upload_dir) ? 'Yes' : 'No') . "<br>";
    echo "Writable: " . (is_writable($upload_dir) ? 'Yes' : 'No') . "<br>";
    
} else {
    echo "<h3>📝 Test Form</h3>";
}
?>

<form method="POST" enctype="multipart/form-data" style="max-width: 600px; margin: 20px 0;">
    <div style="margin: 15px 0;">
        <label><strong>Multiple Documents (documents[]):</strong></label><br>
        <input type="file" name="documents[]" multiple accept="image/*,.pdf" style="width: 100%; padding: 8px;">
        <small>Select multiple files</small>
    </div>
    
    <div style="margin: 15px 0;">
        <label><strong>Single Scan File:</strong></label><br>
        <input type="file" name="scan_file" accept=".pdf,.jpg,.jpeg,.png" style="width: 100%; padding: 8px;">
    </div>
    
    <div style="margin: 15px 0;">
        <label><strong>Criminal File:</strong></label><br>
        <input type="file" name="criminal_file" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" style="width: 100%; padding: 8px;">
    </div>
    
    <div style="margin: 15px 0;">
        <label><strong>FIN Scan:</strong></label><br>
        <input type="file" name="fin_scan" accept="image/*,.pdf" style="width: 100%; padding: 8px;">
    </div>
    
    <div style="margin: 15px 0;">
        <label><strong>Loan File:</strong></label><br>
        <input type="file" name="loan_file" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" style="width: 100%; padding: 8px;">
    </div>
    
    <div style="margin: 15px 0;">
        <label><strong>Leave Document:</strong></label><br>
        <input type="file" name="leave_document" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" style="width: 100%; padding: 8px;">
    </div>
    
    <button type="submit" style="background: #007bff; color: white; padding: 12px 24px; border: none; border-radius: 4px; cursor: pointer;">
        Test Upload
    </button>
</form>

<p><a href="kebele_hr/add_employee.php">Back to Add Employee</a></p>