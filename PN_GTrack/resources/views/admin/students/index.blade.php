@extends('layouts.app')

@section('title', 'Student Management')
@section('subtitle', 'Manage student records and information')

@push('styles')
<style>
    .student-mgmt-card {
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
</style>
@endpush

@section('content')
    <div style="max-width: 1200px;">
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

        <div class="student-mgmt-card">
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
    <div id="addModal" class="custom-modal">
        <div class="custom-modal-content">
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
    <div id="editModal" class="custom-modal">
        <div class="custom-modal-content">
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
            if (event.target.className === 'custom-modal') {
                event.target.style.display = 'none';
            }
        }
    </script>
@endsection
