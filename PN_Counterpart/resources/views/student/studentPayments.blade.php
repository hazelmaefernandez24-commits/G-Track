@extends('layouts.student')

@section('title', 'Payment History')
@section('page-title', 'Payment History')

@push('styles')
  <style>
    /* Matrix table styles (aligned with finance matrix) */
    .matrix-card {
      background: #ffffff;
      border-radius: 0.75rem;
      box-shadow: 0 4px 12px rgba(0,0,0,0.05);
      border: none;
    }
    .matrix-header {
      background: #ff9933;
      color: #fff;
      border-top-left-radius: 0.75rem;
      border-top-right-radius: 0.75rem;
      padding: 0.75rem 1rem;
    }
    #studentPaymentMatrixTable {
      border-collapse: separate;
      border-spacing: 0;
      width: 100%;
    }
    #studentPaymentMatrixTable th {
      position: sticky;
      top: 0;
      background: #ff9933 !important;
      color: #fff !important;
      text-align: center;
      vertical-align: middle;
      height: 60px; /* slightly taller like finance look */
      z-index: 10;
    }
    #studentPaymentMatrixTable td {
      border: 1px solid #dee2e6;
      text-align: center;
      vertical-align: middle;
      padding: 8px 4px;
      min-width: 90px; /* match finance */
      height: 70px;    /* a little taller per request */
      background: #fff;
    }
    /* Match finance matrix cell behavior */
    #studentPaymentMatrixTable .payment-matrix-cell {
      transition: all 0.2s ease;
      cursor: pointer;
    }
    #studentPaymentMatrixTable .payment-matrix-cell:hover {
      background-color: #ffffff !important;
      transform: scale(1.1);
      z-index: 10;
      position: relative;
      box-shadow: 0 2px 8px rgba(0,0,0,0.15);
    }
    .matrix-student-cell { position: sticky; left: 0; background: #fff; z-index: 5; border-right: 2px solid #dee2e6; min-width: 220px; }
    .matrix-center { display: flex; align-items: center; justify-content: center; width: 100%; height: 100%; min-height: 32px; }
  </style>
@endpush

@section('content')
@php
    $approvedFinance = $payments->filter(fn($p) => in_array($p->status, ['Approved','Added by Finance']));
    $declined        = $payments->filter(fn($p) => $p->status === 'Declined');
    $pending         = $payments->filter(fn($p) => $p->status === 'Pending');
    $approvedTotal   = $approvedFinance->sum('amount');
    $declinedTotal   = $declined->sum('amount');
    $pendingTotal    = $pending->sum('amount');
@endphp

{{-- <div class="container-fluid">
  {{-- Global Search --}}
  {{-- <input
    type="text"
    id="global-search"
    class="form-control mb-3"
    placeholder="ðŸ” Search paymentsâ€¦"
  /> --}}

  {{-- Summary Card --}}
  {{-- <div class="row mb-4 justify-content-center"> --}}
    <!-- â€¦ existing Approved total card â€¦ -->
  {{-- </div> --}} 

  <!-- Student Payment Matrix moved into its own tab below -->

  {{-- Status Tabs --}}
  <ul class="nav nav-pills justify-content-center mb-3" id="payment-tabs" role="tablist">
    <li class="nav-item" role="presentation">
      <button class="nav-link active"
              id="matrix-tab"
              data-bs-toggle="pill"
              data-bs-target="#matrix"
              type="button" role="tab">
        Matrix
      </button>
    </li>
    <li class="nav-item" role="presentation">
      <button class="nav-link"
              id="approved-tab"
              data-bs-toggle="pill"
              data-bs-target="#approved"
              type="button" role="tab">
        Approved <span class="badge bg-white text-success">{{ $approvedFinance->count() }}</span>
      </button>
    </li>
    <li class="nav-item" role="presentation">
      <button class="nav-link"
              id="declined-tab"
              data-bs-toggle="pill"
              data-bs-target="#declined"
              type="button" role="tab">
        Declined <span class="badge bg-white text-danger">{{ $declined->count() }}</span>
      </button>
    </li>
    <li class="nav-item" role="presentation">
      <button class="nav-link"
              id="pending-tab"
              data-bs-toggle="pill"
              data-bs-target="#pending"
              type="button" role="tab">
        Pending <span class="badge bg-white text-warning">{{ $pending->count() }}</span>
      </button>
    </li>
  </ul>

  <div class="tab-content" id="payment-tabs-content">
    <div class="tab-pane fade show active" id="matrix" role="tabpanel">
      <!-- Student Payment Matrix (mirrors finance matrix, single student) -->
      <div class="card matrix-card mb-4">
        <div class="matrix-header d-flex justify-content-between align-items-center" style="background-color: #32abe3 !important; color: #fff !important;">
          <h6 class="mb-0"><i class="fas fa-table me-2"></i>Payment Matrix</h6>
        </div>
        <div class="card-body p-0">
          <div class="table-responsive" style="max-height: 60vh;">
            <table class="table table-bordered table-hover mb-0" id="studentPaymentMatrixTable">
              <thead>
                <tr>

                  <th class="text-center align-middle" style="min-width: 220px; left:0; position: sticky;">Student</th>
                  <th class="text-center align-middle" style="min-width: 80px; left:220px; position: sticky;">Batch</th>
                  <th id="month1">-</th>
                  <th id="month2">-</th>
                  <th id="month3">-</th>
                  <th id="month4">-</th>
                  <th id="month5">-</th>
                  <th id="month6">-</th>
                  <th id="month7">-</th>
                  <th id="month8">-</th>
                  <th id="month9">-</th>
                  <th id="month10">-</th>
                  <th id="month11">-</th>
                  <th id="month12">-</th>
                  <th class="text-center align-middle" style="min-width: 110px; position: sticky; right: 110px;">Total Paid</th>
                  <th class="text-center align-middle" style="min-width: 110px; position: sticky; right: 0;">Payable</th>
                </tr>
              </thead>
              <tbody id="studentMatrixBody">
              </tbody>
            </table>
          </div>
          <!-- Year Pagination Footer (mimic finance) -->
          <div class="d-flex justify-content-center align-items-center mt-3 p-3" style="background: #f8f9fa; border-radius: 8px;">
            <div class="d-flex align-items-center">
              <button type="button" class="btn btn-outline-primary btn-sm me-2" id="prevYearBtn">
                <i class="fas fa-chevron-left"></i>
              </button>
              <span class="mx-3 fw-bold" style="color: #2c3e50;">
                <i class="fas fa-calendar-alt me-2" style="color: #32abe3;"></i>
                <span id="currentYearDisplay">--</span>
              </span>
              <button type="button" class="btn btn-outline-primary btn-sm ms-2" id="nextYearBtn">
                <i class="fas fa-chevron-right"></i>
              </button>
            </div>
          </div>
          
        </div>
      </div>
    </div>

    <div class="tab-pane fade" id="approved" role="tabpanel">
      @include('student._table', [
        'payments'     => $approvedFinance,
        'emptyMessage' => 'Nothing approved yet.',
        'badgeClass'   => fn($s) => $s==='Approved' ? 'success' : 'info'
      ])
    </div>

    <div class="tab-pane fade" id="declined" role="tabpanel">
      @include('student._table', [
        'payments'     => $declined,
        'emptyMessage' => 'No declined transactions.',
        'badgeClass'   => fn($s) => 'danger'
      ])
    </div>

    <div class="tab-pane fade" id="pending" role="tabpanel">
      @include('student._table', [
        'payments'     => $pending,
        'emptyMessage' => 'No pending payments.',
        'badgeClass'   => fn($s) => 'warning'
      ])
    </div>
  </div>

  {{-- Doughnut Summary --}}
  <div class="chart-container mt-4">
    <canvas id="paymentChart"></canvas>
  </div>
</div>
@endsection

@section('scripts')
  <!-- jQuery, DataTables & Chart.js -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
  

  <script>
    // Matrix data prepared by controller (single student)
    const studentMatrixData = @json($matrixData ?? null);
    const matrixMonthlyFee = @json($matrixMonthlyFee ?? 500);

    // Year pagination state
    let availableYears = [];
    let currentYearIndex = 0;

    function updateLegend() {
      const now = new Date();
      const currentMonth = now.toLocaleString('default', { month: 'long' });
      const currentYear = now.getFullYear();
      const el = document.getElementById('matrixCurrentPeriod');
      if (el) el.innerHTML = `Current Period: <strong>${currentMonth} ${currentYear}</strong>`;
    }

    function generateYears() {
      // Repurpose availableYears as page indices (12-month slices)
      availableYears = [];
      const range = (studentMatrixData && Array.isArray(studentMatrixData.month_range)) ? studentMatrixData.month_range : [];
      const totalMonths = range.length;
      const pageSize = 12;
      const pageCount = Math.max(1, Math.ceil(totalMonths / pageSize));
      for (let p = 0; p < pageCount; p++) availableYears.push(p);

      // Choose the page that contains current month if present; otherwise the last page
      const now = new Date();
      const currMonth = now.getMonth() + 1;
      const currYear = now.getFullYear();
      let todayIdx = -1;
      for (let i = 0; i < totalMonths; i++) {
        const m = range[i];
        if (m && m.month === currMonth && m.year === currYear) { todayIdx = i; break; }
      }
      currentYearIndex = todayIdx >= 0 ? Math.floor(todayIdx / pageSize) : Math.max(0, pageCount - 1);
    }

    function updateMonthHeadersForYear(pageIndex) {
      const range = (studentMatrixData && Array.isArray(studentMatrixData.month_range)) ? studentMatrixData.month_range : [];
      const pageSize = 12;
      const startIdx = pageIndex * pageSize;
      for (let i = 0; i < pageSize; i++) {
        const th = document.getElementById(`month${i+1}`);
        if (!th) continue;
        const entry = range[startIdx + i];
        th.textContent = entry ? entry.display : '-';
      }
      const disp = document.getElementById('currentYearDisplay');
      if (disp && range.length > 0) {
        const startEntry = range[startIdx];
        const endEntry = range[Math.min(range.length - 1, startIdx + pageSize - 1)];
        if (startEntry && endEntry) {
          const y1 = startEntry.year;
          const y2 = endEntry.year;
          disp.textContent = (y1 === y2) ? `${y1}` : `${y1} – ${y2}`;
        } else {
          disp.textContent = '-';
        }
      }
    }

    function renderRowForYear(pageIndex) {
      const tbody = document.getElementById('studentMatrixBody');
      if (!tbody) return;
      tbody.innerHTML = '';
      if (!studentMatrixData) {
        const r = document.createElement('tr');
        const c = document.createElement('td');
        c.colSpan = 16;
        c.className = 'text-center text-muted py-4';
        c.innerHTML = '<i class="fas fa-info-circle me-2"></i>No data available.';
        r.appendChild(c);
        tbody.appendChild(r);
        return;
      }

      const data = studentMatrixData;
      const row = document.createElement('tr');

      // Student cell
      const studentCell = document.createElement('td');
      studentCell.className = 'matrix-student-cell text-center align-middle';
      studentCell.innerHTML = `<div class="d-flex flex-column align-items-center justify-content-center h-100">
        <div class="fw-semibold">${data.student.display_name}</div>
        <small class="text-muted">ID: ${data.student.student_id}</small>
      </div>`;
      row.appendChild(studentCell);

      // Batch cell
      const batchCell = document.createElement('td');
      batchCell.className = 'text-center align-middle';
      batchCell.innerHTML = `<span>${data.student.batch ?? ''}</span>`;
      row.appendChild(batchCell);

      // 12 month cells (page slice)
      const range = (studentMatrixData && Array.isArray(studentMatrixData.month_range)) ? studentMatrixData.month_range : [];
      const pageSize = 12;
      const startIdx = pageIndex * pageSize;
      const now = new Date();
      const currMonth = now.getMonth() + 1;
      const currYear  = now.getFullYear();
      for (let i = 0; i < pageSize; i++) {
        const td = document.createElement('td');
        td.className = 'payment-matrix-cell';
        const entry = range[startIdx + i];
        if (!entry) {
          td.innerHTML = `<span style="color: transparent;">-</span>`;
          row.appendChild(td);
          continue;
        }
        const actualMonth = entry.month;
        const actualYear  = entry.year;
        const monthKey = `${actualMonth}_${actualYear}`;
        const monthData = data.monthly_data[monthKey];

        const isPast = (actualYear < currYear) || (actualYear === currYear && actualMonth < currMonth);
        const isCurrent = (actualYear === currYear && actualMonth === currMonth);

        if (monthData && monthData.is_paid) {
          td.innerHTML = `<div class="matrix-center"><i class="fas fa-check text-success" title="Paid: Php ${(monthData.total||0).toLocaleString()}"></i></div>`;
        } else if (monthData && monthData.status === 'partial') {
          const remaining = monthData.remaining_balance ?? Math.max(0, matrixMonthlyFee - (monthData.total||0));
          td.innerHTML = `<div class="text-warning fw-bold d-flex flex-column align-items-center" title="Partial Payment: Php ${(monthData.total||0).toLocaleString()} paid, Php ${remaining.toLocaleString()} remaining" style="font-size: 0.75rem; line-height: 1.2;">
              <span style="font-size: 0.65rem; opacity: 0.8;">needs</span>
              <span style="font-size: 0.8rem; font-weight: 700;">Php ${remaining.toLocaleString()}</span>
            </div>`;
        } else if (isCurrent || isPast) {
          td.innerHTML = `<span style="color: transparent;">-</span>`;
        } else {
          td.innerHTML = `<span style="color: transparent;">-</span>`;
        }
        row.appendChild(td);
      }

      // Totals
      const totalTd = document.createElement('td');
      totalTd.className = 'text-center fw-bold align-middle';
      totalTd.innerHTML = `Php ${(data.student.total_paid||0).toLocaleString()}`;
      row.appendChild(totalTd);

      const payableTd = document.createElement('td');
      payableTd.className = 'text-center fw-bold align-middle';
      const payable = data.student.payable || data.student.remaining_balance || 0;
      payableTd.innerHTML = `Php ${(payable).toLocaleString()}`;
      row.appendChild(payableTd);

      tbody.appendChild(row);
    }

    function updateNavButtons() {
      const prev = document.getElementById('prevYearBtn');
      const next = document.getElementById('nextYearBtn');
      if (!prev || !next) return;
      prev.disabled = currentYearIndex <= 0;
      next.disabled = currentYearIndex >= availableYears.length - 1;
      prev.style.opacity = prev.disabled ? '0.5' : '1';
      next.style.opacity = next.disabled ? '0.5' : '1';
    }

    function showYear(y) {
      // y is page index
      updateMonthHeadersForYear(y);
      renderRowForYear(y);
      updateNavButtons();
    }

    document.addEventListener('DOMContentLoaded', function(){
      updateLegend();
      generateYears();
      if (availableYears.length === 0) {
        const year = new Date().getFullYear();
        availableYears = [year];
        currentYearIndex = 0;
      }
      showYear(availableYears[currentYearIndex]);

      document.getElementById('prevYearBtn')?.addEventListener('click', function(){
        if (currentYearIndex > 0) {
          currentYearIndex--;
          showYear(availableYears[currentYearIndex]);
        }
      });
      document.getElementById('nextYearBtn')?.addEventListener('click', function(){
        if (currentYearIndex < availableYears.length - 1) {
          currentYearIndex++;
          showYear(availableYears[currentYearIndex]);
        }
      });
    });
  </script>

  <script>
    $(function(){
      // Init DataTables for each tab
      const dtApproved = $('#approved-table').DataTable({ paging:true, ordering:true, info:false });
      const dtDeclined = $('#declined-table').DataTable({ paging:true, ordering:true, info:false });
      const dtPending  = $('#pending-table').DataTable({ paging:true, ordering:true, info:false });

      // Global search across all
      $('#global-search').on('keyup', function(){
        dtApproved.search(this.value).draw();
        dtDeclined.search(this.value).draw();
        dtPending.search(this.value).draw();
      });

      // Removed doughnut chart per request
    });
  </script>
@endsection

