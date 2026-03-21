@extends('layouts.apps')

@section('content')
<div class="container py-4">
    <h2 class="fw-bold mb-1" style="font-size:2rem;">All Categories</h2>
    <div class="text-muted mb-4" style="font-size:1.05rem;">Manage all categories and their tasks</div>
    <div class="card">
        <ul class="list-group list-group-flush">
            @foreach($categories as $category)
            <li class="list-group-item d-flex justify-content-between align-items-center" style="font-size:1.1rem;">
                {{ $category->name }}
                <form action="{{ route('categories.destroy', $category->id) }}" method="POST" style="margin:0;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                </form>
            </li>
            @endforeach
            <li class="list-group-item p-2">
                <form action="{{ route('categories.store') }}" method="POST" class="d-flex align-items-center gap-2 mb-0">
                    @csrf
                    <input type="text" name="name" class="form-control" placeholder="Add new category..." required style="font-size:1rem;">
                    <button type="submit" class="btn btn-primary btn-sm">Add</button>
                </form>
            </li>
        </ul>
    </div>
</div>
@endsection