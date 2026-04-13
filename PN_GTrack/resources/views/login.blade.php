<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'PN_GTrack') }} - G!Track</title> 
    <style>
        :root{
            --bg:#f6f7fb;
            --text:#0f172a;
            --muted:#6b7280;
            --card-border:#e5e7eb;
            --shadow: 0 1px 2px rgba(0,0,0,.04);
            --blue:#4f46e5;
            --blue-dark:#2563eb;
        }
        *{box-sizing:border-box;}
        body{
            margin:0;
            background: linear-gradient(135deg, #f0f0f3 0%, #f4f3f5 100%);
            color:var(--text);
            font-family: ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Arial, "Noto Sans", "Liberation Sans", sans-serif;
            min-height:100vh;
            display:flex;
            align-items:center;
            justify-content:center;
        }
        .login-container{
            width:100%;
            max-width:420px;
            padding:20px;
        }
        .login-card{
            background:#fff;
            border-radius:16px;
            box-shadow: 0 20px 25px rgba(0,0,0,.15);
            padding:40px;
            position:relative;
        }
        .logo-section{
            text-align:center;
            margin-bottom:30px;
        }
        .logo-section img{
            width:80px;
            height:auto;
            margin-bottom:16px;
        }
        .logo-title{
            font-size:24px;
            font-weight:800;
            margin:0 0 4px 0;
            background: linear-gradient(135deg, #22bbee 0%, #22BBEE 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .logo-subtitle{
            font-size:14px;
            color:#6b7280;
            margin:0;
        }
        .form-group{
            margin-bottom:20px;
        }
        .form-label{
            display:block;
            font-size:13px;
            font-weight:700;
            color:#1f2937;
            margin-bottom:6px;
        }
        .form-input{
            width:100%;
            padding:12px 14px;
            border:1px solid #d1d5db;
            border-radius:8px;
            font-size:14px;
            font-family:inherit;
            transition: all 0.2s;
        }
        .form-input:focus{
            outline:none;
            border-color:#2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }
        .form-input::placeholder{
            color:#9ca3af;
        }
        .submit-btn{
            width:100%;
            padding:12px 16px;
            background: linear-gradient(135deg, #22BBEE 0%, #22BBEE 100%);
            color:#fff;
            border:none;
            border-radius:8px;
            font-size:14px;
            font-weight:700;
            cursor:pointer;
            transition: all 0.2s;
            box-shadow: 0 4px 6px rgba(102, 126, 234, 0.3);
        }
        .submit-btn:hover{
            transform: translateY(-2px);
            box-shadow: 0 10px 15px rgba(102, 126, 234, 0.4);
        }
        .submit-btn:active{
            transform: translateY(0);
        }
        .forgot-password{
            display:inline-block;
            margin-top:12px;
            color:#2563eb;
            text-decoration:none;
            font-weight:600;
            transition: color 0.2s;
        }
        .forgot-password:hover{
            color:#1d4ed8;
        }
        .error-message{
            background:#FEE2E2;
            color:#991B1B;
            padding:12px 14px;
            border-radius:8px;
            font-size:13px;
            margin-bottom:20px;
            border-left:4px solid #EF4444;
        }
        @media (max-width: 480px){
            .login-card{
                padding:24px;
            }
            .logo-title{
                font-size:20px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            
            <div class="logo-section">
                <img src="{{ asset('images/gtrack.png') }}" alt="PN_GTrack Logo">
                <p class="logo-subtitle">Admin Authentication Portal</p>
            </div>

            
            @if ($errors->any())
                <div class="error-message">
                    <strong>Login Failed</strong><br>
                    @foreach ($errors->all() as $error)
                        {{ $error }}<br>
                    @endforeach
                </div>
            @endif

            
            <form method="POST" action="{{ route('login') }}">
                @csrf

               
                <div class="form-group">
    <label class="form-label" for="email">Email</label>
    <input 
        type="email" 
        class="form-input @error('email') is-invalid @enderror" 
        id="email" 
        name="email" 
        placeholder="Enter your Email"
        value="{{ old('email') }}"
        required 
        autofocus>
    @error('email')
        <span class="error-message">{{ $message }}</span>
    @enderror
</div>
                
                <div class="form-group">
                    <label class="form-label" for="password">Password</label>
                    <input 
                        type="password" 
                        class="form-input @error('password') is-invalid @enderror" 
                        id="password" 
                        name="password" 
                        placeholder="Enter your password"
                        required>
                    @error('password')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                
                <button type="submit" class="submit-btn">Login</button>

                
                <div style="text-align:center;">
                    <a href="#" class="forgot-password">Forgot Password?</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
