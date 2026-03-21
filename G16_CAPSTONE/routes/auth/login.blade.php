<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Tasking Hub System</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
    
</head>
<body>
    <div class="login-wrapper">
        <div class="login-box">
            <div class="logo-container">
                <img src="{{ asset('images/pn-logo.png') }}" alt="PN Logo" class="login-logo">
            </div>
            
            <h1 class="login-title">LOG IN</h1>
            
            @if($errors->any())
                <div class="alert alert-danger">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('auth.login') }}" class="login-form">
                @csrf
                <div class="form-group">
                    <label for="userid">Faculty ID | Student ID</label>
                    <input type="text" id="userid" name="userid" placeholder="Enter your user id" required>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Enter your password" required>
                </div>

                <button type="submit" class="login-button">Login</button>
            </form>
                 <div class="forgot-password">
            <a href="#">Forgot password?</a>
        </div>
        </div>
    </div>
</body>
</html>
