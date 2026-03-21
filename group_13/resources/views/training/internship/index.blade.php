@extends('layouts.nav')

@section('title', $title)

@section('styles')
<style>
    .card { background:#fff; border-radius:8px; box-shadow:0 2px 10px rgba(0,0,0,.06); padding:16px; margin:20px; }
    .actions { display:flex; gap:10px; margin-bottom:12px; }
    .btn { padding:5px 10px; border-radius:6px; border:none; cursor:pointer; color:#fff; background:#007bff; text-decoration:none; }
    a.btn { text-decoration:none; }
    a.btn:hover { text-decoration:none; }
    .btn-secondary { background:#6c757d; }
    .table-responsive { overflow-x:auto; }
    table { width:100%; border-collapse:collapse; }
    thead th { text-align:left; background:#f8f9fa; border-bottom:1px solid #e9ecef; padding:10px; font-size:.85rem; text-transform:uppercase; color:#6c757d; }
    tbody td { padding:10px; border-bottom:1px solid #f1f3f5; font-size:.95rem; }
    .modal { display:none; position:fixed; inset:0; background:rgba(0,0,0,.4); align-items:center; justify-content:center; z-index:1000; }
    .modal-content { background:#fff; border-radius:10px; width:95%; max-width:900px; padding:20px; }
    .grid { display:grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap:12px; }
    .field label { display:block; font-size:.85rem; color:#6c757d; margin-bottom:6px; }
    .field select, .field input { width:100%; padding:8px 10px; border:1px solid #ced4da; border-radius:6px; }
    .students { display:block !important; max-height:240px; overflow:auto; border:1px solid #e9ecef; border-radius:8px; padding:8px; text-align:left; }
    .students .student-row { display:flex; align-items:center; justify-content:flex-start !important; gap:8px; padding:8px 6px; border-bottom:1px dashed #f1f3f5; }
    .students .student-row label.student-check { display:inline-flex; align-items:center; gap:10px; }
    .students .student-row input[type="checkbox"] { margin:0; }
    .students .student-row span { display:inline-block; white-space:nowrap; }
    .modal-actions { display:flex; justify-content:flex-end; gap:10px; margin-top:16px; }

    /* Filters */
    .filter-bar { background:#f8f9fa; border:1px solid #e9ecef; border-radius:10px; padding:12px 16px; margin:10px 0 14px 0; }
    .filter-form { display:flex; gap:12px; align-items:flex-end; flex-wrap:wrap; }
    .ff-group { display:flex; flex-direction:column; }
    .ff-group label { font-size:.75rem; color:#6c757d; margin:0 0 4px 2px; }
    .filter-bar select, .filter-bar input[type="text"] { height:36px; padding:8px 10px; border:1px solid #ced4da; border-radius:8px; background:#fff; min-width:200px; color:#212529; }
    .filter-bar select option { color:#212529; }
    .btn-filter { background:#0d6efd; }
    .btn-reset { background:#6c757d; }

    /* Days alignment */
    .grid .field-days { grid-column: 1/ -1; }
    .days-wrap { border:1px solid #ced4da; border-radius:6px; padding:5px; background:#fff; }
    .days-grid { display:grid; grid-template-columns: repeat(5, minmax(120px, 1fr)); gap:12px 18px; align-items:center; }
    .days-grid label { display:flex; align-items:center; gap:2px; line-height:1.2; }
    @media (max-width: 992px) { .days-grid { grid-template-columns: repeat(3, minmax(120px, 1fr)); } }
    @media (max-width: 576px) { .days-grid { grid-template-columns: repeat(2, minmax(120px, 1fr)); } }
</style>
@endsection

@section('content')
<div class="container-fluid" style="padding:20px;">
    <div class="card">
        <h2 style="margin:0 0 10px 0;">Internship</h2>
        <div class="actions">
            <button id="open-set-intern" class="btn"><i class="fas fa-user-plus"></i> Set Intern</button>
        </div>

        <!-- Filters moved above table -->
        <div class="filter-bar">
            <form id="internship-filter-form" method="GET" action="{{ route('training.internship.index') }}" class="filter-form">
                <div class="ff-group">
                    <label>Batch</label>
                    <select name="batch" onchange="document.getElementById('internship-filter-form').submit()">
                        <option value="">All</option>
                        @isset($batches)
                            @foreach($batches as $b)
                                <option value="{{ $b }}" {{ ($activeBatch ?? '') == $b ? 'selected' : '' }}>{{ $b }}</option>
                            @endforeach
                        @endisset
                    </select>
                </div>
                <div class="ff-group">
                    <label>Company</label>
                    <select name="company" onchange="document.getElementById('internship-filter-form').submit()">
                        <option value="">All</option>
                        @isset($companies)
                            @foreach($companies as $c)
                                <option value="{{ $c }}" {{ ($activeCompany ?? '') == $c ? 'selected' : '' }}>{{ $c }}</option>
                            @endforeach
                        @endisset
                    </select>
                </div>
                <button type="submit" class="btn btn-filter">Filter</button>
                <a href="{{ route('training.internship.index') }}" class="btn btn-reset">Reset</a>
            </form>
        </div>

        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Student ID</th>
                        <th>Name</th>
                        <th>Company</th>
                        <th>Time of Duty</th>
                        <th>Actions</th>
                        <th>Start Date</th>
                        <th>Tentative End Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($internships as $item)
                        <tr>
                            <td>{{ $item->student_id }}</td>
                            <td>{{ optional($item->student)->user_fname }} {{ optional($item->student)->user_lname }}</td>
                            <td>{{ $item->company ?? '-' }}</td>
                            <td>
                                @php
                                    $ti = $item->time_in ? \Carbon\Carbon::parse($item->time_in)->format('g:i A') : null;
                                    $to = $item->time_out ? \Carbon\Carbon::parse($item->time_out)->format('g:i A') : null;
                                    $daysArr = [];
                                    if (!empty($item->time_of_duty)) {
                                        $todDecoded = json_decode($item->time_of_duty, true);
                                        if (is_array($todDecoded) && isset($todDecoded['days']) && is_array($todDecoded['days'])) {
                                            $daysArr = $todDecoded['days'];
                                        } else {
                                            // fallback if stored as CSV
                                            $daysArr = array_filter(array_map('trim', explode(',', (string) $item->time_of_duty)));
                                        }
                                    }
                                    $daysCsv = implode(',', $daysArr);
                                @endphp
                                <button type="button"
                                        class="btn btn-secondary duty-view-btn"
                                        style="padding:4px 8px;"
                                        data-time-in="{{ $ti ?? '' }}"
                                        data-time-out="{{ $to ?? '' }}"
                                        data-days="{{ e(!empty($item->days) ? implode(',', (array) $item->days) : $daysCsv) }}">
                                    View
                                </button>
                            </td>
                            <td>
                                <a href="{{ route('training.internship.edit', $item->id) }}" class="btn btn-secondary" style="padding:4px 8px;">Edit</a>
                                <form action="{{ route('training.internship.destroy', $item->id) }}" method="POST" style="display:inline" onsubmit="return confirm('Delete this internship?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn" style="background:#dc3545; padding:4px 8px;">Delete</button>
                                </form>
                            </td>
                            <td>{{ optional($item->start_date)->format('M d, Y') }}</td>
                            <td>{{ optional($item->end_date)->format('M d, Y') ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align:center; color:#6c757d;">No interns set yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    

    <!-- Set Intern Modal -->
    <div id="set-intern-modal" class="modal" aria-hidden="true">
        <div class="modal-content">
            <h3 style="margin-top:0;">Set Intern</h3>
            <form method="POST" action="{{ route('training.internship.store') }}" id="set-intern-form">
                @csrf
                <div class="grid">
                    <div class="field">
                        <label>School</label>
                        <select id="school-select" name="school_id" required>
                            <option value="" disabled selected>Select school</option>
                            @foreach($schools as $s)
                                <option value="{{ $s->school_id }}">{{ $s->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="field">
                        <label>Class</label>
                        <select id="class-select" name="class_id" required>
                            <option value="" disabled selected>Select class</option>
                        </select>
                    </div>
                    <div class="field field-days">
                        <label>Days</label>
                        <div class="days-wrap">
                            <div class="days-grid">
                                @php $weekdays = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday']; @endphp
                                @foreach($weekdays as $w)
                                    <label>
                                        <input type="checkbox" name="days[]" value="{{ $w }}"> <span>{{ $w }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <div class="field">
                        <label>Time In</label>
                        <input type="time" name="time_in" required />
                    </div>
                    <div class="field">
                        <label>Time Out</label>
                        <input type="time" name="time_out" required />
                    </div>
                    <div class="field">
                        <label>Company</label>
                        <input type="text" name="company" placeholder="Optional company" />
                    </div>
                    <div class="field">
                        <label>Start date</label>
                        <input type="date" name="start_date" required />
                    </div>
                    <div class="field">
                        <label>Tentative end date</label>
                        <input type="date" name="end_date" />
                    </div>
                </div>

                <div class="field" style="margin-top:12px;">
                    <label>Students</label>
                    <div id="students-box" class="students">
                        <div style="color:#6c757d;">Select a class to load students…</div>
                    </div>
                </div>

                <div class="modal-actions">
                    <button type="button" id="close-set-intern" class="btn btn-secondary">Close</button>
                    <button type="submit" class="btn">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>
    <!-- Duty Details Modal -->
    <div id="duty-details-modal" class="modal" aria-hidden="true">
        <div class="modal-content" style="max-width:420px;">
            <h3 style="margin-top:0;">Time of Duty</h3>
            <div id="duty-details-body" style="line-height:1.8; color:#212529;"></div>
            <div class="modal-actions">
                <button type="button" id="close-duty-details" class="btn btn-secondary">Close</button>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('set-intern-modal');
    document.getElementById('open-set-intern').addEventListener('click', () => modal.style.display = 'flex');
    document.getElementById('close-set-intern').addEventListener('click', () => modal.style.display = 'none');
    modal.addEventListener('click', (e) => { if (e.target === modal) modal.style.display = 'none'; });

    const schoolSelect = document.getElementById('school-select');
    const classSelect = document.getElementById('class-select');
    const studentsBox = document.getElementById('students-box');
    const dutyModal = document.getElementById('duty-details-modal');
    const dutyModalBody = document.getElementById('duty-details-body');
    const closeDutyBtn = document.getElementById('close-duty-details');

    schoolSelect.addEventListener('change', function() {
        classSelect.innerHTML = '<option value="" disabled selected>Loading…</option>';
        fetch(`{{ route('training.internship.classes','__SCHOOL__') }}`.replace('__SCHOOL__', this.value))
            .then(r => r.json())
            .then(items => {
                classSelect.innerHTML = '<option value="" disabled selected>Select class</option>';
                items.forEach(c => {
                    const opt = document.createElement('option');
                    opt.value = c.id;
                    opt.textContent = c.class_name || c.class_id;
                    classSelect.appendChild(opt);
                });
                studentsBox.innerHTML = '<div style="color:#6c757d;">Select a class to load students…</div>';
            });
    });

    classSelect.addEventListener('change', function() {
        studentsBox.innerHTML = '<div style="color:#6c757d;">Loading students…</div>';
        fetch(`{{ route('training.internship.students','__CLASS__') }}`.replace('__CLASS__', this.value))
            .then(r => r.json())
            .then(students => {
                if (!students.length) {
                    studentsBox.innerHTML = '<div style="color:#6c757d;">No students in this class.</div>';
                    return;
                }
                const frag = document.createDocumentFragment();
                students.forEach(s => {
                    const row = document.createElement('div');
                    row.className = 'student-row';
                    row.innerHTML = `<label class="student-check">` +
                        `<input type="checkbox" name="student_ids[]" value="${s.user_id}" />` +
                        `<span>${s.user_id} - ${s.user_fname ?? ''} ${s.user_lname ?? ''}</span>` +
                    `</label>`;
                    frag.appendChild(row);
                });
                studentsBox.innerHTML = '';
                studentsBox.appendChild(frag);
            });
    });

    // Time of Duty View button handler
    document.querySelectorAll('.duty-view-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const ti = this.getAttribute('data-time-in') || '-';
            const to = this.getAttribute('data-time-out') || '-';
            const daysCsv = this.getAttribute('data-days') || '';
            const daysList = daysCsv ? daysCsv.split(',').map(function(d){ return d.trim(); }).filter(Boolean) : [];
            const daysHtml = daysList.length ? daysList.map(function(d){ return `<span style="display:inline-block; background:#f1f3f5; padding:2px 8px; border-radius:12px; margin:2px;">${d}</span>`; }).join(' ') : '<em>No days set</em>';

            dutyModalBody.innerHTML = `
                <div><strong>Time In:</strong> ${ti}</div>
                <div><strong>Time Out:</strong> ${to}</div>
                <div style="margin-top:8px;"><strong>Days:</strong><br>${daysHtml}</div>
            `;
            dutyModal.style.display = 'flex';
        });
    });

    closeDutyBtn.addEventListener('click', function(){ dutyModal.style.display = 'none'; });
    dutyModal.addEventListener('click', function(e){ if (e.target === dutyModal) dutyModal.style.display = 'none'; });
});
</script>
@endsection


