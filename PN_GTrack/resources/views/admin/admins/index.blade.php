@extends('layouts.app')

@section('title', 'Admin Management')
@section('subtitle', 'Manage system administrators')

@push('styles')
<style>
    .admin-mgmt-card {
        background: #fff;
        border-radius: 12px;
        box-shadow: var(--card-shadow);
        padding: 24px;
        margin-bottom: 20px;
        border: 1px solid var(--border-color);
    }
    .btn {
        padding: 8px 16px;
        border-radius: 8px;
        border: none;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
        text-decoration: none;
        font-size: 14px;
    }
    .btn-primary { background: var(--sidebar-active); color: #fff; }
    .btn-success { background: var(--online); color: #fff; }
    .btn-danger { background: var(--offline); color: #fff; }
    .table-container { overflow-x: auto; }
    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
    }
    th {
        text-align: left;
        padding: 12px;
        border-bottom: 2px solid var(--border-color);
        color: var(--text-muted);
        font-size: 12px;
        text-transform: uppercase;
    }
    td {
        padding: 12px;
        border-bottom: 1px solid var(--border-color);
        font-size: 14px;
    }
    .custom-modal {
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
    .custom-modal-content {
        background-color: #fff;
        padding: 24px;
        border-radius: 16px;
        width: 400px;
        max-height: 80vh;
        overflow-y: auto;
        box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25);
        margin: 0;
    }
    .form-group { margin-bottom: 16px; }
    .form-group label { display: block; margin-bottom: 4px; font-size: 13px; font-weight: 600; }
    .form-control {
        width: 100%;
        padding: 10px;
        border: 1px solid var(--border-color);
        border-radius: 8px;
        font-size: 14px;
        box-sizing: border-box;
    }
    .alert {
        padding: 12px;
        border-radius: 8px;
        margin-bottom: 16px;
        font-size: 14px;
    }
    .alert-success { background: #dcfce7; color: #166534; }
    .alert-danger { background: #fee2e2; color: #991b1b; }
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
@endpush

@section('content')
    <div style="max-width: 1000px;">
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

        <div class="admin-mgmt-card">
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
    <div id="addModal" class="custom-modal">
        <div class="custom-modal-content">
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
    <div id="editModal" class="custom-modal">
        <div class="custom-modal-content">
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
            if (event.target.className === 'custom-modal') {
                event.target.style.display = 'none';
            }
        }
    </script>
@endsection
