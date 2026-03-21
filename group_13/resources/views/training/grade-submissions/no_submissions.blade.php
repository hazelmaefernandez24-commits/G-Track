@extends('layouts.nav')

@section('content')

<div class="container">
    <h1>Monitor Grade Submissions</h1>
    <hr>
    <div class="alert alert-info">
        {{ $message ?? 'No grade submissions available to monitor.' }}
    </div>
</div>

@endsection 