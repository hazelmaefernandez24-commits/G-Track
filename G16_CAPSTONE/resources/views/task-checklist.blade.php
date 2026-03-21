@extends('layouts.apps')

@section('content')
<div class="container-fluid p-4">
  <div class="row justify-content-center">
    <div class="col-12">
      <div class="card shadow-sm">
        <div class="card-header bg-light border-0">
          <h4 class="mb-0 text-center text-primary">Task Checklist</h4>
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-bordered mb-0" style="font-size: 14px;">
              <thead class="table-light">
                <tr>
                  <th rowspan="2" class="text-center align-middle" style="background-color: #f8f9fa; border: 1px solid #dee2e6; font-size: 14px; font-weight: 600; padding: 12px; min-width: 200px;">
                    Task Description
                  </th>
                  <th colspan="8" class="text-center" style="background-color: #e3f2fd; border: 1px solid #dee2e6; padding: 8px; font-size: 13px;">
                    <div class="d-flex align-items-center justify-content-center gap-2">
                      <span class="fw-semibold">Week 1:</span>
                      <input type="date" id="week1_date" value="{{ $currentWeekStart->format('Y-m-d') }}" class="form-control form-control-sm" style="width: 140px; font-size: 12px;" onchange="updateWeekDates()">
                    </div>
                  </th>
                  <th colspan="8" class="text-center" style="background-color: #e8f5e9; border: 1px solid #dee2e6; padding: 8px; font-size: 13px;">
                    <div class="d-flex align-items-center justify-content-center gap-2">
                      <span class="fw-semibold">Week 2:</span>
                      <input type="date" id="week2_date" value="{{ $currentWeekStart->copy()->addWeek()->format('Y-m-d') }}" class="form-control form-control-sm" style="width: 140px; font-size: 12px;" onchange="updateWeekDates()">
                    </div>
                  </th>
                </tr>
                <tr>
                  <th class="text-center" style="width: 50px; border: 1px solid #dee2e6; font-size: 12px; padding: 8px; background-color: #e3f2fd;">MON</th>
                  <th class="text-center" style="width: 50px; border: 1px solid #dee2e6; font-size: 12px; padding: 8px; background-color: #e3f2fd;">TUE</th>
                  <th class="text-center" style="width: 50px; border: 1px solid #dee2e6; font-size: 12px; padding: 8px; background-color: #e3f2fd;">WED</th>
                  <th class="text-center" style="width: 50px; border: 1px solid #dee2e6; font-size: 12px; padding: 8px; background-color: #e3f2fd;">THU</th>
                  <th class="text-center" style="width: 50px; border: 1px solid #dee2e6; font-size: 12px; padding: 8px; background-color: #e3f2fd;">FRI</th>
                  <th class="text-center" style="width: 50px; border: 1px solid #dee2e6; font-size: 12px; padding: 8px; background-color: #e3f2fd;">SAT</th>
                  <th class="text-center" style="width: 50px; border: 1px solid #dee2e6; font-size: 12px; padding: 8px; background-color: #e3f2fd;">SUN</th>
                  <th class="text-center" style="width: 120px; background-color: #fff3e0; border: 1px solid #dee2e6; font-size: 12px; padding: 8px; color: #e65100;">REMARKS</th>
                  <th class="text-center" style="width: 50px; border: 1px solid #dee2e6; font-size: 12px; padding: 8px; background-color: #e8f5e9;">MON</th>
                  <th class="text-center" style="width: 50px; border: 1px solid #dee2e6; font-size: 12px; padding: 8px; background-color: #e8f5e9;">TUE</th>
                  <th class="text-center" style="width: 50px; border: 1px solid #dee2e6; font-size: 12px; padding: 8px; background-color: #e8f5e9;">WED</th>
                  <th class="text-center" style="width: 50px; border: 1px solid #dee2e6; font-size: 12px; padding: 8px; background-color: #e8f5e9;">THU</th>
                  <th class="text-center" style="width: 50px; border: 1px solid #dee2e6; font-size: 12px; padding: 8px; background-color: #e8f5e9;">FRI</th>
                  <th class="text-center" style="width: 50px; border: 1px solid #dee2e6; font-size: 12px; padding: 8px; background-color: #e8f5e9;">SAT</th>
                  <th class="text-center" style="width: 50px; border: 1px solid #dee2e6; font-size: 12px; padding: 8px; background-color: #e8f5e9;">SUN</th>
                  <th class="text-center" style="width: 120px; background-color: #fff3e0; border: 1px solid #dee2e6; font-size: 12px; padding: 8px; color: #e65100;">REMARKS</th>
                </tr>
              </thead>
              <tbody>
                @php
                  $kitchenTasks = $tasks->where('task_category', 'KITCHEN');
                  $cleaningTasks = $tasks->where('task_category', 'GENERAL CLEANING');
                @endphp

                <!-- Kitchen Tasks -->
                @foreach($kitchenTasks as $index => $task)
                <tr class="task-row">
                  @if($index === 0)
                  <td rowspan="{{ $kitchenTasks->count() }}" class="category-header" style="background: linear-gradient(135deg, #4caf50, #66bb6a); color: white; vertical-align: middle; text-align: center; font-weight: 600; border: 1px solid #dee2e6; font-size: 13px; padding: 15px; min-width: 120px;">
                    <div style="writing-mode: vertical-lr; text-orientation: mixed;">
                      KITCHEN TASKS
                    </div>
                  </td>
                  @endif
                  <td class="task-description" style="background-color: #fafafa; padding: 12px; border: 1px solid #dee2e6; font-size: 13px; line-height: 1.4;">
                    {{ $task->task_description }}
                  </td>

                  <!-- Week 1 Status -->
                  @for($day = 0; $day < 7; $day++)
                  <td class="day-cell" style="text-align: center; padding: 8px; border: 1px solid #dee2e6; background-color: #f8f9fa;">
                    <div class="status-buttons d-flex justify-content-center gap-1">
                      <button type="button" class="status-btn check-btn {{ ($task->week1_status[$day] ?? '') === '✓' ? 'active' : '' }}"
                              data-task-id="{{ $task->id }}" data-week="1" data-day="{{ $day }}" data-status="✓">✓</button>
                      <button type="button" class="status-btn wrong-btn {{ ($task->week1_status[$day] ?? '') === '✗' ? 'active' : '' }}"
                              data-task-id="{{ $task->id }}" data-week="1" data-day="{{ $day }}" data-status="✗">✗</button>
                    </div>
                  </td>
                  @endfor

                  <!-- Week 1 Remarks -->
                  <td class="remarks-cell" style="padding: 8px; border: 1px solid #dee2e6; background-color: #fff8e1;">
                    <textarea class="remarks-input form-control form-control-sm" data-task-id="{{ $task->id }}" data-week="1" placeholder="Add remarks...">{{ $task->week1_remarks ?? '' }}</textarea>
                  </td>

                  <!-- Week 2 Status -->
                  @for($day = 0; $day < 7; $day++)
                  <td class="day-cell" style="text-align: center; padding: 8px; border: 1px solid #dee2e6; background-color: #f1f8e9;">
                    <div class="status-buttons d-flex justify-content-center gap-1">
                      <button type="button" class="status-btn check-btn {{ ($task->week2_status[$day] ?? '') === '✓' ? 'active' : '' }}"
                              data-task-id="{{ $task->id }}" data-week="2" data-day="{{ $day }}" data-status="✓">✓</button>
                      <button type="button" class="status-btn wrong-btn {{ ($task->week2_status[$day] ?? '') === '✗' ? 'active' : '' }}"
                              data-task-id="{{ $task->id }}" data-week="2" data-day="{{ $day }}" data-status="✗">✗</button>
                    </div>
                  </td>
                  @endfor

                  <!-- Week 2 Remarks -->
                  <td class="remarks-cell" style="padding: 8px; border: 1px solid #dee2e6; background-color: #fff8e1;">
                    <textarea class="remarks-input form-control form-control-sm" data-task-id="{{ $task->id }}" data-week="2" placeholder="Add remarks...">{{ $task->week2_remarks ?? '' }}</textarea>
                  </td>
                </tr>
                @endforeach

                <!-- General Cleaning Tasks -->
                @foreach($cleaningTasks as $index => $task)
                <tr class="task-row">
                  @if($index === 0)
                  <td rowspan="{{ $cleaningTasks->count() }}" class="category-header" style="background: linear-gradient(135deg, #2196f3, #42a5f5); color: white; vertical-align: middle; text-align: center; font-weight: 600; border: 1px solid #dee2e6; font-size: 13px; padding: 15px; min-width: 120px;">
                    <div style="writing-mode: vertical-lr; text-orientation: mixed;">
                      GENERAL CLEANING
                    </div>
                  </td>
                  @endif
                  <td class="task-description" style="background-color: #fafafa; padding: 12px; border: 1px solid #dee2e6; font-size: 13px; line-height: 1.4;">
                    {{ $task->task_description }}
                  </td>

                  <!-- Week 1 Status -->
                  @for($day = 0; $day < 7; $day++)
                  <td class="day-cell" style="text-align: center; padding: 8px; border: 1px solid #dee2e6; background-color: #f8f9fa;">
                    <div class="status-buttons d-flex justify-content-center gap-1">
                      <button type="button" class="status-btn check-btn {{ ($task->week1_status[$day] ?? '') === '✓' ? 'active' : '' }}"
                              data-task-id="{{ $task->id }}" data-week="1" data-day="{{ $day }}" data-status="✓">✓</button>
                      <button type="button" class="status-btn wrong-btn {{ ($task->week1_status[$day] ?? '') === '✗' ? 'active' : '' }}"
                              data-task-id="{{ $task->id }}" data-week="1" data-day="{{ $day }}" data-status="✗">✗</button>
                    </div>
                  </td>
                  @endfor

                  <!-- Week 1 Remarks -->
                  <td class="remarks-cell" style="padding: 8px; border: 1px solid #dee2e6; background-color: #fff8e1;">
                    <textarea class="remarks-input form-control form-control-sm" data-task-id="{{ $task->id }}" data-week="1" placeholder="Add remarks...">{{ $task->week1_remarks ?? '' }}</textarea>
                  </td>

                  <!-- Week 2 Status -->
                  @for($day = 0; $day < 7; $day++)
                  <td class="day-cell" style="text-align: center; padding: 8px; border: 1px solid #dee2e6; background-color: #f1f8e9;">
                    <div class="status-buttons d-flex justify-content-center gap-1">
                      <button type="button" class="status-btn check-btn {{ ($task->week2_status[$day] ?? '') === '✓' ? 'active' : '' }}"
                              data-task-id="{{ $task->id }}" data-week="2" data-day="{{ $day }}" data-status="✓">✓</button>
                      <button type="button" class="status-btn wrong-btn {{ ($task->week2_status[$day] ?? '') === '✗' ? 'active' : '' }}"
                              data-task-id="{{ $task->id }}" data-week="2" data-day="{{ $day }}" data-status="✗">✗</button>
                    </div>
                  </td>
                  @endfor

                  <!-- Week 2 Remarks -->
                  <td class="remarks-cell" style="padding: 8px; border: 1px solid #dee2e6; background-color: #fff8e1;">
                    <textarea class="remarks-input form-control form-control-sm" data-task-id="{{ $task->id }}" data-week="2" placeholder="Add remarks...">{{ $task->week2_remarks ?? '' }}</textarea>
                  </td>
                </tr>
                @endforeach

              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<style>
/* Clean and Simple Task Checklist Styling */
body {
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  background-color: #f8f9fa;
}

.card {
  border: none;
  border-radius: 12px;
  overflow: hidden;
}

.table {
  border-collapse: separate;
  border-spacing: 0;
  margin: 0;
  font-size: 13px;
}

.table td, .table th {
  border: 1px solid #dee2e6;
  vertical-align: middle;
  transition: all 0.2s ease;
}

.task-row:hover {
  background-color: #f1f3f4;
}

.status-buttons {
  display: flex;
  gap: 4px;
  justify-content: center;
  align-items: center;
}

.status-btn {
  width: 24px;
  height: 24px;
  border: 2px solid #e0e0e0;
  background: white;
  font-size: 12px;
  font-weight: 600;
  cursor: pointer;
  border-radius: 6px;
  padding: 0;
  margin: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s ease;
  box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.check-btn {
  color: #4caf50;
  border-color: #4caf50;
}

.check-btn:hover {
  background: #e8f5e9;
  transform: translateY(-1px);
  box-shadow: 0 2px 6px rgba(76, 175, 80, 0.3);
}

.check-btn.active {
  background: #4caf50;
  color: white;
  border-color: #4caf50;
  transform: scale(1.1);
}

.wrong-btn {
  color: #f44336;
  border-color: #f44336;
}

.wrong-btn:hover {
  background: #ffebee;
  transform: translateY(-1px);
  box-shadow: 0 2px 6px rgba(244, 67, 54, 0.3);
}

.wrong-btn.active {
  background: #f44336;
  color: white;
  border-color: #f44336;
  transform: scale(1.1);
}

.remarks-input {
  border: 1px solid #e0e0e0;
  resize: vertical;
  background: white;
  padding: 6px 8px;
  margin: 0;
  outline: none;
  width: 100%;
  min-height: 32px;
  font-size: 12px;
  line-height: 1.3;
  border-radius: 4px;
  transition: border-color 0.2s ease;
}

.remarks-input:focus {
  border-color: #2196f3;
  box-shadow: 0 0 0 2px rgba(33, 150, 243, 0.1);
}

.category-header {
  border-radius: 8px 0 0 8px;
  box-shadow: inset 0 1px 3px rgba(0,0,0,0.1);
}

.task-description {
  font-weight: 500;
  color: #333;
}

/* Responsive adjustments */
@media (max-width: 768px) {
  .table {
    font-size: 11px;
  }

  .status-btn {
    width: 20px;
    height: 20px;
    font-size: 10px;
  }

  .remarks-input {
    font-size: 11px;
    min-height: 28px;
  }
}

@media print {
  .container-fluid {
    max-width: none;
    padding: 0;
  }

  .table td, .table th {
    border: 1px solid #000;
    -webkit-print-color-adjust: exact;
  }

  .category-header {
    -webkit-print-color-adjust: exact;
  }

  .task-description {
    -webkit-print-color-adjust: exact;
  }
}
</style>

<script>
// Update task status with buttons
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('status-btn')) {
        const taskId = e.target.dataset.taskId;
        const week = e.target.dataset.week;
        const day = e.target.dataset.day;
        const status = e.target.dataset.status;

        // Find other button in same cell and deactivate it
        const otherButtons = e.target.parentElement.querySelectorAll('.status-btn');
        otherButtons.forEach(btn => {
            if (btn !== e.target) {
                btn.classList.remove('active');
            }
        });

        // Toggle current button
        if (e.target.classList.contains('active')) {
            e.target.classList.remove('active');
            // Send empty status to clear
            updateTaskStatus(taskId, week, day, '');
        } else {
            e.target.classList.add('active');
            // Send status
            updateTaskStatus(taskId, week, day, status);
        }
    }
});

function updateTaskStatus(taskId, week, day, status) {
    fetch('{{ route("task.updateStatus") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            task_id: taskId,
            week: week,
            day: day,
            status: status
        })
    })
    .then(response => response.json())
    .then(data => {
        if (!data.success) {
            alert('Error updating status');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error updating status');
    });
}

// Update remarks with debounce
let remarksTimeout;
document.addEventListener('input', function(e) {
    if (e.target.classList.contains('remarks-input')) {
        clearTimeout(remarksTimeout);
        remarksTimeout = setTimeout(() => {
            const taskId = e.target.dataset.taskId;
            const week = e.target.dataset.week;
            const remarks = e.target.value;

            fetch('{{ route("task.updateRemarks") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    task_id: taskId,
                    week: week,
                    remarks: remarks
                })
            })
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    alert('Error updating remarks');
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        }, 1000); // Wait 1 second after user stops typing
    }
});

// Update week dates
function updateWeekDates() {
    const week1Date = document.getElementById('week1_date').value;
    const week2Date = document.getElementById('week2_date').value;

    if (week1Date) {
        fetch('{{ route("task.updateDates") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                week_start_date: week1Date
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Optionally reload the page to reflect new dates
                // location.reload();
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
    }
}
</script>
@endsection