@extends('layouts.app')

@php($isEditing = isset($admin))

@section('title', $isEditing ? 'Edit Admin' : 'Add Admin')
@section('subtitle', $isEditing ? 'Update administrator information' : 'Create a system administrator')

@push('styles')
<style>
    .form-card {
        max-width: 760px;
        padding: 28px;
        background: #fff;
        border: 1px solid var(--border-color);
        border-radius: 16px;
        box-shadow: var(--card-shadow);
    }
    .form-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 16px;
    }
    .form-group { margin-bottom: 16px; }
    .form-group label {
        display: block;
        margin-bottom: 6px;
        color: #334155;
        font-size: 13px;
        font-weight: 600;
    }
    .form-control {
        width: 100%;
        box-sizing: border-box;
        padding: 10px 12px;
        border: 1px solid var(--border-color);
        border-radius: 8px;
        background: #fff;
        color: var(--text-main);
        font-size: 14px;
    }
    .form-control:focus {
        outline: none;
        border-color: var(--sidebar-active);
        box-shadow: 0 0 0 3px rgba(34, 187, 234, 0.15);
    }
    .form-help { color: var(--text-muted); font-size: 12px; }
    .alert-danger {
        margin-bottom: 20px;
        padding: 12px 16px;
        border: 1px solid #fecaca;
        border-radius: 8px;
        background: #fee2e2;
        color: #991b1b;
    }
    .form-actions { display: flex; gap: 10px; margin-top: 24px; }
    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 10px 16px;
        border: 0;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 600;
        text-decoration: none;
        cursor: pointer;
    }
    .btn-primary { background: #22bbea; color: #fff; }
    .btn-secondary { background: #e2e8f0; color: #475569; }
    @media (max-width: 640px) {
        .form-grid { grid-template-columns: 1fr; gap: 0; }
        .form-card { padding: 20px; }
        .form-actions { flex-direction: column; }
    }
</style>
@endpush

@section('content')
<div class="form-card">
    @if($errors->any())
        <div class="alert-danger" role="alert">
            <ul style="margin:0; padding-left:20px;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ $isEditing ? route('admins.update', $admin) : route('admins.store') }}" method="POST">
        @csrf
        @if($isEditing)
            @method('PUT')
            <div class="form-group">
                <label for="staff_id">Staff ID</label>
                <input id="staff_id" class="form-control" value="{{ $admin->staff_id }}" readonly>
            </div>
        @else
            <p class="form-help" style="margin:0 0 20px;">The Staff ID is generated automatically from the selected role.</p>
        @endif

        <div class="form-grid">
            <div class="form-group">
                <label for="first_name">First Name</label>
                <input id="first_name" type="text" name="first_name" class="form-control" value="{{ old('first_name', $admin->first_name ?? '') }}" required>
            </div>
            <div class="form-group">
                <label for="middle_initial">Middle Initial</label>
                <input id="middle_initial" type="text" name="middle_initial" class="form-control" maxlength="1" placeholder="Optional" value="{{ old('middle_initial', $admin->middle_initial ?? '') }}">
            </div>
            <div class="form-group">
                <label for="last_name">Last Name</label>
                <input id="last_name" type="text" name="last_name" class="form-control" value="{{ old('last_name', $admin->last_name ?? '') }}" required>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input id="email" type="email" name="email" class="form-control" value="{{ old('email', $admin->email ?? '') }}" required>
            </div>
            <div class="form-group">
                <label for="role">Role</label>
                <select id="role" name="role" class="form-control" required>
                    <option value="education" {{ old('role', $admin->role ?? 'education') === 'education' ? 'selected' : '' }}>Education</option>
                    <option value="main" {{ old('role', $admin->role ?? 'education') === 'main' ? 'selected' : '' }}>Main Admin</option>
                </select>
            </div>
        </div>

        @if($isEditing)
            <div class="form-group">
                <label for="new_password">Set New Password <span class="form-help">(leave blank to keep current password)</span></label>
                <input id="new_password" type="password" name="new_password" class="form-control" autocomplete="new-password" minlength="6">
            </div>
            <div class="form-group">
                <label for="new_password_confirmation">Confirm New Password</label>
                <input id="new_password_confirmation" type="password" name="new_password_confirmation" class="form-control" autocomplete="new-password" minlength="6">
            </div>
        @else
            <div class="form-grid">
                <div class="form-group">
                    <label for="password" id="password-label">Temporary Password</label>
                    <input id="password" type="password" name="password" class="form-control" required minlength="6" autocomplete="new-password">
                    <span class="form-help" id="temporary-password-help">The new sub admin must choose a new password at first login.</span>
                </div>
                <div class="form-group">
                    <label for="password_confirmation">Confirm Password</label>
                    <input id="password_confirmation" type="password" name="password_confirmation" class="form-control" required minlength="6" autocomplete="new-password">
                </div>
            </div>
        @endif

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">{{ $isEditing ? 'Update Admin' : 'Save Admin' }}</button>
            <a href="{{ route('admins.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

@if(!$isEditing)
    <script>
        const roleSelect = document.getElementById('role');
        const passwordLabel = document.getElementById('password-label');
        const temporaryPasswordHelp = document.getElementById('temporary-password-help');

        function updatePasswordDescription() {
            const isSubAdmin = roleSelect.value === 'education';
            passwordLabel.textContent = isSubAdmin ? 'Temporary Password' : 'Password';
            temporaryPasswordHelp.hidden = !isSubAdmin;
        }

        roleSelect.addEventListener('change', updatePasswordDescription);
        updatePasswordDescription();
    </script>
@endif
@endsection
