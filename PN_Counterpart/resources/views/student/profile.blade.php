@extends('layouts.student')

@section('title', 'Profile')
@section('page-title', 'Student Profile')

@section('content')
<div class="container">
    <h3>Profile Information</h3>
    <p><strong>Name:</strong> {{ $student['user_fname'] }} {{ $student['user_lname'] }}</p>
    <p><strong>Email:</strong> {{ $student['user_email'] }}</p>
    <p><strong>User ID:</strong> {{ $student['user_id'] }}</p>
    <p><strong>Role:</strong> {{ $student['user_role'] }}</p>
</div>
@endsection
