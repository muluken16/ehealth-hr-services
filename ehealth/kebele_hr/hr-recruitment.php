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
                            <button class="section-action-btn">
                                <i class="fas fa-plus"></i> Post New Job
                            </button>
                            <button class="section-action-btn">
                                <i class="fas fa-eye"></i> View Applications
                            </button>
                        </div>
                    </div>
                    <div class="hr-section-body">
                        <div class="recruitment-jobs" id="recruitmentJobsContainer">
                            <!-- Job postings will be loaded here -->
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Load job postings on page load
        window.addEventListener('load', function() {
            fetch('get_kebele_hr_jobs.php')
                .then(response => response.json())
                .then(data => {
                    const container = document.getElementById('recruitmentJobsContainer');
                    container.innerHTML = '';

                    if (data.length > 0) {
                        data.forEach(job => {
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
                                        <i class="fas fa-users"></i>
                                        <span>Applications: 0</span>
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
                        container.innerHTML = '<p>No open positions.</p>';
                    }

                    // Update badge
                    document.getElementById('recruitmentBadge').textContent = data.length;
                })
                .catch(error => console.error('Error loading job postings:', error));
        });

    </script>
    <script src="scripts.js"></script>
</body>
</html>