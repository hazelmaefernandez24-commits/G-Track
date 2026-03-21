<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Your Temporary Password</title>
</head>
<body>
<h1>Welcome, <?php echo e($user->user_fname); ?>!</h1>
<p>Your account has been created successfully. With your role as: <h2><?php echo e($user->user_role); ?></h2> Below are your temporary login credentials:</p>
<p><strong>User ID:</strong> <?php echo e($user->user_id); ?></p>
<p><strong>Temporary Password:</strong> <?php echo e($tempPassword); ?></p>
<p>Please log in and change your password as soon as possible.</p>
<p>Thank you!</p>
</body>
</html>
<?php /**PATH C:\CAPSTONE\PN_Systems\group_13\resources\views/emails/tempPassword.blade.php ENDPATH**/ ?>