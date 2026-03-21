@extends('layouts.nav')

@section('title', $title)

@section('content')
<div class="container-fluid" style="padding: 20px;">
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px;">
        <div style="display: flex; align-items: center; gap: 15px;">
            <img src="{{ asset('images/calendar.png') }}" alt="Calendar Icon" style="width: 32px; height: 32px;">
            <h1 style="color: #333; font-weight: 600; margin: 0; font-size: 24px;">Add New Event</h1>
        </div>
        <a href="{{ route('training.calendar.manage') }}" class="btn btn-secondary" style="background-color: #6c757d; border: none; padding: 10px 20px; border-radius: 5px; text-decoration: none; color: white; font-weight: 500;">
            <i class="fas fa-arrow-left"></i> Back to List
        </a>
    </div>
    <hr style="margin-bottom: 20px;">

    <div style="background: white; border-radius: 10px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); padding: 30px; max-width: auto;">
        <form method="POST" action="{{ route('training.calendar.store') }}">
            @csrf
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                <div>
                    <label for="title" style="display: block; font-weight: 600; color: #2c3e50; margin-bottom: 8px;">
                        Event/Activty <span style="color: #e74c3c;">*</span>
                    </label>
                    <input type="text" 
                           id="title" 
                           name="title" 
                           value="{{ old('title') }}"
                           required
                           style="width: 100%; padding: 12px; border: 2px solid #e9ecef; border-radius: 5px; font-size: 16px; transition: border-color 0.3s ease;"
                           placeholder="Enter event title">
                    @error('title')
                        <div style="color: #e74c3c; font-size: 0.9em; margin-top: 5px;">{{ $message }}</div>
                    @enderror
                </div>

                <div>
                    <label for="category" style="display: block; font-weight: 600; color: #2c3e50; margin-bottom: 8px;">
                        Category <span style="color: #e74c3c;">*</span>
                    </label>
                    <select id="category" 
                            name="category" 
                            required
                            style="width: 100%; padding: 12px; border: 2px solid #e9ecef; border-radius: 5px; font-size: 16px; transition: border-color 0.3s ease;">
                        <option value="">Select Category</option>
                        <option value="school_activity" {{ old('category') == 'school_activity' ? 'selected' : '' }}>School Activity</option>
                        <option value="holiday" {{ old('category') == 'holiday' ? 'selected' : '' }}>Holiday</option>
                        <option value="examination" {{ old('category') == 'examination' ? 'selected' : '' }}>Examination</option>
                        <option value="deadline" {{ old('category') == 'deadline' ? 'selected' : '' }}>Deadline</option>
                        <option value="vacation" {{ old('category') == 'vacation' ? 'selected' : '' }}>Vacation</option>
                        <option value="special" {{ old('category') == 'special' ? 'selected' : '' }}>Special Event</option>
                    </select>
                    @error('category')
                        <div style="color: #e74c3c; font-size: 0.9em; margin-top: 5px;">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div style="margin-bottom: 20px;">
                <label for="description" style="display: block; font-weight: 600; color: #2c3e50; margin-bottom: 8px;">
                    Description
                </label>
                <textarea id="description" 
                          name="description" 
                          rows="3"
                          style="width: 100%; padding: 12px; border: 2px solid #e9ecef; border-radius: 5px; font-size: 16px; transition: border-color 0.3s ease; resize: vertical;"
                          placeholder="Enter event description (optional)">{{ old('description') }}</textarea>
                @error('description')
                    <div style="color: #e74c3c; font-size: 0.9em; margin-top: 5px;">{{ $message }}</div>
                @enderror
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                <div>
                    <label for="start_date" style="display: block; font-weight: 600; color: #2c3e50; margin-bottom: 8px;">
                        Start Date <span style="color: #e74c3c;">*</span>
                    </label>
                    <input type="date" 
                           id="start_date" 
                           name="start_date" 
                           value="{{ old('start_date') }}"
                           required
                           style="width: 100%; padding: 12px; border: 2px solid #e9ecef; border-radius: 5px; font-size: 16px; transition: border-color 0.3s ease;">
                    @error('start_date')
                        <div style="color: #e74c3c; font-size: 0.9em; margin-top: 5px;">{{ $message }}</div>
                    @enderror
                </div>

                <div>
                    <label for="end_date" style="display: block; font-weight: 600; color: #2c3e50; margin-bottom: 8px;">
                        End Date <span style="color: #e74c3c;">*</span>
                    </label>
                    <input type="date" 
                           id="end_date" 
                           name="end_date" 
                           value="{{ old('end_date') }}"
                           required
                           style="width: 100%; padding: 12px; border: 2px solid #e9ecef; border-radius: 5px; font-size: 16px; transition: border-color 0.3s ease;">
                    @error('end_date')
                        <div style="color: #e74c3c; font-size: 0.9em; margin-top: 5px;">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 30px;">
                <div>
                    <label for="semester" style="display: block; font-weight: 600; color: #2c3e50; margin-bottom: 8px;">
                        Semester
                    </label>
                    <select id="semester" 
                            name="semester"
                            style="width: 100%; padding: 12px; border: 2px solid #e9ecef; border-radius: 5px; font-size: 16px; transition: border-color 0.3s ease;">
                        <option value="">Select Semester (Optional)</option>
                        <option value="first" {{ old('semester') == 'first' ? 'selected' : '' }}>First Semester</option>
                        <option value="second" {{ old('semester') == 'second' ? 'selected' : '' }}>Second Semester</option>
                        <option value="summer" {{ old('semester') == 'summer' ? 'selected' : '' }}>Summer</option>
                    </select>
                    @error('semester')
                        <div style="color: #e74c3c; font-size: 0.9em; margin-top: 5px;">{{ $message }}</div>
                    @enderror
                </div>

                <div>
                    <label for="academic_year" style="display: block; font-weight: 600; color: #2c3e50; margin-bottom: 8px;">
                        Academic Year <span style="color: #e74c3c;">*</span>
                    </label>
                    <input type="text" 
                           id="academic_year" 
                           name="academic_year" 
                           value="{{ old('academic_year', '2025-2026') }}"
                           required
                           style="width: 100%; padding: 12px; border: 2px solid #e9ecef; border-radius: 5px; font-size: 16px; transition: border-color 0.3s ease;"
                           placeholder="e.g., 2025-2026">
                    @error('academic_year')
                        <div style="color: #e74c3c; font-size: 0.9em; margin-top: 5px;">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div style="display: flex; gap: 15px; justify-content: flex-end;">
                <a href="{{ route('training.calendar.manage') }}" 
                   style="background-color: #6c757d; color: white; padding: 12px 24px; border-radius: 5px; text-decoration: none; font-weight: 500; transition: all 0.3s ease;">
                    Cancel
                </a>
                <button type="submit" 
                        style="background-color: #28a745; color: white; padding: 12px 24px; border: none; border-radius: 5px; font-weight: 500; cursor: pointer; transition: all 0.3s ease;">
                    <i class="fas fa-save"></i> Create Event
                </button>
            </div>
        </form>
    </div>
</div>

<style>
input:focus, select:focus, textarea:focus {
    outline: none;
    border-color: #3498db !important;
    box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
}

.btn:hover, button:hover {
    opacity: 0.9;
    transform: translateY(-1px);
}

@media (max-width: 768px) {
    .container-fluid {
        padding: 10px !important;
    }
    
    div[style*="grid-template-columns: 1fr 1fr"] {
        grid-template-columns: 1fr !important;
        gap: 15px !important;
    }
    
    div[style*="max-width: 800px"] {
        padding: 20px !important;
    }
}
</style>

<script>
// Auto-set end date when start date changes
document.getElementById('start_date').addEventListener('change', function() {
    const endDateInput = document.getElementById('end_date');
    if (!endDateInput.value) {
        endDateInput.value = this.value;
    }
});
</script>
@endsection
