<!-- Enhanced Add Employee Modal -->
<div class="modal enhanced-modal" id="enhancedAddEmployeeModal">
    <div class="modal-content enhanced-modal-content">
        <div class="modal-header">
            <div class="modal-icon">
                <i class="fas fa-user-plus"></i>
            </div>
            <h2 class="modal-title">Add New Employee</h2>
            <button class="close-modal" id="closeEnhancedModal">&times;</button>
        </div>
        
        <div class="modal-body">
            <form id="enhancedAddEmployeeForm">
                <!-- Step Indicator -->
                <div class="step-indicator">
                    <div class="step active" data-step="1">
                        <div class="step-number">1</div>
                        <div class="step-label">Basic Info</div>
                    </div>
                    <div class="step" data-step="2">
                        <div class="step-number">2</div>
                        <div class="step-label">Position</div>
                    </div>
                    <div class="step" data-step="3">
                        <div class="step-number">3</div>
                        <div class="step-label">Contact</div>
                    </div>
                </div>

                <!-- Step 1: Basic Information -->
                <div class="form-step active" data-step="1">
                    <h3 class="step-title">
                        <i class="fas fa-user"></i>
                        Basic Information
                    </h3>
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label>First Name <span class="required">*</span></label>
                            <input type="text" name="first_name" required class="form-input">
                        </div>
                        <div class="form-group">
                            <label>Last Name <span class="required">*</span></label>
                            <input type="text" name="last_name" required class="form-input">
                        </div>
                        <div class="form-group">
                            <label>Gender</label>
                            <select name="gender" class="form-input">
                                <option value="">Select Gender</option>
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Date of Birth</label>
                            <input type="date" name="date_of_birth" class="form-input">
                        </div>
                    </div>
                </div>

                <!-- Step 2: Position Information -->
                <div class="form-step" data-step="2">
                    <h3 class="step-title">
                        <i class="fas fa-briefcase"></i>
                        Position Details
                    </h3>
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Position <span class="required">*</span></label>
                            <input type="text" name="position" required class="form-input" placeholder="e.g., Nurse, Doctor">
                        </div>
                        <div class="form-group">
                            <label>Department</label>
                            <select name="department_assigned" class="form-input">
                                <option value="">Select Department</option>
                                <option value="general_medicine">General Medicine</option>
                                <option value="pediatrics">Pediatrics</option>
                                <option value="obstetrics_gynecology">Obstetrics & Gynecology</option>
                                <option value="emergency">Emergency / ER</option>
                                <option value="pharmacy">Pharmacy</option>
                                <option value="laboratory">Laboratory</option>
                                <option value="administration">Administration</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Employment Type</label>
                            <select name="employment_type" class="form-input">
                                <option value="full-time">Full-Time</option>
                                <option value="part-time">Part-Time</option>
                                <option value="contract">Contract</option>
                                <option value="temporary">Temporary</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Join Date</label>
                            <input type="date" name="join_date" class="form-input" value="<?php echo date('Y-m-d'); ?>">
                        </div>
                    </div>
                </div>

                <!-- Step 3: Contact Information -->
                <div class="form-step" data-step="3">
                    <h3 class="step-title">
                        <i class="fas fa-envelope"></i>
                        Contact Information
                    </h3>
                    
                    <div class="form-grid">
                        <div class="form-group full-width">
                            <label>Email Address <span class="required">*</span></label>
                            <input type="email" name="email" required class="form-input" placeholder="employee@example.com">
                        </div>
                        <div class="form-group">
                            <label>Phone Number</label>
                            <input type="tel" name="phone_number" class="form-input" placeholder="+251 9XX XXX XXX">
                        </div>
                        <div class="form-group">
                            <label>Emergency Contact</label>
                            <input type="text" name="emergency_contact" class="form-input" placeholder="Name and phone">
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" id="prevStepBtn" style="display: none;">
                <i class="fas fa-arrow-left"></i>
                Previous
            </button>
            <div class="btn-spacer"></div>
            <button type="button" class="btn btn-secondary" id="cancelBtn">Cancel</button>
            <button type="button" class="btn btn-primary" id="nextStepBtn">
                Next
                <i class="fas fa-arrow-right"></i>
            </button>
            <button type="submit" class="btn btn-success" id="submitEmployeeBtn" style="display: none;">
                <i class="fas fa-plus"></i>
                Add Employee
            </button>
        </div>
    </div>
</div>

<style>
/* Enhanced Modal Styles */
.enhanced-modal {
    display: none;
    position: fixed;
    z-index: 10000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.6);
    backdrop-filter: blur(5px);
    animation: fadeIn 0.3s ease;
}

.enhanced-modal-content {
    background: white;
    margin: 2% auto;
    border-radius: 20px;
    width: 90%;
    max-width: 700px;
    max-height: 90vh;
    overflow: hidden;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    animation: slideInDown 0.4s ease;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes slideInDown {
    from {
        opacity: 0;
        transform: translateY(-50px) scale(0.95);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

.modal-header {
    background: linear-gradient(135deg, var(--primary) 0%, #1a5270 100%);
    color: white;
    padding: 25px 30px;
    display: flex;
    align-items: center;
    gap: 15px;
    position: relative;
}

.modal-icon {
    width: 50px;
    height: 50px;
    border-radius: 12px;
    background: rgba(255, 255, 255, 0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
}

.modal-title {
    font-size: 1.5rem;
    font-weight: 700;
    margin: 0;
    flex: 1;
}

.close-modal {
    position: absolute;
    right: 20px;
    top: 50%;
    transform: translateY(-50%);
    background: rgba(255, 255, 255, 0.2);
    border: none;
    color: white;
    font-size: 1.5rem;
    width: 40px;
    height: 40px;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.close-modal:hover {
    background: rgba(255, 255, 255, 0.3);
    transform: translateY(-50%) rotate(90deg);
}

.modal-body {
    padding: 30px;
    max-height: 60vh;
    overflow-y: auto;
}

/* Step Indicator */
.step-indicator {
    display: flex;
    justify-content: center;
    margin-bottom: 30px;
    position: relative;
}

.step-indicator::before {
    content: '';
    position: absolute;
    top: 20px;
    left: 25%;
    right: 25%;
    height: 2px;
    background: #e2e8f0;
    z-index: 1;
}

.step {
    display: flex;
    flex-direction: column;
    align-items: center;
    position: relative;
    z-index: 2;
    flex: 1;
}

.step-number {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #e2e8f0;
    color: #94a3b8;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    margin-bottom: 8px;
    transition: all 0.3s ease;
}

.step.active .step-number {
    background: var(--primary);
    color: white;
    transform: scale(1.1);
}

.step.completed .step-number {
    background: #10b981;
    color: white;
}

.step-label {
    font-size: 0.85rem;
    color: #64748b;
    font-weight: 600;
}

.step.active .step-label {
    color: var(--primary);
}

/* Form Steps */
.form-step {
    display: none;
    animation: slideInRight 0.3s ease;
}

.form-step.active {
    display: block;
}

@keyframes slideInRight {
    from {
        opacity: 0;
        transform: translateX(20px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

.step-title {
    display: flex;
    align-items: center;
    gap: 10px;
    color: #1e293b;
    font-size: 1.2rem;
    font-weight: 700;
    margin-bottom: 20px;
    padding-bottom: 10px;
    border-bottom: 2px solid #f1f5f9;
}

.form-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 20px;
}

.form-group.full-width {
    grid-column: 1 / -1;
}

.form-group {
    display: flex;
    flex-direction: column;
}

.form-group label {
    font-weight: 600;
    color: #374151;
    margin-bottom: 6px;
    font-size: 0.9rem;
}

.required {
    color: #ef4444;
}

.form-input {
    padding: 12px 15px;
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    font-size: 0.95rem;
    transition: all 0.3s ease;
    background: #f9fafb;
}

.form-input:focus {
    outline: none;
    border-color: var(--primary);
    background: white;
    box-shadow: 0 0 0 3px rgba(26, 74, 95, 0.1);
}

.form-input.valid {
    border-color: #10b981;
    background: #f0fdf4;
}

.form-input.invalid {
    border-color: #ef4444;
    background: #fef2f2;
}

/* Modal Footer */
.modal-footer {
    background: #f8fafc;
    padding: 20px 30px;
    display: flex;
    gap: 15px;
    align-items: center;
    border-top: 1px solid #e2e8f0;
}

.btn-spacer {
    flex: 1;
}

.btn {
    padding: 10px 20px;
    border-radius: 8px;
    font-weight: 600;
    font-size: 0.9rem;
    cursor: pointer;
    transition: all 0.3s ease;
    border: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.btn-primary {
    background: linear-gradient(135deg, var(--primary) 0%, #1a5270 100%);
    color: white;
    box-shadow: 0 2px 8px rgba(26, 74, 95, 0.3);
}

.btn-primary:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(26, 74, 95, 0.4);
}

.btn-secondary {
    background: #f1f5f9;
    color: #64748b;
    border: 1px solid #e2e8f0;
}

.btn-secondary:hover {
    background: #e2e8f0;
    border-color: #cbd5e1;
}

.btn-success {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: white;
    box-shadow: 0 2px 8px rgba(16, 185, 129, 0.3);
}

.btn-success:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.4);
}

/* Responsive */
@media (max-width: 768px) {
    .enhanced-modal-content {
        width: 95%;
        margin: 5% auto;
    }

    .modal-header {
        padding: 20px;
    }

    .modal-body {
        padding: 20px;
    }

    .form-grid {
        grid-template-columns: 1fr;
        gap: 15px;
    }

    .step-indicator {
        margin-bottom: 20px;
    }

    .step-number {
        width: 35px;
        height: 35px;
    }

    .step-label {
        font-size: 0.8rem;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let currentModalStep = 1;
    const totalModalSteps = 3;
    
    const modal = document.getElementById('enhancedAddEmployeeModal');
    const form = document.getElementById('enhancedAddEmployeeForm');
    const prevBtn = document.getElementById('prevStepBtn');
    const nextBtn = document.getElementById('nextStepBtn');
    const submitBtn = document.getElementById('submitEmployeeBtn');
    const cancelBtn = document.getElementById('cancelBtn');
    const closeBtn = document.getElementById('closeEnhancedModal');

    // Open modal function
    window.openEnhancedAddModal = function() {
        modal.style.display = 'block';
        document.body.style.overflow = 'hidden';
        showModalStep(1);
    };

    // Close modal function
    function closeModal() {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
        form.reset();
        showModalStep(1);
    }

    // Show specific step
    function showModalStep(step) {
        // Hide all steps
        document.querySelectorAll('.form-step').forEach(s => s.classList.remove('active'));
        document.querySelectorAll('.step').forEach(s => s.classList.remove('active', 'completed'));

        // Show current step
        document.querySelector(`.form-step[data-step="${step}"]`).classList.add('active');
        document.querySelector(`.step[data-step="${step}"]`).classList.add('active');

        // Mark completed steps
        for (let i = 1; i < step; i++) {
            document.querySelector(`.step[data-step="${i}"]`).classList.add('completed');
        }

        // Update buttons
        prevBtn.style.display = step === 1 ? 'none' : 'inline-flex';
        nextBtn.style.display = step === totalModalSteps ? 'none' : 'inline-flex';
        submitBtn.style.display = step === totalModalSteps ? 'inline-flex' : 'none';

        currentModalStep = step;
    }

    // Validate current step
    function validateModalStep(step) {
        const currentStep = document.querySelector(`.form-step[data-step="${step}"]`);
        const requiredInputs = currentStep.querySelectorAll('[required]');
        let isValid = true;

        requiredInputs.forEach(input => {
            if (!input.value.trim()) {
                isValid = false;
                input.classList.add('invalid');
            } else {
                input.classList.remove('invalid');
                input.classList.add('valid');
            }
        });

        if (!isValid) {
            // Shake animation
            currentStep.style.animation = 'shake 0.5s ease-in-out';
            setTimeout(() => {
                currentStep.style.animation = '';
            }, 500);
        }

        return isValid;
    }

    // Event listeners
    nextBtn.addEventListener('click', () => {
        if (validateModalStep(currentModalStep)) {
            if (currentModalStep < totalModalSteps) {
                showModalStep(currentModalStep + 1);
            }
        }
    });

    prevBtn.addEventListener('click', () => {
        if (currentModalStep > 1) {
            showModalStep(currentModalStep - 1);
        }
    });

    submitBtn.addEventListener('click', () => {
        if (validateModalStep(currentModalStep)) {
            submitEmployeeForm();
        }
    });

    cancelBtn.addEventListener('click', closeModal);
    closeBtn.addEventListener('click', closeModal);

    // Close on outside click
    modal.addEventListener('click', (e) => {
        if (e.target === modal) {
            closeModal();
        }
    });

    // Submit form
    function submitEmployeeForm() {
        const formData = new FormData(form);
        
        // Show loading state
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adding...';
        submitBtn.disabled = true;

        fetch('employee_actions.php?action=add', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                closeModal();
                // Show success message
                showNotification('Employee added successfully!', 'success');
                // Reload employees if function exists
                if (typeof loadEmployees === 'function') {
                    loadEmployees();
                }
                if (typeof loadGlobalStats === 'function') {
                    loadGlobalStats();
                }
            } else {
                showNotification(data.message || 'Failed to add employee', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('An error occurred while adding the employee', 'error');
        })
        .finally(() => {
            submitBtn.innerHTML = '<i class="fas fa-plus"></i> Add Employee';
            submitBtn.disabled = false;
        });
    }

    // Show notification
    function showNotification(message, type) {
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.innerHTML = `
            <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
            <span>${message}</span>
        `;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.classList.add('show');
        }, 100);
        
        setTimeout(() => {
            notification.classList.remove('show');
            setTimeout(() => {
                document.body.removeChild(notification);
            }, 300);
        }, 3000);
    }

    // Real-time validation
    form.addEventListener('input', (e) => {
        if (e.target.hasAttribute('required')) {
            if (e.target.value.trim()) {
                e.target.classList.remove('invalid');
                e.target.classList.add('valid');
            } else {
                e.target.classList.remove('valid');
            }
        }
    });

    // Email validation
    const emailInput = form.querySelector('input[name="email"]');
    if (emailInput) {
        emailInput.addEventListener('blur', function() {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (this.value && !emailRegex.test(this.value)) {
                this.classList.add('invalid');
                this.classList.remove('valid');
            }
        });
    }
});
</script>

<!-- Notification Styles -->
<style>
.notification {
    position: fixed;
    top: 20px;
    right: 20px;
    background: white;
    padding: 15px 20px;
    border-radius: 10px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
    display: flex;
    align-items: center;
    gap: 10px;
    z-index: 10001;
    transform: translateX(400px);
    opacity: 0;
    transition: all 0.3s ease;
}

.notification.show {
    transform: translateX(0);
    opacity: 1;
}

.notification-success {
    border-left: 4px solid #10b981;
    color: #065f46;
}

.notification-error {
    border-left: 4px solid #ef4444;
    color: #991b1b;
}

.notification i {
    font-size: 1.2rem;
}

@keyframes shake {
    0%, 100% { transform: translateX(0); }
    25% { transform: translateX(-5px); }
    75% { transform: translateX(5px); }
}
</style>