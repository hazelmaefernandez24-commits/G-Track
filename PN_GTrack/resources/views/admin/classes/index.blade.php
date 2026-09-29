@extends('layouts.app')

@section('title', 'Class Management')
@section('subtitle', 'Manage student classes and batches')

@push('styles')
<style>
    .class-mgmt-card {
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
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
    }
    .btn-primary { background: var(--sidebar-active); background-color: #22bbea; color: #fff; }
    .btn-primary:hover { background-color: #1aa6d2; }
    .btn-success { background: var(--online); color: #fff; }
    .btn-success:hover { opacity: 0.9; }
    .btn-danger { background: var(--offline); color: #fff; }
    .btn-danger:hover { opacity: 0.9; }
    .btn-secondary { background: #e2e8f0; color: #475569; }
    .btn-secondary:hover { background: #cbd5e1; }
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
        letter-spacing: 0.5px;
    }
    td {
        padding: 14px 12px;
        border-bottom: 1px solid var(--border-color);
        font-size: 14px;
        vertical-align: middle;
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
        width: 420px;
        max-height: 85vh;
        overflow-y: auto;
        box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25);
        margin: 0;
    }
    .form-group { margin-bottom: 16px; }
    .form-group label { display: block; margin-bottom: 6px; font-size: 13px; font-weight: 600; color: #334155; }
    .form-control {
        width: 100%;
        padding: 10px 12px;
        border: 1px solid var(--border-color);
        border-radius: 8px;
        font-size: 14px;
        box-sizing: border-box;
        transition: border-color 0.2s;
    }
    .form-control:focus {
        outline: none;
        border-color: #22bbea;
        box-shadow: 0 0 0 3px rgba(34, 187, 234, 0.15);
    }
    .alert {
        padding: 12px 16px;
        border-radius: 8px;
        margin-bottom: 16px;
        font-size: 14px;
    }
    .alert-success { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
    .alert-danger { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
    .students-badge {
        display: inline-flex;
        align-items: center;
        padding: 3px 10px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        background: #f1f5f9;
        color: #334155;
    }
    .students-badge.has-students {
        background: #e0f2fe;
        color: #0369a1;
    }
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

        <div class="class-mgmt-card">
            <div style="display:flex; justify-content:space-between; align-items:center;">
                <div>
                    <h2 style="margin:0; font-size:20px; font-weight:700;">All Classes</h2>
                    <p style="margin:4px 0 0; color:var(--text-muted); font-size:13px;">Create and manage graduating batches or classes</p>
                </div>
                <button class="btn btn-primary" onclick="openModal('addModal')">
                    <i data-lucide="plus" style="width:16px; height:16px;"></i>
                    Add New Class
                </button>
            </div>

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Class Name</th>
                            <th>Number of Students</th>
                            <th>Date Created</th>
                            <th style="text-align:right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($classes as $c)
                        <tr>
                            <td style="font-weight:700; color:#0f172a; font-size:15px;">
                                Class {{ $c->name }}
                            </td>
                            <td>
                                <span class="students-badge {{ $c->students_count > 0 ? 'has-students' : '' }}">
                                    {{ $c->students_count }} {{ Str::plural('Student', $c->students_count) }}
                                </span>
                            </td>
                            <td style="color:#64748b; font-size:13px;">
                                {{ $c->created_at ? $c->created_at->format('M d, Y') : '—' }}
                            </td>
                            <td style="text-align:right; white-space:nowrap;">
                                <button class="btn btn-success" style="padding:5px 10px; font-size:12px;" 
                                    onclick="editClass({{ json_encode($c) }})">
                                    <i data-lucide="edit-3" style="width:13px; height:13px;"></i>
                                    Edit
                                </button>
                                <button type="button" class="btn btn-danger" style="padding:5px 10px; font-size:12px;" 
                                    onclick="confirmDelete({{ json_encode($c->id) }}, {{ json_encode($c->name) }}, {{ $c->students_count }})">
                                    <i data-lucide="trash-2" style="width:13px; height:13px;"></i>
                                    Delete
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" style="text-align:center; padding:32px; color:var(--text-muted);">
                                No classes found. Click "Add New Class" to create one.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Add Modal -->
    <div id="addModal" class="custom-modal">
        <div class="custom-modal-content">
            <h3 style="margin-top:0; font-size:18px;">Add New Class</h3>
            @if($errors->any() && old('_method') !== 'PUT')
                <div class="alert alert-danger">
                    <ul style="margin:0; padding-left:18px;">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <form id="addForm" action="/admin/classes" method="POST">
                @csrf
                <div class="form-group">
                    <label>Class Name <span style="color:#ef4444;">*</span></label>
                    <input type="text" name="name" class="form-control" required placeholder="e.g. 2029" value="{{ old('_method') !== 'PUT' ? old('name') : '' }}">
                    <small style="color:var(--text-muted); font-size:12px;">This is the identifier used when tagging students and notifications.</small>
                </div>
                <div style="display:flex; gap:10px; margin-top:24px;">
                    <button type="submit" class="btn btn-primary" style="flex:1;">Save Class</button>
                    <button type="button" class="btn btn-secondary" style="flex:1;" onclick="closeModal('addModal')">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Modal -->
    <div id="editModal" class="custom-modal">
        <div class="custom-modal-content">
            <h3 style="margin-top:0; font-size:18px;">Edit Class</h3>
            @if($errors->any() && old('_method') === 'PUT')
                <div id="editErrorAlert" class="alert alert-danger">
                    <ul style="margin:0; padding-left:18px;">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <form id="editForm" method="POST" action="/admin/classes/{{ old('_method') === 'PUT' ? old('edit_record_id') : '' }}">
                @csrf
                @method('PUT')
                <input type="hidden" name="edit_record_id" id="edit_record_id" value="{{ old('edit_record_id') }}">
                <div class="form-group">
                    <label>Class Name <span style="color:#ef4444;">*</span></label>
                    <input type="text" name="name" id="edit_name" class="form-control" required value="{{ old('_method') === 'PUT' ? old('name') : '' }}">
                    <small style="color:#f59e0b; font-size:12px; display:block; margin-top:4px;">
                        ⚠️ Changing this will automatically update the class for all currently enrolled students.
                    </small>
                </div>
                <div style="display:flex; gap:10px; margin-top:24px;">
                    <button type="submit" class="btn btn-primary" style="flex:1;">Update Class</button>
                    <button type="button" class="btn btn-secondary" style="flex:1;" onclick="closeModal('editModal')">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="custom-modal">
        <div class="custom-modal-content">
            <h3 style="margin-top:0; color:#ef4444; font-size:18px;">Delete Class</h3>
            
            <div id="deleteWarningWithStudents" style="display:none;">
                <div class="alert alert-danger">
                    <strong id="deleteBlockedMsg">Cannot delete this class.</strong>
                    <p style="margin:6px 0 0; font-size:13px;">There are students currently assigned to this class. Please reassign or update those students before deleting this class.</p>
                </div>
                <div style="display:flex; justify-content:flex-end; margin-top:20px;">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('deleteModal')">Close</button>
                </div>
            </div>

            <div id="deleteConfirmationSafe" style="display:none;">
                <p style="font-size:14px; color:#475569; margin:0 0 20px;">
                    Are you sure you want to delete <strong id="deleteClassName"></strong>? This action cannot be undone.
                </p>
                <form id="deleteForm" method="POST" action="">
                    @csrf
                    @method('DELETE')
                    <div style="display:flex; gap:10px;">
                        <button type="submit" class="btn btn-danger" style="flex:1;">Yes, Delete</button>
                        <button type="button" class="btn btn-secondary" style="flex:1;" onclick="closeModal('deleteModal')">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    function openModal(id) { 
        const m = document.getElementById(id);
        if (m) m.style.display = 'flex'; 
    }

    function closeModal(id) { 
        const m = document.getElementById(id);
        if (m) m.style.display = 'none'; 
    }

    function editClass(c) {
        document.getElementById('editForm').action = '/admin/classes/' + c.id;
        document.getElementById('edit_record_id').value = c.id;
        document.getElementById('edit_name').value = c.name;
        openModal('editModal');
    }

    function confirmDelete(id, name, studentCount) {
        if (studentCount > 0) {
            document.getElementById('deleteBlockedMsg').textContent = `Cannot delete Class '${name}'. (${studentCount} student(s) enrolled)`;
            document.getElementById('deleteWarningWithStudents').style.display = 'block';
            document.getElementById('deleteConfirmationSafe').style.display = 'none';
        } else {
            document.getElementById('deleteClassName').textContent = `Class '${name}'`;
            document.getElementById('deleteForm').action = '/admin/classes/' + id;
            document.getElementById('deleteWarningWithStudents').style.display = 'none';
            document.getElementById('deleteConfirmationSafe').style.display = 'block';
        }
        openModal('deleteModal');
    }

    document.addEventListener('DOMContentLoaded', function () {
        const openAddOnError = {!! json_encode($errors->any() && old('_method') !== 'PUT') !!};
        const openEditOnError = {!! json_encode($errors->any() && old('_method') === 'PUT' && old('edit_record_id')) !!};
        if (openAddOnError) {
            openModal('addModal');
        }
        if (openEditOnError) {
            const editId = {!! json_encode(old('edit_record_id')) !!};
            if (editId) {
                document.getElementById('editForm').action = '/admin/classes/' + editId;
                openModal('editModal');
            }
        }
        if (window.lucide) {
            lucide.createIcons();
        }
    });

    window.onclick = function(event) {
        if (event.target.className === 'custom-modal') {
            event.target.style.display = 'none';
        }
    }
</script>
@endpush
