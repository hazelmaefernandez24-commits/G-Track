<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="{{ asset('css/forgot-password.css') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap"
        rel="stylesheet">
    <title>Forgot Password</title>
</head>

<body>
    <a href="{{ route('login') }}">
        <div class="icon-back">
            <svg class="w-6 h-6 text-gray-800 dark:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                width="30" height="30" fill="none" viewBox="0 0 24 24">
                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                    d="m15 19-7-7 7-7" />
            </svg>

        </div>
    </a>

    <h2>Forgot Password</h2>

    <div class="forgot-container">
        <img src="{{ asset('images/pnlogo.png') }}" alt="Logo">
        <form action="{{ route('forgot-password.verify') }}" method="POST">
            @csrf
            <label for="user_id">Enter your id number</label>
            <input type="text" name="user_id" id="user_id" value="{{ old('user_id') }}">
            @error('user_id')
                <div class="alert alert-danger" style="color: red">
                    <i data-feather="alert-circle" class="w-4 h-4 mr-1"></i>
                    {{ $message }}
                </div>
            @enderror
            <label for="email">Enter your email address</label>
            <input type="email" name="email" id="email" value="{{ old('email') }}">
            @error('email')
                <div class="alert alert-danger" style="color: red">
                    <i data-feather="alert-circle" class="w-4 h-4 mr-1"></i>
                    {{ $message }}
                </div>
            @enderror
            <button type="submit">Verify</button>
        </form>
    </div>
</body>

</html>
