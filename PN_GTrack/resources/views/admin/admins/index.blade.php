<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Management - G!Track</title>
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
            max-width:1000px;
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
        .alert-danger{ background:#fee2e2; color:#991b1b; }
        .role-badge{
            padding:2px 8px;
            border-radius:4px;
            font-size:11px;
            font-weight:700;
            text-transform:uppercase;
        }
        .role-main{ background:#eff6ff; color:#1e40af; }
        .role-education{ background:#f3f4f6; color:#374151; }
    </style>
</head>
<body>
    <header class="header">
        <h1>Admin Management</h1>
        <a href="/dashboard" style="color:#fff; text-decoration:none; font-weight:600;">Back to Dashboard</a>
    </header>

    <div class="container">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
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
                <h2 style="margin:0; font-size:20px;">System Administrators</h2>
                <button class="btn btn-primary" onclick="openModal('addModal')">Add New Admin</button>
            </div>

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Staff ID</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($admins as $admin)
                        <tr>
                            <td style="font-weight:700;">{{ $admin->staff_id }}</td>
                            <td>{{ $admin->email }}</td>
                            <td>
                                <span class="role-badge {{ $admin->role === 'main' ? 'role-main' : 'role-education' }}">
                                    {{ $admin->role }}
                                </span>
                            </td>
                            <td>
                                <button class="btn btn-success" style="padding:4px 8px; font-size:12px;" 
                                    onclick="editAdmin({{ json_encode($admin) }})">Edit</button>
                                @if($admin->id !== Auth::guard('admin')->id())
                                <form action="/admin/admins/{{ $admin->id }}" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-danger" style="padding:4px 8px; font-size:12px;">Delete</button>
                                </form>
                                @endif
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
            <h3>Add New Admin</h3>
            <form action="/admin/admins" method="POST">
                @csrf
                <div class="form-group">
                    <label>Staff ID</label>
                    <input type="text" name="staff_id" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Role</label>
                    <select name="role" class="form-control" required>
                        <option value="education">Education</option>
                        <option value="main">Main Admin</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <div style="display:flex; gap:10px; margin-top:20px;">
                    <button type="submit" class="btn btn-primary" style="flex:1;">Save Admin</button>
                    <button type="button" class="btn btn-danger" style="flex:1;" onclick="closeModal('addModal')">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <h3>Edit Admin</h3>
            <form id="editForm" method="POST">
                @csrf
                @method('PUT')
                <div class="form-group">
                    <label>Staff ID</label>
                    <input type="text" name="staff_id" id="edit_staff_id" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" id="edit_email" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Role</label>
                    <select name="role" id="edit_role" class="form-control" required>
                        <option value="education">Education</option>
                        <option value="main">Main Admin</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Password (Leave blank to keep current)</label>
                    <input type="password" name="password" class="form-control">
                </div>
                <div style="display:flex; gap:10px; margin-top:20px;">
                    <button type="submit" class="btn btn-primary" style="flex:1;">Update Admin</button>
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
        
        function editAdmin(admin) {
            document.getElementById('editForm').action = '/admin/admins/' + admin.id;
            document.getElementById('edit_staff_id').value = admin.staff_id;
            document.getElementById('edit_email').value = admin.email;
            document.getElementById('edit_role').value = admin.role;
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
