custom.css body {
    font-family: 'Poppins', sans-serif;
    margin: 0;
    padding: 0;
    font-size: 20px;
    background-color: #f8f8f8;
}

header {
    background-color: #22BBEA;
    color: white;
    padding: 45px;
}

.logo {
          margin-left: none;
        }

        .logo img {
            width: 500px;
            height: auto;
            margin-left: none;
        }

h1.page-title {
    text-align: center; 
    margin-top: 20px; 
}


.container {
    display: flex;
}

.sidebar {
    width: 220px;
    background-color: #fa5408;
    padding: 20px;
    min-height: 100vh;
    border-right: 1px solid #ccc;
}

.sidebar h3 {
    margin-top: 0;
}

.sidebar ul {
    list-style: none;
    padding-left: 0;
}

.sidebar ul li {
    margin: 15px 0;
}

.sidebar ul li:hover {
    background: #e4733f;
    max-width: 100%;
    border-radius: 5px;
    transition: background-color 0.3s ease;
}

.sidebar ul li a {
    text-decoration: none;
    color: #000000;
    padding: 10px;
    display: block;
    border-radius: 5px;
}

.content {
    flex: 1;
    padding: 30px;
    font-display: center;
}

table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
}

th, td {
    border: 2px solid black;
    padding: 10px;
    text-align: center;
}

th {
    background-color: #22BBEA;
}

.status-select {
    font-size: 18px;
    padding: 4px 8px;
    background-color: #22BBEA;
    color: rgb(0, 0, 0);
    border: 1px solid #ccc;
    border-radius: 4px;
    cursor: pointer;
}

.status-select option {
    background-color: rgb(178, 238, 240); /* Default background for options */
    color: black; /* Default text color for options */
}

.status-select option:checked {
    background-color: rgb(255, 255, 255); /* Ensures the selected option stays green */
    color: black; /* Optional: Change text color for better contrast */
}

.status-select option[value="checked"] {
   background-color:rgb(255, 255, 255);
    color: black;
}


.day-nav {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 20px;
    margin-top: 10px;
    font-size: 20px;
}

.day-nav button {
    padding: 8px 14px;
    font-size: 18px;
    background-color: #22BBEA;
    color: white;
    border: none;
    cursor: pointer;
    border-radius: 5px;
}

.day-nav span {
    font-weight: bold;
}

body, h1, h2, h3, h4, h5, h6, p, input, button, textarea, select, label, span {
    font-family: 'Poppins', sans-serif;
}

/* === TASK BUTTONS === */
.task-buttons {
    margin-top: 20px;
}

.task-buttons button {
    background-color: #3490dc;
    color: white;
    border: none;
    padding: 10px 18px;
    margin-right: 10px;
    border-radius: 5px;
    font-weight: 600;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

.task-buttons button:hover {
    background-color: #2779bd;
}

/* === FORM STYLES === */
#taskFormContainer {
    margin-top: 20px;
    background: #f9f9f9;
    padding: 20px;
    border-radius: 8px;
    max-width: 550px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
}

#taskForm label {
    display: block;
    margin-top: 10px;
    font-weight: 500;
}

#taskForm input,
#taskForm select,
#taskForm textarea {
    width: 100%;
    padding: 10px;
    margin-top: 5px;
    border: 1px solid #ccc;
    border-radius: 4px;
    font-size: 14px;
}

#taskForm button[type="submit"],
#taskForm button[type="button"] {
    margin-top: 15px;
    padding: 10px 16px;
    font-weight: 600;
    color: black;
    border: none;
    border-radius: 5px;
    cursor: pointer;
}

#taskForm button[type="submit"] {
    background-color: #31b93c; /* green */
    color: black;
    margin-right: 10px;
}

#taskForm button[type="submit"]:hover {
    background-color: #3bc552;
}

#taskForm button[type="button"] {
    background-color: #e3342f; /* red */
    color:black;
}

#taskForm button[type="button"]:hover {
    background-color: #cc1f1a;
}

.feedback-section {
    margin-top: 40px;
    padding: 20px;
    background-color: #eaf7fb;
    border: 1px solid #ccc;
    border-radius: 10px;
}

.feedback-section h3 {
    font-family: 'Poppins', sans-serif;
    margin-bottom: 15px;
}

textarea {
    font-family: 'Poppins', sans-serif;
    width: 100%;
    padding: 10px;
    font-size: 16px;
    resize: vertical;
    border: 1px solid #ccc;
    border-radius: 5px;
}

.custom-file-input input[type="file"] {
    display: none; 
}

.custom-file-input label {
    display: inline-block;
    padding: 5px 10px;
    background-color:none; 
    color: rgb(20, 20, 20);
    border-radius: 5px;
    cursor: pointer;
    font-family: 'Poppins', sans-serif;
    font-size: 14px;
}
.custom-file-input label {
    background-color: #ffffff; 
    border: 1px solid #3a3a3a; 
    color: rgb(2, 2, 2); 
}


.file-name {
    display: inline-block;
    margin-left: 10px;
    font-size: 16px;
    font-family: 'Poppins', sans-serif;
    color: #333;
}

button[type="submit"] {
    margin-top: 15px;
    background-color: #22BBEA;
    color: rgb(0, 0, 0);
    border: none;
    padding: 10px 18px;
    border-radius: 5px;
    cursor: pointer;
    font-family: 'Poppins', sans-serif;
    font-size: 16px;
}

button[type="submit"]:hover {
    font-family: 'Poppins', sans-serif;
    background-color: #199cc9;
}

.feedback {
    font-family: 'Poppins', sans-serif;
    width: 100%;
    max-width: 700px;
    margin-top: 20px;
    padding: 15px;
    border: 2px solid black;
    background-color:rgb(245, 245, 245);
    text-align: left;
}

.feedback h2 {
    font-family: 'Poppins', sans-serif;
    margin-bottom: 10px;
    font-size: 22px;
}

.feedback textarea {
    font-family: 'Poppins', sans-serif;
    width: 95%;
    height: 80px;
    padding: 10px;
    border: 1px solid black;
    font-size: 18px;
}

.feedback input[type="file"] {
    font-family: 'Poppins', sans-serif;
    font-size: 25px;
    margin-top: 10px;
}

.feedback button {
    font-family: 'Poppins', sans-serif;
    display: block;
    width: 100%;
    padding: 10px;
    margin-top: 10px;
    background: rgb(67, 193, 231);
    color: black;
    border: none;
    cursor: pointer;
    font-size: 18px;
}

.feedback button:hover {
    background: rgb(50, 170, 210);
}


.hidden {
    display: none; /* Hide the form by default */
}
You sent
roomtask.blade.php <!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Room Checklist - Tasking Hub System</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="{{ asset('css/custom.css') }}" />
  <script src="{{ asset('js/roomtask.js') }}" defer></script>
</head>
<body>
  <header>
  <div class="logo">
    <img src="{{ asset('images/pnlogo-header.png') }}" alt="PN Logo">
    </div>
  </header>
  <div class="container">
    <div class="sidebar">
      <ul>
        <li><a href="{{ route('dashboard') }}" class="sidebar-link">Dashboard</a></li>
        <li><a href="#">Room Tasks</a></li>
        <li><a href="#">General Tasks</a></li>
        <li><a href="#">Reports</a></li>
        <li><a href="#">Settings</a></li>
      </ul>
    </div>

    <div class="content">
      <h1 class="page-title">ROOM TASK ASSIGNMENTS and CHECKLIST</h1>

      <div class="day-nav">
        <button id="prevDayButton">&laquo;</button>
        <span id="currentDay">Monday</span>
        <button id="nextDayButton">&raquo;</button>
      </div>

      <table>
        <thead>
          <tr>
            <th>Student Name</th>
            <th>Assigned Area</th>
            <th>Description</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody id="taskTableBody"></tbody>
      </table>

      <div class="task-buttons">
        <button id="addTaskButton" onclick="openForm('add')">Add Task</button>
        <button id="editTaskButton" onclick="openForm('edit')">Edit Task</button>
      </div>

      <!-- Task Form -->
      <div id="taskFormContainer" class="hidden">
        <h3 id="formTitle">Add Task</h3>
        <form id="taskForm" onsubmit="saveTask(event)">
          <div id="taskNameContainer">
            <input type="text" id="taskName" name="taskName" placeholder="Enter name" required />
          </div>

          <label for="taskArea">Area:</label>
          <input type="text" id="taskArea" name="taskArea" placeholder="Enter area" required />

          <label for="taskDesc">Description:</label>
          <textarea id="taskDesc" name="taskDesc" placeholder="Enter description" rows="4" required></textarea>

          <button type="submit">Save Task</button>
          <button type="button" onclick="closeForm()">Cancel</button>
        </form>
      </div>

      <div class="feedback-section">
        <h3>Task Feedback</h3>
        <form method="POST" action="#" enctype="multipart/form-data" onsubmit="return validateForm()">
          <textarea name="feedback" rows="4" placeholder="Write your feedback here..."></textarea><br />
          <div class="custom-file-input">
            <label for="feedback_file">Choose Files (Minimum 3 Photos)</label>
            <input type="file" id="feedback_file" name="feedback_files[]" multiple accept="image/*" onchange="showFileName(this)" />
            <span class="file-name" id="fileName">No files chosen</span>
          </div>
          <button type="submit">Submit Feedback</button>
        </form>
      </div>
    </div>
  </div>

  <script>
    const tasksByDay = @json($tasksByDay);
    const days = @json($daysOfWeek);
    let currentIndex = 0;
    const currentDayElement = document.getElementById("currentDay");
    const taskTableBody = document.getElementById("taskTableBody");

    function loadTasks(day) {
      currentDayElement.textContent = day;
      taskTableBody.innerHTML = "";

      tasksByDay[day].forEach(task => {
        const row = document.createElement("tr");
        row.innerHTML = `
          <td>${task.name}</td>
          <td>${task.area}</td>
          <td>${task.desc}</td>
          <td>
            <select class="status-select" onchange="updateSelectColor(this)">
              <option value="not yet done">Not yet</option>
              <option value="checked">CLEANED</option>
              <option value="wrong">DIRTY</option>
            </select>
          </td>
        `;
        taskTableBody.appendChild(row);
      });
    }

    function prevDay() {
      currentIndex = (currentIndex === 0) ? days.length - 1 : currentIndex - 1;
      loadTasks(days[currentIndex]);
    }

    function nextDay() {
      currentIndex = (currentIndex === days.length - 1) ? 0 : currentIndex + 1;
      loadTasks(days[currentIndex]);
    }

    document.getElementById("prevDayButton").addEventListener("click", prevDay);
    document.getElementById("nextDayButton").addEventListener("click", nextDay);

    loadTasks(days[currentIndex]);

    function updateSelectColor(selectElement) {
      const value = selectElement.value;
      selectElement.style.backgroundColor =
        value === "checked" ? "green" :
        value === "wrong" ? "red" : "white";
    }

    function showFileName(input) {
      const fileNameDisplay = document.getElementById("fileName");
      const files = input.files;

      if (files.length === 0) {
        fileNameDisplay.textContent = "No files chosen";
      } else {
        const fileNames = Array.from(files).map(file => file.name).join(", ");
        fileNameDisplay.textContent = fileNames;
      }
    }

    let currentMode = 'add';
    let editTaskIndex = null;

    function openForm(mode) {
      currentMode = mode;
      const formContainer = document.getElementById('taskFormContainer');
      const title = document.getElementById('formTitle');
      const taskNameContainer = document.getElementById('taskNameContainer');

      if (mode === 'add') {
        title.textContent = 'Add Task';
        taskNameContainer.innerHTML = `
          <input type="text" id="taskName" name="taskName" placeholder="Enter name" required />
        `;
        document.getElementById('taskForm').reset();
      } else if (mode === 'edit') {
        title.textContent = 'Edit Task';
        taskNameContainer.innerHTML = `
          <label for="taskNameDropdown">Select Name to Edit:</label>
          <select id="taskNameDropdown" name="taskNameDropdown" required></select>
          <label for="taskName">Change Name To:</label>
          <input type="text" id="taskName" name="taskName" placeholder="Enter new name" required />
        `;

        const nameDropdown = document.getElementById('taskNameDropdown');
        const editableNameInput = document.getElementById('taskName');

        tasksByDay[days[currentIndex]].forEach((task, index) => {
          const option = document.createElement('option');
          option.value = index;
          option.textContent = task.name;
          nameDropdown.appendChild(option);
        });

        function loadSelected(index) {
          const selectedTask = tasksByDay[days[currentIndex]][index];
          editableNameInput.value = selectedTask.name;
          document.getElementById('taskArea').value = selectedTask.area;
          document.getElementById('taskDesc').value = selectedTask.desc;
          editTaskIndex = index;
        }

        loadSelected(0); // Load first task by default

        nameDropdown.addEventListener('change', function () {
          loadSelected(this.value);
        });
      }

      formContainer.classList.remove('hidden');
    }

    function closeForm() {
      document.getElementById('taskFormContainer').classList.add('hidden');
    }

    function saveTask(event) {
      event.preventDefault();

      const name = document.getElementById('taskName').value;
      const area = document.getElementById('taskArea').value;
      const desc = document.getElementById('taskDesc').value;

      if (currentMode === 'add') {
        tasksByDay[days[currentIndex]].push({ name, area, desc });
      } else if (currentMode === 'edit') {
        tasksByDay[days[currentIndex]][editTaskIndex] = { name, area, desc };
      }

      loadTasks(days[currentIndex]);
      closeForm();
    }
  </script>
</body>
</html>