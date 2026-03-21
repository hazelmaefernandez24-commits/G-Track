<?php $__env->startSection('title', $title); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid" style="padding: 20px;">
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px;">
        <div style="display: flex; align-items: center; gap: 15px;">
            <img src="<?php echo e(asset('images/analytics.png')); ?>" alt="Calendar Icon" style="width: 32px; height: 32px;">
            <h1 style="color: #333; font-weight: 600; margin: 0; font-size: 24px;">Manage Calendar Events</h1>
        </div>
        <a href="<?php echo e(route('training.calendar.create')); ?>" class="btn btn-primary" style="background-color: #3498db; border: none; padding: 10px 20px; border-radius: 5px; text-decoration: none; color: white; font-weight: 500;">
            <i class="fas fa-plus"></i> Add New Event
        </a>
    </div>
    <hr style="margin-bottom: 20px;">

    <?php if(session('success')): ?>
        <div class="alert alert-success" style="background-color: #d4edda; color: #155724; padding: 12px; border-radius: 5px; margin-bottom: 20px; border: 1px solid #c3e6cb;">
            <?php echo e(session('success')); ?>

        </div>
    <?php endif; ?>

    <div style="background: white; border-radius: 10px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); overflow: hidden;">
        <div style="padding: 20px; border-bottom: 1px solid #e9ecef;">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <h3 style="margin: 0; color: #2c3e50;">Events/Activty & Holidays</h3>
                <div style="display: flex; gap: 10px;">
                    <a href="<?php echo e(route('training.calendar.index')); ?>" class="btn btn-outline-secondary" style="padding: 8px 16px; border: 1px solid #6c757d; color: #6c757d; text-decoration: none; border-radius: 4px;">
                        <i class="fas fa-calendar"></i> Calendar View
                    </a>
                </div>
            </div>
        </div>

        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse;">
                <thead style="background-color: #f8f9fa;">
                    <tr>
                        <th style="padding: 15px; text-align: left; font-weight: 600; color: #495057; border-bottom: 2px solid #dee2e6;">Title</th>
                        <th style="padding: 15px; text-align: left; font-weight: 600; color: #495057; border-bottom: 2px solid #dee2e6;">Category</th>
                        <th style="padding: 15px; text-align: left; font-weight: 600; color: #495057; border-bottom: 2px solid #dee2e6;">Start Date</th>
                        <th style="padding: 15px; text-align: left; font-weight: 600; color: #495057; border-bottom: 2px solid #dee2e6;">End Date</th>
                        <th style="padding: 15px; text-align: left; font-weight: 600; color: #495057; border-bottom: 2px solid #dee2e6;">Semester</th>
                        <th style="padding: 15px; text-align: left; font-weight: 600; color: #495057; border-bottom: 2px solid #dee2e6;">Academic Year</th>
                        <th style="padding: 15px; text-align: center; font-weight: 600; color: #495057; border-bottom: 2px solid #dee2e6;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $events; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $event): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr style="border-bottom: 1px solid #dee2e6;">
                            <td style="padding: 15px; vertical-align: top;">
                                <div style="font-weight: 500; color: #2c3e50;"><?php echo e($event->title); ?></div>
                                <?php if($event->description): ?>
                                    <div style="font-size: 0.9em; color: #6c757d; margin-top: 4px;"><?php echo e(Str::limit($event->description, 50)); ?></div>
                                <?php endif; ?>
                            </td>
                            <td style="padding: 15px; vertical-align: top;">
                                <span class="category-badge" style="
                                    display: inline-block;
                                    padding: 4px 12px;
                                    border-radius: 20px;
                                    font-size: 0.85em;
                                    font-weight: 500;
                                    color: white;
                                    background-color: <?php echo e($event->category_color); ?>;
                                ">
                                    <?php echo e($event->category_label); ?>

                                </span>
                            </td>
                            <td style="padding: 15px; vertical-align: top; color: #495057;">
                                <?php echo e(\Carbon\Carbon::parse($event->start_date)->format('M d, Y')); ?>

                            </td>
                            <td style="padding: 15px; vertical-align: top; color: #495057;">
                                <?php echo e(\Carbon\Carbon::parse($event->end_date)->format('M d, Y')); ?>

                            </td>
                            <td style="padding: 15px; vertical-align: top; color: #495057;">
                                <?php if($event->semester): ?>
                                    <span style="text-transform: capitalize;"><?php echo e($event->semester); ?></span>
                                <?php else: ?>
                                    <span style="color: #6c757d;">-</span>
                                <?php endif; ?>
                            </td>
                            <td style="padding: 15px; vertical-align: top; color: #495057;">
                                <?php echo e($event->academic_year); ?>

                            </td>
                            <td style="padding: 15px; text-align: center; vertical-align: top;">
                                <div style="display: flex; gap: 8px; justify-content: center;">
                                    <a href="<?php echo e(route('training.calendar.edit', $event)); ?>" 
                                       style="background-color: #ffc107; color: #212529; padding: 6px 12px; border-radius: 4px; text-decoration: none; font-size: 0.85em;">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <form method="POST" action="<?php echo e(route('training.calendar.destroy', $event)); ?>" 
                                          style="display: inline;" 
                                          onsubmit="return confirm('Are you sure you want to delete this event?')">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('DELETE'); ?>
                                        <button type="submit" 
                                                style="background-color: #dc3545; color: white; padding: 6px 12px; border: none; border-radius: 4px; cursor: pointer; font-size: 0.85em;">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="7" style="padding: 40px; text-align: center; color: #6c757d;">
                                <div style="font-size: 1.1em; margin-bottom: 10px;">No events found</div>
                                <div style="font-size: 0.9em;">
                                    <a href="<?php echo e(route('training.calendar.create')); ?>" style="color: #3498db; text-decoration: none;">
                                        Click here to add your first event
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if($events->hasPages()): ?>
            <div style="padding: 20px; border-top: 1px solid #dee2e6;">
                <?php echo e($events->links()); ?>

            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.btn:hover {
    opacity: 0.9;
    transform: translateY(-1px);
    transition: all 0.2s ease;
}

.category-badge {
    text-shadow: 0 1px 2px rgba(0,0,0,0.1);
}

table tr:hover {
    background-color: #f8f9fa;
}

.alert {
    animation: slideIn 0.3s ease;
}

@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@media (max-width: 768px) {
    .container-fluid {
        padding: 10px !important;
    }
    
    table {
        font-size: 0.9em;
    }
    
    th, td {
        padding: 10px 8px !important;
    }
    
    .btn {
        padding: 8px 12px !important;
        font-size: 0.8em !important;
    }
}
</style>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.nav', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\CAPSTONE\PN_Systems\group_13\resources\views/training/calendar/manage.blade.php ENDPATH**/ ?>