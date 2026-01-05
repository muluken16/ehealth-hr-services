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
    <title>HealthFirst | Training Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../style/style.css">
</head>
<body>
    <div class="hr-container">
        <?php include 'sidebar.php'; ?>

        <?php
        $page_title = 'Training Management';
        include 'navbar.php';
        ?>

        <main class="hr-main">
            <!-- Training Content -->
            <div class="hr-dashboard">
                <div class="hr-section">
                    <div class="hr-section-header">
                        <h2 class="hr-section-title">Training Programs</h2>
                        <div class="hr-section-actions">
                            <button class="section-action-btn" id="scheduleTrainingBtn">
                                <i class="fas fa-plus"></i> Schedule Training
                            </button>
                            <button class="section-action-btn">
                                <i class="fas fa-history"></i> Past Trainings
                            </button>
                        </div>
                    </div>
                    <div class="hr-section-body">
                        <div class="training-courses" id="trainingList">
                            <!-- Training sessions will be loaded here -->
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Schedule Training Modal -->
    <div class="modal" id="scheduleTrainingModal">
        <div class="modal-content">
            <span class="close-modal" id="closeTrainingModal">&times;</span>
            <h2 class="modal-title">Schedule Training Session</h2>

            <form id="trainingForm">
                <div class="form-row">
                    <div class="form-group">
                        <label for="trainingTitle">Training Title *</label>
                        <input type="text" id="trainingTitle" required>
                    </div>
                    <div class="form-group">
                        <label for="trainingTrainer">Trainer</label>
                        <input type="text" id="trainingTrainer">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="trainingDate">Date *</label>
                        <input type="date" id="trainingDate" required>
                    </div>
                    <div class="form-group">
                        <label for="trainingStartTime">Start Time</label>
                        <input type="time" id="trainingStartTime">
                    </div>
                    <div class="form-group">
                        <label for="trainingEndTime">End Time</label>
                        <input type="time" id="trainingEndTime">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="trainingVenue">Venue</label>
                        <input type="text" id="trainingVenue">
                    </div>
                    <div class="form-group">
                        <label for="maxParticipants">Max Participants</label>
                        <input type="number" id="maxParticipants" min="1">
                    </div>
                </div>

                <div class="form-group">
                    <label for="trainingDescription">Description</label>
                    <textarea id="trainingDescription" placeholder="Training objectives, agenda, etc."></textarea>
                </div>

                <div class="form-actions">
                    <button type="submit" class="submit-btn">Schedule Training</button>
                    <button type="button" class="cancel-btn" id="cancelTrainingBtn">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script src="scripts.js"></script>
    <script>
        // Load training sessions
        function loadTrainingSessions() {
            const container = document.getElementById('trainingList');
            container.innerHTML = '<p>Loading training sessions...</p>';

            fetch('get_upcoming_trainings.php')
                .then(response => response.json())
                .then(data => {
                    container.innerHTML = '';
                    if (data.success && data.trainings.length > 0) {
                        data.trainings.forEach(session => {
                            const card = document.createElement('div');
                            card.className = 'job-card'; // Reuse job-card style for consistency
                            card.innerHTML = `
                                <div class="job-header">
                                    <div>
                                        <div class="job-title">${session.title}</div>
                                        <div class="job-department">Trainer: ${session.trainer}</div>
                                    </div>
                                    <div class="job-type">${session.status || 'Scheduled'}</div>
                                </div>
                                <div class="job-details">
                                    <div class="job-detail">
                                        <i class="fas fa-calendar"></i>
                                        <span>${new Date(session.session_date).toLocaleDateString()}</span>
                                    </div>
                                    <div class="job-detail">
                                        <i class="fas fa-clock"></i>
                                        <span>${session.start_time} - ${session.end_time}</span>
                                    </div>
                                    <div class="job-detail">
                                        <i class="fas fa-map-marker-alt"></i>
                                        <span>${session.venue}</span>
                                    </div>
                                </div>
                                <p style="margin-top:10px; color:#555;">${session.description}</p>
                            `;
                            container.appendChild(card);
                        });
                    } else {
                        container.innerHTML = '<p>No upcoming trainings scheduled.</p>';
                    }
                })
                .catch(error => {
                    console.error('Error loading trainings:', error);
                    container.innerHTML = '<p>Error loading training sessions.</p>';
                });
        }

        // Schedule Training Button
        document.getElementById('scheduleTrainingBtn').addEventListener('click', () => {
            document.getElementById('scheduleTrainingModal').style.display = 'block';
        });

        // Close modal
        document.getElementById('closeTrainingModal').addEventListener('click', () => {
            document.getElementById('scheduleTrainingModal').style.display = 'none';
        });

        document.getElementById('cancelTrainingBtn').addEventListener('click', () => {
            document.getElementById('scheduleTrainingModal').style.display = 'none';
        });

        // Form submission
        document.getElementById('trainingForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = {
                title: document.getElementById('trainingTitle').value,
                description: document.getElementById('trainingDescription').value,
                trainer: document.getElementById('trainingTrainer').value,
                session_date: document.getElementById('trainingDate').value,
                start_time: document.getElementById('trainingStartTime').value,
                end_time: document.getElementById('trainingEndTime').value,
                venue: document.getElementById('trainingVenue').value,
                max_participants: document.getElementById('maxParticipants').value
            };

            fetch('schedule_training.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(formData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Training scheduled successfully!');
                    document.getElementById('scheduleTrainingModal').style.display = 'none';
                    document.getElementById('trainingForm').reset();
                    loadTrainingSessions();
                } else {
                    alert('Error scheduling training: ' + data.message);
                }
            })
            .catch(error => console.error('Error:', error));
        });

        // Load trainings on page load
        document.addEventListener('DOMContentLoaded', function() {
            loadTrainingSessions();
        });
    </script>
</body>
</html>