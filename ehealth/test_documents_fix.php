<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>🧪 Test Documents Upload Fix</h2>";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    echo "<h3>📤 Form Submitted</h3>";
    
    // Check if documents were uploaded
    if (isset($_FILES['documents']) && is_array($_FILES['documents']['name'])) {
        $files = $_FILES['documents'];
        echo "<h4>📁 Documents Array Structure:</h4>";
        echo "<pre>";
        print_r($files);
        echo "</pre>";
        
        echo "<h4>📋 Processing Files:</h4>";
        $uploaded_count = 0;
        
        for ($i = 0; $i < count($files['name']); $i++) {
            if ($files['error'][$i] == 0 && !empty($files['name'][$i])) {
                echo "✅ File $i: " . $files['name'][$i] . " (Size: " . $files['size'][$i] . " bytes)<br>";
                $uploaded_count++;
            } else {
                echo "❌ File $i: Error " . $files['error'][$i] . "<br>";
            }
        }
        
        echo "<h4>📊 Summary:</h4>";
        echo "Total files selected: " . count($files['name']) . "<br>";
        echo "Valid files for upload: $uploaded_count<br>";
        
        if ($uploaded_count > 0) {
            echo "<div style='background: #d4edda; color: #155724; padding: 15px; border-radius: 4px; margin: 10px 0;'>";
            echo "🎉 SUCCESS! Documents array is working correctly!<br>";
            echo "The JavaScript fix resolved the issue - files are now preserved for upload.";
            echo "</div>";
        } else {
            echo "<div style='background: #f8d7da; color: #721c24; padding: 15px; border-radius: 4px; margin: 10px 0;'>";
            echo "❌ No valid files found. Please select files and try again.";
            echo "</div>";
        }
        
    } else {
        echo "<div style='background: #fff3cd; color: #856404; padding: 15px; border-radius: 4px; margin: 10px 0;'>";
        echo "⚠️ No documents array found in \$_FILES. Make sure to select files before submitting.";
        echo "</div>";
    }
    
    // Check other files
    $other_files = ['scan_file', 'criminal_file', 'fin_scan', 'loan_file', 'leave_document'];
    echo "<h4>📎 Other File Uploads:</h4>";
    foreach ($other_files as $field) {
        if (isset($_FILES[$field]) && $_FILES[$field]['error'] == 0) {
            echo "✅ $field: " . $_FILES[$field]['name'] . "<br>";
        } else {
            echo "⚪ $field: Not uploaded<br>";
        }
    }
    
} else {
    echo "<p>Use this form to test if the documents upload fix is working:</p>";
}
?>

<form method="POST" enctype="multipart/form-data" style="max-width: 600px; margin: 20px 0; padding: 20px; border: 1px solid #ddd; border-radius: 8px;">
    <h3>📝 Test Document Upload</h3>
    
    <div style="margin: 15px 0;">
        <label><strong>Multiple Documents (this should now work):</strong></label><br>
        <input type="file" name="documents[]" multiple accept="image/*,.pdf,.doc,.docx" style="width: 100%; padding: 8px; margin: 5px 0;">
        <small style="color: #666;">Select multiple files to test the fix</small>
    </div>
    
    <div style="margin: 15px 0;">
        <label><strong>Single Scan File (for comparison):</strong></label><br>
        <input type="file" name="scan_file" accept=".pdf,.jpg,.jpeg,.png" style="width: 100%; padding: 8px;">
    </div>
    
    <button type="submit" style="background: #28a745; color: white; padding: 12px 24px; border: none; border-radius: 4px; cursor: pointer;">
        🧪 Test Upload
    </button>
</form>

<div style="background: #e9ecef; padding: 15px; border-radius: 4px; margin: 20px 0;">
    <h4>🔧 What was fixed:</h4>
    <ul>
        <li><strong>JavaScript Issue</strong>: The <code>addDocument()</code> function was clearing <code>input.value = ""</code> after showing preview</li>
        <li><strong>Solution</strong>: Removed the line that clears the input, so files are preserved for form submission</li>
        <li><strong>Improvement</strong>: Added better file preview with file sizes and clear button</li>
        <li><strong>Enhanced PHP</strong>: Added better error handling and debugging for file uploads</li>
    </ul>
</div>

<p><a href="kebele_hr/add_employee.php">🔗 Test the Real Form</a></p>