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
    <title>HealthFirst | Zone HR Training</title>
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
            <div class="hr-dashboard">
                <div class="filters-section">
                    <div class="search-box"><input type="text" placeholder="Search trainings..." id="trainingSearch"><i class="fas fa-search"></i></div>
                    <select class="filter-select" id="statusFilter">
                        <option value="">All Status</option>
                        <option value="upcoming">Upcoming</option>
                        <option value="ongoing">Ongoing</option>
                        <option value="completed">Completed</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                    <button class="add-btn" onclick="openModal('scheduleTrainingModal')"><i class="fas fa-plus"></i> Schedule Training</button>
                </div>
                <div class="hr-section">
                    <div class="hr-section-header">
                        <h2 class="hr-section-title">Training Sessions</h2>
                        <div class="hr-section-actions">
                            <button class="section-action-btn" onclick="refreshTrainings()">
                                <i class="fas fa-sync-alt"></i> Refresh
                            </button>
                        </div>
                    </div>
                    <div class="hr-section-body">
                        <div class="table-container">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Training Title</th>
                                        <th>Trainer</th>
                                        <th>Date</th>
                                        <th>Time</th>
                                        <th>Location</th>
                                        <th>Participants</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="trainingTableBody">
                                    <!-- Training sessions will be loaded here -->
                                </tbody>
                            </table>
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
                        <input type="text" id="trainingTitle" name="trainingTitle" required>
                    </div>
                    <div class="form-group">
                        <label for="trainer">Trainer/Instructor *</label>
                        <input type="text" id="trainer" name="trainer" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="trainingDate">Date *</label>
                        <input type="date" id="trainingDate" name="trainingDate" required>
                    </div>
                    <div class="form-group">
                        <label for="startTime">Start Time *</label>
                        <input type="time" id="startTime" name="startTime" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="endTime">End Time *</label>
                        <input type="time" id="endTime" name="endTime" required>
                    </div>
                    <div class="form-group">
                        <label for="location">Location *</label>
                        <input type="text" id="location" name="location" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="maxParticipants">Max Participants</label>
                        <input type="number" id="maxParticipants" name="maxParticipants" min="1">
                    </div>
                    <div class="form-group">
                        <label for="department">Target Department</label>
                        <select id="department" name="department">
                            <option value="">All Departments</option>
                            <option value="medical">Medical</option>
                            <option value="administration">Administration</option>
                            <option value="technical">Technical</option>
                            <option value="support">Support</option>
                            <option value="finance">Finance</option>
                            <option value="hr">Human Resources</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="description">Description *</label>
                    <textarea id="description" name="description" rows="4" required></textarea>
                </div>

                <div class="form-group">
                    <label for="objectives">Learning Objectives</label>
                    <textarea id="objectives" name="objectives" rows="3"></textarea>
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
        let allTrainings = [];
        const trainingSearch = document.getElementById('trainingSearch');
        const statusFilter = document.getElementById('statusFilter');

        // Load trainings on page load
        document.addEventListener('DOMContentLoaded', function() {
            loadTrainings();
        });

        // Load trainings from database
        function loadTrainings() {
            fetch('get_trainings.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        allTrainings = data.trainings;
                        displayTrainings(allTrainings);
                    } else {
                        console.error('Failed to load trainings:', data.message);
                    }
                })
                .catch(error => {
                    console.error('Error loading trainings:', error);
                });
        }

        // Display trainings in table
        function displayTrainings(trainings) {
            const tbody = document.getElementById('trainingTableBody');
            tbody.innerHTML = '';

            if (trainings.length === 0) {
                tbody.innerHTML = '<tr><td colspan="8" style="text-align: center; padding: 40px; color: var(--gray);">No training sessions found</td></tr>';
                return;
            }

            trainings.forEach(training => {
                const trainingDate = new Date(training.date).toLocaleDateString('en-GB');
                const startTime = training.start_time.substring(0, 5);
                const endTime = training.end_time.substring(0, 5);

                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${training.title}</td>
                    <td>${training.trainer}</td>
                    <td>${trainingDate}</td>
                    <td>${startTime} - ${endTime}</td>
                    <td>${training.location}</td>
                    <td>${training.participants || 0}/${training.max_participants || 'Unlimited'}</td>
                    <td><span class="status-badge ${training.status}">${training.status}</span></td>
                    <td>
                        <div class="action-buttons">
                            <button class="action-btn view" onclick="viewTraining(${training.id})"><i class="fas fa-eye"></i></button>
                            <button class="action-btn edit" onclick="editTraining(${training.id})"><i class="fas fa-edit"></i></button>
                            <button class="action-btn delete" onclick="deleteTraining(${training.id}, '${training.title}')"><i class="fas fa-trash"></i></button>
                        </div>
                    </td>
                `;
                tbody.appendChild(row);
            });
        }

        // Filter functionality
        function filterTrainings() {
            const searchTerm = trainingSearch.value.toLowerCase();
            const statusValue = statusFilter.value;

            const filtered = allTrainings.filter(training => {
                const matchesSearch = training.title.toLowerCase().includes(searchTerm) ||
                                    training.trainer.toLowerCase().includes(searchTerm);
                const matchesStatus = statusValue === '' || training.status === statusValue;

                return matchesSearch && matchesStatus;
            });

            displayTrainings(filtered);
        }

        trainingSearch.addEventListener('input', filterTrainings);
        statusFilter.addEventListener('change', filterTrainings);

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
                resetTrainingForm();
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
        document.getElementById('trainingForm').addEventListener('submit', (e) => {
            e.preventDefault();

            const formData = new FormData(e.target);

            fetch('schedule_training.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Training scheduled successfully!');
                    closeAllModals();
                    resetTrainingForm();
                    loadTrainings();
                } else {
                    alert('Failed to schedule training: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while scheduling the training.');
            });
        });

        function resetTrainingForm() {
            document.getElementById('trainingForm').reset();
        }

        // Training actions
        function viewTraining(id) {
            const training = allTrainings.find(t => t.id == id);
            if (training) {
                let details = `Training Details:\n\n`;
                details += `Title: ${training.title}\n`;
                details += `Trainer: ${training.trainer}\n`;
                details += `Date: ${new Date(training.date).toLocaleDateString()}\n`;
                details += `Time: ${training.start_time} - ${training.end_time}\n`;
                details += `Location: ${training.location}\n`;
                details += `Max Participants: ${training.max_participants || 'Unlimited'}\n`;
                details += `Current Participants: ${training.participants || 0}\n`;
                details += `Department: ${training.department || 'All'}\n`;
                details += `Status: ${training.status}\n\n`;
                details += `Description:\n${training.description}\n\n`;
                details += `Objectives:\n${training.objectives || 'Not specified'}\n`;
                alert(details);
            }
        }

        function editTraining(id) {
            alert('Edit functionality would be implemented here.');
        }

        function deleteTraining(id, title) {
            if (confirm(`Are you sure you want to delete the training "${title}"?`)) {
                alert('Delete functionality would be implemented here.');
            }
        }

        // Refresh function
        function refreshTrainings() {
            loadTrainings();
        }
    </script>
</body>
</html>