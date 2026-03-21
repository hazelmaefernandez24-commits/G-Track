<?php $__env->startSection('content'); ?>
<div class="submission-view-container">
    <h1>Submitted Grades for <?php echo e($gradeSubmission->term ?? 'N/A'); ?></h1>

    <div class="submission-details">
        <p><strong>Semester:</strong> <?php echo e($gradeSubmission->semester ?? 'N/A'); ?></p>
        <p><strong>Term:</strong> <?php echo e($gradeSubmission->term ?? 'N/A'); ?></p>
        <p><strong>Academic Year:</strong> <?php echo e($gradeSubmission->academic_year ?? 'N/A'); ?></p>
    </div>

    <h3>Subjects and Grades</h3>
    
    <?php if($studentSubjectEntries->isEmpty()): ?>
        <p>No subjects found for this submission.</p>
    <?php else: ?>
        <table class="subjects-table">
            <thead>
                <tr>
                    <th>Subject</th>
                    <th>Your Grade</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $studentSubjectEntries; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $entry): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td><?php echo e($entry->subject_name ?? 'N/A'); ?></td>
                        <td>
                            <?php echo e($entry->grade ?? '-'); ?>

                        </td>
                        <td>
                             <?php
                                $status = $entry->status ?? 'pending';
                             ?>
                             <span class="status <?php echo e($status); ?>"><?php echo e(ucfirst($status)); ?></span>
                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    <?php endif; ?>

    <?php if($proof): ?>
        <h3>Proof of Submission</h3>
        <div class="proof-container">
            <p><strong>Status:</strong> <?php echo e(ucfirst($proof->status)); ?></p>
            <div class="proof-file">
                <?php if($proof->file_type === 'pdf'): ?>
                    <iframe src="<?php echo e(asset('storage/' . $proof->file_path)); ?>" width="100%" height="500px"></iframe>
                <?php elseif(in_array($proof->file_type, ['jpg', 'jpeg', 'png'])): ?>
                    <img src="<?php echo e(asset('storage/' . $proof->file_path)); ?>" alt="Proof" style="max-width: 100%; max-height: 500px;">
                <?php else: ?>
                    <a href="<?php echo e(asset('storage/' . $proof->file_path)); ?>" download="<?php echo e($proof->file_name); ?>">Download Proof</a>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    <div class="back-link" style="margin-top: 20px;">
        <a href="<?php echo e(route('student.grade-submissions.list')); ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left" style="margin-right: 8px;"></i>
            Back to Grade Submissions
        </a>
    </div>

</div>

<style>
.submission-view-container {
    padding: 20px;
    max-width: 800px;
    margin: 20px auto;
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.submission-view-container h1 {
    color: #333;
    margin-bottom: 20px;
    font-size: 24px;
    text-align: center;
}

.submission-details {
    background-color: #f8f9fa;
    padding: 15px;
    border-radius: 5px;
    margin-bottom: 20px;
    border: 1px solid #e9ecef;
}

.submission-details p {
    margin: 5px 0;
    color: #555;
}

.submission-view-container h3 {
    margin-top: 25px;
    margin-bottom: 15px;
    color: #555;
    font-size: 20px;
    border-bottom: 1px solid #eee;
    padding-bottom: 5px;
}

.subjects-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 20px;
}

.subjects-table th,
.subjects-table td {
    border: 1px solid #ddd;
    padding: 10px;
    text-align: left;
}

.subjects-table th {
    background-color: #f2f2f2;
    font-weight: bold;
    color: #333;
}

.status {
    padding: 4px 8px;
    border-radius: 3px;
    font-size: 14px;
    font-weight: 500;
}

.status.pending {
    background: #fff3cd;
    color: #856404;
}

.status.approved {
    background: #d4edda;
    color: #155724;
}

.status.rejected {
    background: #f8d7da;
    color: #721c24;
}

.status.submitted {
     background-color: #cce5ff;
     color: #004085;
}

.btn-secondary {
    display: inline-block;
    padding: 8px 16px;
    color: #fff;
    background-color: #6c757d;
    border-color: #6c757d;
    text-align: center;
    vertical-align: middle;
    border: 1px solid transparent;
    border-radius: .25rem;
    text-decoration: none;
    transition: color .15s ease-in-out,background-color .15s ease-in-out,border-color .15s ease-in-out,box-shadow .15s ease-in-out;
}

.btn-secondary:hover {
    color: #fff;
    background-color: #5a6268;
    border-color: #545b62;
}

</style>
<style>
/* Responsive adjustments for student submission detail */
@media (max-width: 768px) {
  .submission-view-container { padding: 15px; margin: 10px; }
  .subjects-table { display: block; overflow-x: auto; -webkit-overflow-scrolling: touch; }
  .subjects-table table { min-width: 520px; }
  thead th { position: sticky; top: 0; z-index: 1; }
  .btn-secondary { width: 100%; text-align: center; }
}

@media (max-width: 480px) {
  .submission-view-container h1 { font-size: 20px; }
  .submission-details { padding: 12px; }
}
</style>
<?php $__env->stopSection(); ?> 
<?php echo $__env->make('layouts.student_layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\CAPSTONE\PN_Systems\group_13\resources\views/student/view_submission.blade.php ENDPATH**/ ?>