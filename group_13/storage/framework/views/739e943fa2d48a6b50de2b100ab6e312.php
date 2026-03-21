<?php $__env->startSection('content'); ?>

<style>
/* Simpler, cleaner visuals */
.submissions-main-container { max-width: 1100px; margin: 0 auto; padding: 20px; background:#fff; }
.submissions-header { background:#22bbea; color:#fff; padding:18px 20px; border-radius:10px; margin-bottom:16px; }
.submissions-title { font-size:24px; font-weight:600; margin:0; display:flex; gap:10px; align-items:center; }
.submissions-subtitle { margin:6px 0 0 34px; opacity:.95; font-size:14px; }

.filter-section { background:#fff; border:1px solid #e9ecef; border-radius:10px; margin-bottom:16px; }
.filter-header { padding:12px 14px; border-bottom:1px solid #e9ecef; background:#f8f9fa; color:#333; }
.filter-title { margin:0; font-size:14px; color:#333; display:flex; align-items:center; gap:8px; }
.filter-content { padding:12px 14px; }
.filter-grid { display:grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap:10px; }
.filter-label { font-size:12px; color:#6c757d; }
.filter-select { padding:8px 10px; border:1px solid #ced4da; border-radius:8px; background:#fff; }

.card-slim { background:#fff; border:1px solid #e9ecef; border-radius:10px; }
.card-slim + .card-slim { margin-top:12px; }
.card-slim-header { padding:12px 14px; border-bottom:1px solid #f1f3f5; display:flex; justify-content:space-between; align-items:center; }
.card-slim-body { padding:12px 14px; }

.table-responsive { overflow-x:auto; }
table { width:100%; border-collapse:collapse; }
thead th { background:#f8f9fa; color:#6c757d; font-weight:600; text-transform:uppercase; font-size:12px; padding:10px; border-bottom:1px solid #e9ecef; }
tbody td { padding:10px; border-bottom:1px solid #f1f3f5; font-size:14px; }

.pill { display:inline-block; padding:4px 8px; border-radius:999px; font-size:12px; font-weight:600; }
.pill.approved { background:#e7f5e9; color:#1e8e4a; }
.pill.rejected { background:#fde8e7; color:#c0392b; }
.pill.pending { background:#fff4e0; color:#b46900; }
.pill.review { background:#eaf4fe; color:#1e74c8; }

.btn-ghost { background:#fff; border:1px solid #e9ecef; color:#333; padding:8px 12px; border-radius:8px; text-decoration:none; font-weight:500; }
.btn-ghost:hover { background:#f8f9fa; }
.btn-primary { background:#22bbea; border:none; color:#fff; padding:8px 12px; border-radius:8px; text-decoration:none; font-weight:600; }
.btn-primary:hover { opacity:.95; }

.empty { text-align:center; color:#6c757d; padding:24px 10px; }

@media (max-width: 768px) {
  .filter-grid { grid-template-columns: 1fr; }
}
</style>

<style>
/* Mobile responsiveness for grade submissions list */
@media (max-width: 768px) {
  .submissions-main-container { padding: 12px; }
  .submissions-header { padding:14px; }
  .submissions-title { font-size:20px; }
  .filter-section { border-radius:8px; }
  .filter-content { padding:10px; }
  .card-slim-header { flex-direction: column; align-items: flex-start; gap: 6px; }
  .card-slim-body { padding:8px; }
  .table-responsive { overflow-x:auto; -webkit-overflow-scrolling:touch; }
  table { min-width: 560px; }
  thead th { position: sticky; top: 0; z-index: 1; }
  .btn-primary, .btn-ghost { padding:8px 10px; font-size: 14px; }
}

@media (max-width: 480px) {
  .submissions-title { font-size:18px; }
  .filter-select { width: 100%; }
  .btn-primary, .btn-ghost { width: auto; }
}
</style>

<div class="submissions-main-container">
    <!-- Page Header -->
    <div class="submissions-header">
        <h1 class="submissions-title">
            <i class="fas fa-file-alt"></i>
            My Grade Submissions
        </h1>
        <p class="submissions-subtitle">View and manage all your grade submission records</p>
    </div>

    <!-- Filter Section -->
    <div class="filter-section">
        <div class="filter-header">
            <h3 class="filter-title">
                <i class="fas fa-filter"></i>
                Filter Options
            </h3>
        </div>
        <div class="filter-content">
            <form method="GET" action="<?php echo e(route('student.grade-submissions.list')); ?>" class="filter-form">
                <div class="filter-grid">
                    <div class="filter-group">
                        <label for="filter_key" class="filter-label">Filter by Period</label>
                        <select name="filter_key" id="filter_key" class="filter-select" onchange="this.form.submit()">
                            <option value="">All Submissions</option>
                            <?php if(isset($filterOptions)): ?>
                                <?php $__currentLoopData = $filterOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($option); ?>" <?php echo e(request('filter_key') == $option ? 'selected' : ''); ?>><?php echo e($option); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            <?php endif; ?>
                        </select>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Submissions Section -->
    <?php if(isset($gradeSubmissions) && $gradeSubmissions->count()): ?>
        <div class="card-slim">
            <div class="card-slim-header">
                <div style="font-weight:600;color:#333;">Grade Submissions</div>
            </div>
            <div class="card-slim-body">
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Period</th>
                                <th>Academic Year</th>
                                <th>Status</th>
                                <th>Updated</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = $gradeSubmissions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $submission): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td>
                                        <strong><?php echo e($submission->semester); ?> <?php echo e(ucfirst($submission->term)); ?></strong>
                                    </td>
                                    <td>AY <?php echo e($submission->academic_year); ?></td>
                                    <td>
                                        <span class="pill <?php echo e(strtolower($submission->status)); ?>"><?php echo e(ucfirst($submission->status)); ?></span>
                                    </td>
                                    <td><?php echo e(optional($submission->updated_at)->format('M d, Y')); ?></td>
                                    <td>
                                        <a href="<?php echo e(route('student.view-submission', $submission->id)); ?>" class="btn-primary">View</a>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="card-slim">
            <div class="card-slim-body empty">
                No grade submissions found.
            </div>
        </div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.student_layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\CAPSTONE\PN_Systems\group_13\resources\views/student/grade_submissions_list.blade.php ENDPATH**/ ?>