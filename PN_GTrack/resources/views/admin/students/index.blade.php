<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Management - G!Track</title>
    <style>
        :root{
            --bg:#f6f7fb;
            --text:#0f172a;
            --muted:#6b7280;
            --card-border:#e5e7eb;
            --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --blue:#2563eb;
            --green:#16a34a;
            --red:#dc2626;
        }
        body{
            margin:0;
            background:var(--bg);
            color:var(--text);
            font-family: ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Arial, "Noto Sans", "Liberation Sans", sans-serif;
        }
        .header{
            height:64px;
            background:var(--blue);
            color:#fff;
            display:flex;
            align-items:center;
            padding:0 20px;
            justify-content: space-between;
        }
        .header h1{ font-size:18px; margin:0; }
        .container{
            max-width:1200px;
            margin:20px auto;
            padding:0 20px;
        }
        .card{
            background:#fff;
            border-radius:12px;
            box-shadow: var(--shadow);
            padding:20px;
            margin-bottom:20px;
        }
        .btn{
            padding:8px 16px;
            border-radius:8px;
            border:none;
            font-weight:600;
            cursor:pointer;
            transition: all 0.2s;
            text-decoration:none;
            font-size:14px;
        }
        .btn-primary{ background:var(--blue); color:#fff; }
        .btn-success{ background:var(--green); color:#fff; }
        .btn-danger{ background:var(--red); color:#fff; }
        .table-container{ overflow-x:auto; }
        table{
            width:100%;
            border-collapse:collapse;
            margin-top:20px;
        }
        th{
            text-align:left;
            padding:12px;
            border-bottom:2px solid var(--card-border);
            color:var(--muted);
            font-size:12px;
            text-transform:uppercase;
        }
        td{
            padding:12px;
            border-bottom:1px solid var(--card-border);
            font-size:14px;
        }
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            backdrop-filter: blur(4px);
            align-items: center;
            justify-content: center;
        }
        .modal-content {
            background-color: #fff;
            padding: 24px;
            border-radius: 16px;
            width: 400px;
            max-height: 80vh;
            overflow-y: auto;
            box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25);
            margin: 0;
        }
        .form-group{ margin-bottom:16px; }
        .form-group label{ display:block; margin-bottom:4px; font-size:13px; font-weight:600; }
        .form-control{
            width:100%;
            padding:10px;
            border:1px solid var(--card-border);
            border-radius:8px;
            font-size:14px;
            box-sizing:border-box;
        }
        .alert{
            padding:12px;
            border-radius:8px;
            margin-bottom:16px;
            font-size:14px;
        }
        .alert-success{ background:#dcfce7; color:#166534; }
    </style>
</head>
<body>
    <header class="header">
        <h1>Student Management</h1>
        <a href="/dashboard" style="color:#fff; text-decoration:none; font-weight:600;">Back to Dashboard</a>
    </header>

    <div class="container">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger" style="background:#fee2e2; color:#991b1b; padding:12px; border-radius:8px; margin-bottom:16px;">
                <ul style="margin:0; padding-left:20px;">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card">
            <div style="display:flex; justify-content:space-between; align-items:center;">
                <h2 style="margin:0; font-size:20px;">All Students</h2>
                <button class="btn btn-primary" onclick="openModal('addModal')">Add New Student</button>
            </div>

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Student ID</th>
                            <th>Name</th>
                            <th>Class</th>
                            <th>Gender</th>
                            <th>Contact</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($students as $student)
                        <tr>
                            <td style="font-weight:700;">{{ $student->student_id }}</td>
                            <td>{{ $student->name }}</td>
                            <td>{{ $student->class }}</td>
                            <td>{{ $student->gender }}</td>
                            <td>{{ $student->contact }}</td>
                            <td>
                                <button class="btn btn-success" style="padding:4px 8px; font-size:12px;" 
                                    onclick="editStudent({{ json_encode($student) }})">Edit</button>
                                <form action="/admin/students/{{ $student->id }}" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-danger" style="padding:4px 8px; font-size:12px;">Delete</button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Add Modal -->
    <div id="addModal" class="modal">
        <div class="modal-content">
            <h3>Add New Student</h3>
            <form action="/admin/students" method="POST">
                @csrf
                <div class="form-group">
                    <label>Student ID</label>
                    <input type="text" name="student_id" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Name</label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Class</label>
                    <select name="class" class="form-control" required>
                        <option value="2026">2026</option>
                        <option value="2027">2027</option>
                        <option value="2028">2028</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Gender</label>
                    <select name="gender" class="form-control" required>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Contact</label>
                    <input type="text" name="contact" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <div style="display:flex; gap:10px; margin-top:20px;">
                    <button type="submit" class="btn btn-primary" style="flex:1;">Save Student</button>
                    <button type="button" class="btn btn-danger" style="flex:1;" onclick="closeModal('addModal')">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <h3>Edit Student</h3>
            <form id="editForm" method="POST">
                @csrf
                @method('PUT')
                <div class="form-group">
                    <label>Student ID</label>
                    <input type="text" name="student_id" id="edit_student_id" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Name</label>
                    <input type="text" name="name" id="edit_name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" id="edit_email" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Class</label>
                    <select name="class" id="edit_class" class="form-control" required>
                        <option value="2026">2026</option>
                        <option value="2027">2027</option>
                        <option value="2028">2028</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Gender</label>
                    <select name="gender" id="edit_gender" class="form-control" required>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Contact</label>
                    <input type="text" name="contact" id="edit_contact" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Password (Leave blank to keep current)</label>
                    <input type="password" name="password" class="form-control">
                </div>
                <div style="display:flex; gap:10px; margin-top:20px;">
                    <button type="submit" class="btn btn-primary" style="flex:1;">Update Student</button>
                    <button type="button" class="btn btn-danger" style="flex:1;" onclick="closeModal('editModal')">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openModal(id) { 
            const modal = document.getElementById(id);
            modal.style.display = 'flex'; 
        }
        function closeModal(id) { 
            const modal = document.getElementById(id);
            modal.style.display = 'none'; 
        }
        
        function editStudent(student) {
            document.getElementById('editForm').action = '/admin/students/' + student.id;
            document.getElementById('edit_student_id').value = student.student_id;
            document.getElementById('edit_name').value = student.name;
            document.getElementById('edit_email').value = student.email;
            document.getElementById('edit_class').value = student.class;
            document.getElementById('edit_gender').value = student.gender;
            document.getElementById('edit_contact').value = student.contact;
            openModal('editModal');
        }

        window.onclick = function(event) {
            if (event.target.className === 'modal') {
                event.target.style.display = 'none';
            }
        }
    </script>
</body>
</html>
