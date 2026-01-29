<?php
session_start();

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']);
$job_id = isset($_GET['job_id']) ? (int) $_GET['job_id'] : 0;

// If not logged in and no job_id, redirect to login
if (!$isLoggedIn && $job_id === 0) {
    header('Location: ../login_ui.php');
    exit;
}

// If not logged in, they'll see the application form
$page_title = $isLoggedIn ? "Job Applications" : "Submit Application";
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HealthFirst |
        <?php echo $page_title; ?>
    </title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../style/style.css">
    <style>
        .application-form {
            background: white;
            border-radius: 16px;
            padding: 30px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            max-width: 600px;
            margin: 0 auto;
        }

        .application-form h3 {
            color: #1e293b;
            margin-bottom: 20px;
            font-size: 1.4rem;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-weight: 600;
            color: #475569;
            margin-bottom: 8px;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s;
        }

        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #3498db;
            box-shadow: 0 0 0 4px rgba(52, 152, 219, 0.1);
        }

        .submit-application-btn {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #3498db, #2980b9);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }

        .submit-application-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(52, 152, 219, 0.4);
        }

        .external-notice {
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 25px;
            color: #1e40af;
        }

        .job-info-banner {
            background: linear-gradient(135deg, #1a5270, #2c7da0);
            color: white;
            padding: 25px;
            border-radius: 12px;
            margin-bottom: 30px;
        }

        .job-info-banner h2 {
            margin: 0 0 10px 0;
            font-size: 1.5rem;
        }

        .job-info-banner p {
            margin: 0;
            opacity: 0.9;
        }

        .applications-container {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .application-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
        }

        .application-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 15px;
        }

        .applicant-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .applicant-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: linear-gradient(135deg, #3498db, #2980b9);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 1.2rem;
        }

        .applicant-name {
            font-weight: 700;
            font-size: 1.1rem;
            color: #1e293b;
        }

        .applicant-contact {
            font-size: 0.9rem;
            color: #64748b;
        }

        .job-badge {
            background: #e0f2fe;
            color: #0369a1;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .application-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 15px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
        }

        .detail-item {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .detail-label {
            font-size: 0.8rem;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .detail-value {
            font-weight: 600;
            color: #1e293b;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .status-pending {
            background: #fef3c7;
            color: #d97706;
        }

        .status-reviewed {
            background: #dbeafe;
            color: #2563eb;
        }

        .status-shortlisted {
            background: #dcfce7;
            color: #16a34a;
        }

        .status-rejected {
            background: #fee2e2;
            color: #dc2626;
        }

        .status-hired {
            background: #f3e8ff;
            color: #9333ea;
        }

        .application-actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #e2e8f0;
        }

        .action-btn {
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            font-size: 0.85rem;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .action-btn.view {
            background: #3498db;
            color: white;
        }

        .action-btn.view:hover {
            background: #2980b9;
        }

        .action-btn.approve {
            background: #27ae60;
            color: white;
        }

        .action-btn.approve:hover {
            background: #219a52;
        }

        .action-btn.reject {
            background: #e74c3c;
            color: white;
        }

        .action-btn.reject:hover {
            background: #c0392b;
        }

        .no-applications {
            text-align: center;
            padding: 50px;
            color: #6c757d;
        }

        .no-applications i {
            font-size: 4rem;
            margin-bottom: 20px;
            opacity: 0.5;
        }

        .filter-bar {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
            padding: 15px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .filter-bar select {
            padding: 10px 15px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            font-size: 0.9rem;
            min-width: 150px;
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
            $page_title = "Job Applications";
            include 'navbar.php';
            ?>

            <!-- Content -->
            <div class="content">
                <?php if (!$isLoggedIn && $job_id > 0): ?>
                    <!-- Application Form for External Users -->
                    <div class="application-form">
                        <div class="job-info-banner">
                            <h2 id="jobTitle">Loading...</h2>
                            <p id="jobDept"></p>
                        </div>

                        <div class="external-notice">
                            <i class="fas fa-info-circle"></i>
                            <strong>Submit Your Application</strong>
                            <p style="margin-top: 5px; font-size: 0.9rem;">Please fill out the form below to apply for this
                                position.</p>
                        </div>

                        <form id="applicationForm" enctype="multipart/form-data">
                            <input type="hidden" name="job_id" value="<?php echo $job_id; ?>">

                            <div class="form-group">
                                <label>Full Name *</label>
                                <input type="text" name="applicant_name" required placeholder="Enter your full name">
                            </div>

                            <div class="form-group">
                                <label>Email Address *</label>
                                <input type="email" name="email" required placeholder="your@email.com">
                            </div>

                            <div class="form-group">
                                <label>Phone Number</label>
                                <input type="tel" name="phone" placeholder="+251 9XX XXX XXX">
                            </div>

                            <div class="form-group">
                                <label>Cover Letter</label>
                                <textarea name="cover_letter" rows="5"
                                    placeholder="Tell us why you're interested in this position..."></textarea>
                            </div>

                            <div class="form-group">
                                <label>Resume/CV * (PDF, DOC, DOCX)</label>
                                <input type="file" name="resume" accept=".pdf,.doc,.docx" required>
                            </div>

                            <button type="submit" class="submit-application-btn">
                                <i class="fas fa-paper-plane"></i> Submit Application
                            </button>
                        </form>

                        <div id="formMessage" class="message" style="margin-top: 20px;"></div>

                        <div
                            style="margin-top: 20px; text-align: center; padding-top: 20px; border-top: 1px solid #e2e8f0;">
                            <p style="color: #64748b; font-size: 0.9rem;">
                                Already have an account?
                                <a href="../login_ui.php?job_id=<?php echo $job_id; ?>"
                                    style="color: #3498db; font-weight: 600;">Login here</a>
                            </p>
                        </div>
                    </div>

                    <script>
                        // Load job details
                        fetch('get_kebele_hr_jobs.php')
                            .then(res => res.json())
                            .then(jobs => {
                                const job = jobs.find(j => j.id == <?php echo $job_id; ?>);
                                if (job) {
                                    document.getElementById('jobTitle').textContent = job.title;
                                    document.getElementById('jobDept').textContent = job.department + ' | ' + (job.location || 'HealthFirst');
                                } else {
                                    document.getElementById('jobTitle').textContent = 'Job Position';
                                }
                            })
                            .catch(err => console.error(err));

                        // Handle form submission
                        document.getElementById('applicationForm').addEventListener('submit', async function (e) {
                            e.preventDefault();

                            const formData = new FormData(this);
                            const submitBtn = this.querySelector('.submit-application-btn');
                            const messageDiv = document.getElementById('formMessage');

                            submitBtn.disabled = true;
                            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Submitting...';

                            try {
                                const response = await fetch('recruitment_actions.php?action=submit_application', {
                                    method: 'POST',
                                    body: formData
                                });

                                const data = await response.json();

                                if (data.success) {
                                    messageDiv.textContent = '✅ Application submitted successfully! We will contact you soon.';
                                    messageDiv.className = 'message success';
                                    messageDiv.style.display = 'block';
                                    this.reset();
                                    submitBtn.innerHTML = '<i class="fas fa-check"></i> Submitted';
                                } else {
                                    messageDiv.textContent = '❌ Error: ' + (data.message || 'Failed to submit application');
                                    messageDiv.className = 'message error';
                                    messageDiv.style.display = 'block';
                                    submitBtn.disabled = false;
                                    submitBtn.innerHTML = '<i class="fas fa-paper-plane"></i> Submit Application';
                                }
                            } catch (error) {
                                messageDiv.textContent = '❌ Network error. Please try again.';
                                messageDiv.className = 'message error';
                                messageDiv.style.display = 'block';
                                submitBtn.disabled = false;
                                submitBtn.innerHTML = '<i class="fas fa-paper-plane"></i> Submit Application';
                            }
                        });
                    </script>

                <?php else: ?>
                    <!-- HR View - Applications List -->
                    <div class="hr-section">
                        <div class="hr-section-header">
                            <h2 class="hr-section-title">Job Applications</h2>
                            <div class="hr-section-actions">
                                <button class="section-action-btn" onclick="window.location.href='hr-recruitment.php'">
                                    <i class="fas fa-arrow-left"></i> Back to Jobs
                                </button>
                            </div>
                        </div>

                        <div class="filter-bar">
                            <select id="jobFilter" onchange="loadApplications()">
                                <option value="">All Jobs</option>
                            </select>
                            <select id="statusFilter" onchange="loadApplications()">
                                <option value="">All Status</option>
                                <option value="pending">Pending</option>
                                <option value="reviewed">Reviewed</option>
                                <option value="shortlisted">Shortlisted</option>
                                <option value="rejected">Rejected</option>
                                <option value="hired">Hired</option>
                            </select>
                        </div>

                        <div class="hr-section-body">
                            <div class="applications-container" id="applicationsContainer">
                                <div style="text-align: center; padding: 40px;">
                                    <i class="fas fa-spinner fa-spin" style="font-size: 2rem; color: #3498db;"></i>
                                    <p style="margin-top: 10px; color: #6c757d;">Loading applications...</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                loadJobsForFilter();
                loadApplications();
            });

            function loadJobsForFilter() {
                fetch('get_kebele_hr_jobs.php')
                    .then(response => response.json())
                    .then(data => {
                        const select = document.getElementById('jobFilter');
                        data.forEach(job => {
                            const option = document.createElement('option');
                            option.value = job.id;
                            option.textContent = job.title;
                            select.appendChild(option);
                        });
                    });
            }

            function loadApplications() {
                const jobId = document.getElementById('jobFilter').value;
                const status = document.getElementById('statusFilter').value;

                let url = 'get_applications.php?';
                if (jobId) url += 'job_id=' + jobId + '&';
                if (status) url += 'status=' + status;

                fetch(url)
                    .then(response => response.json())
                    .then(data => {
                        const container = document.getElementById('applicationsContainer');
                        container.innerHTML = '';

                        if (data.length > 0) {
                            data.forEach(app => {
                                const initials = app.applicant_name ? app.applicant_name.split(' ').map(n => n[0]).join('').toUpperCase() : 'NA';
                                const statusClass = 'status-' + app.status;

                                const card = document.createElement('div');
                                card.className = 'application-card';
                                card.innerHTML = `
                                <div class="application-header">
                                    <div class="applicant-info">
                                        <div class="applicant-avatar">${initials}</div>
                                        <div>
                                            <div class="applicant-name">${app.applicant_name || 'Unknown'}</div>
                                            <div class="applicant-contact">${app.email || ''} • ${app.phone || ''}</div>
                                        </div>
                                    </div>
                                    <span class="job-badge">${app.job_title || 'Job #' + app.job_id}</span>
                                </div>
                                <div class="application-details">
                                    <div class="detail-item">
                                        <span class="detail-label">Applied On</span>
                                        <span class="detail-value">${new Date(app.applied_at).toLocaleDateString()}</span>
                                    </div>
                                    <div class="detail-item">
                                        <span class="detail-label">Status</span>
                                        <span class="status-badge ${statusClass}" style="text-transform: capitalize;">${app.status}</span>
                                    </div>
                                    <div class="detail-item">
                                        <span class="detail-label">Resume</span>
                                        <span class="detail-value">${app.resume ? '<a href="../uploads/applications/' + app.resume + '" target="_blank"><i class="fas fa-file-pdf"></i> View</a>' : 'Not uploaded'}</span>
                                    </div>
                                </div>
                                ${app.cover_letter ? '<div style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 15px;"><strong>Cover Letter:</strong><br>' + app.cover_letter + '</div>' : ''}
                                <div class="application-actions">
                                    <button class="action-btn view" onclick="viewResume('${app.resume || ''}')">
                                        <i class="fas fa-file-pdf"></i> View Resume
                                    </button>
                                    <button class="action-btn approve" onclick="updateStatus(${app.id}, 'shortlisted')">
                                        <i class="fas fa-check"></i> Shortlist
                                    </button>
                                    <button class="action-btn reject" onclick="updateStatus(${app.id}, 'rejected')">
                                        <i class="fas fa-times"></i> Reject
                                    </button>
                                </div>
                            `;
                                container.appendChild(card);
                            });
                        } else {
                            container.innerHTML = `
                            <div class="no-applications">
                                <i class="fas fa-users"></i>
                                <h3>No Applications Found</h3>
                                <p>There are no job applications matching your filters.</p>
                            </div>
                        `;
                        }
                    })
                    .catch(error => {
                        console.error('Error loading applications:', error);
                        document.getElementById('applicationsContainer').innerHTML = '<p style="text-align: center; padding: 40px; color: #e74c3c;">Error loading applications. Please try again.</p>';
                    });
            }

            function viewResume(resume) {
                if (resume) {
                    window.open('../uploads/applications/' + resume, '_blank');
                } else {
                    alert('No resume uploaded for this application.');
                }
            }

            function updateStatus(applicationId, status) {
                if (!confirm('Change application status to "' + status + '"?')) return;

                const formData = new FormData();
                formData.append('application_id', applicationId);
                formData.append('status', status);

                fetch('recruitment_actions.php?action=update_application_status', {
                    method: 'POST',
                    body: formData
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('✅ Application status updated!');
                            loadApplications();
                        } else {
                            alert('❌ Error: ' + (data.message || 'Failed to update status'));
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('❌ Error updating status');
                    });
            }
        </script>
        <script src="scripts.js"></script>
    <?php endif; ?>
</body>

</html>