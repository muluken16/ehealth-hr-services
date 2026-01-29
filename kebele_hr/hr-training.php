<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HealthFirst | Training</title>
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
                $page_title = "Training Management";
                include 'navbar.php'; 
            ?>

            <!-- Content -->
            <div class="content">
                <!-- Training Programs Section -->
                <div class="hr-section">
                    <div class="hr-section-header">
                        <h2 class="hr-section-title">Training Programs</h2>
                        <div class="hr-section-actions">
                            <button class="section-action-btn">
                                <i class="fas fa-plus"></i> Schedule Training
                            </button>
                            <button class="section-action-btn">
                                <i class="fas fa-history"></i> Past Trainings
                            </button>
                        </div>
                    </div>
                    <div class="hr-section-body">
                        <div class="training-courses" id="trainingCoursesContainer">
                            <!-- Training courses will be loaded here -->
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Load training courses on page load
        window.addEventListener('load', function() {
            fetch('get_kebele_hr_trainings.php')
                .then(response => response.json())
                .then(data => {
                    const container = document.getElementById('trainingCoursesContainer');
                    container.innerHTML = '';

                    if (data.length > 0) {
                        data.forEach(training => {
                            const statusClass = training.status === 'upcoming' ? 'upcoming' : (training.status === 'ongoing' ? 'ongoing' : 'completed');
                            const statusText = training.status === 'upcoming' ? 'Upcoming' : (training.status === 'ongoing' ? 'Ongoing' : 'Completed');

                            const trainingCard = document.createElement('div');
                            trainingCard.className = 'training-card';
                            trainingCard.innerHTML = `
                                <div class="training-header">
                                    <div>
                                        <div class="training-title">${training.title}</div>
                                        <div class="training-category">${training.category}</div>
                                    </div>
                                    <div class="training-status ${statusClass}">${statusText}</div>
                                </div>
                                <div class="training-details">
                                    <div class="training-detail">
                                        <i class="fas fa-calendar-alt"></i>
                                        <span>Date: ${new Date(training.scheduled_date).toLocaleDateString()}</span>
                                    </div>
                                    <div class="training-detail">
                                        <i class="fas fa-clock"></i>
                                        <span>Time: ${training.duration}</span>
                                    </div>
                                    <div class="training-detail">
                                        <i class="fas fa-map-marker-alt"></i>
                                        <span>Venue: ${training.venue}</span>
                                    </div>
                                    <div class="training-detail">
                                        <i class="fas fa-user-tie"></i>
                                        <span>Trainer: ${training.trainer}</span>
                                    </div>
                                </div>
                                <div class="training-participants">
                                    <span>Participants: ${training.participants_count}</span>
                                    <div class="participant-avatars">
                                        <div class="participant-avatar">A</div>
                                        <div class="participant-avatar">B</div>
                                        <div class="participant-avatar">C</div>
                                        <div class="participant-avatar">+${training.participants_count - 3}</div>
                                    </div>
                                </div>
                            `;
                            container.appendChild(trainingCard);
                        });
                    } else {
                        container.innerHTML = '<p>No training programs scheduled.</p>';
                    }

                    // Update badge
                    document.getElementById('trainingBadge').textContent = data.length;
                })
                .catch(error => console.error('Error loading training courses:', error));
        });

    </script>
    <script src="scripts.js"></script>
</body>
</html>