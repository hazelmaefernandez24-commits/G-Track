<!DOCTYPE html>
<html>
<head>
    <title>Student Activity</title>
    <style>
        body { margin: 0; font-family: 'Inter', sans-serif; background: #F8FBFF; color: #404040; }
        h2 { margin: 0 0 14px; color: #404040; }
        p { margin: 4px 0; color: rgba(64,64,64,0.75); }
        table {
            border-collapse: collapse;
            width: 100%;
            background: #FFFFFF;
            border-radius: 18px;
            overflow: hidden;
            box-shadow: 0 20px 50px -30px rgba(64, 64, 64, 0.25);
        }
        th, td {
            border: 1px solid rgba(34, 187, 234, 0.18);
            padding: 12px 10px;
            text-align: left;
        }
        th {
            background-color: rgba(34, 187, 234, 0.12);
            color: #404040;
            font-weight: 700;
        }
        .online {
            color: #22BBEA;
            font-weight: 700;
        }
        .offline {
            color: #FF9933;
            font-weight: 700;
        }
    </style>
</head>
<body>
    <h2>Student Activity</h2>
    <p>Total Students: {{ $total }}</p>
    <p>Online: {{ $students->where('status', true)->count() }} | Offline: {{ $students->where('status', false)->count() }}</p>
    <p>Latest Location Update: {{ $students->max('last_update') }}</p>

    <table>
        <thead>
            <tr>
                <th>Student ID</th>
                <th>Name</th>
                <th>Class</th>
                <th>Gender</th>
                <th>Status</th>
                <th>Battery</th>  
                <th>Signal</th>    
                <th>Last Update</th>
                <th>Contact</th>
            </tr>
        </thead>
        <tbody>
            @foreach($students as $student)
            <tr>
                <td>{{ $student->student_id }}</td>
                <td>{{ $student->name }}</td>
                <td>{{ $student->class }}</td>
                <td>{{ $student->gender }}</td>
                <td>
                    @if($student->status)
                        <span class="online">● Online</span>
                    @else
                        <span class="offline">● Offline</span>
                    @endif
                </td>
                <td>{{ isset($student->battery_level) ? $student->battery_level . '%' : 'N/A' }}</td>   
                <td>{{ $student->signal_status }}</td>   
                <td>{{ $student->last_update }}</td>
                <td>{{ $student->contact }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
