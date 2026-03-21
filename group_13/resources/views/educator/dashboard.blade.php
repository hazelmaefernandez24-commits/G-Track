@extends('layouts.educator_layout')

@section('content')

<link rel="stylesheet" href="{{ asset('css/training/dashboard.css') }}">

<div class="dashboard-container" style="padding: 20px;">

    <h1 style="margin-bottom: 20px; color: #333; font-weight: 300;">Dashboard</h1>
    <hr>

    <!-- Stats Grid -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 40px;">
        <div style="background: beige; padding: 20px; border-radius: 8px; text-align: center; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
            <p class="stat-number">{{ $schoolsCount }}</p>
            <p>Total No. of Schools</p>
        </div>

        <div style="background: beige; padding: 20px; border-radius: 8px; text-align: center; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
            <p class="stat-number">{{ $classesCount }}</p>
            <p>Total No. of Classes</p>
        </div>

        <div style="background: beige; padding: 20px; border-radius: 8px; text-align: center; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
            <p class="stat-number">{{ $studentsCount }}</p>
            <p>Total No. of Students</p>
        </div>
    </div>

    <h1 style="font-weight:300; margin-bottom: 20px; color: #333;">Student by Class Analytics</h1>
    <hr>
    <!-- Charts -->
    <div style="display: flex; flex-direction: column; gap: 30px;">
        <!-- Batch Chart with Pagination -->
        <div style="background: #fff; width: 95%; border-radius: 12px; padding: 30px 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.10); max-width: 900px; margin: 0 auto 30px auto; display: flex; flex-direction: column; align-items: center;">
            <h3 style="text-align: center; margin: 0 0 24px 0; color: #333; font-size: 1.5em; font-weight: 500; width: 100%;">Students by Class and Sex</h3>
            <div style="height: 340px; width: 100%; max-width: 700px; display: flex; align-items: center; justify-content: center;">
                <canvas id="batchChart"></canvas>
            </div>
            <div style="margin-top: 15px; text-align: center; color: #666; font-size: 12px;">
                <span id="batchRangeInfo">Showing classes 1-5</span>
            </div>
            <div style="display: flex; align-items: center; justify-content: center; gap: 10px; margin-top: 15px;">
                <button id="prevBatchPage" onclick="changeBatchPage(-1)" style="background: #22bbea; color: white; border: none; border-radius: 6px; padding: 8px 12px; cursor: pointer; font-size: 12px; display: flex; align-items: center; gap: 4px;" disabled>
                    <i class="fas fa-chevron-left"></i> Prev
                </button>
                <span id="batchPageInfo" style="font-size: 12px; color: #666; margin: 0 10px;">Page 1 of 1</span>
                <button id="nextBatchPage" onclick="changeBatchPage(1)" style="background: #22bbea; color: white; border: none; border-radius: 6px; padding: 8px 12px; cursor: pointer; font-size: 12px; display: flex; align-items: center; gap: 4px;">
                    Next <i class="fas fa-chevron-right"></i>
                </button>
            </div>
        </div>



    <h1 style="font-weight:300;  color: #333;">Sex by Class Analytics</h1>
    <hr style="margin-top: -20px;">
    <div class="options">
    <div style="background: #fff; width: 95%; border-radius: 12px; padding: 30px 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.10); max-width: 600px; margin: 30px auto 0 auto; display: flex; flex-direction: column; align-items: center;">
        <div style="width:100%; display:flex; flex-direction:column; align-items:center; margin-bottom: 10px;">
            <select id="batchFilter" style="width:135px; padding: 10px 14px; border-radius: 8px; border: 1px solid #ddd; background: #f8f9fa; font-size:1em; color:#333; outline:none; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
                <option value="all">All Batches</option>
                @foreach($batchCounts->keys() as $batch)
                    <option value="{{ $batch }}">Batch {{ $batch }}</option>
                @endforeach
            </select>
        </div>
        <h3 style="color: #333; margin-bottom: 20px; font-size: 1.5em; font-weight: 500; text-align: center;">Sex Distribution <span id='pieBatchTitle' style='font-size:0.7em; color:#888;'></span></h3>
        <div style="height: 320px; width: 320px; display: flex; align-items: center; justify-content: center;">
            <canvas id="genderBarChart"></canvas>
        </div>
        <div id="pieLegend" style="display: flex; gap: 20px; justify-content: center; margin-top: 20px;">
            <div style="display: flex; align-items: center; gap: 6px;"><span style="display:inline-block;width:18px;height:18px;background:#22bbea;border-radius:50%;"></span> <span style="color:#333;">Male</span></div>
            <div style="display: flex; align-items: center; gap: 6px;"><span style="display:inline-block;width:18px;height:18px;background:#ff9933;border-radius:50%;"></span> <span style="color:#333;">Female</span></div>
        </div>
    </div>
    </div>

    <!-- Recent Items Section -->
    <h1 style="font-weight:300; color: #333;">Recent Activity from training</h1>
    <hr>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-bottom: 40px;">
        <!-- Recent Students -->
        <div style="background: beige; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
            <h3 style="color: #333; margin-bottom: 15px;">Recent Students</h3>
            <div class="recent-list">
                @foreach($recentStudents as $student)
                    <div class="recent-item">
                        <i class="fas fa-user"></i>
                        <div>
                            <strong>{{ $student->user_fname }} {{ $student->user_lname }}</strong>
                            <small>Batch {{ $student->studentDetail->batch ?? 'N/A' }}</small>
                        </div>
                        <span class="recent-date">{{ $student->created_at->diffForHumans() }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Recent Schools -->
        <div style="background: beige; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
            <h3 style="color: #333; margin-bottom: 15px;">Recent Schools</h3>
            <div class="recent-list">
                @foreach($recentSchools as $school)
                    <div class="recent-item">
                        <i class="fas fa-school"></i>
                        <div>
                            <strong>{{ $school->name }}</strong>
                            <small>{{ $school->department }} - {{ $school->course }}</small>
                        </div>
                        <span class="recent-date">{{ $school->created_at->diffForHumans() }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Recent Classes -->
        <div style="background: beige; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
            <h3 style="color: #333; margin-bottom: 15px;">Recent Classes</h3>
            <div class="recent-list">
                @foreach($recentClasses as $class)
                    <div class="recent-item">
                        <i class="fas fa-chalkboard"></i>
                        <div>
                            <strong>{{ $class->class_name }}</strong>
                            <small>{{ $class->school->name ?? 'N/A' }}</small>
                        </div>
                        <span class="recent-date">{{ $class->created_at->diffForHumans() }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

</div>






@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // --- Students by Batch Chart with Pagination ---
    const batchCtx = document.getElementById('batchChart');
    if (batchCtx) {
        const allBatches = {!! json_encode($batchCounts->keys()) !!};
        const studentsByGenderByBatch = {!! json_encode($studentsByGenderByBatch) !!};

        // Pagination variables
        let currentBatchPage = 1;
        const batchesPerPage = 5;
        const totalBatchPages = Math.ceil(allBatches.length / batchesPerPage);
        let batchChart = null;

        // Function to get paginated data
        function getPaginatedBatchData(page) {
            const startIndex = (page - 1) * batchesPerPage;
            const endIndex = startIndex + batchesPerPage;
            const paginatedBatches = allBatches.slice(startIndex, endIndex);

            const maleCounts = paginatedBatches.map(batch => studentsByGenderByBatch[batch]?.male ?? 0);
            const femaleCounts = paginatedBatches.map(batch => studentsByGenderByBatch[batch]?.female ?? 0);

            return {
                labels: paginatedBatches,
                maleCounts: maleCounts,
                femaleCounts: femaleCounts,
                startIndex: startIndex,
                endIndex: Math.min(endIndex, allBatches.length)
            };
        }

        // Function to update chart
        function updateBatchChart(page) {
            const data = getPaginatedBatchData(page);

            if (batchChart) {
                batchChart.destroy();
            }

            batchChart = new Chart(batchCtx, {
                type: 'bar',
                data: {
                    labels: data.labels,
                    datasets: [
                        {
                            label: 'Male',
                            data: data.maleCounts,
                            backgroundColor: '#22bbea',
                        },
                        {
                            label: 'Female',
                            data: data.femaleCounts,
                            backgroundColor: '#ff9933',
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: true },
                        title: {
                            display: true,
                            font: { size: 16 }
                        }
                    },
                    scales: {
                        x: { stacked: false, title: { display: true, text: 'Class' } },
                        y: { beginAtZero: true, max: 70, title: { display: true, text: 'Number of Students' } }
                    },
                    layout: { padding: { left: 10, right: 10 } }
                }
            });

            // Update pagination info
            document.getElementById('batchPageInfo').textContent = `Page ${page} of ${totalBatchPages}`;
            document.getElementById('batchRangeInfo').textContent = `Showing classes ${data.startIndex + 1}-${data.endIndex} of ${allBatches.length}`;

            // Update button states
            document.getElementById('prevBatchPage').disabled = page === 1;
            document.getElementById('nextBatchPage').disabled = page === totalBatchPages;

            // Update button styles
            const prevBtn = document.getElementById('prevBatchPage');
            const nextBtn = document.getElementById('nextBatchPage');

            if (page === 1) {
                prevBtn.style.background = '#ccc';
                prevBtn.style.cursor = 'not-allowed';
            } else {
                prevBtn.style.background = '#22bbea';
                prevBtn.style.cursor = 'pointer';
            }

            if (page === totalBatchPages) {
                nextBtn.style.background = '#ccc';
                nextBtn.style.cursor = 'not-allowed';
            } else {
                nextBtn.style.background = '#22bbea';
                nextBtn.style.cursor = 'pointer';
            }
        }

        // Global function for pagination buttons
        window.changeBatchPage = function(direction) {
            const newPage = currentBatchPage + direction;
            if (newPage >= 1 && newPage <= totalBatchPages) {
                currentBatchPage = newPage;
                updateBatchChart(currentBatchPage);
            }
        };

        // Initialize chart
        updateBatchChart(currentBatchPage);

        // Add hover effects to pagination buttons
        const prevBtn = document.getElementById('prevBatchPage');
        const nextBtn = document.getElementById('nextBatchPage');

        [prevBtn, nextBtn].forEach(btn => {
            btn.addEventListener('mouseenter', function() {
                if (!this.disabled) {
                    this.style.background = '#1e9bd1';
                    this.style.transform = 'translateY(-1px)';
                    this.style.boxShadow = '0 2px 8px rgba(34, 187, 234, 0.3)';
                }
            });

            btn.addEventListener('mouseleave', function() {
                if (!this.disabled) {
                    this.style.background = '#22bbea';
                    this.style.transform = 'translateY(0)';
                    this.style.boxShadow = 'none';
                }
            });

            btn.addEventListener('mousedown', function() {
                if (!this.disabled) {
                    this.style.transform = 'translateY(0)';
                }
            });
        });

        // Add keyboard navigation
        document.addEventListener('keydown', function(e) {
            if (e.target.closest('.chart-container') || document.activeElement === document.body) {
                if (e.key === 'ArrowLeft' && currentBatchPage > 1) {
                    e.preventDefault();
                    changeBatchPage(-1);
                } else if (e.key === 'ArrowRight' && currentBatchPage < totalBatchPages) {
                    e.preventDefault();
                    changeBatchPage(1);
                }
            }
        });
    }

    // --- Pie Chart for Sex Distribution (with batch filter) ---
    const genderBarCtx = document.getElementById('genderBarChart');
    const batchFilter = document.getElementById('batchFilter');
    const genderByBatch = {!! json_encode($genderByBatch) !!};

    function renderGenderPieChart(batchValue) {
        let male = 0, female = 0;
        if (batchValue === 'all') {
            Object.values(genderByBatch).forEach(batch => {
                male += batch.male ?? 0;
                female += batch.female ?? 0;
            });
        } else {
            male = genderByBatch[batchValue]?.male ?? 0;
            female = genderByBatch[batchValue]?.female ?? 0;
        }
        if (genderBarCtx) {
            if (window.genderPieChart) {
                window.genderPieChart.destroy();
            }
            window.genderPieChart = new Chart(genderBarCtx, {
                type: 'pie',
                data: {
                    labels: ['Male', 'Female'],
                    datasets: [
                        {
                            data: [male, female],
                            backgroundColor: ['#22bbea', '#ff9933']
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        title: {
                            display: false
                        }
                    }
                }
            });
            // Update the title
            const pieBatchTitle = document.getElementById('pieBatchTitle');
            if (pieBatchTitle) {
                pieBatchTitle.textContent = batchValue === 'all' ? '(All Batches)' : `(Batch ${batchValue})`;
            }
        }
    }

    if (genderBarCtx) {
        renderGenderPieChart('all');
        if (batchFilter) {
            batchFilter.addEventListener('change', function(e) {
                renderGenderPieChart(e.target.value);
            });
        }
    }
});
</script>



@endpush



@endsection

