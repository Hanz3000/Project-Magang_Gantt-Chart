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
        --day-width: 24px;
    }

    /* ===== TASK LIST STYLING (GAMBAR STYLE) ===== */
    .task-row {
        display: grid;
        grid-template-columns: 50px 1fr 150px 110px 110px; /* Adjusted first column to 50px for extra button */
        height: 40px;
        align-items: center; /* Center content vertically */
        padding: 0;
        border-bottom: 1px solid #e0e0e0;
        background: white;
        font-size: 13px;
        transition: background-color 0.15s ease;
        max-width: 100%;
        position: relative;
    }

    .task-row:hover {
        background-color: #f5f5f5 !important;
    }

    .task-row.hidden-task {
        display: none;
    }

    .task-row.task-child {
        background: white;
    }

    .task-row.task-child:hover {
        background-color: #f5f5f5 !important;
    }

    /* Task Cell Base */
    .task-cell {
        padding: 8px 12px;
        border-right: 1px solid #e0e0e0;
        display: flex;
        align-items: center; /* Center content vertically */
        height: 100%;
    }

    /* Task Toggle Cell - Adjusted for two buttons */
    .task-toggle-cell {
        justify-content: center;
        padding: 4px;
        border-right: 1px solid #e0e0e0;
        display: flex;
        align-items: center; /* Center content vertically */
        gap: 4px;
    }

    .toggle-collapse {
        cursor: pointer;
        padding: 6px;
        border-radius: 4px;
        transition: all 0.15s ease;
        color: #424242;
        width: 20px;
        height: 20px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: none;
        background: transparent;
    }

    .toggle-collapse:hover {
        background-color: #e0e0e0;
    }

    .toggle-collapse.rotate-90 {
        transform: rotate(90deg);
    }

    /* Full Chart Button */
    .full-chart-btn {
        background: none;
        border: none;
        cursor: pointer;
        padding: 2px;
        border-radius: 2px;
        color: #666;
        transition: all 0.2s ease;
        width: 20px;
        height: 20px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
    }

    .full-chart-btn:hover {
        color: #0078d4;
        background: #f0f0f0;
    }

    .gantt-container.chart-only-mode .full-chart-btn {
        color: #dc2626;
    }

    .gantt-container.chart-only-mode .full-chart-btn:hover {
        color: #b91c1c;
        background: #fef2f2;
    }

    /* Task Name Cell */
    .task-name-cell {
        text-align: left;
        justify-content: flex-start;
        padding: 8px 12px;
        cursor: pointer;
        border-right: 1px solid #e0e0e0;
        display: flex;
        align-items: center; /* Center content vertically */
        gap: 8px;
        position: relative;
    }

    .task-name-text {
        font-size: 14px;
        color: #212121;
        font-weight: 500;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .task-row.task-child .task-name-text {
        font-weight: 400;
    }

    /* Task Icon Square (akan berubah warna sesuai level) */
    .task-icon-square {
        width: 16px;
        height: 16px;
        border-radius: 3px;
        flex-shrink: 0;
        border: 1px solid rgba(0, 0, 0, 0.1);
        transition: all 0.2s ease;
    }

    /* Task Indicator - digunakan di partial yang ada */
    .task-indicator {
        width: 16px;
        height: 16px;
        border-radius: 3px;
        flex-shrink: 0;
        border: 1px solid rgba(0, 0, 0, 0.1);
        transition: all 0.2s ease;
        margin-right: 8px;
    }

    /* Date Cell */
    .task-date-cell {
        justify-content: flex-start;
        padding: 8px 12px;
        border-right: 1px solid #e0e0e0;
        display: flex;
        align-items: center; /* Center content vertically */
    }

    .task-date-text {
        font-size: 13px;
        color: #424242;
        font-weight: 400;
    }

    /* Task Duration Cell */
    .task-duration-cell {
        justify-content: center;
        padding: 8px 12px;
        border-right: none;
        display: flex;
        align-items: center; /* Center content vertically */
    }

    /* Duration Badge Modern - Dibuat netral tanpa warna dinamis */
    .duration-badge-modern {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: 500;
        min-width: 35px;
        text-align: center;
        color: #6b7280;
        background: transparent;
        border: 1px solid #e5e7eb;
        transition: all 0.2s ease;
    }

    .duration-badge-modern:hover {
        background: #f9fafb;
        border-color: #d1d5db;
    }

    /* Duration Badge Simple (tetap untuk backward compatibility) */
    .duration-badge-simple {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: 500;
        min-width: 35px;
        text-align: center;
        color: #6b7280;
        background: transparent;
        border: 1px solid #e5e7eb;
        transition: all 0.2s ease;
    }

    /* ===== GANTT CONTAINER ===== */
    .gantt-container {
        display: flex;
        flex-direction: column;
        min-height: calc(100vh - 120px);
        overflow-y: auto;
        overflow-x: hidden;
        background: #ffffff;
        border: 1px solid #d1d5db;
        border-radius: 12px; /* Rounded corners */
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        max-width: 100vw;
        box-sizing: border-box;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    }

    .gantt-container.fullscreen {
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        z-index: 9999;
        background: white;
        border-radius: 0;
        box-shadow: none;
        min-height: 100vh;
        max-width: 100vw;
        overflow: hidden;
    }

    /* Chart Only Mode Styles */
    .gantt-container.chart-only-mode .task-list-container,
    .gantt-container.chart-only-mode .task-list-header-section,
    .gantt-container.chart-only-mode .resizer {
        display: none !important;
    }

    .gantt-container.chart-only-mode .combined-header-container .timeline-header-section {
        width: 100% !important;
    }

    .gantt-container.chart-only-mode .gantt-main-content .gantt-view-container {
        width: 100% !important;
    }

    .gantt-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-bottom: 1px solid #d1d5db;
        padding: 12px 16px;
        font-size: 16px;
        font-weight: 600;
        color: white;
        border-radius: 12px 12px 0 0;
    }

    .gantt-main-content {
        display: flex;
        flex: 1;
        min-height: 0;
        overflow: hidden;
    }

    /* Task List Container */
    .task-list-container {
        width: 550px; /* Lebih lebar untuk menampung kolom baru */
        min-width: 400px;
        max-width: 80%;
        display: flex;
        flex-direction: column;
        border-right: 1px solid #e0e0e0;
        background: #ffffff;
        overflow: hidden;
        flex-shrink: 0;
    }

    /* Gantt View Container */
    .gantt-view-container {
        flex: 1;
        display: flex;
        flex-direction: column;
        background: white;
        overflow: hidden;
        min-width: 0;
    }

    /* Combined Header Container */
    .combined-header-container {
        display: flex;
        width: 100%;
        background: #f8f9fa;
        border-bottom: 2px solid #e5e7eb;
        position: sticky;
        top: 0;
        z-index: 20;
    }

    .task-list-header-section {
        width: 550px; /* Match task-list-container */
        min-width: 400px;
        max-width: 80%;
        border-right: 1px solid #e0e0e0;
        overflow: hidden;
        flex-shrink: 0;
    }

    .timeline-header-section {
        flex: 1;
        overflow-x: hidden !important;
        overflow-y: hidden;
        min-width: 0;
    }

    .timeline-header-section::-webkit-scrollbar {
        display: none;
    }

    /* Modern Task Header Row - Adjusted grid */
    .task-header-row {
        display: grid;
        grid-template-columns: 50px 1fr 150px 110px 110px; /* Adjusted first column to 50px */
        height: 40px;
        align-items: center;
        padding: 0;
        border-bottom: 2px solid #bdbdbd;
        position: relative;
        background: #fafafa;
    }

    /* ... (rest of the styles remain unchanged) ... */

    .task-header-cell {
        padding: 8px 12px;
        text-align: left;
        border-right: 1px solid #e0e0e0;
        font-size: 12px;
        font-weight: 600;
        color: #616161;
        text-transform: none;
        letter-spacing: 0;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        display: flex;
        align-items: center;
        height: 100%;
    }

    .task-header-cell:first-child {
        justify-content: center;
        gap: 4px; /* For header alignment */
    }

    .task-header-cell:nth-child(4),
    .task-header-cell:nth-child(5) {
        justify-content: flex-start;
    }

    .task-header-cell:last-child {
        justify-content: center;
        border-right: none;
    }

    .shifted-right {
        position: relative;
        left: 150px;
        transition: left 0.3s ease;
    }

    .shifted {
        position: relative;
        left: 0;
        transition: left 0.3s ease;
    }

    /* Task List Body */
    .task-list-body {
        flex: 1;
        min-height: 0 !important;
        overflow-y: auto !important;
        background: white;
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
        min-width: 100%;
    }

    .timeline-header-container::-webkit-scrollbar {
        display: none;
    }

    .gantt-content-container {
        flex: 1;
        min-height: 0;
        overflow-x: auto !important;
        overflow-y: auto !important;
        max-height: none !important;
        max-width: 100%;
        scrollbar-width: thin;
        padding-bottom: 10px;
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
        width: var(--day-width);
        min-width: var(--day-width);
        max-width: var(--day-width);
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

    .timeline-day::after {
        content: attr(data-dayname);
        font-size: 7px;
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
        color: #dc2626;
    }

    .timeline-day.weekend::after {
        color: #dc2626;
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

    .timeline-day.sunday {
        background-color: #fef2f2;
        color: #dc2626;
    }

    .timeline-day.sunday::after {
        color: #dc2626;
        font-weight: 700;
    }

    /* Gantt Rows */
    .gantt-row {
        height: 40px; /* Match task row height exactly */
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
        background: #f0f7ff;
    }

    .gantt-row.hidden-gantt-row {
        display: none;
    }

    .gantt-grid-cell {
        width: var(--day-width);
        min-width: var(--day-width);
        max-width: var(--day-width);
        height: 40px; /* Match exact height */
        border-right: 1px solid #f1f5f9;
        flex-shrink: 0;
    }

    .gantt-grid-cell.weekend {
        background-color: #f9fafb;
    }

    .gantt-grid-cell.today {
        background-color: #dbeafe;
        border-left: 2px solid #1e40af;
        border-right: 2px solid #1e40af;
    }

    /* Task Bars - Enhanced */
    .gantt-bar {
        position: absolute;
        top: 8px; /* Centered vertically */
        height: 20px;
        border-radius: 4px;
        display: flex;
        align-items: center;
        justify-content: flex-start;
        padding: 0 8px;
        font-size: 10px;
        font-weight: 600;
        color: white;
        cursor: pointer;
        transition: all 0.2s ease;
        overflow: hidden;
        white-space: nowrap;
        text-overflow: ellipsis;
        min-width: 20px;
        z-index: 5;
        border: 1px solid rgba(0, 0, 0, 0.15);
        box-sizing: border-box;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }

    .gantt-bar:hover {
        transform: translateY(-2px);
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

    .indicator-level-0 { background: var(--level-0-bg); }
    .indicator-level-1 { background: var(--level-1-bg); }
    .indicator-level-2 { background: var(--level-2-bg); }
    .indicator-level-3 { background: var(--level-3-bg); }
    .indicator-level-4 { background: var(--level-4-bg); }
    .indicator-level-5 { background: var(--level-5-bg); }

    /* Task Children Container */
    .task-children {
        display: block;
    }

    .task-children.collapsed {
        display: none;
    }

    /* Resizer Styles */
    .resizer {
        width: 5px;
        background-color: #d1d5db;
        cursor: col-resize;
        position: relative;
        transition: background-color 0.2s ease;
        flex-shrink: 0;
        border-left: 1px solid #e5e7eb;
        border-right: 1px solid #e5e7eb;
    }

    .resizer:hover,
    .resizer.active {
        background-color: #9ca3af;
    }

    .resizer::before {
        content: '';
        position: absolute;
        top: 0;
        left: -5px;
        width: 15px;
        height: 100%;
        background: transparent;
    }

    /* Modern Toolbar */
    .toolbar {
        background: #f8f9fa;
        border-bottom: 1px solid #e5e7eb;
        padding: 12px 16px;
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
        border-radius: 8px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        border: 1px solid #e5e7eb;
    }

    /* Month Navigation */
    .month-navigation {
        display: flex;
        align-items: center;
        gap: 8px;
        background: #f8f9fa;
        padding: 8px 12px;
        border-bottom: none;
        box-shadow: none;
        flex-wrap: wrap;
        border-radius: 8px;
    }

    .nav-button {
        padding: 8px 14px;
        border: 1px solid #d1d5db;
        border-radius: 6px;
        background: white;
        color: #374151;
        font-size: 12px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.15s ease;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .nav-button:hover:not(:disabled) {
        background: #f3f4f6;
        border-color: #9ca3af;
        transform: translateY(-1px);
    }

    .nav-button:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    .current-period {
        font-weight: 600;
        color: #1e40af;
        font-size: 13px;
        min-width: 180px;
        text-align: center;
        padding: 8px 14px;
        background: #dbeafe;
        border-radius: 6px;
        border: 1px solid #93c5fd;
        cursor: pointer;
        transition: all 0.15s ease;
    }

    .current-period:hover {
        background: #bfdbfe;
        border-color: #60a5fa;
    }

    .period-selector {
        padding: 8px 10px;
        border: 1px solid #d1d5db;
        border-radius: 6px;
        background: white;
        color: #374151;
        font-size: 12px;
        font-weight: 500;
        cursor: pointer;
    }

    .control-button {
        padding: 8px 12px;
        border: 1px solid #d1d5db;
        border-radius: 6px;
        background: white;
        color: #374151;
        font-size: 12px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.15s ease;
        display: flex;
        align-items: center;
        gap: 4px;
        text-decoration: none;
    }

    .control-button:hover {
        background: #f3f4f6;
        border-color: #9ca3af;
        transform: translateY(-1px);
    }

    .control-button.primary {
        background: #0078d4;
        color: white;
        border-color: #106ebe;
    }

    .control-button.primary:hover {
        background: #106ebe;
    }

    /* Zoom Controls */
    .zoom-controls {
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .zoom-button {
        width: 30px;
        height: 30px;
        border: 1px solid #d1d5db;
        background: #ffffff;
        color: #374151;
        cursor: pointer;
        border-radius: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        font-weight: 600;
        transition: all 0.15s ease;
    }

    .zoom-button:hover:not(:disabled) {
        background: #f3f4f6;
        border-color: #9ca3af;
        transform: scale(1.05);
    }

    .zoom-button:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    .zoom-level {
        font-size: 12px;
        font-weight: 600;
        color: #374151;
        min-width: 50px;
        text-align: center;
        padding: 4px 8px;
        border: 1px solid #d1d5db;
        border-radius: 6px;
        background: #fff;
    }

    /* Loading States */
    .gantt-bar.loading {
        background: #e5e7eb !important;
        animation: pulse 1.5s ease-in-out infinite;
    }

    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.7; }
    }

    .gantt-rows-container {
        width: fit-content;
        min-width: 100%;
        overflow-x: visible;
        display: block;
    }

    /* Enhanced Modal Styles with Smooth Animations */
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
    }

    .modal.show {
        display: flex;
        pointer-events: auto;
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

    #dateModal.show {
        background-color: rgba(0, 0, 0, 0.5);
        backdrop-filter: blur(4px);
        opacity: 1;
    }

    #dateModal.show > div {
        transform: translateY(0) scale(1);
        opacity: 1;
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
            width: 350px;
            min-width: 180px;
            max-width: 70%;
        }

        .resizer {
            width: 5px;
        }
    }

    @media (max-width: 768px) {
        .task-list-container,
        .task-list-header-section {
            width: 300px;
            min-width: 150px;
            max-width: 60%;
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

        .date-fields-row {
            flex-direction: column;
            gap: 12px;
        }

        .date-field {
            flex: 1 1 100%;
        }

        .resizer {
            width: 5px;
        }

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

    .modal-btn:focus {
        outline: 2px solid #2563eb;
        outline-offset: 2px;
    }

    .modal-close-x:focus {
        outline: 2px solid rgba(255, 255, 255, 0.8);
        outline-offset: 2px;
    }

    .full-chart-btn:focus {
        outline: 2px solid #2563eb;
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
        .gantt-bar,
        .toggle-collapse,
        .nav-button,
        .control-button,
        .modal-content {
            transition: none;
        }

        .gantt-bar:hover {
            transform: none;
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

    /* Highlight untuk task row - Biru sangat soft */
    .task-row.row-highlighted {
        background-color: #eff6ff !important;
        transition: background-color 0.2s ease;
    }

    .task-row.row-highlighted .task-cell {
        background-color: #eff6ff !important;
    }

    /* Highlight untuk gantt row - Biru sangat soft */
    .gantt-row.row-highlighted .gantt-grid-cell {
        background-color: #eff6ff !important;
        transition: background-color 0.2s ease;
    }

    /* Highlight untuk gantt bar - Shadow biru halus */
    .gantt-row.row-highlighted .gantt-bar {
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        transform: scale(1.02);
        transition: all 0.2s ease;
        z-index: 10;
    }

    /* Highlight untuk task name - Biru sedikit lebih gelap */
    .task-row.row-highlighted .task-name-text {
        color: #1e40af;
        font-weight: 600;
    }

    /* Highlight untuk duration badge */
    .task-row.row-highlighted .duration-badge-modern {
        background: #dbeafe !important;
        border-color: #93c5fd !important;
        color: #1e40af !important;
    }

    /* Highlight untuk kolom timeline */
    .timeline-day.column-highlighted {
        background-color: #bfdbfe !important;
        color: #1e40af !important;
        font-weight: 700 !important;
        border-left: 2px solid #3b82f6;
        border-right: 2px solid #3b82f6;
        box-shadow: inset 0 0 10px rgba(59, 130, 246, 0.2);
    }

    .gantt-grid-cell.column-highlighted {
        background-color: #dbeafe !important;
        border-left: 2px solid #93c5fd !important;
        border-right: 2px solid #93c5fd !important;
    }

    /* Kombinasi row dan column highlight (intersection) */
    .gantt-row.row-highlighted .gantt-grid-cell.column-highlighted {
        background-color: #93c5fd !important;
        box-shadow: inset 0 0 15px rgba(59, 130, 246, 0.3);
    }

    /* Fullscreen-specific adjustments for alignment and scrolling */
    .gantt-container.fullscreen .gantt-main-content {
        position: relative;
        overflow: hidden;
    }

    .gantt-container.fullscreen .task-list-body,
    .gantt-container.fullscreen .gantt-content-container {
        scrollbar-gutter: stable;
    }
</style>

<div class="gantt-container">
    <!-- Toolbar -->
    <div class="toolbar">
        <!-- Navigasi Bulan -->
        <div class="month-navigation">
            <button class="nav-button" id="prevBtn" onclick="navigateMonth(-1)" title="Bulan Sebelumnya (Alt + Panah Kiri)">
                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                </svg>
                Sebelumnya
            </button>

            <div class="current-period" id="currentPeriod" onclick="openDateModal()" title="Klik untuk memilih bulan/tahun">
                Januari 2025
            </div>

            <button class="nav-button" id="nextBtn" onclick="navigateMonth(1)" title="Bulan Berikutnya (Alt + Panah Kanan)">
                Berikutnya
                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                </svg>
            </button>

            <select class="period-selector" id="periodSelector" onchange="changePeriod(this.value)" title="Pilih Periode Timeline">
                <option value="1">1 Bulan</option>
                <option value="3" selected>3 Bulan</option>
                <option value="6">6 Bulan</option>
                <option value="12">1 Tahun</option>
            </select>

            <button class="nav-button" onclick="goToToday()" title="Ke Hari Ini (Alt + Home)">
                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                </svg>
                Hari Ini
            </button>
        </div>

        <div class="toolbar-right">
            <!-- Zoom + Perluas + Ciutkan + Tambah Tugas -->
            <div class="zoom-controls">
                <button class="zoom-button" id="zoomOutBtn" onclick="zoomOut()" title="Zoom Out">-</button>
                <span class="zoom-level" id="zoomLevel">100%</span>
                <button class="zoom-button" id="zoomInBtn" onclick="zoomIn()" title="Zoom In">+</button>
            </div>

            <button class="control-button" onclick="expandAll()" title="Perluas semua tugas">
                Perluas
            </button>

            <button class="control-button" onclick="collapseAll()" title="Ciutkan semua tugas">
                Ciutkan
            </button>

            <button class="control-button" onclick="toggleFullscreen()" title="Fullscreen (F11)">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M3 5a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 10a1 1 0 011-1h6a1 1 0 110 2H4a1 1 0 01-1-1zM3 15a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM13 5a1 1 0 00-1 1v6a1 1 0 102 0V6a1 1 0 00-1-1zM15 7a1 1 0 011-1h6a1 1 0 110 2h-6a1 1 0 01-1-1zM15 12a1 1 0 011-1h6a1 1 0 110 2h-6a1 1 0 01-1-1zM9 15a1 1 0 011-1h6a1 1 0 110 2h-6a1 1 0 01-1-1z" clip-rule="evenodd"></path>
                </svg>
                Layar Penuh
            </button>

            @if(isset($createRoute))
            <a href="{{ $createRoute }}" class="control-button primary">
                Tambah Tugas Baru
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
                    <div class="modal-field-label">Durasi</div>
                    <div class="modal-field-value" id="taskDuration">-</div>
                </div>

                <!-- Start Date dan Finish Date bersebelahan -->
                <div class="date-fields-row">
                    <div class="date-field">
                        <div class="modal-field-label">Tanggal Mulai</div>
                        <div class="modal-field-value" id="taskStartDate">-</div>
                    </div>
                    <div class="date-field">
                        <div class="modal-field-label">Tanggal Selesai</div>
                        <div class="modal-field-value" id="taskFinishDate">-</div>
                    </div>
                </div>

                <div class="modal-field">
                    <div class="modal-field-label">Deskripsi</div>
                    <div class="modal-field-value" id="taskDescription">Tidak ada deskripsi tersedia</div>
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
                    class="modal-btn modal-btn-danger">
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

    <!-- Modal Pemilih Bulan/Tahun -->
    <div id="dateModal" class="fixed inset-0 bg-black bg-opacity-0 backdrop-blur-none flex items-center justify-center z-[1000] transition-all duration-400 ease-in-out" role="dialog" aria-modal="true" aria-labelledby="dateModalTitle" style="display: none;">
        <div class="bg-white rounded-lg w-full max-w-md mx-4 overflow-hidden shadow-xl transform translate-y-10 scale-95 opacity-0 transition-all duration-400 ease-[cubic-bezier(0.4,0,0.2,1)]">
            <div class="bg-blue-600 text-white p-4 flex justify-between items-center">
                <h4 id="dateModalTitle" class="text-lg font-semibold">Pilih Bulan dan Tahun</h4>
                <button class="text-2xl font-bold hover:bg-white/20 rounded p-1 transition" onclick="closeDateModal()" aria-label="Tutup modal">&times;</button>
            </div>
            <div class="p-4">
                <div class="flex items-center justify-center gap-2 mb-4">
                    <button class="bg-gray-100 border border-gray-300 rounded px-2 py-1 hover:bg-gray-200 transition" onclick="changeModalYear(-1)" aria-label="Tahun sebelumnya">&lt;</button>
                    <input type="number" id="modalYearInput" class="w-20 text-center border border-gray-300 rounded px-2 py-1 focus:outline-none focus:ring-2 focus:ring-blue-500" aria-label="Tahun saat ini">
                    <button class="bg-gray-100 border border-gray-300 rounded px-2 py-1 hover:bg-gray-200 transition" onclick="changeModalYear(1)" aria-label="Tahun berikutnya">&gt;</button>
                </div>
                <div id="modalMonthsGrid" class="grid grid-cols-3 gap-2 sm:grid-cols-2"></div>
            </div>
            <div class="bg-gray-50 p-4 text-right">
                <button class="bg-gray-100 border border-gray-300 rounded px-4 py-2 hover:bg-gray-200 transition" onclick="closeDateModal()">Batal</button>
            </div>
        </div>
    </div>

    <!-- Combined Header -->
    <div class="combined-header-container">
        <!-- Task List Header Section -->
        <div class="task-list-header-section">
            <div class="task-header-row">
                <div class="task-header-cell" style="justify-content: center;">
                    <button class="full-chart-btn" onclick="toggleChartOnlyMode()" title="Sembunyikan Daftar Tugas (Full Chart Mode)">
                        <svg width="12" height="12" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M3 5a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 10a1 1 0 011-1h6a1 1 0 110 2H4a1 1 0 01-1-1zM3 15a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM13 5a1 1 0 00-1 1v6a1 1 0 102 0V6a1 1 0 00-1-1zM15 7a1 1 0 011-1h6a1 1 0 110 2h-6a1 1 0 01-1-1zM15 12a1 1 0 011-1h6a1 1 0 110 2h-6a1 1 0 01-1-1zM9 15a1 1 0 011-1h6a1 1 0 110 2h-6a1 1 0 01-1-1z" clip-rule="evenodd"></path>
                        </svg>
                    </button>
                </div>
                <div class="task-header-cell">Nama Task</div>
                <div class="task-header-cell">Tanggal Mulai</div>
                <div class="task-header-cell">Tanggal Selesai</div>
                <div class="task-header-cell">Durasi</div>
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
        <!-- Task List -->
        <div class="task-list-container">
            <div class="task-list-body" id="taskListBody">
                @if(isset($tasks) && $tasks->count() > 0)
                    @foreach($tasks->whereNull('parent_id') as $task)
                        @include('partials.task-item', [
                            'task' => $task, 
                            'level' => 0,
                            'allTasks' => $tasks
                        ])
                    @endforeach
                @else
                    <div class="p-8 text-center text-gray-500">
                        <svg class="w-12 h-12 mx-auto mb-4 text-gray-300" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"></path>
                        </svg>
                        <p class="text-sm">Tidak ada tugas. Klik "Tambah Tugas" untuk memulai.</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Resizer -->
        <div class="resizer" id="resizerMain"></div>

        <!-- Gantt View -->
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
let collapsedTasks = new Set();
let isModalAnimating = false;

const monthNames = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

@if(isset($structuredTasks) && count($structuredTasks) > 0)
tasksData = @json($structuredTasks);
@endif

document.addEventListener('DOMContentLoaded', function() {
    console.log('Tasks data:', tasksData);
    const yearInput = document.getElementById('modalYearInput');
    if (yearInput) {
        yearInput.addEventListener('input', renderModalMonths);
    }

    // Load saved colors from localStorage
    for (let i = 0; i < 6; i++) {
        const bg = localStorage.getItem(`level-${i}-bg`);
        const border = localStorage.getItem(`level-${i}-border`);
        if (bg) document.documentElement.style.setProperty(`--level-${i}-bg`, bg);
        if (border) document.documentElement.style.setProperty(`--level-${i}-border`, border);
    }

    initializeTimeline();
    setupScrollSynchronization();
    updateGanttChart();
    updateZoomButtons();
    initResizer();
    setupRowHighlight();
    setupColumnHighlight();

    document.querySelectorAll('.task-children.collapsed').forEach(container => {
        const parentId = container.getAttribute('data-parent-id');
        if (parentId) collapsedTasks.add(parentId);
    });

    const modal = document.getElementById('taskModal');
    if (modal) trapFocus(modal);

    // Initialize task icon colors after page load
    setTimeout(() => {
        initializeTaskIconColors();
        updateTaskIconColors();
    }, 100);
});

function toggleChartOnlyMode() {
    const container = document.querySelector('.gantt-container');
    if (!container) return;

    const isActive = container.classList.contains('chart-only-mode');
    container.classList.toggle('chart-only-mode');

    const toolbarRight = document.querySelector('.toolbar-right');
    let exitBtn = document.querySelector('.exit-chart-only');

    if (!isActive) {
        // Entering mode: add exit button to toolbar
        if (!exitBtn) {
            exitBtn = document.createElement('button');
            exitBtn.className = 'control-button exit-chart-only';
            exitBtn.innerHTML = `
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                </svg>
                Tampilkan Daftar Tugas
            `;
            exitBtn.title = 'Kembalikan Daftar Tugas';
            exitBtn.onclick = toggleChartOnlyMode;
            toolbarRight.appendChild(exitBtn);
        }
        document.querySelectorAll('.full-chart-btn').forEach(btn => {
            btn.title = 'Tampilkan Daftar Tugas';
        });
    } else {
        // Exiting mode: remove exit button
        if (exitBtn) {
            exitBtn.remove();
        }
        document.querySelectorAll('.full-chart-btn').forEach(btn => {
            btn.title = 'Sembunyikan Daftar Tugas (Full Chart Mode)';
        });
    }

    // Re-sync scroll after layout change
    setTimeout(setupScrollSynchronization, 100);
}

// ... (all other functions remain unchanged) ...

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

function isToday(date) {
    const today = new Date();
    return date.getDate() === today.getDate() &&
           date.getMonth() === today.getMonth() &&
           date.getFullYear() === today.getFullYear();
}

function updateCurrentPeriodDisplay() {
    const periodElement = document.getElementById('currentPeriod');
    if (periodElement) {
        const startMonth = timelineData.startDate.toLocaleDateString('en-US', { month: 'long', year: 'numeric' });
        const endMonth = timelineData.endDate.toLocaleDateString('en-US', { month: 'long', year: 'numeric' });
        periodElement.textContent = timelinePeriod === 1 ? startMonth : `${startMonth} - ${endMonth}`;
    }
}

function renderTimelineHeaders() {
    renderMonthHeaders();
    renderDayHeaders();
    updateGanttWidths();
    setDefaultScrollPosition();
}

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

function isHoliday(date) {
    const holidays = [
        '2025-01-01', '2025-03-03', '2025-04-18', '2025-05-01', '2025-05-29',
        '2025-06-01', '2025-06-29', '2025-08-17', '2025-09-16', '2025-12-25'
    ];
    const dateString = date.toISOString().split('T')[0];
    return holidays.includes(dateString);
}

function getDayWidth() {
    return 24 * (currentZoom / 100);
}

function isTaskVisible(task) {
    // Jika tidak punya parent, pasti visible
    if (!task.parent_id) {
        return true;
    }
    
    // Cari parent dari task
    const parent = tasksData.find(t => t.id === task.parent_id);
    if (!parent) {
        return false;
    }
    
    // Jika parent di-collapse, task tidak visible
    if (collapsedTasks.has(parent.id.toString())) {
        return false;
    }
    
    // Rekursif cek parent-parent di atasnya
    return isTaskVisible(parent);
}

function getVisibleTasks() {
    // tasksData dari controller sudah urut hierarkis
    // Filter hanya yang visible (tidak ketutup collapse)
    return tasksData.filter(task => {
        // Root task selalu visible
        if (!task.parent_id) return true;
        
        // Cek apakah parent di-collapse
        let currentParentId = task.parent_id;
        while (currentParentId) {
            if (collapsedTasks.has(currentParentId.toString())) {
                return false;
            }
            // Cari parent berikutnya
            const parentTask = tasksData.find(t => t.id === currentParentId);
            currentParentId = parentTask ? parentTask.parent_id : null;
        }
        
        return true;
    });
}

function getTasksInDOMOrder() {
    const orderedTasks = [];
    const taskRows = document.querySelectorAll('.task-row');
    
    taskRows.forEach(row => {
        const taskId = parseInt(row.getAttribute('data-task-id'));
        const task = tasksData.find(t => t.id === taskId);
        if (task) {
            orderedTasks.push(task);
        }
    });
    
    return orderedTasks;
}

// UPDATE fungsi updateGanttChart() jadi seperti ini:
function updateGanttChart() {
    const ganttRowsContainer = document.getElementById('ganttRowsContainer');
    if (!ganttRowsContainer) return;

    // Ambil tasks sesuai urutan DOM, bukan dari tasksData
    const orderedTasks = getTasksInDOMOrder();
    
    console.log('DOM Order:', orderedTasks.map(t => t.name)); // Debug
    
    let ganttHTML = '';
    if (orderedTasks.length > 0) {
        orderedTasks.forEach(task => {
            // Cek visibility untuk collapse/expand
            const taskRow = document.querySelector(`.task-row[data-task-id="${task.id}"]`);
            const isVisible = taskRow && taskRow.offsetParent !== null;
            
            if (isVisible) {
                ganttHTML += generateGanttRow(task);
            }
        });
    }
    
    ganttRowsContainer.innerHTML = ganttHTML;
    addTodayIndicator();
    updateGanttWidths();
}

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

function generateTaskBar(task, dayWidth) {
    const hasChildren = tasksData.some(t => t.parent_id == task.id);
    if (!hasChildren && task.parent_id == null) return null;
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
             data-parent-id="${task.parent_id || ''}"
             data-start-day="${startDayOffset}"
             data-duration="${task.duration || 0}">
            <span class="task-bar-text">${task.name}</span>
        </div>
    `;
}

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

const minZoom = 50;
const maxZoom = 200;
const zoomStep = 25;

function updateZoomLevel() {
    const zoomLevelElement = document.getElementById('zoomLevel');
    if (zoomLevelElement) zoomLevelElement.textContent = `${currentZoom}%`;
    updateZoomButtons();
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
        renderTimelineHeaders();
        updateGanttChart();
    }
}

function zoomOut() {
    if (currentZoom > minZoom) {
        currentZoom -= zoomStep;
        updateZoomLevel();
        renderTimelineHeaders();
        updateGanttChart();
    }
}

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

function setupScrollSynchronization() {
    const taskListBody = document.getElementById('taskListBody');
    const ganttContent = document.getElementById('ganttContent');
    const timelineHeaderSection = document.getElementById('timelineHeaderSection');
    if (!taskListBody || !ganttContent || !timelineHeaderSection) return;

    // Enhanced synchronization for better alignment, especially in fullscreen
    const syncScroll = () => {
        if (taskListBody.style.display !== 'none') {
            taskListBody.scrollTop = ganttContent.scrollTop;
        }
        timelineHeaderSection.scrollLeft = ganttContent.scrollLeft;
    };

    const syncTaskScroll = () => {
        ganttContent.scrollTop = taskListBody.scrollTop;
    };

    taskListBody.addEventListener('scroll', syncTaskScroll, { passive: true });
    ganttContent.addEventListener('scroll', syncScroll, { passive: true });

    // Additional sync on resize or fullscreen change for alignment
    const handleAlignmentSync = () => {
        setTimeout(syncScroll, 50);
    };
    window.addEventListener('resize', handleAlignmentSync);
    document.addEventListener('fullscreenchange', handleAlignmentSync);
}

function setDefaultScrollPosition() {
    const ganttContent = document.getElementById('ganttContent');
    if (ganttContent) {
        ganttContent.scrollLeft = 0;
    }
}

function toggleTaskCollapse(taskId) {
    const toggleIcon = document.querySelector(`[data-task-id="${taskId}"].toggle-collapse`);
    const childrenContainer = document.querySelector(`.task-children[data-parent-id="${taskId}"]`);
    
    if (toggleIcon && childrenContainer) {
        // Toggle class
        toggleIcon.classList.toggle('rotate-90');
        childrenContainer.classList.toggle('collapsed');
        
        // Update collapsedTasks Set
        if (childrenContainer.classList.contains('collapsed')) {
            collapsedTasks.add(taskId.toString());
        } else {
            collapsedTasks.delete(taskId.toString());
        }
        
        // Update gantt chart dengan delay
        setTimeout(() => {
            updateGanttChart();
        }, 50);
    }
}

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
            console.log('Task from name cell:', task);
            openTaskModal(task);
        }
    }
    if (e.target === document.getElementById('taskModal') && !isModalAnimating) {
        closeTaskModal();
    }
});

function handleTaskBarClick(taskId) {
    const task = tasksData.find(t => t.id == taskId);
    if (task) {
        console.log('Task from gantt bar:', task);
        openTaskModal(task);
        document.dispatchEvent(new CustomEvent('taskSelected', { detail: { task } }));
    }
}

// Ganti fungsi expandAll() dan collapseAll() yang lama dengan yang ini:

function expandAll() {
    // Hapus class collapsed dari semua task-children
    document.querySelectorAll('.task-children').forEach(container => {
        container.classList.remove('collapsed');
    });
    
    // Rotate semua toggle icon
    document.querySelectorAll('.toggle-collapse').forEach(icon => {
        icon.classList.add('rotate-90');
    });
    
    // Clear collapsedTasks Set
    collapsedTasks.clear();
    
    // Update Gantt chart setelah delay singkat untuk memastikan DOM sudah terupdate
    setTimeout(() => {
        updateGanttChart();
    }, 50);
}

function collapseAll() {
    // Tambahkan class collapsed ke semua task-children
    document.querySelectorAll('.task-children').forEach(container => {
        container.classList.add('collapsed');
        const parentId = container.getAttribute('data-parent-id');
        if (parentId) {
            collapsedTasks.add(parentId);
        }
    });
    
    // Remove rotate dari semua toggle icon
    document.querySelectorAll('.toggle-collapse').forEach(icon => {
        icon.classList.remove('rotate-90');
    });
    
    // Update Gantt chart setelah delay singkat untuk memastikan DOM sudah terupdate
    setTimeout(() => {
        updateGanttChart();
    }, 50);
}


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
    if (e.key === 'F11') {
        e.preventDefault();
        toggleFullscreen();
    }
});

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

function handleResize() {
    setTimeout(() => {
        renderTimelineHeaders();
        updateGanttChart();
    }, 100);
}
window.addEventListener('resize', handleResize);

document.addEventListener('touchend', function(e) {
    if (e.target.closest('.modal-btn') || e.target.closest('.modal-close-x')) e.preventDefault();
});

function toggleFullscreen() {
    const container = document.querySelector('.gantt-container');
    if (!container) return;

    if (!document.fullscreenElement) {
        // Enter fullscreen
        const scrollY = window.scrollY;
        document.body.classList.add('no-scroll');
        document.body.style.top = `-${scrollY}px`;
        document.body.dataset.scrollY = scrollY;
        container.classList.add('fullscreen');
        container.requestFullscreen().catch(err => {
            console.error('Error entering fullscreen:', err);
            // Fallback: just use the class for styling
        });
    } else {
        // Exit fullscreen
        document.exitFullscreen().then(() => {
            document.body.classList.remove('no-scroll');
            const scrollY = document.body.dataset.scrollY || 0;
            document.body.style.top = '';
            window.scrollTo(0, parseInt(scrollY));
            container.classList.remove('fullscreen');
        }).catch(err => {
            console.error('Error exiting fullscreen:', err);
            // Fallback: remove class
            document.body.classList.remove('no-scroll');
            const scrollY = document.body.dataset.scrollY || 0;
            document.body.style.top = '';
            window.scrollTo(0, parseInt(scrollY));
            container.classList.remove('fullscreen');
        });
    }
}

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
    modal.offsetHeight;
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

    const taskNameEl = document.getElementById('taskName');
    if (taskNameEl) taskNameEl.textContent = task.name || 'Untitled Task';

    const durationEl = document.getElementById('taskDuration');
    if (durationEl) {
        durationEl.textContent = task.duration ? `${task.duration} hari` : 'Tidak ditentukan';
        durationEl.className = task.duration ? 'modal-field-value' : 'modal-field-value empty';
    }

    const startDateEl = document.getElementById('taskStartDate');
    if (startDateEl) {
        startDateEl.textContent = task.startDate ? formatDate(task.startDate) : 'Tidak diatur';
        startDateEl.className = task.startDate ? 'modal-field-value' : 'modal-field-value empty';
    }

    const finishDateEl = document.getElementById('taskFinishDate');
    if (finishDateEl) {
        finishDateEl.textContent = task.endDate ? formatDate(task.endDate) : 'Not set';
        finishDateEl.className = task.endDate ? 'modal-field-value' : 'modal-field-value empty';
    }

    const descriptionEl = document.getElementById('taskDescription');
    if (descriptionEl) {
        descriptionEl.textContent = task.description || 'Deskripsi tidak tersedia';
        descriptionEl.className = task.description ? 'modal-field-value' : 'modal-field-value empty';
    }

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
                }
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
        <label class="modal-field-label">Ubah Warna Level ${relLevel}</label>
        <div style="display: flex; gap: 8px; align-items: center;">
            <input type="color" id="levelColorPicker" value="${bgColor}">
            <button id="resetColorBtn" class="modal-btn modal-btn-secondary">Reset Warna</button>
        </div>
    `;
    const modalBody = document.querySelector('.modal-body');
    if (modalBody) modalBody.appendChild(colorPickerContainer);

    const colorPicker = document.getElementById('levelColorPicker');
    if (colorPicker) {
        colorPicker.addEventListener('input', function(e) {
            const newBg = e.target.value;
            const newBorder = darkenColor(newBg);
            localStorage.setItem(`${colorKey}-bg`, newBg);
            localStorage.setItem(`${colorKey}-border`, newBorder);
            if (modalHeader) modalHeader.style.background = `linear-gradient(135deg, ${newBg} 0%, ${newBorder} 100%)`;
            updateGanttChart();
            updateTaskIconColors(); // Update icon colors instead of badges
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
            updateTaskIconColors(); // Update icon colors instead of badges
        });
    }
}

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

function darkenColor(color, amount = 0.1) {
    let [r, g, b] = color.match(/\w\w/g).map(x => parseInt(x, 16));
    r = Math.max(0, Math.round(r * (1 - amount)));
    g = Math.max(0, Math.round(g * (1 - amount)));
    b = Math.max(0, Math.round(b * (1 - amount)));
    return `#${r.toString(16).padStart(2, '0')}${g.toString(16).padStart(2, '0')}${b.toString(16).padStart(2, '0')}`;
}

const defaultColors = [
    { bg: '#0078d4', border: '#106ebe' },
    { bg: '#107c10', border: '#0e6e0e' },
    { bg: '#881798', border: '#7a1589' },
    { bg: '#ff8c00', border: '#e67e00' },
    { bg: '#e81123', border: '#d10e20' },
    { bg: '#5c2d91', border: '#522982' }
];

function getRootId(task) {
    let current = task;
    while (current.parent_id) {
        current = tasksData.find(t => t.id === current.parent_id) || current;
    }
    return current.id;
}

function getRelativeLevel(task) {
    let level = 0;
    let current = task;
    while (current.parent_id) {
        level++;
        current = tasksData.find(t => t.id === current.parent_id) || current;
    }
    return level;
}

function getColorForRootAndLevel(rootId, relLevel) {
    const bgKey = `color-root-${rootId}-rellevel-${relLevel}-bg`;
    const borderKey = `color-root-${rootId}-rellevel-${relLevel}-border`;
    const bg = localStorage.getItem(bgKey) || defaultColors[relLevel % 6].bg;
    const border = localStorage.getItem(borderKey) || defaultColors[relLevel % 6].border;
    return { bg, border };
}

// Initialize task icon colors on page load to match gantt chart colors
function initializeTaskIconColors() {
    // Loop through all task rows and set initial colors based on task data
    document.querySelectorAll('.task-row').forEach(taskRow => {
        const taskId = taskRow.getAttribute('data-task-id');
        const task = tasksData.find(t => t.id == taskId);
        
        if (task) {
            const rootId = getRootId(task);
            const relLevel = getRelativeLevel(task);
            const { bg, border } = getColorForRootAndLevel(rootId, relLevel);
            
            // Find the icon within this task row
            const iconElement = taskRow.querySelector('.task-icon-square');
            if (iconElement) {
                iconElement.style.backgroundColor = bg;
                iconElement.style.borderColor = border;
                // Remove default color classes
                iconElement.classList.remove('task-icon-blue', 'task-icon-green');
            }
        }
    });
}

// Update task icon colors instead of duration badges
function updateTaskIconColors() {
    tasksData.forEach(task => {
        const rootId = getRootId(task);
        const relLevel = getRelativeLevel(task);
        const colorKey = `color-root-${rootId}-rellevel-${relLevel}`;
        const bgColor = localStorage.getItem(`${colorKey}-bg`) || defaultColors[relLevel % 6].bg;
        
        // Update the task icon square color by finding the task row first
        const taskRow = document.querySelector(`[data-task-id="${task.id}"].task-row`);
        if (taskRow) {
            const iconElement = taskRow.querySelector('.task-icon-square');
            if (iconElement) {
                iconElement.style.backgroundColor = bgColor;
                iconElement.style.borderColor = darkenColor(bgColor);
                iconElement.classList.remove('task-icon-blue', 'task-icon-green');
            }
        }
    });
}

function openDateModal() {
    const modal = document.getElementById('dateModal');
    if (!modal) return;
    modal.style.display = 'flex';
    setTimeout(() => modal.classList.add('show'), 10);

    const yearInput = document.getElementById('modalYearInput');
    yearInput.value = currentDate.getFullYear();
    renderModalMonths();
    yearInput.focus();

    modal.addEventListener('click', outsideClickHandler);
    document.addEventListener('keydown', escKeyHandler);
}

function closeDateModal() {
    const modal = document.getElementById('dateModal');
    if (!modal) return;
    modal.classList.remove('show');
    setTimeout(() => modal.style.display = 'none', 400);

    modal.removeEventListener('click', outsideClickHandler);
    document.removeEventListener('keydown', escKeyHandler);
}

function outsideClickHandler(e) {
    if (!e.target.closest('.bg-white') && e.target.id === 'dateModal') closeDateModal();
}

function escKeyHandler(e) {
    if (e.key === 'Escape') closeDateModal();
}

function renderModalMonths() {
    const grid = document.getElementById('modalMonthsGrid');
    if (!grid) return;
    grid.innerHTML = '';

    const currentMonth = currentDate.getMonth();
    const currentYear = currentDate.getFullYear();
    const modalYear = parseInt(document.getElementById('modalYearInput').value) || currentYear;

    monthNames.forEach((name, index) => {
        const btn = document.createElement('button');
        btn.className = `p-3 rounded border border-gray-300 text-sm font-medium transition ${
            index === currentMonth && modalYear === currentYear
                ? 'bg-blue-600 text-white border-blue-700'
                : 'bg-gray-50 hover:bg-gray-100'
        }`;
        btn.textContent = name;
        btn.onclick = () => setMonthYear(index, modalYear);
        grid.appendChild(btn);
    });
}

function changeModalYear(direction) {
    const yearInput = document.getElementById('modalYearInput');
    yearInput.value = parseInt(yearInput.value) + direction;
    renderModalMonths();
}

function setMonthYear(month, year) {
    currentDate = new Date(year, month, 1);
    closeDateModal();
    updateCurrentPeriodDisplay();
    initializeTimeline();
    updateGanttChart();
}

function initResizer() {
    const resizer = document.getElementById('resizerMain');
    const taskListContainer = document.querySelector('.task-list-container');
    const headerLeft = document.querySelector('.task-list-header-section');

    if (!resizer || !taskListContainer || !headerLeft) {
        console.error('Resizer initialization failed: Missing elements');
        return;
    }

    const savedWidth = localStorage.getItem('taskListWidth');
    if (savedWidth) {
        taskListContainer.style.width = savedWidth;
        headerLeft.style.width = savedWidth;
        updateGanttChart();
        renderTimelineHeaders();
    }

    let startX, startWidth;

    resizer.addEventListener('mousedown', function(e) {
        e.preventDefault();
        startX = e.clientX;
        startWidth = taskListContainer.getBoundingClientRect().width;
        resizer.classList.add('active');

        document.addEventListener('mousemove', onMouseMove);
        document.addEventListener('mouseup', onMouseUp);
    });

    function onMouseMove(e) {
        const dx = e.clientX - startX;
        const maxWidthPercent = window.matchMedia("(max-width: 1024px)").matches ? 0.7 : 0.8;
        const minWidth = window.matchMedia("(max-width: 768px)").matches ? 150 : 200;
        const maxWidth = window.innerWidth * maxWidthPercent;

        const newWidth = Math.max(minWidth, Math.min(maxWidth, startWidth + dx));
        const newWidthPx = `${newWidth}px`;

        taskListContainer.style.width = newWidthPx;
        headerLeft.style.width = newWidthPx;
        updateGanttChart();
        renderTimelineHeaders();
    }

    function onMouseUp() {
        resizer.classList.remove('active');
        document.removeEventListener('mousemove', onMouseMove);
        document.removeEventListener('mouseup', onMouseUp);
        localStorage.setItem('taskListWidth', taskListContainer.style.width);
    }

    resizer.addEventListener('touchstart', function(e) {
        e.preventDefault();
        startX = e.touches[0].clientX;
        startWidth = taskListContainer.getBoundingClientRect().width;
        resizer.classList.add('active');

        document.addEventListener('touchmove', onTouchMove);
        document.addEventListener('touchend', onTouchEnd);
    });

    function onTouchMove(e) {
        const dx = e.touches[0].clientX - startX;
        const maxWidthPercent = window.matchMedia("(max-width: 1024px)").matches ? 0.7 : 0.8;
        const minWidth = window.matchMedia("(max-width: 768px)").matches ? 150 : 200;
        const maxWidth = window.innerWidth * maxWidthPercent;

        const newWidth = Math.max(minWidth, Math.min(maxWidth, startWidth + dx));
        const newWidthPx = `${newWidth}px`;

        taskListContainer.style.width = newWidthPx;
        headerLeft.style.width = newWidthPx;
        updateGanttChart();
        renderTimelineHeaders();
    }

    function onTouchEnd() {
        resizer.classList.remove('active');
        document.removeEventListener('touchmove', onTouchMove);
        document.removeEventListener('touchend', onTouchEnd);
        localStorage.setItem('taskListWidth', taskListContainer.style.width);
    }

    window.addEventListener('resize', () => {
        const currentWidth = parseFloat(taskListContainer.style.width) || taskListContainer.getBoundingClientRect().width;
        const maxWidthPercent = window.matchMedia("(max-width: 1024px)").matches ? 0.7 : 0.8;
        const minWidth = window.matchMedia("(max-width: 768px)").matches ? 150 : 200;
        const maxWidth = window.innerWidth * maxWidthPercent;

        const adjustedWidth = Math.max(minWidth, Math.min(maxWidth, currentWidth));
        taskListContainer.style.width = `${adjustedWidth}px`;
        headerLeft.style.width = `${adjustedWidth}px`;
        localStorage.setItem('taskListWidth', taskListContainer.style.width);
        updateGanttChart();
        renderTimelineHeaders();
    });
}

// 1. Fungsi untuk highlight row
function highlightRow(taskId) {
    // Remove existing highlights
    removeAllHighlights();
    
    if (!taskId) return;
    
    // Highlight task row di task list
    const taskRow = document.querySelector(`.task-row[data-task-id="${taskId}"]`);
    if (taskRow) {
        taskRow.classList.add('row-highlighted');
    }
    
    // Highlight gantt row
    const ganttRow = document.querySelector(`.gantt-row[data-task-id="${taskId}"]`);
    if (ganttRow) {
        ganttRow.classList.add('row-highlighted');
    }
}

// 2. Fungsi untuk remove highlight
function removeAllHighlights() {
    document.querySelectorAll('.row-highlighted').forEach(el => {
        el.classList.remove('row-highlighted');
    });
}

// 3. Setup event listeners untuk hover
function setupRowHighlight() {
    // Hover pada task rows
    document.addEventListener('mouseover', function(e) {
        const taskRow = e.target.closest('.task-row');
        if (taskRow) {
            const taskId = taskRow.getAttribute('data-task-id');
            highlightRow(taskId);
        }
        
        // Hover pada gantt bars
        const ganttBar = e.target.closest('.gantt-bar');
        if (ganttBar) {
            const taskId = ganttBar.getAttribute('data-task-id');
            highlightRow(taskId);
        }
        
        // Hover pada gantt rows (cells)
        const ganttRow = e.target.closest('.gantt-row');
        if (ganttRow && !ganttBar) {
            const taskId = ganttRow.getAttribute('data-task-id');
            highlightRow(taskId);
        }
    });
    
    // Remove highlight saat mouse leave gantt container
    const ganttContainer = document.querySelector('.gantt-container');
    if (ganttContainer) {
        ganttContainer.addEventListener('mouseleave', removeAllHighlights);
    }
    
    // Remove highlight saat mouse leave task list
    const taskListBody = document.getElementById('taskListBody');
    if (taskListBody) {
        taskListBody.addEventListener('mouseleave', removeAllHighlights);
    }
}

function highlightTimelineColumn(dayIndex) {
    // Remove existing column highlights
    removeAllColumnHighlights();
    
    if (dayIndex === null || dayIndex === undefined) return;
    
    // Highlight timeline day header
    const timelineDays = document.querySelectorAll('.timeline-day');
    if (timelineDays[dayIndex]) {
        timelineDays[dayIndex].classList.add('column-highlighted');
    }
    
    // Highlight semua gantt-grid-cell di kolom yang sama
    document.querySelectorAll('.gantt-row').forEach(row => {
        const cells = row.querySelectorAll('.gantt-grid-cell');
        if (cells[dayIndex]) {
            cells[dayIndex].classList.add('column-highlighted');
        }
    });
}

function removeAllColumnHighlights() {
    document.querySelectorAll('.column-highlighted').forEach(el => {
        el.classList.remove('column-highlighted');
    });
}

function setupColumnHighlight() {
    // Hover pada timeline day header
    document.addEventListener('mouseover', function(e) {
        const timelineDay = e.target.closest('.timeline-day');
        if (timelineDay) {
            // Ambil index dari parent container yang benar
            const dayContainer = timelineDay.closest('.day-header');
            const allDays = dayContainer.querySelectorAll('.timeline-day');
            const dayIndex = Array.from(allDays).indexOf(timelineDay);
            highlightTimelineColumn(dayIndex);
        }
        
        // Hover pada gantt grid cell
        const ganttCell = e.target.closest('.gantt-grid-cell');
        if (ganttCell && !e.target.closest('.gantt-bar')) {
            const row = ganttCell.closest('.gantt-row');
            const cells = row.querySelectorAll('.gantt-grid-cell');
            const dayIndex = Array.from(cells).indexOf(ganttCell);
            highlightTimelineColumn(dayIndex);
        }
    });
    
    // Remove highlight saat mouse leave dari timeline header
    const timelineHeader = document.querySelector('.timeline-header-section');
    if (timelineHeader) {
        timelineHeader.addEventListener('mouseleave', removeAllColumnHighlights);
    }
    
    // Remove highlight saat mouse leave dari gantt content
    const ganttContent = document.getElementById('ganttContent');
    if (ganttContent) {
        ganttContent.addEventListener('mouseleave', removeAllColumnHighlights);
    }
}
</script>
@endsection