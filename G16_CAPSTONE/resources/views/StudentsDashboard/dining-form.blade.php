<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dining Team Tasking Form</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Poppins', sans-serif;
      background: #f6f8fa;
      padding: 20px;
    }

    .form-container {
      max-width: 950px;
      margin: 0 auto;
      background: #fff;
      border: 2px solid #000;
      border-radius: 8px;
      overflow: hidden;
    }

    .form-header {
      background: #fff;
      padding: 15px;
      border-bottom: 2px solid #000;
    }

    .form-title {
      font-size: 1.2rem;
      font-weight: 700;
      text-align: center;
      margin-bottom: 10px;
    }

    .form-title .team-name {
      color: #e74c3c;
      text-transform: uppercase;
    }

    .form-info {
      display: grid;
      grid-template-columns: 1fr 1fr 1fr;
      gap: 20px;
      font-size: 0.9rem;
    }

    .info-group {
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .info-label {
      font-weight: 600;
    }

    .info-input {
      border: none;
      border-bottom: 1px solid #000;
      padding: 2px 5px;
      font-family: 'Poppins', sans-serif;
      font-size: 0.9rem;
      min-width: 100px;
    }

    .tasking-table {
      width: 100%;
      border-collapse: collapse;
    }

    .tasking-table th,
    .tasking-table td {
      border: 1px solid #000;
      padding: 8px;
      text-align: center;
      font-size: 0.85rem;
    }

    .tasking-table th {
      background: #f8f9fa;
      font-weight: 600;
    }

    .meal-header {
      background: #e9ecef;
      font-weight: 600;
      writing-mode: vertical-lr;
      text-orientation: mixed;
      width: 80px;
    }

    .day-header {
      background: #f8f9fa;
      font-weight: 600;
      padding: 10px 8px;
    }

    .task-cell {
      height: 60px;
      vertical-align: top;
      position: relative;
    }

    .task-input {
      width: 100%;
      height: 100%;
      border: none;
      padding: 4px;
      font-family: 'Poppins', sans-serif;
      font-size: 0.8rem;
      resize: none;
      background: transparent;
      cursor: pointer;
      text-align: center;
      line-height: 1.2;
    }

    .task-input:focus {
      outline: 2px solid #22BBEA;
      background: #f0f8ff;
    }

    .assignment-modal {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0,0,0,0.5);
      z-index: 1000;
    }

    .assignment-modal-content {
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      background: #fff;
      padding: 25px;
      border-radius: 8px;
      width: 500px;
      max-height: 80vh;
      overflow-y: auto;
    }

    .assignment-header {
      font-size: 1.2rem;
      font-weight: 600;
      margin-bottom: 20px;
      color: #333;
    }

    .student-assignment {
      margin-bottom: 15px;
      padding: 15px;
      border: 1px solid #ddd;
      border-radius: 6px;
      background: #f9f9f9;
    }

    .student-select, .task-select {
      width: 100%;
      padding: 8px;
      margin: 5px 0;
      border: 1px solid #ccc;
      border-radius: 4px;
      font-family: 'Poppins', sans-serif;
    }

    .add-student-btn {
      background: #28a745;
      color: #fff;
      border: none;
      padding: 8px 15px;
      border-radius: 4px;
      cursor: pointer;
      margin: 10px 5px;
      font-size: 0.85rem;
    }

    .remove-student-btn {
      background: #dc3545;
      color: #fff;
      border: none;
      padding: 5px 10px;
      border-radius: 4px;
      cursor: pointer;
      font-size: 0.75rem;
      float: right;
    }

    .modal-buttons {
      text-align: center;
      margin-top: 20px;
    }

    .save-assignment-btn {
      background: #007bff;
      color: #fff;
      border: none;
      padding: 10px 20px;
      border-radius: 4px;
      cursor: pointer;
      margin: 0 5px;
    }

    .cancel-assignment-btn {
      background: #6c757d;
      color: #fff;
      border: none;
      padding: 10px 20px;
      border-radius: 4px;
      cursor: pointer;
      margin: 0 5px;
    }

    .back-btn {
      position: fixed;
      top: 15px;
      left: 15px;
      background: #22BBEA;
      color: #fff;
      border: none;
      padding: 4px 8px;
      border-radius: 3px;
      font-family: 'Poppins', sans-serif;
      font-weight: 500;
      font-size: 0.7rem;
      cursor: pointer;
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      gap: 4px;
    }

    .back-btn:hover {
      background: #1a9bc7;
    }

    .save-btn {
      background: #28a745;
      color: #fff;
      border: none;
      padding: 8px 20px;
      border-radius: 4px;
      font-family: 'Poppins', sans-serif;
      font-weight: 600;
      cursor: pointer;
      margin: 20px;
      font-size: 0.85rem;
    }

    .save-btn:hover {
      background: #218838;
    }

    @media print {
      .back-btn, .save-btn {
        display: none;
      }
      
      body {
        background: #fff;
        padding: 0;
      }
    }
  </style>
</head>
<body>
  <a href="{{ route('student.general.task') }}" class="back-btn">
    <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
      <path fill-rule="evenodd" d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8z"/>
    </svg>
    Back to General Tasks
  </a>

  <div class="form-container">
    <div class="form-header">
      <div class="form-title">
        TASKING: <span class="team-name">DINING TEAM</span>
      </div>
      <div class="form-info">
        <div class="info-group">
          <span class="info-label">Coordinator:</span>
          <input type="text" class="info-input" placeholder="Name">
        </div>
        <div class="info-group">
          <span class="info-label">Coordinator's Room #:</span>
          <input type="text" class="info-input" placeholder="Room">
        </div>
        <div class="info-group">
          <span class="info-label">DATE DURATION:</span>
          <input type="text" class="info-input" placeholder="Date">
        </div>
        <div class="info-group">
          <span class="info-label">C2025:</span>
          <input type="text" class="info-input" placeholder="Name">
        </div>
        <div class="info-group">
          <span class="info-label">Coordinator's Room #:</span>
          <input type="text" class="info-input" placeholder="Room">
        </div>
        <div class="info-group">
          <span class="info-label">DATE DURATION:</span>
          <input type="text" class="info-input" placeholder="Date">
        </div>
      </div>
    </div>

    <table class="tasking-table">
      <thead>
        <tr>
          <th rowspan="2" style="width: 80px;">MEAL/DAY</th>
          <th class="day-header">Monday</th>
          <th class="day-header">Tuesday</th>
          <th class="day-header">Wednesday</th>
          <th class="day-header">Thursday</th>
          <th class="day-header">Friday</th>
          <th class="day-header">Saturday</th>
          <th class="day-header">Sunday</th>
        </tr>
        <tr>
          <td style="font-size: 0.7rem; color: #666;">2025</td>
          <td style="font-size: 0.7rem; color: #666;">2025</td>
          <td style="font-size: 0.7rem; color: #666;">2025</td>
          <td style="font-size: 0.7rem; color: #666;">2025</td>
          <td style="font-size: 0.7rem; color: #666;">2025</td>
          <td style="font-size: 0.7rem; color: #666;">2025</td>
          <td style="font-size: 0.7rem; color: #666;">2025</td>
        </tr>
      </thead>
      <tbody>
        <!-- Breakfast Row 1 -->
        <tr>
          <td rowspan="2" class="meal-header">Breakfast</td>
          <td class="task-cell" onclick="openAssignmentModal('breakfast', 'monday', '2025')"><div class="task-input" id="breakfast-monday-2025">Click to assign</div></td>
          <td class="task-cell" onclick="openAssignmentModal('breakfast', 'tuesday', '2025')"><div class="task-input" id="breakfast-tuesday-2025">Click to assign</div></td>
          <td class="task-cell" onclick="openAssignmentModal('breakfast', 'wednesday', '2025')"><div class="task-input" id="breakfast-wednesday-2025">Click to assign</div></td>
          <td class="task-cell" onclick="openAssignmentModal('breakfast', 'thursday', '2025')"><div class="task-input" id="breakfast-thursday-2025">Click to assign</div></td>
          <td class="task-cell" onclick="openAssignmentModal('breakfast', 'friday', '2025')"><div class="task-input" id="breakfast-friday-2025">Click to assign</div></td>
          <td class="task-cell" onclick="openAssignmentModal('breakfast', 'saturday', '2025')"><div class="task-input" id="breakfast-saturday-2025">Click to assign</div></td>
          <td class="task-cell" onclick="openAssignmentModal('breakfast', 'sunday', '2025')"><div class="task-input" id="breakfast-sunday-2025">Click to assign</div></td>
        </tr>
        <!-- Breakfast Row 2 -->
        <tr>
          <td class="task-cell" onclick="openAssignmentModal('breakfast', 'monday', '2026')"><div class="task-input" id="breakfast-monday-2026">Click to assign</div></td>
          <td class="task-cell" onclick="openAssignmentModal('breakfast', 'tuesday', '2026')"><div class="task-input" id="breakfast-tuesday-2026">Click to assign</div></td>
          <td class="task-cell" onclick="openAssignmentModal('breakfast', 'wednesday', '2026')"><div class="task-input" id="breakfast-wednesday-2026">Click to assign</div></td>
          <td class="task-cell" onclick="openAssignmentModal('breakfast', 'thursday', '2026')"><div class="task-input" id="breakfast-thursday-2026">Click to assign</div></td>
          <td class="task-cell" onclick="openAssignmentModal('breakfast', 'friday', '2026')"><div class="task-input" id="breakfast-friday-2026">Click to assign</div></td>
          <td class="task-cell" onclick="openAssignmentModal('breakfast', 'saturday', '2026')"><div class="task-input" id="breakfast-saturday-2026">Click to assign</div></td>
          <td class="task-cell" onclick="openAssignmentModal('breakfast', 'sunday', '2026')"><div class="task-input" id="breakfast-sunday-2026">Click to assign</div></td>
        </tr>
        
        <!-- Lunch Row 1 -->
        <tr>
          <td rowspan="2" class="meal-header">Lunch</td>
          <td class="task-cell" onclick="openAssignmentModal('lunch', 'monday', '2025')"><div class="task-input" id="lunch-monday-2025">Click to assign</div></td>
          <td class="task-cell" onclick="openAssignmentModal('lunch', 'tuesday', '2025')"><div class="task-input" id="lunch-tuesday-2025">Click to assign</div></td>
          <td class="task-cell" onclick="openAssignmentModal('lunch', 'wednesday', '2025')"><div class="task-input" id="lunch-wednesday-2025">Click to assign</div></td>
          <td class="task-cell" onclick="openAssignmentModal('lunch', 'thursday', '2025')"><div class="task-input" id="lunch-thursday-2025">Click to assign</div></td>
          <td class="task-cell" onclick="openAssignmentModal('lunch', 'friday', '2025')"><div class="task-input" id="lunch-friday-2025">Click to assign</div></td>
          <td class="task-cell" onclick="openAssignmentModal('lunch', 'saturday', '2025')"><div class="task-input" id="lunch-saturday-2025">Click to assign</div></td>
          <td class="task-cell" onclick="openAssignmentModal('lunch', 'sunday', '2025')"><div class="task-input" id="lunch-sunday-2025">Click to assign</div></td>
        </tr>
        <!-- Lunch Row 2 -->
        <tr>
          <td class="task-cell" onclick="openAssignmentModal('lunch', 'monday', '2026')"><div class="task-input" id="lunch-monday-2026">Click to assign</div></td>
          <td class="task-cell" onclick="openAssignmentModal('lunch', 'tuesday', '2026')"><div class="task-input" id="lunch-tuesday-2026">Click to assign</div></td>
          <td class="task-cell" onclick="openAssignmentModal('lunch', 'wednesday', '2026')"><div class="task-input" id="lunch-wednesday-2026">Click to assign</div></td>
          <td class="task-cell" onclick="openAssignmentModal('lunch', 'thursday', '2026')"><div class="task-input" id="lunch-thursday-2026">Click to assign</div></td>
          <td class="task-cell" onclick="openAssignmentModal('lunch', 'friday', '2026')"><div class="task-input" id="lunch-friday-2026">Click to assign</div></td>
          <td class="task-cell" onclick="openAssignmentModal('lunch', 'saturday', '2026')"><div class="task-input" id="lunch-saturday-2026">Click to assign</div></td>
          <td class="task-cell" onclick="openAssignmentModal('lunch', 'sunday', '2026')"><div class="task-input" id="lunch-sunday-2026">Click to assign</div></td>
        </tr>
        
        <!-- Dinner Row 1 -->
        <tr>
          <td rowspan="2" class="meal-header">Dinner</td>
          <td class="task-cell" onclick="openAssignmentModal('dinner', 'monday', '2025')"><div class="task-input" id="dinner-monday-2025">Click to assign</div></td>
          <td class="task-cell" onclick="openAssignmentModal('dinner', 'tuesday', '2025')"><div class="task-input" id="dinner-tuesday-2025">Click to assign</div></td>
          <td class="task-cell" onclick="openAssignmentModal('dinner', 'wednesday', '2025')"><div class="task-input" id="dinner-wednesday-2025">Click to assign</div></td>
          <td class="task-cell" onclick="openAssignmentModal('dinner', 'thursday', '2025')"><div class="task-input" id="dinner-thursday-2025">Click to assign</div></td>
          <td class="task-cell" onclick="openAssignmentModal('dinner', 'friday', '2025')"><div class="task-input" id="dinner-friday-2025">Click to assign</div></td>
          <td class="task-cell" onclick="openAssignmentModal('dinner', 'saturday', '2025')"><div class="task-input" id="dinner-saturday-2025">Click to assign</div></td>
          <td class="task-cell" onclick="openAssignmentModal('dinner', 'sunday', '2025')"><div class="task-input" id="dinner-sunday-2025">Click to assign</div></td>
        </tr>
        <!-- Dinner Row 2 -->
        <tr>
          <td class="task-cell" onclick="openAssignmentModal('dinner', 'monday', '2026')"><div class="task-input" id="dinner-monday-2026">Click to assign</div></td>
          <td class="task-cell" onclick="openAssignmentModal('dinner', 'tuesday', '2026')"><div class="task-input" id="dinner-tuesday-2026">Click to assign</div></td>
          <td class="task-cell" onclick="openAssignmentModal('dinner', 'wednesday', '2026')"><div class="task-input" id="dinner-wednesday-2026">Click to assign</div></td>
          <td class="task-cell" onclick="openAssignmentModal('dinner', 'thursday', '2026')"><div class="task-input" id="dinner-thursday-2026">Click to assign</div></td>
          <td class="task-cell" onclick="openAssignmentModal('dinner', 'friday', '2026')"><div class="task-input" id="dinner-friday-2026">Click to assign</div></td>
          <td class="task-cell" onclick="openAssignmentModal('dinner', 'saturday', '2026')"><div class="task-input" id="dinner-saturday-2026">Click to assign</div></td>
          <td class="task-cell" onclick="openAssignmentModal('dinner', 'sunday', '2026')"><div class="task-input" id="dinner-sunday-2026">Click to assign</div></td>
        </tr>
      </tbody>
    </table>

    <button class="save-btn" onclick="saveForm()">Save Tasking Form</button>
  </div>

  <!-- Assignment Modal -->
  <div id="assignmentModal" class="assignment-modal">
    <div class="assignment-modal-content">
      <div class="assignment-header" id="modalHeader">Assign Students</div>

      <div id="studentAssignments">
        <!-- Dynamic student assignment forms will be added here -->
      </div>

      <button class="add-student-btn" onclick="addStudentAssignment()">+ Add Student</button>

      <div class="modal-buttons">
        <button class="save-assignment-btn" onclick="saveAssignments()">Save Assignments</button>
        <button class="cancel-assignment-btn" onclick="closeAssignmentModal()">Cancel</button>
      </div>
    </div>
  </div>

  <script>
    let currentAssignment = {
      meal: '',
      day: '',
      year: '',
      assignments: []
    };

    // Student data - will be populated from your database
    const students = {
      '2025': [],
      '2026': []
    };

    // Dining tasks based on your requirements
    const diningTasks = [
      'Set up the dining area ahead of time',
      'Distributed the food equally',
      'Properly wiped the tables after mealtime',
      'Rang the bell or announce to batchmates that it\'s mealtime',
      'Swept the dining area',
      'Arranged and cleaned the dining area after mealtime (chairs, tables, and dishes)',
      'Packed the lunch of batchmates on time',
      'Gathered all the dishes for washing'
    ];

    function openAssignmentModal(meal, day, year) {
      currentAssignment = { meal, day, year, assignments: [] };

      document.getElementById('modalHeader').textContent =
        `Assign Students - ${meal.charAt(0).toUpperCase() + meal.slice(1)} - ${day.charAt(0).toUpperCase() + day.slice(1)} - ${year}`;

      // Load existing assignments if any
      loadExistingAssignments();

      document.getElementById('assignmentModal').style.display = 'block';
    }

    function closeAssignmentModal() {
      document.getElementById('assignmentModal').style.display = 'none';
      document.getElementById('studentAssignments').innerHTML = '';
    }

    function addStudentAssignment() {
      const assignmentDiv = document.createElement('div');
      assignmentDiv.className = 'student-assignment';
      assignmentDiv.innerHTML = `
        <button class="remove-student-btn" onclick="removeStudentAssignment(this)">Remove</button>
        <select class="student-select" onchange="updateAssignment()">
          <option value="">Select Student</option>
          ${students[currentAssignment.year]?.map(student =>
            `<option value="${student}">${student}</option>`
          ).join('') || ''}
        </select>
        <select class="task-select" onchange="updateAssignment()">
          <option value="">Select Task</option>
          ${diningTasks.map(task =>
            `<option value="${task}">${task}</option>`
          ).join('')}
        </select>
      `;

      document.getElementById('studentAssignments').appendChild(assignmentDiv);
    }

    function removeStudentAssignment(button) {
      button.parentElement.remove();
      updateAssignment();
    }

    function updateAssignment() {
      const assignments = [];
      const assignmentDivs = document.querySelectorAll('.student-assignment');

      assignmentDivs.forEach(div => {
        const student = div.querySelector('.student-select').value;
        const task = div.querySelector('.task-select').value;

        if (student && task) {
          assignments.push({ student, task });
        }
      });

      currentAssignment.assignments = assignments;
    }

    function saveAssignments() {
      updateAssignment();

      if (currentAssignment.assignments.length === 0) {
        alert('Please add at least one student assignment.');
        return;
      }

      // Update the cell display
      const cellId = `${currentAssignment.meal}-${currentAssignment.day}-${currentAssignment.year}`;
      const cell = document.getElementById(cellId);

      if (cell) {
        if (currentAssignment.assignments.length === 1) {
          // Single assignment - show student name only
          const assignment = currentAssignment.assignments[0];
          cell.innerHTML = `<strong>${assignment.student}</strong>`;
          cell.title = `${assignment.student}: ${assignment.task}`;
        } else if (currentAssignment.assignments.length > 1) {
          // Multiple assignments - show count and names
          const studentNames = currentAssignment.assignments.map(a => a.student);
          cell.innerHTML = `<strong>${studentNames.length} Students</strong><br><small>${studentNames.join(', ')}</small>`;
          cell.title = currentAssignment.assignments.map(a =>
            `${a.student}: ${a.task}`
          ).join('\n');
        } else {
          cell.innerHTML = 'Click to assign';
          cell.title = '';
        }
      }

      // Here you would typically save to database
      console.log('Saving assignments:', currentAssignment);

      closeAssignmentModal();
    }

    function loadExistingAssignments() {
      // Clear existing assignments
      document.getElementById('studentAssignments').innerHTML = '';

      // Add one empty assignment form to start
      addStudentAssignment();
    }

    function saveForm() {
      alert('Tasking form saved successfully!');
      // Here you can add actual save functionality
    }

    // Close modal when clicking outside
    window.onclick = function(event) {
      const modal = document.getElementById('assignmentModal');
      if (event.target === modal) {
        closeAssignmentModal();
      }
    }
  </script>
</body>
</html>