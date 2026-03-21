<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Ground Floor Tasking Form</title>
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
    }

    .task-input:focus {
      outline: 2px solid #22BBEA;
      background: #f0f8ff;
    }

    .back-btn {
      position: fixed;
      top: 20px;
      left: 20px;
      background: #22BBEA;
      color: #fff;
      border: none;
      padding: 6px 12px;
      border-radius: 4px;
      font-family: 'Poppins', sans-serif;
      font-weight: 500;
      font-size: 0.8rem;
      cursor: pointer;
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      gap: 6px;
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
        TASKING: <span class="team-name">GROUNDFLOOR</span>
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
          <td class="task-cell"><textarea class="task-input" placeholder="2025"></textarea></td>
          <td class="task-cell"><textarea class="task-input" placeholder="2025"></textarea></td>
          <td class="task-cell"><textarea class="task-input" placeholder="2025"></textarea></td>
          <td class="task-cell"><textarea class="task-input" placeholder="2025"></textarea></td>
          <td class="task-cell"><textarea class="task-input" placeholder="2025"></textarea></td>
          <td class="task-cell"><textarea class="task-input" placeholder="2025"></textarea></td>
          <td class="task-cell"><textarea class="task-input" placeholder="2025"></textarea></td>
        </tr>
        <!-- Breakfast Row 2 -->
        <tr>
          <td class="task-cell"><textarea class="task-input" placeholder="2026"></textarea></td>
          <td class="task-cell"><textarea class="task-input" placeholder="2026"></textarea></td>
          <td class="task-cell"><textarea class="task-input" placeholder="2026"></textarea></td>
          <td class="task-cell"><textarea class="task-input" placeholder="2026"></textarea></td>
          <td class="task-cell"><textarea class="task-input" placeholder="2026"></textarea></td>
          <td class="task-cell"><textarea class="task-input" placeholder="2026"></textarea></td>
          <td class="task-cell"><textarea class="task-input" placeholder="2026"></textarea></td>
        </tr>
        
        <!-- Lunch Row 1 -->
        <tr>
          <td rowspan="2" class="meal-header">Lunch</td>
          <td class="task-cell"><textarea class="task-input" placeholder="2025"></textarea></td>
          <td class="task-cell"><textarea class="task-input" placeholder="2025"></textarea></td>
          <td class="task-cell"><textarea class="task-input" placeholder="2025"></textarea></td>
          <td class="task-cell"><textarea class="task-input" placeholder="2025"></textarea></td>
          <td class="task-cell"><textarea class="task-input" placeholder="2025"></textarea></td>
          <td class="task-cell"><textarea class="task-input" placeholder="2025"></textarea></td>
          <td class="task-cell"><textarea class="task-input" placeholder="2025"></textarea></td>
        </tr>
        <!-- Lunch Row 2 -->
        <tr>
          <td class="task-cell"><textarea class="task-input" placeholder="2026"></textarea></td>
          <td class="task-cell"><textarea class="task-input" placeholder="2026"></textarea></td>
          <td class="task-cell"><textarea class="task-input" placeholder="2026"></textarea></td>
          <td class="task-cell"><textarea class="task-input" placeholder="2026"></textarea></td>
          <td class="task-cell"><textarea class="task-input" placeholder="2026"></textarea></td>
          <td class="task-cell"><textarea class="task-input" placeholder="2026"></textarea></td>
          <td class="task-cell"><textarea class="task-input" placeholder="2026"></textarea></td>
        </tr>
        
        <!-- Dinner Row 1 -->
        <tr>
          <td rowspan="2" class="meal-header">Dinner</td>
          <td class="task-cell"><textarea class="task-input" placeholder="2025"></textarea></td>
          <td class="task-cell"><textarea class="task-input" placeholder="2025"></textarea></td>
          <td class="task-cell"><textarea class="task-input" placeholder="2025"></textarea></td>
          <td class="task-cell"><textarea class="task-input" placeholder="2025"></textarea></td>
          <td class="task-cell"><textarea class="task-input" placeholder="2025"></textarea></td>
          <td class="task-cell"><textarea class="task-input" placeholder="2025"></textarea></td>
          <td class="task-cell"><textarea class="task-input" placeholder="2025"></textarea></td>
        </tr>
        <!-- Dinner Row 2 -->
        <tr>
          <td class="task-cell"><textarea class="task-input" placeholder="2026"></textarea></td>
          <td class="task-cell"><textarea class="task-input" placeholder="2026"></textarea></td>
          <td class="task-cell"><textarea class="task-input" placeholder="2026"></textarea></td>
          <td class="task-cell"><textarea class="task-input" placeholder="2026"></textarea></td>
          <td class="task-cell"><textarea class="task-input" placeholder="2026"></textarea></td>
          <td class="task-cell"><textarea class="task-input" placeholder="2026"></textarea></td>
          <td class="task-cell"><textarea class="task-input" placeholder="2026"></textarea></td>
        </tr>
      </tbody>
    </table>

    <button class="save-btn" onclick="saveForm()">Save Tasking Form</button>
  </div>

  <script>
    function saveForm() {
      alert('Ground Floor tasking form saved successfully!');
      // Here you can add actual save functionality
    }
  </script>
</body>
</html>