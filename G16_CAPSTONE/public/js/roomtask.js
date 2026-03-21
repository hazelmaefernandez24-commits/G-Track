function getWeekDates(year, month, weekNumber) {
    // Get the first day of the current month
    const firstDay = new Date(year, month - 1, 1);
    const firstDayOfWeek = firstDay.getDay(); // 0 = Sunday, 1 = Monday, etc.

    // Calculate the start date of the week
    let startDate = new Date(year, month - 1, 1);
    
    // For Week 1, we need to find the previous Sunday
    if (weekNumber === 1) {
        // If the first day of month is not Sunday, go back to previous Sunday
        if (firstDayOfWeek !== 0) {
            startDate.setDate(startDate.getDate() - firstDayOfWeek);
        }
    } else {
        // For subsequent weeks, start from the day after the previous week's Saturday
        const previousWeekStart = new Date(year, month - 1, 1);
        previousWeekStart.setDate(previousWeekStart.getDate() - firstDayOfWeek + ((weekNumber - 1) * 7));
        startDate = previousWeekStart;
    }

    // Calculate end date (Saturday)
    const endDate = new Date(startDate);
    endDate.setDate(endDate.getDate() + 6);

    return { startDate, endDate };
}

function updateWeeks() {
    const monthSelect = document.getElementById('monthSelect');
    const yearSelect = document.getElementById('yearSelect');
    const weekSelect = document.getElementById('weekSelect');
    
    const month = parseInt(monthSelect.value);
    const year = parseInt(yearSelect.value);
    
    if (month && year) {
        const firstDay = new Date(year, month - 1, 1);
        const lastDay = new Date(year, month, 0);
        const firstDayOfWeek = firstDay.getDay();
        const daysInMonth = lastDay.getDate();
        
        // Calculate total weeks that include days from this month
        const totalDays = daysInMonth + firstDayOfWeek;
        const weeksInMonth = Math.ceil(totalDays / 7);
        
        // Clear and add default option
        weekSelect.innerHTML = '<option value="">Select Week</option>';
        
        // Show weeks based on actual calendar weeks
        for (let i = 1; i <= weeksInMonth; i++) {
            const { startDate, endDate } = getWeekDates(year, month, i);
            
            // Format dates
            const startDateStr = startDate.toLocaleDateString('en-US', { 
                month: 'short', 
                day: 'numeric' 
            });
            const endDateStr = endDate.toLocaleDateString('en-US', { 
                month: 'short', 
                day: 'numeric' 
            });
            
            const option = document.createElement('option');
            option.value = i;
            option.textContent = `Week ${i} (${startDateStr} - ${endDateStr})`;
            weekSelect.appendChild(option);
        }
    } else {
        weekSelect.innerHTML = '<option value="">Select Week</option>';
    }
}

// Add event listeners when the DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
  // Add click handlers for the task buttons
  document.getElementById('addTaskButton').addEventListener('click', function() {
    openForm('add');
  });

  document.getElementById('editTaskButton').addEventListener('click', function() {
    openForm('edit');
  });

  // Initialize other event listeners
  const monthSelect = document.getElementById('monthSelect');
  const yearSelect = document.getElementById('yearSelect');
  const weekSelect = document.getElementById('weekSelect');

  if (monthSelect && yearSelect && weekSelect) {
    monthSelect.addEventListener('change', updateWeeks);
    yearSelect.addEventListener('change', updateWeeks);
  }

  // Handle window resize
  window.addEventListener('resize', function() {
    const roomDetails = document.querySelector('.room-details');
    const timeSelection = document.querySelector('.time-selection');
    
    if (window.innerWidth < 768) {
      roomDetails.style.flexDirection = 'column';
      timeSelection.style.width = '100%';
    } else {
      roomDetails.style.flexDirection = 'row';
      timeSelection.style.width = 'auto';
    }
  });

  // Set default values and trigger initial update
  monthSelect.value = new Date().getMonth() + 1;
  yearSelect.value = new Date().getFullYear();
  updateWeeks();
});

function openForm(mode) {
  currentMode = mode;
  const formContainer = document.getElementById('taskFormContainer');
  const title = document.getElementById('formTitle');
  const taskNameContainer = document.getElementById('taskNameContainer');
  const form = document.getElementById('taskForm');

  if (!formContainer || !title || !taskNameContainer || !form) {
    console.error('Required form elements not found');
    return;
  }

  formContainer.classList.remove('hidden');

  if (mode === 'add') {
    title.textContent = 'Add Task';
    taskNameContainer.innerHTML = `
      <label for="taskName">Name:</label>
      <input type="text" id="taskName" name="taskName" placeholder="Enter name" required />
    `;
    form.reset();
  } else if (mode === 'edit') {
    title.textContent = 'Edit Task';
    const currentDay = document.getElementById('currentDay').textContent;
  const room = window.selectedRoom;
    
    if (tasksByDay[currentDay] && tasksByDay[currentDay][room]) {
      const tasks = tasksByDay[currentDay][room];
      taskNameContainer.innerHTML = `
        <label for="taskNameDropdown">Select Task to Edit:</label>
        <select id="taskNameDropdown" name="taskNameDropdown" required>
          ${tasks.map((task, index) => `
            <option value="${index}">${task.name} - ${task.area}</option>
          `).join('')}
        </select>
        <label for="taskName">Change Name To:</label>
        <input type="text" id="taskName" name="taskName" placeholder="Enter new name" required />
      `;

      const nameDropdown = document.getElementById('taskNameDropdown');
      if (nameDropdown) {
        loadSelectedTask(nameDropdown.value);
        nameDropdown.addEventListener('change', function() {
          loadSelectedTask(this.value);
        });
      }
    } else {
      alert('No tasks available to edit');
      closeForm();
    }
  }
}

function loadSelectedTask(index) {
  const currentDay = document.getElementById('currentDay').textContent;
  const room = window.selectedRoom;
  const task = tasksByDay[currentDay][room][index];
  
  if (task) {
    document.getElementById('taskName').value = task.name;
    document.getElementById('taskArea').value = task.area;
    document.getElementById('taskDesc').value = task.desc;
    editTaskIndex = index;
  }
}

function closeForm() {
  const formContainer = document.getElementById('taskFormContainer');
  const form = document.getElementById('taskForm');
  if (formContainer && form) {
    formContainer.classList.add('hidden');
    form.reset();
  }
}

function saveTask(event) {
  event.preventDefault();

  const currentDay = document.getElementById('currentDay').textContent;
  const room = '{{ $selectedRoom }}';
  const name = currentMode === 'add' 
    ? document.getElementById('taskName').value
    : tasksByDay[currentDay][room][editTaskIndex].name;
  const area = document.getElementById('taskArea').value;
  const desc = document.getElementById('taskDesc').value;

  // Validate inputs
  if (!name || !area || !desc) {
    alert('Please fill in all fields');
    return;
  }

  // Use RoomManagement endpoints (create/update) which expect: room_number, area, description, day, assigned_to
  const payload = {
    room_number: room,
    area: area,
    description: desc,
    day: currentDay,
    assigned_to: (currentMode === 'add') ? '' : name // keep assigned_to empty for add (rotation assigns later)
  };

  // Use runtime endpoints for RoomManagement API
  let url = '/room-management/tasks';
  let method = 'POST';
  if (currentMode === 'edit') {
    const id = tasksByDay[currentDay] && tasksByDay[currentDay][room] && tasksByDay[currentDay][room][editTaskIndex] && tasksByDay[currentDay][room][editTaskIndex].id
                ? tasksByDay[currentDay][room][editTaskIndex].id
                : '';
    if (!id) {
      alert('Unable to determine task id for update');
      return;
    }
    url = `/room-management/tasks/${id}`;
    method = 'PUT';
  }

  fetch(url, {
    method: method,
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
    },
    body: JSON.stringify(payload)
  })
  .then(response => {
    if (!response.ok) {
      throw new Error('Network response was not ok');
    }
    return response.json();
  })
  .then(data => {
    if (data.success) {
      // Update the tasksByDay object
      if (currentMode === 'add') {
        if (!tasksByDay[currentDay]) {
          tasksByDay[currentDay] = {};
        }
        if (!tasksByDay[currentDay][room]) {
          tasksByDay[currentDay][room] = [];
        }
        tasksByDay[currentDay][room].push({
          name,
          area,
          desc,
          status: 'not yet'
        });
      } else if (currentMode === 'edit' && editTaskIndex !== null) {
        tasksByDay[currentDay][room][editTaskIndex] = {
          name,
          area,
          desc,
          status: tasksByDay[currentDay][room][editTaskIndex].status || 'not yet'
        };
      }

      // Refresh the task list
      loadTasksForCurrentDay();
      closeForm();
      alert('Task saved successfully!');
    } else {
      throw new Error(data.message || 'Failed to save task');
    }
  })
  .catch(error => {
    console.error('Error:', error);
    alert('An error occurred while saving the task: ' + error.message);
  });
}

function updateStatus(button, taskId, status) {
    const currentDay = document.getElementById('currentDay').textContent;
    const currentWeek = document.getElementById('weekSelect').value;
    
    // Check if the day in this week is already completed
    if (weekDayCompletionStatus[currentWeek] && weekDayCompletionStatus[currentWeek][currentDay]) {
        return false;
    }

    const row = button.closest('tr');
    const checkBtn = row.querySelector('.check-btn');
    const wrongBtn = row.querySelector('.wrong-btn');

    // Remove active class from both buttons
    checkBtn.classList.remove('active');
    wrongBtn.classList.remove('active');

    // Add active class to clicked button
    if (status === 'checked') {
        checkBtn.classList.add('active');
    } else if (status === 'wrong') {
        wrongBtn.classList.add('active');
    }

    // Make API call to update status
    fetch('/update-task-status', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({
            taskId: taskId,
            status: status,
            day: currentDay,
            week: currentWeek
        })
    });

    // Check if all tasks have status and enable/disable Mark All as Completed button
    const rows = document.querySelectorAll('#taskTableBody tr');
    const allTasksHaveStatus = Array.from(rows).every(row => {
        const checkActive = row.querySelector('.check-btn.active');
        const wrongActive = row.querySelector('.wrong-btn.active');
        return checkActive || wrongActive;
    });

    const markAllBtn = document.getElementById('markAllCompleted');
    if (!(weekDayCompletionStatus[currentWeek] && weekDayCompletionStatus[currentWeek][currentDay])) {
        markAllBtn.disabled = !allTasksHaveStatus;
        markAllBtn.style.opacity = allTasksHaveStatus ? '1' : '0.5';
        markAllBtn.style.cursor = allTasksHaveStatus ? 'pointer' : 'not-allowed';
    }
}

function editTask(taskId) {
    // Here you can implement the edit functionality
    // For example, show a modal with the task details
    alert('Edit functionality will be implemented here');
}


function loadTasksForCurrentDay() {
const currentDay = document.getElementById('currentDay').textContent;
const room = window.selectedRoom;
const currentWeek = document.getElementById('weekSelect').value;

if (tasksByDay[currentDay] && tasksByDay[currentDay][room]) {
    const tasks = tasksByDay[currentDay][room];
    const isDayCompleted = weekDayCompletionStatus[currentWeek] &&
        weekDayCompletionStatus[currentWeek][currentDay];

    taskTableBody.innerHTML = tasks.map(task => {
        const isChecked = isDayCompleted && task.status === 'checked';
        const isWrong = isDayCompleted && task.status === 'wrong';
        return `
            <tr data-task-id="${task.id}">
                <td>${task.name}</td>
                <td>${task.area}</td>
                <td>${task.desc}</td>
                <td>
                    <div class="status-buttons">
                        <button class="status-btn check-btn ${isChecked ? 'active' : ''}" 
                                onclick="updateStatus(this, '${task.id}', 'checked')"
                                ${isDayCompleted ? 'disabled' : ''}
                                style="${isDayCompleted ? (isChecked ? 'opacity: 1; background-color: #08a821; color: white; border-color: #08a821;' : 'display: none;') : ''}">
                            <i class="fas fa-check"></i>
                        </button>
                        <button class="status-btn wrong-btn ${isWrong ? 'active' : ''}" 
                                onclick="updateStatus(this, '${task.id}', 'wrong')"
                                ${isDayCompleted ? 'disabled' : ''}
                                style="${isDayCompleted ? (isWrong ? 'opacity: 1; background-color: #e61515; color: white; border-color: #e61515;' : 'display: none;') : ''}">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `;
    }).join('');

    // Update the "Mark All Completed" button state
    const markAllBtn = document.getElementById('markAllCompleted');
    if (isDayCompleted) {
        markAllBtn.disabled = true;
        markAllBtn.style.opacity = '0.5';
        markAllBtn.style.cursor = 'not-allowed';
    } else {
        const allTasksHaveStatus = tasks.every(task => task.status === 'checked' || task.status === 'wrong');
        markAllBtn.disabled = !allTasksHaveStatus;
        markAllBtn.style.opacity = allTasksHaveStatus ? '1' : '0.5';
        markAllBtn.style.cursor = allTasksHaveStatus ? 'pointer' : 'not-allowed';
    }
} else {
    taskTableBody.innerHTML = `<tr><td colspan="4" style="text-align:center;">No tasks assigned for this room on ${currentDay}.</td></tr>`;
    const markAllBtn = document.getElementById('markAllCompleted');
    markAllBtn.disabled = true;
    markAllBtn.style.opacity = '0.5';
    markAllBtn.style.cursor = 'not-allowed';
}
}

function markAllCompleted() {
    const currentDay = document.getElementById('currentDay').textContent;
    const room = window.selectedRoom;
    const currentWeek = document.getElementById('weekSelect').value;
    const currentMonth = document.getElementById('monthSelect').value;
    const currentYear = document.getElementById('yearSelect').value;

    // Create a unique key for this specific day
    const dayKey = `${currentYear}-${currentMonth}-${currentWeek}-${currentDay}`;

    // Check if all tasks have a status
    const taskRows = document.querySelectorAll('#taskTableBody tr[data-task-id]');
    const allTasksHaveStatus = Array.from(taskRows).every(row => {
        const checkActive = row.querySelector('.check-btn.active');
        const wrongActive = row.querySelector('.wrong-btn.active');
        return checkActive || wrongActive;
    });

    if (!allTasksHaveStatus) {
        alert('Please set a status (checked or wrong) for all tasks before marking the day as completed.');
        return;
    }

    // Show confirmation dialog
    const confirmed = confirm('Are you sure you want to mark all tasks for this day as completed?');
    if (!confirmed) {
        return;
    }

    // Get all tasks for the current day with their current status
    const tasks = Array.from(taskRows).map(row => {
        const taskId = row.getAttribute('data-task-id');
        const checkBtn = row.querySelector('.check-btn');
        const wrongBtn = row.querySelector('.wrong-btn');

        let status = 'not yet';
        if (checkBtn && checkBtn.classList.contains('active')) {
            status = 'checked';
        } else if (wrongBtn && wrongBtn.classList.contains('active')) {
            status = 'wrong';
        }

        console.log(`Task ${taskId} will be marked as: ${status}`);

        return {
            id: taskId,
      status: status,
      assigned_name: (row.children[0] ? row.children[0].textContent.trim() : '')
        };
    });

    // Send the request to mark the day as completed
    fetch('/mark-day-complete', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({
            day: currentDay,
            room: room,
            week: currentWeek,
            month: currentMonth,
            year: currentYear,
            dayKey: dayKey,
            tasks: tasks
        })
    })
.then(response => {
    if (!response.ok) {
        return response.json().then(err => Promise.reject(err));
    }
    return response.json();
})
.then(data => {
    if (data.success) {
        // Lock the buttons after successful completion and hide unselected statuses
        rows.forEach(row => {
            const checkBtn = row.querySelector('.check-btn');
            const wrongBtn = row.querySelector('.wrong-btn');
            if (checkBtn.classList.contains('active')) {
                checkBtn.disabled = true;
                checkBtn.style.opacity = '1';
                checkBtn.style.backgroundColor = '#08a821';
                checkBtn.style.color = 'white';
                checkBtn.style.borderColor = '#08a821';
                wrongBtn.style.display = 'none'; // Hide the unselected button
            } else if (wrongBtn.classList.contains('active')) {
                wrongBtn.disabled = true;
                wrongBtn.style.opacity = '1';
                wrongBtn.style.backgroundColor = '#e61515';
                wrongBtn.style.color = 'white';
                wrongBtn.style.borderColor = '#e61515';
                checkBtn.style.display = 'none'; // Hide the unselected button
            }
        });

        // Update weekDayCompletionStatus to reflect completion
        if (!weekDayCompletionStatus[currentWeek]) {
            weekDayCompletionStatus[currentWeek] = {};
        }
        weekDayCompletionStatus[currentWeek][currentDay] = true;

        // Disable the "Mark All Completed" button
        const markAllBtn = document.getElementById('markAllCompleted');
        markAllBtn.disabled = true;
        markAllBtn.style.opacity = '0.5';
        markAllBtn.style.cursor = 'not-allowed';

        alert('Day marked as completed successfully!');
    } else {
        throw new Error(data.message || 'Failed to mark day as completed.');
    }
})
.catch(error => {
    console.error('Error:', error);
    alert('An error occurred while marking the day as completed: ' + error.message);
});
} 
function validateFiles(input) {
  const maxFiles = 3;
  const files = input.files;
  const errorDiv = document.getElementById('fileError');
  const submitButton = document.getElementById('submitFeedback');
  
  // Display file names
  if (files.length > 0) {
    const fileNames = Array.from(files).map(file => file.name);
    document.getElementById('fileName').textContent = 'Selected files: ' + fileNames.join(', ');
  } else {
    document.getElementById('fileName').textContent = 'No files chosen';
  }
  
  // Validate maximum files
  if (files.length > maxFiles) {
    errorDiv.textContent = 'Maximum 3 photos allowed.';
    errorDiv.style.display = 'block';
    submitButton.disabled = true;
    return false;
  }
  
  // Validate file types
  const validTypes = ['image/jpeg', 'image/png', 'image/jpg'];
  const invalidFiles = Array.from(files).filter(file => !validTypes.includes(file.type));
  
  if (invalidFiles.length > 0) {
    errorDiv.textContent = 'Please select only image files (JPG, PNG)';
    errorDiv.style.display = 'block';
    submitButton.disabled = true;
    return false;
  }
  
  errorDiv.style.display = 'none';
  submitButton.disabled = false;
  return true;
}

// Add form submission validation
document.getElementById('feedbackForm').addEventListener('submit', function(e) {
  const fileInput = document.getElementById('feedback_files');
  if (!validateFiles(fileInput)) {
    e.preventDefault();
}
});

document.getElementById('prevDayButton').addEventListener('click', function() {
  currentDayIndex = (currentDayIndex - 1 + days.length) % days.length;
  currentDayElement.textContent = days[currentDayIndex];
  loadTasksForCurrentDay();
});

document.getElementById('nextDayButton').addEventListener('click', function() {
  currentDayIndex = (currentDayIndex + 1) % days.length;
  currentDayElement.textContent = days[currentDayIndex];
  loadTasksForCurrentDay();
});


function disableStatusSelects() {
  const statusSelects = document.querySelectorAll('.status-select');
  statusSelects.forEach(select => {
    select.disabled = true;
  });
  
  // Disable buttons
  document.getElementById('markAllCompleted').disabled = true;
  document.getElementById('markDayComplete').disabled = true;
  document.getElementById('markAllCompleted').style.opacity = '0.5';
  document.getElementById('markDayComplete').style.opacity = '0.5';
}

window.tasksByDay = JSON.parse(document.getElementById('tasksByDayData').textContent);
window.daysOfWeek = JSON.parse(document.getElementById('daysOfWeekData').textContent);
window.currentDayIndex = parseInt(document.getElementById('currentDayIndexData').textContent, 10);
window.dayCompletionStatus = JSON.parse(document.getElementById('dayCompletionStatusData').textContent);
window.selectedRoom = document.getElementById('selectedRoomData').textContent;

