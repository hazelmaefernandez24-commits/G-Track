@extends('layouts.finance')

@section('title', 'Profile')
@section('page-title', 'Finance Profile')

@section('content')
<div class="container">
    <h3>Profile Information</h3>
    <p><strong>Name:</strong> {{ $finance->first_name }} {{ $finance->last_name }}</p>
    @php $sessionUser = session('user'); @endphp
    <p><strong>Email:</strong> {{ $sessionUser['user_email'] ?? 'N/A' }}</p>
    <p><strong>Department:</strong> {{ $finance->department }}</p>
</div>
@endsection
