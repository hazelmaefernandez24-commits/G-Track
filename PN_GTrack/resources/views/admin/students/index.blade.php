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
    .btn-primary { background: var(--sidebar-active); background-color: #22bbea; color: #fff; }
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
                                <button type="button" class="btn btn-danger" style="padding:4px 8px; font-size:12px;" onclick="confirmDelete('{{ $student->id }}')">Delete</button>
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
            @if($errors->any() && old('_method') !== 'PUT')
                <div class="alert alert-danger">
                    <ul style="margin:0; padding-left:20px;">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <form id="addForm" action="/admin/students" method="POST">
                @csrf
                <div class="form-group">
                    <label>Student ID</label>
                    <input type="text" name="student_id" class="form-control" value="{{ old('_method') !== 'PUT' ? old('student_id') : '' }}">
                </div>
                <div class="form-group">
                    <label>First Name</label>
                    <input type="text" name="first_name" id="add_first_name" class="form-control" value="{{ old('_method') !== 'PUT' ? old('first_name') : '' }}">
                </div>
                <div class="form-group">
                    <label>Middle Initial</label>
                    <input type="text" name="middle_initial" id="add_middle_initial" class="form-control" maxlength="1" placeholder="(optional)" value="{{ old('_method') !== 'PUT' ? old('middle_initial') : '' }}">
                </div>
                <div class="form-group">
                    <label>Last Name</label>
                    <input type="text" name="last_name" id="add_last_name" class="form-control" value="{{ old('_method') !== 'PUT' ? old('last_name') : '' }}">
                </div>
                <input type="hidden" name="name" id="add_full_name" value="{{ old('_method') !== 'PUT' ? old('name') : '' }}">
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control" value="{{ old('_method') !== 'PUT' ? old('email') : '' }}">
                </div>
                <div class="form-group">
                    <label>Class</label>
                    <select name="class" class="form-control">
                        <option value="2026"{{ old('_method') !== 'PUT' && old('class') === '2026' ? ' selected' : '' }}>2026</option>
                        <option value="2027"{{ old('_method') !== 'PUT' && old('class') === '2027' ? ' selected' : '' }}>2027</option>
                        <option value="2028"{{ old('_method') !== 'PUT' && old('class') === '2028' ? ' selected' : '' }}>2028</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Gender</label>
                    <select name="gender" class="form-control">
                        <option value="Male"{{ old('_method') !== 'PUT' && old('gender') === 'Male' ? ' selected' : '' }}>Male</option>
                        <option value="Female"{{ old('_method') !== 'PUT' && old('gender') === 'Female' ? ' selected' : '' }}>Female</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Contact</label>
                    <input type="text" name="contact" class="form-control" value="{{ old('_method') !== 'PUT' ? old('contact') : '' }}">
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" class="form-control">
                </div>
                <div class="form-group">
                    <label>Confirm Password</label>
                    <input type="password" name="password_confirmation" class="form-control" required>
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
            @if($errors->any() && old('_method') === 'PUT')
                <div id="editErrorAlert" class="alert alert-danger">
                    <ul style="margin:0; padding-left:20px;">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <form id="editForm" method="POST" action="/admin/students/{{ old('_method') === 'PUT' ? old('edit_record_id') : '' }}">
                @csrf
                @method('PUT')
                <input type="hidden" name="edit_record_id" id="edit_record_id" value="{{ old('edit_record_id') }}">
                <div class="form-group">
                    <label>Student ID</label>
                    <input type="text" name="student_id" id="edit_student_id" class="form-control" required value="{{ old('_method') === 'PUT' ? old('student_id') : '' }}">
                </div>
                <div class="form-group">
                    <label>First Name</label>
                    <input type="text" name="first_name" id="edit_first_name" class="form-control" required value="{{ old('_method') === 'PUT' ? old('first_name') : '' }}">
                </div>
                <div class="form-group">
                    <label>Middle Initial</label>
                    <input type="text" name="middle_initial" id="edit_middle_initial" class="form-control" maxlength="1" placeholder="(optional)" value="{{ old('_method') === 'PUT' ? old('middle_initial') : '' }}">
                </div>
                <div class="form-group">
                    <label>Last Name</label>
                    <input type="text" name="last_name" id="edit_last_name" class="form-control" required value="{{ old('_method') === 'PUT' ? old('last_name') : '' }}">
                </div>
                <input type="hidden" name="name" id="edit_name">
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" id="edit_email" class="form-control" required value="{{ old('_method') === 'PUT' ? old('email') : '' }}">
                </div>
                <div class="form-group">
                    <label>Class</label>
                    <select name="class" id="edit_class" class="form-control" required>
                        <option value="2026"{{ old('_method') === 'PUT' && old('class') === '2026' ? ' selected' : '' }}>2026</option>
                        <option value="2027"{{ old('_method') === 'PUT' && old('class') === '2027' ? ' selected' : '' }}>2027</option>
                        <option value="2028"{{ old('_method') === 'PUT' && old('class') === '2028' ? ' selected' : '' }}>2028</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Gender</label>
                    <select name="gender" id="edit_gender" class="form-control" required>
                        <option value="Male"{{ old('_method') === 'PUT' && old('gender') === 'Male' ? ' selected' : '' }}>Male</option>
                        <option value="Female"{{ old('_method') === 'PUT' && old('gender') === 'Female' ? ' selected' : '' }}>Female</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Contact</label>
                    <input type="text" name="contact" id="edit_contact" class="form-control" required value="{{ old('_method') === 'PUT' ? old('contact') : '' }}">
                </div>
                <div class="form-group">
                    <label>Current Password</label>
                    <input type="password" name="current_password" id="edit_current_password" class="form-control" autocomplete="current-password">
                </div>
                <div class="form-group">
                    <label>New Password</label>
                    <input type="password" name="new_password" id="edit_new_password" class="form-control" autocomplete="new-password">
                </div>
                <div class="form-group">
                    <label>Confirm New Password</label>
                    <input type="password" name="new_password_confirmation" id="edit_new_password_confirmation" class="form-control" autocomplete="new-password">
                </div>
                <div style="display:flex; gap:10px; margin-top:20px;">
                    <button type="submit" class="btn btn-primary" style="flex:1;">Update Student</button>
                    <button type="button" class="btn btn-danger" style="flex:1;" onclick="closeModal('editModal')">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Modal -->
    <div id="deleteModal" class="custom-modal">
        <div class="custom-modal-content" style="width: 350px; text-align: center;">
            <h3 style="color: #dc2626; margin-top: 0;">Confirm Delete</h3>
            <p style="margin: 20px 0; font-size: 15px; color: #4b5563;">Are you sure you want to delete this student?</p>
            <form id="deleteForm" method="POST" action="">
                @csrf
                @method('DELETE')
                <div style="display:flex; gap:10px; margin-top:20px;">
                    <button type="submit" class="btn btn-danger" style="flex:1;">Yes, Delete</button>
                    <button type="button" class="btn btn-primary" style="flex:1; background-color: #6b7280; border: none;" onclick="closeModal('deleteModal')">Cancel</button>
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
        
        function clearEditErrors() {
            const errorAlert = document.getElementById('editErrorAlert');
            if (errorAlert) {
                errorAlert.style.display = 'none';
            }
        }

        function confirmDelete(id) {
            document.getElementById('deleteForm').action = '/admin/students/' + id;
            openModal('deleteModal');
        }

        function editStudent(student) {
            clearEditErrors();
            document.getElementById('editForm').action = '/admin/students/' + student.id;
            document.getElementById('edit_record_id').value = student.id;
            document.getElementById('edit_student_id').value = student.student_id;

            // Try to split existing full name into parts
            const name = student.name || '';
            const parts = name.trim().split(/\s+/).filter(Boolean);
            let first = '';
            let last = '';
            let middle = '';
            if (parts.length === 1) {
                first = parts[0];
            } else if (parts.length === 2) {
                first = parts[0];
                last = parts[1];
            } else if (parts.length >= 3) {
                first = parts[0];
                last = parts[parts.length - 1];
                // take middle initial from the middle parts
                middle = parts.slice(1, parts.length - 1).map(p => p.charAt(0)).join('');
            }

            document.getElementById('edit_first_name').value = first;
            document.getElementById('edit_middle_initial').value = middle ? middle.charAt(0) : '';
            document.getElementById('edit_last_name').value = last;
            document.getElementById('edit_email').value = student.email;
            document.getElementById('edit_class').value = student.class;
            document.getElementById('edit_gender').value = student.gender;
            document.getElementById('edit_contact').value = student.contact;
            // set hidden edit_name as fallback
            document.getElementById('edit_name').value = name;
            openModal('editModal');
        }

        // Compose full name before submitting add or edit forms
        document.getElementById('addForm')?.addEventListener('submit', function (e) {
            const f = document.getElementById('add_first_name')?.value.trim() || '';
            const m = document.getElementById('add_middle_initial')?.value.trim() || '';
            const l = document.getElementById('add_last_name')?.value.trim() || '';
            const full = `${f}${m ? ' ' + m + '.' : ''}${l ? ' ' + l : ''}`.trim();
            document.getElementById('add_full_name').value = full;
        });

        document.getElementById('editForm')?.addEventListener('submit', function (e) {
            const f = document.getElementById('edit_first_name')?.value.trim() || '';
            const m = document.getElementById('edit_middle_initial')?.value.trim() || '';
            const l = document.getElementById('edit_last_name')?.value.trim() || '';
            const full = `${f}${m ? ' ' + m + '.' : ''}${l ? ' ' + l : ''}`.trim();
            document.getElementById('edit_name').value = full;
        });

        document.addEventListener('DOMContentLoaded', function () {
            const openAddOnError = {!! json_encode($errors->any() && old('_method') !== 'PUT') !!};
            const openEditOnError = {!! json_encode($errors->any() && old('_method') === 'PUT' && old('edit_record_id')) !!};
            if (openAddOnError) {
                openModal('addModal');
            }
            if (openEditOnError) {
                const editId = {!! json_encode(old('edit_record_id')) !!};
                if (editId) {
                    document.getElementById('editForm').action = '/admin/students/' + editId;
                    openModal('editModal');
                }
            }
        });

        window.onclick = function(event) {
            if (event.target.className === 'custom-modal') {
                event.target.style.display = 'none';
            }
        }
    </script>
@endsection
