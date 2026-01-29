<?php
session_start();
require_once dirname(__DIR__) . '/db.php';
$conn = getDBConnection();

header('Content-Type: application/json');

$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    case 'post_job':
        try {
            $title = $_POST['title'] ?? '';
            $department = $_POST['department'] ?? '';
            $description = $_POST['description'] ?? '';
            $requirements = $_POST['requirements'] ?? '';
            $employment_type = $_POST['employment_type'] ?? 'full-time';
            $salary_range = $_POST['salary_range'] ?? '';
            $location = $_POST['location'] ?? '';
            $application_deadline = $_POST['application_deadline'] ?? null;
            $status = $_POST['status'] ?? 'open';
            $posted_by = isset($_POST['posted_by']) ? (int) $_POST['posted_by'] : 1;
            $kebele = $_SESSION['kebele'] ?? 'Kebele 1';

            $sql = "INSERT INTO job_postings (title, department, description, requirements, employment_type, salary_range, location, application_deadline, status, posted_by, kebele) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssssssssis", $title, $department, $description, $requirements, $employment_type, $salary_range, $location, $application_deadline, $status, $posted_by, $kebele);

            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Job posted successfully!', 'job_id' => $conn->insert_id]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error posting job: ' . $stmt->error]);
            }
            $stmt->close();
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;

    case 'update_job':
        try {
            $job_id = $_POST['job_id'] ?? 0;
            $title = $_POST['title'] ?? '';
            $department = $_POST['department'] ?? '';
            $description = $_POST['description'] ?? '';
            $requirements = $_POST['requirements'] ?? '';
            $employment_type = $_POST['employment_type'] ?? 'full-time';
            $salary_range = $_POST['salary_range'] ?? '';
            $location = $_POST['location'] ?? '';
            $application_deadline = $_POST['application_deadline'] ?? null;
            $status = $_POST['status'] ?? 'open';

            $sql = "UPDATE job_postings SET title=?, department=?, description=?, requirements=?, employment_type=?, salary_range=?, location=?, application_deadline=?, status=? WHERE id=?";

            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssssssssi", $title, $department, $description, $requirements, $employment_type, $salary_range, $location, $application_deadline, $status, $job_id);

            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Job updated successfully!']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error updating job: ' . $stmt->error]);
            }
            $stmt->close();
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;

    case 'delete_job':
        try {
            $job_id = $_POST['job_id'] ?? 0;

            $sql = "DELETE FROM job_postings WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $job_id);

            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Job deleted successfully!']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error deleting job: ' . $stmt->error]);
            }
            $stmt->close();
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;

    case 'submit_application':
        try {
            $job_id = $_POST['job_id'] ?? 0;
            $applicant_name = $_POST['applicant_name'] ?? '';
            $email = $_POST['email'] ?? '';
            $phone = $_POST['phone'] ?? '';
            $cover_letter = $_POST['cover_letter'] ?? '';

            // Handle file upload
            $resume = '';
            if (isset($_FILES['resume']) && $_FILES['resume']['error'] == 0) {
                $upload_dir = dirname(__DIR__) . '/uploads/applications/';
                if (!is_dir($upload_dir))
                    mkdir($upload_dir, 0777, true);

                $ext = strtolower(pathinfo($_FILES['resume']['name'], PATHINFO_EXTENSION));
                $allowed = ['pdf', 'doc', 'docx'];
                if (in_array($ext, $allowed)) {
                    $filename = 'app_' . $job_id . '_' . time() . '.' . $ext;
                    if (move_uploaded_file($_FILES['resume']['tmp_name'], $upload_dir . $filename)) {
                        $resume = $filename;
                    }
                }
            }

            $sql = "INSERT INTO job_applications (job_id, applicant_name, email, phone, resume, cover_letter) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("isssss", $job_id, $applicant_name, $email, $phone, $resume, $cover_letter);

            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Application submitted successfully!']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error submitting application: ' . $stmt->error]);
            }
            $stmt->close();
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;

    case 'update_application_status':
        try {
            $application_id = $_POST['application_id'] ?? 0;
            $status = $_POST['status'] ?? 'pending';

            $sql = "UPDATE job_applications SET status = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("si", $status, $application_id);

            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Application status updated!']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error updating status: ' . $stmt->error]);
            }
            $stmt->close();
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}

$conn->close();
