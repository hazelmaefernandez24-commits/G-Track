<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports</title>
     <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet"/>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <style>
         body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            background: #f6f8fa;
        }
       
        header {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            background-color: #22BBEA;
            color: white;
            padding: 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 100px;
            z-index: 1000; /* makes sure it stays above other elements */
        }

        /* Since header is fixed, push down the rest of the content */
        .container-fluid {
            margin-top: 100px;
        }

        /* Logout removed - only available in dashboard */
        .logo {
            margin-left: 0;
        }
        .logo img {
        /* constrain logo height so header stays compact */
        height: 56px;
        width: auto;
        margin-left: 0;
        display: block;
        }

        .header-right {
        font-family: 'Poppins', sans-serif;
        flex: 1;
        display: flex;
        justify-content: flex-end;
        align-items: center;
        font-size: 18px;
        font-weight: 500;
        }
        
        .container-fluid {
            display: flex;
            min-height: 100vh;
        }
        .sidebar {
            border-right: 3px solid #22BBEA;
            background: #f8f9fa;
            min-width: 250px;
            max-width: 280px;
            padding-top: 20px;
            padding-bottom: 30px;
            height: 200vh;
        }
        .sidebar ul {
            list-style: none;
            padding-left: 0;
            margin: 0;
        }
        .sidebar ul li {
            margin: 15px 0;
            border-radius: 5px;
            transition: background-color 0.3s ease, color 0.3s ease;
        }
        .sidebar ul li a {
            font-family: 'Poppins', sans-serif;
            text-decoration: none;
            color: black;
            display: flex;
            align-items: center;
            padding: 10px 15px;
            border-radius: 5px;
            font-size: 18px;
            font-weight: 500;
            transition: color 0.3s ease;
        }
        .sidebar ul li:hover {
            background: #fa5408;
            color: white;
            max-width: 100%;
        }
        .sidebar ul li:hover a {
            color: white;
        }
        .sidebar-icon {
            width: 30px;
            height: 30px;
            margin-right: 8px;
            vertical-align: middle;
        }
        .content {
            flex: 1;
            padding: 0;
            background: #f6f8fa;
        }
    </style>
</head>
<body>
    <!-- Header always at the top -->
    <header>
        <div class="logo">
            <img src="{{ asset('images/pnlogo-header.png') }}" alt="PN Logo">
        </div>
        
        <!-- Logout removed - only available in dashboard -->
    </header>
    <div class="container-fluid">
        <div class="row">
            <!-- Include consistent admin sidebar -->
            @include('partials.sidebar')
            
            <!-- Main content beside sidebar -->
            <main class="main-content" style="min-height: 90vh; padding: 20px;">
                <div class="container-fluid py-3">
                    @yield('content')
                </div>
            </main>
        </div>
    </div>

    <!-- Logout form moved to header -->
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>