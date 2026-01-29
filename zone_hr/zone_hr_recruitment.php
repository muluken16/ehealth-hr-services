<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'zone_hr') {
    header('Location: ../index.html');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HealthFirst | Zone HR Recruitment</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../style/style.css">
</head>
<body>
    <div class="hr-container">
        <?php include 'sidebar.php'; ?>
        <?php
        $page_title = 'Recruitment';
        include 'navbar.php';
        ?>
            <div class="hr-dashboard">
                <div class="filters-section">
                    <div class="search-box"><input type="text" placeholder="Search jobs..." id="jobSearch"><i class="fas fa-search"></i></div>
                    <select class="filter-select" id="statusFilter">
                        <option value="">All Status</option>
                        <option value="open">Open</option>
                        <option value="closed">Closed</option>
                    </select>
                    <button class="add-btn" onclick="openModal('postJobModal')"><i class="fas fa-plus"></i> Post Job</button>
                </div>
                <div class="hr-section">
                    <div class="hr-section-header">
                        <h2 class="hr-section-title">Job Postings</h2>
                        <div class="hr-section-actions">
                            <button class="section-action-btn" onclick="refreshJobs()">
                                <i class="fas fa-sync-alt"></i> Refresh
                            </button>
                        </div>
                    </div>
                    <div class="hr-section-body">
                        <div class="table-container">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Job Title</th>
                                        <th>Department</th>
                                        <th>Location</th>
                                        <th>Posted Date</th>
                                        <th>Deadline</th>
                                        <th>Status</th>
                                        <th>Applications</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="jobTableBody">
                                    <!-- Job postings will be loaded here -->
                                </tbody>
                            </table>
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
            <h2 class="modal-title">Post New Job</h2>

            <form id="jobForm">
                <div class="form-row">
                    <div class="form-group">
                        <label for="jobTitle">Job Title *</label>
                        <input type="text" id="jobTitle" name="jobTitle" required>
                    </div>
                    <div class="form-group">
                        <label for="department">Department *</label>
                        <select id="department" name="department" required>
                            <option value="">Select Department</option>
                            <option value="medical">Medical</option>
                            <option value="administration">Administration</option>
                            <option value="technical">Technical</option>
                            <option value="support">Support</option>
                            <option value="finance">Finance</option>
                            <option value="hr">Human Resources</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="location">Location *</label>
                        <input type="text" id="location" name="location" required>
                    </div>
                    <div class="form-group">
                        <label for="employmentType">Employment Type *</label>
                        <select id="employmentType" name="employmentType" required>
                            <option value="full-time">Full-time</option>
                            <option value="part-time">Part-time</option>
                            <option value="contract">Contract</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="salaryRange">Salary Range</label>
                        <input type="text" id="salaryRange" name="salaryRange" placeholder="e.g., 15,000 - 25,000 ETB">
                    </div>
                    <div class="form-group">
                        <label for="deadline">Application Deadline *</label>
                        <input type="date" id="deadline" name="deadline" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="jobDescription">Job Description *</label>
                    <textarea id="jobDescription" name="jobDescription" rows="6" required></textarea>
                </div>

                <div class="form-group">
                    <label for="requirements">Requirements *</label>
                    <textarea id="requirements" name="requirements" rows="4" required></textarea>
                </div>

                <div class="form-actions">
                    <button type="submit" class="submit-btn">Post Job</button>
                    <button type="button" class="cancel-btn" id="cancelJobBtn">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script src="scripts.js"></script>
    <script>
        let allJobs = [];
        const jobSearch = document.getElementById('jobSearch');
        const statusFilter = document.getElementById('statusFilter');

        // Load jobs on page load
        document.addEventListener('DOMContentLoaded', function() {
            loadJobs();
        });

        // Load jobs from database
        function loadJobs() {
            fetch('get_jobs.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        allJobs = data.jobs;
                        displayJobs(allJobs);
                    } else {
                        console.error('Failed to load jobs:', data.message);
                    }
                })
                .catch(error => {
                    console.error('Error loading jobs:', error);
                });
        }

        // Display jobs in table
        function displayJobs(jobs) {
            const tbody = document.getElementById('jobTableBody');
            tbody.innerHTML = '';

            if (jobs.length === 0) {
                tbody.innerHTML = '<tr><td colspan="8" style="text-align: center; padding: 40px; color: var(--gray);">No job postings found</td></tr>';
                return;
            }

            jobs.forEach(job => {
                const postedDate = new Date(job.posted_date).toLocaleDateString('en-GB');
                const deadline = new Date(job.deadline).toLocaleDateString('en-GB');

                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${job.title}</td>
                    <td><span class="department-badge ${job.department}">${job.department}</span></td>
                    <td>${job.location}</td>
                    <td>${postedDate}</td>
                    <td>${deadline}</td>
                    <td><span class="status-badge ${job.status}">${job.status}</span></td>
                    <td>${job.applications || 0}</td>
                    <td>
                        <div class="action-buttons">
                            <button class="action-btn view" onclick="viewJob(${job.id})"><i class="fas fa-eye"></i></button>
                            <button class="action-btn edit" onclick="editJob(${job.id})"><i class="fas fa-edit"></i></button>
                            <button class="action-btn delete" onclick="deleteJob(${job.id}, '${job.title}')"><i class="fas fa-trash"></i></button>
                        </div>
                    </td>
                `;
                tbody.appendChild(row);
            });
        }

        // Filter functionality
        function filterJobs() {
            const searchTerm = jobSearch.value.toLowerCase();
            const statusValue = statusFilter.value;

            const filtered = allJobs.filter(job => {
                const matchesSearch = job.title.toLowerCase().includes(searchTerm) ||
                                    job.department.toLowerCase().includes(searchTerm);
                const matchesStatus = statusValue === '' || job.status === statusValue;

                return matchesSearch && matchesStatus;
            });

            displayJobs(filtered);
        }

        jobSearch.addEventListener('input', filterJobs);
        statusFilter.addEventListener('change', filterJobs);

        // Modal Functions
        function openModal(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.style.display = 'flex';
                document.body.style.overflow = 'hidden';
            }
        }

        function closeModal(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.style.display = 'none';
                document.body.style.overflow = 'auto';
            }
        }

        function closeAllModals() {
            const modals = document.querySelectorAll('.modal');
            modals.forEach(modal => {
                modal.style.display = 'none';
            });
            document.body.style.overflow = 'auto';
        }

        // Close modal when clicking outside
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('modal')) {
                closeAllModals();
            }
        });

        // Close modal buttons
        document.querySelectorAll('.close-modal').forEach(btn => {
            btn.addEventListener('click', () => {
                closeAllModals();
            });
        });

        // Cancel buttons
        document.querySelectorAll('.cancel-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                resetJobForm();
                closeAllModals();
            });
        });

        // Close modal with Escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                closeAllModals();
            }
        });

        // Form submission
        document.getElementById('jobForm').addEventListener('submit', (e) => {
            e.preventDefault();

            const formData = new FormData(e.target);

            fetch('post_job.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Job posted successfully!');
                    closeAllModals();
                    resetJobForm();
                    loadJobs();
                } else {
                    alert('Failed to post job: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while posting the job.');
            });
        });

        function resetJobForm() {
            document.getElementById('jobForm').reset();
        }

        // Job actions
        function viewJob(id) {
            const job = allJobs.find(j => j.id == id);
            if (job) {
                let details = `Job Details:\n\n`;
                details += `Title: ${job.title}\n`;
                details += `Department: ${job.department}\n`;
                details += `Location: ${job.location}\n`;
                details += `Employment Type: ${job.employment_type}\n`;
                details += `Salary Range: ${job.salary_range || 'Not specified'}\n`;
                details += `Deadline: ${new Date(job.deadline).toLocaleDateString()}\n`;
                details += `Status: ${job.status}\n`;
                details += `Applications: ${job.applications || 0}\n\n`;
                details += `Description:\n${job.description}\n\n`;
                details += `Requirements:\n${job.requirements}\n`;
                alert(details);
            }
        }

        function editJob(id) {
            alert('Edit functionality would be implemented here.');
        }

        function deleteJob(id, title) {
            if (confirm(`Are you sure you want to delete the job posting "${title}"?`)) {
                alert('Delete functionality would be implemented here.');
            }
        }

        // Refresh function
        function refreshJobs() {
            loadJobs();
        }
    </script>
</body>
</html>