<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>General Task Assignment</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        body { background: #eef2f7; font-family: "Poppins", sans-serif; }
        .welcome-container {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            background: #eef2f7;
        }
        .welcome-title {
            font-size: 2.7rem;
            font-weight: 700;
            color: #222;
            margin-bottom: 1.5rem;
        }
        .welcome-btn {
            font-size: 1.3rem;
            padding: 0.8rem 2.5rem;
            border-radius: 8px;
            font-weight: 600;
            background: #1565c0;
            color: #fff;
            border: none;
            transition: background 0.2s;
        }
        .welcome-btn:hover {
            background: #0d47a1;
            color: #fff;
        }
    </style>
</head>
<body>
    <div class="welcome-container">
        <div class="welcome-title">General Task Assignment</div>
        <div class="card shadow-lg p-4" style="min-width: 350px; max-width: 400px; border-radius: 18px; background: #fff;">
            <div class="list-group mb-3">
                <a href="{{ route('dashboard') }}" class="list-group-item list-group-item-action d-flex align-items-center" style="font-size: 1.1rem; font-weight: 600;">
                    <span class="me-2"><i class="bi bi-speedometer2"></i></span> Dashboard
                </a>
                <a href="{{ route('students.index') }}" class="list-group-item list-group-item-action d-flex align-items-center" style="font-size: 1.1rem; font-weight: 600;">
                    <span class="me-2"><i class="bi bi-people"></i></span> Students
                </a>
                <a href="{{ route('categories.index') }}" class="list-group-item list-group-item-action d-flex align-items-center" style="font-size: 1.1rem; font-weight: 600;">
                    <span class="me-2"><i class="bi bi-tags"></i></span> Categories
                </a>
                <a href="{{ route('assignments.index') }}" class="list-group-item list-group-item-action d-flex align-items-center" style="font-size: 1.1rem; font-weight: 600;">
                    <span class="me-2"><i class="bi bi-list-task"></i></span> Assignments
                </a>
            </div>
            <div class="text-center mt-3">
                <small class="text-muted">Manage and track all task assignments across categories and more.</small>
            </div>
        </div>
    </div>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
</body>
</html>