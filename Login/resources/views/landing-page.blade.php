<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PNPH Main Menu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
   <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --accent-color: #e74c3c;
            --light-color: #ecf0f1;
            --dark-color: #2c3e50;
        }

        * {
            font-family: 'Poppins', sans-serif !important;
        }

        /* Preserve icon fonts */
        .fas, .far, .fal, .fab, .fa,
        [class*="fa-"],
        .material-icons,
        .glyphicon {
            font-family: "Font Awesome 6 Free", "Font Awesome 6 Pro", "Font Awesome 5 Free", "Font Awesome 5 Pro", "Material Icons", "Glyphicons Halflings" !important;
        }

        /* Preserve SVG icons */
        svg {
            font-family: inherit !important;
        }

        body {
            font-family: 'Poppins', sans-serif !important;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            margin: 0;
            padding: 2rem 1rem;
        }

        .main-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }

        .header {
            color: var(--dark-color);
            position: relative;
            padding-bottom: 1rem;
        }

        .header h1 {
            font-weight: 700;
            font-size: 3rem;
            margin-bottom: 0.5rem;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.1);
        }

        .header p {
            font-size: 1.1rem;
            color: #7f8c8d;
        }

        .menu-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
            margin-top: 2rem;
        }

        .menu-item {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            text-align: center;
            transition: all 0.3s ease;
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
            border: none;
            cursor: pointer;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            position: relative;
            overflow: hidden;
        }

        .menu-item-link {
            text-decoration: none;
            color: inherit;
            display: block;
            border-radius: 15px;
            transition: all 0.3s ease;
        }

        .menu-item-link:hover .menu-item {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.15);
        }

        .menu-item-link:hover .menu-item::before {
            height: 8px;
            background: var(--accent-color);
        }

        .menu-item::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: var(--secondary-color);
            transition: all 0.3s ease;
        }

        .menu-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.15);
        }

        .menu-item:hover::before {
            height: 8px;
            background: var(--accent-color);
        }

        .menu-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: var(--secondary-color);
            transition: all 0.3s ease;
        }

        .menu-item:hover .menu-icon {
            transform: scale(1.1) rotate(5deg);
            color: var(--accent-color);
        }

        .menu-title {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: var(--dark-color);
        }

        .menu-desc {
            color: #7f8c8d;
            font-size: 0.9rem;
            margin-bottom: 0;
        }

        /* Mobile Responsiveness */
        @media (max-width: 768px) {
            .main-container {
                padding: 1rem;
            }

            .menu-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
                margin-top: 1.5rem;
            }

            .header h1 {
                font-size: 2rem;
            }

            .header p {
                font-size: 1rem;
            }

            .menu-item {
                padding: 1.5rem;
            }

            .menu-icon {
                font-size: 2.5rem;
            }

            .menu-title {
                font-size: 1.3rem;
            }

            .menu-desc {
                font-size: 0.85rem;
            }
        }

        @media (max-width: 480px) {
            .main-container {
                padding: 0.5rem;
            }

            .header h1 {
                font-size: 1.8rem;
            }

            .header p {
                font-size: 0.9rem;
            }

            .menu-item {
                padding: 1.2rem;
            }

            .menu-icon {
                font-size: 2.2rem;
            }

            .menu-title {
                font-size: 1.2rem;
            }

            .menu-desc {
                font-size: 0.8rem;
            }

            .logout-top-right {
                position: absolute;
                top: 1rem;
                right: 1rem;
            }
        }
    </style>
</head>
<body>
    @php($token = request('token'))
    <div class="main-container">
        <div class="container my-4">
            <div class="d-flex justify-content-between align-items-start">
                <div class="header">
                    <h1>PNPH Main Menu</h1>
                    <p>Welcome to the Passerelles Numeriques Philippines System</p>
                </div>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <input type="hidden" name="token" value="{{ $token }}">
                    <button type="submit" class="btn btn-danger btn-md">Logout</button>
                </form>
            </div>
        </div>

        <div class="menu-grid">
            <!-- Menu Item 1 -->
            @if ($user_role == 'admin' || $user_role == 'educator' || $user_role == 'student' || $user_role == 'training')
                <a href="{{ env('SYSTEM_1_URL') }}?token={{ $token }}" class="menu-item-link">
                    <div class="menu-item">
                        <i class="fas fa-graduation-cap menu-icon"></i>
                        <h3 class="menu-title">Student Academic Monitoring System</h3>
                        <p class="menu-desc">Analyzes students' performance data</p>
                    </div>
                </a>
            @else
                <a href="#" class="menu-item-link" style="pointer-events: none; opacity: 0.5;">
                    <div class="menu-item">
                        <i class="fas fa-graduation-cap menu-icon"></i>
                        <h3 class="menu-title">Student Academic Monitoring System</h3>
                        <p class="menu-desc">Analyzes students' performance data</p>
                    </div>
                </a>
            @endif

            <!-- Menu Item 2 -->
            @if ($user_role == 'educator')
                <a href="{{ env('SYSTEM_2_URL') }}?token={{ $token }}" class="menu-item-link">
                    <div class="menu-item">
                        <i class="fas fa-chalkboard-teacher menu-icon"></i>
                        <h3 class="menu-title">Logify</h3>
                        <p class="menu-desc">Manage Academic, Going Out and Visitor Logs</p>
                    </div>
                </a>
            @else
                <a href="#" class="menu-item-link" style="pointer-events: none; opacity: 0.5;">
                    <div class="menu-item">
                        <i class="fas fa-chalkboard-teacher menu-icon"></i>
                        <h3 class="menu-title">Logify</h3>
                        <p class="menu-desc">Manage Academic, Going Out and Visitor Logs</p>
                    </div>
                </a>
            @endif

            <!-- Menu Item 3 -->
            @if ($user_role == 'educator' || $user_role == 'student' || $user_role == 'inspector')
                <a href="{{ env('SYSTEM_3_URL') }}?token={{ $token }}" class="menu-item-link">
                    <div class="menu-item">
                        <i class="fas fa-book menu-icon"></i>
                        <h3 class="menu-title">Tasking Hub</h3>
                        <p class="menu-desc">Course catalog and schedules</p>
                    </div>
                </a>
            @else
                <a href="#" class="menu-item-link" style="pointer-events: none; opacity: 0.5;">
                    <div class="menu-item">
                        <i class="fas fa-book menu-icon"></i>
                        <h3 class="menu-title">Tasking Hub</h3>
                        <p class="menu-desc">Course catalog and schedules</p>
                    </div>
                </a>
            @endif

            <!-- Menu Item 4 -->
            @if ($user_role == 'educator' || $user_role == 'student')
                <a href="{{ env('SYSTEM_4_URL') }}?token={{ $token }}" class="menu-item-link">
                    <div class="menu-item">
                        <i class="fas fa-calendar-alt menu-icon"></i>
                        <h3 class="menu-title">VioLytics</h3>
                        <p class="menu-desc">Violation tracker and management</p>
                    </div>
                </a>
            @else
                <a href="#" class="menu-item-link" style="pointer-events: none; opacity: 0.5;">
                    <div class="menu-item">
                        <i class="fas fa-calendar-alt menu-icon"></i>
                        <h3 class="menu-title">VioLytics</h3>
                        <p class="menu-desc">Violation tracker and management</p>
                    </div>
                </a>
            @endif

            <!-- Menu Item 5 -->
            @if ($user_role == 'kitchen' || $user_role == 'student' || $user_role == 'cook')
                <a href="{{ env('SYSTEM_5_URL') }}?token={{ $token }}" class="menu-item-link">
                    <div class="menu-item">
                        <i class="fas fa-file-alt menu-icon"></i>
                        <h3 class="menu-title">Kitchen Management System</h3>
                        <p class="menu-desc">Manage kitchen operations</p>
                    </div>
                </a>
            @else
                <a href="#" class="menu-item-link" style="pointer-events: none; opacity: 0.5;">
                    <div class="menu-item">
                        <i class="fas fa-file-alt menu-icon"></i>
                        <h3 class="menu-title">Kitchen Management System</h3>
                        <p class="menu-desc">Manage kitchen operations</p>
                    </div>
                </a>
            @endif

            <!-- Menu Item 6 -->
            @if ($user_role == 'finance' || $user_role == 'student' || $user_role == 'cashier')
                <a href="{{ env('SYSTEM_6_URL') }}?token={{ $token }}" class="menu-item-link">
                    <div class="menu-item">
                        <i class="fas fa-cog menu-icon"></i>
                        <h3 class="menu-title">Counterpart Management System</h3>
                        <p class="menu-desc">System configuration</p>
                    </div>
                </a>
            @else
                <a href="#" class="menu-item-link" style="pointer-events: none; opacity: 0.5;">
                    <div class="menu-item">
                        <i class="fas fa-cog menu-icon"></i>
                        <h3 class="menu-title">Counterpart Management System</h3>
                        <p class="menu-desc">System configuration</p>
                    </div>
                </a>
            @endif
        </div>
        @if(request('error'))
            <div class="mt-4 d-flex justify-content-center">
                <div class="text-center alert alert-danger">
                    {{ request('error') }}
                </div>
            </div>
        @endif
    </div>
</body>
</html>

