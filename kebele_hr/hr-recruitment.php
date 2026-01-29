<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HealthFirst | Recruitment</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../style/style.css">
    <style>
        .recruitment-jobs {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .job-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
        }

        .job-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 15px;
        }

        .job-title {
            font-weight: 700;
            font-size: 1.1rem;
            color: #1e293b;
        }

        .job-department {
            font-size: 0.9rem;
            color: #64748b;
        }

        .job-type {
            background: #e0f2fe;
            color: #0369a1;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .job-details {
            display: flex;
            gap: 25px;
            margin-bottom: 15px;
            padding: 12px;
            background: #f8f9fa;
            border-radius: 8px;
        }

        .job-detail {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.9rem;
            color: #475569;
        }

        .job-detail i {
            color: #3498db;
        }

        .job-actions {
            display: flex;
            gap: 10px;
        }

        .job-action-btn {
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            font-size: 0.85rem;
        }

        .job-action-btn.view {
            background: #3498db;
            color: white;
        }

        .job-action-btn.view:hover {
            background: #2980b9;
        }

        .job-action-btn.edit {
            background: #27ae60;
            color: white;
        }

        .job-action-btn.edit:hover {
            background: #219a52;
        }

        .job-action-btn.delete {
            background: #e74c3c;
            color: white;
        }

        .job-action-btn.delete:hover {
            background: #c0392b;
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }

        .modal.show {
            display: flex;
        }

        .modal-content {
            background: white;
            border-radius: 16px;
            padding: 30px;
            max-width: 600px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
        }

        .modal-title {
            font-size: 1.4rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .modal-title i {
            color: #3498db;
        }

        .close-modal {
            float: right;
            font-size: 1.5rem;
            cursor: pointer;
            color: #94a3b8;
        }

        .close-modal:hover {
            color: #1e293b;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 15px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            font-weight: 600;
            color: #475569;
            margin-bottom: 8px;
            font-size: 0.9rem;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            font-size: 0.95rem;
            transition: all 0.3s;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #3498db;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
        }

        .form-group textarea {
            min-height: 100px;
            resize: vertical;
        }

        .form-actions {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            margin-top: 20px;
        }

        .submit-btn {
            padding: 12px 24px;
            background: linear-gradient(135deg, #3498db, #2980b9);
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(52, 152, 219, 0.3);
        }

        .cancel-btn {
            padding: 12px 24px;
            background: #f1f5f9;
            color: #64748b;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
        }

        .cancel-btn:hover {
            background: #e2e8f0;
        }

        .application-count {
            background: #fef3c7;
            color: #d97706;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .status-badge {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .status-open {
            background: #dcfce7;
            color: #16a34a;
        }

        .status-closed {
            background: #fee2e2;
            color: #dc2626;
        }

        .status-filled {
            background: #dbeafe;
            color: #2563eb;
        }
    </style>
</head>

<body>
    <div class="hr-container">
        <!-- Sidebar -->
        <?php include 'sidebar.php'; ?>

        <!-- Main Content -->
        <main class="main-content">
            <?php
            $page_title = "Recruitment Management";
            include 'navbar.php';
            ?>

            <!-- Content -->
            <div class="content">
                <!-- Open Positions Section -->
                <div class="hr-section">
                    <div class="hr-section-header">
                        <h2 class="hr-section-title">Open Positions</h2>
                        <div class="hr-section-actions">
                            <button class="section-action-btn" onclick="openPostJobModal()">
                                <i class="fas fa-plus"></i> Post New Job
                            </button>
                            <button class="section-action-btn" onclick="window.location.href='view_applications.php'">
                                <i class="fas fa-eye"></i> View Applications
                            </button>
                        </div>
                    </div>
                    <div class="hr-section-body">
                        <div class="recruitment-jobs" id="recruitmentJobsContainer">
                            <div style="text-align: center; padding: 40px;">
                                <i class="fas fa-spinner fa-spin" style="font-size: 2rem; color: #3498db;"></i>
                                <p style="margin-top: 10px; color: #6c757d;">Loading job postings...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Post Job Modal -->
    <div class="modal" id="postJobModal">
        <div class="modal-content">
            <span class="close-modal" onclick="closePostJobModal()">&times;</span>
            <h2 class="modal-title">
                <i class="fas fa-briefcase"></i> Post New Job Opening
            </h2>
            <form id="postJobForm">
                <div class="form-row">
                    <div class="form-group">
                        <label>Job Title *</label>
                        <input type="text" id="jobTitle" name="title" required placeholder="e.g., Senior Nurse">
                    </div>
                    <div class="form-group">
                        <label>Department *</label>
                        <select id="jobDepartment" name="department" required>
                            <option value="">Select Department</option>
                            <option value="medical">Medical</option>
                            <option value="administration">Administration</option>
                            <option value="technical">Technical</option>
                            <option value="support">Support</option>
                            <option value="emergency">Emergency</option>
                            <option value="pediatrics">Pediatrics</option>
                            <option value="laboratory">Laboratory</option>
                            <option value="pharmacy">Pharmacy</option>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Employment Type</label>
                        <select id="jobType" name="employment_type">
                            <option value="full-time">Full-Time</option>
                            <option value="part-time">Part-Time</option>
                            <option value="contract">Contract</option>
                            <option value="temporary">Temporary</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Application Deadline</label>
                        <input type="date" id="applicationDeadline" name="application_deadline">
                    </div>
                </div>
                <div class="form-group">
                    <label>Job Description *</label>
                    <textarea id="jobDescription" name="description" required
                        placeholder="Describe the role, responsibilities, and requirements..."></textarea>
                </div>
                <div class="form-group">
                    <label>Requirements & Qualifications</label>
                    <textarea id="jobRequirements" name="requirements"
                        placeholder="List required education, experience, and skills..."></textarea>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Salary Range</label>
                        <input type="text" id="salaryRange" name="salary_range" placeholder="e.g., 5000-8000 ETB">
                    </div>
                    <div class="form-group">
                        <label>Location</label>
                        <input type="text" id="jobLocation" name="location" placeholder="e.g., Main Hospital">
                    </div>
                </div>
                <div class="form-actions">
                    <button type="button" class="cancel-btn" onclick="closePostJobModal()">Cancel</button>
                    <button type="submit" class="submit-btn">Post Job</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Job Details Modal -->
    <div class="modal" id="jobDetailsModal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeJobDetailsModal()">&times;</span>
            <h2 class="modal-title" id="detailsTitle">
                <i class="fas fa-info-circle"></i> Job Details
            </h2>
            <div id="jobDetailsContent">
                <!-- Content populated by JS -->
            </div>
            <div class="form-actions">
                <button type="button" class="cancel-btn" onclick="closeJobDetailsModal()">Close</button>
                <button type="button" class="submit-btn" onclick="viewApplications()">View Applications</button>
            </div>
        </div>
    </div>

    <script>
        // Set default deadline to 30 days from now
        document.addEventListener('DOMContentLoaded', function () {
            const deadline = new Date();
            deadline.setDate(deadline.getDate() + 30);
            document.getElementById('applicationDeadline').value = deadline.toISOString().split('T')[0];

            loadJobPostings();
        });

        function loadJobPostings() {
            fetch('get_kebele_hr_jobs.php')
                .then(response => response.json())
                .then(data => {
                    const container = document.getElementById('recruitmentJobsContainer');
                    container.innerHTML = '';

                    if (data.length > 0) {
                        data.forEach(job => {
                            const statusClass = job.status === 'open' ? 'status-open' : (job.status === 'closed' ? 'status-closed' : 'status-filled');
                            const jobCard = document.createElement('div');
                            jobCard.className = 'job-card';
                            jobCard.innerHTML = `
                                <div class="job-header">
                                    <div>
                                        <div class="job-title">${job.title}</div>
                                        <div class="job-department">${job.department}</div>
                                    </div>
                                    <div>
                                        <span class="job-type">${job.employment_type}</span>
                                        <span class="status-badge ${statusClass}" style="margin-left: 8px; text-transform: capitalize;">${job.status}</span>
                                    </div>
                                </div>
                                <div class="job-details">
                                    <div class="job-detail">
                                        <i class="fas fa-map-marker-alt"></i>
                                        <span>${job.location || 'Not specified'}</span>
                                    </div>
                                    <div class="job-detail">
                                        <i class="fas fa-clock"></i>
                                        <span>Posted: ${new Date(job.posted_at).toLocaleDateString()}</span>
                                    </div>
                                    <div class="job-detail">
                                        <i class="fas fa-calendar-alt"></i>
                                        <span>Deadline: ${job.application_deadline ? new Date(job.application_deadline).toLocaleDateString() : 'Open'}</span>
                                    </div>
                                    <div class="job-detail">
                                        <i class="fas fa-money-bill"></i>
                                        <span>${job.salary_range || 'Negotiable'}</span>
                                    </div>
                                </div>
                                <div class="job-actions">
                                    <button class="job-action-btn view" onclick="viewJobDetails(${job.id})">
                                        <i class="fas fa-eye"></i> View Details
                                    </button>
                                    <button class="job-action-btn edit" onclick="editJob(${job.id})">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                    <button class="job-action-btn delete" onclick="deleteJob(${job.id}, '${job.title}')">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </div>
                            `;
                            container.appendChild(jobCard);
                        });
                    } else {
                        container.innerHTML = `
                            <div style="text-align: center; padding: 50px; color: #6c757d;">
                                <i class="fas fa-briefcase" style="font-size: 3rem; margin-bottom: 15px; opacity: 0.5;"></i>
                                <p>No job postings yet.</p>
                                <p>Click "Post New Job" to create your first opening.</p>
                            </div>
                        `;
                    }

                    // Update badge
                    const badge = document.getElementById('sidebarJobsBadge');
                    if (badge) badge.textContent = data.length;
                })
                .catch(error => {
                    console.error('Error loading job postings:', error);
                    document.getElementById('recruitmentJobsContainer').innerHTML = '<p style="text-align: center; padding: 40px; color: #e74c3c;">Error loading job postings. Please try again.</p>';
                });
        }

        function openPostJobModal() {
            document.getElementById('postJobModal').classList.add('show');
        }

        function closePostJobModal() {
            document.getElementById('postJobModal').classList.remove('show');
            document.getElementById('postJobForm').reset();
        }

        function viewJobDetails(jobId) {
            fetch('get_kebele_hr_jobs.php')
                .then(response => response.json())
                .then(data => {
                    const job = data.find(j => j.id == jobId);
                    if (!job) return;

                    document.getElementById('detailsTitle').innerHTML = `<i class="fas fa-info-circle"></i> ${job.title}`;
                    document.getElementById('jobDetailsContent').innerHTML = `
                        <div class="form-group">
                            <label>Department</label>
                            <p>${job.department}</p>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Employment Type</label>
                                <p>${job.employment_type}</p>
                            </div>
                            <div class="form-group">
                                <label>Location</label>
                                <p>${job.location || 'Not specified'}</p>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Salary Range</label>
                                <p>${job.salary_range || 'Negotiable'}</p>
                            </div>
                            <div class="form-group">
                                <label>Application Deadline</label>
                                <p>${job.application_deadline ? new Date(job.application_deadline).toLocaleDateString() : 'Open'}</p>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Job Description</label>
                            <p style="background: #f8f9fa; padding: 15px; border-radius: 8px;">${job.description || 'No description provided.'}</p>
                        </div>
                        <div class="form-group">
                            <label>Requirements & Qualifications</label>
                            <p style="background: #f8f9fa; padding: 15px; border-radius: 8px;">${job.requirements || 'No requirements specified.'}</p>
                        </div>
                        <div class="form-group">
                            <label>Status</label>
                            <p style="text-transform: capitalize; font-weight: 600;">${job.status}</p>
                        </div>
                    `;
                    document.getElementById('jobDetailsModal').classList.add('show');
                });
        }

        function closeJobDetailsModal() {
            document.getElementById('jobDetailsModal').classList.remove('show');
        }

        function editJob(jobId) {
            alert('Edit functionality - Job ID: ' + jobId);
            // Can be implemented to pre-fill the post job form
        }

        function deleteJob(jobId, jobTitle) {
            if (!confirm('Are you sure you want to delete the job posting: ' + jobTitle + '?')) return;

            const formData = new FormData();
            formData.append('job_id', jobId);

            fetch('recruitment_actions.php?action=delete_job', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('✅ Job posting deleted successfully!');
                        loadJobPostings();
                    } else {
                        alert('❌ Error: ' + (data.message || 'Failed to delete job posting'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('❌ Error deleting job posting');
                });
        }

        function viewApplications() {
            window.location.href = 'view_applications.php';
        }

        // Handle post job form submission
        document.getElementById('postJobForm').addEventListener('submit', function (e) {
            e.preventDefault();

            const formData = new FormData(this);
            formData.append('status', 'open');
            // Ensure posted_by is an integer
            formData.append('posted_by', parseInt(<?php echo $_SESSION["user_id"] ?? 1; ?>));

            fetch('recruitment_actions.php?action=post_job', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('✅ Job posted successfully!');
                        closePostJobModal();
                        loadJobPostings();
                    } else {
                        alert('❌ Error: ' + (data.message || 'Failed to post job'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('❌ Error posting job');
                });
        });

        // Close modal on outside click
        document.addEventListener('click', function (e) {
            if (e.target.classList.contains('modal')) {
                e.target.classList.remove('show');
            }
        });
    </script>
    <script src="scripts.js"></script>
</body>

</html>