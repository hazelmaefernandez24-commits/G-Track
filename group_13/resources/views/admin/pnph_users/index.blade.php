@extends('layouts.admin_layout')

@section('content')
<div class="admin-container">
    <div class="page-header">
        <div class="header-content">
            <h1>Manage Users</h1>
            <p class="text-muted">View and manage system users</p>
        </div>
        <div class="header-actions">
            <a href="{{ route('admin.pnph_users.create') }}" class="create-user-btn">
                <div class="btn-icon-wrapper">
                    <i class="fas fa-user-plus"></i>
                </div>
                <div class="btn-content">
                    <span class="btn-title">Create New User</span>
                    <span class="btn-subtitle">Add user to system</span>
                </div>
                <div class="btn-arrow">
                    <i class="fas fa-arrow-right"></i>
                </div>
            </a>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <form action="{{ route('admin.pnph_users.index') }}" method="GET" class="filter-form">
                <div class="filter-group">
                    <div class="form-group">
                        <select name="role" id="role" class="form-select" onchange="this.form.submit()">
                            <option value="">All Roles</option>
                            @foreach ($roles as $role)
                                <option value="{{ $role }}" {{ $roleFilter == $role ? 'selected' : '' }}>
                                    {{ ucfirst($role) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <select name="status" id="status" class="form-select" onchange="this.form.submit()">
                            <option value="">All Status</option>
                            <option value="active" {{ $statusFilter == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ $statusFilter == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                </div>
            </form>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($users as $user)
                            <tr>
                                <td>#{{ $user->user_id }}</td>
                                <td>
                                    <div class="user-info">
                                        <div class="user-avatar">
                                            {{ strtoupper(substr($user->user_fname, 0, 1)) }}
                                        </div>
                                        <div>
                                            <div class="user-name">{{ $user->user_fname }} {{ $user->user_lname }}</div>
                                            <div class="user-details">
                                                {{ $user->user_mInitial }}{{ $user->user_suffix ? ', ' . $user->user_suffix : '' }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $user->user_email }}</td>
                                <td>
                                    <span class="role-badge">{{ ucfirst($user->user_role) }}</span>
                                </td>
                                <td>
                                    @if ($user->status === 'active')
                                        <span class="status-badge active">
                                            <i class="fas fa-circle"></i> Active
                                        </span>
                                    @else
                                        <span class="status-badge inactive">
                                            <i class="fas fa-circle"></i> Inactive
                                        </span>
                                    @endif
                                </td>
                                <td class="actions">
                                    <div class="action-buttons">
                                        <a href="{{ route('admin.pnph_users.show', $user->user_id) }}"
                                           class="btn-icon"
                                           title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.pnph_users.edit', $user->user_id) }}"
                                           class="btn-icon"
                                           title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        @if($user->status === 'active')
                                            <form action="{{ route('admin.pnph_users.destroy', $user->user_id) }}"
                                                  method="POST"
                                                  style="display: inline-block;"
                                                  onsubmit="return confirmDeactivate('{{ $user->user_fname }} {{ $user->user_lname }}')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        class="btn-icon"
                                                        title="Deactivate User"
                                                        style="background: none; border: none; color: #dc3545; cursor: pointer;">
                                                    <i class="fas fa-user-slash"></i>
                                                </button>
                                            </form>
                                        @else
                                            <span class="btn-icon"
                                                  title="User is inactive"
                                                  style="color: #6c757d; cursor: not-allowed;">
                                                <i class="fas fa-user-slash"></i>
                                            </span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4">No users found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if ($users->hasPages())
            <div class="pagination-container">
                <div class="pagination-info">
                    Showing {{ $users->firstItem() }} to {{ $users->lastItem() }} of {{ $users->total() }} entries
                </div>
                <div class="pagination-buttons">
                    @if ($users->onFirstPage())
                        <span class="pagination-button disabled">
                            <i class="fas fa-chevron-left"></i> Previous
                        </span>
                    @else
                        <a href="{{ $users->appends(request()->query())->previousPageUrl() }}" class="pagination-button">
                            <i class="fas fa-chevron-left"></i> Previous
                        </a>
                    @endif

                    <div class="page-info">
                        Page {{ $users->currentPage() }} of {{ $users->lastPage() }}
                    </div>

                    @if ($users->hasMorePages())
                        <a href="{{ $users->appends(request()->query())->nextPageUrl() }}" class="pagination-button">
                            Next <i class="fas fa-chevron-right"></i>
                        </a>
                    @else
                        <span class="pagination-button disabled">
                            Next <i class="fas fa-chevron-right"></i>
                        </span>
                    @endif
                </div>
            </div>
            @endif
        </div>
    </div>

    <!-- Optional: Floating Action Button for Mobile (uncomment to enable) -->
    <!--
    <a href="{{ route('admin.pnph_users.create') }}" class="create-user-fab d-md-none" title="Create New User">
        <i class="fas fa-plus"></i>
    </a>
    -->
</div>

<style>
/* Main Container */
.admin-container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 20px;
}

/* Page Header */
.page-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 25px;
    gap: 20px;
}

.page-header h1 {
    font-size: 1.8rem;
    color: var(--text-color);
    margin: 0 0 5px 0;
}

.page-header .text-muted {
    color: #6c757d;
    margin: 0;
}

.header-content {
    flex: 1;
}

.header-actions {
    flex-shrink: 0;
}

/* Modern Create User Button */
.create-user-btn {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 16px 24px;
    background: linear-gradient(135deg, #22bbea 0%, #1e9bd1 100%);
    color: white;
    text-decoration: none;
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(34, 187, 234, 0.3);
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
    min-width: 240px;
}

.create-user-btn::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
    transition: left 0.5s ease;
}

.create-user-btn:hover::before {
    left: 100%;
}

.create-user-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 25px rgba(34, 187, 234, 0.4);
    color: white;
    text-decoration: none;
}

.create-user-btn:active {
    transform: translateY(0);
    box-shadow: 0 4px 15px rgba(34, 187, 234, 0.3);
}

.btn-icon-wrapper {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 44px;
    height: 44px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 10px;
    flex-shrink: 0;
}

.btn-icon-wrapper i {
    font-size: 18px;
    color: white;
}

.btn-content {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.btn-title {
    font-size: 16px;
    font-weight: 600;
    color: white;
    line-height: 1.2;
}

.btn-subtitle {
    font-size: 12px;
    color: rgba(255, 255, 255, 0.8);
    line-height: 1.2;
}

.btn-arrow {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 24px;
    height: 24px;
    background: rgba(255, 255, 255, 0.15);
    border-radius: 6px;
    transition: all 0.3s ease;
    flex-shrink: 0;
}

.btn-arrow i {
    font-size: 12px;
    color: white;
    transition: transform 0.3s ease;
}

.create-user-btn:hover .btn-arrow {
    background: rgba(255, 255, 255, 0.25);
}

.create-user-btn:hover .btn-arrow i {
    transform: translateX(2px);
}

/* Alternative Compact Button Style */
.create-user-btn.compact {
    min-width: auto;
    padding: 12px 20px;
    gap: 10px;
}

.create-user-btn.compact .btn-icon-wrapper {
    width: 36px;
    height: 36px;
}

.create-user-btn.compact .btn-icon-wrapper i {
    font-size: 16px;
}

.create-user-btn.compact .btn-title {
    font-size: 14px;
}

.create-user-btn.compact .btn-subtitle {
    display: none;
}

/* Floating Action Button Style (Alternative) */
.create-user-fab {
    position: fixed;
    bottom: 30px;
    right: 30px;
    width: 60px;
    height: 60px;
    background: linear-gradient(135deg, #22bbea 0%, #1e9bd1 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    text-decoration: none;
    box-shadow: 0 6px 20px rgba(34, 187, 234, 0.4);
    transition: all 0.3s ease;
    z-index: 1000;
}

.create-user-fab:hover {
    transform: scale(1.1);
    box-shadow: 0 8px 25px rgba(34, 187, 234, 0.5);
    color: white;
    text-decoration: none;
}

.create-user-fab i {
    font-size: 24px;
}

/* Responsive Design */
@media (max-width: 768px) {
    .page-header {
        flex-direction: column;
        align-items: stretch;
        gap: 15px;
    }

    .create-user-btn {
        min-width: auto;
        justify-content: center;
    }

    .btn-content {
        text-align: center;
    }
}

/* Card Styles */
.card {
    background: white;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    margin-bottom: 25px;
    overflow: hidden;
}

.card-header {
    padding: 20px;
    border-bottom: 1px solid #eee;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.card-body {
    padding: 0;
}

/* Table Styles */
.table-responsive {
    overflow-x: auto;
}

.data-table {
    width: 100%;
    border-collapse: collapse;
}

.data-table th,
.data-table td {
    padding: 15px 20px;
    text-align: left;
    border-bottom: 1px solid #eee;
}

.data-table th {
    font-weight: 600;
    color: #555;
    font-size: 0.9rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.data-table tr:last-child td {
    border-bottom: none;
}

.data-table tr:hover {
    background-color: #f9f9f9;
}

/* User Info */
.user-info {
    display: flex;
    align-items: center;
    gap: 12px;
}

.user-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background-color: #e3f2fd;
    color: var(--primary-color);
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 0.9rem;
}

.user-name {
    font-weight: 500;
    color: #333;
}

.user-details {
    font-size: 0.85rem;
    color: #6c757d;
}

/* Badges */
.role-badge {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 0.8rem;
    font-weight: 500;
    background-color: #e3f2fd;
    color: #1976d2;
}

.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 0.8rem;
    font-weight: 500;
}

.status-badge i {
    font-size: 0.6rem;
}

.status-badge.active {
    background-color: #e8f5e9;
    color: #2e7d32;
}

.status-badge.inactive {
    background-color: #ffebee;
    color: #c62828;
}

/* Action Buttons */
.actions {
    text-align: right;
}

.action-buttons {
    display: flex;
    justify-content: flex-end;
    gap: 8px;
}

.btn-icon {
    width: 32px;
    height: 32px;
    border-radius: 6px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    color: #666;
    background: #f5f5f5;
    transition: all 0.2s;
}

.btn-icon:hover {
    background: #e0e0e0;
    color: #333;
    text-decoration: none;
}

/* Pagination */
.pagination-container {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px;
    border-top: 1px solid #eee;
}

.pagination-info {
    color: #6c757d;
    font-size: 0.9rem;
}

.pagination-buttons {
    display: flex;
    align-items: center;
    gap: 10px;
}

.pagination-button {
    padding: 8px 16px;
    border-radius: 6px;
    background: white;
    border: 1px solid #ddd;
    color: #333;
    font-size: 0.9rem;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    text-decoration: none;
    transition: all 0.2s;
}

.pagination-button:hover:not(.disabled) {
    background: #f5f5f5;
    border-color: #ccc;
}

.pagination-button.disabled {
    color: #aaa;
    cursor: not-allowed;
}

.page-info {
    margin: 0 10px;
    font-size: 0.9rem;
    color: #666;
}

/* Form Elements */
.filter-form {
    width: 100%;
}

.filter-group {
    display: flex;
    gap: 15px;
    align-items: center;
}

.form-group {
    margin: 0;
}

.form-select {
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 6px;
    background-color: white;
    font-size: 0.9rem;
    min-width: 180px;
    -webkit-appearance: none;
    -moz-appearance: none;
    appearance: none;
    background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
    background-repeat: no-repeat;
    background-position: right 10px center;
    background-size: 16px;
    padding-right: 36px;
}

/* Responsive */
@media (max-width: 768px) {
    .page-header {
        flex-direction: column;
        gap: 15px;
    }

    .card-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 15px;
    }

    .filter-group {
        flex-direction: column;
        align-items: stretch;
        gap: 10px;
    }

    .form-select {
        min-width: 100%;
    }

    .pagination-container {
        flex-direction: column;
        gap: 15px;
        align-items: flex-start;
    }
}
</style>
</div>

<style>
    /* Simple and Clean Pagination Style */
    .pagination-simple {
        margin-top: 30px;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 10px;
    }
    
    .pagination-buttons {
        display: flex;
        gap: 20px;
    }
    
    .pagination-button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 10px 25px;
        border-radius: 30px;
        font-weight: 500;
        text-decoration: none;
        transition: all 0.3s ease;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
    
    .pagination-button.prev {
        background: linear-gradient(135deg, #1a8fc4 0%, #22bbea 100%);
        color: white;
        border: 1px solid #1a8fc4;
    }
    
    .pagination-button.next {
        background: linear-gradient(135deg, #22bbea 0%, #4ac9f5 100%);
        color: white;
        border: 1px solid #22bbea;
    }
    
    .pagination-button.prev:hover:not(.disabled) {
        background: linear-gradient(135deg, #15779e 0%, #1a9ecf 100%);
    }
    
    .pagination-button.next:hover:not(.disabled) {
        background: linear-gradient(135deg, #1a9ecf 0%, #3abcec 100%);
    }
    
    .pagination-button:hover:not(.disabled) {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.15);
    }
    
    .pagination-button:active:not(.disabled) {
        transform: translateY(0);
    }
    
    .pagination-button.disabled {
        background: #e9ecef;
        color: #6c757d;
        cursor: not-allowed;
        opacity: 0.7;
    }
    
    .pagination-button i {
        margin: 0 5px;
        font-size: 0.9em;
    }
    
    .pagination-info {
        color: #666;
        font-size: 0.9em;
        margin-top: 8px;
        font-weight: 500;
    }
    
    .pagination-button:focus {
        outline: none;
        box-shadow: 0 0 0 3px rgba(34, 187, 234, 0.3);
    }
</style>

@endsection

@section('scripts')
<script>
    function confirmDeactivate(userName) {
        return confirm(`Are you sure you want to deactivate user "${userName}"?\n\nThis will prevent them from logging into the system.`);
    }

    // Enhanced Create User Button Interactions
    document.addEventListener('DOMContentLoaded', function() {
        const createBtn = document.querySelector('.create-user-btn');

        if (createBtn) {
            // Add ripple effect on click
            createBtn.addEventListener('click', function(e) {
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

            // Add loading state when clicked
            createBtn.addEventListener('click', function(e) {
                const btnTitle = this.querySelector('.btn-title');
                const btnIcon = this.querySelector('.btn-icon-wrapper i');
                const originalTitle = btnTitle.textContent;
                const originalIcon = btnIcon.className;

                // Show loading state
                btnTitle.textContent = 'Loading...';
                btnIcon.className = 'fas fa-spinner fa-spin';

                // Reset after navigation (in case of back button)
                setTimeout(() => {
                    btnTitle.textContent = originalTitle;
                    btnIcon.className = originalIcon;
                }, 2000);
            });
        }

        // Add keyboard navigation
        document.addEventListener('keydown', function(e) {
            if (e.ctrlKey && e.key === 'n') {
                e.preventDefault();
                if (createBtn) {
                    createBtn.click();
                }
            }
        });

        // Add tooltip for keyboard shortcut
        if (createBtn) {
            createBtn.title = 'Create New User (Ctrl+N)';
        }
    });

    // Add CSS for ripple animation
    const style = document.createElement('style');
    style.textContent = `
        @keyframes ripple {
            to {
                transform: scale(2);
                opacity: 0;
            }
        }

        .create-user-btn {
            position: relative;
            overflow: hidden;
        }

        /* Pulse animation for attention */
        @keyframes pulse {
            0% {
                box-shadow: 0 4px 20px rgba(34, 187, 234, 0.3);
            }
            50% {
                box-shadow: 0 6px 25px rgba(34, 187, 234, 0.5);
            }
            100% {
                box-shadow: 0 4px 20px rgba(34, 187, 234, 0.3);
            }
        }

        .create-user-btn.pulse {
            animation: pulse 2s infinite;
        }

        /* Loading state */
        .create-user-btn.loading {
            pointer-events: none;
            opacity: 0.8;
        }

        .create-user-btn.loading .btn-arrow {
            display: none;
        }
    `;
    document.head.appendChild(style);

    // Enhanced search functionality
    function filterUsers() {
        const searchInput = document.getElementById('userSearch');
        const roleFilter = document.getElementById('roleFilter');
        const statusFilter = document.getElementById('statusFilter');

        if (searchInput) {
            const searchTerm = searchInput.value.toLowerCase();
            const selectedRole = roleFilter ? roleFilter.value : '';
            const selectedStatus = statusFilter ? statusFilter.value : '';

            const rows = document.querySelectorAll('tbody tr');

            rows.forEach(row => {
                const name = row.querySelector('.user-name')?.textContent.toLowerCase() || '';
                const email = row.cells[2]?.textContent.toLowerCase() || '';
                const role = row.querySelector('.role-badge')?.textContent.toLowerCase() || '';
                const status = row.querySelector('.status-badge')?.textContent.toLowerCase() || '';

                const matchesSearch = name.includes(searchTerm) || email.includes(searchTerm);
                const matchesRole = !selectedRole || role.includes(selectedRole.toLowerCase());
                const matchesStatus = !selectedStatus || status.includes(selectedStatus.toLowerCase());

                if (matchesSearch && matchesRole && matchesStatus) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }
    }

    // Auto-filter on input
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('userSearch');
        const roleFilter = document.getElementById('roleFilter');
        const statusFilter = document.getElementById('statusFilter');

        if (searchInput) {
            searchInput.addEventListener('input', filterUsers);
        }
        if (roleFilter) {
            roleFilter.addEventListener('change', filterUsers);
        }
        if (statusFilter) {
            statusFilter.addEventListener('change', filterUsers);
        }
    });
</script>
@endsection