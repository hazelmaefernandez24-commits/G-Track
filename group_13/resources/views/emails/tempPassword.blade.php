<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Your Temporary Password</title>
</head>
<body>
<h1>Welcome, {{ $user->user_fname }}!</h1>
<p>Your account has been created successfully. With your role as: <h2>{{ $user->user_role }}</h2> Below are your temporary login credentials:</p>
<p><strong>User ID:</strong> {{ $user->user_id }}</p>
<p><strong>Temporary Password:</strong> {{ $tempPassword }}</p>
<p>Please log in and change your password as soon as possible.</p>
<p>Thank you!</p>
</body>
</html>
