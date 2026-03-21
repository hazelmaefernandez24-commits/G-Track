@extends('layouts.apps')

@section('content')
<div class="container">
  <div class="row justify-content-center">
    <div class="col-md-6">
      <div class="card">
        <div class="card-body">
          <h2 class="dashboard-title mb-4">Add New Student</h2>

          <form method="POST" action="{{ url('/students') }}">
            @csrf

            <div class="mb-3">
              <label for="name" class="form-label fw-bold">Name:</label>
              <input type="text" class="form-control" id="name" name="name" required>
            </div>

            <div class="mb-3">
              <label for="gender" class="form-label fw-bold">Gender:</label>
              <select class="form-select" id="gender" name="gender" required>
                <option value="">Select Gender</option>
                <option value="Male">Male</option>
                <option value="Female">Female</option>
              </select>
            </div>

            <div class="mb-4">
              <label for="batch" class="form-label fw-bold">Batch:</label>
              <input type="number" class="form-control" id="batch" name="batch" placeholder="e.g., 2025" required>
            </div>

            <div class="d-grid">
              <button type="submit" class="btn btn-primary">Add Student</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection