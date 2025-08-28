@extends('layouts.app')

@section('content')
<style>
    :root {
    --level-0-bg: #0078d4;
    --level-0-border: #106ebe;
    --level-1-bg: #107c10;
    --level-1-border: #0e6e0e;
    --level-2-bg: #881798;
    --level-2-border: #7a1589;
    --level-3-bg: #ff8c00;
    --level-3-border: #e67e00;
    --level-4-bg: #e81123;
    --level-4-border: #d10e20;
    --level-5-bg: #5c2d91;
    --level-5-border: #522982;
}

    .gantt-container {
        display: flex;
        flex-direction: column;
        min-height: calc(100vh - 120px);
        overflow-y: auto;
        /* Izinkan scroll vertikal */
        overflow-x: hidden;
        background: #ffffff;
        border: 1px solid #d1d5db;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        max-width: 100vw;
        box-sizing: border-box;
    }

    .gantt-header {
        background: #f8f9fa;
        border-bottom: 1px solid #d1d5db;
        padding: 8px 12px;
        font-size: 14px;
        font-weight: 600;
        color: #374151;
    }

    .gantt-main-content {
        display: flex;
        flex: 1;
        min-height: 0;
        overflow: hidden;
    }

    /* Task List - 50% width */
    .task-list-container {
        width: 50%;
        min-width: 50%;
        max-width: 50%;
        display: flex;
        flex-direction: column;
        border-right: 1px solid #d1d5db;
        background: #ffffff;
        overflow: hidden;
    }

    /* Gantt View - 50% width */
    .gantt-view-container {
        width: 50%;
        min-width: 50%;
        max-width: 50%;
        display: flex;
        flex-direction: column;
        background: white;
        overflow: hidden;
    }

    /* Combined Header Container */
    .combined-header-container {
        display: flex;
        width: 100%;
        background: #f1f3f4;
        border-bottom: 1px solid #d1d5db;
        position: sticky;
        top: 0;
        z-index: 20;
    }

    .task-list-header-section {
        width: 50%;
        min-width: 50%;
        max-width: 50%;
        border-right: 1px solid #d1d5db;
        overflow: hidden;
    }

    .timeline-header-section {
        overflow-x: hidden !important;
        /* Hilangkan scrollbar horizontal */
        overflow-y: hidden;
        width: 50%;
        min-width: 50%;
        max-width: 50%;
    }

    .timeline-header-section::-webkit-scrollbar {
        display: none;
    }

    .task-header-row {
        display: grid;
        grid-template-columns: 40px 1fr 80px 80px 80px;
        height: 32px;
        align-items: center;
        padding: 0 8px;
        border-bottom: 1px solid #d1d5db;
    }

    .task-header-cell {
        padding: 4px 8px;
        text-align: center;
        border-right: 1px solid #e5e7eb;
        font-size: 11px;
        font-weight: 600;
        color: #6b7280;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .task-header-cell:first-child {
        text-align: left;
    }

    /* Task List Body */
    .task-list-body {
        flex: 1;
        overflow: visible !important;
        min-height: auto !important;
        background: white;
        max-height: none !important;
    }

    .timeline-header-container {
        background: #f1f3f4;
        border-bottom: 1px solid #d1d5db;
        overflow-x: auto;
        overflow-y: hidden;
        scrollbar-width: none;
        -ms-overflow-style: none;
        max-width: 100%;
        width: fit-content;
        /* Lebar sesuai konten */
        min-width: 100%;
    }

    .timeline-header-container::-webkit-scrollbar {
        display: none;
    }

    /* Pastikan gantt-content-container selalu memiliki scrollbar horizontal */
    .gantt-content-container {
        overflow-x: auto !important;
        /* Selalu tampilkan scrollbar horizontal */
        overflow-y: auto !important;
        /* Scroll vertikal tetap aktif */
        max-height: none !important;
        max-width: 100%;
        scrollbar-width: thin;
        /* Untuk Firefox */
        /* Tambahkan padding-bottom agar scrollbar selalu terlihat di bawah */
        padding-bottom: 10px;
        /* Sesuaikan sesuai kebutuhan */
    }

    body.no-scroll {
        overflow: hidden !important;
        position: fixed;
        width: 100%;
        height: 100%;
    }

    /* Timeline Grid */
    .month-header {
        display: flex;
        border-bottom: 1px solid #d1d5db;
        background: #f1f3f4;
        height: 20px;
        min-width: fit-content;
    }

    .month-section {
        text-align: center;
        font-weight: 600;
        font-size: 11px;
        color: #374151;
        border-right: 1px solid #d1d5db;
        padding: 2px 4px;
        background: #f1f3f4;
        display: flex;
        align-items: center;
        justify-content: center;
        min-width: 60px;
    }

    .day-header {
        display: flex;
        background: #f8f9fa;
        height: 32px;
        border-bottom: 1px solid #d1d5db;
        min-width: fit-content;
    }

    .timeline-day {
        width: 24px;
        min-width: 24px;
        max-width: 24px;
        flex-shrink: 0;
        text-align: center;
        border-right: 1px solid #e5e7eb;
        font-size: 10px;
        font-weight: 500;
        padding: 2px;
        background: #f8f9fa;
        color: #374151;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .timeline-day.weekend {
        background-color: #f3f4f6;
        color: #9ca3af;
    }

    .timeline-day.today {
        background-color: #dbeafe;
        color: #1e40af;
        font-weight: 700;
    }

    /* Task Rows */
    .task-row {
        display: grid;
        grid-template-columns: 40px 1fr 80px 80px 80px;
        height: 32px;
        align-items: center;
        padding: 0 8px;
        border-bottom: 1px solid #f1f5f9;
        background: white;
        font-size: 11px;
        transition: background-color 0.1s ease;
        max-width: 100%;
    }

    .task-row:nth-child(even) {
        background: #fafbfc;
    }

    .task-row:hover {
        background-color: #e3f2fd;
    }

    .task-row.hidden-task {
        display: none;
    }

    .task-row.task-child {
        background: #f8f9fa;
        border-left: 3px solid #e5e7eb;
    }

    .task-row.task-child:hover {
        background-color: #f0f9ff;
    }

    .task-cell {
        padding: 2px 4px;
        text-align: center;
        border-right: 1px solid #f1f5f9;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .task-cell:first-child {
        text-align: left;
        display: flex;
        align-items: center;
    }

    .task-name-cell {
        text-align: left;
        padding-left: 4px;
        font-weight: 500;
        color: #374151;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        display: flex;
        align-items: center;
    }

    /* Gantt Rows */
    .gantt-row {
        height: 32px;
        position: relative;
        border-bottom: 1px solid #f1f5f9;
        background: white;
        display: flex;
        min-width: fit-content;
        overflow: hidden;
    }

    .gantt-row:nth-child(even) {
        background: #fafbfc;
    }

    .gantt-row:hover {
        background: #e3f2fd;
    }

    .gantt-row.hidden-gantt-row {
        display: none;
    }

    .gantt-grid-cell {
        width: 24px;
        min-width: 24px;
        max-width: 24px;
        height: 32px;
        border-right: 1px solid #f1f5f9;
        flex-shrink: 0;
    }

    .gantt-grid-cell.weekend {
        background-color: #f9fafb;
    }

    .gantt-grid-cell.today {
        background-color: #dbeafe;
        /* Biru muda, sama dengan timeline-day.today */
        border-left: 2px solid #1e40af;
        border-right: 2px solid #1e40af;
    }

    /* Task Bars */
    .gantt-bar {
        position: absolute;
        top: 6px;
        height: 20px;
        border-radius: 2px;
        display: flex;
        align-items: center;
        justify-content: flex-start;
        padding: 0 6px;
        font-size: 10px;
        font-weight: 500;
        color: white;
        cursor: pointer;
        transition: all 0.2s ease;
        overflow: hidden;
        white-space: nowrap;
        text-overflow: ellipsis;
        min-width: 20px;
        z-index: 5;
        border: 1px solid rgba(0, 0, 0, 0.1);
        box-sizing: border-box;
    }

    .gantt-bar:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        z-index: 10;
    }

    /* Task Colors by Level */
.level-0 {
    background: var(--level-0-bg);
    border-color: var(--level-0-border);
}

.level-1 {
    background: var(--level-1-bg);
    border-color: var(--level-1-border);
}

.level-2 {
    background: var(--level-2-bg);
    border-color: var(--level-2-border);
}

.level-3 {
    background: var(--level-3-bg);
    border-color: var(--level-3-border);
}

.level-4 {
    background: var(--level-4-bg);
    border-color: var(--level-4-border);
}

.level-5 {
    background: var(--level-5-bg);
    border-color: var(--level-5-border);
}

/* Task Indicators */
.task-indicator {
    width: 12px;
    height: 12px;
    border-radius: 2px;
    margin-right: 6px;
    flex-shrink: 0;
    border: 1px solid rgba(0, 0, 0, 0.1);
}

.indicator-level-0 {
    background: var(--level-0-bg);
}

.indicator-level-1 {
    background: var(--level-1-bg);
}

.indicator-level-2 {
    background: var(--level-2-bg);
}

.indicator-level-3 {
    background: var(--level-3-bg);
}

.indicator-level-4 {
    background: var(--level-4-bg);
}

.indicator-level-5 {
    background: var(--level-5-bg);
}
    /* Duration Badges */
    .duration-badge {
        padding: 2px 6px;
        border-radius: 3px;
        font-size: 10px;
        font-weight: 500;
        background-color: #f3f4f6;
        color: #374151;
        border: 1px solid #e5e7eb;
    }

    /* Toggle Icons */
    .toggle-collapse {
        cursor: pointer;
        padding: 2px;
        border-radius: 2px;
        transition: all 0.1s ease;
        color: #6b7280;
        width: 16px;
        height: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .toggle-collapse:hover {
        background-color: #e5e7eb;
        color: #374151;
    }

    .toggle-collapse.rotate-90 {
        transform: rotate(90deg);
    }


    .gantt-rows-container {
        width: fit-content;
        /* Lebar sesuai konten (jumlah hari di timeline) */
        min-width: 100%;
        /* Mengisi parent */
        overflow-x: visible;
        /* Cegah pemotongan */
        display: block;
    }

    /* Month Navigation Styles */
    .month-navigation {
        display: flex;
        align-items: center;
        gap: 8px;
        background: #f8f9fa;
        padding: 8px 12px;
        border-bottom: 1px solid #d1d5db;
        flex-wrap: wrap;
    }

    .nav-button {
        padding: 6px 12px;
        border: 1px solid #d1d5db;
        border-radius: 4px;
        background: white;
        color: #374151;
        font-size: 12px;
        cursor: pointer;
        transition: all 0.1s ease;
        display: flex;
        align-items: center;
        gap: 4px;
        font-weight: 500;
    }

    .nav-button:hover:not(:disabled) {
        background: #f3f4f6;
        border-color: #9ca3af;
    }

    .nav-button:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    .current-period {
        font-weight: 600;
        color: #374151;
        font-size: 13px;
        min-width: 180px;
        text-align: center;
        padding: 6px 12px;
        background: #e3f2fd;
        border-radius: 4px;
        border: 1px solid #bbdefb;
    }

    .period-selector {
        padding: 6px 8px;
        border: 1px solid #d1d5db;
        border-radius: 4px;
        background: white;
        color: #374151;
        font-size: 12px;
        cursor: pointer;
        font-weight: 500;
    }

    /* Toolbar Styles */
    .toolbar {
        background: #f8f9fa;
        border-bottom: 1px solid #d1d5db;
        padding: 8px 12px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
    }

    .toolbar-left {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .toolbar-right {
        display: flex;
        align-items: center;
        gap: 8px;
        background: white;
        padding: 8px 12px;
        border-radius: 6px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        border: 1px solid #e5e7eb;
    }

    .control-button {
        padding: 6px 10px;
        border: 1px solid #d1d5db;
        border-radius: 4px;
        background: white;
        color: #374151;
        font-size: 12px;
        cursor: pointer;
        transition: all 0.1s ease;
        display: flex;
        align-items: center;
        gap: 4px;
        font-weight: 500;
    }

    .control-button:hover {
        background: #f3f4f6;
        border-color: #9ca3af;
    }

    .control-button.primary {
        background: #0078d4;
        color: white;
        border-color: #106ebe;
    }

    .control-button.primary:hover {
        background: #106ebe;
    }

    .zoom-controls {
        display: flex;
        align-items: center;
        gap: 4px;
        padding: 4px;
        border: 1px solid #e5e7eb;
        border-radius: 4px;
        background: #f9fafb;
    }

    .zoom-button {
        padding: 4px 6px;
        border: 1px solid #d1d5db;
        background: white;
        color: #374151;
        cursor: pointer;
        border-radius: 3px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.1s ease;
    }

    .zoom-button:hover:not(:disabled) {
        background: #f3f4f6;
        border-color: #9ca3af;
    }

    .zoom-button:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    .zoom-level {
        font-size: 11px;
        font-weight: 600;
        color: #374151;
        min-width: 40px;
        text-align: center;
        padding: 0 4px;
    }



    /* Loading States */
    .gantt-bar.loading {
        background: #e5e7eb !important;
        animation: pulse 1.5s ease-in-out infinite;
    }

    @keyframes pulse {

        0%,
        100% {
            opacity: 1;
        }

        50% {
            opacity: 0.7;
        }
    }

    /* Task Children Container */
    .task-children {
        display: block;
    }

    .task-children.collapsed {
        display: none;
    }

    /* Responsive Design */
    @media (max-width: 1200px) {
        .toolbar {
            flex-direction: column;
            align-items: stretch;
            gap: 8px;
        }

        .toolbar-left,
        .toolbar-right {
            justify-content: center;
        }

        .month-navigation {
            justify-content: center;
            flex-wrap: wrap;
        }

        .current-period {
            min-width: 150px;
        }
    }

    @media (max-width: 1024px) {

        .task-list-container,
        .task-list-header-section {
            width: 45%;
            min-width: 45%;
            max-width: 45%;
        }

        .gantt-view-container,
        .timeline-header-section {
            width: 55%;
            min-width: 55%;
            max-width: 55%;
        }

        .timeline-day,
        .gantt-grid-cell {
            width: 22px;
            min-width: 22px;
            max-width: 22px;
        }
    }

    @media (max-width: 768px) {

        .task-list-container,
        .task-list-header-section {
            width: 40%;
            min-width: 40%;
            max-width: 40%;
        }

        .gantt-view-container,
        .timeline-header-section {
            width: 60%;
            min-width: 60%;
            max-width: 60%;
        }

        .timeline-day,
        .gantt-grid-cell {
            width: 20px;
            min-width: 20px;
            max-width: 20px;
        }

        .month-navigation {
            padding: 6px;
            gap: 4px;
        }

        .nav-button,
        .control-button {
            padding: 4px 8px;
            font-size: 11px;
        }

        .current-period {
            min-width: 120px;
            font-size: 12px;
        }

        /* Update responsive untuk date fields */
        .date-fields-row {
            flex-direction: column;
            gap: 12px;
        }

        .date-field {
            flex: 1 1 100%;
        }
    }

    /* Accessibility Improvements */
    .gantt-bar:focus {
        outline: 2px solid #2563eb;
        outline-offset: 1px;
    }

    .nav-button:focus,
    .control-button:focus,
    .zoom-button:focus {
        outline: 2px solid #2563eb;
        outline-offset: 2px;
    }

    /* High contrast mode support */
    @media (prefers-contrast: high) {
        .gantt-bar {
            border-width: 2px;
            border-style: solid;
        }

        .gantt-grid-cell,
        .timeline-day {
            border-width: 1px;
            border-color: #000;
        }
    }

    /* Reduced motion support */
    @media (prefers-reduced-motion: reduce) {
        .gantt-bar {
            transition: none;
        }

        .gantt-bar:hover {
            transform: none;
        }

        .toggle-collapse {
            transition: none;
        }

        .gantt-bar.loading {
            animation: none;
        }
    }

    /* Print styles */
    @media print {
        .gantt-container {
            height: auto !important;
            min-height: auto !important;
            max-height: none !important;
            overflow: visible !important;
        }

        .task-list-body,
        .gantt-content-container {
            height: auto !important;
            max-height: none !important;
            overflow: visible !important;
        }

        .toolbar,
        .month-navigation {
            display: none !important;
        }

        .gantt-bar {
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
    }

    /* Enhanced Modal Styles with Smooth Animations - UPDATED */
    .modal {
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0);
        backdrop-filter: blur(0px);
        opacity: 0;
        transition: opacity 0.3s cubic-bezier(0.25, 0.46, 0.45, 0.94),
            background-color 0.3s cubic-bezier(0.25, 0.46, 0.45, 0.94),
            backdrop-filter 0.3s cubic-bezier(0.25, 0.46, 0.45, 0.94);
        display: flex;
        align-items: center;
        justify-content: center;
        pointer-events: none;
        /* Nonaktifkan interaksi saat modal tidak terlihat */
    }

    .modal.show {
        display: flex;
        pointer-events: auto;
        /* Aktifkan interaksi saat modal ditampilkan */
    }

    .modal.opening {
        background-color: rgba(0, 0, 0, 0.5);
        backdrop-filter: blur(2px);
        opacity: 1;
        pointer-events: auto;
    }

    .modal.closing {
        background-color: rgba(0, 0, 0, 0);
        backdrop-filter: blur(0px);
        opacity: 0;
        pointer-events: none;
        /* Nonaktifkan interaksi selama animasi penutupan */
    }

    .modal-content {
        background-color: #ffffff;
        margin: 0;
        padding: 0;
        border: none;
        border-radius: 12px;
        width: 90%;
        max-width: 500px;
        max-height: 90vh;
        overflow: hidden;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        transform: translateY(50px) scale(0.9);
        opacity: 0;
        transition: all 0.3s cubic-bezier(0.25, 0.46, 0.45, 0.94);
    }

    .modal.opening .modal-content {
        transform: translateY(0) scale(1);
        opacity: 1;
    }

    .modal.closing .modal-content {
        transform: translateY(-30px) scale(0.95);
        opacity: 0;
    }

    .modal-header {
        background: linear-gradient(135deg, #0078d4 0%, #106ebe 100%);
        color: white;
        padding: 20px 24px;
        border-bottom: none;
        position: relative;
    }

    .modal-header h4 {
        margin: 0;
        font-size: 18px;
        font-weight: 600;
        line-height: 1.4;
        padding-right: 40px;
    }

    .modal-close-x {
        position: absolute;
        top: 50%;
        right: 20px;
        transform: translateY(-50%);
        background: none;
        border: none;
        color: white;
        font-size: 24px;
        cursor: pointer;
        padding: 8px;
        border-radius: 6px;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
    }

    .modal-close-x:hover {
        background-color: rgba(255, 255, 255, 0.2);
        transform: translateY(-50%) scale(1.1);
    }

    .modal-body {
        padding: 24px;
        background: #ffffff;
        max-height: 60vh;
        overflow-y: auto;
    }

    .modal-field {
        margin-bottom: 20px;
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .modal-field:last-child {
        margin-bottom: 0;
    }

    .modal-field-label {
        font-weight: 600;
        color: #374151;
        font-size: 13px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .modal-field-value {
        color: #6b7280;
        font-size: 14px;
        line-height: 1.6;
        padding: 12px 16px;
        background: #f9fafb;
        border-radius: 8px;
        border: 1px solid #e5e7eb;
        min-height: 20px;
        word-wrap: break-word;
    }

    .modal-field-value.empty {
        color: #9ca3af;
        font-style: italic;
    }

    /* CSS untuk date fields bersebelahan */
    .date-fields-row {
        display: flex;
        gap: 16px;
        margin-bottom: 20px;
    }

    .date-field {
        flex: 1;
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .date-field .modal-field-label {
        font-weight: 600;
        color: #374151;
        font-size: 13px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .date-field .modal-field-value {
        color: #6b7280;
        font-size: 14px;
        line-height: 1.6;
        padding: 12px 16px;
        background: #f9fafb;
        border-radius: 8px;
        border: 1px solid #e5e7eb;
        min-height: 20px;
        word-wrap: break-word;
    }

    .date-field .modal-field-value.empty {
        color: #9ca3af;
        font-style: italic;
    }

    .modal-footer {
        display: flex;
        justify-content: flex-end;
        gap: 12px;
        padding: 20px 24px;
        background: #f9fafb;
        border-top: 1px solid #e5e7eb;
    }

    .modal-btn {
        padding: 10px 18px;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s cubic-bezier(0.25, 0.46, 0.45, 0.94);
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        border: 1px solid transparent;
        position: relative;
        overflow: hidden;
    }

    .modal-btn::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
        transition: left 0.5s;
    }

    .modal-btn:hover::before {
        left: 100%;
    }

    .modal-btn-secondary {
        background: #ffffff;
        color: #6b7280;
        border-color: #d1d5db;
    }

    .modal-btn-secondary:hover {
        background: #f9fafb;
        color: #374151;
        border-color: #9ca3af;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .modal-btn-primary {
        background: #0078d4;
        color: white;
        border-color: #0078d4;
    }

    .modal-btn-primary:hover {
        background: #106ebe;
        border-color: #106ebe;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(16, 110, 190, 0.3);
    }

    .modal-btn-danger {
        background: #dc2626;
        color: white;
        border-color: #dc2626;
    }

    .modal-btn-danger:hover {
        background: #b91c1c;
        border-color: #b91c1c;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(220, 38, 38, 0.3);
    }

    /* Responsive Design for Modal */
    @media (max-width: 768px) {
        .modal-content {
            width: 95%;
            margin: 20px auto;
            max-height: calc(100vh - 40px);
            border-radius: 16px;
        }

        .modal-header,
        .modal-body,
        .modal-footer {
            padding: 16px 20px;
        }

        .modal-footer {
            flex-direction: column-reverse;
        }

        .modal-btn {
            width: 100%;
            justify-content: center;
            padding: 12px 18px;
        }
    }

    @media (max-width: 480px) {
        .modal-content {
            width: 100%;
            height: 100%;
            max-height: 100vh;
            border-radius: 0;
            margin: 0;
        }

        .modal {
            align-items: stretch;
        }

        .modal-body {
            max-height: calc(100vh - 180px);
        }
    }

    /* Accessibility improvements */
    .modal-btn:focus {
        outline: 2px solid #2563eb;
        outline-offset: 2px;
    }

    .modal-close-x:focus {
        outline: 2px solid rgba(255, 255, 255, 0.8);
        outline-offset: 2px;
    }

    /* Smooth scrollbar for modal body */
    .modal-body::-webkit-scrollbar {
        width: 6px;
    }

    .modal-body::-webkit-scrollbar-track {
        background: #f1f5f9;
        border-radius: 3px;
    }

    .modal-body::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 3px;
    }

    .modal-body::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
    }

    /* Duration Badge Styles */
    .duration-badge {
        transition: background-color 0.2s ease;
    }

    .duration-badge[data-level="0"] {
        background-color: #0078d4;
        /* Blue */
        color: white;
        border: 1px solid #106ebe;
    }

    .duration-badge[data-level="1"] {
        background-color: #107c10;
        /* Green */
        color: white;
        border: 1px solid #0e6e0e;
    }

    .duration-badge[data-level="2"] {
        background-color: #881798;
        /* Purple */
        color: white;
        border: 1px solid #7a1589;
    }

    .duration-badge[data-level="3"] {
        background-color: #ff8c00;
        /* Orange */
        color: white;
        border: 1px solid #e67e00;
    }

    .duration-badge[data-level="4"] {
        background-color: #e81123;
        /* Red */
        color: white;
        border: 1px solid #d10e20;
    }

    .duration-badge[data-level="5"] {
        background-color: #5c2d91;
        /* Dark Purple */
        color: white;
        border: 1px solid #522982;
    }

    /* Default for other levels */
    .duration-badge:not([data-level]) {
        background-color: #6b7280;
        /* Gray */
        color: white;
        border: 1px solid #4b5563;
    }

    .timeline-day {
        width: 24px;
        min-width: 24px;
        max-width: 24px;
        flex-shrink: 0;
        text-align: center;
        border-right: 1px solid #e5e7eb;
        font-size: 10px;
        font-weight: 500;
        padding: 2px;
        background: #f8f9fa;
        color: #374151;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-direction: column;
        position: relative;
    }

    /* Style untuk menampilkan hari dalam bahasa Indonesia */
    .timeline-day::after {
        content: attr(data-dayname);
        font-size: 7px;
        /* Diperkecil sedikit */
        font-weight: 400;
        color: #6b7280;
        position: absolute;
        bottom: 1px;
        width: 100%;
        text-align: center;
        line-height: 1;
    }

    .timeline-day.weekend {
        background-color: #fef2f2;
        /* Background lebih terang untuk hari libur */
        color: #dc2626;
        /* Warna merah untuk angka */
    }

    .timeline-day.weekend::after {
        color: #dc2626;
        /* Warna merah untuk nama hari */
        font-weight: 600;
    }

    .timeline-day.today {
        background-color: #dbeafe;
        color: #1e40af;
        font-weight: 700;
    }

    .timeline-day.today::after {
        color: #1e40af;
        font-weight: 600;
    }

    /* Style khusus untuk hari Minggu */
    .timeline-day.sunday {
        background-color: #fef2f2;
        color: #dc2626;
    }

    .timeline-day.sunday::after {
        color: #dc2626;
        font-weight: 700;
    }
</style>

<div class="gantt-container">
    <!-- Toolbar -->
    <div class="toolbar">
        <div class="toolbar-left">
            <h5 class="text-sm font-semibold text-gray-800 flex items-center">
                <svg class="w-4 h-4 mr-2 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"></path>
                </svg>
                Gantt Chart - Project Schedule
            </h5>
        </div>

        <div class="toolbar-right">
            <div class="zoom-controls">
                <button class="zoom-button" id="zoomOutBtn" onclick="zoomOut()" title="Zoom Out (Ctrl/Cmd + -)">
                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M3 10a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"></path>
                    </svg>
                </button>

                <span class="zoom-level" id="zoomLevel">100%</span>

                <button class="zoom-button" id="zoomInBtn" onclick="zoomIn()" title="Zoom In (Ctrl/Cmd + +)">
                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"></path>
                    </svg>
                </button>
            </div>

            <button class="control-button" onclick="expandAll()" title="Expand All Tasks">
                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"></path>
                </svg>
                Expand
            </button>

            <button class="control-button" onclick="collapseAll()" title="Collapse All Tasks">
                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M5 10a1 1 0 011-1h8a1 1 0 110 2H6a1 1 0 01-1-1z" clip-rule="evenodd"></path>
                </svg>
                Collapse
            </button>

            @if(isset($createRoute))
            <a href="{{ $createRoute }}" class="control-button primary" title="Add New Task">
                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"></path>
                </svg>
                Add Task
            </a>
            @endif
        </div>
    </div>

    <!-- Enhanced Modal Structure -->
    <div id="taskModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h4 id="taskName">Task Details</h4>
                <button class="modal-close-x" onclick="closeTaskModal()" aria-label="Close modal">&times;</button>
            </div>

            <div class="modal-body">
                <div class="modal-field">
                    <div class="modal-field-label">Duration</div>
                    <div class="modal-field-value" id="taskDuration">-</div>
                </div>

                <!-- Start Date dan Finish Date bersebelahan -->
                <div class="date-fields-row">
                    <div class="date-field">
                        <div class="modal-field-label">Start Date</div>
                        <div class="modal-field-value" id="taskStartDate">-</div>
                    </div>
                    <div class="date-field">
                        <div class="modal-field-label">Finish Date</div>
                        <div class="modal-field-value" id="taskFinishDate">-</div>
                    </div>
                </div>

                <div class="modal-field">
                    <div class="modal-field-label">Description</div>
                    <div class="modal-field-value" id="taskDescription">No description available</div>
                </div>
            </div>

            <div class="modal-footer">
                <button class="modal-btn modal-btn-secondary" onclick="closeTaskModal()">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                    </svg>
                    Close
                </button>

                <a href="#" id="editTaskBtn" class="modal-btn modal-btn-primary">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"></path>
                    </svg>
                    Edit
                </a>

                <a href="#" id="deleteTaskBtn"
                    class="modal-btn modal-btn-danger flex items-center gap-2">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M6.5 3a1 1 0 00-1 1v1H4a1 1 0 000 2h1v9a2 2 0 002 2h6a2 2 0 002-2V7h1a1 1 0 100-2h-1.5V4a1 1 0 00-1-1h-5zM7.5 5h5V4h-5v1zM7 7v8h6V7H7z" clip-rule="evenodd"></path>
                        <path d="M9 9v4M11 9v4"></path>
                    </svg>
                    <span>Hapus</span>
                </a>

                <!-- Form hapus task (hidden) -->
                <form id="deleteTaskForm" method="POST" style="display:none;">
                    @csrf
                    @method('DELETE')
                </form>
            </div>
        </div>
    </div>

    <!-- Month Navigation -->
    <div class="month-navigation">
        <button class="nav-button" id="prevBtn" onclick="navigateMonth(-1)" title="Previous Month (Alt + Left Arrow)">
            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd"></path>
            </svg>
            Previous
        </button>

        <div class="current-period" id="currentPeriod">
            January 2025
        </div>

        <button class="nav-button" id="nextBtn" onclick="navigateMonth(1)" title="Next Month (Alt + Right Arrow)">
            Next
            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
            </svg>
        </button>

        <select class="period-selector" id="periodSelector" onchange="changePeriod(this.value)" title="Select Timeline Period">
            <option value="1">1 Month</option>
            <option value="3" selected>3 Months</option>
            <option value="6">6 Months</option>
            <option value="12">1 Year</option>
        </select>

        <button class="nav-button" onclick="goToToday()" title="Go to Today (Alt + Home)">
            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
            </svg>
            Hari ini
        </button>
    </div>

    <!-- Combined Header -->
    <div class="combined-header-container">
        <!-- Task List Header Section -->
        <div class="task-list-header-section">
            <div class="task-header-row">
                <div class="task-header-cell" style="width: 40px;"></div>
                <div class="task-header-cell" style="text-align: left;">Task Name</div>
                <div class="task-header-cell">Duration</div>
                <div class="task-header-cell">Start</div>
                <div class="task-header-cell">Finish</div>
            </div>
        </div>

        <!-- Timeline Header Section -->
        <div class="timeline-header-section" id="timelineHeaderSection">
            <div class="timeline-header-container">
                <div id="monthHeaderContainer"></div>
                <div id="dayHeaderContainer"></div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="gantt-main-content">
        <!-- Task List (50% width) -->
        <div class="task-list-container">
            <div class="task-list-body" id="taskListBody">
                @if(isset($tasks) && $tasks->count() > 0)
                @foreach($tasks as $task)
                @include('partials.task-item', ['task' => $task, 'level' => 0])
                @endforeach
                @else
                <div class="p-8 text-center text-gray-500">
                    <svg class="w-12 h-12 mx-auto mb-4 text-gray-300" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"></path>
                    </svg>
                    <p class="text-sm">No tasks available. Click "Add Task" to get started.</p>
                </div>
                @endif
            </div>
        </div>

        <!-- Gantt View (50% width) -->
        <div class="gantt-view-container">
            <div class="gantt-content-container" id="ganttContent">
                <div class="gantt-rows-container" id="ganttRowsContainer">
                    <!-- Gantt bars will be generated by JavaScript -->
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Global variables for timeline management
let currentDate = new Date();
let timelinePeriod = 3; // months
let currentZoom = 100;
let timelineData = {
    startDate: null,
    endDate: null,
    days: []
};

let tasksData = [];
let collapsedTasks = new Set(); // Track collapsed tasks
let isModalAnimating = false;

@if(isset($structuredTasks) && count($structuredTasks) > 0)
tasksData = @json($structuredTasks);
@endif

document.addEventListener('DOMContentLoaded', function() {
    console.log('Tasks data:', tasksData); // Debug log

    // Load warna dari localStorage saat halaman dimuat
    for (let i = 0; i < 6; i++) {
        const bg = localStorage.getItem(`level-${i}-bg`);
        const border = localStorage.getItem(`level-${i}-border`);
        if (bg) document.documentElement.style.setProperty(`--level-${i}-bg`, bg);
        if (border) document.documentElement.style.setProperty(`--level-${i}-border`, border);
    }

    // Inisialisasi Gantt chart
    initializeTimeline();
    setupScrollSynchronization();
    updateGanttChart();
    updateZoomButtons();

    // Inisialisasi collapse state dari DOM
    document.querySelectorAll('.task-children.collapsed').forEach(container => {
        const parentId = container.getAttribute('data-parent-id');
        if (parentId) collapsedTasks.add(parentId);
    });

    // Inisialisasi trap focus untuk modal
    const modal = document.getElementById('taskModal');
    if (modal) trapFocus(modal);

    updateDurationBadgeColors();
});

// Initialize timeline based on current date and period
function initializeTimeline() {
    const startOfMonth = new Date(currentDate.getFullYear(), currentDate.getMonth(), 1);
    timelineData.startDate = new Date(startOfMonth);

    const endDate = new Date(startOfMonth);
    endDate.setMonth(endDate.getMonth() + timelinePeriod);
    endDate.setDate(endDate.getDate() - 1);
    timelineData.endDate = endDate;

    generateTimelineDays();
    updateCurrentPeriodDisplay();
    renderTimelineHeaders();
}

// Generate array of days for the timeline
function generateTimelineDays() {
    timelineData.days = [];
    const currentDay = new Date(timelineData.startDate);

    while (currentDay <= timelineData.endDate) {
        const dayInfo = {
            date: new Date(currentDay),
            dayOfWeek: currentDay.getDay(),
            isWeekend: currentDay.getDay() === 0 || currentDay.getDay() === 6,
            isHoliday: isHoliday(currentDay),
            isToday: isToday(currentDay),
            dayNumber: currentDay.getDate(),
            monthYear: currentDay.toLocaleDateString('id-ID', { month: 'short', year: 'numeric' })
        };
        timelineData.days.push(dayInfo);
        currentDay.setDate(currentDay.getDate() + 1);
    }
}

// Check if date is today
function isToday(date) {
    const today = new Date();
    return date.getDate() === today.getDate() &&
           date.getMonth() === today.getMonth() &&
           date.getFullYear() === today.getFullYear();
}

// Update current period display
function updateCurrentPeriodDisplay() {
    const periodElement = document.getElementById('currentPeriod');
    if (periodElement) {
        const startMonth = timelineData.startDate.toLocaleDateString('en-US', { month: 'long', year: 'numeric' });
        const endMonth = timelineData.endDate.toLocaleDateString('en-US', { month: 'long', year: 'numeric' });
        periodElement.textContent = timelinePeriod === 1 ? startMonth : `${startMonth} - ${endMonth}`;
    }
}

// Render timeline headers
function renderTimelineHeaders() {
    renderMonthHeaders();
    renderDayHeaders();
    updateGanttWidths();
    setDefaultScrollPosition();
}

// Render month headers
function renderMonthHeaders() {
    const monthHeaderContainer = document.getElementById('monthHeaderContainer');
    if (!monthHeaderContainer) return;

    const monthGroups = {};
    timelineData.days.forEach(day => {
        const monthKey = `${day.date.getFullYear()}-${day.date.getMonth()}`;
        if (!monthGroups[monthKey]) {
            monthGroups[monthKey] = {
                name: day.date.toLocaleDateString('en-US', { month: 'short', year: 'numeric' }),
                days: []
            };
        }
        monthGroups[monthKey].days.push(day);
    });

    let monthHeaderHTML = '<div class="month-header">';
    Object.values(monthGroups).forEach(month => {
        const dayWidth = getDayWidth();
        const monthWidth = month.days.length * dayWidth;
        monthHeaderHTML += `<div class="month-section" style="width: ${monthWidth}px;">${month.name}</div>`;
    });
    monthHeaderHTML += '</div>';

    monthHeaderContainer.innerHTML = monthHeaderHTML;
}

// Render day headers
function renderDayHeaders() {
    const dayHeaderContainer = document.getElementById('dayHeaderContainer');
    if (!dayHeaderContainer) return;

    const dayNames = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'];

    let dayHeaderHTML = '<div class="day-header">';
    timelineData.days.forEach(day => {
        const classes = ['timeline-day'];
        if (day.dayOfWeek === 0) classes.push('sunday');
        if (day.isToday) classes.push('today');

        const dayWidth = getDayWidth();
        dayHeaderHTML += `
            <div class="${classes.join(' ')}" 
                 style="width: ${dayWidth}px; min-width: ${dayWidth}px; max-width: ${dayWidth}px;"
                 data-dayname="${dayNames[day.dayOfWeek]}"
                 title="${getFullDayName(day.dayOfWeek)}">
                ${day.dayNumber}
            </div>
        `;
    });
    dayHeaderHTML += '</div>';

    dayHeaderContainer.innerHTML = dayHeaderHTML;
}

function getFullDayName(dayOfWeek) {
    const fullDayNames = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
    return fullDayNames[dayOfWeek];
}

// Check if date is a holiday
function isHoliday(date) {
    const holidays = [
        '2025-01-01', '2025-03-03', '2025-04-18', '2025-05-01', '2025-05-29',
        '2025-06-01', '2025-06-29', '2025-08-17', '2025-09-16', '2025-12-25'
    ];
    const dateString = date.toISOString().split('T')[0];
    return holidays.includes(dateString);
}

// Get current day width based on zoom
function getDayWidth() {
    const baseWidth = 24;
    return Math.round(baseWidth * (currentZoom / 100));
}

// Check if a task should be visible
function isTaskVisible(task) {
    if (!task.parent_id) return true;
    const parent = tasksData.find(t => t.id === task.parent_id);
    if (!parent) return false;
    return isTaskVisible(parent) && !collapsedTasks.has(parent.id.toString());
}

// Get visible tasks in hierarchical order
function getVisibleTasks() {
    const visibleTasks = [];
    function traverseTasks(tasks, parentId = null) {
        tasks.forEach(task => {
            if (task.parent_id === parentId) {
                if (isTaskVisible(task)) {
                    visibleTasks.push(task);
                    if (!collapsedTasks.has(task.id.toString())) {
                        traverseTasks(tasks, task.id);
                    }
                }
            }
        });
    }
    traverseTasks(tasksData, null);
    return visibleTasks;
}

// Update Gantt chart bars
function updateGanttChart() {
    const ganttRowsContainer = document.getElementById('ganttRowsContainer');
    if (!ganttRowsContainer) return;

    let ganttHTML = '';
    const visibleTasks = getVisibleTasks();
    if (visibleTasks.length > 0) {
        visibleTasks.forEach(task => {
            ganttHTML += generateGanttRow(task);
        });
    }
    ganttRowsContainer.innerHTML = ganttHTML;
    addTodayIndicator();
    updateGanttWidths();
}

// Generate Gantt row for a task
function generateGanttRow(task) {
    const dayWidth = getDayWidth();
    const isHidden = !isTaskVisible(task);
    let rowHTML = `<div class="gantt-row ${isHidden ? 'hidden-gantt-row' : ''}" data-task-id="${task.id}">`;
    timelineData.days.forEach(day => {
        const classes = ['gantt-grid-cell'];
        if (day.isWeekend) classes.push('weekend');
        if (day.isToday) classes.push('today');
        rowHTML += `<div class="${classes.join(' ')}" style="width: ${dayWidth}px; min-width: ${dayWidth}px; max-width: ${dayWidth}px;"></div>`;
    });
    const taskBar = generateTaskBar(task, dayWidth);
    if (taskBar) rowHTML += taskBar;
    rowHTML += '</div>';
    return rowHTML;
}

// Generate task bar
function generateTaskBar(task, dayWidth) {
    if (!task.startDate || !task.endDate) return null;
    const taskStart = new Date(task.startDate);
    const taskEnd = new Date(task.endDate);
    if (taskEnd < timelineData.startDate || taskStart > timelineData.endDate) return null;

    const timelineStart = timelineData.startDate;
    const startDayOffset = Math.max(0, Math.floor((taskStart - timelineStart) / (24 * 60 * 60 * 1000)));
    const endDayOffset = Math.min(timelineData.days.length - 1, Math.floor((taskEnd - timelineStart) / (24 * 60 * 60 * 1000)));
    const barLeft = startDayOffset * dayWidth;
    const barWidth = Math.max(dayWidth, (endDayOffset - startDayOffset + 1) * dayWidth - 2);

    const rootId = getRootId(task);
    const relLevel = getRelativeLevel(task);
    const { bg, border } = getColorForRootAndLevel(rootId, relLevel);

    return `
        <div class="gantt-bar" 
             style="left: ${barLeft}px; width: ${barWidth}px; background: ${bg}; border-color: ${border};"
             data-task-id="${task.id}"
             data-start-day="${startDayOffset}"
             data-duration="${task.duration || 0}">
            <span class="task-bar-text">${task.name}</span>
        </div>
    `;
}

// Add today indicator line
function addTodayIndicator() {
    const today = new Date();
    const todayIndex = timelineData.days.findIndex(day =>
        day.date.getDate() === today.getDate() &&
        day.date.getMonth() === today.getMonth() &&
        day.date.getFullYear() === today.getFullYear()
    );
    if (todayIndex !== -1) {
        const dayWidth = getDayWidth();
        const leftPosition = todayIndex * dayWidth + (dayWidth / 2);
        document.querySelectorAll('.gantt-row').forEach(row => {
            const existingIndicator = row.querySelector('.today-indicator');
            if (existingIndicator) existingIndicator.remove();
            const todayIndicator = document.createElement('div');
            todayIndicator.className = 'today-indicator';
            todayIndicator.style.left = `${leftPosition}px`;
            row.appendChild(todayIndicator);
        });
    }
}

// Navigation functions
function navigateMonth(direction) {
    currentDate.setMonth(currentDate.getMonth() + direction);
    initializeTimeline();
    updateGanttChart();
}

function changePeriod(months) {
    timelinePeriod = parseInt(months);
    initializeTimeline();
    updateGanttChart();
}

function goToToday() {
    currentDate = new Date();
    initializeTimeline();
    updateGanttChart();
}

// Zoom functions
const minZoom = 50;
const maxZoom = 200;
const zoomStep = 25;

function updateZoomLevel() {
    const zoomLevelElement = document.getElementById('zoomLevel');
    if (zoomLevelElement) zoomLevelElement.textContent = `${currentZoom}%`;
    updateZoomButtons();
    renderTimelineHeaders();
    updateGanttChart();
}

function updateZoomButtons() {
    const zoomInBtn = document.getElementById('zoomInBtn');
    const zoomOutBtn = document.getElementById('zoomOutBtn');
    if (zoomInBtn) zoomInBtn.disabled = currentZoom >= maxZoom;
    if (zoomOutBtn) zoomOutBtn.disabled = currentZoom <= minZoom;
}

function zoomIn() {
    if (currentZoom < maxZoom) {
        currentZoom += zoomStep;
        updateZoomLevel();
    }
}

function zoomOut() {
    if (currentZoom > minZoom) {
        currentZoom -= zoomStep;
        updateZoomLevel();
    }
}

// Update Gantt widths
function updateGanttWidths() {
    const dayWidth = getDayWidth();
    const totalWidth = timelineData.days.length * dayWidth;
    const ganttRowsContainer = document.getElementById('ganttRowsContainer');
    if (ganttRowsContainer) {
        ganttRowsContainer.style.width = `${totalWidth}px`;
        ganttRowsContainer.style.minWidth = `${totalWidth}px`;
    }
    const timelineHeaderContainer = document.querySelector('.timeline-header-container');
    if (timelineHeaderContainer) {
        timelineHeaderContainer.style.width = `${totalWidth}px`;
        timelineHeaderContainer.style.minWidth = `${totalWidth}px`;
    }
}

// Setup scroll synchronization
function setupScrollSynchronization() {
    const taskListBody = document.getElementById('taskListBody');
    const ganttContent = document.getElementById('ganttContent');
    const timelineHeaderSection = document.getElementById('timelineHeaderSection');
    if (!taskListBody || !ganttContent || !timelineHeaderSection) return;

    taskListBody.addEventListener('scroll', () => ganttContent.scrollTop = taskListBody.scrollTop);
    ganttContent.addEventListener('scroll', () => {
        taskListBody.scrollTop = ganttContent.scrollTop;
        timelineHeaderSection.scrollLeft = ganttContent.scrollLeft;
    });
}

// Set default scroll position
// Set default scroll position to the start of the timeline
function setDefaultScrollPosition() {
    const ganttContent = document.getElementById('ganttContent');
    if (ganttContent) {
        ganttContent.scrollLeft = 0; // Set scroll ke awal (kiri)
    }
}

// Task collapse/expand functionality
function toggleTaskCollapse(taskId) {
    const toggleIcon = document.querySelector(`[data-task-id="${taskId}"].toggle-collapse`);
    const childrenContainer = document.querySelector(`.task-children[data-parent-id="${taskId}"]`);
    if (toggleIcon && childrenContainer) {
        toggleIcon.classList.toggle('rotate-90');
        childrenContainer.classList.toggle('collapsed');
        if (childrenContainer.classList.contains('collapsed')) collapsedTasks.add(taskId.toString());
        else collapsedTasks.delete(taskId.toString());
        updateGanttChart();
    }
}

// Event listeners for toggle buttons and task interactions
document.addEventListener('click', function(e) {
    if (e.target.closest('.toggle-collapse')) {
        const taskId = e.target.closest('.toggle-collapse').getAttribute('data-task-id');
        toggleTaskCollapse(taskId);
    }
    if (e.target.closest('.gantt-bar')) {
        const taskId = e.target.closest('.gantt-bar').getAttribute('data-task-id');
        handleTaskBarClick(taskId);
    }
    if (e.target.closest('.task-name-cell')) {
        const taskId = e.target.closest('.task-name-cell').getAttribute('data-task-id');
        const task = tasksData.find(t => t.id == taskId);
        if (task) {
            console.log('Task from name cell:', task); // Debug log
            openTaskModal(task);
        }
    }
    if (e.target === document.getElementById('taskModal') && !isModalAnimating) {
        closeTaskModal();
    }
});

// Handle task bar click
function handleTaskBarClick(taskId) {
    const task = tasksData.find(t => t.id == taskId);
    if (task) {
        console.log('Task from gantt bar:', task); // Debug log
        openTaskModal(task);
        document.dispatchEvent(new CustomEvent('taskSelected', { detail: { task } }));
    }
}

// Expand/Collapse all functions
function expandAll() {
    document.querySelectorAll('.task-children').forEach(container => container.classList.remove('collapsed'));
    document.querySelectorAll('.toggle-collapse').forEach(icon => icon.classList.add('rotate-90'));
    collapsedTasks.clear();
    updateGanttChart();
}

function collapseAll() {
    document.querySelectorAll('.task-children').forEach(container => {
        container.classList.add('collapsed');
        const parentId = container.getAttribute('data-parent-id');
        if (parentId) collapsedTasks.add(parentId);
    });
    document.querySelectorAll('.toggle-collapse').forEach(icon => icon.classList.remove('rotate-90'));
    updateGanttChart();
}

// Keyboard shortcuts
document.addEventListener('keydown', function(e) {
    if (e.ctrlKey || e.metaKey) {
        if (e.key === '=' || e.key === '+') { e.preventDefault(); zoomIn(); }
        else if (e.key === '-') { e.preventDefault(); zoomOut(); }
    }
    if (e.altKey) {
        if (e.key === 'ArrowLeft') { e.preventDefault(); navigateMonth(-1); }
        else if (e.key === 'ArrowRight') { e.preventDefault(); navigateMonth(1); }
        else if (e.key === 'Home') { e.preventDefault(); goToToday(); }
    }
    if (e.key === 'Escape') {
        const modal = document.getElementById('taskModal');
        if (modal?.classList.contains('opening') && !isModalAnimating) closeTaskModal();
        document.querySelectorAll('.gantt-bar.selected').forEach(bar => bar.classList.remove('selected'));
    }
});

// Utility functions
function formatDate(dateString) {
    const date = new Date(dateString);
    const day = String(date.getDate()).padStart(2, '0');
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const year = date.getFullYear().toString().slice(-2);
    return `${day}-${month}-${year}`;
}

function calculateDuration(startDate, endDate) {
    const start = new Date(startDate);
    const end = new Date(endDate);
    const timeDiff = end.getTime() - start.getTime();
    return Math.ceil(timeDiff / (1000 * 3600 * 24)) + 1;
}

// Responsive handling
function handleResize() {
    setTimeout(() => {
        renderTimelineHeaders();
        updateGanttChart();
    }, 100);
}
window.addEventListener('resize', handleResize);

// Prevent double-tap zoom on mobile
document.addEventListener('touchend', function(e) {
    if (e.target.closest('.modal-btn') || e.target.closest('.modal-close-x')) e.preventDefault();
});

// Public API for external integration
window.GanttChart = {
    navigateMonth,
    changePeriod,
    goToToday,
    zoomIn,
    zoomOut,
    setZoom: function(level) {
        if (level >= minZoom && level <= maxZoom) {
            currentZoom = level;
            updateZoomLevel();
        }
    },
    expandAll,
    collapseAll,
    updateGanttChart,
    addTask: function(task) {
        tasksData.push(task);
        updateGanttChart();
    },
    removeTask: function(taskId) {
        const index = tasksData.findIndex(task => task.id == taskId);
        if (index > -1) {
            tasksData.splice(index, 1);
            updateGanttChart();
        }
    },
    updateTask: function(taskId, updates) {
        const taskIndex = tasksData.findIndex(task => task.id == taskId);
        if (taskIndex > -1) {
            tasksData[taskIndex] = { ...tasksData[taskIndex], ...updates };
            updateGanttChart();
        }
    },
    refreshData: function(newTasks) {
        tasksData = newTasks;
        updateGanttChart();
    },
    getCurrentPeriod: function() {
        return { startDate: timelineData.startDate, endDate: timelineData.endDate, period: timelinePeriod };
    },
    getVisibleTasks: function() {
        return getVisibleTasks().filter(task => {
            const startDate = task.startDate;
            const endDate = task.endDate;
            return startDate && endDate && !(new Date(endDate) < timelineData.startDate || new Date(startDate) > timelineData.endDate);
        });
    }
};

// Event listeners for Laravel integration
document.addEventListener('taskSelected', function(e) {
    console.log('Task selected:', e.detail.task);
});
document.addEventListener('taskUpdated', function(e) {
    console.log('Task updated:', e.detail.task);
    updateGanttChart();
});
document.addEventListener('taskDeleted', function(e) {
    window.GanttChart.removeTask(e.detail.taskId);
});
document.addEventListener('taskAdded', function(e) {
    window.GanttChart.addTask(e.detail.task);
});

// Enhanced modal functions
function openTaskModal(task) {
    if (isModalAnimating) return;
    const modal = document.getElementById('taskModal');
    isModalAnimating = true;
    populateModalContent(task);
    document.body.classList.add('no-scroll');
    const scrollY = window.scrollY;
    document.body.style.top = `-${scrollY}px`;
    document.body.dataset.scrollY = scrollY;
    modal.style.display = 'flex';
    modal.offsetHeight; // Force reflow
    modal.classList.add('opening');
    setTimeout(() => { isModalAnimating = false; }, 300);
}

function closeTaskModal() {
    if (isModalAnimating) return;
    const modal = document.getElementById('taskModal');
    isModalAnimating = true;
    modal.classList.remove('opening');
    modal.classList.add('closing');
    document.body.classList.remove('no-scroll');
    const scrollY = document.body.dataset.scrollY || 0;
    document.body.style.top = '';
    window.scrollTo(0, parseInt(scrollY));
    setTimeout(() => {
        modal.classList.remove('closing');
        modal.style.display = 'none';
        isModalAnimating = false;
        const colorPickerContainer = document.getElementById('colorPickerContainer');
        if (colorPickerContainer) colorPickerContainer.remove();
    }, 300);
}

function populateModalContent(task) {
    console.log('Populating modal with task:', task);
    console.log('taskDuration element:', document.getElementById('taskDuration'));

    const taskNameEl = document.getElementById('taskName');
    if (taskNameEl) taskNameEl.textContent = task.name || 'Untitled Task';

    const durationEl = document.getElementById('taskDuration');
    if (durationEl) {
        durationEl.textContent = task.duration ? `${task.duration} days` : 'Not specified';
        durationEl.className = task.duration ? 'modal-field-value' : 'modal-field-value empty';
    } else console.error('taskDuration element not found!');

    const startDateEl = document.getElementById('taskStartDate');
    if (startDateEl) {
        startDateEl.textContent = task.startDate ? formatDate(task.startDate) : 'Not set';
        startDateEl.className = task.startDate ? 'modal-field-value' : 'modal-field-value empty';
    } else console.error('taskStartDate element not found!');

    const finishDateEl = document.getElementById('taskFinishDate');
    if (finishDateEl) {
        finishDateEl.textContent = task.endDate ? formatDate(task.endDate) : 'Not set';
        finishDateEl.className = task.endDate ? 'modal-field-value' : 'modal-field-value empty';
    } else console.error('taskFinishDate element not found!');

    const descriptionEl = document.getElementById('taskDescription');
    if (descriptionEl) {
        descriptionEl.textContent = task.description || 'No description available';
        descriptionEl.className = task.description ? 'modal-field-value' : 'modal-field-value empty';
    } else console.error('taskDescription element not found!');

    const editBtn = document.getElementById('editTaskBtn');
    const deleteBtn = document.getElementById('deleteTaskBtn');
    if (editBtn && task.id) editBtn.setAttribute('href', `/tasks/${task.id}/edit`);
    if (deleteBtn && task.id) {
        deleteBtn.onclick = function(e) {
            e.preventDefault();
            if (confirm('Apakah Anda yakin ingin menghapus tugas ini?')) {
                const form = document.getElementById('deleteTaskForm');
                if (form) {
                    form.action = `/tasks/${task.id}`;
                    form.submit();
                } else console.error('deleteTaskForm not found!');
            }
        };
    }

    const rootId = getRootId(task);
    const relLevel = getRelativeLevel(task);
    const colorKey = `color-root-${rootId}-rellevel-${relLevel}`;
    const bgColor = localStorage.getItem(`${colorKey}-bg`) || defaultColors[relLevel % 6].bg;
    const borderColor = localStorage.getItem(`${colorKey}-border`) || defaultColors[relLevel % 6].border;

    const modalHeader = document.querySelector('.modal-header');
    if (modalHeader) modalHeader.style.background = `linear-gradient(135deg, ${bgColor} 0%, ${borderColor} 100%)`;

    let colorPickerContainer = document.getElementById('colorPickerContainer');
    if (colorPickerContainer) colorPickerContainer.remove();
    colorPickerContainer = document.createElement('div');
    colorPickerContainer.id = 'colorPickerContainer';
    colorPickerContainer.className = 'modal-field';
    colorPickerContainer.innerHTML = `
        <label class="modal-field-label">Subtree Color for Level ${relLevel} (Affects only this family)</label>
        <div style="display: flex; gap: 8px; align-items: center;">
            <input type="color" id="levelColorPicker" value="${bgColor}">
            <button id="resetColorBtn" class="modal-btn modal-btn-secondary">Reset to Default</button>
        </div>
    `;
    const modalBody = document.querySelector('.modal-body');
    if (modalBody) modalBody.appendChild(colorPickerContainer);
    else console.error('modal-body not found!');

    const colorPicker = document.getElementById('levelColorPicker');
    if (colorPicker) {
        colorPicker.addEventListener('input', function(e) {
            const newBg = e.target.value;
            const newBorder = darkenColor(newBg);
            localStorage.setItem(`${colorKey}-bg`, newBg);
            localStorage.setItem(`${colorKey}-border`, newBorder);
            if (modalHeader) modalHeader.style.background = `linear-gradient(135deg, ${newBg} 0%, ${newBorder} 100%)`;
            updateGanttChart();
            updateDurationBadgeColors();
        });
    }

    const resetBtn = document.getElementById('resetColorBtn');
    if (resetBtn) {
        resetBtn.addEventListener('click', function() {
            localStorage.removeItem(`${colorKey}-bg`);
            localStorage.removeItem(`${colorKey}-border`);
            const defaultBg = defaultColors[relLevel % 6].bg;
            const defaultBorder = defaultColors[relLevel % 6].border;
            if (colorPicker) colorPicker.value = defaultBg;
            if (modalHeader) modalHeader.style.background = `linear-gradient(135deg, ${defaultBg} 0%, ${defaultBorder} 100%)`;
            updateGanttChart();
            updateDurationBadgeColors();
        });
    }
}

// Add focus trap for accessibility
function trapFocus(element) {
    const focusableElements = element.querySelectorAll('button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])');
    const firstFocusable = focusableElements[0];
    const lastFocusable = focusableElements[focusableElements.length - 1];

    element.addEventListener('keydown', function(e) {
        if (e.key === 'Tab') {
            if (e.shiftKey && document.activeElement === firstFocusable) {
                lastFocusable.focus();
                e.preventDefault();
            } else if (!e.shiftKey && document.activeElement === lastFocusable) {
                firstFocusable.focus();
                e.preventDefault();
            }
        }
    });
}

// Darken color function
function darkenColor(color, amount = 0.1) {
    let [r, g, b] = color.match(/\w\w/g).map(x => parseInt(x, 16));
    r = Math.max(0, Math.round(r * (1 - amount)));
    g = Math.max(0, Math.round(g * (1 - amount)));
    b = Math.max(0, Math.round(b * (1 - amount)));
    return `#${r.toString(16).padStart(2, '0')}${g.toString(16).padStart(2, '0')}${b.toString(16).padStart(2, '0')}`;
}

// Default colors array
const defaultColors = [
    { bg: '#0078d4', border: '#106ebe' }, // relLevel 0
    { bg: '#107c10', border: '#0e6e0e' }, // 1
    { bg: '#881798', border: '#7a1589' }, // 2
    { bg: '#ff8c00', border: '#e67e00' }, // 3
    { bg: '#e81123', border: '#d10e20' }, // 4
    { bg: '#5c2d91', border: '#522982' }  // 5
];

// Get root ID of a task
function getRootId(task) {
    let current = task;
    while (current.parent_id) {
        current = tasksData.find(t => t.id === current.parent_id) || current;
    }
    return current.id;
}

// Get relative level (depth from root)
function getRelativeLevel(task) {
    let level = 0;
    let current = task;
    while (current.parent_id) {
        level++;
        current = tasksData.find(t => t.id === current.parent_id) || current;
    }
    return level;
}

// Get color for root and level
function getColorForRootAndLevel(rootId, relLevel) {
    const bgKey = `color-root-${rootId}-rellevel-${relLevel}-bg`;
    const borderKey = `color-root-${rootId}-rellevel-${relLevel}-border`;
    const bg = localStorage.getItem(bgKey) || defaultColors[relLevel % 6].bg;
    const border = localStorage.getItem(borderKey) || defaultColors[relLevel % 6].border;
    return { bg, border };
}

// Update duration badge colors
function updateDurationBadgeColors() {
    tasksData.forEach(task => {
        const rootId = getRootId(task);
        const relLevel = getRelativeLevel(task);
        const colorKey = `color-root-${rootId}-rellevel-${relLevel}`;
        const bgColor = localStorage.getItem(`${colorKey}-bg`) || defaultColors[relLevel % 6].bg;
        const borderColor = localStorage.getItem(`${colorKey}-border`) || defaultColors[relLevel % 6].border;
        const durationElement = document.getElementById(`duration-${task.id}`);
        if (durationElement) {
            durationElement.style.backgroundColor = bgColor;
            durationElement.style.color = '#ffffff';
            durationElement.style.border = `1px solid ${borderColor}`;
        }
    });
}
</script>
@endsection