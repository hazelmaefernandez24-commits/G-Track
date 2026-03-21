@extends('layouts.admin_layout')

@section('content')
<link rel="stylesheet" href="{{ asset('css/admin/edit.css') }}">

<div class="edit-user-container">
    <h2 class="page-title">
        Edit User:
        <span class="highlight">{{ $user->user_fname }} {{ $user->user_lname }}</span>
    </h2>

    <form action="{{ route('admin.pnph_users.update', $user->user_id) }}" method="POST" class="edit-form">
        @csrf
        @method('PUT')

        <div class="form-row">
            <label>User ID</label>
            <input type="text" value="{{ $user->user_id }}" disabled>
            <small class="note">This field cannot be changed.</small>
        </div>

        <div class="form-grid">
            <div class="form-group">
                <label>First Name</label>
                <input type="text" name="user_fname" value="{{ $user->user_fname }}" required>
            </div>

            <div class="form-group">
                <label>Last Name</label>
                <input type="text" name="user_lname" value="{{ $user->user_lname }}" required>
            </div>

            <div class="form-group">
                <label>Middle Initial</label>
                <input type="text" name="user_mInitial" value="{{ $user->user_mInitial }}">
            </div>

            <div class="form-group">
                <label>Suffix</label>
                <input type="text" name="user_suffix" value="{{ $user->user_suffix }}">
            </div>

            <div class="form-group">
                <label>Gender</label>
                <select name="gender">
                    <option value="" {{ $user->gender === null ? 'selected' : '' }}>Prefer not to say</option>
                    <option value="M" {{ $user->gender === 'M' ? 'selected' : '' }}>Male</option>
                    <option value="F" {{ $user->gender === 'F' ? 'selected' : '' }}>Female</option>
                </select>
            </div>

            <div class="form-group">
                <label>Email</label>
                <input type="email" name="user_email" value="{{ $user->user_email }}" required>
            </div>

            <div class="form-group">
                <label>Role</label>
                <input type="text" name="user_role" value="{{ $user->user_role }}" required>
            </div>

            <div class="form-group">
                <label>Status</label>
                <select name="status" required>
                    <option value="active" {{ $user->status === 'active' ? 'selected' : '' }}>Activate</option>
                    <option value="inactive" {{ $user->status === 'inactive' ? 'selected' : '' }}>Deactivate</option>
                </select>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Update User</button>
            <a href="{{ route('admin.pnph_users.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
