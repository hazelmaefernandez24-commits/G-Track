@extends('layouts.app')

@section('title', 'Notifications')
@section('subtitle', 'Send and manage notifications')

@push('styles')
<style>
        /* ---- your existing styles remain unchanged ---- */
        :root {
            --bg: #F8FBFF;
            --card: #FFFFFF;
            --line: rgba(34, 187, 234, 0.18);
            --text: #404040;
            --muted: rgba(64, 64, 64, 0.68);
            --primary: #22BBEA;
            --primary-dark: #009DE1;
            --blue: #22BBEA;
            --blue-dark: #009DE1;
            --accent: #FF9933;
            --accent-dark: #FF9933;
            --red: #FF9933;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Arial, Noto Sans, Liberation Sans, sans-serif;
            background: var(--bg);
            color: var(--text);
        }

        .topbar {
            height: 64px;
            background: linear-gradient(90deg, #009DE1 0%, #22BBEA 100%);
            border-bottom: 1px solid rgba(34, 187, 234, 0.18);
            box-shadow: var(--card-shadow);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 20px;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
            color: #fff;
            text-decoration: none;
        }

        .brand-badge {
            width: 34px;
            height: 34px;
            border-radius: 10px;
            background: transparent;
            display: flex;
            align-items: center;
            justify-content: center;
        }



        .brand-text {
            display: flex;
            flex-direction: column;
        }

        .brand-name {
            font-size: 19px;
            font-weight: 800;
            line-height: 1;
        }

        .brand-sub {
            font-size: 11px;
            font-weight: 400;
            opacity: 0.8;
            margin-top: 2px;
        }

        .actions {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .icon-btn {
            position: relative;
            background: rgba(255, 153, 51, 0.12);
            border: 1px solid rgba(255, 153, 51, 0.28);
            width: 34px;
            height: 34px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #FFFFFF;
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .icon-btn:hover {
            background: rgba(255, 153, 51, 0.2);
        }

        .logout {
            background: rgba(255, 255, 255, 0.13);
            border: 1px solid rgba(255, 255, 255, 0.24);
            color: #FFFFFF;
            font-weight: 700;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            padding: 8px 12px;
            border-radius: 10px;
            transition: all 0.2s ease;
        }

        .logout:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        .page-title h1 {
            margin: 0;
            font-size: 26px;
            font-weight: 800;
            letter-spacing: .1px;
        }

        .page-title p {
            margin: 6px 0 18px 0;
            color: var(--accent);
            font-weight: 500;
        }

        .cards {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 18px;
            margin-top: 14px;
            margin-bottom: 24px;
        }

        .container {
            max-width: 1300px;
            margin: 0 auto;
            padding: 20px 24px 40px 24px;
        }

        .card {
            background: #fff;
            border: 1px solid rgba(0, 0, 0, .08);
            border-radius: 16px;
            padding: 18px 18px 16px 18px;
            position: relative;
            overflow: hidden;
            box-shadow: 0 1px 2px rgba(0, 0, 0, .04);
            min-height: 150px;
        }

        .card-head {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 122x;
            margin-bottom: 12px;
        }

        .card-title {
            font-size: 14px;
            font-weight: 800;
        }

        .status-dot {
            width: 34px;
            height: 34px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid rgba(0, 0, 0, .06);
            background: #fff;
        }

        .stat-number {
            font-size: 28px;
            font-weight: 900;
            margin-top: 6px;
        }

        .stat-sub {
            margin-top: 6px;
            font-size: 13px;
            color: #667085;
            font-weight: 500;
        }

        .latest {
            margin-top: 6px;
        }

        .latest-time {
            font-size: 16px;
            font-weight: 800;
            margin-top: 6px;
        }

        .latest-date {
            font-size: 13px;
            color: var(--muted);
            margin-top: 3px;
        }

        .latest-icon {
            width: 34px;
            height: 34px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid rgba(0, 0, 0, .06);
            background: #fff;
        }

        .overview-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 12px;
            margin-top: 12px;
        }

        .feature-row {
            display: grid;
            grid-template-columns: 1fr;
            gap: 10px;
        }

        .filter-block {
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            align-items: center;
            padding: 16px 18px;
            border-top: 1px solid var(--line);
            border-bottom: 1px solid var(--line);
            background: linear-gradient(90deg, #009DE1 0%, #22BBEA 100%);
            border-radius: 0 0 12px 12px;
        }

        .filter-label {
            font-weight: 700;
            color: #fff;
            font-size: 14px;
        }

        .select-wrap {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        select {
            border: 1px solid var(--line);
            border-radius: 8px;
            padding: 9px 11px;
            font-size: 14px;
            background: #fff;
            color: #404040;
        }

        /* --- MAIN TAB UI (matches sub-tab pill style) --- */
        .tabs {
            display: flex;
            gap: 1px;
            background: rgba(34, 187, 234, 0.12);
            border: 1px solid var(--line);
            border-radius: 8px;
            padding: 3px;
            margin-top: 10px;
            width: 100%;
        }

        .tab {
            flex: 1;
            min-width: 110px;
            text-align: center;
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 700;
            color: var(--muted);
            text-decoration: none;
            cursor: pointer;
            transition: all 0.2s;
            white-space: nowrap;
        }

        .tab .tab-count {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 22px;
            padding: 3px 7px;
            margin-left: 6px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 800;
            background: #ef4444;
            color: #fff;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
        }

        .tab:hover {
            color: var(--blue);
        }

        .tab.active {
            background: #fff;
            color: var(--blue);
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
        }

        /* --- RESTORED LEGACY STYLES --- */
        .card-panel {
            background: #fff;
            border: 1px solid var(--line);
            border-radius: 12px;
            margin-top: 8px;
            padding: 14px;
        }

        .card-panel-grid {
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 10px;
            align-items: center;
            
        }

        .badge-pill {
            padding: 3px 9px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 700;
            border: 1px solid #dbeafe;
            background: #eff6ff;
            color: #1d4ed8;
        }

        .badge-right {
            background: #fff;
            border: 1px solid var(--line);
            color: var(--text);
            padding: 5px 10px;
            border-radius: 999px;
            font-weight: 700;
            font-size: 12px;
        }

        .messages {
            margin-top: 10px;
        }

        .message-item {
            background: #f8fafc;
            border: 1px solid var(--line);
            border-radius: 10px;
            padding: 12px;
            margin-bottom: 10px;
        }

        .message-head {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 16px;
        }

        .message-title {
            margin: 0;
            font-weight: 700;
            font-size: 14px;
            line-height: 1.3;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 8px;
        }

        .message-meta {
            color: var(--muted);
            font-size: 12px;
        }

        .message-body {
            margin: 7px 0 0;
            color: var(--text);
            font-size: 13px;
        }

        .action-btn {
            border: none;
            border-radius: 8px;
            color: #fff;
            font-weight: 700;
            padding: 6px 12px;
            cursor: pointer;
        }

        .ack-btn {
            background: var(--accent);
        }

        .read-btn {
            background: var(--primary-dark);
        }

        /* Sub Tabs */
        .sub-tabs {
            display: flex;
            gap: 1px;
            background: rgba(34, 187, 234, 0.12);
            border: 1px solid var(--line);
            border-radius: 8px;
            padding: 3px;
            margin-bottom: 16px;
            width: 100%;
        }

        .sub-tab {
            flex: 1;
            text-align: center;
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            text-decoration: none;
            color: var(--muted);
            transition: all 0.2s;
        }

        .sub-tab:hover {
            color: var(--blue);
        }

        .sub-tab.active {
            background: #fff;
            color: var(--blue);
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
        }

        /* --- MESSENGER-STYLE CHAT UI --- */
        .chat-container {
            margin-bottom: 30px;
            background: #ffffffff;
            border: 1px solid var(--line);
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        .chat-header {
            background: #f8fafc;
            padding: 12px 20px;
            border-bottom: 1px solid var(--line);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .chat-scroll-area {
            max-height: 500px;
            overflow-y: auto;
            padding: 24px;
            background: #fff;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .chat-bubble {
            max-width: 75%;
            padding: 10px 14px;
            font-size: 14px;
            line-height: 1.5;
            position: relative;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
        }

        .bubble-left {
            align-self: flex-start;
            background: #F8FBFF;
            color: var(--text);
            border-radius: 4px 18px 18px 18px;
        }

        .bubble-right {
            align-self: flex-end;
            background: #f97316;
            /* Orange */
            color: #fff;
            border-radius: 18px 18px 4px 18px;
        }

        .chat-timestamp {
            font-size: 10px;
            margin-top: 4px;
            opacity: 0.7;
            display: block;
            text-align: inherit;
        }

        .chat-input-area {
            background: #f8fafc;
            padding: 16px 20px;
            border-top: 1px solid var(--line);
        }

        .chat-input-row {
            background: #fff;
            border: 1px solid var(--line);
            border-radius: 999px;
            padding: 4px 4px 4px 18px;
            display: flex;
            align-items: center;
            gap: 12px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.02);
        }

        .chat-input-row input {
            flex: 1;
            border: none;
            outline: none;
            padding: 8px 0;
            font-size: 14px;
            background: transparent;
        }

        .chat-send-btn {
            background: var(--blue-dark);
            color: #fff;
            border: none;
            width: 34px;
            height: 34px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s;
        }

        .chat-send-btn:hover {
            background: #009DE1;
        }

        /* --- MESSENGER UI INTEGRATION --- */
        .messenger-wrapper {
            display: flex;
            height: 650px;
            background: linear-gradient(90deg, #009DE1 0%, #22BBEA 100%);
            border: 1px solid var(--line);
            border-radius: 12px;
            overflow: hidden;
            margin-top: 10px;
        }

        /* LEFT PANEL — Student List */
        .student-panel {
            width: 320px;
            background: #ffffffff;
            border-right: 1px solid var(--line);
            display: flex;
            flex-direction: column;
            flex-shrink: 0;
        }

        .panel-header {
            padding: 14px 16px 10px;
            border-bottom: 1px solid var(--line);
        }

        .panel-title {
            font-size: 16px;
            font-weight: 800;
            margin-bottom: 10px;
        }

        .search-box {
            display: flex;
            align-items: center;
            background: #F8FBFF;
            border-radius: 20px;
            padding: 8px 14px;
            gap: 8px;
            margin-bottom: 10px;
        }

        .search-box input {
            background: none;
            border: none;
            outline: none;
            font-size: 13px;
            color: var(--text);
            width: 100%;
        }

        .class-filter-wrap {
            margin-top: 8px;
            display: block;
        }

        .class-filter-select {
            width: 100%;
            border: 1px solid var(--line);
            border-radius: 10px;
            padding: 8px 10px;
            background: #fff;
            color: var(--text);
            font-size: 13px;
            outline: none;
        }

        .class-filter-select:focus {
            border-color: rgba(59, 130, 246, 0.55);
            box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.15);
        }

        .student-list {
            flex: 1;
            overflow-y: auto;
        }

        .alpha-heading {
            padding: 6px 16px 4px;
            font-size: 10px;
            font-weight: 900;
            letter-spacing: 1px;
            text-transform: uppercase;
            color: var(--blue);
            background: #F8FBFF;
            border-bottom: 1px solid rgba(34, 187, 234, 0.18);
        }

        .student-row {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 16px;
            cursor: pointer;
            border-bottom: 1px solid #f8fafc;
            transition: background 0.15s;
        }

        .student-row:hover {
            background: #f8fafc;
        }

        .student-row.active {
            background: #F8FBFF;
            border-left: 3px solid var(--blue);
        }

        .avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: rgba(34, 187, 234, 0.18);
            color: var(--blue-dark);
            font-weight: 800;
            font-size: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            position: relative;
            overflow: visible;
        }

        .avatar.female {
            background: rgba(255, 153, 51, 0.16);
            color: var(--accent);
        }

        .avatar img.profile-pic {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
        }

        .unread-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            min-width: 20px;
            height: 20px;
            padding: 0 6px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: 800;
            color: #fff;
            background: #ef4444;
            border-radius: 999px;
            border: 2px solid #fff;
            line-height: 1;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.15);
        }

        .student-name {
            font-weight: 700;
            font-size: 13px;
        }

        .student-sub {
            font-size: 11px;
            color: var(--muted);
        }

        /* RIGHT PANEL — Chat */
        .chat-panel {
            flex: 1;
            display: flex;
            flex-direction: column;
            background: #f8fafc;
            overflow: hidden;
        }

        .chat-empty {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: var(--muted);
            text-align: center;
            gap: 10px;
        }

        .chat-header {
            background: #fff;
            border-bottom: 1px solid var(--line);
            padding: 10px 20px;
            display: flex;
            flex-shrink: 0;
        }

        .chat-header-profile {
            display: flex;
            align-items: center;
            gap: 12px;
            width: 100%;
        }

        .chat-header .student-info {
            min-width: 0;
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .chat-header-name {
            font-weight: 800;
            font-size: 14px;
            min-width: 0;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .chat-header-sub {
            font-size: 11px;
            color: var(--muted);
        }

        .chat-messages {
            flex: 1;
            overflow-y: auto;
            padding: 20px;
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .date-sep {
            text-align: center;
            margin: 15px 0 10px;
        }

        .date-sep span {
            background: rgba(34, 187, 234, 0.08);
            color: var(--muted);
            font-size: 10px;
            font-weight: 800;
            padding: 4px 12px;
            border-radius: 999px;
            text-transform: uppercase;
        }

        .bubble-wrap {
            display: flex;
            gap: 8px;
            margin: 4px 0;
            align-items: flex-end;
        }

        .bubble-wrap.admin {
            flex-direction: row-reverse;
        }

        .bubble-content {
            display: flex;
            flex-direction: column;
            max-width: 75%;
        }

        .bubble-wrap.admin .bubble-content {
            align-items: flex-end;
        }

        .bubble {
            padding: 8px 14px;
            border-radius: 18px;
            font-size: 14px;
            line-height: 1.4;
        }

        .bubble.student {
            background: #fff;
            color: var(--text);
            border: 1px solid var(--line);
            border-bottom-left-radius: 4px;
        }

        .bubble.admin {
            background: var(--blue);
            color: #fff;
            border-bottom-right-radius: 4px;
        }

        .bubble.sos-alert {
            background: rgba(255, 153, 51, 0.16);
            color: var(--accent);
            border: 1px solid rgba(255, 153, 51, 0.25);
        }

        .bubble-time {
            font-size: 10px;
            margin-top: 4px;
            color: var(--muted);
        }

        .sender-label {
            font-size: 11px;
            font-weight: 700;
            color: var(--blue-dark);
            margin-bottom: 6px;
            letter-spacing: 0.02em;
        }

        .chat-input-area {
            background: #fff;
            border-top: 1px solid var(--line);
            padding: 12px 20px;
            display: flex;
            align-items: flex-end;
            gap: 10px;
        }

        .input-wrap {
            flex: 1;
            background: #F8FBFF;
            border: 1px solid var(--line);
            border-radius: 20px;
            padding: 8px 16px;
        }

        .input-wrap textarea {
            width: 100%;
            background: none;
            border: none;
            outline: none;
            font-size: 14px;
            resize: none;
            line-height: 1.4;
            max-height: 100px;
            font-family: inherit;
        }

        .send-btn {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--blue);
            color: #fff;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .send-btn:disabled {
            background: rgba(34, 187, 234, 0.1);
        }

        /* --- MODAL STYLES --- */
        .modal-backdrop {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            backdrop-filter: blur(4px);
        }

        .modal-content {
            background: #fff;
            border-radius: 20px;
            width: 95%;
            max-width: 650px;
            padding: 28px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            position: relative;
            animation: modal-in 0.3s ease-out;
            max-height: 85vh;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        @keyframes modal-in {
            from {
                transform: translateY(30px);
                opacity: 0;
            }

            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .modal-close {
            position: absolute;
            top: 20px;
            right: 20px;
            cursor: pointer;
            color: var(--muted);
            z-index: 10;
            padding: 4px;
            border-radius: 50%;
            transition: background 0.2s;
        }

        .modal-close:hover {
            background: #F8FBFF;
            color: var(--text);
        }

        .modal-subject {
            font-size: 20px;
            font-weight: 800;
            margin-bottom: 4px;
            color: var(--text);
            line-height: 1.3;
        }

        .modal-body-container {
            background: #f8fafc;
            border: 1px solid var(--line);
            border-radius: 14px;
            margin-top: 20px;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        .modal-scroll-area {
            padding: 24px;
            overflow-y: auto;
            flex: 1;
        }

        /* Custom Scrollbar for Modal */
        .modal-scroll-area::-webkit-scrollbar {
            width: 6px;
        }

        .modal-scroll-area::-webkit-scrollbar-track {
            background: #F8FBFF;
        }

        .modal-scroll-area::-webkit-scrollbar-thumb {
            background: rgba(34, 187, 234, 0.18);
            border-radius: 10px;
        }

        .broadcast-item-clickable {
            cursor: pointer;
            transition: all 0.2s;
        }

        .broadcast-item-clickable:hover {
            background: rgba(255, 153, 51, 0.08);
            transform: translateY(-2px);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }

        @keyframes slide-down {
            from {
                transform: translateY(-10px);
                opacity: 0;
            }

            to {
                transform: translateY(0);
                opacity: 1;
            }
        }
    
</style>
<!-- Quill Rich Text Editor -->
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
<script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>
@endpush

@section('content')

{{--
    <div class="page-title" style="margin-bottom: 24px;">
            
            <p>Summary of system communications and alerts</p>
        </div>

        <section class="cards">

            <!-- Emergency Alerts -->
            <article class="card">
                <div class="card-head">
                    <div>
                        <div class="card-title">Emergency Alerts</div>
                        <div class="stat-number" style="color: var(--red);">{{ $stats['sos'] }}</div>
                        <div class="stat-sub">
                            Pending SOS alerts
                        </div>
                    </div>
                    <div class="status-dot" style="color: var(--red);">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M1 21h22L12 2 1 21Zm12-3h-2v-2h2v2Zm0-4h-2v-4h2v4Z" fill="currentColor" />
                        </svg>
                    </div>
                </div>
            </article>

            <!-- Broadcast History -->
            <article class="card">
                <div class="card-head">
                    <div>
                        <div class="card-title">Broadcast History</div>
                        <div class="stat-number" style="color: var(--accent-dark);">{{ $stats['broadcast'] }}</div>
                        <div class="stat-sub">
                            Announcements sent
                        </div>
                    </div>
                    <div class="status-dot" style="color: var(--accent-dark);">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M20 2H4c-1.1 0-2 .9-2 2v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2Zm0 14H5.17L4 17.17V4h16v12Z"
                                fill="currentColor" />
                            <path d="M7 9h10v2H7z" fill="currentColor" opacity=".3" />
                        </svg>
                    </div>
                </div>
            </article>
        </section>
        --}}

        <div class='section' style="margin-top: 0px;">
            <div class='filter-block' style="border-radius: 12px; border: 1px solid var(--line);">
                <span class='filter-label'>Filter by Class</span>
                <div class='select-wrap'>
                    <span style='color:var(--muted);font-size:15px;'>:</span>
                    <select id='class-filter'
                        onchange="location.href='?class=' + encodeURIComponent(this.value) + '&tab={{ $tab }}'">
                        <option value='all' {{ $class === 'all' ? 'selected' : '' }}>All Classes</option>
                        <option value='2026' {{ $dbClass === '2026' ? 'selected' : '' }}>Class 2026</option>
                        <option value='2027' {{ $dbClass === '2027' ? 'selected' : '' }}>Class 2027</option>
                        <option value='2028' {{ $dbClass === '2028' ? 'selected' : '' }}>Class 2028</option>
                    </select>
                </div>
            </div>
        </div>

        <div class='section'>
            <div class='tabs'>
                <a class='tab {{ $tab === "student" ? "active" : "" }}'
                    href='?class={{ urlencode($class) }}&tab=student'>
                    Student Messages
                    @php $studentUnread = $unreadCounts->sum(); @endphp
                    @if($studentUnread > 0)
                        <span class="tab-count">{{ $studentUnread }}</span>
                    @endif
                </a>
                <a class='tab {{ $tab === "sos" ? "active" : "" }}'
                    href='?class={{ urlencode($class) }}&tab=sos'>
                    Emergency Alerts
                    @php $emergencyCount = $stats['sos'] + $stats['blackout']; @endphp
                    @if($emergencyCount > 0)
                        <span style="background: var(--red); color: white; padding: 2px 6px; border-radius: 12px; font-size: 11px; margin-left: 6px; font-weight: bold;">{{ $emergencyCount }}</span>
                    @endif
                </a>
                <a class='tab {{ $tab === "broadcast" ? "active" : "" }}'
                    href='?class={{ urlencode($class) }}&tab=broadcast'>
                    Broadcast Notifications
                </a>
            </div>

            <div class='card-panel'>
                <div class='card-panel-grid'>
                    <div>
                        <h3 class="card-title" style="font-size: 17px;">
    {{ $class === 'all' ? 'All Classes' : 'Class of ' . $class }}
</h3>
                       {{-- <p class='card-sub'>{{ $notifications->count() }}
                            {{ $notifications->count() === 1 ? 'message' : 'messages' }}
                        </p> --}}
                    </div>
                </div>

                @if($tab === 'broadcast')
                    <div id="broadcast-form-container"
                        style="display: none; background: #fff; border: 1px solid var(--line); border-radius: 12px; padding: 24px; margin-bottom: 24px; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.05); animation: slide-down 0.3s ease-out;">
                        <div
                            style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px;">
                            <div>
                                <h3 style="margin: 0 0 4px 0; font-size: 18px; font-weight: 800;">Compose New Broadcast</h3>
                                <p style="margin: 0; font-size: 13px; color: var(--muted);">Prepare and send an announcement
                                    to students</p>
                            </div>
                            <button onclick="toggleBroadcastForm()"
                                style="background: #F8FBFF; color: var(--muted); border: none; padding: 8px; border-radius: 8px; cursor: pointer;">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <line x1="18" y1="6" x2="6" y2="18"></line>
                                    <line x1="6" y1="6" x2="18" y2="18"></line>
                                </svg>
                            </button>
                        </div>

                        <form method="POST" action="/notifications/send" id="broadcast-form"
                            style="display: flex; flex-direction: column; gap: 16px;">
                            @csrf
                            <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 16px;">
                                <div>
                                    <label
                                        style="display: block; font-size: 11px; font-weight: 800; color: var(--muted); text-transform: uppercase; margin-bottom: 6px;">Target
                                        Audience</label>
                                    <select name="target" required
                                        style="width: 100%; padding: 10px; border: 1px solid var(--line); border-radius: 8px; font-size: 13px; background: #f8fafc;">
                                        <option value="all">All Students</option>
                                        <option value="2026">Class 2026</option>
                                        <option value="2027">Class 2027</option>
                                        <option value="2028">Class 2028</option>
                                    </select>
                                </div>
                                <div>
                                    <label
                                        style="display: block; font-size: 11px; font-weight: 800; color: var(--muted); text-transform: uppercase; margin-bottom: 6px;">Subject
                                        Line</label>
                                    <input type="text" name="subject" required placeholder="Enter subject Title..."
                                        style="width: 100%; padding: 10px; border: 1px solid var(--line); border-radius: 8px; font-size: 13px; background: #f8fafc;">
                                </div>
                            </div>

                            <div>
                                <label
                                    style="display: block; font-size: 11px; font-weight: 800; color: var(--muted); text-transform: uppercase; margin-bottom: 6px;">Message
                                    Content (Rich Text)</label>
                                <div id="quill-editor"
                                    style="height: 150px; background: #f8fafc; border-radius: 8px; border: 1px solid var(--line);">
                                </div>
                                <input type="hidden" name="message" id="broadcast-message-input">
                            </div>

                            <div style="display: flex; justify-content: flex-end; gap: 12px;">
                                <button type="button" onclick="toggleBroadcastForm()"
                                    style="background: #F8FBFF; color: var(--muted); border: none; padding: 12px 24px; border-radius: 8px; font-weight: 700; font-size: 14px; cursor: pointer;">
                                    Cancel
                                </button>
                                <button type="submit"
                                    style="background: var(--blue); color: #fff; border: none; padding: 12px 32px; border-radius: 8px; font-weight: 700; font-size: 14px; cursor: pointer; transition: all 0.2s;">
                                    Send Announcement Now
                                </button>
                            </div>
                        </form>
                    </div>

                    <div class='broadcast-info'
                        style="margin-bottom: 24px; display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <strong>Broadcast Notifications History</strong>
                            <p style="margin: 4px 0 0 0;">Detailed log of all outbound school-wide announcements.</p>
                        </div>
                        <button onclick="toggleBroadcastForm()" id="toggle-broadcast-btn"
                            style="background: var(--blue); color: #fff; border: none; padding: 12px 20px; border-radius: 10px; font-weight: 700; font-size: 13px; cursor: pointer; display: flex; align-items: center; gap: 8px; box-shadow: 0 4px 6px -1px rgba(37, 99, 235, 0.2); transition: all 0.2s;">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="12" y1="5" x2="12" y2="19"></line>
                                <line x1="5" y1="12" x2="19" y2="12"></line>
                            </svg>
                            Send New Broadcast
                        </button>
                    </div>
                @endif

                <div class='messages'>
                    @if($tab === 'sos')
                        <div class="sub-tabs">
                            <a href="?class={{ urlencode($class) }}&tab=sos&subtab=sos"
                                class="sub-tab {{ $subtab === 'sos' ? 'active' : '' }}">
                                SOS Alerts
                            </a>
                            <a href="?class={{ urlencode($class) }}&tab=sos&subtab=blackout"
                                class="sub-tab {{ $subtab === 'blackout' ? 'active' : '' }}">
                                Blackout Alerts
                            </a>
                        </div>

                        @if($subtab === 'sos')
                            <div style="margin-bottom: 16px; display: flex; justify-content: flex-end;">
                                <button type="button" class="action-btn" onclick="openSOSHistoryModal()" style="background: var(--blue); display: flex; align-items: center; gap: 6px;">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <circle cx="12" cy="12" r="10"></circle>
                                        <polyline points="12 6 12 12 16 14"></polyline>
                                    </svg>
                                    Archives
                                </button>
                            </div>
                            @forelse($notifications->where('type', 'sos')->where('status', '!=', 'resolved') as $notification)
                                <div class='message-item'
                                    style="{{ $notification->status === 'resolved' ? 'opacity: 0.7; border-left: 4px solid var(--muted);' : 'border-left: 4px solid var(--red);' }}">
                                    <div class='message-head'>
                                        <p class='message-title'>
                                            SOS Alert: {{ optional($notification->student)->name ?? 'Unknown Student' }}
                                            ({{ optional($notification->student)->student_id ?? 'N/A' }})
                                            @if($notification->status === 'resolved')
                                                <span class='badge-pill'
                                                    style='background:rgba(34, 187, 234, 0.08);color:rgba(64, 64, 64, 0.68);border-color:rgba(34, 187, 234, 0.18);'>I am Safe
                                                    (Resolved)</span>
                                            @else
                                                <span class='badge-pill'
                                                    style='background:rgba(255, 153, 51, 0.16);color:var(--accent);border-color:rgba(255, 153, 51, 0.25);'>Active Help
                                                    Needed</span>
                                            @endif
                                        </p>
                                        <span
                                            class='message-meta'>{{ \Carbon\Carbon::parse($notification->created_at)->format('n/j/Y, h:i A') }}</span>
                                    </div>
                                    {{-- Message Body removed as per request --}}

                                    @php
                                        // Get the student's latest GPS record from the locations table
                                        $latestLocation = $notification->student
                                            ? \App\Models\Location::where('student_id', $notification->student->id)
                                                ->orderBy('recorded_at', 'desc')
                                                ->first()
                                            : null;

                                        // Prefer the notification snapshot first, then live student data
                                        $currentBattery = $notification->battery_level ?? optional($notification->student)->battery_level;
                                        $currentSignal = $notification->signal_status ?? optional($notification->student)->signal_status;
                                        $currentLat = $latestLocation->latitude ?? optional($notification->student)->latitude ?? $notification->latitude;
                                        $currentLng = $latestLocation->longitude ?? optional($notification->student)->longitude ?? $notification->longitude;
                                    @endphp

                                    <div class='message-meta'
                                        style='margin-top:12px; background: #f8fafc; padding: 12px; border-radius: 10px; border: 1px solid rgba(0,0,0,0.05);'>
                                        <!-- MEDIA FEEDS SECTION -->
                                        <div
                                            style="margin-bottom: 12px;">
                                            <!-- Video Feed Container -->
                                            <div
                                                style="background: #404040; border-radius: 8px; padding: 12px; min-height: 160px; display: flex; flex-direction: column; justify-content: center; position: relative; overflow: hidden;">
                                                <div
                                                    style="position: absolute; top: 12px; left: 12px; background: rgba(220, 38, 38, 0.9); color: #fff; font-size: 10px; font-weight: 900; padding: 4px 10px; border-radius: 6px; text-transform: uppercase; z-index: 10; display: flex; align-items: center; gap: 6px; box-shadow: 0 2px 4px rgba(0,0,0,0.2);">
                                                    <span
                                                        style="width: 8px; height: 8px; background: #fff; border-radius: 50%; animation: pulse 1s infinite;"></span>
                                                    Live Video Feed
                                                </div>

                                                @if($notification->video_url || (isset($notification->media_url) && !Str::endsWith($notification->media_url, ['.mp3', '.wav'])))
                                                    <div style="position: relative;">
                                                        <video controls style="width: 100%; border-radius: 6px; max-height: 400px; background: #000;">
                                                            <source src="{{ $notification->video_url ?? $notification->media_url }}"
                                                                type="video/mp4">
                                                        </video>
                                                        <div style="margin-top: 8px; display: flex; justify-content: flex-end;">
                                                            <a href="{{ $notification->video_url ?? $notification->media_url }}" download 
                                                               style="background: rgba(255,255,255,0.1); color: #fff; text-decoration: none; font-size: 11px; padding: 6px 12px; border-radius: 6px; border: 1px solid rgba(255,255,255,0.2); display: flex; align-items: center; gap: 6px; transition: all 0.2s;"
                                                               onmouseover="this.style.background='rgba(255,255,255,0.2)'"
                                                               onmouseout="this.style.background='rgba(255,255,255,0.1)'">
                                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                                                    <polyline points="7 10 12 15 17 10"></polyline>
                                                                    <line x1="12" y1="15" x2="12" y2="3"></line>
                                                                </svg>
                                                                Save to Laptop
                                                            </a>
                                                        </div>
                                                    </div>
                                                @else
                                                    <div style="text-align: center; color: rgba(255,255,255,0.4); padding: 40px 0;">
                                                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none"
                                                            stroke="currentColor" stroke-width="2"
                                                            style="margin-bottom: 12px; opacity: 0.5;">
                                                            <path d="M23 7l-7 5 7 5V7z" />
                                                            <rect x="1" y="5" width="15" height="14" rx="2" ry="2" />
                                                        </svg>
                                                        <div style="font-size: 13px; font-weight: 700;">No Video Feed Available</div>
                                                        <p style="font-size: 11px; opacity: 0.6; margin-top: 4px;">Student device has not uploaded video data</p>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>

                                        <!-- TELEMETRY SECTION (Restored Original Style) -->
                                        <div
                                            style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; padding-top: 12px; border-top: 1px solid rgba(0,0,0,0.05);">
                                            <div>
                                                <div
                                                    style="font-size: 10px; text-transform: uppercase; font-weight: 800; color: var(--muted); letter-spacing: 0.5px;">
                                                    Live Battery</div>
                                                <div class="notification-battery-display-{{ $notification->id }}"
                                                    style="font-weight: 700; color: {{ ($currentBattery ?? 0) < 20 ? 'var(--accent)' : '#404040' }}; font-size: 14px; margin-top: 2px;">
                                                    🔋 {{ isset($currentBattery) ? $currentBattery . '%' : 'N/A' }}
                                                </div>
                                            </div>
                                            <div>
                                                <div
                                                    style="font-size: 10px; text-transform: uppercase; font-weight: 800; color: var(--muted); letter-spacing: 0.5px;">
                                                    Live Signal</div>
                                                @php
                                                    $sigLower = strtolower($currentSignal ?? '');
                                                    $sigColor = '#FF9933'; // Default to warning accent
                                                    if (Str::contains($sigLower, ['excellent', 'strong', 'good'])) $sigColor = '#009DE1';
                                                    elseif (Str::contains($sigLower, 'fair')) $sigColor = '#FF9933';
                                                    elseif (empty($sigLower) || $sigLower === 'n/a') $sigColor = '#404040';
                                                @endphp
                                                <div style="font-weight: 700; color: {{ $sigColor }}; font-size: 14px; margin-top: 2px;">
                                                    {!! Str::contains($sigLower, ['excellent', 'strong', 'good', 'fair']) ? '📶' : '⚠️' !!}
                                                    {{ $currentSignal ?? 'N/A' }}
                                                </div>
                                            </div>
                                            <div style="grid-column: span 2;">
                                                <div
                                                    style="font-size: 10px; text-transform: uppercase; font-weight: 800; color: var(--muted); letter-spacing: 0.5px;">
                                                    Last Known Location</div>
                                                <div
                                                    style="font-weight: 700; color: var(--blue); font-size: 13px; margin-top: 2px;">
                                                    @if($currentLat)
                                                        <a href="/tracking?student_id={{ optional($notification->student)->student_id ?? $notification->student_id }}"
                                                            style="text-decoration: none; color: inherit; display: flex; align-items: center; gap: 4px;">
                                                            📍 {{ number_format($currentLat, 5) }}, {{ number_format($currentLng, 5) }}
                                                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none"
                                                                stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                                                                stroke-linejoin="round">
                                                                <line x1="7" y1="17" x2="17" y2="7"></line>
                                                                <polyline points="7 7 17 7 17 17"></polyline>
                                                            </svg>
                                                        </a>
                                                    @else
                                                        {{ $notification->location ?? 'Location Unavailable' }}
                                                    @endif
                                                </div>
                                            </div>

                                            @if($notification->status !== 'resolved')
                                                <div
                                                    style="grid-column: span 4; margin-top: 8px; padding-top: 12px; border-top: 1px dashed rgba(0,0,0,0.1); display: flex; justify-content: flex-end; gap: 10px;">
                                                    {{-- Acknowledged (Mark as Seen) --}}
                                                    @if(!$notification->read)
                                                        <form method='POST' action='/notifications/{{ $notification->id }}/acknowledge'
                                                            style='display:inline;'>
                                                            @csrf
                                                            <button class='action-btn'
                                                                style="font-size:11px; padding:8px 16px; border-radius: 8px; background: var(--blue); color: #fff; border: none; font-weight: 700; cursor: pointer; display: flex; align-items: center; gap: 4px;"
                                                                type='submit'>
                                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                                                                    stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                                                                    stroke-linejoin="round">
                                                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                                                    <circle cx="12" cy="12" r="3"></circle>
                                                                </svg>
                                                                Acknowledged
                                                            </button>
                                                        </form>
                                                    @endif

                                                    {{-- Mark as Resolved (Safe) --}}
                                                    <form method='POST' action='/notifications/{{ $notification->id }}/resolve'
                                                        style='display:inline;'>
                                                        @csrf
                                                        <button class='action-btn ack-btn'
                                                            style="font-size:11px; padding:8px 16px; border-radius: 8px; font-weight: 700; display: flex; align-items: center; gap: 4px;"
                                                            type='submit'>
                                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                                                                stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                                                                stroke-linejoin="round">
                                                                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                                                <polyline points="22 4 12 14.01 9 11.01"></polyline>
                                                            </svg>
                                                            Mark as Resolved
                                                        </button>
                                                    </form>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class='message-item'
                                    style='background:#f8fafc;border-style:dashed;text-align:center;color:var(--muted);'>No current
                                    SOS alerts.</div>
                            @endforelse
                        @else
                            <div style="margin-bottom: 16px; display: flex; justify-content: flex-end;">
                                <button type="button" class="action-btn" onclick="openBlackoutHistoryModal()" style="background: var(--blue); display: flex; align-items: center; gap: 6px;">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <circle cx="12" cy="12" r="10"></circle>
                                        <polyline points="12 6 12 12 16 14"></polyline>
                                    </svg>
                                    Archives
                                </button>
                            </div>
                                @forelse($notifications->where('type', 'blackout')->where('status', '!=', 'resolved') as $notification)
                                        <div class='message-item' style="border-left: 4px solid var(--blue);">
                                            <div class='message-head'>
                                                <p class='message-title'>
                                                    Blackout Alert: {{ optional($notification->student)->name ?? 'Unknown Student' }}
                                                   
                                                </p>
                                                <span
                                                    class='message-meta'>{{ \Carbon\Carbon::parse($notification->created_at)->format('n/j/Y, h:i A') }}</span>
                                            </div>
                                            {{-- Message from student removed: only battery, signal, and location are displayed --}}
                                            @php
                                                // Get the student's latest GPS record from the locations table
                                                $latestLocation = $notification->student
                                                    ? \App\Models\Location::where('student_id', $notification->student->id)
                                                        ->orderBy('recorded_at', 'desc')
                                                        ->first()
                                                    : null;

                                                // Prefer the notification snapshot first, then live student data
                                                $currentBattery = $notification->battery_level ?? optional($notification->student)->battery_level;
                                                $currentSignal = $notification->signal_status ?? optional($notification->student)->signal_status;
                                                $currentLat = $latestLocation->latitude ?? optional($notification->student)->latitude ?? $notification->latitude;
                                                $currentLng = $latestLocation->longitude ?? optional($notification->student)->longitude ?? $notification->longitude;
                                            @endphp

                                            <div class='message-meta'
                                                style='margin-top:12px; background: #f8fafc; padding: 12px; border-radius: 10px; border: 1px solid rgba(0,0,0,0.05);'>
                                                <div
                                                    style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px;">
                                                    <div>
                                                        <div
                                                            style="font-size: 10px; text-transform: uppercase; font-weight: 800; color: var(--muted); letter-spacing: 0.5px;">
                                                            Live Battery</div>
                                                        <div class="notification-battery-display-{{ $notification->id }}"
                                                            style="font-weight: 700; color: {{ ($currentBattery ?? 0) < 20 ? 'var(--red)' : '#404040' }}; font-size: 14px; margin-top: 2px;">
                                                            🔋 {{ isset($currentBattery) ? $currentBattery . '%' : 'N/A' }}
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <div
                                                            style="font-size: 10px; text-transform: uppercase; font-weight: 800; color: var(--muted); letter-spacing: 0.5px;">
                                                            Live Signal</div>
                                                        @php
                                                            $sigLower = strtolower($currentSignal ?? '');
                                                            $sigColor = '#FF9933'; // Default to warning accent
                                                            if (Str::contains($sigLower, ['excellent', 'strong', 'good'])) $sigColor = '#009DE1';
                                                            elseif (Str::contains($sigLower, 'fair')) $sigColor = '#FF9933';
                                                            elseif (empty($sigLower) || $sigLower === 'n/a') $sigColor = '#404040';
                                                        @endphp
                                                        <div style="font-weight: 700; color: {{ $sigColor }}; font-size: 14px; margin-top: 2px;">
                                                            {!! Str::contains($sigLower, ['excellent', 'strong', 'good', 'fair']) ? '📶' : '⚠️' !!}
                                                            {{ $currentSignal ?? 'N/A' }}
                                                        </div>
                                                    </div>
                                                    <div style="grid-column: span 2;">
                                                        <div
                                                            style="font-size: 10px; text-transform: uppercase; font-weight: 800; color: var(--muted); letter-spacing: 0.5px;">
                                                            Last Known Location</div>
                                                        <div style="font-weight: 700; color: var(--blue); font-size: 13px; margin-top: 2px;">
                                                            @if($currentLat)
                                                                <a href="/tracking?student_id={{ optional($notification->student)->student_id ?? $notification->student_id }}"
                                                                    style="text-decoration: none; color: inherit; display: flex; align-items: center; gap: 4px;">
                                                                    📍 {{ number_format($currentLat, 5) }}, {{ number_format($currentLng, 5) }}
                                                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none"
                                                                        stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                                                                        stroke-linejoin="round">
                                                                        <line x1="7" y1="17" x2="17" y2="7"></line>
                                                                        <polyline points="7 7 17 7 17 17"></polyline>
                                                                    </svg>
                                                                </a>
                                                            @else
                                                                {{ $notification->location ?? 'Location Unavailable' }}
                                                            @endif
                                                        </div>
                                                    </div>

                                                    @if($notification->status !== 'resolved')
                                                        <div
                                                            style="grid-column: span 4; margin-top: 8px; padding-top: 12px; border-top: 1px dashed rgba(0,0,0,0.1); display: flex; justify-content: flex-end; gap: 10px;">
                                                        {{-- Acknowledged (Mark as Seen) --}}
                                                        @if(!$notification->read)
                                                            <form method='POST' action='/notifications/{{ $notification->id }}/acknowledge'
                                                                style='display:inline;'>
                                                                @csrf
                                                                <button class='action-btn'
                                                                    style="font-size:11px; padding:8px 16px; border-radius: 8px; background: var(--blue); color: #fff; border: none; font-weight: 700; cursor: pointer; display: flex; align-items: center; gap: 4px;"
                                                                    type='submit'>
                                                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                                                                        stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                                                                        stroke-linejoin="round">
                                                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                                                        <circle cx="12" cy="12" r="3"></circle>
                                                                    </svg>
                                                                    Acknowledged
                                                                </button>
                                                            </form>
                                                        @endif

                                                        {{-- Mark as Resolved (Safe) --}}
                                                        <form method='POST' action='/notifications/{{ $notification->id }}/resolve'
                                                            style='display:inline;'>
                                                            @csrf
                                                            <button class='action-btn ack-btn'
                                                                style="font-size:11px; padding:8px 16px; border-radius: 8px; font-weight: 700; display: flex; align-items: center; gap: 4px;"
                                                                type='submit'>
                                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                                                                    stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                                                                    stroke-linejoin="round">
                                                                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                                                    <polyline points="22 4 12 14.01 9 11.01"></polyline>
                                                                </svg>
                                                                Mark as Resolved
                                                            </button>
                                                        </form>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                <div class='message-item'
                                    style='background:#f8fafc;border-style:dashed;text-align:center;color:var(--muted);'>No blackout
                                    events.</div>
                            @endforelse
                        @endif
                    @elseif($tab === 'broadcast')
                    @forelse($notifications as $notification)
                        <div class='message-item broadcast-item-clickable'
                            onclick="showBroadcastDetails('{{ addslashes($notification->subject ?? 'Broadcast Notification') }}', '{{ addslashes($notification->message) }}', '{{ \Carbon\Carbon::parse($notification->created_at)->format('n/j/Y, h:i A') }}', '{{ addslashes($notification->sender_name ?? ($notification->sender_type === 'admin' ? 'Admin' : 'System')) }}')"
                            style="border-left: 4px solid var(--yellow);">
                            <div class='message-head'>
                                <p class='message-title'>
                                    {{ $notification->subject ?? 'No Subject' }}
                                    <span class='badge-pill'
                                        style='background:rgba(255, 153, 51, 0.16);color:var(--accent);border-color:rgba(255, 153, 51, 0.25);'>Outbound</span>
                                </p>
                                <span
                                    class='message-meta'>{{ \Carbon\Carbon::parse($notification->created_at)->format('n/j/Y, h:i A') }}</span>
                            </div>
                            <div class='message-meta' style='margin-top:8px;'>
                                <span style='font-weight:700;color:var(--muted);'>Sent by:</span> {{ $notification->sender_name ?? ($notification->sender_type === 'admin' ? 'Admin' : 'System') }}
                            </div>
                            <div class='message-meta' style='margin-top:6px;'>
                                @if($notification->class && $notification->class !== 'all') Class: {{ $notification->class }} |
                                @endif
                                <span class='badge-pill' style="font-size: 10px;">Sent to All</span>
                            </div>
                        </div>
                    @empty
                        <div class='message-item'
                            style='background:#fff;border-color:rgba(34, 187, 234, 0.18);text-align:center;padding:30px;color:var(--muted);'>No
                            broadcast history.</div>
                    @endforelse
                @else
                    {{-- MESSENGER-STYLE STUDENT CHAT --}}
                    <div class="messenger-wrapper">
                        {{-- Sidebar --}}
                        <aside class="student-panel">
                            <div class="panel-header">
                                <div class="panel-title" style="display: flex; align-items: center; gap: 10px;">
                                    Student Chats
                                    <button id="view-students-btn" type="button" onclick="openStudentsModal()" style="margin-left: 50px; padding: 4px 12px; font-size: 12px; font-weight: 700; border-radius: 8px; border: 1px solid #009DE1; background: #fff; color: #009DE1; cursor: pointer; transition: background 0.2s;">
                                        View students
                                    </button>
                                </div>
                                    <!-- All Students Modal -->
                                    <div id="all-students-modal" onclick="closeStudentsModal(event)" style="display:none; position:fixed; z-index:9999; left:0; top:0; width:100vw; height:100vh; background:rgba(0,0,0,0.18); align-items:center; justify-content:center;">
                                        <div onclick="event.stopPropagation()" style="background:#fff; border-radius:16px; max-width:480px; width:96vw; max-height:80vh; overflow:auto; box-shadow:0 8px 32px rgba(0,0,0,0.18); padding:28px 24px 18px 24px; position:relative;">
                                            <button type="button" onclick="closeStudentsModal()" style="position:absolute; top:12px; right:12px; background:none; border:none; font-size:20px; color:var(--muted); cursor:pointer;">&times;</button>
                                            <h2 style="font-size:20px; font-weight:800; margin-bottom:12px;">All Students</h2>
                                            <div style="margin-bottom:12px; display:flex; gap:10px; align-items:center;">
                                                <label for="modal-class-filter" style="font-weight:700; font-size:13px;">Class:</label>
                                                <select id="modal-class-filter" style="padding:6px 12px; border-radius:8px; border:1px solid var(--line); font-size:13px;">
                                                    <option value="all">All</option>
                                                    <option value="2026">2026</option>
                                                    <option value="2027">2027</option>
                                                    <option value="2028">2028</option>
                                                </select>
                                            </div>
                                            <div style="margin-bottom:10px;">
                                                <input id="modal-student-search" type="text" placeholder="Search students..." style="width:100%; padding:7px 12px; border-radius:8px; border:1px solid var(--line); font-size:13px;">
                                            </div>
                                            <div id="modal-student-list" style="max-height:48vh; overflow:auto;">
                                                <div style="text-align:center; color:var(--muted); font-size:13px; padding:30px 0;">Loading...</div>
                                            </div>
                                        </div>
                                    </div>
                                <div class="search-box">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                        <circle cx="11" cy="11" r="8" />
                                        <line x1="21" y1="21" x2="16.65" y2="16.65" />
                                    </svg>
                                    <input type="text" id="student-search" placeholder="Search students..."
                                        autocomplete="off" oninput="filterStudentList()">
                                </div>
                               
                            </div>
                            <div class="student-list" id="student-list-container">
                                @php $currentLetter = ''; @endphp
                                @forelse($sidebarStudents as $student)
                                    @php
                                        $letter = strtoupper(substr($student->name, 0, 1));
                                        $isFemale = strtolower($student->gender ?? '') === 'female';
                                        $unread = $unreadCounts[$student->id] ?? 0;
                                        if ($unread === 0) {
                                            $unreadQuery = \App\Models\Notification::where(function ($q) use ($student) {
                                                $q->where('student_id', $student->id)
                                                  ->orWhere('student_id', $student->student_id);
                                            })
                                            ->where('sender_type', 'student')
                                            ->where('read', false);

                                            if (isset($currentAdminId) && in_array($currentAdminRole, ['education', 'main'])) {
                                                $unreadQuery->where('admin_id', $currentAdminId);
                                            }

                                            $unread = $unreadQuery->count();
                                        }
                                    @endphp
                                    @if($letter !== $currentLetter)
                                        @php $currentLetter = $letter; @endphp
                                        <div class="alpha-heading alpha-row">{{ $letter }}</div>
                                    @endif
                                    <div class="student-row" data-id="{{ $student->id }}"
                                        data-name="{{ strtolower($student->name) }}" data-class="{{ $student->class }}"
                                        data-display-name="{{ $student->name }}" data-student-id="{{ $student->student_id }}"
                                        data-gender="{{ strtolower($student->gender ?? '') }}"
                                        @php
                                            // Resolve profile picture URL — handle both old full-URL and new relative-path formats
                                            $profilePicUrl = '';
                                            if ($student->profile_picture) {
                                                if (str_starts_with($student->profile_picture, 'http')) {
                                                    // Old format: full URL (may have wrong IP) — rewrite using current asset() base
                                                    $picPath = ltrim(parse_url($student->profile_picture, PHP_URL_PATH), '/');
                                                    // $picPath is like: storage/profile_pictures/xxx.jpg
                                                    $profilePicUrl = asset($picPath);
                                                } else {
                                                    // New format: relative path like profile_pictures/xxx.jpg
                                                    $profilePicUrl = asset('storage/' . $student->profile_picture);
                                                }
                                            }
                                        @endphp
                                        data-profile-picture="{{ $profilePicUrl }}">
                                        <div class="avatar {{ $isFemale ? 'female' : '' }}">
                                            @if($profilePicUrl)
                                                <img src="{{ $profilePicUrl }}" alt="{{ $student->name }}" class="profile-pic" onerror="this.style.display='none';this.parentElement.textContent='{{ strtoupper(substr($student->name, 0, 1)) }}'">
                                            @else
                                                {{ strtoupper(substr($student->name, 0, 1)) }}
                                            @endif
                                            @if($unread > 0)
                                            <div class="unread-badge">{{ $unread > 99 ? '99+' : $unread }}</div>@endif
                                        </div>
                                        <div class="student-info" style="flex: 1;">
                                            <div class="student-name">{{ $student->name }}</div>
                                            <div class="student-sub">{{ $student->student_id }} · {{ $student->class }}</div>
                                        </div>
                                        <div class="student-actions" style="position: relative;">
                                            <button type="button" class="three-dot-btn" onclick="toggleStudentMenu(event, {{ $student->id }})" style="background:none; border:none; color:var(--muted); cursor:pointer; padding:4px;">
                                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <circle cx="12" cy="12" r="1"></circle>
                                                    <circle cx="12" cy="5" r="1"></circle>
                                                    <circle cx="12" cy="19" r="1"></circle>
                                                </svg>
                                            </button>
                                            <div id="student-menu-{{ $student->id }}" class="student-dropdown" style="display:none; position:absolute; right:0; top:100%; background:#fff; border:1px solid var(--line); border-radius:8px; box-shadow:0 4px 12px rgba(0,0,0,0.1); z-index:100; min-width:150px;">
                                                <button type="button" onclick="deleteStudentConversation(event, {{ $student->id }})" style="width:100%; text-align:left; padding:8px 12px; background:none; border:none; color:var(--red); font-size:13px; font-weight:600; cursor:pointer;">
                                                    Delete Conversation
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div style="padding: 40px 20px; text-align: center; color: var(--muted); font-size: 13px;">
                                        No students found.</div>
                                @endforelse
                            </div>
                        </aside>

                        {{-- Chat Panel --}}
                        <section class="chat-panel">
                            <div class="chat-empty" id="chat-empty">
                                <div
                                    style="background: #eff6ff; width: 64px; height: 64px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-bottom: 20px;">
                                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="var(--blue)"
                                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z" />
                                    </svg>
                                </div>
                                <p style="font-weight: 800; color: #404040; font-size: 16px;">Select a student</p>
                                <p style="font-size: 13px; color: var(--muted);">Click on a student to see message history
                                </p>
                            </div>

                            <div class="chat-header" id="chat-header" style="display:none;">
                                <div class="chat-header-profile">
                                    <div class="avatar" id="chat-header-avatar"></div>
                                    <div class="student-info">
                                        <div class="chat-header-name" id="chat-header-name">Student Name</div>
                                        <div class="chat-header-sub" id="chat-header-sub">ID · Class</div>
                                    </div>
                                </div>
                            </div>

                            <div class="chat-messages" id="chat-messages" style="display:none;"></div>

                            <div class="chat-input-area" id="chat-input-area" style="display:none;">
                                @if($canMessage)
                                    <div class="input-wrap">
                                        <textarea id="msg-input" rows="1" placeholder="Type a message..."
                                            oninput="this.style.height='auto';this.style.height=this.scrollHeight+'px'"></textarea>
                                    </div>
                                    <button type="button" class="send-btn" id="send-btn" title="Send message">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                            stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                            <line x1="22" y1="2" x2="11" y2="13" />
                                            <polygon points="22 2 15 22 11 13 2 9 22 2" />
                                        </svg>
                                    </button>
                                @else
                                    <div style="width:100%; padding:12px 14px; border-radius:12px; background:rgba(255, 153, 51, 0.12); color:var(--accent); font-size:13px; font-weight:600; text-align:center;">
                                        Messaging is available for education staff only.
                                    </div>
                                @endif
                            </div>
                        </section>
                    </div>
                @endif
            </div>
        </div>
        </div>
    


    <script>
        function pollDashboardStats() {
            fetch('/api/dashboard/stats')
                .then(res => res.json())
                .then(data => {
                    const onlineEl = document.getElementById('online-count');
                    const offlineEl = document.getElementById('offline-count');
                    const timeEl = document.getElementById('latest-time');
                    const dateEl = document.getElementById('latest-date');
                    if (onlineEl) onlineEl.textContent = data.onlineCount;
                    if (offlineEl) offlineEl.textContent = data.offlineCount;
                    if (timeEl) timeEl.textContent = data.latestTime ?? '—';
                    if (dateEl) dateEl.textContent = data.latestDate ?? '—';

                    // LIVE TELEMETRY UPDATE for Notifications
                    if (data.students) {
                        data.students.forEach(s => {
                            const batteryDivs = document.querySelectorAll('.battery-display-' + s.id);
                            batteryDivs.forEach(div => {
                                const level = s.battery_level;
                                if (level !== null && level !== undefined) {
                                    div.textContent = '🔋 ' + level + '%';
                                    div.style.color = level < 20 ? 'var(--accent)' : '#404040';
                                } else {
                                    div.textContent = '🔋 N/A';
                                    div.style.color = '#404040';
                                }
                            });
                        });
                    }
                    // Do not update notification snapshot batteries; these should remain the battery level captured when the alert was created.
                })
                .catch(err => console.error('Dashboard poll error:', err));
        }

        // Poll the current page and update UI without disrupting an open conversation
        function pollNotifications() {
            if (isStudentsModalOpen) return;

            fetch(window.location.href, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(res => res.text())
                .then(html => {
                    try {
                        const parser = new DOMParser();
                        const doc = parser.parseFromString(html, 'text/html');

                        // Always refresh the cards (summary stats)
                        const newCards = doc.querySelector('.cards');
                        const cardsEl = document.querySelector('.cards');
                        if (newCards && cardsEl) cardsEl.innerHTML = newCards.innerHTML;

                        // Always refresh the tabs (notification counts)
                        const newTabs = doc.querySelector('.tabs');
                        const tabsEl = document.querySelector('.tabs');
                        if (newTabs && tabsEl) tabsEl.innerHTML = newTabs.innerHTML;

                        // If a student convo is currently open, only refresh the student list (so active chat isn't removed)
                        const studentListEl = document.getElementById('student-list-container');
                        const newStudentList = doc.getElementById('student-list-container');

                        if (typeof activeStudentId !== 'undefined' && activeStudentId) {
                            if (newStudentList && studentListEl) {
                                studentListEl.innerHTML = newStudentList.innerHTML;
                                // Re-apply the 'active' class to the currently selected student row if it still exists
                                const currentRow = document.querySelector(`.student-row[data-id="${activeStudentId}"]`);
                                if (currentRow) {
                                    document.querySelectorAll('.student-row').forEach(r => r.classList.remove('active'));
                                    currentRow.classList.add('active');
                                }
                            }

                            // Do not replace the chat panel content to avoid interrupting the open conversation
                            return;
                        }

                        // Otherwise, replace the whole messages area (used when no convo is open)
                        const newMessages = doc.querySelector('.messages');
                        const messagesEl = document.querySelector('.messages');
                        if (newMessages && messagesEl) {
                            messagesEl.innerHTML = newMessages.innerHTML;
                        }
                    } catch (err) {
                        console.error('Error parsing notifications HTML:', err);
                    }
                })
                .catch(err => console.error('Notifications poll error:', err));
        }

        // Track active conversation state so polling doesn't remove the open chat
        let activeStudentId = null;
        let activeStudentName = '';
        let activeIsFemale = false;
        let activeStudentProfilePic = ''; // Store active student's profile picture URL
        let msgPollTimer = null;

        document.addEventListener('DOMContentLoaded', function () {
            pollDashboardStats();
            setInterval(pollDashboardStats, 10000); // Sync every 10s

            // Start notifications auto-refresh and schedule periodic sync
            try {
                pollNotifications();
                setInterval(pollNotifications, 10000); // Refresh notifications every 10s
            } catch (err) {
                console.error('Failed to start notifications poll:', err);
            }

            // Global click listener for student rows (delegation)
            document.addEventListener('click', function (e) {
                const row = e.target.closest('.student-row');
                if (row) {
                    try {
                        if (row.classList.contains('modal-student-row')) {
                            closeStudentsModal();
                        }
                        handleStudentSelection(row);
                    } catch (err) {
                        console.error('Selection error:', err);
                    }
                }
            });

            // Student message filters
            document.getElementById('student-search')?.addEventListener('input', filterStudentList);
            document.getElementById('student-class-filter')?.addEventListener('change', filterStudentList);
            filterStudentList();
        });

        // --- MESSENGER LOGIC ---
        // --- Broadcast Details Modal Handling ---
        let isStudentsModalOpen = false;

        function openStudentsModal() {
            const modal = document.getElementById('all-students-modal');
            if (!modal) return;
            isStudentsModalOpen = true;
            modal.style.display = 'flex';
            const modalClassFilter = document.getElementById('modal-class-filter');
            if (modalClassFilter && !modalClassFilter.dataset.listenerAttached) {
                modalClassFilter.addEventListener('change', loadAllStudents);
                modalClassFilter.dataset.listenerAttached = '1';
            }
            const modalSearch = document.getElementById('modal-student-search');
            if (modalSearch && !modalSearch.dataset.listenerAttached) {
                modalSearch.addEventListener('input', filterModalStudentList);
                modalSearch.dataset.listenerAttached = '1';
            }
            loadAllStudents();
        }

        function closeStudentsModal(event) {
            const modal = document.getElementById('all-students-modal');
            if (!modal) return;
            if (event && event.target !== modal) return;
            isStudentsModalOpen = false;
            modal.style.display = 'none';
        }

        function openStudentFromModal(row) {
            closeStudentsModal();
            handleStudentSelection(row);
        }

        function loadAllStudents() {
            const classVal = document.getElementById('modal-class-filter')?.value || 'all';
            const listDiv = document.getElementById('modal-student-list');
            if (!listDiv) return;

            listDiv.innerHTML = '<div style="text-align:center; color:var(--muted); font-size:13px; padding:30px 0;">Loading...</div>';
            fetch(`/students/all/json?class=${encodeURIComponent(classVal)}`)
                .then(res => res.json())
                .then(data => {
                    if (!data.students || data.students.length === 0) {
                        listDiv.innerHTML = '<div style="text-align:center; color:var(--muted); font-size:13px; padding:30px 0;">No students found.</div>';
                        return;
                    }

                    const selectedClass = classVal.toString();
                    const students = selectedClass === 'all'
                        ? data.students
                        : data.students.filter(stu => ((stu['class'] || '').toString() === selectedClass));

                    if (students.length === 0) {
                        listDiv.innerHTML = '<div style="text-align:center; color:var(--muted); font-size:13px; padding:30px 0;">No students found.</div>';
                        return;
                    }

                    let html = '';
                    students.forEach(stu => {
                        const online = Boolean(stu['status']);
                        const studentName = (stu['name'] || 'Student');
                        const studentClass = (stu['class'] || 'N/A');
                        const profilePic = stu['profile_picture'] || '';
                        // Build avatar: show profile image if available, otherwise show initial
                        const avatarHtml = profilePic
                            ? `<img src="${profilePic}" alt="${escHtml(studentName)}" style="width:28px;height:28px;border-radius:50%;object-fit:cover;display:block;">`
                            : `<span style="width:28px;height:28px;border-radius:50%;background:${online ? 'rgba(34, 187, 234, 0.12)' : '#F8FBFF'};color:${online ? '#009DE1' : 'var(--muted)'};display:flex;align-items:center;justify-content:center;font-weight:800;font-size:15px;flex-shrink:0;">${(studentName || 'S').charAt(0).toUpperCase()}</span>`;
                        html += `<div class="student-row modal-student-row" data-id="${escHtml(stu['student_id'] || stu['id'])}" data-name="${(studentName).toLowerCase()}" data-class="${escHtml(studentClass)}" data-display-name="${escHtml(studentName)}" data-student-id="${escHtml(stu['student_id'] || '')}" data-gender="" data-profile-picture="${escHtml(profilePic)}" onclick="openStudentFromModal(this)" style="display:flex;align-items:center;gap:12px;padding:8px 0;border-bottom:1px solid rgba(34, 187, 234, 0.08);cursor:pointer;">
                            ${avatarHtml}
                            <span style="flex:1;">
                                <span style="font-weight:700;">${escHtml(studentName)}</span><br>
                                <span style="font-size:12px;color:var(--muted);">${escHtml(stu['student_id'] || '')} · ${escHtml(studentClass)}</span>
                            </span>
                            <span style="font-size:12px;font-weight:700;color:${online ? '#009DE1' : 'var(--muted)'};">${online ? 'Online' : 'Offline'}</span>
                        </div>`;
                    });
                    listDiv.innerHTML = html;
                    filterModalStudentList();
                })
                .catch(() => {
                    listDiv.innerHTML = '<div style="text-align:center; color:var(--accent); font-size:13px; padding:30px 0;">Error loading students.</div>';
                });
        }

        function filterModalStudentList() {
            const q = (document.getElementById('modal-student-search')?.value || '').toLowerCase();
            document.querySelectorAll('#modal-student-list .modal-student-row').forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(q) ? 'flex' : 'none';
            });
        }

        function showBroadcastDetails(subject, message, time, senderLabel = '') {
            const backdrop = document.getElementById('broadcast-modal');
            document.getElementById('modal-subject').textContent = subject;
            document.getElementById('modal-message').innerHTML = message;
            document.getElementById('modal-time').textContent = time;
            const senderEl = document.getElementById('modal-sender');
            if (senderEl) {
                senderEl.textContent = senderLabel ? 'Sent by: ' + senderLabel : 'Sent by: Admin';
            }
            backdrop.style.display = 'flex';
        }

        function closeBroadcastModal(e) {
            if (e.target.classList.contains('modal-backdrop')) {
                e.target.style.display = 'none';
            }
        }

        // --- Quill Rich Text Editor Initialization ---
        var quill;
        document.addEventListener('DOMContentLoaded', function () {
            if (document.getElementById('quill-editor')) {
                quill = new Quill('#quill-editor', {
                    theme: 'snow',
                    modules: {
                        toolbar: [
                            [{ 'header': [1, 2, 3, false] }],
                            ['bold', 'italic', 'underline', 'strike'],
                            [{ 'list': 'ordered' }, { 'list': 'bullet' }],
                            [{ 'align': [] }],
                            ['clean']
                        ]
                    },
                    placeholder: 'Type your announcement here...'
                });

                const form = document.getElementById('broadcast-form');
                if (form) {
                    form.onsubmit = function () {
                        const messageInput = document.getElementById('broadcast-message-input');
                        messageInput.value = quill.root.innerHTML;
                    };
                }
            }
        });

        function toggleBroadcastForm() {
            const container = document.getElementById('broadcast-form-container');
            const triggerBtn = document.getElementById('toggle-broadcast-btn');
            if (container.style.display === 'none') {
                container.style.display = 'block';
                triggerBtn.style.display = 'none';
            } else {
                container.style.display = 'none';
                triggerBtn.style.display = 'flex';
            }
        }

        document.getElementById('modal-class-filter')?.addEventListener('change', loadAllStudents);
        document.getElementById('modal-student-search')?.addEventListener('input', filterModalStudentList);

        function filterStudentList() {
            const q = document.getElementById('student-search').value.toLowerCase();
            const cls = document.getElementById('student-class-filter')?.value || 'all';

            document.querySelectorAll('.student-row').forEach(row => {
                const nameMatch = row.dataset.name.includes(q);
                const classMatch = cls === 'all' || row.dataset.class === cls;
                row.style.display = (nameMatch && classMatch) ? 'flex' : 'none';
            });

            // Hide alpha headings if no visible students in that group
            document.querySelectorAll('.alpha-row').forEach(heading => {
                let hasVisible = false;
                let next = heading.nextElementSibling;
                while (next && !next.classList.contains('alpha-row')) {
                    if (next.style.display !== 'none') { hasVisible = true; break; }
                    next = next.nextElementSibling;
                }
                heading.style.display = hasVisible ? 'block' : 'none';
            });
        }

        // Renamed to handleStudentSelection for better clarity with the delegation
        function handleStudentSelection(row) {
            const id = row.getAttribute('data-id');
            const name = row.getAttribute('data-display-name') || 'Student';
            const studentId = row.getAttribute('data-student-id') || 'N/A';
            const cls = row.getAttribute('data-class') || 'N/A';
            const gender = row.getAttribute('data-gender') || '';

            // UI Updates
            document.querySelectorAll('.student-row').forEach(r => r.classList.remove('active'));
            row.classList.add('active');

            const dot = row.querySelector('.unread-badge');
            if (dot) dot.remove();

            activeStudentId = id;
            activeStudentName = name;
            activeIsFemale = (gender === 'female');
            activeStudentProfilePic = row.getAttribute('data-profile-picture') || '';

            // Header Updates
            const avatarEl = document.getElementById('chat-header-avatar');
            if (avatarEl) {
                if (activeStudentProfilePic) {
                    avatarEl.innerHTML = '<img src="' + activeStudentProfilePic + '" alt="' + name + '" class="profile-pic">';
                    avatarEl.className = 'avatar';
                } else {
                    avatarEl.textContent = name.charAt(0).toUpperCase();
                    avatarEl.className = 'avatar' + (activeIsFemale ? ' female' : '');
                }
            }

            const nameEl = document.getElementById('chat-header-name');
            if (nameEl) nameEl.textContent = name;

            const subEl = document.getElementById('chat-header-sub');
            if (subEl) subEl.textContent = studentId + ' · Class ' + cls;

            // Panel Visibility
            const emptyState = document.getElementById('chat-empty');
            if (emptyState) emptyState.style.display = 'none';

            const headerPanel = document.getElementById('chat-header');
            if (headerPanel) headerPanel.style.display = 'flex';

            const msgsPanel = document.getElementById('chat-messages');
            if (msgsPanel) {
                msgsPanel.style.display = 'flex';
                msgsPanel.innerHTML = '<div style="text-align:center;padding:40px;color:var(--muted);font-size:13px;">Loading conversation...</div>';
            }

            const inputPanel = document.getElementById('chat-input-area');
            if (inputPanel) inputPanel.style.display = 'flex';

            closeStudentsModal();
            loadMessages(true);

            clearInterval(msgPollTimer);
            msgPollTimer = setInterval(() => loadMessages(false), 4000);
        }

        // Helper for onclick-style calls if still in HTML
        function openChat(row) {
            handleStudentSelection(row);
        }

        async function loadMessages(forceScroll) {
            if (!activeStudentId) return;
            try {
                const res = await fetch(`/messages/${activeStudentId}/json`);
                if (!res.ok) throw new Error('Server error ' + res.status);
                const data = await res.json();
                renderMessages(data.messages, forceScroll);
            } catch (e) {
                console.error('Poll error:', e);
                const container = document.getElementById('chat-messages');
                if (container) container.innerHTML = '<div style="color:red;padding:20px;text-align:center;">Error loading history: ' + e.message + '</div>';
            }
        }

        function renderMessages(messages, forceScroll) {
            try {
                const container = document.getElementById('chat-messages');
                if (!container) return;
                const wasAtBottom = container.scrollHeight - container.scrollTop - container.clientHeight < 80;

                let html = '';
                let prevDate = '';

                if (!messages || !Array.isArray(messages) || messages.length === 0) {
                    html = '<div style="text-align:center;color:var(--muted);font-size:13px;margin-top:40px;">No messages yet. Say hello! 👋</div>';
                } else {
                    messages.forEach(msg => {
                        const d = new Date(msg.created_at);
                        const dateStr = d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
                        const timeStr = d.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
                        const isAdmin = msg.sender_type === 'admin';
                        const isEmergency = msg.type === 'sos' || msg.type === 'blackout';

                        if (dateStr !== prevDate) {
                            html += `<div class="date-sep"><span>${dateStr}</span></div>`;
                            prevDate = dateStr;
                        }

                        const bubClass = isAdmin ? 'admin' : 'student';
                        const sosClass = isEmergency ? ' sos-alert' : '';
                        const prefix = isEmergency ? '<b>🚨 ALERT:</b> ' : '';
                        const senderLabel = isAdmin && msg.sender_name ? `<div class="sender-label">${escHtml(msg.sender_name)}</div>` : '';

                        // Build avatar for student messages using profile picture
                        let avatarHtml = '';
                        if (!isAdmin) {
                            if (activeStudentProfilePic) {
                                avatarHtml = `<img src="${activeStudentProfilePic}" alt="${escHtml(activeStudentName)}" style="width:28px;height:28px;border-radius:50%;object-fit:cover;flex-shrink:0;align-self:flex-end;" onerror="this.style.display='none'">` ;
                            } else {
                                const initial = (activeStudentName || 'S').charAt(0).toUpperCase();
                                avatarHtml = `<div style="width:28px;height:28px;border-radius:50%;background:rgba(34,187,234,0.18);color:#009DE1;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:12px;flex-shrink:0;align-self:flex-end;">${initial}</div>`;
                            }
                        }

                        html += `
                        <div class="bubble-wrap ${bubClass}">
                            ${!isAdmin ? avatarHtml : ''}
                            <div class="bubble-content">
                                ${senderLabel}
                                <div class="bubble ${bubClass}${sosClass}">${prefix}${escHtml(msg.message)}</div>
                                <div class="bubble-time">${timeStr}</div>
                            </div>
                            ${isAdmin ? avatarHtml : ''}
                        </div>`;
                    });
                }

                container.innerHTML = html;
                if (forceScroll || wasAtBottom) {
                    container.scrollTop = container.scrollHeight;
                }
            } catch (renderErr) {
                console.error('Render error:', renderErr);
                const container = document.getElementById('chat-messages');
                if (container) container.innerHTML = '<div style="color:red;padding:20px;text-align:center;">Rendering error: ' + renderErr.message + '</div>';
            }
        }

        const canSendStudentMessages = {{ $canMessage ? 'true' : 'false' }};

        async function sendMessage() {
            const input = document.getElementById('msg-input');
            const btn = document.getElementById('send-btn');
            const text = input?.value.trim() || '';

            if (!text) {
                showSendError('Please type a reply before sending.');
                input?.focus();
                return;
            }

            if (!canSendStudentMessages) {
                showSendError('Only education staff can send student replies.');
                return;
            }

            if (!activeStudentId) {
                showSendError('Select a student conversation first.');
                return;
            }

            if (btn) btn.disabled = true;

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';

            try {
                const res = await fetch(`/messages/new/${activeStudentId}`, {
                    method: 'POST',
                    credentials: 'same-origin',
                    redirect: 'follow',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ message: text })
                });

                if (!res.ok) {
                    let errMsg = `Server error (${res.status})`;
                    try {
                        const j = await res.json();
                        errMsg = j.message || errMsg;
                    } catch (_) {
                        if (res.status === 419 || res.status === 401 || res.status === 403) {
                            errMsg = 'Your session expired or is not authorized. Please refresh and sign in again.';
                        }
                    }
                    showSendError(errMsg);
                    return;
                }

                const data = await res.json();
                if (!data || data.success === false) {
                    showSendError(data?.message || 'Message could not be sent.');
                    return;
                }

                input.value = '';
                input.style.height = 'auto';
                await loadMessages(true);
            } catch (e) {
                console.error('Send error:', e);
                showSendError('Network error — could not send message.');
            } finally {
                if (btn) btn.disabled = false;
                input?.focus();
            }
        }

        function showSendError(msg) {
            let toast = document.getElementById('send-error-toast');
            if (!toast) {
                toast = document.createElement('div');
                toast.id = 'send-error-toast';
                toast.style.cssText = 'position:fixed;bottom:24px;left:50%;transform:translateX(-50%);background:var(--accent);color:#fff;padding:10px 22px;border-radius:10px;font-size:13px;font-weight:700;z-index:9999;box-shadow:0 4px 16px rgba(0,0,0,0.2);';
                document.body.appendChild(toast);
            }
            toast.textContent = '⚠️ ' + msg;
            toast.style.display = 'block';
            setTimeout(() => { toast.style.display = 'none'; }, 4000);
        }


        function escHtml(str) {
            if (!str) return '';
            return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
        }

        document.addEventListener('click', function (e) {
            const sendBtn = e.target.closest('#send-btn');
            if (sendBtn) {
                e.preventDefault();
                sendMessage();
            }
        });

        document.addEventListener('keydown', function (e) {
            const input = e.target.closest('#msg-input');
            if (input && e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                sendMessage();
            }
        });
    </script>

    <!-- Broadcast Details Modal -->
    <div id="broadcast-modal" class="modal-backdrop" onclick="closeBroadcastModal(event)">
        <div class="modal-content" onclick="event.stopPropagation()">
            <button class="modal-close" onclick="document.getElementById('broadcast-modal').style.display='none'">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                    stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
            <div id="modal-time"
                style="font-size: 11px; font-weight: 800; color: var(--muted); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px;">
            </div>
            <div id="modal-sender" style="font-size: 12px; font-weight: 700; color: var(--blue-dark); margin-bottom: 8px;"></div>
            <h2 id="modal-subject" class="modal-subject"></h2>

            <div class="modal-body-container">
                <div class="modal-scroll-area">
                    <div id="modal-message" style="line-height: 1.6; font-size: 15px; color: #334155;"></div>
                </div>
            </div>

            <div style="margin-top: 24px; display: flex; justify-content: flex-end;">
                <button onclick="document.getElementById('broadcast-modal').style.display='none'"
                    style="background: #F8FBFF; color: var(--muted); border: none; padding: 10px 24px; border-radius: 10px; font-weight: 700; font-size: 14px; cursor: pointer;">
                    Close
                </button>
            </div>
        </div>
    </div>


    <!-- SOS History Modal -->
    <div id="sos-history-modal" class="modal-backdrop" onclick="closeSOSHistoryModal(event)">
        <div class="modal-content" onclick="event.stopPropagation()">
            <button class="modal-close" onclick="document.getElementById('sos-history-modal').style.display='none'">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                    stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 16px; padding-right: 40px;">
                <div>
                    <h2 class="modal-subject">SOS Alerts History</h2>
                    <p style="font-size: 13px; color: var(--muted); margin-top: 4px; margin-bottom: 0;">Resolved SOS alerts.</p>
                </div>
                <button type="button" onclick="document.getElementById('deleteArchiveModal').style.display='flex'" style="background: rgba(220, 38, 38, 0.1); color: var(--red); border: 1px solid rgba(220, 38, 38, 0.2); padding: 8px 16px; border-radius: 8px; font-weight: 700; font-size: 13px; cursor: pointer; display: flex; align-items: center; gap: 6px; transition: all 0.2s;" onmouseover="this.style.background='rgba(220, 38, 38, 0.2)'" onmouseout="this.style.background='rgba(220, 38, 38, 0.1)'">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="3 6 5 6 21 6"></polyline>
                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                    </svg>
                    Delete all Archive
                </button>
            </div>

            <div class="modal-body-container" style="background: transparent; border: none; padding: 0;">
                <div class="modal-scroll-area" style="padding: 0;">
                    @forelse($notifications->where('type', 'sos')->where('status', 'resolved') as $notification)
                        <div class='message-item' style="border-left: 4px solid var(--muted); opacity: 0.8; margin-bottom: 12px; background: #fff;">
                            <div class='message-head'>
                                <p class='message-title'>
                                    SOS Alert: {{ optional($notification->student)->name ?? 'Unknown Student' }}
                                    ({{ optional($notification->student)->student_id ?? 'N/A' }})
                                    <span class='badge-pill'
                                        style='background:rgba(34, 187, 234, 0.08);color:rgba(64, 64, 64, 0.68);border-color:rgba(34, 187, 234, 0.18);'>I am Safe
                                        (Resolved)</span>
                                </p>
                                <span class='message-meta'>{{ \Carbon\Carbon::parse($notification->created_at)->format('n/j/Y, h:i A') }}</span>
                            </div>
                                    @php
                                        // Get the student's latest GPS record from the locations table
                                        $latestLocation = $notification->student
                                            ? \App\Models\Location::where('student_id', $notification->student->id)
                                                ->orderBy('recorded_at', 'desc')
                                                ->first()
                                            : null;

                                        // Prefer the notification snapshot first, then live student data
                                        $currentBattery = $notification->battery_level ?? optional($notification->student)->battery_level;
                                        $currentSignal = $notification->signal_status ?? optional($notification->student)->signal_status;
                                        $currentLat = $latestLocation->latitude ?? optional($notification->student)->latitude ?? $notification->latitude;
                                        $currentLng = $latestLocation->longitude ?? optional($notification->student)->longitude ?? $notification->longitude;
                                    @endphp

                                    <div class='message-meta'
                                        style='margin-top:12px; background: #f8fafc; padding: 12px; border-radius: 10px; border: 1px solid rgba(0,0,0,0.05);'>
                                        <!-- MEDIA FEEDS SECTION -->
                                        <div
                                            style="margin-bottom: 12px;">
                                            <!-- Video Feed Container -->
                                            <div
                                                style="background: #404040; border-radius: 8px; padding: 12px; min-height: 160px; display: flex; flex-direction: column; justify-content: center; position: relative; overflow: hidden;">
                                                <div
                                                    style="position: absolute; top: 12px; left: 12px; background: rgba(220, 38, 38, 0.9); color: #fff; font-size: 10px; font-weight: 900; padding: 4px 10px; border-radius: 6px; text-transform: uppercase; z-index: 10; display: flex; align-items: center; gap: 6px; box-shadow: 0 2px 4px rgba(0,0,0,0.2);">
                                                    <span
                                                        style="width: 8px; height: 8px; background: #fff; border-radius: 50%; animation: pulse 1s infinite;"></span>
                                                    Live Video Feed
                                                </div>

                                                @if($notification->video_url || (isset($notification->media_url) && !Str::endsWith($notification->media_url, ['.mp3', '.wav'])))
                                                    <div style="position: relative;">
                                                        <video controls style="width: 100%; border-radius: 6px; max-height: 400px; background: #000;">
                                                            <source src="{{ $notification->video_url ?? $notification->media_url }}"
                                                                type="video/mp4">
                                                        </video>
                                                        <div style="margin-top: 8px; display: flex; justify-content: flex-end;">
                                                            <a href="{{ $notification->video_url ?? $notification->media_url }}" download 
                                                               style="background: rgba(255,255,255,0.1); color: #fff; text-decoration: none; font-size: 11px; padding: 6px 12px; border-radius: 6px; border: 1px solid rgba(255,255,255,0.2); display: flex; align-items: center; gap: 6px; transition: all 0.2s;"
                                                               onmouseover="this.style.background='rgba(255,255,255,0.2)'"
                                                               onmouseout="this.style.background='rgba(255,255,255,0.1)'">
                                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                                                    <polyline points="7 10 12 15 17 10"></polyline>
                                                                    <line x1="12" y1="15" x2="12" y2="3"></line>
                                                                </svg>
                                                                Save to Laptop
                                                            </a>
                                                        </div>
                                                    </div>
                                                @else
                                                    <div style="text-align: center; color: rgba(255,255,255,0.4); padding: 40px 0;">
                                                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none"
                                                            stroke="currentColor" stroke-width="2"
                                                            style="margin-bottom: 12px; opacity: 0.5;">
                                                            <path d="M23 7l-7 5 7 5V7z" />
                                                            <rect x="1" y="5" width="15" height="14" rx="2" ry="2" />
                                                        </svg>
                                                        <div style="font-size: 13px; font-weight: 700;">No Video Feed Available</div>
                                                        <p style="font-size: 11px; opacity: 0.6; margin-top: 4px;">Student device has not uploaded video data</p>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>

                                        <!-- TELEMETRY SECTION (Restored Original Style) -->
                                        <div
                                            style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; padding-top: 12px; border-top: 1px solid rgba(0,0,0,0.05);">
                                            <div>
                                                <div
                                                    style="font-size: 10px; text-transform: uppercase; font-weight: 800; color: var(--muted); letter-spacing: 0.5px;">
                                                    Live Battery</div>
                                                <div class="notification-battery-display-{{ $notification->id }}"
                                                    style="font-weight: 700; color: {{ ($currentBattery ?? 0) < 20 ? 'var(--accent)' : '#404040' }}; font-size: 14px; margin-top: 2px;">
                                                    🔋 {{ isset($currentBattery) ? $currentBattery . '%' : 'N/A' }}
                                                </div>
                                            </div>
                                            <div>
                                                <div
                                                    style="font-size: 10px; text-transform: uppercase; font-weight: 800; color: var(--muted); letter-spacing: 0.5px;">
                                                    Live Signal</div>
                                                @php
                                                    $sigLower = strtolower($currentSignal ?? '');
                                                    $sigColor = '#FF9933'; // Default to warning accent
                                                    if (Str::contains($sigLower, ['excellent', 'strong', 'good'])) $sigColor = '#009DE1';
                                                    elseif (Str::contains($sigLower, 'fair')) $sigColor = '#FF9933';
                                                    elseif (empty($sigLower) || $sigLower === 'n/a') $sigColor = '#404040';
                                                @endphp
                                                <div style="font-weight: 700; color: {{ $sigColor }}; font-size: 14px; margin-top: 2px;">
                                                    {!! Str::contains($sigLower, ['excellent', 'strong', 'good', 'fair']) ? '📶' : '⚠️' !!}
                                                    {{ $currentSignal ?? 'N/A' }}
                                                </div>
                                            </div>
                                            <div style="grid-column: span 2;">
                                                <div
                                                    style="font-size: 10px; text-transform: uppercase; font-weight: 800; color: var(--muted); letter-spacing: 0.5px;">
                                                    Last Known Location</div>
                                                <div
                                                    style="font-weight: 700; color: var(--blue); font-size: 13px; margin-top: 2px;">
                                                    @if($currentLat)
                                                        <a href="/tracking?student_id={{ optional($notification->student)->student_id ?? $notification->student_id }}"
                                                            style="text-decoration: none; color: inherit; display: flex; align-items: center; gap: 4px;">
                                                            📍 {{ number_format($currentLat, 5) }}, {{ number_format($currentLng, 5) }}
                                                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none"
                                                                stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                                                                stroke-linejoin="round">
                                                                <line x1="7" y1="17" x2="17" y2="7"></line>
                                                                <polyline points="7 7 17 7 17 17"></polyline>
                                                            </svg>
                                                        </a>
                                                    @else
                                                        {{ $notification->location ?? 'Location Unavailable' }}
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                    @empty
                        <div class='message-item' style='background:#f8fafc;border-style:dashed;text-align:center;color:var(--muted); padding: 30px;'>No resolved SOS events.</div>
                    @endforelse
                </div>
            </div>

            <div style="margin-top: 24px; display: flex; justify-content: flex-end;">
                <button onclick="document.getElementById('sos-history-modal').style.display='none'"
                    style="background: #F8FBFF; color: var(--muted); border: none; padding: 10px 24px; border-radius: 10px; font-weight: 700; font-size: 14px; cursor: pointer;">
                    Close
                </button>
            </div>
        </div>
    </div>

    <!-- Blackout History Modal -->
    <div id="blackout-history-modal" class="modal-backdrop" onclick="closeBlackoutHistoryModal(event)">
        <div class="modal-content" onclick="event.stopPropagation()">
            <button class="modal-close" onclick="document.getElementById('blackout-history-modal').style.display='none'">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                    stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 16px; padding-right: 40px;">
                <div>
                    <h2 class="modal-subject">Blackout Alerts History</h2>
                    <p style="font-size: 13px; color: var(--muted); margin-top: 4px; margin-bottom: 0;">Resolved blackout alerts.</p>
                </div>
                <button type="button" onclick="document.getElementById('deleteBlackoutArchiveModal').style.display='flex'" style="background: rgba(220, 38, 38, 0.1); color: var(--red); border: 1px solid rgba(220, 38, 38, 0.2); padding: 8px 16px; border-radius: 8px; font-weight: 700; font-size: 13px; cursor: pointer; display: flex; align-items: center; gap: 6px; transition: all 0.2s;" onmouseover="this.style.background='rgba(220, 38, 38, 0.2)'" onmouseout="this.style.background='rgba(220, 38, 38, 0.1)'">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="3 6 5 6 21 6"></polyline>
                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                    </svg>
                    Delete all Archive
                </button>
            </div>

            <div class="modal-body-container" style="background: transparent; border: none; padding: 0;">
                <div class="modal-scroll-area" style="padding: 0;">
                    @forelse($notifications->where('type', 'blackout')->where('status', 'resolved') as $notification)
                        <div class='message-item' style="border-left: 4px solid var(--muted); opacity: 0.8; margin-bottom: 12px; background: #fff;">
                            <div class='message-head'>
                                <p class='message-title'>
                                    Blackout Alert: {{ optional($notification->student)->name ?? 'Unknown Student' }}
                                </p>
                                <span class='message-meta'>{{ \Carbon\Carbon::parse($notification->created_at)->format('n/j/Y, h:i A') }}</span>
                            </div>
                            <div class='message-meta' style='margin-top:12px; background: #f8fafc; padding: 12px; border-radius: 10px; border: 1px solid rgba(0,0,0,0.05);'>
                                <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px;">
                                    <div>
                                        <div style="font-size: 10px; text-transform: uppercase; font-weight: 800; color: var(--muted);">Battery</div>
                                        <div style="font-weight: 700; color: #404040; font-size: 14px; margin-top: 2px;">🔋 {{ $notification->battery_level ?? 'N/A' }}%</div>
                                    </div>
                                    <div>
                                        <div style="font-size: 10px; text-transform: uppercase; font-weight: 800; color: var(--muted);">Signal</div>
                                        <div style="font-weight: 700; color: #404040; font-size: 14px; margin-top: 2px;">📶 {{ $notification->signal_status ?? 'N/A' }}</div>
                                    </div>
                                    <div style="grid-column: span 2;">
                                        <div style="font-size: 10px; text-transform: uppercase; font-weight: 800; color: var(--muted);">Location</div>
                                        <div style="font-weight: 700; color: var(--blue); font-size: 13px; margin-top: 2px;">{{ $notification->location ?? 'Location Unavailable' }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class='message-item' style='background:#f8fafc;border-style:dashed;text-align:center;color:var(--muted); padding: 30px;'>No resolved blackout events.</div>
                    @endforelse
                </div>
            </div>

            <div style="margin-top: 24px; display: flex; justify-content: flex-end;">
                <button onclick="document.getElementById('blackout-history-modal').style.display='none'"
                    style="background: #F8FBFF; color: var(--muted); border: none; padding: 10px 24px; border-radius: 10px; font-weight: 700; font-size: 14px; cursor: pointer;">
                    Close
                </button>
            </div>
        </div>
    </div>
    
    <!-- Delete All Archive Modal -->
    <div id="deleteArchiveModal" style="display: none; position: fixed; z-index: 3000; left: 0; top: 0; width: 100vw; height: 100vh; background-color: rgba(0,0,0,0.5); backdrop-filter: blur(4px); align-items: center; justify-content: center;" onclick="if(event.target === this) this.style.display='none'">
        <div style="background-color: #fff; padding: 24px; border-radius: 16px; width: 350px; text-align: center; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25);">
            <h3 style="color: #dc2626; margin-top: 0; font-size: 18px; font-weight: 700;">Confirm Delete</h3>
            <p style="margin: 20px 0; font-size: 14px; color: #4b5563;">Are you sure you want to delete all SOS archives?</p>
            <form method="POST" action="{{ route('notifications.delete-all-sos') }}">
                @csrf
                <div style="display:flex; gap:10px; margin-top:20px;">
                    <button type="submit" style="flex:1; background: #f97316; color: #fff; border: none; padding: 10px; border-radius: 8px; font-weight: 700; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.background='#ea580c'" onmouseout="this.style.background='#f97316'">Yes, Delete</button>
                    <button type="button" style="flex:1; background-color: #6b7280; color: #fff; border: none; padding: 10px; border-radius: 8px; font-weight: 700; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.background='#4b5563'" onmouseout="this.style.background='#6b7280'" onclick="document.getElementById('deleteArchiveModal').style.display='none'">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete All Blackout Archive Modal -->
    <div id="deleteBlackoutArchiveModal" style="display: none; position: fixed; z-index: 3000; left: 0; top: 0; width: 100vw; height: 100vh; background-color: rgba(0,0,0,0.5); backdrop-filter: blur(4px); align-items: center; justify-content: center;" onclick="if(event.target === this) this.style.display='none'">
        <div style="background-color: #fff; padding: 24px; border-radius: 16px; width: 350px; text-align: center; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25);">
            <h3 style="color: #dc2626; margin-top: 0; font-size: 18px; font-weight: 700;">Confirm Delete</h3>
            <p style="margin: 20px 0; font-size: 14px; color: #4b5563;">Are you sure you want to delete all Blackout archives?</p>
            <form method="POST" action="{{ route('notifications.delete-all-blackout') }}">
                @csrf
                <div style="display:flex; gap:10px; margin-top:20px;">
                    <button type="submit" style="flex:1; background: #f97316; color: #fff; border: none; padding: 10px; border-radius: 8px; font-weight: 700; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.background='#ea580c'" onmouseout="this.style.background='#f97316'">Yes, Delete</button>
                    <button type="button" style="flex:1; background-color: #6b7280; color: #fff; border: none; padding: 10px; border-radius: 8px; font-weight: 700; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.background='#4b5563'" onmouseout="this.style.background='#6b7280'" onclick="document.getElementById('deleteBlackoutArchiveModal').style.display='none'">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Conversation Modal -->
    <div id="deleteConversationModal" style="display: none; position: fixed; z-index: 3000; left: 0; top: 0; width: 100vw; height: 100vh; background-color: rgba(0,0,0,0.5); backdrop-filter: blur(4px); align-items: center; justify-content: center;" onclick="if(event.target === this) this.style.display='none'">
        <div style="background-color: #fff; padding: 24px; border-radius: 16px; width: 350px; text-align: center; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25);">
            <h3 style="color: #dc2626; margin-top: 0; font-size: 18px; font-weight: 700;">Confirm Delete</h3>
            <p style="margin: 20px 0; font-size: 14px; color: #4b5563;">Are you sure you want to delete this conversation?</p>
            <div style="display:flex; gap:10px; margin-top:20px;">
                <button type="button" id="confirmDeleteConversationBtn" style="flex:1; background: #f97316; color: #fff; border: none; padding: 10px; border-radius: 8px; font-weight: 700; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.background='#ea580c'" onmouseout="this.style.background='#f97316'">Yes, Delete</button>
                <button type="button" style="flex:1; background-color: #6b7280; color: #fff; border: none; padding: 10px; border-radius: 8px; font-weight: 700; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.background='#4b5563'" onmouseout="this.style.background='#6b7280'" onclick="document.getElementById('deleteConversationModal').style.display='none'">Cancel</button>
            </div>
        </div>
    </div>
    <script>
        function openSOSHistoryModal() {
            document.getElementById('sos-history-modal').style.display = 'flex';
        }
        function closeSOSHistoryModal(event) {
            if (event.target.id === 'sos-history-modal') {
                document.getElementById('sos-history-modal').style.display = 'none';
            }
        }
        function openBlackoutHistoryModal() {
            document.getElementById('blackout-history-modal').style.display = 'flex';
        }
        function closeBlackoutHistoryModal(event) {
            if (event.target.id === 'blackout-history-modal') {
                document.getElementById('blackout-history-modal').style.display = 'none';
            }
        }
        function toggleStudentMenu(event, studentId) {
            event.stopPropagation();
            document.querySelectorAll('.student-dropdown').forEach(el => {
                if (el.id !== 'student-menu-' + studentId) el.style.display = 'none';
            });
            const menu = document.getElementById('student-menu-' + studentId);
            if (menu) {
                menu.style.display = menu.style.display === 'none' ? 'block' : 'none';
            }
        }

        document.addEventListener('click', function() {
            document.querySelectorAll('.student-dropdown').forEach(el => {
                el.style.display = 'none';
            });
        });

        async function deleteStudentConversation(event, studentId) {
            event.stopPropagation();
            
            document.querySelectorAll('.student-dropdown').forEach(el => {
                el.style.display = 'none';
            });

            // Open the custom modal
            const modal = document.getElementById('deleteConversationModal');
            modal.style.display = 'flex';

            // Set up the confirm button click handler (run once)
            const confirmBtn = document.getElementById('confirmDeleteConversationBtn');
            confirmBtn.onclick = async function() {
                modal.style.display = 'none';
                try {
                    const csrfToken = document.querySelector('input[name="_token"]')?.value;
                    const res = await fetch(`/messages/${studentId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Content-Type': 'application/json'
                        }
                    });
                    const data = await res.json();
                    if (data.success) {
                        if (typeof activeStudentId !== 'undefined' && activeStudentId == studentId) {
                            document.getElementById('chat-messages').innerHTML = '<div style="text-align:center;color:var(--muted);font-size:13px;margin-top:40px;">No messages yet. Say hello! 👋</div>';
                        }
                        pollNotifications(); // Refresh list to clear unread counts if any
                    } else {
                        alert('Error: ' + (data.message || 'Could not delete conversation.'));
                    }
                } catch (e) {
                    console.error(e);
                    alert('An error occurred while deleting the conversation.');
                }
            };
        }
    </script>
@endsection
