@extends('layouts.app')

@section('content')
<style>
    /* Microsoft Project Style Gantt Chart - Enhanced Version */
   .gantt-container {
    display: flex;
    flex-direction: column;
    min-height: calc(100vh - 120px);
    overflow-y: auto; /* Izinkan scroll vertikal */
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
        width: 50%;
        min-width: 50%;
        max-width: 50%;
        overflow-x: auto;
        overflow-y: hidden;
        scrollbar-width: none;
        -ms-overflow-style: none;
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
    }

    .timeline-header-container::-webkit-scrollbar {
        display: none;
    }

    .gantt-content-container {
        flex: 1;
        overflow: visible !important;
        position: relative;
        background: #ffffff;
        max-height: none !important;
        max-width: 100%;
    }

    /* Tambahkan ini untuk memastikan scroll global di body */
   body {
    overflow-y: auto !important;
    width: 100%;
    margin: 0;
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
        background-color: #fef3c7;
        border-left: 2px solid #f59e0b;
        border-right: 2px solid #f59e0b;
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
        background: #0078d4;
        border-color: #106ebe;
    }

    .level-1 {
        background: #107c10;
        border-color: #0e6e0e;
    }

    .level-2 {
        background: #881798;
        border-color: #7a1589;
    }

    .level-3 {
        background: #ff8c00;
        border-color: #e67e00;
    }

    .level-4 {
        background: #e81123;
        border-color: #d10e20;
    }

    .level-5 {
        background: #5c2d91;
        border-color: #522982;
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
        background: #0078d4;
    }

    .indicator-level-1 {
        background: #107c10;
    }

    .indicator-level-2 {
        background: #881798;
    }

    .indicator-level-3 {
        background: #ff8c00;
    }

    .indicator-level-4 {
        background: #e81123;
    }

    .indicator-level-5 {
        background: #5c2d91;
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

    /* Container for Gantt Rows */
    .gantt-rows-container {
        min-height: 100%;
        display: block;
        width: 100%;
        max-width: 100%;
        overflow-x: hidden;
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

    /* Today Indicator Line */
    .today-indicator {
        position: absolute;
        top: 0;
        bottom: 0;
        width: 2px;
        background: #f59e0b;
        z-index: 15;
        pointer-events: none;
        box-shadow: 0 0 4px rgba(245, 158, 11, 0.5);
    }

    /* Loading States */
    .gantt-bar.loading {
        background: #e5e7eb !important;
        animation: pulse 1.5s ease-in-out infinite;
    }

    @keyframes pulse {
        0%, 100% {
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
    pointer-events: none; /* Nonaktifkan interaksi saat modal tidak terlihat */
}
    .modal.show {
        display: flex;
    pointer-events: auto; /* Aktifkan interaksi saat modal ditampilkan */
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
    pointer-events: none; /* Nonaktifkan interaksi selama animasi penutupan */
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

    /* Progress bar styles */
    .progress-container {
        background: #f3f4f6;
        border-radius: 8px;
        height: 24px;
        position: relative;
        overflow: hidden;
        border: 1px solid #e5e7eb;
    }

    .progress-bar {
        height: 100%;
        background: linear-gradient(90deg, #10b981, #059669);
        border-radius: 7px;
        transition: width 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
    }

    .progress-text {
        color: white;
        font-size: 11px;
        font-weight: 600;
        text-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
        position: absolute;
        left: 50%;
        transform: translateX(-50%);
        z-index: 1;
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
                
                <div class="modal-field">
                    <div class="modal-field-label">Progress</div>
                    <div class="progress-container">
                        <div class="progress-bar" id="progressBar" style="width: 0%;">
                            <div class="progress-text" id="progressText">0%</div>
                        </div>
                    </div>
                </div>
                
                <div class="modal-field">
                    <div class="modal-field-label">Start Date</div>
                    <div class="modal-field-value" id="taskStartDate">-</div>
                </div>
                
                <div class="modal-field">
                    <div class="modal-field-label">Finish Date</div>
                    <div class="modal-field-value" id="taskFinishDate">-</div>
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
                
                <a href="#" id="deleteTaskBtn" class="modal-btn modal-btn-danger">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z" clip-rule="evenodd"></path>
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8 7a1 1 0 012 0v4a1 1 0 11-2 0V7zM12 7a1 1 0 012 0v4a1 1 0 11-2 0V7z" clip-rule="evenodd"></path>
                    </svg>
                    Delete
                </a>
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
            Today
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
    let isModalAnimating = false; // ADDED: Animation lock

    @if(isset($structuredTasks) && count($structuredTasks) > 0)
    tasksData = @json($structuredTasks);
    @endif

    // Initialize the Gantt chart
    document.addEventListener('DOMContentLoaded', function() {
        initializeTimeline();
        setupScrollSynchronization();
        updateGanttChart();
        updateZoomButtons();
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
                isToday: isToday(currentDay),
                dayNumber: currentDay.getDate(),
                monthYear: currentDay.toLocaleDateString('en-US', {
                    month: 'short',
                    year: 'numeric'
                })
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
            const startMonth = timelineData.startDate.toLocaleDateString('en-US', {
                month: 'long',
                year: 'numeric'
            });
            const endMonth = timelineData.endDate.toLocaleDateString('en-US', {
                month: 'long',
                year: 'numeric'
            });

            if (timelinePeriod === 1) {
                periodElement.textContent = startMonth;
            } else {
                periodElement.textContent = `${startMonth} - ${endMonth}`;
            }
        }
    }

    // Render timeline headers
    function renderTimelineHeaders() {
        renderMonthHeaders();
        renderDayHeaders();
    }

    // Render month headers
    function renderMonthHeaders() {
        const monthHeaderContainer = document.getElementById('monthHeaderContainer');
        if (!monthHeaderContainer) return;

        // Group days by month
        const monthGroups = {};
        timelineData.days.forEach(day => {
            const monthKey = `${day.date.getFullYear()}-${day.date.getMonth()}`;
            if (!monthGroups[monthKey]) {
                monthGroups[monthKey] = {
                    name: day.date.toLocaleDateString('en-US', {
                        month: 'short',
                        year: 'numeric'
                    }),
                    days: []
                };
            }
            monthGroups[monthKey].days.push(day);
        });

        // Create month header HTML
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

        let dayHeaderHTML = '<div class="day-header">';
        timelineData.days.forEach(day => {
            const classes = ['timeline-day'];
            if (day.isWeekend) classes.push('weekend');
            if (day.isToday) classes.push('today');

            const dayWidth = getDayWidth();
            dayHeaderHTML += `
            <div class="${classes.join(' ')}" style="width: ${dayWidth}px; min-width: ${dayWidth}px; max-width: ${dayWidth}px;">
                ${day.dayNumber}
            </div>
        `;
        });
        dayHeaderHTML += '</div>';

        dayHeaderContainer.innerHTML = dayHeaderHTML;
    }

    // Get current day width based on zoom
    function getDayWidth() {
        const baseWidth = 24;
        return Math.round(baseWidth * (currentZoom / 100));
    }

    // Check if a task should be visible (not collapsed)
    function isTaskVisible(task) {
        // If task has no parent, it's always visible
        if (!task.parent_id) {
            return true;
        }

        // Check if any parent is collapsed
        let currentParentId = task.parent_id;
        while (currentParentId) {
            if (collapsedTasks.has(currentParentId.toString())) {
                return false;
            }
            // Find the parent task to check its parent
            const parentTask = tasksData.find(t => t.id == currentParentId);
            currentParentId = parentTask ? parentTask.parent_id : null;
        }

        return true;
    }

    // Get visible tasks based on collapse state
    function getVisibleTasks() {
        return tasksData.filter(task => isTaskVisible(task));
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

        // Add today indicator if today is visible in timeline
        addTodayIndicator();
    }

    // Generate Gantt row for a task
    function generateGanttRow(task) {
        const dayWidth = getDayWidth();
        let rowHTML = '<div class="gantt-row">';

        // Create grid cells for each day
        timelineData.days.forEach(day => {
            const classes = ['gantt-grid-cell'];
            if (day.isWeekend) classes.push('weekend');
            if (day.isToday) classes.push('today');

            rowHTML += `<div class="${classes.join(' ')}" style="width: ${dayWidth}px; min-width: ${dayWidth}px; max-width: ${dayWidth}px;"></div>`;
        });

        // Add task bar if it falls within timeline
        const taskBar = generateTaskBar(task, dayWidth);
        if (taskBar) {
            rowHTML += taskBar;
        }

        rowHTML += '</div>';
        return rowHTML;
    }

    // Generate task bar
    function generateTaskBar(task, dayWidth) {
        if (!task.start && !task.startDate) return null;
        if (!task.finish && !task.endDate) return null;

        const taskStart = new Date(task.start || task.startDate);
        const taskEnd = new Date(task.finish || task.endDate);

        // Check if task overlaps with timeline
        if (taskEnd < timelineData.startDate || taskStart > timelineData.endDate) {
            return null; // Task is outside timeline
        }

        // Calculate position and width
        const timelineStart = timelineData.startDate;
        const startDayOffset = Math.max(0, Math.floor((taskStart - timelineStart) / (24 * 60 * 60 * 1000)));
        const endDayOffset = Math.min(timelineData.days.length - 1, Math.floor((taskEnd - timelineStart) / (24 * 60 * 60 * 1000)));

        const barLeft = startDayOffset * dayWidth;
        const barWidth = Math.max(dayWidth, (endDayOffset - startDayOffset + 1) * dayWidth - 2);

        const levelClass = `level-${(task.level || 0) % 6}`;
        const progress = task.progress || 0;
        const progressWidth = progress ? (barWidth * progress / 100) : 0;

        let taskBarHTML = `
        <div class="gantt-bar ${levelClass}" 
            style="left: ${barLeft}px; width: ${barWidth}px;"
            data-task-id="${task.id}"
            data-start-day="${startDayOffset}"
            data-duration="${task.duration || 0}"
            title="${task.name} (${task.duration || 0} days) - ${progress}% complete">
    `;

        // Add progress indicator if exists
        if (progress > 0) {
            taskBarHTML += `<div class="progress-indicator" style="width: ${progressWidth}px; height: 100%; background: rgba(255,255,255,0.3); position: absolute; left: 0; top: 0; border-radius: 2px;"></div>`;
        }

        taskBarHTML += `<span class="task-bar-text">${task.name}</span></div>`;

        return taskBarHTML;
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

            const ganttRows = document.querySelectorAll('.gantt-row');
            ganttRows.forEach(row => {
                // Remove existing today indicator
                const existingIndicator = row.querySelector('.today-indicator');
                if (existingIndicator) {
                    existingIndicator.remove();
                }

                // Add new today indicator
                const todayIndicator = document.createElement('div');
                todayIndicator.className = 'today-indicator';
                todayIndicator.style.left = leftPosition + 'px';
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
        if (zoomLevelElement) {
            zoomLevelElement.textContent = currentZoom + '%';
        }

        updateZoomButtons();
        renderTimelineHeaders();
        updateGanttChart();
    }

    function updateZoomButtons() {
        const zoomInBtn = document.getElementById('zoomInBtn');
        const zoomOutBtn = document.getElementById('zoomOutBtn');

        if (zoomInBtn) {
            zoomInBtn.disabled = currentZoom >= maxZoom;
        }

        if (zoomOutBtn) {
            zoomOutBtn.disabled = currentZoom <= minZoom;
        }
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

    // Scroll synchronization
    function setupScrollSynchronization() {
        const taskListBody = document.getElementById('taskListBody');
        const ganttContent = document.getElementById('ganttContent');
        const timelineHeaderSection = document.getElementById('timelineHeaderSection');

        if (!taskListBody || !ganttContent || !timelineHeaderSection) return;

        // Vertical scroll synchronization
        taskListBody.addEventListener('scroll', function() {
            ganttContent.scrollTop = this.scrollTop;
        });

        ganttContent.addEventListener('scroll', function() {
            taskListBody.scrollTop = this.scrollTop;
            timelineHeaderSection.scrollLeft = this.scrollLeft;
        });

        // Horizontal scroll synchronization
        timelineHeaderSection.addEventListener('scroll', function() {
            ganttContent.scrollLeft = this.scrollLeft;
        });
    }

    // Task collapse/expand functionality - MODIFIED
    function toggleTaskCollapse(taskId) {
        const toggleIcon = document.querySelector(`[data-task-id="${taskId}"].toggle-collapse`);
        const childrenContainer = document.querySelector(`.task-children[data-parent-id="${taskId}"]`);

        if (toggleIcon && childrenContainer) {
            toggleIcon.classList.toggle('rotate-90');
            childrenContainer.classList.toggle('collapsed');
            
            // Update collapsed tasks tracking
            if (childrenContainer.classList.contains('collapsed')) {
                collapsedTasks.add(taskId.toString());
            } else {
                collapsedTasks.delete(taskId.toString());
            }
            
            // Update Gantt chart to reflect visibility changes
            updateGanttChart();
        }
    }

    // Add event listeners for toggle buttons
    document.addEventListener('click', function(e) {
        if (e.target.closest('.toggle-collapse')) {
            const taskId = e.target.closest('.toggle-collapse').getAttribute('data-task-id');
            toggleTaskCollapse(taskId);
        }

        // Task bar click handler
        if (e.target.closest('.gantt-bar')) {
            const taskId = e.target.closest('.gantt-bar').getAttribute('data-task-id');
            handleTaskBarClick(taskId);
        }
    });

    // Handle task bar click
    function handleTaskBarClick(taskId) {
        const task = tasksData.find(t => t.id == taskId);
        if (task) {
            console.log('Task clicked:', task);

            // Trigger a custom event
            document.dispatchEvent(new CustomEvent('taskSelected', {
                detail: {
                    task: task
                }
            }));
        }
    }

    // Expand/Collapse all functions - MODIFIED
    function expandAll() {
        document.querySelectorAll('.task-children').forEach(container => {
            container.classList.remove('collapsed');
        });
        document.querySelectorAll('.toggle-collapse').forEach(icon => {
            icon.classList.add('rotate-90');
        });
        
        // Clear collapsed tasks tracking
        collapsedTasks.clear();
        
        // Update Gantt chart
        updateGanttChart();
    }

    function collapseAll() {
        document.querySelectorAll('.task-children').forEach(container => {
            container.classList.add('collapsed');
            // Add parent task ID to collapsed set
            const parentId = container.getAttribute('data-parent-id');
            if (parentId) {
                collapsedTasks.add(parentId);
            }
        });
        document.querySelectorAll('.toggle-collapse').forEach(icon => {
            icon.classList.remove('rotate-90');
        });
        
        // Update Gantt chart
        updateGanttChart();
    }

    // Keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        if (e.ctrlKey || e.metaKey) {
            if (e.key === '=' || e.key === '+') {
                e.preventDefault();
                zoomIn();
            } else if (e.key === '-') {
                e.preventDefault();
                zoomOut();
            }
        }

        // Navigation shortcuts
        if (e.altKey) {
            if (e.key === 'ArrowLeft') {
                e.preventDefault();
                navigateMonth(-1);
            } else if (e.key === 'ArrowRight') {
                e.preventDefault();
                navigateMonth(1);
            } else if (e.key === 'Home') {
                e.preventDefault();
                goToToday();
            }
        }

        // Modal keyboard support - UPDATED
        if (e.key === 'Escape') {
            const modal = document.getElementById('taskModal');
            if (modal.classList.contains('opening') && !isModalAnimating) {
                closeTaskModal();
            }
            // Close any open modals or deselect items
            document.querySelectorAll('.gantt-bar.selected').forEach(bar => {
                bar.classList.remove('selected');
            });
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

    // Public API for external integration
    window.GanttChart = {
        // Navigation
        navigateMonth,
        changePeriod,
        goToToday,

        // Zoom
        zoomIn,
        zoomOut,
        setZoom: function(level) {
            if (level >= minZoom && level <= maxZoom) {
                currentZoom = level;
                updateZoomLevel();
            }
        },

        // Task management
        expandAll,
        collapseAll,
        updateGanttChart,

        // Data manipulation
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
            const task = tasksData.find(task => task.id == taskId);
            if (task) {
                Object.assign(task, updates);
                updateGanttChart();
            }
        },

        refreshData: function(newTasks) {
            tasksData = newTasks;
            updateGanttChart();
        },

        // Getters
        getCurrentPeriod: function() {
            return {
                startDate: timelineData.startDate,
                endDate: timelineData.endDate,
                period: timelinePeriod
            };
        },

        getVisibleTasks: function() {
            return getVisibleTasks().filter(task => {
                const startDate = task.start || task.startDate;
                const endDate = task.finish || task.endDate;
                if (!startDate || !endDate) return false;
                const taskStart = new Date(startDate);
                const taskEnd = new Date(endDate);
                return !(taskEnd < timelineData.startDate || taskStart > timelineData.endDate);
            });
        }
    };

    // Event listeners for Laravel integration
    document.addEventListener('taskSelected', function(e) {
        const task = e.detail.task;
        console.log('Task selected:', task);
        // Handle task selection - you can customize this
    });

    document.addEventListener('taskUpdated', function(e) {
        const updatedTask = e.detail.task;
        console.log('Task updated:', updatedTask);
        // Handle task updates - refresh the chart
        updateGanttChart();
    });

    // Initialize collapse state from DOM
    document.addEventListener('DOMContentLoaded', function() {
        // Check for initially collapsed task containers
        document.querySelectorAll('.task-children.collapsed').forEach(container => {
            const parentId = container.getAttribute('data-parent-id');
            if (parentId) {
                collapsedTasks.add(parentId);
            }
        });
    });

    // Enhanced modal functions with smooth animations - UPDATED
    function openTaskModal(task) {
        if (isModalAnimating) return;
        
        const modal = document.getElementById('taskModal');
        isModalAnimating = true;
        
        // Populate modal content
        populateModalContent(task);
        
        // Prevent body scroll
        document.body.style.overflow = 'hidden';
        
        // Show modal
        modal.style.display = 'flex';
        
        // Force reflow
        modal.offsetHeight;
        
        // Add opening class for animation
        modal.classList.add('opening');
        
        // Reset animation flag after animation completes
        setTimeout(() => {
            isModalAnimating = false;
        }, 300);
    }

    function closeTaskModal() {
    if (isModalAnimating) return;
    
    const modal = document.getElementById('taskModal');
    isModalAnimating = true;
    
    // Add closing class for animation
    modal.classList.remove('opening');
    modal.classList.add('closing');
    
    // Hide modal after animation completes
    setTimeout(() => {
        modal.classList.remove('closing');
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
        isModalAnimating = false;
    }, 300); // Match with CSS transition duration
}

    function populateModalContent(task) {
        // Populate modal fields
        document.getElementById('taskName').textContent = task.name || 'Untitled Task';
        
        const durationEl = document.getElementById('taskDuration');
        durationEl.textContent = task.duration ? `${task.duration} days` : 'Not specified';
        durationEl.className = task.duration ? 'modal-field-value' : 'modal-field-value empty';
        
        const startDateEl = document.getElementById('taskStartDate');
        startDateEl.textContent = task.start || task.startDate ? formatDate(task.start || task.startDate) : 'Not set';
        startDateEl.className = (task.start || task.startDate) ? 'modal-field-value' : 'modal-field-value empty';
        
        const finishDateEl = document.getElementById('taskFinishDate');
        finishDateEl.textContent = task.finish || task.endDate ? formatDate(task.finish || task.endDate) : 'Not set';
        finishDateEl.className = (task.finish || task.endDate) ? 'modal-field-value' : 'modal-field-value empty';
        
        // Handle progress
        const progress = task.progress || 0;
        const progressBar = document.getElementById('progressBar');
        const progressText = document.getElementById('progressText');
        
        if (progressBar && progressText) {
            progressBar.style.width = progress + '%';
            progressText.textContent = progress + '%';
        }
        
        const descriptionEl = document.getElementById('taskDescription');
        descriptionEl.textContent = task.description || 'No description available';
        descriptionEl.className = task.description ? 'modal-field-value' : 'modal-field-value empty';
        
        // Set action button links
        const editBtn = document.getElementById('editTaskBtn');
        const deleteBtn = document.getElementById('deleteTaskBtn');
        
        if (editBtn && task.id) {
            editBtn.setAttribute('href', `/tasks/${task.id}/edit`);
        }
        
        if (deleteBtn && task.id) {
            deleteBtn.onclick = function(e) {
                e.preventDefault();
                if (confirm('Are you sure you want to delete this task?')) {
                    window.location.href = `/tasks/${task.id}/delete`;
                }
            };
        }
    }

    // Enhanced click handlers - UPDATED
    document.addEventListener('click', function(e) {
        const modal = document.getElementById('taskModal');
        
        // Close modal when clicking backdrop (but not during animation)
        if (e.target === modal && !isModalAnimating) {
            closeTaskModal();
        }
        
        // Handle gantt bar clicks
        if (e.target.closest('.gantt-bar')) {
            const taskId = e.target.closest('.gantt-bar').getAttribute('data-task-id');
            const task = tasksData.find(t => t.id == taskId);
            if (task) {
                openTaskModal(task);
            }
        }
    });

    // Prevent double-tap zoom on mobile
    document.addEventListener('touchend', function(e) {
        if (e.target.closest('.modal-btn') || e.target.closest('.modal-close-x')) {
            e.preventDefault();
        }
    });

    // Add focus trap for accessibility
    function trapFocus(element) {
        const focusableElements = element.querySelectorAll(
            'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
        );
        const firstFocusable = focusableElements[0];
        const lastFocusable = focusableElements[focusableElements.length - 1];

        element.addEventListener('keydown', function(e) {
            if (e.key === 'Tab') {
                if (e.shiftKey) {
                    if (document.activeElement === firstFocusable) {
                        lastFocusable.focus();
                        e.preventDefault();
                    }
                } else {
                    if (document.activeElement === lastFocusable) {
                        firstFocusable.focus();
                        e.preventDefault();
                    }
                }
            }
        });
    }

    // Initialize focus trap when modal opens
    document.addEventListener('DOMContentLoaded', function() {
        const modal = document.getElementById('taskModal');
        if (modal) {
            trapFocus(modal);
        }
    });
</script>

@endsection