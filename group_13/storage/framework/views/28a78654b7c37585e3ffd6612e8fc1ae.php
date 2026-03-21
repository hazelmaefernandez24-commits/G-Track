<?php $__env->startSection('content'); ?>


<link rel="stylesheet" href="<?php echo e(asset('css/training/classes/edit.css')); ?>">

<div class="page-container">
    <div class="header-section">
        <h2>Edit Class</h2>
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

    <!-- Student Conflict Validation Messages -->
    <?php if(session('student_conflicts')): ?>
        <div class="alert alert-danger student-conflicts-alert">
            <div class="alert-header">
                <i class="fas fa-exclamation-triangle"></i>
                <strong>Student Enrollment Conflicts</strong>
            </div>
            <div class="alert-body">
                <p><?php echo e(session('error')); ?></p>
                <ul class="conflict-list">
                    <?php $__currentLoopData = session('student_conflicts'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $conflict): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li><i class="fas fa-user-times"></i> <?php echo e($conflict); ?></li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
            </div>
            <div class="alert-footer">
                <small><i class="fas fa-info-circle"></i> Each student can only be enrolled in one class at a time. Please remove students from their current classes before adding them to this class.</small>
            </div>
        </div>
    <?php endif; ?>

    <form action="<?php echo e(route('training.classes.update', $class)); ?>" method="POST" class="form-container">
        <?php echo csrf_field(); ?>
        <?php echo method_field('PUT'); ?>
        <input type="hidden" name="school_id" value="<?php echo e($class->school_id); ?>">

        <div class="form-group">
            <label for="class_id">Class ID</label>
            <input type="text" id="class_id" name="class_id" value="<?php echo e(old('class_id', $class->class_id)); ?>" required>
            <?php $__errorArgs = ['class_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                <div class="error-message"><?php echo e($message); ?></div>
            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>

        <div class="form-group">
            <label for="class_name">Class Name</label>
            <input type="text" id="class_name" name="class_name" value="<?php echo e(old('class_name', $class->class_name)); ?>" required>
            <?php $__errorArgs = ['class_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                <div class="error-message"><?php echo e($message); ?></div>
            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>

        <div class="form-group">
            <label for="student_ids">Select Students</label>
            <div class="filter-section">
                <select id="batchFilter" class="form-select">
                    <option value="">All Batches</option>
                    <?php $__currentLoopData = $students->pluck('studentDetail.batch')->unique(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $batch): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($batch); ?>"><?php echo e($batch); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div class="students-container">
                <?php $__currentLoopData = $students; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $student): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="student-checkbox" data-batch="<?php echo e($student->studentDetail->batch ?? ''); ?>">
                        <input type="checkbox" 
                            id="student_<?php echo e($student->user_id); ?>" 
                            name="student_ids[]" 
                            value="<?php echo e($student->user_id); ?>"
                            <?php echo e((is_array(old('student_ids', $class->students->pluck('user_id')->toArray())) && 
                                in_array($student->user_id, old('student_ids', $class->students->pluck('user_id')->toArray()))) ? 'checked' : ''); ?>>
                        <label for="student_<?php echo e($student->user_id); ?>">
                            <?php echo e($student->user_id); ?> - <?php echo e($student->user_fname); ?> <?php echo e($student->user_mInitial); ?>. <?php echo e($student->user_lname); ?>

                            <span class="batch-tag"><?php echo e($student->studentDetail->batch ?? ''); ?></span>
                        </label>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
            <?php $__errorArgs = ['student_ids'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                <div class="error-message"><?php echo e($message); ?></div>
            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>

        <script>
        document.addEventListener('DOMContentLoaded', function() {
            const batchFilter = document.getElementById('batchFilter');
            const studentCheckboxes = document.querySelectorAll('.student-checkbox');

            batchFilter.addEventListener('change', function() {
                const selectedBatch = this.value;
                
                studentCheckboxes.forEach(checkbox => {
                    if (!selectedBatch || checkbox.dataset.batch === selectedBatch) {
                        checkbox.style.display = 'flex';
                    } else {
                        checkbox.style.display = 'none';
                    }
                });
            });
        });
        </script>

        <div class="form-actions">
            <button type="submit" class="btn-submit">Update Class</button>
            <a href="<?php echo e(route('training.schools.show', ['school' => $class->school_id])); ?>" class="btn-cancel">Cancel</a>
        </div>
    </form>
</div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.nav', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\CAPSTONE\PN_Systems\group_13\resources\views/training/classes/edit.blade.php ENDPATH**/ ?>