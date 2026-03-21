<?php $__env->startSection('content'); ?>
<link rel="stylesheet" href="<?php echo e(asset('css/training/student-info.css')); ?>">

<div class="header-section">
       <h1 style="font-weight: 300;">Students Information</h1>
       <hr>
    </div>

    <?php if(session('success')): ?>
        <div class="alert alert-success">
            <?php echo e(session('success')); ?>

        </div>
    <?php endif; ?>

    <?php if(session('error')): ?>
        <div class="alert alert-danger">
            <?php echo e(session('error')); ?>

        </div>
    <?php endif; ?>

<form method="GET" action="<?php echo e(route('training.students-info')); ?>" class="filter-form">
        <div class="form-group">
            <label for="batch">Filter Students</label>
            <select name="batch" id="batch" class="form-control" onchange="this.form.submit()">
                <option value="">All Students</option>
                <option value="N/A" <?php echo e(request('batch') === 'N/A' ? 'selected' : ''); ?>>No Student ID (N/A)</option>
                <option disabled>──────────</option>
                <?php $__currentLoopData = $batches; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $batch): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($batch); ?>" <?php echo e(request('batch') == $batch ? 'selected' : ''); ?>>
                        Batch: <?php echo e($batch); ?>

                    </option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>
    </form>
    
<div class="page-container">
    


    

<br>

<!-- <div class="table-wrapper"> -->
        <div class="table-header">
            <div class="header-cell">USER ID</div>
            <div class="header-cell">STUDENT ID</div>
            <div class="header-cell">LAST NAME</div>
            <div class="header-cell">FIRST NAME</div>
            <div class="header-cell">MI</div>
            <div class="header-cell">SUFFIX</div>
            <div class="header-cell">SEX</div>
            <div class="header-cell">EMAIL</div>
            <div class="header-cell act1">ACTIONS</div>
        </div>
        
        <?php $__empty_1 = true; $__currentLoopData = $students; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $student): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <div class="table-row">
                <div class="cell"><?php echo e($student->user_id); ?></div>
                <div class="cell"><?php echo e($student->studentDetail->student_id ?? 'N/A'); ?></div>
                <div class="cell"><?php echo e($student->user_lname); ?></div>
                <div class="cell"><?php echo e($student->user_fname); ?></div>
                <div class="cell"><?php echo e($student->user_mInitial); ?></div>
                <div class="cell"><?php echo e($student->user_suffix ?? ''); ?></div>
                <div class="cell"><?php echo e($student->studentDetail->gender ?? 'N/A'); ?></div>
                <div class="cell"><?php echo e($student->user_email); ?></div>
                <div class="cell">
                    <div class="action-buttons">
                        <a href="<?php echo e(route('training.students.view', $student->user_id)); ?>" class="btn-icon" title="View">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href="<?php echo e(route('training.students.edit', $student->user_id)); ?>" class="btn-icon" title="Edit">
                            <i class="fas fa-edit"></i>
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <div class="table-row">
                <div class="cell empty-message">No students found</div>
            </div>
        <?php endif; ?>
    </div>
    <?php if($students->hasPages()): ?>
    <div class="pagination-container">
        <div class="pagination-info">
            Showing <?php echo e($students->firstItem()); ?> to <?php echo e($students->lastItem()); ?> of <?php echo e($students->total()); ?> entries
        </div>
        <div class="pagination-buttons">
            <?php if($students->onFirstPage()): ?>
                <span class="pagination-button disabled">
                    <i class="fas fa-chevron-left"></i> Previous
                </span>
            <?php else: ?>
                <a href="<?php echo e($students->previousPageUrl()); ?>" class="pagination-button">
                    <i class="fas fa-chevron-left"></i> Previous
                </a>
            <?php endif; ?>

            <div class="page-info">
                Page <?php echo e($students->currentPage()); ?> of <?php echo e($students->lastPage()); ?>

            </div>

            <?php if($students->hasMorePages()): ?>
                <a href="<?php echo e($students->nextPageUrl()); ?>" class="pagination-button">
                    Next <i class="fas fa-chevron-right"></i>
                </a>
            <?php else: ?>
                <span class="pagination-button disabled">
                    Next <i class="fas fa-chevron-right"></i>
                </span>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<style>
/* Pagination */
.pagination-container {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px;
    border-top: 1px solid #eee;
    margin-top: 20px;
}

.pagination-info {
    color: #6c757d;
    font-size: 0.9rem;
}

.pagination-buttons {
    display: flex;
    align-items: center;
    gap: 10px;
}

.pagination-button {
    padding: 8px 16px;
    border-radius: 6px;
    background: white;
    border: 1px solid #ddd;
    color: #333;
    font-size: 0.9rem;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    text-decoration: none;
    transition: all 0.2s;
}

.pagination-button:hover:not(.disabled) {
    background: #f5f5f5;
    border-color: #ccc;
}

.pagination-button.disabled {
    color: #aaa;
    cursor: not-allowed;
}

.page-info {
    margin: 0 10px;
    font-size: 0.9rem;
    color: #666;
}

@media (max-width: 768px) {
    .pagination-container {
        flex-direction: column;
        gap: 15px;
        align-items: flex-start;
    }
}
</style>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.nav', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\CAPSTONE\PN_Systems\group_13\resources\views/training/students-info.blade.php ENDPATH**/ ?>