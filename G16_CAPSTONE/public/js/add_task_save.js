(function(){
  // Minimal, defensive Add Task save handler. Safe JS (no template literals except where unavoidable).
  function getCsrf(){
    var m = document.querySelector('meta[name="csrf-token"]');
    return m ? m.getAttribute('content') : '';
  }

  function showNotification(msg, type){
    try{
      var n = document.createElement('div');
      n.className = 'alert alert-' + (type || 'success') + ' position-fixed';
      n.style.cssText = 'top:80px; right:20px; z-index:9999; min-width:300px;';
      n.innerHTML = '<div class="d-flex align-items-center"><div style="flex:1">' + msg + '</div><button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
      document.body.appendChild(n);
      setTimeout(function(){ try{ n.remove(); }catch(e){} }, 5000);
    }catch(e){ console.log('Notify:', msg); }
  }

  function closeAddModal(){
    var el = document.getElementById('addTaskModalGeneral');
    if (!el) return;
    try{
      var m = bootstrap.Modal.getInstance(el);
      if (m) m.hide();
    }catch(e){ /* ignore */ }
  }

  function onSaveClick(e){
    // prevent other click handlers from running (they may reference broken globals)
    if (e && typeof e.stopImmediatePropagation === 'function') e.stopImmediatePropagation();
    if (e && typeof e.preventDefault === 'function') e.preventDefault();
    var btn = e.currentTarget;
    var title = (document.getElementById('newTaskTitle_general') || {}).value || '';
    var desc = (document.getElementById('newTaskDescription_general') || {}).value || '';
    title = title.trim(); desc = desc.trim();
    if (!title || !desc) { showNotification('Area and description are required','danger'); return; }

    btn.disabled = true;
    var old = btn.innerHTML;
    btn.innerHTML = 'Saving...';

    var payload = { name: 'Everyone', area: title, desc: desc, day: 'Monday', room: null, mode: 'add' };
    try{
      var csrf = getCsrf();
      fetch('/save-task', {
        method: 'POST',
        headers: { 'Content-Type':'application/json', 'X-CSRF-TOKEN': csrf, 'Accept':'application/json' },
        body: JSON.stringify(payload)
      }).then(function(res){
        if (res.status === 422) return res.json().then(function(err){ throw err; });
        return res.json();
      }).then(function(j){
        if (j && j.success){
          showNotification(j.message || 'Saved', 'success');
          closeAddModal();
          // Try to call existing refresh if available
          try{ if (typeof loadManageTasks === 'function') loadManageTasks((window.manageTasksState && window.manageTasksState.roomNumber) || null); }catch(e){}
        } else {
          var msg = (j && j.message) ? j.message : 'Failed to save';
          showNotification(msg, 'danger');
        }
      }).catch(function(err){
        var msg = (err && err.message) ? err.message : JSON.stringify(err);
        showNotification('Save failed: ' + msg, 'danger');
      }).finally(function(){ btn.disabled = false; btn.innerHTML = old; });
    }catch(err){
      showNotification('Unexpected error: ' + (err && err.message), 'danger');
      btn.disabled = false; btn.innerHTML = old;
    }
  }

  function attach(){
    var btn = document.getElementById('btnSaveTaskModalGeneral');
    if (!btn) return;
    // Remove duplicate listeners if any
    try{ btn.removeEventListener('click', onSaveClick); }catch(e){}
    // Use capture=false so this runs in bubble phase; our handler will stopImmediatePropagation
    btn.addEventListener('click', onSaveClick);
  }

  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', attach); else attach();
})();

