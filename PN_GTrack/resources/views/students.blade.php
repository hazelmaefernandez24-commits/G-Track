<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>G!Track - Student List</title>
    <style>
        :root {
            --bg: #f0f2f5;
            --sidebar-bg: #ffffff;
            --line: #e5e7eb;
            --text: #0f172a;
            --muted: #64748b;
            --blue: #2563eb;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
            background: var(--bg);
            color: var(--text);
            height: 100vh;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        /* ── Topbar ── */
        .topbar {
            height: 58px;
            background: #2563eb;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 20px;
            flex-shrink: 0;
        }
        .brand { display: flex; align-items: center; gap: 10px; color: #fff; text-decoration: none; }
        .brand-name { font-size: 17px; font-weight: 800; }
        .brand-sub  { font-size: 11px; opacity: 0.75; }
        .nav-actions { display: flex; align-items: center; gap: 8px; }
        .nav-btn {
            background: rgba(255,255,255,0.12); border: 1px solid rgba(255,255,255,0.2);
            color: #fff; font-weight: 700; font-size: 13px;
            display: flex; align-items: center; gap: 6px;
            padding: 7px 14px; border-radius: 8px; text-decoration: none; cursor: pointer;
            transition: background 0.2s;
        }
        .nav-btn:hover { background: rgba(255,255,255,0.22); }

        /* ── Branding ── */
        .brand-text { display: flex; flex-direction: column; }
        .brand-name { font-size: 18px; font-weight: 800; line-height: 1.1; }
        .brand-sub { font-size: 11px; opacity: 0.7; font-weight: 400; margin-top: 2px; }

        /* ── Layout ── */
        .layout {
            display: flex;
            flex: 1;
            overflow: hidden;
        }

        /* ══════════════════════════════
           LEFT PANEL — Student List
        ══════════════════════════════ */
        .student-panel {
            width: 340px;
            background: var(--sidebar-bg);
            border-right: 1px solid var(--line);
            display: flex;
            flex-direction: column;
            flex-shrink: 0;
        }

        .panel-header {
            padding: 14px 16px 10px;
            border-bottom: 1px solid var(--line);
        }
        .panel-title { font-size: 18px; font-weight: 800; margin-bottom: 10px; }

        /* Search */
        .search-box {
            display: flex; align-items: center;
            background: #f1f5f9; border-radius: 20px;
            padding: 8px 14px; gap: 8px;
            margin-bottom: 10px;
        }
        .search-box svg { color: var(--muted); flex-shrink: 0; }
        .search-box input {
            background: none; border: none; outline: none;
            font-size: 14px; color: var(--text); width: 100%;
        }
        .search-box input::placeholder { color: var(--muted); }

        /* Class tabs */
        .class-tabs {
            display: flex; gap: 4px;
            background: #f1f5f9; border: 1px solid var(--line);
            border-radius: 8px; padding: 3px;
        }
        .class-tab {
            flex: 1; text-align: center; padding: 5px 4px;
            border-radius: 6px; font-size: 11px; font-weight: 700;
            color: var(--muted); cursor: pointer; transition: all 0.15s;
            white-space: nowrap;
        }
        .class-tab.active { background: #fff; color: var(--blue); box-shadow: 0 1px 3px rgba(0,0,0,0.07); }

        /* Student list */
        .student-list { flex: 1; overflow-y: auto; }

        /* Alpha heading */
        .alpha-heading {
            padding: 5px 16px 3px;
            font-size: 10px; font-weight: 900; letter-spacing: 1px;
            text-transform: uppercase; color: var(--blue);
            background: #eff6ff; border-bottom: 1px solid #dbeafe;
        }

        /* Student row */
        .student-row {
            display: flex; align-items: center; gap: 12px;
            padding: 11px 16px; cursor: pointer;
            border-bottom: 1px solid #f1f5f9; transition: background 0.15s;
        }
        .student-row:hover  { background: #f8fafc; }
        .student-row.active { background: #eff6ff; border-left: 3px solid var(--blue); }

        .avatar {
            width: 40px; height: 40px; border-radius: 50%;
            background: #dbeafe; color: #1d4ed8;
            font-weight: 800; font-size: 15px;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0; position: relative;
        }
        .avatar.female { background: #fce7f3; color: #be185d; }
        .unread-dot {
            position: absolute; top: 0; right: 0;
            width: 11px; height: 11px;
            background: var(--blue); border-radius: 50%;
            border: 2px solid #fff;
        }

        .student-info { flex: 1; min-width: 0; }
        .student-name {
            font-weight: 700; font-size: 14px;
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        }
        .student-sub { font-size: 11px; color: var(--muted); margin-top: 1px; }
        .student-class-badge {
            font-size: 10px; font-weight: 700;
            background: #eff6ff; color: var(--blue);
            border: 1px solid #dbeafe; border-radius: 999px;
            padding: 2px 7px; flex-shrink: 0;
        }

        .no-results { padding: 40px 16px; text-align: center; color: var(--muted); font-size: 13px; }

        /* ══════════════════════════════
           RIGHT PANEL — Chat
        ══════════════════════════════ */
        .chat-panel {
            flex: 1;
            display: flex;
            flex-direction: column;
            background: #e9eef4;
            background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23cbd5e1' fill-opacity='0.15'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
            overflow: hidden;
        }

        /* Empty state */
        .chat-empty {
            flex: 1; display: flex; flex-direction: column;
            align-items: center; justify-content: center;
            color: var(--muted); text-align: center; gap: 14px;
        }
        .chat-empty-icon {
            width: 72px; height: 72px; background: #dbeafe;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
        }

        /* Chat header */
        .chat-header {
            background: #fff; border-bottom: 1px solid var(--line);
            padding: 12px 20px;
            display: flex; align-items: center; gap: 12px;
            flex-shrink: 0;
        }
        .chat-header-avatar {
            width: 40px; height: 40px; border-radius: 50%;
            background: #dbeafe; color: #1d4ed8;
            font-weight: 800; font-size: 15px;
            display: flex; align-items: center; justify-content: center;
        }
        .chat-header-avatar.female { background: #fce7f3; color: #be185d; }
        .chat-header-name { font-weight: 800; font-size: 15px; }
        .chat-header-sub  { font-size: 12px; color: var(--muted); margin-top: 1px; }

        /* Messages area */
        .chat-messages {
            flex: 1; overflow-y: auto;
            padding: 20px 24px;
            display: flex; flex-direction: column; gap: 4px;
        }

        /* Date separator */
        .date-sep { text-align: center; margin: 14px 0 8px; }
        .date-sep span {
            background: rgba(255,255,255,0.8); color: var(--muted);
            font-size: 11px; font-weight: 700;
            padding: 4px 12px; border-radius: 999px;
        }

        /* Bubble */
        .bubble-wrap { display: flex; gap: 8px; margin: 6px 0; align-items: flex-end; }
        .bubble-wrap.admin { flex-direction: row-reverse; }

        .bubble-content {
            display: flex;
            flex-direction: column;
            max-width: 70%;
            min-width: 40px;
        }
        .bubble-wrap.admin .bubble-content { align-items: flex-end; }
        .bubble-wrap.student .bubble-content { align-items: flex-start; }

        .bubble-av {
            width: 30px; height: 30px; border-radius: 50%;
            background: #dbeafe; color: #1d4ed8;
            font-weight: 800; font-size: 11px;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }
        .bubble-av.admin-av { background: #2563eb; color: #fff; }
        .bubble-av.female-av { background: #fce7f3; color: #be185d; }

        .bubble {
            padding: 9px 15px;
            border-radius: 18px;
            font-size: 14px;
            line-height: 1.5;
            word-wrap: break-word;
            width: fit-content;
            position: relative;
        }
        .bubble.student { background: #fff; color: var(--text); border-bottom-left-radius: 4px; border: 1px solid #e2e8f0; }
        .bubble.admin   { background: #2563eb; color: #fff; border-bottom-right-radius: 4px; box-shadow: 0 1px 2px rgba(0,0,0,0.1); }

        .bubble-time { font-size: 10px; margin-top: 4px; color: var(--muted); }
        .bubble.admin .bubble-time { color: rgba(255,255,255,0.7); text-align: right; }

        /* Input */
        .chat-input-area {
            background: #fff; border-top: 1px solid var(--line);
            padding: 12px 20px;
            display: flex; align-items: flex-end; gap: 10px;
            flex-shrink: 0;
        }
        .input-wrap {
            flex: 1; background: #f1f5f9; border: 1px solid var(--line);
            border-radius: 24px; padding: 10px 16px;
        }
        .input-wrap textarea {
            width: 100%; background: none; border: none; outline: none;
            font-size: 14px; resize: none; line-height: 1.5;
            color: var(--text); max-height: 90px; overflow-y: auto;
            font-family: inherit;
        }
        .input-wrap textarea::placeholder { color: var(--muted); }

        .send-btn {
            width: 44px; height: 44px; border-radius: 50%;
            background: var(--blue); color: #fff; border: none;
            cursor: pointer; display: flex; align-items: center; justify-content: center;
            flex-shrink: 0; transition: background 0.2s, transform 0.1s;
        }
        .send-btn:hover  { background: #1d4ed8; }
        .send-btn:active { transform: scale(0.95); }
        .send-btn:disabled { background: #94a3b8; cursor: default; }
    </style>
</head>
<body>

    {{-- Topbar --}}
    <header class="topbar">
        <a href="/dashboard" class="brand">
            <div class="brand-text">
                <div class="brand-name">G!Track — Student List</div>
            </div>
        </a>
        <div class="nav-actions">
            <a href="/notifications" class="nav-btn">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                Notifications
            </a>
            <a href="/dashboard" class="nav-btn">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                Dashboard
            </a>
            <form action="{{ route('logout') }}" method="POST" style="display:inline;">
                @csrf
                <button type="submit" class="nav-btn">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                    Logout
                </button>
            </form>
        </div>
    </header>

    <div class="layout">

        {{-- ══ LEFT: Student List ══ --}}
        <aside class="student-panel">
            <div class="panel-header">
                <div class="panel-title">Students</div>

                <div class="search-box">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    <input type="text" id="search-input" placeholder="Search student name..." autocomplete="off"/>
                </div>

                <div class="class-tabs" id="class-tabs">
                    <div class="class-tab active" data-class="all">All</div>
                    <div class="class-tab" data-class="2026">2026</div>
                    <div class="class-tab" data-class="2027">2027</div>
                    <div class="class-tab" data-class="2028">2028</div>
                </div>
            </div>

            <div class="student-list" id="student-list">
                @php $currentLetter = ''; @endphp
                @foreach($students as $student)
                    @php
                        $letter = strtoupper(substr($student->name, 0, 1));
                        $isFemale = strtolower($student->gender ?? '') === 'female';

                        // Check unread messages for this student
                        $unread = \Illuminate\Support\Facades\DB::table('notifications')
                            ->where(function($q) use ($student) {
                                $q->where('student_id', $student->id)
                                  ->orWhere('student_id', $student->student_id);
                            })
                            ->where('sender_type', 'student')
                            ->where('read', false)
                            ->whereNotIn('type', ['sos','blackout','broadcast'])
                            ->count();
                    @endphp

                    @if($letter !== $currentLetter)
                        @php $currentLetter = $letter; @endphp
                        <div class="alpha-heading alpha-row">{{ $letter }}</div>
                    @endif

                    <div class="student-row"
                         data-id="{{ $student->id }}"
                         data-name="{{ strtolower($student->name) }}"
                         data-class="{{ $student->class }}"
                         data-display-name="{{ $student->name }}"
                         data-student-id="{{ $student->student_id }}"
                         data-gender="{{ strtolower($student->gender ?? '') }}"
                         data-class-label="{{ $student->class }}"
                         onclick="openChat(this)">
                        <div class="avatar {{ $isFemale ? 'female' : '' }}">
                            {{ strtoupper(substr($student->name, 0, 1)) }}
                            @if($unread > 0)<div class="unread-dot"></div>@endif
                        </div>
                        <div class="student-info">
                            <div class="student-name">{{ $student->name }}</div>
                            <div class="student-sub">{{ $student->student_id }}</div>
                        </div>
                        <span class="student-class-badge">{{ $student->class }}</span>
                    </div>
                @endforeach

                @if($students->isEmpty())
                    <div class="no-results">No students found.</div>
                @endif
            </div>
        </aside>

        {{-- ══ RIGHT: Chat ══ --}}
        <section class="chat-panel" id="chat-panel">

            {{-- Empty state --}}
            <div class="chat-empty" id="chat-empty">
                <div class="chat-empty-icon">
                    <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                </div>
                <div>
                    <p style="font-weight:700;font-size:16px;color:#0f172a;">Select a student</p>
                    <p style="font-size:13px;margin-top:4px;">Click any student from the list to open their conversation</p>
                </div>
            </div>

            {{-- Chat header (hidden until student selected) --}}
            <div class="chat-header" id="chat-header" style="display:none;">
                <div class="chat-header-avatar" id="chat-header-avatar">A</div>
                <div>
                    <div class="chat-header-name" id="chat-header-name">Student Name</div>
                    <div class="chat-header-sub" id="chat-header-sub">ID · Class</div>
                </div>
            </div>

            {{-- Messages --}}
            <div class="chat-messages" id="chat-messages" style="display:none;"></div>

            {{-- Input --}}
            <div class="chat-input-area" id="chat-input-area" style="display:none;">
                <div class="input-wrap">
                    <textarea id="msg-input" rows="1" placeholder="Type a message..." onInput="autoResize(this)"></textarea>
                </div>
                <button class="send-btn" id="send-btn" title="Send message">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                </button>
            </div>

        </section>
    </div>

    <script>
        // ── State ──
        let activeStudentId   = null;
        let activeStudentName = '';
        let activeIsFemale    = false;
        let pollTimer         = null;

        // ── Search ──
        document.getElementById('search-input').addEventListener('input', function () {
            filterList();
        });

        // ── Class tabs ──
        document.querySelectorAll('.class-tab').forEach(tab => {
            tab.addEventListener('click', function () {
                document.querySelectorAll('.class-tab').forEach(t => t.classList.remove('active'));
                this.classList.add('active');
                filterList();
            });
        });

        function filterList() {
            const q     = document.getElementById('search-input').value.toLowerCase();
            const cls   = document.querySelector('.class-tab.active').dataset.class;
            let   lastVisibleLetter = null;

            // Hide/show rows
            document.querySelectorAll('.student-row').forEach(row => {
                const nameMatch  = row.dataset.name.includes(q);
                const classMatch = cls === 'all' || row.dataset.class === cls;
                row.style.display = (nameMatch && classMatch) ? '' : 'none';
            });

            // Hide alpha headings with no visible rows below them
            document.querySelectorAll('.alpha-row').forEach(heading => {
                let hasVisible = false;
                let next = heading.nextElementSibling;
                while (next && !next.classList.contains('alpha-row')) {
                    if (next.style.display !== 'none') { hasVisible = true; break; }
                    next = next.nextElementSibling;
                }
                heading.style.display = hasVisible ? '' : 'none';
            });
        }

        // ── Open Chat ──
        function openChat(row) {
            const id       = row.dataset.id;
            const name     = row.dataset.displayName;
            const studentId = row.dataset.studentId;
            const cls      = row.dataset.classLabel;
            const gender   = row.dataset.gender;

            // Highlight active row
            document.querySelectorAll('.student-row').forEach(r => r.classList.remove('active'));
            row.classList.add('active');

            // Remove unread dot
            const dot = row.querySelector('.unread-dot');
            if (dot) dot.remove();

            activeStudentId   = id;
            activeStudentName = name;
            activeIsFemale    = gender === 'female';

            // Update header
            const avatarEl = document.getElementById('chat-header-avatar');
            avatarEl.textContent  = name.charAt(0).toUpperCase();
            avatarEl.className    = 'chat-header-avatar' + (activeIsFemale ? ' female' : '');
            document.getElementById('chat-header-name').textContent = name;
            document.getElementById('chat-header-sub').textContent  = studentId + ' · Class ' + cls;

            // Show panels
            document.getElementById('chat-empty').style.display      = 'none';
            document.getElementById('chat-header').style.display     = 'flex';
            document.getElementById('chat-messages').style.display   = 'flex';
            document.getElementById('chat-input-area').style.display = 'flex';

            // Load messages
            loadMessages(true);

            // Start polling
            clearInterval(pollTimer);
            pollTimer = setInterval(() => loadMessages(false), 4000);
        }

        // ── Load messages ──
        async function loadMessages(scrollToBottom) {
            if (!activeStudentId) return;
            try {
                const res  = await fetch(`/messages/${activeStudentId}/json`);
                const data = await res.json();
                renderMessages(data.messages, scrollToBottom);
            } catch (e) {
                console.error('Poll error:', e);
            }
        }

        function renderMessages(messages, forceScroll) {
            const container = document.getElementById('chat-messages');
            const wasAtBottom = container.scrollHeight - container.scrollTop - container.clientHeight < 80;

            let html = '';
            let prevDate = '';

            if (!messages || messages.length === 0) {
                html = '<div style="text-align:center;color:#64748b;font-size:13px;margin-top:40px;">No messages yet. Say hello! 👋</div>';
            } else {
                messages.forEach(msg => {
                    const d = new Date(msg.created_at);
                    const dateStr = d.toLocaleDateString('en-US', { month:'short', day:'numeric', year:'numeric' });
                    const timeStr = d.toLocaleTimeString('en-US', { hour:'2-digit', minute:'2-digit' });
                    const isAdmin = msg.sender_type === 'admin';

                    if (dateStr !== prevDate) {
                        html += `<div class="date-sep"><span>${dateStr}</span></div>`;
                        prevDate = dateStr;
                    }

                    const avClass  = isAdmin ? 'admin-av' : (activeIsFemale ? 'female-av' : '');
                    const avLabel  = isAdmin ? 'AD' : activeStudentName.charAt(0).toUpperCase();
                    const bubClass = isAdmin ? 'admin' : 'student';

                    html += `
                        <div class="bubble-wrap ${bubClass}">
                            <div class="bubble-av ${avClass}">${avLabel}</div>
                            <div class="bubble-content">
                                <div class="bubble ${bubClass}">
                                    ${escHtml(msg.message)}
                                </div>
                                <div class="bubble-time">${timeStr}</div>
                            </div>
                        </div>`;
                });
            }

            container.innerHTML = html;
            if (forceScroll || wasAtBottom) {
                container.scrollTop = container.scrollHeight;
            }
        }

        function escHtml(str) {
            return String(str)
                .replace(/&/g,'&amp;').replace(/</g,'&lt;')
                .replace(/>/g,'&gt;').replace(/"/g,'&quot;');
        }

        // ── Send message ──
        async function sendMessage() {
            const input = document.getElementById('msg-input');
            const text  = input.value.trim();
            if (!text || !activeStudentId) return;

            document.getElementById('send-btn').disabled = true;

            const fd = new FormData();
            fd.append('message', text);
            fd.append('_token', '{{ csrf_token() }}');

            try {
                await fetch(`/messages/new/${activeStudentId}`, { method: 'POST', body: fd });
                input.value = '';
                input.style.height = 'auto';
                await loadMessages(true);
            } catch (e) {
                console.error(e);
            } finally {
                document.getElementById('send-btn').disabled = false;
                input.focus();
            }
        }

        document.getElementById('send-btn').addEventListener('click', sendMessage);
        document.getElementById('msg-input').addEventListener('keydown', function (e) {
            if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); sendMessage(); }
        });

        // ── Auto-resize textarea ──
        function autoResize(el) {
            el.style.height = 'auto';
            el.style.height = Math.min(el.scrollHeight, 90) + 'px';
        }

        // ── Auto-open student chat from URL parameter ──
        window.addEventListener('DOMContentLoaded', () => {
            const urlParams = new URLSearchParams(window.location.search);
            const targetId = urlParams.get('id');
            if (targetId) {
                const row = document.querySelector(`.student-row[data-id="${targetId}"]`);
                if (row) {
                    // Slight delay to ensure everything is ready
                    setTimeout(() => {
                        row.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        openChat(row);
                    }, 300);
                }
            }
        });
    </script>
</body>
</html>
