<?php $__env->startSection('content'); ?>
<link rel="stylesheet" href="<?php echo e(asset('css/training/school.css')); ?>">

<div class="page-container">
    <div class="header-section">
        <a href="javascript:history.back()" class="back-button">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>

    <h1 style="font-weight: 300;">School Details</h1>
    <hr>

    <?php if(session('success')): ?>
        <div class="alert alert-success">
            <?php echo e(session('success')); ?>

        </div>
    <?php endif; ?>

    <?php if(session('error')): ?>
        <div class="alert alert-error">
            <?php echo e(session('error')); ?>

        </div>
    <?php endif; ?>

    <div class="school-details-card">
        <h3>School Information</h3>
        <div class="detail-row">
            <span class="label">School ID:</span>
            <span class="value"><?php echo e($school->school_id); ?></span>
        </div>
        <div class="detail-row">
            <span class="label">School Name:</span>
            <span class="value"><?php echo e($school->name); ?></span>
        </div>
        <div class="detail-row">
            <span class="label">Department:</span>
            <span class="value"><?php echo e($school->department); ?></span>
        </div>
        <div class="detail-row">
            <span class="label">Course:</span>
            <span class="value"><?php echo e($school->course); ?></span>
        </div>
        <div class="detail-row">
            <span class="label">No. of Semester:</span>
            <span class="value"><?php echo e($school->semester_count); ?></span>
        </div>
        <div class="detail-row">
            <span class="label">Terms:</span>
            <span class="value">
                <ul class="terms-list">
                    <?php $__currentLoopData = $school->terms; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $term): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li><?php echo e($term); ?></li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
            </span>
        </div>
        <div class="detail-row">
            <span class="label">Grade Ranges:</span>
            <span class="value">
                <div class="grade-ranges">
                    <div class="grade-range passing">
                        <span class="grade-label">Passing:</span>
                        <span class="grade-value"><?php echo e(number_format($school->passing_grade_min, 1)); ?> - <?php echo e(number_format($school->passing_grade_max, 1)); ?></span>
                    </div>
                    <div class="grade-range failing">
                        <span class="grade-label">Failing:</span>
                        <span class="grade-value">
                            <?php if($school->passing_grade_min == 1.0): ?>
                                <?php echo e(number_format($school->passing_grade_max + 0.1, 1)); ?> - 5.0
                            <?php else: ?>
                                1.0 - <?php echo e(number_format($school->passing_grade_min - 0.1, 1)); ?>

                            <?php endif; ?>
                        </span>
                    </div>
                </div>
            </span>
        </div>
    </div>

    <div class="school-details-card">
        <h3>Subjects</h3>
        <div class="subjects-table-container">
            <div class="subjects-table-header">
                <div class="header-cell">Subject Name</div>
                <div class="header-cell">Offer Code</div>
                <div class="header-cell">Instructor</div>
                <div class="header-cell">Schedule</div>
            </div>
            <?php $__empty_1 = true; $__currentLoopData = $school->subjects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $subject): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <?php if(is_object($subject)): ?>
                    <div class="subjects-table-row">
                        <div class="cell"><?php echo e($subject->name); ?></div>
                        <div class="cell"><?php echo e($subject->offer_code); ?></div>
                        <div class="cell"><?php echo e($subject->instructor); ?></div>
                        <div class="cell"><?php echo e($subject->schedule); ?></div>
                    </div>
                <?php else: ?>
                    <div class="subjects-table-row">
                        <div class="cell" colspan="4">Invalid subject data</div>
                    </div>
                <?php endif; ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="subjects-table-row">
                    <div class="cell" colspan="4">No subjects found.</div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="school-details-card">
        <h3>Classes</h3>
        <div class="table-wrapper">
            <div class="table-header">
                <div class="header-cell">Class ID</div>
                <div class="header-cell">Class Name</div>
                <div class="header-cell">No. of Students</div>
                <div class="header-cell">Actions</div>
            </div>
            <?php $__empty_1 = true; $__currentLoopData = $classes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $class): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <?php if(is_object($class)): ?>
                    <div class="table-row">
                        <div class="cell"><?php echo e($class->class_id); ?></div>
                        <div class="cell"><?php echo e($class->class_name); ?></div>
                        <div class="cell"><?php echo e($class->students->count()); ?></div>
                        <div class="cell">
                            <div class="action-buttons">
                                <a href="<?php echo e(route('training.classes.show', $class)); ?>" class="btn-icon" title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="<?php echo e(route('training.classes.edit', $class)); ?>" class="btn-icon" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="table-row">
                        <div class="cell" colspan="4">Invalid class data</div>
                    </div>
                <?php endif; ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="table-row">
                    <div class="cell" colspan="4">No classes found.</div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.nav', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\CAPSTONE\PN_Systems\group_13\resources\views/training/schools/show.blade.php ENDPATH**/ ?>