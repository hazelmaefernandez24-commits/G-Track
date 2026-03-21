
@extends('layouts.student')
    
@section('title', 'Payment Options')
@section('page-title', 'Payment Options')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Available Payment Methods</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($paymentMethods as $method)
                        <div class="col-md-4 mb-4">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">{{ $method->name }}</h5>
                                    @if($method->qr_image)
                                        <img src="{{ Storage::url($method->qr_image) }}" alt="QR Code" class="img-fluid mb-3">
                                    @endif
                                    @if($method->account_name)
                                        <p class="card-text"><strong>Account Name:</strong> {{ $method->account_name }}</p>
                                    @endif
                                    @if($method->account_number)
                                        <p class="card-text"><strong>Account Number:</strong> {{ $method->account_number }}</p>
                                    @endif
                                    @if($method->description)
                                        <p class="card-text">{{ $method->description }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection