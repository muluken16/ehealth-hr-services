<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'wereda_hr') {
    header('Location: ../index.html');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HealthFirst | Recruitment</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../style/style.css">
</head>
<body>
    <div class="hr-container">
        <?php include 'sidebar.php'; ?>

        <?php
        $page_title = 'Recruitment Management';
        include 'navbar.php';
        ?>

        <main class="hr-main">
            <!-- Recruitment Content -->
            <div class="hr-dashboard">
                <div class="hr-section">
                    <div class="hr-section-header">
                        <h2 class="hr-section-title">Open Positions</h2>
                        <div class="hr-section-actions">
                            <button class="section-action-btn" id="postJobBtn">
                                <i class="fas fa-plus"></i> Post New Job
                            </button>
                            <button class="section-action-btn">
                                <i class="fas fa-eye"></i> View Applications
                            </button>
                        </div>
                    </div>
                    <div class="hr-section-body">
                        <div class="recruitment-jobs" id="jobListings">
                            <!-- Job listings will be loaded here -->
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Post Job Modal -->
    <div class="modal" id="postJobModal">
        <div class="modal-content">
            <span class="close-modal" id="closeJobModal">&times;</span>
            <h2 class="modal-title">Post New Job Opening</h2>

            <form id="jobForm">
                <div class="form-row">
                    <div class="form-group">
                        <label for="jobTitle">Job Title *</label>
                        <input type="text" id="jobTitle" required>
                    </div>
                    <div class="form-group">
                        <label for="jobDepartment">Department *</label>
                        <select id="jobDepartment" required>
                            <option value="">Select Department</option>
                            <option value="medical">Medical</option>
                            <option value="administration">Administration</option>
                            <option value="technical">Technical</option>
                            <option value="support">Support</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="jobType">Employment Type</label>
                        <select id="jobType">
                            <option value="full-time">Full-time</option>
                            <option value="part-time">Part-time</option>
                            <option value="contract">Contract</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="jobLocation">Location</label>
                        <input type="text" id="jobLocation">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="salaryRange">Salary Range</label>
                        <input type="text" id="salaryRange" placeholder="e.g., $50,000 - $70,000">
                    </div>
                    <div class="form-group">
                        <label for="applicationDeadline">Application Deadline</label>
                        <input type="date" id="applicationDeadline">
                    </div>
                </div>

                <div class="form-group">
                    <label for="jobDescription">Job Description *</label>
                    <textarea id="jobDescription" required placeholder="Describe the role, responsibilities, and requirements..."></textarea>
                </div>

                <div class="form-group">
                    <label for="qualifications">Qualifications & Requirements</label>
                    <textarea id="qualifications" placeholder="List required education, experience, and skills..."></textarea>
                </div>

                <div class="form-actions">
                    <button type="submit" class="submit-btn">Post Job Opening</button>
                    <button type="button" class="cancel-btn" id="cancelJobBtn">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script src="scripts.js"></script>
    <script>
        // Load job postings
        function loadJobPostings() {
            // For now, show a message since we don't have a get_jobs.php
            const container = document.getElementById('jobListings');
            container.innerHTML = '<p>Loading job postings...</p>';

            // You can implement fetching jobs from database here
            // For now, just show existing jobs from the database
            fetch('get_job_postings.php')
                .then(response => response.json())
                .then(data => {
                    container.innerHTML = '';
                    if (data.jobs && data.jobs.length > 0) {
                        data.jobs.forEach(job => {
                            const jobCard = document.createElement('div');
                            jobCard.className = 'job-card';
                            jobCard.innerHTML = `
                                <div class="job-header">
                                    <div>
                                        <div class="job-title">${job.title}</div>
                                        <div class="job-department">${job.department}</div>
                                    </div>
                                    <div class="job-type">${job.employment_type}</div>
                                </div>
                                <div class="job-details">
                                    <div class="job-detail">
                                        <i class="fas fa-map-marker-alt"></i>
                                        <span>${job.location}</span>
                                    </div>
                                    <div class="job-detail">
                                        <i class="fas fa-clock"></i>
                                        <span>Posted: ${new Date(job.posted_at).toLocaleDateString()}</span>
                                    </div>
                                    <div class="job-detail">
                                        <i class="fas fa-dollar-sign"></i>
                                        <span>${job.salary_range}</span>
                                    </div>
                                </div>
                                <div class="job-actions">
                                    <button class="job-action-btn">View Details</button>
                                    <button class="job-action-btn">Edit</button>
                                </div>
                            `;
                            container.appendChild(jobCard);
                        });
                    } else {
                        container.innerHTML = '<p>No open positions available.</p>';
                    }
                })
                .catch(error => {
                    console.error('Error loading jobs:', error);
                    container.innerHTML = '<p>No open positions available.</p>';
                });
        }

        // Post Job Button
        document.getElementById('postJobBtn').addEventListener('click', () => {
            document.getElementById('postJobModal').style.display = 'block';
        });

        // Close modal
        document.getElementById('closeJobModal').addEventListener('click', () => {
            document.getElementById('postJobModal').style.display = 'none';
        });

        document.getElementById('cancelJobBtn').addEventListener('click', () => {
            document.getElementById('postJobModal').style.display = 'none';
        });

        // Form submission
        document.getElementById('jobForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = {
                title: document.getElementById('jobTitle').value,
                department: document.getElementById('jobDepartment').value,
                description: document.getElementById('jobDescription').value,
                requirements: document.getElementById('qualifications').value,
                salary_range: document.getElementById('salaryRange').value,
                location: document.getElementById('jobLocation').value,
                employment_type: document.getElementById('jobType').value,
                application_deadline: document.getElementById('applicationDeadline').value
            };

            fetch('post_job.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(formData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Job posted successfully!');
                    document.getElementById('postJobModal').style.display = 'none';
                    document.getElementById('jobForm').reset();
                    loadJobPostings();
                } else {
                    alert('Error posting job: ' + data.message);
                }
            })
            .catch(error => console.error('Error:', error));
        });

        // Load jobs on page load
        document.addEventListener('DOMContentLoaded', function() {
            loadJobPostings();
        });
    </script>
</body>
</html>