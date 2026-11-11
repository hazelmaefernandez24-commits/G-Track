<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Change Temporary Password - {{ config('app.name', 'PN_GTrack') }}</title>
    <link rel="stylesheet" href="{{ asset('css/global-font.css') }}">
    <style>
        * { box-sizing: border-box; }
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
            background: #f6f7fb;
            color: #0f172a;
            font-family: 'Inter', sans-serif;
        }
        .password-card {
            width: 100%;
            max-width: 440px;
            padding: 30px;
            border-radius: 16px;
            background: #fff;
            box-shadow: 0 20px 25px rgba(0, 0, 0, .12);
        }
        h1 { margin: 0 0 8px; font-size: 23px; }
        p { margin: 0 0 22px; color: #64748b; font-size: 14px; line-height: 1.5; }
        .form-group { margin-bottom: 18px; }
        label { display: block; margin-bottom: 6px; font-size: 13px; font-weight: 700; }
        input {
            width: 100%;
            padding: 12px 14px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font: inherit;
        }
        input:focus { outline: 2px solid #22bbee; outline-offset: 1px; }
        .errors {
            margin-bottom: 18px;
            padding: 12px 14px;
            border-radius: 8px;
            background: #fee2e2;
            color: #991b1b;
            font-size: 13px;
        }
        .errors ul { margin: 0; padding-left: 20px; }
        button {
            width: 100%;
            padding: 12px 16px;
            border: 0;
            border-radius: 8px;
            background: #22bbee;
            color: #fff;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <main class="password-card">
        <h1>Choose your password</h1>
        <p>Your account was created with a temporary password. Set a new password to continue to G!Track.</p>

        @if ($errors->any())
            <div class="errors" role="alert">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('account.first-login-password.update') }}">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label for="new_password">New Password</label>
                <input id="new_password" type="password" name="new_password" minlength="6" autocomplete="new-password" required autofocus>
            </div>

            <div class="form-group">
                <label for="new_password_confirmation">Confirm New Password</label>
                <input id="new_password_confirmation" type="password" name="new_password_confirmation" minlength="6" autocomplete="new-password" required>
            </div>

            <button type="submit">Save Password</button>
        </form>
    </main>
</body>
</html>
