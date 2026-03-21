

<?php $__env->startSection('title', $title); ?>

<?php $__env->startSection('content'); ?>
<style>
    .card { background:#fff; border-radius:8px; box-shadow:0 2px 10px rgba(0,0,0,.06); padding:16px; margin:20px 0; }
    .grid { display:grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap:12px; }
    .field label { display:block; font-size:.85rem; color:#6c757d; margin-bottom:6px; }
    .field input { width:100%; padding:8px 10px; border:1px solid #ced4da; border-radius:6px; }
    .btn { padding:8px 10px; border-radius:6px; text-decoration:none; border:none; cursor:pointer; color:#fff; background:#007bff; }
    .btn-secondary { background:#6c757d; }
    .field-days { grid-column: 1 / -1; }
    .days-wrap { border:1px solid #ced4da; border-radius:6px; padding:10px; background:#fff; }
    .days-grid { display:grid; grid-template-columns: repeat(4, minmax(120px, 1fr)); gap:10px 18px; align-items:center; }
    .days-grid label { display:flex; align-items:center; gap:8px; line-height:1.2; }
    @media (max-width: 992px) { .days-grid { grid-template-columns: repeat(3, minmax(120px, 1fr)); } }
    @media (max-width: 576px) { .days-grid { grid-template-columns: repeat(2, minmax(120px, 1fr)); } }
    .header-row { display:flex; align-items:center; justify-content:space-between; gap:10px; }
    .subtitle { color:#6c757d; font-size:.9rem; margin:0; }
</style>
<div class="container-fluid" style="padding:20px; max-width:1200px;">
    <div class="header-row">
        <div>
            <h2 style="margin:0;">Edit Internship</h2>
            <p class="subtitle">Update company, time of duty, and dates</p>
        </div>
        <div>
            <a href="<?php echo e(route('training.internship.index')); ?>" class="btn btn-secondary">Back</a>
        </div>
    </div>

    <form method="POST" action="<?php echo e(route('training.internship.update', $internship->id)); ?>" class="card" style="max-width:100%;">
        <?php echo csrf_field(); ?>
        <?php echo method_field('PUT'); ?>
        <div class="grid">
            <div class="field">
                <label>Company</label>
                <input type="text" name="company" value="<?php echo e(old('company', $internship->company)); ?>" />
            </div>
            <div class="field field-days">
                <label>Days</label>
                <div class="days-wrap">
                    <div class="days-grid">
                        <?php
                            $weekdays = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'];
                            $selectedDays = old('days');
                            if ($selectedDays === null) {
                                if (is_array($internship->days)) {
                                    $selectedDays = $internship->days;
                                } else {
                                    $decoded = null;
                                    if (!empty($internship->time_of_duty)) {
                                        $decoded = json_decode($internship->time_of_duty, true);
                                    }
                                    $selectedDays = $decoded['days'] ?? [];
                                }
                            }
                        ?>
                        <?php $__currentLoopData = $weekdays; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $w): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <label>
                                <input type="checkbox" name="days[]" value="<?php echo e($w); ?>" <?php echo e(in_array($w, (array) $selectedDays, true) ? 'checked' : ''); ?>> <span><?php echo e($w); ?></span>
                            </label>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>
            </div>
            <div class="field">
                <label>Time In</label>
                <input type="time" name="time_in" value="<?php echo e(old('time_in', optional($internship->time_in)->format('H:i'))); ?>" required />
            </div>
            <div class="field">
                <label>Time Out</label>
                <input type="time" name="time_out" value="<?php echo e(old('time_out', optional($internship->time_out)->format('H:i'))); ?>" required />
            </div>
            <div class="field">
                <label>Start date</label>
                <input type="date" name="start_date" value="<?php echo e(old('start_date', optional($internship->start_date)->format('Y-m-d'))); ?>" required />
            </div>
            <div class="field">
                <label>Tentative end date</label>
                <input type="date" name="end_date" value="<?php echo e(old('end_date', optional($internship->end_date)->format('Y-m-d'))); ?>" />
            </div>
        </div>
        <div class="modal-actions" style="margin-top:16px; display:flex; gap:10px; justify-content:flex-end;">
            <a href="<?php echo e(route('training.internship.index')); ?>" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn">Save</button>
        </div>
    </form>
</div>
<?php $__env->stopSection(); ?>



<?php echo $__env->make('layouts.nav', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\CAPSTONE\PN_Systems\group_13\resources\views/training/internship/edit.blade.php ENDPATH**/ ?>