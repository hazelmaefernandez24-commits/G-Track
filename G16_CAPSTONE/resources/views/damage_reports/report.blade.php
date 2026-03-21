@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <!-- Header Section -->
    <div class="d-flex justify-content-between align-items-center mb-4" style="max-width: 1400px; margin: 0 auto;">
    </div>

    <!-- Main Form as Horizontal Card -->
    <div class="card" style="width:1200px; margin: 0 auto;">
        <div class="bg-white rounded-3 shadow-sm border-0">
            <div class="p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h1 class="h3 mb-1 text-gray-800">
                            <img src="https://cdn-icons-png.flaticon.com/512/2991/2991108.png" alt="Report Icon" style="width:28px;height:28px;margin-right:8px;vertical-align:middle;"> Create Report
                        </h1>
                        <p class="text-muted mb-0">Fill in the details and upload supporting materials</p>
                    </div>
                    <div>
                        <a href="{{ route('damage_reports.index') }}" class="btn btn-primary btn-sm">
                            <img src="https://cdn-icons-png.flaticon.com/512/545/545680.png" alt="Back" style="width:16px;height:16px;margin-right:4px;vertical-align:middle;">Back to Reports
                        </a>
                    </div>
                </div>

                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <div class="d-flex align-items-center">
                            <img src="https://cdn-icons-png.flaticon.com/512/564/564619.png" alt="Error" style="width:18px;height:18px;margin-right:8px;"> 
                            <div>
                                <h6 class="alert-heading mb-1">Please correct the following errors:</h6>
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <form action="{{ route('damage_reports.store') }}" method="POST" enctype="multipart/form-data" id="reportForm">
                    @csrf
                    <div class="row g-4">
                            <div class="mb-4">
                                <label class="form-label d-flex align-items-center">
                                    <img src="https://cdn-icons-png.flaticon.com/512/685/685655.png" alt="Photo" style="width:22px;height:22px;margin-right:8px;"> Photo
                                </label>
                                <div class="text-center mb-3">
                                    <div class="bg-light rounded d-flex align-items-center justify-content-center" style="height: 220px;">
                                        <img src="https://cdn-icons-png.flaticon.com/512/685/685655.png" alt="No Photo" style="width:48px;height:48px;">
                                    </div>
                                </div>
                                <label class="form-label mt-2">Add Photo</label>
                                <input type="file" name="photo" class="form-control" accept="image/*">
                                <div class="form-text">Upload a photo for this report</div>
                                @error('photo')
                                    <div class="invalid-feedback d-block">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-7">
                            <div class="mb-3">
                                <label for="title" class="form-label">Report Title</label>
                                <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title') }}" maxlength="255" required>
                                @error('title')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label for="staff_in_charge" class="form-label">Staff In Charge <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('staff_in_charge') is-invalid @enderror" id="staff_in_charge" name="staff_in_charge" value="{{ old('staff_in_charge') }}" required>
                                @error('staff_in_charge')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label for="area" class="form-label">Area <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('area') is-invalid @enderror" id="area" name="area" value="{{ old('area') }}" required>
                                @error('area')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label for="report_date" class="form-label">Report Date</label>
                                <input type="date" class="form-control @error('report_date') is-invalid @enderror" id="report_date" name="report_date" value="{{ old('report_date') }}" max="{{ now()->format('Y-m-d') }}" required>
                                @error('report_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                                    <option value="">Select Status</option>
                                    <option value="pending" {{ old('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="resolved" {{ old('status') == 'resolved' ? 'selected' : '' }}>Resolved</option>
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label for="date_resolved" class="form-label">Date Resolved</label>
                                <input type="date" class="form-control @error('date_resolved') is-invalid @enderror" id="date_resolved" name="date_resolved" value="{{ old('date_resolved') }}" max="{{ now()->format('Y-m-d') }}">
                                <div class="form-text">Leave blank if status is Active</div>
                                @error('date_resolved')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label for="comment" class="form-label">Comments</label>
                                <textarea class="form-control @error('comment') is-invalid @enderror" id="comment" name="comment" rows="5" required>{{ old('comment') }}</textarea>
                                @error('comment')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between mt-4">
                        <a href="{{ route('damage_reports.index') }}" class="btn btn-danger">
                            Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <img src="https://cdn-icons-png.flaticon.com/512/709/709612.png" alt="Save" style="width:18px;height:18px;margin-right:6px;vertical-align:middle;"> Create Report
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection