@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-12">
            <div class="card" style="width:1200px;">
                <div class="card-header">
                    <h4>
                        <img src="https://cdn-icons-png.flaticon.com/512/3135/3135715.png" alt="Report Icon" style="width:28px;height:28px;margin-right:8px;vertical-align:middle;"> Edit Report
                    </h4>
                </div>
                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('reports.update', $report->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <div class="row g-4">
                            <div class="col-md-5">
                                <!-- Current Photo Display -->
                                @if($report->photo)
                                <div class="mb-3 text-center">
                                    <label class="form-label">Current Photo</label>
                                    <div class="mb-2">
                                        <img src="{{ asset('storage/' . $report->photo) }}" 
                                             alt="Current Photo" 
                                             class="img-thumbnail"
                                             style="max-height: 200px; object-fit: cover;">
                                    </div>
                                    <small class="text-muted">Upload a new photo to replace the current one</small>
                                </div>
                                @endif
                                <div class="mb-3">
                                    <label for="photo" class="form-label">{{ $report->photo ? 'Replace Photo' : 'Add Photo' }}</label>
                                    <input type="file" class="form-control @error('photo') is-invalid @enderror" 
                                           id="photo" name="photo" accept="image/*">
                                    @error('photo')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="mb-3">
                                    <label for="report_date" class="form-label">Report Date</label>
                                    <input type="date" class="form-control @error('report_date') is-invalid @enderror" 
                                           id="report_date" name="report_date" 
                                           value="{{ old('report_date', $report->report_date) }}" 
                                           max="{{ now()->format('Y-m-d') }}" required>
                                    @error('report_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Status -->
                                <div class="mb-3">
                                    <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                    <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                                        <option value="">Select Status</option>
                                        <option value="active" {{ old('status', $report->status) == 'active' ? 'selected' : '' }}>Active</option>
                                        <option value="resolved" {{ old('status', $report->status) == 'resolved' ? 'selected' : '' }}>Resolved</option>
                                    </select>
                                    @error('status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-7">
                                <div class="mb-3">
                                    <label for="title" class="form-label">Report Title</label>
                                    <input type="text" class="form-control @error('title') is-invalid @enderror" 
                                           id="title" name="title" value="{{ old('title', $report->title) }}" 
                                           placeholder="Enter report title" required>
                                    @error('title')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Staff In Charge -->
                                <div class="mb-3">
                                    <label for="staff_in_charge" class="form-label">Staff In Charge <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('staff_in_charge') is-invalid @enderror" id="staff_in_charge" name="staff_in_charge" value="{{ old('staff_in_charge', $report->staff_in_charge) }}" placeholder="Enter staff name or ID" required>
                                    @error('staff_in_charge')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Area -->
                                <div class="mb-3">
                                    <label for="area" class="form-label">Area <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('area') is-invalid @enderror" id="area" name="area" value="{{ old('area', $report->area) }}" placeholder="Enter location, department, or area" required>
                                    @error('area')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="comment" class="form-label">Comments</label>
                                    <textarea class="form-control @error('comment') is-invalid @enderror" 
                                              id="comment" name="comment" rows="5" 
                                              placeholder="Enter detailed comments..." required>{{ old('comment', $report->comment) }}</textarea>
                                    @error('comment')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between mt-4">
                            <div>
                                <a href="{{ route('damage_reports.show', $report->id) }}" class="btn btn-danger me-2">
                                    Cancel
                                </a>
                                <a href="{{ route('damage_reports.index') }}" class="btn btn-primary">
                                    Back to Reports
                                </a>
                            </div>
                            <button type="submit" class="btn btn-primary">
                                Update Report
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection