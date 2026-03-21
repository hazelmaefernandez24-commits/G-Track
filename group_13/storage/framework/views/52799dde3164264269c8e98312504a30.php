<?php $__env->startSection('content'); ?>
<div class="create-user-page">
    <!-- Header Section -->
    <div class="page-header">
        <div class="header-content">
            <div class="header-left">
                <a href="<?php echo e(route('admin.pnph_users.index')); ?>" class="back-button">
                    <i class="fas fa-arrow-left"></i>
                    <span>Back to Users</span>
                </a>
                <div class="page-title-section">
                    <h1 class="page-title">Create New User</h1>
                    <p class="page-subtitle">Add a new user to the system with their basic information</p>
                </div>
            </div>
            <div class="header-right">
                <div class="header-stats">
                    <div class="stat-item">
                        <i class="fas fa-users"></i>
                        <span>New User</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Form Container -->
    <div class="form-container">
        <div class="form-card">
            <div class="form-header">
                <h2 class="form-title">
                    <i class="fas fa-user-plus"></i>
                    User Information
                </h2>
                <p class="form-description">
                    Fill in the required information to create a new user account.
                    A temporary password will be automatically generated and sent to the user's email.
                </p>
            </div>

            <form action="<?php echo e(route('admin.pnph_users.store')); ?>" method="POST" class="user-form" id="createUserForm">
                <?php echo csrf_field(); ?>
                <!-- Account Information Section -->
                <div class="form-section">
                    <div class="section-header">
                        <h3 class="section-title">
                            <i class="fas fa-id-card"></i>
                            Account Information
                        </h3>
                        <p class="section-description">Basic account details and credentials</p>
                    </div>

                    <div class="form-row">
                        <div class="form-group full-width">
                            <label for="user_id" class="form-label">
                                <i class="fas fa-fingerprint"></i>
                                User ID
                                <span class="required">*</span>
                            </label>
                            <input type="text"
                                   name="user_id"
                                   id="user_id"
                                   class="form-input"
                                   value="<?php echo e(old('user_id')); ?>"
                                   required
                                   maxlength="20"
                                   placeholder="Enter unique user ID">
                            <div class="input-help">
                                <i class="fas fa-info-circle"></i>
                                Unique identifier for the user (max 20 characters)
                            </div>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="user_email" class="form-label">
                                <i class="fas fa-envelope"></i>
                                Email Address
                                <span class="required">*</span>
                            </label>
                            <input type="email"
                                   name="user_email"
                                   id="user_email"
                                   class="form-input"
                                   value="<?php echo e(old('user_email')); ?>"
                                   required
                                   maxlength="100"
                                   placeholder="user@example.com">
                            <div class="input-help">
                                <i class="fas fa-key"></i>
                                A temporary password will be sent to this email
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="user_role" class="form-label">
                                <i class="fas fa-user-tag"></i>
                                User Role
                                <span class="required">*</span>
                            </label>
                            <select name="user_role" id="user_role" class="form-select" required>
                                <option value="">Select a role</option>
                                <option value="admin" <?php echo e(old('user_role') == 'admin' ? 'selected' : ''); ?>>
                                    <i class="fas fa-crown"></i> Admin
                                </option>
                                <option value="training" <?php echo e(old('user_role') == 'training' ? 'selected' : ''); ?>>
                                    <i class="fas fa-chalkboard-teacher"></i> Training
                                </option>
                                <option value="educator" <?php echo e(old('user_role') == 'educator' ? 'selected' : ''); ?>>
                                    <i class="fas fa-graduation-cap"></i> Educator
                                </option>
                                <option value="student" <?php echo e(old('user_role') == 'student' ? 'selected' : ''); ?>>
                                    <i class="fas fa-user-graduate"></i> Student
                                </option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Personal Information Section -->
                <div class="form-section">
                    <div class="section-header">
                        <h3 class="section-title">
                            <i class="fas fa-user"></i>
                            Personal Information
                        </h3>
                        <p class="section-description">User's personal details and name information</p>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="user_fname" class="form-label">
                                <i class="fas fa-user"></i>
                                First Name
                                <span class="required">*</span>
                            </label>
                            <input type="text"
                                   name="user_fname"
                                   id="user_fname"
                                   class="form-input"
                                   value="<?php echo e(old('user_fname')); ?>"
                                   required
                                   maxlength="50"
                                   placeholder="Enter first name">
                        </div>

                        <div class="form-group">
                            <label for="user_lname" class="form-label">
                                <i class="fas fa-user"></i>
                                Last Name
                                <span class="required">*</span>
                            </label>
                            <input type="text"
                                   name="user_lname"
                                   id="user_lname"
                                   class="form-input"
                                   value="<?php echo e(old('user_lname')); ?>"
                                   required
                                   maxlength="50"
                                   placeholder="Enter last name">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="gender" class="form-label">
                                <i class="fas fa-venus-mars"></i>
                                Gender
                            </label>
                            <select name="gender" id="gender" class="form-select">
                                <option value="">Prefer not to say</option>
                                <option value="M" <?php echo e(old('gender') == 'M' ? 'selected' : ''); ?>>Male</option>
                                <option value="F" <?php echo e(old('gender') == 'F' ? 'selected' : ''); ?>>Female</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="user_mInitial" class="form-label">
                                <i class="fas fa-font"></i>
                                Middle Initial
                            </label>
                            <input type="text"
                                   name="user_mInitial"
                                   id="user_mInitial"
                                   class="form-input"
                                   value="<?php echo e(old('user_mInitial')); ?>"
                                   maxlength="5"
                                   placeholder="M.I.">
                        </div>

                        <div class="form-group">
                            <label for="user_suffix" class="form-label">
                                <i class="fas fa-tag"></i>
                                Suffix
                            </label>
                            <input type="text"
                                   name="user_suffix"
                                   id="user_suffix"
                                   class="form-input"
                                   value="<?php echo e(old('user_suffix')); ?>"
                                   maxlength="10"
                                   placeholder="Jr., Sr., III">
                            <div class="input-help">
                                <i class="fas fa-info-circle"></i>
                                Optional: e.g., Jr., Sr., III
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="form-actions">
                    <div class="action-buttons">
                        <button type="button" class="btn btn-secondary" onclick="window.history.back()">
                            <i class="fas fa-times"></i>
                            Cancel
                        </button>
                        <button type="submit" class="btn btn-primary" id="submitBtn">
                            <i class="fas fa-user-plus"></i>
                            Create User
                        </button>
                    </div>
                    <div class="form-note">
                        <i class="fas fa-info-circle"></i>
                        <span class="required">*</span> Required fields must be filled out
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
/* Modern Create User Page Styles */
.create-user-page {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0;
}

/* Header Section */
.page-header {
    background: linear-gradient(135deg, #22bbea 0%, #1e9bd1 100%);
    color: white;
    padding: 30px;
    border-radius: 12px;
    margin-bottom: 30px;
    box-shadow: 0 4px 20px rgba(34, 187, 234, 0.3);
}

.header-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 20px;
}

.header-left {
    flex: 1;
}

.back-button {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    color: white;
    text-decoration: none;
    font-size: 14px;
    margin-bottom: 15px;
    padding: 8px 16px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 6px;
    transition: all 0.3s ease;
}

.back-button:hover {
    background: rgba(255, 255, 255, 0.3);
    color: white;
    text-decoration: none;
}

.page-title {
    font-size: 28px;
    font-weight: 600;
    margin: 0 0 8px 0;
    color: white;
}

.page-subtitle {
    font-size: 16px;
    margin: 0;
    opacity: 0.9;
}

.header-right {
    flex-shrink: 0;
}

.header-stats {
    display: flex;
    gap: 20px;
}

.stat-item {
    display: flex;
    align-items: center;
    gap: 8px;
    background: rgba(255, 255, 255, 0.2);
    padding: 12px 16px;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 500;
}

.stat-item i {
    font-size: 16px;
}

/* Form Container */
.form-container {
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
    overflow: hidden;
}

.form-card {
    padding: 0;
}

.form-header {
    background: #f8f9fa;
    padding: 30px;
    border-bottom: 1px solid #e9ecef;
}

.form-title {
    font-size: 22px;
    font-weight: 600;
    margin: 0 0 10px 0;
    color: #333;
    display: flex;
    align-items: center;
    gap: 10px;
}

.form-title i {
    color: #22bbea;
    font-size: 20px;
}

.form-description {
    font-size: 14px;
    color: #666;
    margin: 0;
    line-height: 1.5;
}

/* Form Sections */
.user-form {
    padding: 30px;
}

.form-section {
    margin-bottom: 40px;
}

.form-section:last-of-type {
    margin-bottom: 0;
}

.section-header {
    margin-bottom: 25px;
    padding-bottom: 15px;
    border-bottom: 2px solid #f1f3f4;
}

.section-title {
    font-size: 18px;
    font-weight: 600;
    margin: 0 0 8px 0;
    color: #333;
    display: flex;
    align-items: center;
    gap: 10px;
}

.section-title i {
    color: #22bbea;
    font-size: 16px;
}

.section-description {
    font-size: 14px;
    color: #666;
    margin: 0;
}

/* Form Layout */
.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin-bottom: 20px;
}

.form-row:last-child {
    margin-bottom: 0;
}

.form-group {
    display: flex;
    flex-direction: column;
}

.form-group.full-width {
    grid-column: 1 / -1;
}

/* Form Labels */
.form-label {
    font-size: 14px;
    font-weight: 600;
    color: #333;
    margin-bottom: 8px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.form-label i {
    color: #22bbea;
    font-size: 14px;
    width: 16px;
}

.required {
    color: #dc3545;
    font-weight: 700;
}

/* Form Inputs */
.form-input,
.form-select {
    padding: 12px 16px;
    border: 2px solid #e9ecef;
    border-radius: 8px;
    font-size: 14px;
    font-family: 'Poppins', sans-serif;
    transition: all 0.3s ease;
    background: white;
}

.form-input:focus,
.form-select:focus {
    outline: none;
    border-color: #22bbea;
    box-shadow: 0 0 0 3px rgba(34, 187, 234, 0.1);
}

.form-input::placeholder {
    color: #adb5bd;
}

.form-select {
    cursor: pointer;
}

/* Input Help Text */
.input-help {
    font-size: 12px;
    color: #6c757d;
    margin-top: 6px;
    display: flex;
    align-items: center;
    gap: 6px;
}

.input-help i {
    color: #22bbea;
    font-size: 12px;
}

/* Form Actions */
.form-actions {
    margin-top: 40px;
    padding-top: 30px;
    border-top: 2px solid #f1f3f4;
}

.action-buttons {
    display: flex;
    gap: 15px;
    justify-content: flex-end;
    margin-bottom: 15px;
}

.btn {
    padding: 12px 24px;
    border: none;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    font-family: 'Poppins', sans-serif;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 8px;
    text-decoration: none;
}

.btn-primary {
    background: linear-gradient(135deg, #22bbea 0%, #1e9bd1 100%);
    color: white;
    box-shadow: 0 4px 15px rgba(34, 187, 234, 0.3);
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(34, 187, 234, 0.4);
}

.btn-secondary {
    background: #6c757d;
    color: white;
}

.btn-secondary:hover {
    background: #5a6268;
    transform: translateY(-1px);
}

.form-note {
    font-size: 12px;
    color: #6c757d;
    display: flex;
    align-items: center;
    gap: 6px;
    justify-content: center;
}

.form-note i {
    color: #22bbea;
}

/* Responsive Design */
@media (max-width: 768px) {
    .create-user-page {
        padding: 0 15px;
    }

    .page-header {
        padding: 20px;
        margin-bottom: 20px;
    }

    .header-content {
        flex-direction: column;
        align-items: flex-start;
        gap: 15px;
    }

    .page-title {
        font-size: 24px;
    }

    .form-row {
        grid-template-columns: 1fr;
        gap: 15px;
    }

    .user-form {
        padding: 20px;
    }

    .form-header {
        padding: 20px;
    }

    .action-buttons {
        flex-direction: column;
    }

    .btn {
        justify-content: center;
    }
}

/* Loading State */
.btn.loading {
    opacity: 0.7;
    cursor: not-allowed;
    pointer-events: none;
}

.btn.loading::after {
    content: '';
    width: 16px;
    height: 16px;
    border: 2px solid transparent;
    border-top: 2px solid currentColor;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin-left: 8px;
}

@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('createUserForm');
    const submitBtn = document.getElementById('submitBtn');

    // Enhanced form validation
    function validateForm() {
        const requiredFields = form.querySelectorAll('[required]');
        let isValid = true;

        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                field.style.borderColor = '#dc3545';
                isValid = false;
            } else {
                field.style.borderColor = '#e9ecef';
            }
        });

        return isValid;
    }

    // Real-time validation
    form.addEventListener('input', function(e) {
        if (e.target.hasAttribute('required')) {
            if (e.target.value.trim()) {
                e.target.style.borderColor = '#28a745';
            } else {
                e.target.style.borderColor = '#dc3545';
            }
        }
    });

    // Form submission with loading state
    form.addEventListener('submit', function(e) {
        e.preventDefault();

        if (!validateForm()) {
            showErrorMessage('Please fill in all required fields.');
            return;
        }

        // Show confirmation dialog
        if (confirm('Are you sure you want to create this user?\n\nA temporary password will be sent to the provided email address.')) {
            // Add loading state
            submitBtn.classList.add('loading');
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating User...';

            // Submit the form
            setTimeout(() => {
                form.submit();
            }, 500);
        }
    });

    // Auto-generate User ID suggestion
    const fnameInput = document.getElementById('user_fname');
    const lnameInput = document.getElementById('user_lname');
    const userIdInput = document.getElementById('user_id');

    function generateUserIdSuggestion() {
        const fname = fnameInput.value.trim();
        const lname = lnameInput.value.trim();

        if (fname && lname && !userIdInput.value) {
            const suggestion = (fname.charAt(0) + lname).toLowerCase().replace(/[^a-z0-9]/g, '');
            userIdInput.placeholder = `Suggestion: ${suggestion}`;
        }
    }

    fnameInput.addEventListener('input', generateUserIdSuggestion);
    lnameInput.addEventListener('input', generateUserIdSuggestion);

    // Email validation
    const emailInput = document.getElementById('user_email');
    emailInput.addEventListener('blur', function() {
        const email = this.value.trim();
        if (email && !isValidEmail(email)) {
            this.style.borderColor = '#dc3545';
            showErrorMessage('Please enter a valid email address.');
        }
    });

    function isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }

    function showErrorMessage(message) {
        // This will use the global error message system from the layout
        if (window.showErrorMessage) {
            window.showErrorMessage(message);
        } else {
            alert(message);
        }
    }
});
</script>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin_layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\CAPSTONE\PN_Systems\group_13\resources\views/admin/pnph_users/create.blade.php ENDPATH**/ ?>