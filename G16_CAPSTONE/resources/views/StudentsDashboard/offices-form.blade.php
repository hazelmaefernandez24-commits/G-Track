<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Offices & Conference Rooms Tasking Form</title>
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
      color: #007bff;
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
      padding: 6px;
      text-align: center;
      font-size: 0.8rem;
    }

    .tasking-table th {
      background: #f8f9fa;
      font-weight: 600;
    }

    .room-header {
      background: #e9ecef;
      font-weight: 600;
      text-align: left;
      padding: 8px;
      width: 120px;
    }

    .day-header {
      background: #f8f9fa;
      font-weight: 600;
      padding: 8px;
      writing-mode: vertical-lr;
      text-orientation: mixed;
      width: 60px;
    }

    .task-cell {
      height: 40px;
      vertical-align: top;
      position: relative;
      width: 60px;
    }

    .task-input {
      width: 100%;
      height: 100%;
      border: none;
      padding: 2px;
      font-family: 'Poppins', sans-serif;
      font-size: 0.75rem;
      resize: none;
      background: transparent;
      text-align: center;
      cursor: pointer;
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
      background: #007bff;
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
        TASKING: <span class="team-name">OFFICES & CONFERENCE ROOMS</span>
      </div>
      <div class="form-info">
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
        <div class="info-group">
          <span class="info-label">C2026:</span>
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
          <th style="width: 120px;">ROOM/DAY</th>
          <th class="day-header">Monday</th>
          <th class="day-header">Tuesday</th>
          <th class="day-header">Wednesday</th>
          <th class="day-header">Thursday</th>
          <th class="day-header">Friday</th>
          <th class="day-header">Saturday</th>
          <th class="day-header">Sunday</th>
        </tr>
      </thead>
      <tbody>
        <!-- Office 1 -->
        <tr>
          <td class="room-header">Office 1</td>
          <td class="task-cell" onclick="openAssignmentModal('office1', 'monday')"><div class="task-input" id="office1-monday">Click to assign</div></td>
          <td class="task-cell" onclick="openAssignmentModal('office1', 'tuesday')"><div class="task-input" id="office1-tuesday">Click to assign</div></td>
          <td class="task-cell" onclick="openAssignmentModal('office1', 'wednesday')"><div class="task-input" id="office1-wednesday">Click to assign</div></td>
          <td class="task-cell" onclick="openAssignmentModal('office1', 'thursday')"><div class="task-input" id="office1-thursday">Click to assign</div></td>
          <td class="task-cell" onclick="openAssignmentModal('office1', 'friday')"><div class="task-input" id="office1-friday">Click to assign</div></td>
          <td class="task-cell" onclick="openAssignmentModal('office1', 'saturday')"><div class="task-input" id="office1-saturday">Click to assign</div></td>
          <td class="task-cell" onclick="openAssignmentModal('office1', 'sunday')"><div class="task-input" id="office1-sunday">Click to assign</div></td>
        </tr>

        <!-- Office 2 -->
        <tr>
          <td class="room-header">Office 2</td>
          <td class="task-cell" onclick="openAssignmentModal('office2', 'monday')"><div class="task-input" id="office2-monday">Click to assign</div></td>
          <td class="task-cell" onclick="openAssignmentModal('office2', 'tuesday')"><div class="task-input" id="office2-tuesday">Click to assign</div></td>
          <td class="task-cell" onclick="openAssignmentModal('office2', 'wednesday')"><div class="task-input" id="office2-wednesday">Click to assign</div></td>
          <td class="task-cell" onclick="openAssignmentModal('office2', 'thursday')"><div class="task-input" id="office2-thursday">Click to assign</div></td>
          <td class="task-cell" onclick="openAssignmentModal('office2', 'friday')"><div class="task-input" id="office2-friday">Click to assign</div></td>
          <td class="task-cell" onclick="openAssignmentModal('office2', 'saturday')"><div class="task-input" id="office2-saturday">Click to assign</div></td>
          <td class="task-cell" onclick="openAssignmentModal('office2', 'sunday')"><div class="task-input" id="office2-sunday">Click to assign</div></td>
        </tr>
        
        <!-- Conference Room A -->
        <tr>
          <td class="room-header">Conference Room A</td>
          <td class="task-cell" onclick="openAssignmentModal('conferencea', 'monday')"><div class="task-input" id="conferencea-monday">Click to assign</div></td>
          <td class="task-cell" onclick="openAssignmentModal('conferencea', 'tuesday')"><div class="task-input" id="conferencea-tuesday">Click to assign</div></td>
          <td class="task-cell" onclick="openAssignmentModal('conferencea', 'wednesday')"><div class="task-input" id="conferencea-wednesday">Click to assign</div></td>
          <td class="task-cell" onclick="openAssignmentModal('conferencea', 'thursday')"><div class="task-input" id="conferencea-thursday">Click to assign</div></td>
          <td class="task-cell" onclick="openAssignmentModal('conferencea', 'friday')"><div class="task-input" id="conferencea-friday">Click to assign</div></td>
          <td class="task-cell" onclick="openAssignmentModal('conferencea', 'saturday')"><div class="task-input" id="conferencea-saturday">Click to assign</div></td>
          <td class="task-cell" onclick="openAssignmentModal('conferencea', 'sunday')"><div class="task-input" id="conferencea-sunday">Click to assign</div></td>
        </tr>

        <!-- Conference Room B -->
        <tr>
          <td class="room-header">Conference Room B</td>
          <td class="task-cell" onclick="openAssignmentModal('conferenceb', 'monday')"><div class="task-input" id="conferenceb-monday">Click to assign</div></td>
          <td class="task-cell" onclick="openAssignmentModal('conferenceb', 'tuesday')"><div class="task-input" id="conferenceb-tuesday">Click to assign</div></td>
          <td class="task-cell" onclick="openAssignmentModal('conferenceb', 'wednesday')"><div class="task-input" id="conferenceb-wednesday">Click to assign</div></td>
          <td class="task-cell" onclick="openAssignmentModal('conferenceb', 'thursday')"><div class="task-input" id="conferenceb-thursday">Click to assign</div></td>
          <td class="task-cell" onclick="openAssignmentModal('conferenceb', 'friday')"><div class="task-input" id="conferenceb-friday">Click to assign</div></td>
          <td class="task-cell" onclick="openAssignmentModal('conferenceb', 'saturday')"><div class="task-input" id="conferenceb-saturday">Click to assign</div></td>
          <td class="task-cell" onclick="openAssignmentModal('conferenceb', 'sunday')"><div class="task-input" id="conferenceb-sunday">Click to assign</div></td>
        </tr>
        
        <!-- Meeting Room -->
        <tr>
          <td class="room-header">Meeting Room</td>
          <td class="task-cell" onclick="openAssignmentModal('meeting', 'monday')"><div class="task-input" id="meeting-monday">Click to assign</div></td>
          <td class="task-cell" onclick="openAssignmentModal('meeting', 'tuesday')"><div class="task-input" id="meeting-tuesday">Click to assign</div></td>
          <td class="task-cell" onclick="openAssignmentModal('meeting', 'wednesday')"><div class="task-input" id="meeting-wednesday">Click to assign</div></td>
          <td class="task-cell" onclick="openAssignmentModal('meeting', 'thursday')"><div class="task-input" id="meeting-thursday">Click to assign</div></td>
          <td class="task-cell" onclick="openAssignmentModal('meeting', 'friday')"><div class="task-input" id="meeting-friday">Click to assign</div></td>
          <td class="task-cell" onclick="openAssignmentModal('meeting', 'saturday')"><div class="task-input" id="meeting-saturday">Click to assign</div></td>
          <td class="task-cell" onclick="openAssignmentModal('meeting', 'sunday')"><div class="task-input" id="meeting-sunday">Click to assign</div></td>
        </tr>

        <!-- Reception Area -->
        <tr>
          <td class="room-header">Reception Area</td>
          <td class="task-cell" onclick="openAssignmentModal('reception', 'monday')"><div class="task-input" id="reception-monday">Click to assign</div></td>
          <td class="task-cell" onclick="openAssignmentModal('reception', 'tuesday')"><div class="task-input" id="reception-tuesday">Click to assign</div></td>
          <td class="task-cell" onclick="openAssignmentModal('reception', 'wednesday')"><div class="task-input" id="reception-wednesday">Click to assign</div></td>
          <td class="task-cell" onclick="openAssignmentModal('reception', 'thursday')"><div class="task-input" id="reception-thursday">Click to assign</div></td>
          <td class="task-cell" onclick="openAssignmentModal('reception', 'friday')"><div class="task-input" id="reception-friday">Click to assign</div></td>
          <td class="task-cell" onclick="openAssignmentModal('reception', 'saturday')"><div class="task-input" id="reception-saturday">Click to assign</div></td>
          <td class="task-cell" onclick="openAssignmentModal('reception', 'sunday')"><div class="task-input" id="reception-sunday">Click to assign</div></td>
        </tr>

        <!-- Admin Office -->
        <tr>
          <td class="room-header">Admin Office</td>
          <td class="task-cell" onclick="openAssignmentModal('admin', 'monday')"><div class="task-input" id="admin-monday">Click to assign</div></td>
          <td class="task-cell" onclick="openAssignmentModal('admin', 'tuesday')"><div class="task-input" id="admin-tuesday">Click to assign</div></td>
          <td class="task-cell" onclick="openAssignmentModal('admin', 'wednesday')"><div class="task-input" id="admin-wednesday">Click to assign</div></td>
          <td class="task-cell" onclick="openAssignmentModal('admin', 'thursday')"><div class="task-input" id="admin-thursday">Click to assign</div></td>
          <td class="task-cell" onclick="openAssignmentModal('admin', 'friday')"><div class="task-input" id="admin-friday">Click to assign</div></td>
          <td class="task-cell" onclick="openAssignmentModal('admin', 'saturday')"><div class="task-input" id="admin-saturday">Click to assign</div></td>
          <td class="task-cell" onclick="openAssignmentModal('admin', 'sunday')"><div class="task-input" id="admin-sunday">Click to assign</div></td>
        </tr>
        
        <!-- Storage Room -->
        <tr>
          <td class="room-header">Storage Room</td>
          <td class="task-cell"><textarea class="task-input" placeholder="Name"></textarea></td>
          <td class="task-cell"><textarea class="task-input" placeholder="Name"></textarea></td>
          <td class="task-cell"><textarea class="task-input" placeholder="Name"></textarea></td>
          <td class="task-cell"><textarea class="task-input" placeholder="Name"></textarea></td>
          <td class="task-cell"><textarea class="task-input" placeholder="Name"></textarea></td>
          <td class="task-cell"><textarea class="task-input" placeholder="Name"></textarea></td>
          <td class="task-cell"><textarea class="task-input" placeholder="Name"></textarea></td>
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
      room: '',
      day: '',
      assignments: []
    };

    // Student data - will be populated from your database
    const students = {
      '2025': [],
      '2026': []
    };

    // Office tasks based on your requirements
    const officeTasks = [
      'Properly cleaned and brushed the toilet, sink, and shower room, including the tiles',
      'Swept the floor',
      'Mopped the floor',
      'Wiped the tables and chairs (dust-free)',
      'Wiped the mirror with a cloth or paper',
      'Wiped the cabinets (dust-free)',
      'Cleaned and organized the plates, glasses, and spoons',
      'Ensured the pail in the toilet is full of water',
      'Cleaned the window',
      'Cleaned and organized the electronic devices'
    ];

    function openAssignmentModal(room, day) {
      currentAssignment = { room, day, assignments: [] };

      const roomNames = {
        'office1': 'Office 1',
        'office2': 'Office 2',
        'conferencea': 'Conference Room A',
        'conferenceb': 'Conference Room B',
        'meeting': 'Meeting Room',
        'reception': 'Reception Area',
        'admin': 'Admin Office'
      };

      document.getElementById('modalHeader').textContent =
        `Assign Students - ${roomNames[room]} - ${day.charAt(0).toUpperCase() + day.slice(1)}`;

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
          <optgroup label="Class 2025">
            ${students['2025']?.map(student =>
              `<option value="${student}">${student}</option>`
            ).join('') || ''}
          </optgroup>
          <optgroup label="Class 2026">
            ${students['2026']?.map(student =>
              `<option value="${student}">${student}</option>`
            ).join('') || ''}
          </optgroup>
        </select>
        <select class="task-select" onchange="updateAssignment()">
          <option value="">Select Task</option>
          ${officeTasks.map(task =>
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
      const cellId = `${currentAssignment.room}-${currentAssignment.day}`;
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
      alert('Offices & Conference Rooms tasking form saved successfully!');
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