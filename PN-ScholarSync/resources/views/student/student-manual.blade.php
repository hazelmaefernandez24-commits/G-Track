@extends('layouts.student')

@section('title', 'Student Violation Manual')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/student/student-manual.css') }}">
    <style>
        .category-section table { width: 100% !important; table-layout: fixed !important; }
        .category-section h4 { font-size: 1.2rem !important; font-weight: 600 !important; line-height: 1.4 !important; }
        .table th { font-size: 1rem !important; }
        .table td { font-size: 1rem !important; vertical-align: middle !important; word-wrap: break-word !important; white-space: normal !important; }
        .category-section table th:first-child, .category-section table td:first-child { width: 8% !important; text-align: center !important; }
        .category-section table th:nth-child(2), .category-section table td:nth-child(2) { width: 62% !important; }
        .category-section table th:nth-child(3), .category-section table td:nth-child(3) { width: 30% !important; }
        /* Search UI styles to mirror educator */
        .search-container { background-color: #f8f9fa; padding: 20px; border-radius: 8px; border: 1px solid #dee2e6; }
        .search-highlight { background-color: #fff3cd; color: #856404; padding: 2px 4px; border-radius: 3px; font-weight: 500; }
        .input-group-text { background-color: #e9ecef; border-color: #ced4da; }
        #violationSearch { border-color: #ced4da; }
        #violationSearch:focus { border-color: #80bdff; box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25); }
        #searchResults { font-style: italic; }
        .category-section { transition: opacity 0.3s ease; }
        .category-section.hidden { opacity: 0.5; }
        .sortable { cursor: pointer; }
    </style>
@endsection

@section('content')
    <div class="container manual-full-width">
        <div class="main-heading">
            <h1 class="fw-bold mb-0 manual-heading-text">Student Code of Conduct</h1>
        </div>
        <h2>Empowering Responsible Center Life Through Awareness and Discipline.</h2>
        <p>Welcome, students! This code of conduct helps you understand the rules and expectations while living at the center. Staying informed is the first step to success and harmony!</p>

        <!-- Filters (match educator UI, no edit features) -->
        <div class="search-container mb-4">
            <div class="row">
                <div class="col-md-4">
                    <label for="categorySelect" class="form-label small fw-bold">Search by Category</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-tags"></i></span>
                        <select class="form-select" id="categorySelect">
                            <option value="">All Categories</option>
                            @php
                                $categoryOptions = collect($categories)->pluck('category_name')->unique()->sort()->values();
                            @endphp
                            @foreach($categoryOptions as $catName)
                                <option value="{{ $catName }}">{{ $catName }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <label for="violationNameSearch" class="form-label small fw-bold">Search by Violation Name</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-file-alt"></i></span>
                        <input type="text" class="form-control" id="violationNameSearch" placeholder="Enter violation name..." autocomplete="off">
                    </div>
                </div>
                <div class="col-md-4">
                    <label for="severitySearch" class="form-label small fw-bold">Search by Severity</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-exclamation-triangle"></i></span>
                        <select class="form-select" id="severitySearch">
                            <option value="">All Severities</option>
                            <option value="low">Low</option>
                            <option value="medium">Medium</option>
                            <option value="high">High</option>
                            <option value="very high">Very High</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-md-6">
                    <button type="button" class="btn btn-outline-secondary btn-sm" id="clearAllSearch">
                        <i class="fas fa-times"></i> Clear All Filters
                    </button>
                    <small class="text-muted ms-2" id="searchResults"></small>
                </div>
            </div>
        </div>


        <div class="violation-table">
            <div class="violation-header">
                <h3>Violation Categories and Penalties</h3>
            </div>
            @foreach($categories as $index => $category)
            <div class="category-section">
                <h4>{{ $index + 1 }}. {{ $category->category_name }}</h4>
                <table class="table table-bordered category-violation-table" data-category="{{ $category->category_name }}">
                    <thead>
                        <tr>
                            <th style="width: 8%; text-align: center;">#</th>
                            <th style="width: 65%; text-align: left; cursor:pointer;" class="sortable" data-sort="violation_name">Violation Name <span class="sort-icon" style="font-size:0.9em;">&#8597;</span></th>
                            <th style="width: 25%; text-align: center; cursor:pointer;" class="sortable" data-sort="severity">Severity <span class="sort-icon" style="font-size:0.9em;">&#8597;</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($category->violationTypes->unique('violation_name') as $typeIndex => $type)
                        <tr>
                            <td>{{ $index + 1 }}.{{ $typeIndex + 1 }}</td>
                            <td class="violation-name">{{ $type->violation_name }}</td>
                            <td class="violation-severity">
                                @php
                                    $severityName = '';
                                    if ($type->severityRelation) {
                                        $severityName = strtolower($type->severityRelation->severity_name);
                                    } else {
                                        // Fallback: determine severity based on default_penalty
                                        switch ($type->default_penalty) {
                                            case 'VW':
                                                $severityName = 'low';
                                                break;
                                            case 'WW':
                                                $severityName = 'medium';
                                                break;
                                            case 'Pro':
                                                $severityName = 'high';
                                                break;
                                            case 'T':
                                                $severityName = 'very high';
                                                break;
                                            default:
                                                $severityName = 'medium';
                                        }
                                    }
                                @endphp
                                @switch($severityName)
                                    @case('low')
                                        Low
                                        @break
                                    @case('medium')
                                        Medium
                                        @break
                                    @case('high')
                                        High
                                        @break
                                    @case('very high')
                                        Very High
                                        @break
                                    @default
                                        Medium
                                @endswitch
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endforeach
        </div>

        <div class="penalty-system-explanation mt-5">
            <h3 class="text-sm">Penalty Rules Based on Infraction Count and Severity</h3>
            <div class="table-responsive">
                <table class="table table-bordered table-sm">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 20%;" class="text-sm">Infraction Count</th>
                            <th style="width: 20%;" class="text-sm"><span class="badge" color: #000;">🟡 Low</span></th>
                            <th style="width: 20%;" class="text-sm"><span class="badge" color: #fff;">🌸 Medium</span></th>
                            <th style="width: 20%;" class="text-sm"><span class="badge" color: #fff;">🟠 High</span></th>
                            <th style="width: 20%;" class="text-sm"><span class="badge" color: #fff;">🔴 Very High</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="text-sm"><strong>1st Infraction</strong></td>
                            <td class="text-sm">Verbal Warning (VW)</td>
                            <td class="text-sm">Written Warning (WW)</td>
                            <td class="text-sm">Probation (P)</td>
                            <td class="text-sm">Termination (T)</td>
                        </tr>
                        <tr>
                            <td class="text-sm"><strong>2nd Infraction</strong></td>
                            <td class="text-sm">Written Warning (WW)</td>
                            <td class="text-sm">Probation (P)</td>
                            <td class="text-sm">Termination (T)</td>
                            <td class="text-sm"></td>
                        </tr>
                        <tr>
                            <td class="text-sm"><strong>3rd Infraction</strong></td>
                            <td class="text-sm">Probation (P)</td>
                            <td class="text-sm">Termination (T)</td>
                            <td class="text-sm"></td>
                            <td class="text-sm"></td>
                        </tr>
                        <tr>
                            <td class="text-sm"><strong>4th Infraction</strong></td>
                            <td class="text-sm">Termination (T)</td>
                            <td class="text-sm"></td>
                            <td class="text-sm"></td>
                            <td class="text-sm"></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="important-reminders mt-4">
                <div class="bg-light p-4 rounded-lg border">
                    <h3 class="text-sm mb-3">🔑 Important Things to Remember About Penalties</h3>
                    <table class="table table-bordered table-sm mb-0">
                        <tbody>
                            <tr>
                                <td style="width: 30%;" class="align-middle bg-white"><h4 class="mb-0 small">📊 Penalties are based on the violation's severity and infraction number.</h4></td>
                                <td class="align-middle text-sm bg-white">Each penalty depends on how serious the violation is and how many times the student has violated overall.</td>
                            </tr>
                            <tr>
                                <td class="align-middle bg-white"><h4 class="mb-0 small">⏳ The system tracks a total of 4 infractions only.</h4></td>
                                <td class="align-middle text-sm bg-white">Once a student commits 4 total violations (regardless of severity), they are automatically terminated — unless terminated earlier by severity.</td>
                            </tr>
                            <tr>
                                <td class="align-middle bg-white"><h4 class="mb-0 small">⚠️ Serious violations can lead to termination immediately.</h4></td>
                                <td class="align-middle text-sm bg-white">For example, a Very High violation causes immediate termination, even if it's the student's 1st or 2nd infraction.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Add toast container at the top of the body -->
    <div class="toast-container" id="toastContainer"></div>

    <script>
        // Toast function stays as is
        function showSuccessToast(message) {
            const toastContainer = document.getElementById('toastContainer');
            const toast = document.createElement('div');
            toast.className = 'toast success';
            let icon = 'fas fa-check-circle';
            let customMessage = message;
            toast.innerHTML = `<i class="${icon}"></i><div class="toast-message">${customMessage}</div><button class="toast-close" onclick="this.parentElement.remove()"><i class="fas fa-times"></i></button>`;
            toastContainer.appendChild(toast);
            setTimeout(() => { toast.style.animation = 'fadeOut 0.3s ease-out forwards'; setTimeout(() => { if (toast.parentNode) { toast.parentNode.removeChild(toast); } }, 300); }, 2500);
        }
    </script>

    <!-- jQuery for filtering/sorting behavior (page-local) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            let originalViolationData = [];

            function storeOriginalData() {
                $('.category-section').each(function() {
                    const categoryElement = $(this);
                    const categoryName = categoryElement.find('h4').text().trim();
                    const violations = [];
                    categoryElement.find('tbody tr').each(function() {
                        const row = $(this);
                        const violationName = row.find('.violation-name').text().trim();
                        const severity = row.find('.violation-severity').text().trim();
                        violations.push({ element: row.clone(), violationName: violationName.toLowerCase(), severity: severity.toLowerCase(), categoryName: categoryName.toLowerCase() });
                    });
                    originalViolationData.push({ categoryElement: categoryElement, categoryName: categoryName.toLowerCase(), violations: violations });
                });
            }

            function escapeRegex(string) { return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'); }

            function highlightSearchTerms(element, searchTerms) {
                element.find('td').each(function(index) {
                    const cell = $(this); let text = cell.text();
                    if (index === 1 && searchTerms.violationName !== '') { const regex = new RegExp(`(${escapeRegex(searchTerms.violationName)})`, 'gi'); text = text.replace(regex, '<mark class="search-highlight">$1</mark>'); }
                    if (index === 2 && searchTerms.severity !== '') { const regex = new RegExp(`(${escapeRegex(searchTerms.severity)})`, 'gi'); text = text.replace(regex, '<mark class="search-highlight">$1</mark>'); }
                    cell.html(text);
                });
                return element;
            }

            function performSearch() {
                const violationNameTerm = $('#violationNameSearch').val().toLowerCase().trim();
                const categoryTerm = ($('#categorySelect').val() || '').toLowerCase().trim();
                const severityTerm = $('#severitySearch').val().toLowerCase().trim();
                let totalResults = 0;
                if (violationNameTerm === '' && categoryTerm === '' && severityTerm === '') {
                    $('.category-section').show();
                    $('.category-section tbody tr').show();
                    $('#searchResults').text('');
                    return;
                }
                originalViolationData.forEach(function(categoryData) {
                    let categoryHasResults = false; let categoryResults = 0;
                    categoryData.categoryElement.find('tbody').empty();
                    categoryData.violations.forEach(function(violation) {
                        let matches = true;
                        if (violationNameTerm !== '' && !violation.violationName.includes(violationNameTerm)) matches = false;
                        if (categoryTerm !== '' && !violation.categoryName.includes(categoryTerm)) matches = false;
                        if (severityTerm !== '' && !violation.severity.includes(severityTerm)) matches = false;
                        if (matches) {
                            const highlightedElement = highlightSearchTerms(violation.element.clone(), { violationName: violationNameTerm, category: categoryTerm, severity: severityTerm });
                            categoryData.categoryElement.find('tbody').append(highlightedElement);
                            categoryHasResults = true; categoryResults++;
                        }
                    });
                    if (categoryHasResults) { categoryData.categoryElement.show(); totalResults += categoryResults; }
                    else { categoryData.categoryElement.hide(); }
                });
                $('#searchResults').text(totalResults === 0 ? 'No results found' : `${totalResults} violation${totalResults !== 1 ? 's' : ''} found`);
            }

            // Sorting
            let sortStates = {};
            $('.category-violation-table .sortable').on('click', function() {
                const $header = $(this); const $table = $header.closest('table');
                const category = $table.data('category'); const sortKey = $header.data('sort'); const $icon = $header.find('.sort-icon');
                if (!sortStates[category]) sortStates[category] = {}; if (sortStates[category].key === sortKey) sortStates[category].asc = !sortStates[category].asc; else { sortStates[category].key = sortKey; sortStates[category].asc = true; }
                $table.find('.sort-icon').css('color', ''); $icon.css('color', '#3498db');
                const $rows = $table.find('tbody tr').get();
                $rows.sort(function(a, b) {
                    let valA, valB; if (sortKey === 'violation_name') { valA = $(a).find('.violation-name').text().toLowerCase(); valB = $(b).find('.violation-name').text().toLowerCase(); } else if (sortKey === 'severity') { valA = $(a).find('.violation-severity').text().toLowerCase(); valB = $(b).find('.violation-severity').text().toLowerCase(); }
                    if (valA < valB) return sortStates[category].asc ? -1 : 1; if (valA > valB) return sortStates[category].asc ? 1 : -1; return 0;
                });
                $.each($rows, function(idx, row) { $table.find('tbody').append(row); });
            });

            // Initialize
            storeOriginalData();
            $('#violationNameSearch').on('input', performSearch);
            $('#categorySelect').on('change', performSearch);
            $('#severitySearch').on('change', performSearch);
            $('#clearAllSearch').click(function() { $('#violationNameSearch').val(''); $('#categorySelect').val(''); $('#severitySearch').val(''); performSearch(); });
            $('#violationNameSearch').on('keypress', function(e) { if (e.which === 13) { e.preventDefault(); performSearch(); } });
        });
    </script>
@endsection