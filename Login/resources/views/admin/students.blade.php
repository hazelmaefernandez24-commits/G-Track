<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Users - Login System</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            overflow: hidden;
        }

        .header {
            background: linear-gradient(135deg, #22BBEA 0%, #1976d2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }

        .header h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
        }

        .header p {
            font-size: 1.1rem;
            opacity: 0.9;
        }

        .stats {
            display: flex;
            justify-content: space-around;
            padding: 20px;
            background: #f8f9fa;
            border-bottom: 1px solid #e0e0e0;
        }

        .stat-item {
            text-align: center;
        }

        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            color: #22BBEA;
        }

        .stat-label {
            color: #666;
            font-size: 0.9rem;
        }

        .content {
            padding: 30px;
        }

        .search-box {
            margin-bottom: 20px;
            position: relative;
        }

        .search-box input {
            width: 100%;
            padding: 12px 20px;
            border: 2px solid #e0e0e0;
            border-radius: 25px;
            font-size: 1rem;
            outline: none;
            transition: border-color 0.3s;
        }

        .search-box input:focus {
            border-color: #22BBEA;
        }

        .students-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .student-card {
            background: white;
            border: 1px solid #e0e0e0;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .student-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.15);
        }

        .student-header {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }

        .student-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: linear-gradient(135deg, #22BBEA, #1976d2);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 1.2rem;
            margin-right: 15px;
        }

        .student-info h3 {
            color: #333;
            margin-bottom: 5px;
        }

        .student-id {
            color: #666;
            font-size: 0.9rem;
        }

        .student-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-top: 15px;
        }

        .detail-item {
            display: flex;
            flex-direction: column;
        }

        .detail-label {
            font-size: 0.8rem;
            color: #666;
            margin-bottom: 2px;
        }

        .detail-value {
            font-weight: 500;
            color: #333;
        }

        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .status-active {
            background: #e8f5e8;
            color: #2e7d32;
        }

        .status-inactive {
            background: #ffebee;
            color: #c62828;
        }

        .gender-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .gender-m {
            background: #e3f2fd;
            color: #1976d2;
        }

        .gender-f {
            background: #fce4ec;
            color: #c2185b;
        }

        .no-students {
            text-align: center;
            padding: 50px;
            color: #666;
        }

        .back-btn {
            display: inline-block;
            padding: 10px 20px;
            background: #22BBEA;
            color: white;
            text-decoration: none;
            border-radius: 25px;
            margin-bottom: 20px;
            transition: background 0.3s;
        }

        .back-btn:hover {
            background: #1976d2;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🎓 Student Users</h1>
            <p>Login System Database - All Registered Students</p>
        </div>

        <div class="stats">
            <div class="stat-item">
                <div class="stat-number">{{ $students->count() }}</div>
                <div class="stat-label">Total Students</div>
            </div>
            <div class="stat-item">
                <div class="stat-number">{{ $students->where('status', 'active')->count() }}</div>
                <div class="stat-label">Active Students</div>
            </div>
            <div class="stat-item">
                <div class="stat-number">{{ $students->where('gender', 'M')->count() }}</div>
                <div class="stat-label">Male Students</div>
            </div>
            <div class="stat-item">
                <div class="stat-number">{{ $students->where('gender', 'F')->count() }}</div>
                <div class="stat-label">Female Students</div>
            </div>
        </div>

        <div class="content">
            <a href="/" class="back-btn">← Back to Login</a>
            
            <div class="search-box">
                <input type="text" id="searchInput" placeholder="🔍 Search students by name, ID, or batch...">
            </div>

            @if($students->count() > 0)
                <div class="students-grid" id="studentsGrid">
                    @foreach($students as $student)
                        <div class="student-card" data-search="{{ strtolower($student->user_fname . ' ' . $student->user_lname . ' ' . $student->user_id . ' ' . ($student->studentDetail->batch ?? '')) }}">
                            <div class="student-header">
                                <div class="student-avatar">
                                    {{ strtoupper(substr($student->user_fname, 0, 1)) }}{{ strtoupper(substr($student->user_lname, 0, 1)) }}
                                </div>
                                <div class="student-info">
                                    <h3>{{ $student->user_fname }} {{ $student->user_lname }}</h3>
                                    <div class="student-id">ID: {{ $student->user_id }}</div>
                                </div>
                            </div>

                            <div class="student-details">
                                <div class="detail-item">
                                    <span class="detail-label">Email</span>
                                    <span class="detail-value">{{ $student->user_email }}</span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Status</span>
                                    <span class="detail-value">
                                        <span class="status-badge {{ $student->status == 'active' ? 'status-active' : 'status-inactive' }}">
                                            {{ ucfirst($student->status) }}
                                        </span>
                                    </span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Gender</span>
                                    <span class="detail-value">
                                        <span class="gender-badge {{ $student->gender == 'M' ? 'gender-m' : 'gender-f' }}">
                                            {{ $student->gender == 'M' ? 'Male' : 'Female' }}
                                        </span>
                                    </span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Batch</span>
                                    <span class="detail-value">{{ $student->studentDetail->batch ?? 'Not assigned' }}</span>
                                </div>
                                @if($student->studentDetail)
                                <div class="detail-item">
                                    <span class="detail-label">Group</span>
                                    <span class="detail-value">{{ $student->studentDetail->group ?? 'Not assigned' }}</span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Student Number</span>
                                    <span class="detail-value">{{ $student->studentDetail->student_number ?? 'Not assigned' }}</span>
                                </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="no-students">
                    <h3>No student users found</h3>
                    <p>There are currently no users with the "student" role in the database.</p>
                </div>
            @endif
        </div>
    </div>

    <script>
        // Search functionality
        document.getElementById('searchInput').addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const studentCards = document.querySelectorAll('.student-card');
            
            studentCards.forEach(card => {
                const searchData = card.getAttribute('data-search');
                if (searchData.includes(searchTerm)) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>
