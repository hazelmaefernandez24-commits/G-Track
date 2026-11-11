@extends('layouts.app')

@php($isEditing = isset($class))

@section('title', $isEditing ? 'Edit Class' : 'Add Class')
@section('subtitle', $isEditing ? 'Update class details' : 'Create a student class')

@push('styles')
<style>
    .form-card {
        max-width: 680px;
        padding: 28px;
        background: #fff;
        border: 1px solid var(--border-color);
        border-radius: 16px;
        box-shadow: var(--card-shadow);
    }
    .form-group { margin-bottom: 18px; }
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

    <form action="{{ $isEditing ? route('classes.update', $class) : route('classes.store') }}" method="POST">
        @csrf
        @if($isEditing)
            @method('PUT')
        @endif
        <div class="form-group">
            <label for="name">Class Year <span style="color:#ef4444;">*</span></label>
            <input id="name" type="text" name="name" class="form-control" required maxlength="50" inputmode="numeric" pattern="[0-9]+" title="Enter numbers only." value="{{ old('name', $class->name ?? '') }}">
            <p class="form-help" style="margin:8px 0 0;">Enter the year using numbers only. A year already in the system cannot be added again.</p>
            @if($isEditing)
                <p class="form-help" style="margin:8px 0 0; color:#b45309;">Changing the name updates the class for currently enrolled students.</p>
            @endif
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">{{ $isEditing ? 'Update Class' : 'Save Class' }}</button>
            <a href="{{ route('classes.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
