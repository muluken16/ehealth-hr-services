<?php
require_once '../db.php';

echo "<h2>Database Connection Test</h2>";

try {
    $conn = getDBConnection();
    echo "✅ Database connection successful<br><br>";
    
    // Check employees table
    $result = $conn->query("SELECT COUNT(*) as count FROM employees");
    if ($result) {
        $row = $result->fetch_assoc();
        $employeeCount = (int)$row['count'];
        echo "📊 Total employees in database: <strong>$employeeCount</strong><br>";
        
        if ($employeeCount == 0) {
            echo "⚠️ No employees found. Creating sample data...<br>";
            
            // Create sample data
            $sampleEmployees = [
                ['HF-2024-001', 'John', 'Doe', 'male', '1985-03-15', 'Medical', 'Doctor', 'Senior', 'active', 'john.doe@health.gov.et', '2020-01-15', 15000],
                ['HF-2024-002', 'Mary', 'Smith', 'female', '1990-07-22', 'Administration', 'HR Manager', 'Manager', 'active', 'mary.smith@health.gov.et', '2019-03-10', 12000],
                ['HF-2024-003', 'Ahmed', 'Hassan', 'male', '1988-11-08', 'Technical', 'Lab Technician', 'Junior', 'active', 'ahmed.hassan@health.gov.et', '2021-06-01', 8000],
                ['HF-2024-004', 'Fatima', 'Ali', 'female', '1992-05-14', 'Medical', 'Nurse', 'Junior', 'on-leave', 'fatima.ali@health.gov.et', '2022-02-15', 7000],
                ['HF-2024-005', 'David', 'Wilson', 'male', '1987-09-30', 'Support', 'IT Specialist', 'Senior', 'active', 'david.wilson@health.gov.et', '2020-08-20', 10000],
                ['HF-2024-006', 'Sarah', 'Johnson', 'female', '1991-12-03', 'Medical', 'Pharmacist', 'Junior', 'active', 'sarah.johnson@health.gov.et', '2023-01-10', 9000],
                ['HF-2024-007', 'Michael', 'Brown', 'male', '1986-04-18', 'Administration', 'Finance Officer', 'Senior', 'active', 'michael.brown@health.gov.et', '2018-11-05', 11000],
                ['HF-2024-008', 'Aisha', 'Mohammed', 'female', '1993-08-25', 'Technical', 'Radiologist', 'Junior', 'active', 'aisha.mohammed@health.gov.et', '2022-09-12', 8500]
            ];
            
            $stmt = $conn->prepare("INSERT INTO employees (employee_id, first_name, last_name, gender, date_of_birth, department_assigned, position, job_level, status, email, join_date, salary, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
            
            $inserted = 0;
            foreach ($sampleEmployees as $emp) {
                if ($stmt->bind_param("sssssssssssd", $emp[0], $emp[1], $emp[2], $emp[3], $emp[4], $emp[5], $emp[6], $emp[7], $emp[8], $emp[9], $emp[10], $emp[11])) {
                    if ($stmt->execute()) {
                        $inserted++;
                    }
                }
            }
            
            $stmt->close();
            echo "✅ Inserted $inserted sample employees<br>";
            
            // Create sample leave requests
            $leaveStmt = $conn->prepare("INSERT INTO leave_requests (employee_id, leave_type, start_date, end_date, days_requested, reason, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
            
            $sampleLeaves = [
                ['HF-2024-001', 'annual', '2024-02-15', '2024-02-20', 5, 'Family vacation', 'pending'],
                ['HF-2024-003', 'sick', '2024-01-20', '2024-01-22', 2, 'Medical treatment', 'approved'],
                ['HF-2024-006', 'annual', '2024-03-01', '2024-03-05', 4, 'Personal time', 'pending']
            ];
            
            $leaveInserted = 0;
            foreach ($sampleLeaves as $leave) {
                if ($leaveStmt->bind_param("ssssisss", $leave[0], $leave[1], $leave[2], $leave[3], $leave[4], $leave[5], $leave[6])) {
                    if ($leaveStmt->execute()) {
                        $leaveInserted++;
                    }
                }
            }
            
            $leaveStmt->close();
            echo "✅ Inserted $leaveInserted sample leave requests<br>";
            
            // Create sample job postings
            $jobStmt = $conn->prepare("INSERT INTO job_postings (title, department, description, requirements, employment_type, status, posted_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
            
            $sampleJobs = [
                ['Senior Nurse', 'Medical', 'Experienced nurse for medical department', 'BSc Nursing, 3+ years experience', 'full-time', 'open'],
                ['Lab Assistant', 'Technical', 'Laboratory assistant position', 'Diploma in Medical Laboratory', 'full-time', 'open'],
                ['Administrative Assistant', 'Administration', 'General administrative support', 'Diploma, Computer skills', 'part-time', 'open']
            ];
            
            $jobInserted = 0;
            foreach ($sampleJobs as $job) {
                if ($jobStmt->bind_param("ssssss", $job[0], $job[1], $job[2], $job[3], $job[4], $job[5])) {
                    if ($jobStmt->execute()) {
                        $jobInserted++;
                    }
                }
            }
            
            $jobStmt->close();
            echo "✅ Inserted $jobInserted sample job postings<br>";
        }
        
        // Test chart data endpoints
        echo "<br><h3>Testing Chart Data Endpoints:</h3>";
        
        $endpoints = [
            'get_gender_stats.php' => 'Gender Statistics',
            'get_academic_stats.php' => 'Academic Statistics', 
            'get_department_stats.php' => 'Department Statistics',
            'get_job_level_stats.php' => 'Job Level Statistics',
            'get_status_stats.php' => 'Status Statistics',
            'get_hr_stats.php' => 'HR Statistics'
        ];
        
        foreach ($endpoints as $file => $name) {
            if (file_exists($file)) {
                echo "✅ $name endpoint exists<br>";
            } else {
                echo "❌ $name endpoint missing<br>";
            }
        }
        
    } else {
        echo "❌ Could not query employees table<br>";
    }
    
} catch (Exception $e) {
    echo "❌ Database error: " . $e->getMessage() . "<br>";
}

echo "<br><a href='wereda_hr_dashboard.php'>← Back to Dashboard</a>";
?>