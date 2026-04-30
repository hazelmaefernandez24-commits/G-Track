<!DOCTYPE html>
<html>
<head>
    <title>Student Activity</title>
    <style>
        table {
            border-collapse: collapse;
            width: 100%;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
        }
        th {
            background-color: #f4f4f4;
        }
        .online {
            color: green;
            font-weight: bold;
        }
        .offline {
            color: red;
            font-weight: bold;
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
