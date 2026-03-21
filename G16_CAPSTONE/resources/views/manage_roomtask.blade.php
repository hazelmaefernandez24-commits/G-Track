<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Manage Room Tasks</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
  <link rel="stylesheet" href="{{ asset('css/manage_roomtask.css') }}">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <!-- manage_roomtask styles are loaded from public/css/manage_roomtask.css -->
</head>
<body class="manage-roomtask">
  <header class="site-header">
    <div class="logo" style="padding:12px 14px; display:flex; align-items:center; gap:12px;">
      <img src="{{ asset('images/pnlogo-header.png') }}" alt="PN Logo" style="height:60px;">
    </div>
  </header>

  <div class="main-container">
    @include('partials.sidebar')

    <div class="content-container main-content">
      <h1 class="manage-title">Task Templates</h1>
      <p class="manage-sub">Create and manage reusable room tasks with detailed descriptions. You can assign these tasks to specific rooms from this page.</p>

      <div class="manage-card">
        <div class="manage-columns">
          <div class="manage-left">
            <form id="roomTaskForm">
              <input type="hidden" id="taskId" value="" />
              <div class="form-group">
                <label for="taskTitle">Task Title</label>
                <input id="taskTitle" class="form-control" required maxlength="120" />
              </div>
              <div class="form-group">
                <label for="taskDescription">Description</label>
                <textarea id="taskDescription" class="form-control" rows="4" maxlength="500"></textarea>
              </div>
              <div style="margin-top:10px; display:flex; gap:8px;">
                <button type="submit" class="btn btn-primary">Save Task</button>
                <button type="button" class="btn btn-secondary" onclick="resetRoomTaskForm()">Reset</button>
              </div>
            </form>
          </div>

          <div class="manage-right">
            <h4 style="margin-top:0;">Existing Tasks</h4>

            <!-- Selection controls: select all, room dropdown and apply button -->
            <div id="roomTaskControls" style="display:flex; gap:8px; align-items:center; margin-bottom:8px;">
              <label style="display:inline-flex; align-items:center; gap:8px; font-size:0.95rem; color:#333;">
                <input type="checkbox" id="selectAllTasks" /> Select all
              </label>
              <select id="roomSelect" style="padding:6px 8px; border-radius:6px; border:1px solid #ddd;">
                <option value="">-- Choose room --</option>
              </select>
              <button id="applySelectedBtn" class="btn btn-primary" style="min-width:170px;">Apply Selected to Room</button>
              <button id="deleteAllAppliedBtn" class="btn btn-danger" style="min-width:170px;">Delete All Applied Tasks</button>
            </div>

            <div id="roomTasksList" style="min-height:80px;"></div>
          </div>
        </div>

       </div>
    </div>
  </div>

  <script>
    // Re-create the client-side task manager that used to live in the sidebar modal.
    (function(){
  const STORAGE_KEY = 'roomTasks_v1';
  const ASSIGN_KEY = 'roomTaskAssignments_v1';

  // Rooms passed from server (controller can pass $rooms as an array of room numbers/ids)
  // Fallback to an empty array if not provided. Controller may supply `rooms` when rendering this view.
  const AVAILABLE_ROOMS = (typeof window !== 'undefined' && window.availableRooms) ? window.availableRooms : @json($rooms ?? []);

  // Selection state for checkboxes (task ids)
  const selectedTaskIds = new Set();

        function loadTasks() { try { return JSON.parse(localStorage.getItem(STORAGE_KEY) || '[]'); } catch(e){ return []; } }
        function saveTasks(list) { localStorage.setItem(STORAGE_KEY, JSON.stringify(list)); }
        function loadAssignments() { try { return JSON.parse(localStorage.getItem(ASSIGN_KEY) || '{}'); } catch(e){ return {}; } }
        function saveAssignments(obj) { localStorage.setItem(ASSIGN_KEY, JSON.stringify(obj)); }

        window.resetRoomTaskForm = function() {
            document.getElementById('taskId').value = '';
            document.getElementById('taskTitle').value = '';
            document.getElementById('taskDescription').value = '';
        }

        function uid() { return 't'+Date.now().toString(36)+Math.random().toString(36).slice(2,6); }

        function escapeHtml(s){ return String(s||'').replace(/[&<>"']/g, function(c){ return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":"&#39;"}[c]; }); }

        function renderRoomTasks(){
            const list = loadTasks();
            const container = document.getElementById('roomTasksList');
            if(!container) return;
            if(list.length === 0) { container.innerHTML = '<div style="color:#6c757d;">No tasks yet. Create one using the form.</div>'; return; }
            container.innerHTML = '';
            list.forEach(t => {
                const el = document.createElement('div');
                el.style.border = '1px solid #e6e9ef'; el.style.padding = '10px'; el.style.borderRadius = '6px'; el.style.marginBottom = '8px';
        // Include a per-task checkbox so user can select multiple tasks
        el.innerHTML = `
          <label style="display:flex; gap:10px; align-items:flex-start;">
          <input type="checkbox" id="taskCheckbox-${t.id}" ${selectedTaskIds.has(t.id) ? 'checked' : ''} onclick="window.toggleRoomTaskSelection('${t.id}', this.checked)" />
          <div style="flex:1;">
            <strong style="display:block;">${escapeHtml(t.title)}</strong>
            <div style="color:#6c757d; margin-top:6px; white-space:pre-wrap;">${escapeHtml(t.description||'')}</div>
          </div>
          </label>`;
                const actions = document.createElement('div');
                actions.style.marginTop = '8px';
                actions.innerHTML = `<button class="btn" style="margin-right:6px;" onclick="editRoomTask('${t.id}')">Edit</button>
                                     <button class="btn" style="margin-right:6px; background:#dc3545;color:#fff;border:none;" onclick="deleteRoomTask('${t.id}')">Delete</button>`;
                el.appendChild(actions);
                container.appendChild(el);
            });

      // Keep the select-all checkbox state in sync
      const selectAllEl = document.getElementById('selectAllTasks');
      if(selectAllEl) selectAllEl.checked = (list.length > 0 && list.every(t => selectedTaskIds.has(t.id)));
      updateApplyControls();
        }

    // Toggle selection for a single task (exposed globally so inline onclick works)
    window.toggleRoomTaskSelection = function(taskId, checked){
      if(checked) selectedTaskIds.add(taskId);
      else selectedTaskIds.delete(taskId);
      // keep select-all checkbox in sync
      const list = loadTasks();
      const selectAllEl = document.getElementById('selectAllTasks');
      if(selectAllEl) selectAllEl.checked = (list.length > 0 && list.every(t => selectedTaskIds.has(t.id)));
      updateApplyControls();
    }

    // Select/unselect all tasks
    function toggleSelectAll(checked){
      const tasks = loadTasks();
      if(checked){ tasks.forEach(t => selectedTaskIds.add(t.id)); }
      else { selectedTaskIds.clear(); }
      // re-render to show checkboxes
      renderRoomTasks();
    }

    // Update apply controls (enable/disable button and ensure room dropdown populated)
    function updateApplyControls(){
      const applyBtn = document.getElementById('applySelectedBtn');
      if(applyBtn) applyBtn.disabled = (selectedTaskIds.size === 0);
      // Populate room dropdown once if empty
      const roomSel = document.getElementById('roomSelect');
      if(roomSel && roomSel.options.length <= 1){
        // use AVAILABLE_ROOMS (array of room numbers or objects)
        try{
          AVAILABLE_ROOMS.forEach(r => {
            let val, label;
            if(typeof r === 'object') { val = r.room_number ?? r.id ?? JSON.stringify(r); label = r.room_number ?? r.name ?? val; }
            else { val = r; label = r; }
            const opt = document.createElement('option'); opt.value = String(val); opt.text = String(label);
            roomSel.appendChild(opt);
          });
        }catch(e){ /* ignore */ }
      }
    }

    // Apply currently selected tasks to the selected room
    document.addEventListener('DOMContentLoaded', function(){
      const selectAllEl = document.getElementById('selectAllTasks');
      if(selectAllEl) selectAllEl.addEventListener('change', function(e){ toggleSelectAll(this.checked); });

      const applyBtn = document.getElementById('applySelectedBtn');
      if(applyBtn) applyBtn.addEventListener('click', async function(){
        const roomSel = document.getElementById('roomSelect');
        let room = roomSel ? roomSel.value : '';
        if(!room){
          // fallback: prompt the user for a room number
          room = prompt('Enter room number to apply selected tasks to (e.g. 201):');
          if(!room) return alert('No room chosen.');
        }
        const ids = Array.from(selectedTaskIds);
        if(ids.length === 0) return alert('No tasks selected.');

        // Build payload from local tasks
        const allTasks = loadTasks();
        const payloadTasks = ids.map(id => {
          const t = allTasks.find(x => x.id === id) || { title: '', description: '' };
          return {
            title: t.title || '',
            description: t.description || '',
            room_number: String(room),
            // optional fields left blank; server can populate timestamps/week/month/year
            area: t.area || '',
            day: t.day || '',
          };
        });

        // Send to server to persist into `roomtask` table (uses Laravel route)
        try{
          const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
          const res = await fetch('{{ url('/manage-roomtask/apply') }}', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': token,
              'Accept': 'application/json'
            },
            body: JSON.stringify({ tasks: payloadTasks })
          });
          const data = await res.json();
          if(!res.ok || !data.success){
            console.error('Save failed', data);
            return alert('Failed to save tasks to server: ' + (data.message || res.statusText));
          }

          // On success, also apply locally for immediate UX
          ids.forEach(id => applyTaskToRoom(id, String(room)));

          alert(`Saved ${data.inserted ?? payloadTasks.length} task(s) to room ${room}.`);
          // clear selection and re-render
          selectedTaskIds.clear();
          renderRoomTasks();
        }catch(e){
          console.error(e);
          alert('Unexpected error while saving tasks. See console.');
        }
      });

      // ensure dropdown populated on load
      updateApplyControls();
    });

        document.getElementById('roomTaskForm').addEventListener('submit', function(e){
            e.preventDefault();
            const id = document.getElementById('taskId').value || uid();
            const title = document.getElementById('taskTitle').value.trim();
            const description = document.getElementById('taskDescription').value.trim();
            if(!title) { alert('Please provide a task title'); return; }
            const tasks = loadTasks();
            const existing = tasks.find(t=>t.id===id);
            if(existing){ existing.title = title; existing.description = description; existing.updated_at = new Date().toISOString(); }
            else tasks.push({ id, title, description, created_at: new Date().toISOString() });
            saveTasks(tasks);
            renderRoomTasks();
            resetRoomTaskForm();
            alert('Task saved');
        });

        window.editRoomTask = function(id){ const tasks = loadTasks(); const t = tasks.find(x=>x.id===id); if(!t) return alert('Task not found'); document.getElementById('taskId').value = t.id; document.getElementById('taskTitle').value = t.title; document.getElementById('taskDescription').value = t.description || ''; }
    window.deleteRoomTask = async function(id){ 
      if(!confirm('Delete this task?')) return;
      const tasks = loadTasks();
      const task = tasks.find(t=>t.id===id);
      // If this task maps to a persisted TaskTemplate (area/description), ask server to mark it inactive
      if(task){
        try{
          const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
          await fetch('{{ url('/manage-roomtask/delete-template') }}', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': token,
              'Accept': 'application/json'
            },
            body: JSON.stringify({ tasks: [ { title: task.title || task.area || '', description: task.description || '' } ] })
          });
        }catch(e){
          console.warn('Failed to notify server about deleted manage task (template may not exist yet):', e);
        }
      }

      // Remove locally regardless
      let remaining = tasks.filter(t=>t.id!==id);
      saveTasks(remaining);
      renderRoomTasks();
    }
        window.promptApplyTaskToRoom = function(taskId){ const room = prompt('Enter room number to apply this task to (e.g. 201):'); if(!room) return; applyTaskToRoom(taskId, String(room)); alert('Task applied to room ' + room + ' (local only).'); }
        window.applyTaskToRoom = function(taskId, roomNumber){ 
          if(!taskId || !roomNumber) return false; 
          const assignments = loadAssignments(); 
          assignments[roomNumber] = assignments[roomNumber] || []; 
          if(!assignments[roomNumber].includes(taskId)) assignments[roomNumber].push(taskId); 
          saveAssignments(assignments); 
          try { localStorage.setItem(ASSIGN_KEY, JSON.stringify(assignments)); } catch(e){}
          // Broadcast change so room page (possibly in another tab) updates immediately
          try {
            const payload = { updatedRooms: [String(roomNumber)] };
            try { localStorage.setItem('roomAssignmentsUpdated', JSON.stringify(payload)); } catch(e) {}
            try { localStorage.setItem('roomAssignmentsUpdate', JSON.stringify(payload)); } catch(e) {}
            if (_roomUpdatesChannel) {
              try { _roomUpdatesChannel.postMessage(payload); } catch(e) { /* ignore */ }
            }
          } catch(e) { /* ignore */ }
          return true; 
        }
        window.getRoomTasksForRoom = function(roomNumber){ const assignments = loadAssignments(); const tasks = loadTasks(); const ids = (assignments[String(roomNumber)]||[]); return ids.map(id => tasks.find(t=>t.id===id)).filter(Boolean); }
        window.getAllRoomTasks = function(){ return loadTasks(); }
        window.addEventListener('storage', function(e){ if(e.key === STORAGE_KEY || e.key === ASSIGN_KEY){ try{ renderRoomTasks(); }catch(e){} } });

        // Delete all applied tasks from server and local storage
        async function deleteAllAppliedTasks() {
            if (!confirm('Are you sure you want to delete all tasks that have been applied to rooms? This cannot be undone.')) {
                return;
            }

            try {
                const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const res = await fetch('{{ url('/manage-roomtask/delete-all-applied') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token,
                        'Accept': 'application/json'
                    }
                });

                const data = await res.json();
                if (!res.ok || !data.success) {
                    console.error('Delete failed', data);
                    return alert('Failed to delete applied tasks: ' + (data.message || res.statusText));
                }

                // Clear local assignments
                saveAssignments({});
                
                alert(`Successfully deleted all applied tasks from rooms.`);
                renderRoomTasks(); // Refresh the UI
            } catch (e) {
                console.error(e);
                alert('Unexpected error while deleting tasks. See console.');
            }
        }

        // Add event listener for delete all button
        document.addEventListener('DOMContentLoaded', function() {
            const deleteAllBtn = document.getElementById('deleteAllAppliedBtn');
            if (deleteAllBtn) {
                deleteAllBtn.addEventListener('click', deleteAllAppliedTasks);
            }
        });

        // initial render
        renderRoomTasks();
    })();
  </script>
</body>
</html>