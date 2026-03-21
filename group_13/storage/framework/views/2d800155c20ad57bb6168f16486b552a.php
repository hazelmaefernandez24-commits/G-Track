<?php $__env->startSection('content'); ?>

<link rel="stylesheet" href="<?php echo e(asset('css/training/classes/show.css')); ?>">
<div class="page-container">
    <div class="header-section">
        <a href="javascript:history.back()" class="back-button">
            <i class="fas fa-arrow-left"></i> Back
        </a>
        <h2>Class Details</h2>
        <a href="<?php echo e(route('training.schools.show', ['school' => $class->school->school_id])); ?>" class="btn-back">
            <!-- <i class="fas fa-arrow-left"></i>--> Go to School page
        </a>
    </div>

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

    <div class="content-section">
        <div class="class-details card">
            <h3>Class Information</h3>
            <div class="info-grid">
                <div class="info-item">
                    <label>Class ID:</label>
                    <span><?php echo e($class->class_id); ?></span>
                </div>
                <div class="info-item">
                    <label>Class Name:</label>
                    <span><?php echo e($class->class_name); ?></span>
                </div>
                <div class="info-item">
                    <label>School:</label>
                    <span><?php echo e($class->school->name); ?></span>
                </div>
                <div class="info-item">
                    <label>Department:</label>
                    <span><?php echo e($class->school->department); ?></span>
                </div>
                <div class="info-item">
                    <label>Course:</label>
                    <span><?php echo e($class->school->course); ?></span>
                </div>
            </div>
        </div>

        <div class="students-list card">
            <h3>Students List</h3>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Student ID</th>
                            <th>Name</th>
                            <th>Student Number</th>
                            <th>Training Code</th>
                            <th>Group</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $class->students; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $student): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td><?php echo e($student->studentDetail->student_id ?? 'N/A'); ?></td>
                                <td><?php echo e($student->user_fname); ?> <?php echo e($student->user_mInitial); ?>. <?php echo e($student->user_lname); ?></td>
                                <td><?php echo e($student->studentDetail->student_number); ?></td>
                                <td><?php echo e($student->studentDetail->training_code); ?></td>
                                <td><?php echo e($student->studentDetail->group); ?></td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="5" class="text-center">No students assigned to this class.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.nav', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\CAPSTONE\PN_Systems\group_13\resources\views/training/classes/show.blade.php ENDPATH**/ ?>