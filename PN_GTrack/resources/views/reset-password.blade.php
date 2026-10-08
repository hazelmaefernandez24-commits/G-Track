<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reset Password</title>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Arial, "Noto Sans", "Liberation Sans", sans-serif;
            background: linear-gradient(135deg, #f0f0f3 0%, #f4f3f5 100%);
            color: #0f172a;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }
        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background-image: url('{{ asset("images/PNlogo.png") }}');
            background-size: cover;
            background-position: center;
            opacity: 0.03;
            z-index: -1;
            pointer-events: none;
        }
        .card {
            width: 100%;
            max-width: 430px;
            background: #ffffff;
            border-radius: 14px;
            box-shadow: 0 16px 40px rgba(15, 23, 42, 0.12);
            padding: 32px 28px;
        }
        h2 {
            margin: 0 0 20px;
            text-align: center;
            font-size: 28px;
            color: #111827;
        }
        .form-group {
            margin-bottom: 18px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            color: #374151;
            font-weight: 700;
        }
        input {
            width: 100%;
            padding: 12px 14px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 14px;
            background: #fff;
        }
        input:focus {
            outline: none;
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
        }
        .btn {
            width: 100%;
            margin-top: 8px;
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 12px 16px;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
        }
        .btn:hover {
            background: linear-gradient(135deg, #1d4ed8, #1e40af);
        }
        .error-box {
            margin-bottom: 18px;
            padding: 12px 14px;
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-left: 4px solid #dc2626;
            border-radius: 8px;
            color: #991b1b;
            font-size: 13px;
        }
        .success-box {
            margin-bottom: 18px;
            padding: 12px 14px;
            background: #ecfdf5;
            border: 1px solid #a7f3d0;
            border-left: 4px solid #16a34a;
            border-radius: 8px;
            color: #065f46;
            font-size: 13px;
        }
        .muted {
            text-align: center;
            margin-top: 14px;
            font-size: 13px;
            color: #6b7280;
        }
        a {
            color: #2563eb;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="card">
        <h2>Reset Password</h2>
        <p class="muted">Enter your Staff ID and the email address registered to your account, then choose a new password. No email will be sent.</p>

        @if ($errors->any())
            <div class="error-box">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('reset-password.store') }}">
            @csrf

            <div class="form-group">
                <label for="staff_id">Staff ID</label>
                <input type="text" id="staff_id" name="staff_id" value="{{ old('staff_id') }}" placeholder="Enter your Staff ID" autocomplete="username" required>
            </div>

            <div class="form-group">
                <label for="email">Registered Email</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="Enter your registered email" autocomplete="email" required>
            </div>

            <div class="form-group">
                <label for="password">New Password</label>
                <input type="password" id="password" name="password" placeholder="Enter new password" autocomplete="new-password" minlength="6" required>
            </div>

            <div class="form-group">
                <label for="password_confirmation">Confirm New Password</label>
                <input type="password" id="password_confirmation" name="password_confirmation" placeholder="Confirm new password" autocomplete="new-password" minlength="6" required>
            </div>

            <button type="submit" class="btn">Reset Password</button>
        </form>

        <div class="muted">
            <a href="{{ route('login') }}">Back to Login</a>
        </div>
    </div>
</body>
</html>
