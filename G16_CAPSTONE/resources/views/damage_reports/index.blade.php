@extends('layouts.app')

@section('content')
<div class="reports-bg">
    <div class="reports-card reports-card-wide ">
        <!-- Title Row -->
        <div class="reports-header">
            <div>
                <h1 class="reports-title">Maintenance Reports</h1>
                <p class="reports-subtitle">Easily track and manage all reported damages within the center.</p>
            </div>
        </div>

    <!-- Filters Row -->
    <form action="{{ route('damage_reports.index') }}" method="GET" id="reports-filter-form">
            <div class="reports-filters-row">
                <div class="reports-filter-col">
                    <label class="reports-label" for="search">Search</label>
                    <input type="text" name="search" id="search"
                        class="reports-input"
                        placeholder="Search by title or content..."
                        value="{{ request('search') }}">
                </div>
                <div class="reports-filter-col">
                    <label class="reports-label" for="date_filter">Time Period</label>
                    <select name="date_filter" id="date_filter" class="reports-input">
                        <option value="">All Time</option>
                        <option value="today" {{ request('date_filter') == 'today' ? 'selected' : '' }}>Today</option>
                        <option value="week" {{ request('date_filter') == 'week' ? 'selected' : '' }}>This Week</option>
                        <option value="month" {{ request('date_filter') == 'month' ? 'selected' : '' }}>This Month</option>
                    </select>
                </div>
                <div class="reports-filter-col">
                    <label class="reports-label" for="sort">Sort By</label>
                    <select name="sort" id="sort" class="reports-input">
                        <option value="latest" {{ request('sort') == 'latest' ? 'selected' : '' }}>Latest First</option>
                        <option value="oldest" {{ request('sort') == 'oldest' ? 'selected' : '' }}>Oldest First</option>
                        <option value="title" {{ request('sort') == 'title' ? 'selected' : '' }}>By Title</option>
                    </select>
                </div>
                <div class="reports-filter-btn-col" style="flex:1;display:flex;justify-content:flex-end;gap:20px;">
                    <button type="submit" class="reports-btn reports-btn-apply">Apply</button>
                    <a href="{{ route('damage_reports.index') }}" class="reports-btn reports-btn-clear" style="margin-left:0;">
                        Clear
                    </a>
                    <a href="{{ route('damage_reports.create') }}" class="reports-btn reports-btn-create reports-btn-apply" style="padding:8px 28px;height:40px;display:inline-flex;align-items:center;">
                        <img src="https://cdn-icons-png.flaticon.com/512/992/992651.png" alt="Add" style="width:18px;height:18px;margin-right:6px;vertical-align:middle;">Add New Report
                    </a>
                </div>
            </div>
        </form>
        
        <!-- Success Message -->
        @if(session('success'))
            <div class="reports-success">
                {{ session('success') }}
            </div>
        @endif

        <!-- Reports Table or Empty State -->
        @if($reports->isEmpty())
            <div class="reports-empty">
                <div class="reports-empty-icon">
                    <img src="https://cdn-icons-png.flaticon.com/512/685/685655.png" alt="No Reports" style="width:38px;height:38px;">
                </div>
                <h5 class="reports-empty-title">No Reports Found</h5>
                <p class="reports-empty-desc">Start by creating your first report to get started.</p>
                <a href="{{ route('damage_reports.create') }}" class="reports-btn reports-btn-create">
                    <img src="https://cdn-icons-png.flaticon.com/512/992/992651.png" alt="Add" style="width:18px;height:18px;margin-right:6px;vertical-align:middle;">Create first Report
                </a>
            </div>
        @else
            <!-- Reports Table -->
            <div class="reports-table-container" style="width:100%;">
                <div class="table-responsive" style="width:100%;">
                    <table class="table table-hover mb-0" style="width:100%; min-width:100%;">
                        <thead class="table-light">
                            <tr>
                                <th class="border-0 px-4 py-3 text-left" style="width:70px;vertical-align:middle;">Photo</th>
                                <th class="border-0 px-4 py-3 text-left">Report Details</th>
                                <th class="border-0 px-4 py-3 text-left">Staff In Charge</th>
                                <th class="border-0 px-4 py-3 text-left"> Area </th>
                                <th class="border-0 px-4 py-3 text-left"> Status </th>                         
                                <th class="border-0 px-4 py-3 text-left"> Created date </th>
                                <th class="border-0 px-4 py-3 text-center"> Actions </th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($reports as $report)
                                <tr class="report-row report-row-card">
                                    <!-- Photo -->
                                    <td class="px-4 py-3 align-middle">
                                        @if($report->photo_path)
                                            <img src="{{ Storage::url($report->photo_path) }}" alt="Report Photo"
                                                 style="width:48px;height:48px;object-fit:cover;border-radius:8px;border:1px solid #e5e7eb;">
                                        @else
                                            <div style="width:48px;height:48px;display:flex;align-items:center;justify-content:center;background:#f3f3f3;border-radius:8px;">
                                                <img src="https://cdn-icons-png.flaticon.com/512/685/685655.png" alt="No Photo" style="width:22px;height:22px;">
                                            </div>
                                        @endif
                                    </td>
                                    <!-- Report Details -->
                                    <td class="px-4 py-3 align-middle">
                                        <div>
                                            <div style="font-weight:700;font-size:1rem;color:#222;">{{ $report->title }}</div>
                                            <div style="color:#7b7b7b;font-size:0.95rem;">{{ $report->comment }}</div>
                                        </div>
                                    </td>
                                    <!-- Staff In Charge -->
                                    <td class="px-4 py-3 align-middle">
                                        <span style="color:#158aff;font-weight:600;">{{ $report->staff_in_charge }}</span>
                                    </td>
                                    <!-- Area -->
                                    <td class="px-4 py-3 align-middle">
                                        <span style="color:#14aaff;font-weight:500;">{{ $report->area }}</span>
                                    </td>
                                    <!-- Status -->
                                    <td class="px-4 py-3 align-middle">
                                        @if($report->status === 'resolved')
                                            <span style="background:#e6f9f0;color:#1a7f5a;padding:4px 16px;border-radius:8px;font-weight:600;font-size:0.95rem;display:inline-block;">Resolved</span>
                                        @else
                                            <span style="background:#ffd966;color:#b38600;padding:4px 16px;border-radius:8px;font-weight:600;font-size:0.95rem;display:inline-block;">Active</span>
                                        @endif
                                    </td>
                                    <!-- Date & Created -->
                                    <td class="px-4 py-3 align-middle">
                                        <div>
                                            <div style="font-weight:600;color:#222;">{{ $report->report_date->format('M d, Y') }}</div>
                                            <div style="color:#7b7b7b;font-size:0.95rem;">{{ $report->report_date->format('l') }}</div>
                                            <div style="color:#7b7b7b;font-size:0.95rem;">{{ $report->created_at->format('g:i A') }}</div>
                                        </div>
                                    </td>
                                    <!-- Actions -->
                                    <td class="px-4 py-3 align-middle text-center">
                                        <div style="display:flex;gap:12px;justify-content:center;">
                                            <!-- View Button -->
                                                          <a href="{{ route('damage_reports.show', $report) }}"
                                               class="action-btn"
                                               style="background:#2176ff;color:#fff;width:38px;height:38px;display:flex;align-items:center;justify-content:center;border-radius:50%;font-size:18px;box-shadow:0 2px 8px 0 rgba(33,118,255,0.08);"
                                               data-bs-toggle="tooltip"
                                               data-bs-placement="top"
                                               title="View full report details"
                                               aria-label="View report: {{ $report->title }}">
                                                <img src="https://cdn-icons-png.flaticon.com/512/709/709612.png" alt="View" style="width:20px;height:20px;">
                                            </a>
                                            <!-- Edit Button -->
                                                          <a href="{{ route('damage_reports.edit', $report) }}"
                                               class="action-btn"
                                               style="background:#ffb300;color:#fff;width:38px;height:38px;display:flex;align-items:center;justify-content:center;border-radius:50%;font-size:18px;box-shadow:0 2px 8px 0 rgba(255,179,0,0.08);"
                                               data-bs-toggle="tooltip"
                                               data-bs-placement="top"
                                               title="Edit this report"
                                               aria-label="Edit report: {{ $report->title }}">
                                                <img src="https://cdn-icons-png.flaticon.com/512/1159/1159633.png" alt="Edit" style="width:20px;height:20px;">
                                            </a>
                                            <!-- Delete Button -->
                                            <form action="{{ route('damage_reports.destroy', $report) }}" method="POST" class="d-inline" onsubmit="return confirmDelete('{{ $report->title }}')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        class="action-btn"
                                                        style="background:#ff5252;color:#fff;width:38px;height:38px;display:flex;align-items:center;justify-content:center;border-radius:50%;font-size:18px;box-shadow:0 2px 8px 0 rgba(255,82,82,0.08);border:none;"
                                                        data-bs-toggle="tooltip"
                                                        data-bs-placement="top"
                                                        title="Delete this report permanently"
                                                        aria-label="Delete report: {{ $report->title }}">
                                                    <img src="https://cdn-icons-png.flaticon.com/512/1214/1214428.png" alt="Delete" style="width:20px;height:20px;">
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <!-- Pagination -->
                <div class="mt-4">
                    {{ $reports->links() }}
                </div>
            </div>
        @endif
    </div>
</div>

<style>
/* Main background */
.reports-bg {
    min-height: 100vh;
    background: #f7fafd;
    padding: 40px 0;
    top : 100px;
    position: fixed;
    width: 1550px;
}
/* Card */
.reports-card {
    background: #fff;
    border-radius: 18px;
    max-width: 2400px;
    margin: 0 auto;
    box-shadow: 0 2px 16px 0 rgba(20,170,255,0.04);
    padding: 36px 32px 32px 32px;
    font-family: 'Poppins', Arial, Helvetica, sans-serif;
}
.reports-card-wide {
    max-width: 2400px;
    transition: max-width 0.2s;
}
@media (max-width: 2400px) {
    .reports-card-wide {
        max-width: 100vw;
    }
}
/* Header */
.reports-header {
    margin-bottom: 18px;
    height : 100px;
}
.reports-title {
    font-size: 1.6rem;
    font-weight: 700;
    color: #222;
    margin-bottom: 2px;
    display: flex;
    align-items: center;
    gap: 10px;
}
.reports-subtitle {
    color: #7b7b7b;
    font-size: 1rem;
    margin-bottom: 0;
}
/* Filters */
.reports-filters-row {
    display: flex;
    gap: 18px;
    align-items: flex-end;
    margin-bottom: 0;
    flex-wrap: wrap;
}
.reports-filter-col {
    min-width: 220px;
    flex: 0 0 260px;
}
.reports-filter-btn-col {
    min-width: 220px;
    display: flex;
    align-items: flex-end;
    justify-content: flex-end;
    gap: 10px;
}
.reports-label {
    font-size: 12px;
    color: #7b7b7b;
    margin-bottom: 4px;
    display: block;
}
.reports-input {
    width: 100%;
    padding: 8px 14px;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    background: #f7fafd;
    font-size: 15px;
    color: #222;
    outline: none;
    transition: border 0.2s;
}
.reports-input:focus {
    border: 1.5px solid #14aaff;
    background: #fff;
}
.reports-btn {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    border: none;
    border-radius: 8px;
    font-size: 15px;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.18s;
    box-shadow: none;
    text-decoration: none;
}
.reports-btn-apply {
    background: #14aaff;
    color: #fff;
    padding: 8px 28px;
}
.reports-btn-create.reports-btn-apply {
    /* Ensures Add New Report matches Apply button size and style */
    background: #14aaff;
    color: #fff;
    padding: 8px 28px;
    border-radius: 8px;
    font-size: 15px;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 7px;
    border: none;
    text-decoration: none;
    transition: background 0.18s;
}
.reports-btn-create.reports-btn-apply:hover {
    background: #0099e6;
}
.reports-btn-new {
    background: #14aaff;
    color: #fff;
    padding: 8px 22px;
    float: right;
}
.reports-btn-new:hover {
    background: #0099e6;
}
.reports-btn-clear {
    background:rgb(247, 17, 17);
    color: white;
    padding: 8px 22px;
    border: 1px solid #e0e0e0;
    margin-left: 0;
}
.reports-btn-clear:hover {
    background:rgb(180, 7, 7);
}
.reports-btn-export {
    background: #e6f6ff;
    color: #14aaff;
    padding: 8px 22px;
    border: 1px solid #b6e7fa;
}
.reports-btn-export:hover {
    background: #d0f0ff;
    color: #0099e6;
}
.reports-actions-row {
    display: flex;
    justify-content: flex-end;
    align-items: center;
    gap: 10px;
    margin: 18px 0 0 0;
}
/* Success */
.reports-success {
    background: #e6f9f0;
    border: 1px solid #b6f2d6;
    color: #1a7f5a;
    padding: 10px 18px;
    border-radius: 8px;
    margin-bottom: 18px;
}
/* Empty State */
.reports-empty {
    border: 1px solid #f0f0f0;
    border-radius: 14px;
    background: #f7fafd;
    padding: 48px 0 40px 0;
    text-align: center;
    margin-top: 18px;
}
.reports-empty-icon {
    margin-bottom: 18px;
    color: #e0e7ef;
    font-size: 38px;
}
.reports-empty-title {
    font-size: 1.2rem;
    font-weight: 600;
    color: #222;
    margin-bottom: 6px;
}
.reports-empty-desc {
    color: #7b7b7b;
    font-size: 1rem;
    margin-bottom: 18px;
}
.reports-btn-create {
    background: #14aaff;
    color: #fff;
    padding: 10px 28px;
    border-radius: 8px;
    font-size: 1rem;
    font-weight: 600;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    border: none;
    transition: background 0.18s;
}
.reports-btn-create:hover {
    background: #0099e6;
}
.reports-table-container {
    width: 100%;
    margin-top: 10px;
}
.table-responsive {
    width: 100%;
}
.table {
    width: 100% !important;
    min-width: unset !important;
}
/* Card-like row appearance */
.report-row-card {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 2px 8px 0 rgba(20,170,255,0.06);
    margin-bottom: 8px;
    border: 1px solid #f0f0f0;
}
.report-row-card td {
    vertical-align: middle !important;
    border-top: none !important;
    border-bottom: none !important;
}
</style>

<script>
// Enhanced delete confirmation function
function confirmDelete(reportTitle) {
    const message = `Are you sure you want to delete the report "${reportTitle}"?\n\nThis action cannot be undone and will permanently remove:
• The report details
• Any attached photo
• All associated data

Type "DELETE" to confirm:`;

    const userInput = prompt(message);

    if (userInput === "DELETE") {
        // Add loading state to delete button
        const deleteBtn = event.target.closest('.action-btn-delete');
        if (deleteBtn) {
            deleteBtn.classList.add('loading');
            deleteBtn.querySelector('i').className = 'fas fa-spinner';
            deleteBtn.querySelector('.btn-label').textContent = 'Deleting...';
        }
        return true;
    }

    return false;
}

document.addEventListener('DOMContentLoaded', function() {
    // Initialize Bootstrap tooltips with enhanced options
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl, {
            delay: { show: 300, hide: 100 },
            animation: true,
            html: false
        });
    });

    // Photo thumbnail click to view full image
    document.querySelectorAll('.report-thumbnail img').forEach(function(img) {
        img.addEventListener('click', function() {
            // Create modal to show full image
            const modal = document.createElement('div');
            modal.className = 'modal fade';
            modal.innerHTML = `
                <div class="modal-dialog modal-lg modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Report Photo</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body text-center">
                            <img src="${this.src}" class="img-fluid rounded" alt="Report Photo">
                        </div>
                    </div>
                </div>
            `;
            document.body.appendChild(modal);
            const bsModal = new bootstrap.Modal(modal);
            bsModal.show();

            // Remove modal from DOM when hidden
            modal.addEventListener('hidden.bs.modal', function() {
                document.body.removeChild(modal);
            });
        });
    });

    // Enhanced search functionality
    const searchInput = document.querySelector('input[name="search"]');
    if (searchInput) {
        let searchTimeout;
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                // Auto-submit search after 500ms of no typing
                if (this.value.length >= 3 || this.value.length === 0) {
                    this.form.submit();
                }
            }, 500);
        });
    }

    // Enhanced action button interactions
    document.querySelectorAll('.action-btn').forEach(function(btn) {
        // Add ripple effect on click
        btn.addEventListener('click', function(e) {
            const ripple = document.createElement('span');
            const rect = this.getBoundingClientRect();
            const size = Math.max(rect.width, rect.height);
            const x = e.clientX - rect.left - size / 2;
            const y = e.clientY - rect.top - size / 2;

            ripple.style.cssText = `
                position: absolute;
                width: ${size}px;
                height: ${size}px;
                left: ${x}px;
                top: ${y}px;
                background: rgba(255, 255, 255, 0.3);
                border-radius: 50%;
                transform: scale(0);
                animation: ripple 0.6s ease-out;
                pointer-events: none;
            `;

            this.appendChild(ripple);

            setTimeout(() => {
                ripple.remove();
            }, 600);
        });

        // Add keyboard navigation support
        btn.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                this.click();
            }
        });
    });

    // Add ripple animation styles
    const style = document.createElement('style');
    style.textContent = `
        @keyframes ripple {
            to {
                transform: scale(2);
                opacity: 0;
            }
        }

        .action-btn {
            position: relative;
            overflow: hidden;
        }
    `;
    document.head.appendChild(style);

    // Row click to view report (except on action buttons)
    document.querySelectorAll('.report-row').forEach(function(row) {
        row.addEventListener('click', function(e) {
            // Don't trigger if clicking on buttons or links
            if (e.target.closest('button, a, form, .action-buttons-container')) {
                return;
            }

            // Find the view link and navigate to it
            const viewLink = row.querySelector('.action-btn-view');
            if (viewLink) {
                window.location.href = viewLink.href;
            }
        });

        // Add cursor pointer to indicate clickable rows
        row.style.cursor = 'pointer';

        // Add accessibility attributes
        row.setAttribute('role', 'button');
        row.setAttribute('tabindex', '0');
        row.setAttribute('aria-label', 'Click to view report details');

        // Keyboard support for row navigation
        row.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                const viewLink = this.querySelector('.action-btn-view');
                if (viewLink) {
                    viewLink.click();
                }
            }
        });
    });

    // Add loading states for action buttons
    document.querySelectorAll('.action-btn:not(.action-btn-delete)').forEach(function(btn) {
        btn.addEventListener('click', function() {
            if (!this.classList.contains('loading')) {
                this.classList.add('loading');
                const icon = this.querySelector('i');
                const label = this.querySelector('.btn-label');

                if (icon && label) {
                    const originalIcon = icon.className;
                    const originalLabel = label.textContent;

                    icon.className = 'fas fa-spinner fa-spin';
                    label.textContent = 'Loading...';

                    // Reset after navigation (fallback)
                    setTimeout(() => {
                        icon.className = originalIcon;
                        label.textContent = originalLabel;
                        this.classList.remove('loading');
                    }, 2000);
                }
            }
        });
    });
});
</script>
@endsection