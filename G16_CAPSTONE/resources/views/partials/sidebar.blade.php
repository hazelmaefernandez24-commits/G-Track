<!-- Educator-style Sidebar (unified with roomtask page design) -->
<nav class="sidebar-container" id="sidebar" draggable="false">
    <div style="padding: 20px 6px;">
        <ul class="nav flex-column" style="list-style: none; padding: 0; margin: 0;">
            {{-- Educator-friendly grouped sidebar with dropdown parents --}}
        @if(auth()->check() && optional(auth()->user())->user_role === 'educator')
                <li class="nav-item" style="margin: 8px 0;">
                    <a href="{{ route('dashboard') }}" class="nav-link sidebar-link {{ Request::routeIs('dashboard') ? 'active' : '' }}" style="display: flex; align-items: center; padding: 12px 20px; text-decoration: none; color: #374151; border-radius: 8px; margin: 0 15px; transition: all 0.3s ease;">
                        <svg class="sidebar-icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false" role="img" style="vertical-align:middle;" xmlns="http://www.w3.org/2000/svg">
                            <rect x="3" y="3" width="8" height="8" rx="1" fill="currentColor" />
                            <rect x="13" y="3" width="8" height="8" rx="1" fill="currentColor" opacity="0.9" />
                            <rect x="3" y="13" width="8" height="8" rx="1" fill="currentColor" opacity="0.7" />
                            <rect x="13" y="13" width="8" height="8" rx="1" fill="currentColor" opacity="0.5" />
                        </svg>
                        Dashboard
                    </a>
                </li>

                <li class="nav-item" style="margin: 8px 0;">
                    <details class="nav-parent" @if(auth()->check() && auth()->user()->user_role === 'educator') open @endif style="padding: 0 15px;">
                        <summary class="sidebar-summary"> 
                            <span class="summary-left">
                                <svg class="sidebar-icon" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M9 6h11M9 12h11M9 18h11" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" fill="none" /><circle cx="4.5" cy="6" r="1" fill="currentColor" /><circle cx="4.5" cy="12" r="1" fill="currentColor" /><circle cx="4.5" cy="18" r="1" fill="currentColor" /></svg>
                                <span class="summary-text">General Tasking</span>
                            </span>
                            <span class="summary-chevron" aria-hidden="true"></span>
                        </summary>
                        <ul style="list-style:none; padding-left: 14px; margin:8px 0 12px 0;">
                            <li style="margin:6px 0;">
                                <a href="{{ route('generalTask') }}" class="nav-link {{ Request::routeIs('generalTask') ? 'active' : '' }} sidebar-child" style="display:block; padding:8px 16px; border-radius:6px; text-decoration:none; color:#374151;">
                                    <svg class="sidebar-child-icon" viewBox="0 0 24 24" width="16" height="16" xmlns="http://www.w3.org/2000/svg"><rect x="3" y="5" width="18" height="14" rx="2" fill="none" stroke="currentColor" stroke-width="1.4"/><path d="M8 9h8M8 13h8" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round" fill="none"/></svg>
                                    General Tasks
                                </a>
                            </li>
                            <li style="margin:6px 0;">
                                <a href="{{ route('generalTask.inspection') }}" class="nav-link {{ Request::routeIs('generalTask.inspection') ? 'active' : '' }} sidebar-child" style="display:block; padding:8px 16px; border-radius:6px; text-decoration:none; color:#374151;">
                                    <svg class="sidebar-child-icon" viewBox="0 0 24 24" width="16" height="16" xmlns="http://www.w3.org/2000/svg"><path d="M11 4h10M11 10h10M11 16h10" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round" fill="none"/><circle cx="5" cy="6" r="2" fill="none" stroke="currentColor" stroke-width="1.4"/><path d="M5 8v10" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/><path d="M2 21l3-3 3 3" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round" fill="none"/></svg>
                                    Task Evaluation
                                </a>
                            </li>
                        </ul>
                    </details>
                </li>

                <li class="nav-item" style="margin: 8px 0;">
                    <details class="nav-parent" @if(auth()->check() && auth()->user()->user_role === 'educator') open @endif style="padding: 0 15px;">
                        <summary class="sidebar-summary">
                            <span class="summary-left">
                                <svg class="sidebar-icon" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><rect x="3" y="4" width="18" height="16" rx="2" fill="none" stroke="currentColor" stroke-width="1.6" /><path d="M8 8h8M8 12h8M8 16h5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" fill="none" /><circle cx="17.5" cy="7.5" r="1.2" fill="currentColor" /></svg>
                                <span class="summary-text">Room & Task Management</span>
                            </span>
                            <span class="summary-chevron" aria-hidden="true"></span>
                        </summary>
                        <ul style="list-style:none; padding-left: 14px; margin:8px 0 12px 0;">
                            <li style="margin:6px 0;">
                                <a href="{{ route('room.management') }}" class="nav-link {{ Request::routeIs('room.management') ? 'active' : '' }} sidebar-child" style="display:block; padding:8px 16px; border-radius:6px; text-decoration:none; color:#374151;">
                                    <svg class="sidebar-child-icon" viewBox="0 0 24 24" width="16" height="16" xmlns="http://www.w3.org/2000/svg"><rect x="3" y="4" width="18" height="16" rx="2" fill="none" stroke="currentColor" stroke-width="1.4"/><path d="M8 8h8M8 12h8M8 16h5" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" fill="none"/></svg>
                                    Manage Rooms
                                </a>
                            </li>
                            <li style="margin:6px 0;">
                                <a href="{{ url('/manage_roomtask') }}" class="nav-link {{ Request::routeIs('manage_roomtask') ? 'active' : '' }} sidebar-child" style="display:block; padding:8px 16px; border-radius:6px; text-decoration:none; color:#374151;">
                                    <svg class="sidebar-child-icon" viewBox="0 0 24 24" width="16" height="16" xmlns="http://www.w3.org/2000/svg"><path d="M4 7h16M4 12h10M4 17h6" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round" fill="none"/></svg>
                                    Task Templates
                                </a>
                            </li>
                            <li style="margin:6px 0;">
                                <a href="{{ route('admin.task.history') }}" class="nav-link {{ Request::routeIs('admin.task.history') ? 'active' : '' }} sidebar-child" style="display:block; padding:8px 16px; border-radius:6px; text-decoration:none; color:#374151;">
                                    <svg class="sidebar-child-icon" viewBox="0 0 24 24" width="16" height="16" xmlns="http://www.w3.org/2000/svg"><circle cx="12" cy="12" r="9" fill="none" stroke="currentColor" stroke-width="1.4"/><path d="M12 7v6l4 2" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" fill="none"/></svg>
                                    Checklist History
                                </a>
                            </li>
                        </ul>
                    </details>
                </li>

                <li class="nav-item" style="margin: 8px 0;">
                    <a href="{{ route('damage_reports.index') }}" class="nav-link {{ Request::routeIs('damage_reports.*') ? 'active' : '' }}" style="display: flex; align-items: center; padding: 12px 20px; text-decoration: none; color: #374151; border-radius: 8px; margin: 0 15px; transition: all 0.3s ease;">
                        <svg class="sidebar-icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false" role="img" xmlns="http://www.w3.org/2000/svg">
                            <path d="M3 7h18M6 3h12v4" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" fill="none" />
                            <rect x="4" y="9" width="16" height="12" rx="2" fill="none" stroke="currentColor" stroke-width="1.6" />
                            <path d="M8 13h8M8 17h5" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" />
                        </svg>
                        Maintenance Reports
                    </a>
                </li>

            @elseif(auth()->check() && optional(auth()->user())->user_role === 'student')
            <li class="nav-item" style="margin: 8px 0;">
                <a href="{{ route('dashboard') }}" class="nav-link sidebar-link {{ Request::routeIs('dashboard') ? 'active' : '' }}" style="display: flex; align-items: center; padding: 12px 20px; text-decoration: none; color: #374151; border-radius: 8px; margin: 0 15px; transition: all 0.3s ease;">
                    <svg class="sidebar-icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false" role="img" style="vertical-align:middle;" xmlns="http://www.w3.org/2000/svg">
                        <rect x="3" y="3" width="8" height="8" rx="1" fill="currentColor" />
                        <rect x="13" y="3" width="8" height="8" rx="1" fill="currentColor" opacity="0.9" />
                        <rect x="3" y="13" width="8" height="8" rx="1" fill="currentColor" opacity="0.7" />
                        <rect x="13" y="13" width="8" height="8" rx="1" fill="currentColor" opacity="0.5" />
                    </svg>
                    Dashboard
                </a>
            </li>

            <li class="nav-item" style="margin: 8px 0;">
                <details class="nav-parent" open style="padding: 0 15px;">
                    <summary class="sidebar-summary">
                        <span class="summary-left">
                            <svg class="sidebar-icon" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M9 6h11M9 12h11M9 18h11" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" fill="none" /><circle cx="4.5" cy="6" r="1" fill="currentColor" /><circle cx="4.5" cy="12" r="1" fill="currentColor" /><circle cx="4.5" cy="18" r="1" fill="currentColor" /></svg>
                            <span class="summary-text">General Tasking</span>
                        </span>
                        <span class="summary-chevron" aria-hidden="true"></span>
                    </summary>
                    <ul style="list-style:none; padding-left: 14px; margin:8px 0 12px 0;">
                        <li style="margin:6px 0;">
                            <a href="{{ route('my.tasks.simple') }}" class="nav-link {{ Request::routeIs('my.tasks.simple') ? 'active' : '' }} sidebar-child" style="display:block; padding:8px 16px; border-radius:6px; text-decoration:none; color:#374151;">
                                <svg class="sidebar-child-icon" viewBox="0 0 24 24" width="16" height="16" xmlns="http://www.w3.org/2000/svg"><path d="M9 12l2 2 4-4" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" fill="none" /><rect x="3" y="4" width="18" height="16" rx="2" fill="none" stroke="currentColor" stroke-width="1.6" /><path d="M8 8h8M8 16h5" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round" fill="none" /></svg>
                                My Assigned Tasks
                            </a>
                        </li>
                        <li style="margin:6px 0;">
                            <a href="{{ route('generalTask') }}" class="nav-link {{ Request::routeIs('generalTask') ? 'active' : '' }} sidebar-child" style="display:block; padding:8px 16px; border-radius:6px; text-decoration:none; color:#374151;">
                                <svg class="sidebar-child-icon" viewBox="0 0 24 24" width="16" height="16" xmlns="http://www.w3.org/2000/svg"><rect x="3" y="5" width="18" height="14" rx="2" fill="none" stroke="currentColor" stroke-width="1.4"/><path d="M8 9h8M8 13h8" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round" fill="none"/></svg>
                                All Task Assignments
                            </a>
                        </li>
                    </ul>
                </details>
            </li>

            <li class="nav-item" style="margin: 8px 0;">
                <details class="nav-parent" open style="padding: 0 15px;">
                    <summary class="sidebar-summary">
                        <span class="summary-left">
                            <svg class="sidebar-icon" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><rect x="3" y="4" width="18" height="16" rx="2" fill="none" stroke="currentColor" stroke-width="1.6" /><path d="M8 8h8M8 12h8M8 16h5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" fill="none" /><circle cx="17.5" cy="7.5" r="1.2" fill="currentColor" /></svg>
                            <span class="summary-text">Room Tasking</span>
                        </span>
                        <span class="summary-chevron" aria-hidden="true"></span>
                    </summary>
                    <ul style="list-style:none; padding-left: 14px; margin:8px 0 12px 0;">
                        <li style="margin:6px 0;">
                            <a href="{{ route('student.room.tasking') }}" class="nav-link {{ Request::routeIs('student.room.tasking') ? 'active' : '' }} sidebar-child" style="display:block; padding:8px 16px; border-radius:6px; text-decoration:none; color:#374151;">
                                <svg class="sidebar-child-icon" viewBox="0 0 24 24" width="16" height="16" xmlns="http://www.w3.org/2000/svg"><path d="M4 7h16M4 12h10M4 17h6" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round" fill="none"/></svg>
                                Task Assignments & Checklist
                            </a>
                        </li>
                        <li style="margin:6px 0;">
                            <a href="{{ route('admin.task.history') }}" class="nav-link {{ Request::routeIs('admin.task.history') ? 'active' : '' }} sidebar-child" style="display:block; padding:8px 16px; border-radius:6px; text-decoration:none; color:#374151;">
                                <svg class="sidebar-child-icon" viewBox="0 0 24 24" width="16" height="16" xmlns="http://www.w3.org/2000/svg"><circle cx="12" cy="12" r="9" fill="none" stroke="currentColor" stroke-width="1.4"/><path d="M12 7v6l4 2" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" fill="none"/></svg>
                                Checklist History
                            </a>
                        </li>
                    </ul>
                </details>
            </li>

        @elseif(auth()->check() && optional(auth()->user())->user_role === 'inspector')
            <li class="nav-item" style="margin: 8px 0;">
                <a href="{{ route('dashboard') }}" class="nav-link sidebar-link {{ Request::routeIs('dashboard') ? 'active' : '' }}" style="display: flex; align-items: center; padding: 12px 20px; text-decoration: none; color: #374151; border-radius: 8px; margin: 0 15px; transition: all 0.3s ease;">
                    <svg class="sidebar-icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false" role="img" style="vertical-align:middle;" xmlns="http://www.w3.org/2000/svg">
                        <rect x="3" y="3" width="8" height="8" rx="1" fill="currentColor" />
                        <rect x="13" y="3" width="8" height="8" rx="1" fill="currentColor" opacity="0.9" />
                        <rect x="3" y="13" width="8" height="8" rx="1" fill="currentColor" opacity="0.7" />
                        <rect x="13" y="13" width="8" height="8" rx="1" fill="currentColor" opacity="0.5" />
                    </svg>
                    Dashboard
                </a>
            </li>

            <li class="nav-item" style="margin: 8px 0;">
                <details class="nav-parent" open style="padding: 0 15px;">
                    <summary class="sidebar-summary">
                        <span class="summary-left">
                            <svg class="sidebar-icon" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M9 6h11M9 12h11M9 18h11" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" fill="none" /><circle cx="4.5" cy="6" r="1" fill="currentColor" /><circle cx="4.5" cy="12" r="1" fill="currentColor" /><circle cx="4.5" cy="18" r="1" fill="currentColor" /></svg>
                            <span class="summary-text">General Tasking</span>
                        </span>
                        <span class="summary-chevron" aria-hidden="true"></span>
                    </summary>
                    <ul style="list-style:none; padding-left: 14px; margin:8px 0 12px 0;">
                        <li style="margin:6px 0;">
                            <a href="{{ route('generalTask') }}" class="nav-link {{ Request::routeIs('generalTask') ? 'active' : '' }} sidebar-child" style="display:block; padding:8px 16px; border-radius:6px; text-decoration:none; color:#374151;">
                                <svg class="sidebar-child-icon" viewBox="0 0 24 24" width="16" height="16" xmlns="http://www.w3.org/2000/svg"><rect x="3" y="5" width="18" height="14" rx="2" fill="none" stroke="currentColor" stroke-width="1.4"/><path d="M8 9h8M8 13h8" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round" fill="none"/></svg>
                                Task Assignments
                            </a>
                        </li>
                        <li style="margin:6px 0;">
                            <a href="{{ route('generalTask.inspection') }}" class="nav-link {{ Request::routeIs('generalTask.inspection') ? 'active' : '' }} sidebar-child" style="display:block; padding:8px 16px; border-radius:6px; text-decoration:none; color:#374151;">
                                <svg class="sidebar-child-icon" viewBox="0 0 24 24" width="16" height="16" xmlns="http://www.w3.org/2000/svg"><path d="M11 4h10M11 10h10M11 16h10" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round" fill="none"/><circle cx="5" cy="6" r="2" fill="none" stroke="currentColor" stroke-width="1.4"/><path d="M5 8v10" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/><path d="M2 21l3-3 3 3" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round" fill="none"/></svg>
                                Task Evaluation
                            </a>
                        </li>
                    </ul>
                </details>
            </li>

            <li class="nav-item" style="margin: 8px 0;">
                <a href="{{ route('admin.task.history') }}" class="nav-link {{ Request::routeIs('admin.task.history') ? 'active' : '' }}" style="display: flex; align-items: center; padding: 12px 20px; text-decoration: none; color: #374151; border-radius: 8px; margin: 0 15px; transition: all 0.3s ease;">
                    <svg class="sidebar-icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false" role="img" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="12" cy="12" r="9" fill="none" stroke="currentColor" stroke-width="1.6" />
                        <path d="M12 7v6l4 2" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" fill="none" />
                    </svg>
                    Room Checklist History
                </a>
            </li>

            <li class="nav-item" style="margin: 8px 0;">
                <a href="{{ route('damage_reports.index') }}" class="nav-link {{ Request::routeIs('damage_reports.*') ? 'active' : '' }}" style="display: flex; align-items: center; padding: 12px 20px; text-decoration: none; color: #374151; border-radius: 8px; margin: 0 15px; transition: all 0.3s ease;">
                    <svg class="sidebar-icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false" role="img" xmlns="http://www.w3.org/2000/svg">
                        <path d="M3 7h18M6 3h12v4" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" fill="none" />
                        <rect x="4" y="9" width="16" height="12" rx="2" fill="none" stroke="currentColor" stroke-width="1.6" />
                        <path d="M8 13h8M8 17h5" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" />
                    </svg>
                    Maintenance Reports
                </a>
            </li>

        @else
            <!-- Non-educator / default list: keep previous behaviour -->
            <li class="nav-item" style="margin: 8px 0;">
                <a href="{{ route('dashboard') }}" class="nav-link sidebar-link {{ Request::routeIs('dashboard') ? 'active' : '' }}" style="display: flex; align-items: center; padding: 12px 20px; text-decoration: none; color: #374151; border-radius: 8px; margin: 0 15px; transition: all 0.3s ease;">
                    <svg class="sidebar-icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false" role="img" style="vertical-align:middle;" xmlns="http://www.w3.org/2000/svg">
                        <rect x="3" y="3" width="8" height="8" rx="1" fill="currentColor" />
                        <rect x="13" y="3" width="8" height="8" rx="1" fill="currentColor" opacity="0.9" />
                        <rect x="3" y="13" width="8" height="8" rx="1" fill="currentColor" opacity="0.7" />
                        <rect x="13" y="13" width="8" height="8" rx="1" fill="currentColor" opacity="0.5" />
                    </svg>
                    Dashboard
                </a>
            </li>

            
            @if(!(auth()->check() && in_array(optional(auth()->user())->user_role, ['inspector','student'])))
            <li class="nav-item" style="margin: 8px 0;">
                <a href="{{ route('room.management') }}" class="nav-link {{ Request::routeIs('room.management') ? 'active' : '' }}" style="display: flex; align-items: center; padding: 12px 20px; text-decoration: none; color: #374151; border-radius: 8px; margin: 0 15px; transition: all 0.3s ease;">
                    <svg class="sidebar-icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false" role="img" xmlns="http://www.w3.org/2000/svg">
                        <path d="M3 21V8a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v13" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" />
                        <path d="M8 21V12h8v9" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" />
                        <path d="M12 7v4" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                    Manage rooms
                </a>
            </li>

              <li class="nav-item" style="margin: 8px 0;">
                <a href="{{ url('/manage_roomtask') }}" class="nav-link {{ Request::routeIs('manage_roomtask') ? 'active' : '' }}" style="display: flex; align-items: center; padding: 12px 20px; text-decoration: none; color: #374151; border-radius: 8px; margin: 0 15px; transition: all 0.3s ease;">
                    <svg class="sidebar-icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false" role="img" xmlns="http://www.w3.org/2000/svg">
                        <path d="M4 7h16M4 12h10M4 17h6" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" fill="none" />
                        <rect x="16" y="6" width="4" height="4" rx="1" fill="currentColor" opacity="0.9" />
                    </svg>
                    Manage Room Tasks
                </a>
            </li>
            @endif

            <li class="nav-item" style="margin: 8px 0;">
                <a href="{{ route('generalTask') }}" class="nav-link {{ Request::routeIs('generalTask') ? 'active' : '' }}" style="display: flex; align-items: center; padding: 12px 20px; text-decoration: none; color: #374151; border-radius: 8px; margin: 0 15px; transition: all 0.3s ease;">
                    <svg class="sidebar-icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false" role="img" xmlns="http://www.w3.org/2000/svg">
                        <path d="M9 6h11M9 12h11M9 18h11" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" fill="none" />
                        <circle cx="4.5" cy="6" r="1" fill="currentColor" />
                        <circle cx="4.5" cy="12" r="1" fill="currentColor" />
                        <circle cx="4.5" cy="18" r="1" fill="currentColor" />
                    </svg>
                    General Tasks
                </a>
            </li>

            @if(auth()->check() && optional(auth()->user())->user_role === 'inspector')
            <li class="nav-item" style="margin: 8px 0;">
                <a href="{{ route('generalTask.inspection') }}" class="nav-link {{ Request::routeIs('generalTask.inspection') ? 'active' : '' }}" style="display: flex; align-items: center; padding: 12px 20px; text-decoration: none; color: #374151; border-radius: 8px; margin: 0 15px; transition: all 0.3s ease;">
                    <svg class="sidebar-icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false" role="img" xmlns="http://www.w3.org/2000/svg">
                        <path d="M11 4h10M11 10h10M11 16h10" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" fill="none" />
                        <circle cx="5" cy="6" r="2" fill="none" stroke="currentColor" stroke-width="1.4" />
                        <path d="M5 8v10" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" />
                        <path d="M2 21l3-3 3 3" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" fill="none" />
                    </svg>
                    General Task Evaluation
                </a>
            </li>
            @endif
        
            @if(auth()->check() && in_array(optional(auth()->user())->user_role, ['student', 'coordinator']))
            <li class="nav-item" style="margin: 8px 0;">
                <a href="{{ route('my.tasks.simple') }}" class="nav-link {{ Request::routeIs('my.tasks.simple') ? 'active' : '' }}" style="display: flex; align-items: center; padding: 12px 20px; text-decoration: none; color: #374151; border-radius: 8px; margin: 0 15px; transition: all 0.3s ease;">
                    <svg class="sidebar-icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false" role="img" xmlns="http://www.w3.org/2000/svg">
                        <path d="M9 12l2 2 4-4" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" fill="none" />
                        <rect x="3" y="4" width="18" height="16" rx="2" fill="none" stroke="currentColor" stroke-width="1.6" />
                        <path d="M8 8h8M8 16h5" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round" fill="none" />
                    </svg>
                    My General Tasks
                </a>
            </li>
            @endif

            <li class="nav-item" style="margin: 8px 0;">
                <a href="{{ route('admin.task.history') }}" class="nav-link {{ Request::routeIs('admin.task.history') ? 'active' : '' }}" style="display: flex; align-items: center; padding: 12px 20px; text-decoration: none; color: #374151; border-radius: 8px; margin: 0 15px; transition: all 0.3s ease;">
                    <svg class="sidebar-icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false" role="img" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="12" cy="12" r="9" fill="none" stroke="currentColor" stroke-width="1.6" />
                        <path d="M12 7v6l4 2" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" fill="none" />
                    </svg>
                    Room Checklist History
                </a>
            </li>

            @if(auth()->check() && in_array(optional(auth()->user())->user_role, ['educator','inspector']))
            <li class="nav-item" style="margin: 8px 0;">
                <a href="{{ route('damage_reports.index') }}" class="nav-link {{ Request::routeIs('damage_reports.*') ? 'active' : '' }}" style="display: flex; align-items: center; padding: 12px 20px; text-decoration: none; color: #374151; border-radius: 8px; margin: 0 15px; transition: all 0.3s ease;">
                    <svg class="sidebar-icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false" role="img" xmlns="http://www.w3.org/2000/svg">
                        <path d="M3 7h18M6 3h12v4" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" fill="none" />
                        <rect x="4" y="9" width="16" height="12" rx="2" fill="none" stroke="currentColor" stroke-width="1.6" />
                        <path d="M8 13h8M8 17h5" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" />
                    </svg>
                    Maintenance Reports
                </a>
            </li>
            @endif

            @if(!(auth()->check() && in_array(optional(auth()->user())->user_role, ['inspector','student'])))
            <li class="nav-item" style="margin: 8px 0;">
                <a href="{{ route('admin.submitted.task.validation') }}" class="nav-link {{ Request::routeIs('admin.submitted.task.validation') ? 'active' : '' }}" style="display: flex; align-items: center; padding: 12px 20px; text-decoration: none; color: #374151; border-radius: 8px; margin: 0 15px; transition: all 0.3s ease;">
                    <svg class="sidebar-icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false" role="img" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8z" fill="none" stroke="currentColor" stroke-width="1.6" />
                        <path d="M4 20v-1a4 4 0 0 1 4-4h8a4 4 0 0 1 4 4v1" fill="none" stroke="currentColor" stroke-width="1.6" />
                        <path d="M9 14l2 2 4-4" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" fill="none" />
                    </svg>
                    Student Reports
                </a>
            </li>
            @endif

        @endif
        </ul>
    </div>
</nav>

<style>
/* Unified educator sidebar styles (matches roomtask look & spacing) */
:root {
    --sidebar-active-bg: #e0f2fe;
    --sidebar-hover-bg: #f3f4f6;
    --sidebar-text: #374151;
    --sidebar-active-text: #1e40af;
    --sidebar-icon: #4b5563;
    --sidebar-active-icon: #1e40af;
    --sidebar-border-radius: 8px;
    --sidebar-item-padding: 12px 16px;
    --sidebar-child-padding: 8px 16px 8px 42px;
}

.sidebar-container {
    --sidebar-width: 300px;
    width: var(--sidebar-width); /* increased width to keep items on one line */
    background: #ffffff;
    color: #374151;
    padding: 0; /* inner div handles padding */
    /* make position strict so it can't be moved by touch/drag */
    position: fixed !important;
     /* position the sidebar immediately below the header using the shared header height variable
        Keep the sidebar fallback header height at the original value so sidebar positioning/scrolling
        behavior remains unchanged. */
     top: var(--header-height, 96px);
     left: 0;
     /* occupy the remaining viewport height below the header */
     height: calc(100vh - var(--header-height, 96px));
    /* only allow vertical scrolling inside the sidebar; block horizontal movement/swipe */
    overflow-y: auto;
    overflow-x: hidden;
    touch-action: pan-y; /* allow vertical pan, block horizontal gestures */
    overscroll-behavior-x: none; /* prevent scroll chaining horizontally */
    z-index: 90; /* keep header above sidebar */
    /* Match header accent color with a subtle right border */
    border-right: 3px solid #22BBEA;
}

/* Main container padding */
.sidebar-container > div {
    padding: 12px 8px;
}

/* Consistent spacing for all list items */
.nav-item {
    margin: 4px 0 !important;
    width: 100%;
}

/* Ensure consistent padding for all sidebar items */
.sidebar-link,
.sidebar-summary,
.nav-parent > summary {
    padding: 12px 16px !important;
    margin: 0 8px !important;
    width: calc(100% - 16px);
    box-sizing: border-box;
}

/* Ensure consistent alignment for all items */
.nav-parent {
    padding: 0 !important;
    margin: 0 !important;
    width: 100%;
}

/* Ensure child items have consistent indentation */
.nav-parent ul {
    padding-left: 12px;
    margin: 4px 0 8px 0;
}

/* Make sure the active state is clearly visible */
.nav-link.active,
.nav-link.router-link-active,
.nav-link[aria-current="page"] {
    background-color: var(--sidebar-active-bg) !important;
    color: var(--sidebar-active-text) !important;
    font-weight: 600 !important;
}

.sidebar-container,
.sidebar-container * {
    font-family: 'Poppins', system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif !important;
    -webkit-user-drag: none; /* prevent dragging of elements inside the sidebar on WebKit */
    user-drag: none;
}

.sidebar-container .nav-link {
    color: var(--sidebar-text);
    font-weight: 500;
    font-size: 0.95rem;
    transition: all 0.2s ease;
    position: relative;
    white-space: nowrap; /* keep text on one line */
    overflow: hidden;
    text-overflow: ellipsis; /* show ellipsis if too long */
}

/* ensure anchors can shrink correctly and always leave space for icons/chevrons */
.sidebar-container .nav-link {
    display: flex;
    align-items: center;
    gap: 12px;
    min-width: 0;
    padding: var(--sidebar-item-padding);
    margin: 0 8px;
    border-radius: var(--sidebar-border-radius);
}

/* Active and hover states */
.sidebar-container .nav-link:hover,
.sidebar-container .nav-link:focus {
    background-color: var(--sidebar-hover-bg);
    color: var(--sidebar-active-text);
}

.sidebar-container .nav-link.active,
.sidebar-container .nav-link.router-link-active,
.sidebar-container .nav-link[aria-current="page"] {
    background-color: var(--sidebar-active-bg);
    color: var(--sidebar-active-text);
    font-weight: 600;
}

.sidebar-container .nav-link.active .sidebar-icon,
.sidebar-container .nav-link.router-link-active .sidebar-icon,
.sidebar-container .nav-link[aria-current="page"] .sidebar-icon {
    color: var(--sidebar-active-icon);
}

/* icons should never shrink and be fully visible */
.sidebar-container .sidebar-icon,
.sidebar-container .sidebar-child-icon {
    flex: 0 0 auto;
}

/* ensure summary headings and child links also stay on one line */
.sidebar-summary,
.nav-parent ul .nav-link,
.sidebar-container .summary-text {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* make the summary layout reserve space for the chevron and ensure left side (icon+text) can shrink */
.sidebar-summary {
    padding-right: 36px; /* leave room for the chevron at the right */
}
.sidebar-summary .summary-left {
    flex: 1 1 auto;
    min-width: 0; /* allows the summary text to ellipsis correctly inside flex */
}
.summary-chevron {
    flex: 0 0 auto;
    margin-left: 8px;
}

/* Child links styling */
.nav-parent ul .nav-link {
    min-width: 0;
    padding: var(--sidebar-child-padding);
    margin: 2px 8px;
    font-size: 0.9rem;
}

/* Dropdown indicator */
.summary-chevron {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 20px;
    height: 20px;
    margin-left: auto; /* Push to the far right */
    transition: transform 0.2s ease;
    position: relative;
    flex-shrink: 0;
}

/* Add chevron icon using CSS */
.summary-chevron::after {
    content: '';
    display: block;
    width: 6px;
    height: 6px;
    border-right: 2px solid currentColor;
    border-bottom: 2px solid currentColor;
    transform: rotate(45deg);
    margin-top: -3px;
    opacity: 0.7;
    transition: transform 0.2s ease, opacity 0.2s ease;
}

.nav-parent[open] .summary-chevron::after {
    transform: rotate(-135deg);
    margin-top: 3px;
}

/* Ensure consistent dropdown behavior */
.nav-parent > summary {
    list-style: none;
    cursor: pointer;
}

.nav-parent > summary::-webkit-details-marker {
    display: none;
}

/* Make sure the summary has the same hover/active states as other links */
.sidebar-summary {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px 16px !important;
    margin: 0 8px !important;
    border-radius: var(--sidebar-border-radius);
    cursor: pointer;
    transition: all 0.2s ease;
    gap: 8px;
    width: calc(100% - 16px);
    box-sizing: border-box;
}

.sidebar-summary:hover,
.sidebar-summary:focus {
    background-color: var(--sidebar-hover-bg);
    color: var(--sidebar-active-text);
}

/* Ensure the chevron is visible on hover/focus */
.sidebar-summary:hover .summary-chevron::after,
.sidebar-summary:focus .summary-chevron::after {
    opacity: 1;
}

/* Ensure icons are properly aligned */
.sidebar-icon {
    flex-shrink: 0;
    width: 24px;
    height: 24px;
    color: var(--sidebar-icon);
}

.sidebar-child-icon {
    flex-shrink: 0;
    width: 18px;
    height: 18px;
    color: var(--sidebar-icon);
}

/* Active state for parent when child is active */
.nav-parent:has(> ul > li > .nav-link.active) > .sidebar-summary {
    color: var(--sidebar-active-text);
    font-weight: 600;
}

.nav-parent:has(> ul > li > .nav-link.active) > .sidebar-summary .sidebar-icon {
    color: var(--sidebar-active-icon);
}

.sidebar-container .nav-link img { vertical-align: middle; width:26px; height:26px; margin-right:14px; }

.sidebar-container .nav-link .sidebar-icon,
.sidebar .nav-link .sidebar-icon,
#sidebar .nav-link .sidebar-icon {
    vertical-align: middle; width:26px; height:26px; margin-right:14px; color: #374151;
}

.sidebar-container .nav-link:hover {
    background: #f1f5f9;
    color: #111827;
}

.sidebar-container .nav-link.active,
.sidebar-container .nav-link.active:hover {
    background: #e0f2fe;
    color: #0f172a;
    font-weight: 400; /* keep active state from appearing bold */
}

/* Ensure page content is not hidden behind the fixed sidebar. Use a page-level class so only layouts that include the fixed sidebar are shifted. */
/* Ensure main content is pushed below the fixed header and to the right of the sidebar */
.main-content { 
    margin-left: var(--sidebar-width, 340px);
    /* Keep sidebar top/height fallback unchanged. For pages that don't define --header-height, remove the extra space so content sits immediately under the header. */
    margin-top: var(--header-height, 30px);
}

/* Layout offset is controlled by page-level styles (roomtask.css / header partial). */
/* Removed automatic margin-left here to avoid duplicating offsets and creating large gaps. */

@media (max-width: 1024px) {
    .sidebar-container { 
        position: static; 
        width: 100%; 
        height: auto; 
        padding: 0;
    }
    
    .main-content { 
        margin-left: 0; 
    }
    
    /* Ensure dropdowns work properly on mobile */
    .nav-parent {
        position: relative;
    }
    
    .nav-parent ul {
        position: static;
        box-shadow: none;
    }
    
    /* Make sure links are tappable on mobile */
    .nav-link,
    .sidebar-summary {
        padding: 14px 16px !important;
    }
}
/* Styles for educator grouped parents (details/summary) */
.nav-parent summary { list-style: none; outline: none; }
.nav-parent summary::-webkit-details-marker { display: none; }
.sidebar-summary {
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    padding: 12px 12px; /* align left/right padding with anchors */
    padding-left: 12px !important; /* ensure it matches .nav-link left padding */
    padding-right: 12px !important;
    border-radius: 10px;
    /* Parent heading visually stronger */
    font-weight: 700;
    color: #111827;
    font-size: 0.95rem;
    transition: background 0.18s ease, color 0.18s ease;
}
.summary-left { display:flex; align-items:center; gap:10px; flex-wrap:nowrap; }
.summary-text { white-space:nowrap; font-size:0.95rem; }
.summary-chevron {
    display:inline-flex;
    width:18px;
    height:18px;
    color:#9ca3af;
    justify-content:center;
    align-items:center;
    /* smooth rotation when toggling */
    transition: transform 0.18s ease, color 0.18s ease;
}
.summary-chevron::before {
    /* right-pointing triangle that rotates when opened */
    content:'\25B6';
    font-size:0.75rem;
    transform-origin: center;
    display:inline-block;
}
.nav-parent[open] .summary-chevron::before {
    transform: rotate(90deg);
    color: #6b7280;
}
.nav-parent[open] .sidebar-summary { background:#f1f5f9; }
.nav-parent ul { padding-left: 8px; }
.nav-parent ul .nav-link {
    padding-left: 6px;
    /* child items should not be bold so parent stands out */
    font-weight: 400;
    font-size: 0.9rem;
    display:flex;
    align-items:center;
    gap:8px;
}
.sidebar-child-icon { width:16px; height:16px; flex-shrink:0; color: #374151; }
.sidebar-child { color: #374151; font-weight: 400; }

/* Tweak child icon alignment and spacing to match parents */
.sidebar-child-icon { margin-right: 10px; }
</style>
