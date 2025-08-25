@extends('layouts.app')

@section('content')
<style>
/* Microsoft Project Style Gantt Chart - Width Constraints Fixed */
.gantt-container {
    display: flex;
    flex-direction: column;
    min-height: calc(100vh - 120px);
    overflow: hidden; /* Keep hidden to prevent horizontal overflow */
    background: #ffffff;
    border: 1px solid #d1d5db;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    max-width: 100vw; /* Prevent container from exceeding viewport */
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
    overflow: hidden; /* Prevent main content from overflowing */
}

/* Task List - 50% width - CONSTRAINED */
.task-list-container {
    width: 50%;
    min-width: 50%;
    max-width: 50%;
    display: flex;
    flex-direction: column;
    border-right: 1px solid #d1d5db;
    background: #ffffff;
    overflow: hidden; /* Prevent overflow */
}

/* Gantt View - 50% width - CONSTRAINED */
.gantt-view-container {
    width: 50%;
    min-width: 50%;
    max-width: 50%;
    display: flex;
    flex-direction: column;
    background: white;
    overflow: hidden; /* Prevent overflow */
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

/* Task List Body - Controlled Height */
.task-list-body {
    flex: 1;
    overflow-y: auto;
    overflow-x: hidden;
    min-height: 0;
    background: white;
    max-height: calc(100vh - 200px); /* Set reasonable max height */
}

.timeline-header-container {
    background: #f1f3f4;
    border-bottom: 1px solid #d1d5db;
    overflow-x: auto;
    overflow-y: hidden;
    scrollbar-width: none;
    -ms-overflow-style: none;
    /* Calculate max width based on timeline days */
    max-width: 100%;
}

.timeline-header-container::-webkit-scrollbar {
    display: none;
}

/* Gantt Content Container - FIXED WIDTH CONSTRAINTS */
.gantt-content-container {
    flex: 1;
    overflow: auto;
    position: relative;
    background: #ffffff;
    max-height: calc(100vh - 200px); /* Set reasonable max height */
    max-width: 100%; /* Ensure doesn't exceed container */
}

/* Timeline Grid - CONTROLLED WIDTH */
.month-header {
    display: flex;
    border-bottom: 1px solid #d1d5db;
    background: #f1f3f4;
    height: 20px;
    min-width: fit-content; /* Adjust to content */
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
    min-width: 60px; /* Set minimum width for months */
}

.day-header {
    display: flex;
    background: #f8f9fa;
    height: 32px;
    border-bottom: 1px solid #d1d5db;
    min-width: fit-content; /* Adjust to content */
}

.timeline-day {
    width: 24px;
    min-width: 24px;
    max-width: 24px; /* Ensure consistent width */
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

/* Task Rows - CONTROLLED WIDTH */
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
    max-width: 100%; /* Prevent overflow */
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
}

/* Gantt Rows - CONTROLLED WIDTH */
.gantt-row {
    height: 32px;
    position: relative;
    border-bottom: 1px solid #f1f5f9;
    background: white;
    display: flex;
    min-width: fit-content; /* Adjust to content width */
}

.gantt-row:nth-child(even) {
    background: #fafbfc;
}

.gantt-row:hover {
    background: #e3f2fd;
}

.gantt-grid-cell {
    width: 24px;
    min-width: 24px;
    max-width: 24px; /* Ensure consistent width */
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

/* Task Bars - CONSTRAINED WIDTH */
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
    max-width: calc(100% - 10px); /* Ensure bar doesn't exceed row width */
    z-index: 5;
    border: 1px solid rgba(0,0,0,0.1);
    box-sizing: border-box; /* Include padding and border in width */
}

.gantt-bar:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
    z-index: 10;
}

/* Microsoft Project Task Colors */
.level-0 { background: #0078d4; border-color: #106ebe; }
.level-1 { background: #107c10; border-color: #0e6e0e; }
.level-2 { background: #881798; border-color: #7a1589; }
.level-3 { background: #ff8c00; border-color: #e67e00; }
.level-4 { background: #e81123; border-color: #d10e20; }
.level-5 { background: #5c2d91; border-color: #522982; }

/* Task Indicators */
.task-indicator {
    width: 12px;
    height: 12px;
    border-radius: 2px;
    margin-right: 6px;
    flex-shrink: 0;
    border: 1px solid rgba(0,0,0,0.1);
}

.indicator-level-0 { background: #0078d4; }
.indicator-level-1 { background: #107c10; }
.indicator-level-2 { background: #881798; }
.indicator-level-3 { background: #ff8c00; }
.indicator-level-4 { background: #e81123; }
.indicator-level-5 { background: #5c2d91; }

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

/* Container for Gantt Rows - CONTROLLED WIDTH */
.gantt-rows-container {
    min-height: 100%;
    display: block;
    width: 100%;
    max-width: 100%;
    overflow-x: hidden; /* Prevent horizontal overflow */
}

/* Scrollbar Styles */
.task-list-body::-webkit-scrollbar,
.gantt-content-container::-webkit-scrollbar,
.timeline-header-container::-webkit-scrollbar {
    width: 12px;
    height: 12px;
}

.task-list-body::-webkit-scrollbar-track,
.gantt-content-container::-webkit-scrollbar-track,
.timeline-header-container::-webkit-scrollbar-track {
    background: #f1f1f1;
}

.task-list-body::-webkit-scrollbar-thumb,
.gantt-content-container::-webkit-scrollbar-thumb,
.timeline-header-container::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 6px;
}

.task-list-body::-webkit-scrollbar-thumb:hover,
.gantt-content-container::-webkit-scrollbar-thumb:hover,
.timeline-header-container::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}

/* Responsive Design - IMPROVED CONSTRAINTS */
@media (max-width: 1024px) {
    .task-list-container {
        width: 45%;
        min-width: 45%;
        max-width: 45%;
    }
    
    .gantt-view-container {
        width: 55%;
        min-width: 55%;
        max-width: 55%;
    }
    
    .task-list-header-section {
        width: 45%;
        min-width: 45%;
        max-width: 45%;
    }
    
    .timeline-header-section {
        width: 55%;
        min-width: 55%;
        max-width: 55%;
    }
    
    .timeline-day {
        width: 22px;
        min-width: 22px;
        max-width: 22px;
    }
    
    .gantt-grid-cell {
        width: 22px;
        min-width: 22px;
        max-width: 22px;
    }
}

@media (max-width: 768px) {
    .task-list-container {
        width: 40%;
        min-width: 40%;
        max-width: 40%;
    }
    
    .gantt-view-container {
        width: 60%;
        min-width: 60%;
        max-width: 60%;
    }
    
    .task-list-header-section {
        width: 40%;
        min-width: 40%;
        max-width: 40%;
    }
    
    .timeline-header-section {
        width: 60%;
        min-width: 60%;
        max-width: 60%;
    }
    
    .timeline-day {
        width: 20px;
        min-width: 20px;
        max-width: 20px;
    }
    
    .gantt-grid-cell {
        width: 20px;
        min-width: 20px;
        max-width: 20px;
    }
    
    .gantt-container {
        min-height: calc(100vh - 100px);
    }
    
    .task-list-body {
        max-height: calc(100vh - 180px);
    }
    
    .gantt-content-container {
        max-height: calc(100vh - 180px);
    }
}

/* Additional constraints for very small screens */
@media (max-width: 480px) {
    .gantt-container {
        min-height: calc(100vh - 80px);
    }
    
    .timeline-day {
        width: 18px;
        min-width: 18px;
        max-width: 18px;
        font-size: 9px;
    }
    
    .gantt-grid-cell {
        width: 18px;
        min-width: 18px;
        max-width: 18px;
    }
    
    .gantt-bar {
        font-size: 9px;
        padding: 0 4px;
        min-width: 16px;
    }
    
    .task-list-body {
        max-height: calc(100vh - 160px);
    }
    
    .gantt-content-container {
        max-height: calc(100vh - 160px);
    }
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
}

.toolbar-button {
    padding: 6px 12px;
    border: 1px solid #d1d5db;
    border-radius: 4px;
    background: white;
    color: #374151;
    font-size: 12px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.1s ease;
}

.toolbar-button:hover {
    background: #f3f4f6;
    border-color: #9ca3af;
}

.toolbar-button.primary {
    background: #0078d4;
    color: white;
    border-color: #106ebe;
}

.toolbar-button.primary:hover {
    background: #106ebe;
}

/* Modal Styles */
.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
}

.modal.show {
    display: flex;
    align-items: center;
    justify-content: center;
}

.modal-content {
    background: white;
    border-radius: 4px;
    width: 90%;
    max-width: 500px;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.2);
    overflow: hidden;
}

.modal-header {
    background: #f8f9fa;
    padding: 12px 16px;
    border-bottom: 1px solid #d1d5db;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-body {
    padding: 16px;
}

.modal-footer {
    background: #f8f9fa;
    padding: 12px 16px;
    border-top: 1px solid #d1d5db;
    display: flex;
    justify-content: flex-end;
    gap: 8px;
}

/* Controls Container */
.controls-container {
    position: absolute;
    top: 0;
    right: 0;
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 4px 8px;
    background: rgba(255, 255, 255, 0.9);
    border-radius: 4px;
    margin: 4px;
    z-index: 30;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.control-button {
    padding: 4px 8px;
    border: 1px solid #d1d5db;
    border-radius: 3px;
    background: white;
    color: #374151;
    font-size: 11px;
    cursor: pointer;
    transition: all 0.1s ease;
    display: flex;
    align-items: center;
    gap: 4px;
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
    gap: 2px;
}

.zoom-button {
    padding: 2px 4px;
    border: 1px solid #d1d5db;
    background: white;
    color: #374151;
    cursor: pointer;
    border-radius: 2px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.zoom-button:hover {
    background: #f3f4f6;
}

.zoom-level {
    font-size: 11px;
    font-weight: 500;
    color: #374151;
    min-width: 36px;
    text-align: center;
}

/* Additional styles for truncated gantt bars */
.gantt-bar.truncated {
    position: relative;
}

.gantt-bar.truncated::after {
    content: "...";
    position: absolute;
    right: 2px;
    top: 50%;
    transform: translateY(-50%);
    color: rgba(255, 255, 255, 0.8);
    font-weight: bold;
    font-size: 8px;
    pointer-events: none;
}

/* Improved tooltip for truncated bars */
.gantt-bar.truncated:hover::before {
    content: attr(title);
    position: absolute;
    bottom: 100%;
    left: 50%;
    transform: translateX(-50%);
    background: rgba(0, 0, 0, 0.8);
    color: white;
    padding: 4px 8px;
    border-radius: 3px;
    font-size: 11px;
    white-space: nowrap;
    z-index: 1000;
    margin-bottom: 5px;
}

.gantt-bar.truncated:hover::after {
    content: "";
    position: absolute;
    bottom: 100%;
    left: 50%;
    transform: translateX(-50%);
    border: 4px solid transparent;
    border-top-color: rgba(0, 0, 0, 0.8);
    z-index: 1000;
    margin-bottom: 1px;
}

/* Ensure proper overflow handling */
.gantt-row {
    position: relative;
    overflow: hidden; /* Hide overflowing content */
}

/* Today indicator line */
.today-indicator {
    position: absolute;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #f59e0b;
    z-index: 15;
    pointer-events: none;
}

/* Weekend background for gantt rows */
.gantt-grid-cell.weekend {
    background: linear-gradient(
        to bottom,
        rgba(0, 0, 0, 0.02) 0%,
        rgba(0, 0, 0, 0.03) 100%
    );
}

/* Hover effects for better UX */
.gantt-row:hover .gantt-grid-cell {
    background-color: rgba(59, 130, 246, 0.05);
}

.gantt-row:hover .gantt-grid-cell.weekend {
    background: linear-gradient(
        to bottom,
        rgba(59, 130, 246, 0.05) 0%,
        rgba(59, 130, 246, 0.08) 100%
    );
}

/* Loading state for gantt bars */
.gantt-bar.loading {
    background: #e5e7eb !important;
    animation: pulse 1.5s ease-in-out infinite;
}

@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.7; }
}

/* Responsive adjustments for smaller screens */
@media (max-width: 768px) {
    .gantt-bar {
        font-size: 9px;
        padding: 0 3px;
    }
    
    .gantt-bar.truncated::after {
        font-size: 7px;
        right: 1px;
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
    
    .gantt-bar {
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
}

/* Focus styles for accessibility */
.gantt-bar:focus {
    outline: 2px solid #2563eb;
    outline-offset: 1px;
}

.toggle-collapse:focus {
    outline: 2px solid #2563eb;
    outline-offset: 1px;
    border-radius: 2px;
}

/* High contrast mode support */
@media (prefers-contrast: high) {
    .gantt-bar {
        border-width: 2px;
        border-style: solid;
    }
    
    .gantt-grid-cell {
        border-width: 1px;
        border-color: #000;
    }
    
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
</style>

<div class="gantt-container">
    <!-- Toolbar -->
    <div class="toolbar">
        <div class="flex items-center space-x-3">
            <h5 class="text-sm font-semibold text-gray-800 flex items-center">
                <svg class="w-4 h-4 mr-2 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"></path>
                </svg>
                Gantt Chart - Project Schedule
            </h5>
        </div>
        
        <div class="flex items-center space-x-3 bg-white px-3 py-2 rounded-lg shadow-sm border border-gray-200">
        
         <div class="zoom-controls">
                    <button class="zoom-button" onclick="zoomOut()">
                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M3 10a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"></path>
                        </svg>
                    </button>
                    
                    <span class="zoom-level" id="zoomLevel">100%</span>
                    
                    <button class="zoom-button" onclick="zoomIn()">
                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"></path>
                        </svg>
                    </button>
                </div>

        <!-- Expand All -->
            <button class="flex items-center px-2 py-1 text-sm text-gray-700 hover:bg-gray-100 rounded-md transition" onclick="expandAll()">
                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"></path>
                </svg>
                Expand
            </button>

            <!-- Collapse All -->
            <button class="flex items-center px-2 py-1 text-sm text-gray-700 hover:bg-gray-100 rounded-md transition" onclick="collapseAll()">
                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"></path>
                </svg>
                Collapse
            </button>
<!-- add task -->
             <a href="{{ route('tasks.create') }}" class="control-button primary">
                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"></path>
                    </svg>
                    Add Task
                </a>
        </div>
    </div>

    <!-- Combined Header -->
    <div class="combined-header-container">
        <!-- Task List Header Section -->
        <div class="task-list-header-section">
            <div class="task-header-row">
                <div class="task-header-cell"></div>
                <div class="task-header-cell" style="text-align: left;">Task Name</div>
                <div class="task-header-cell">Duration</div>
                <div class="task-header-cell">Start</div>
                <div class="task-header-cell">Finish</div>
            </div>
        </div>
        
        <!-- Timeline Header Section -->
        <div class="timeline-header-section" id="timelineHeaderSection">
            @if(isset($tasks) && $tasks->count() > 0)
                @php
                    $minDate = \Carbon\Carbon::now()->startOfMonth();
                    $maxDate = \Carbon\Carbon::now()->addMonths(6)->endOfMonth();
                    $totalDays = $minDate->diffInDays($maxDate) + 1;
                    
                    // Group days by month
                    $monthGroups = [];
                    $currentDate = $minDate->copy();
                    while ($currentDate <= $maxDate) {
                        $monthKey = $currentDate->format('Y-m');
                        if (!isset($monthGroups[$monthKey])) {
                            $monthGroups[$monthKey] = [
                                'name' => $currentDate->format('M Y'),
                                'days' => []
                            ];
                        }
                        $monthGroups[$monthKey]['days'][] = $currentDate->copy();
                        $currentDate->addDay();
                    }
                @endphp
                
                <!-- Month Headers -->
                <div class="month-header">
                    @foreach($monthGroups as $monthData)
                        <div class="month-section" style="width: {{ count($monthData['days']) * 24 }}px;">
                            {{ $monthData['name'] }}
                        </div>
                    @endforeach
                </div>
                
                <!-- Day Headers -->
                <div class="day-header">
                    @foreach($monthGroups as $monthData)
                        @foreach($monthData['days'] as $date)
                            @php
                                $dayOfWeek = $date->dayOfWeek;
                                $isWeekend = in_array($dayOfWeek, [0, 6]);
                                $isToday = $date->isToday();
                            @endphp
                            <div class="timeline-day {{ $isWeekend ? 'weekend' : '' }} {{ $isToday ? 'today' : '' }}">
                                {{ $date->format('d') }}
                            </div>
                        @endforeach
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <!-- Main Content -->
    <div class="gantt-main-content">
        <!-- Task List (50% width) -->
        <div class="task-list-container">
            <!-- Task List Body -->
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
                        <!-- Gantt Content -->
            <div class="gantt-content-container" id="ganttContent">
                @if(isset($tasks) && $tasks->count() > 0)
                    <div class="gantt-rows-container">
                        @foreach($tasks as $task)
                            @include('partials.gantt-item', [
                                'task' => $task, 
                                'level' => 0, 
                                'minDate' => $minDate, 
                                'totalDays' => $totalDays
                            ])
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
// Synchronize scrolling between task list and gantt chart
document.addEventListener('DOMContentLoaded', function() {
    const taskListBody = document.getElementById('taskListBody');
    const ganttContent = document.getElementById('ganttContent');
    const timelineHeaderSection = document.getElementById('timelineHeaderSection');
    
    // Vertical scroll synchronization
    if (taskListBody && ganttContent) {
        taskListBody.addEventListener('scroll', function() {
            ganttContent.scrollTop = this.scrollTop;
        });
        
        ganttContent.addEventListener('scroll', function() {
            taskListBody.scrollTop = this.scrollTop;
            if (timelineHeaderSection) {
                timelineHeaderSection.scrollLeft = this.scrollLeft;
            }
        });
    }
    
    // Horizontal scroll synchronization for timeline
    if (timelineHeaderSection && ganttContent) {
        timelineHeaderSection.addEventListener('scroll', function() {
            ganttContent.scrollLeft = this.scrollLeft;
        });        
        ganttContent.addEventListener('scroll', function() {
            timelineHeaderSection.scrollLeft = this.scrollLeft;
        });
    }
});

// Task collapse/expand functionality
function toggleTaskCollapse(taskId) {
    const toggleIcon = document.querySelector(`[data-task-id="${taskId}"].toggle-collapse`);
    const childrenContainer = document.querySelector(`.task-children[data-parent-id="${taskId}"]`);
    const ganttChildrenContainer = document.querySelector(`.gantt-children[data-parent-id="${taskId}"]`);

    if (toggleIcon && childrenContainer) {
        toggleIcon.classList.toggle('rotate-90');
        
        if (childrenContainer.style.display === 'none') {
            childrenContainer.style.display = 'block';
            if (ganttChildrenContainer) ganttChildrenContainer.style.display = 'block';
        } else {
            childrenContainer.style.display = 'none';
            if (ganttChildrenContainer) ganttChildrenContainer.style.display = 'none';
        }
    }
}

// Add event listeners for toggle buttons
document.addEventListener('click', function(e) {
    if (e.target.closest('.toggle-collapse')) {
        const taskId = e.target.closest('.toggle-collapse').getAttribute('data-task-id');
        toggleTaskCollapse(taskId);
    }
});

// Expand/Collapse all functions
function expandAll() {
    document.querySelectorAll('.task-children').forEach(container => {
        container.style.display = 'block';
    });
    document.querySelectorAll('.gantt-children').forEach(container => {
        container.style.display = 'block';
    });
    document.querySelectorAll('.toggle-collapse').forEach(icon => {
        icon.classList.add('rotate-90');
    });
}

function collapseAll() {
    document.querySelectorAll('.task-children').forEach(container => {
        container.style.display = 'none';
    });
    document.querySelectorAll('.gantt-children').forEach(container => {
        container.style.display = 'none';
    });
    document.querySelectorAll('.toggle-collapse').forEach(icon => {
        icon.classList.remove('rotate-90');
    });
}

// Zoom functions
let currentZoom = 100;
const minZoom = 50;
const maxZoom = 200;
const zoomStep = 25;

function updateZoomLevel() {
    const zoomLevelElement = document.getElementById('zoomLevel');
    if (zoomLevelElement) {
        zoomLevelElement.textContent = currentZoom + '%';
    }
    
    // Calculate new day width based on zoom level
    const baseWidth = 24; // Base width in pixels
    const newWidth = Math.round(baseWidth * (currentZoom / 100));
    
    // Update timeline day widths
    const timelineDays = document.querySelectorAll('.timeline-day');
    const ganttGridCells = document.querySelectorAll('.gantt-grid-cell');
    
    timelineDays.forEach(day => {
        day.style.width = newWidth + 'px';
        day.style.minWidth = newWidth + 'px';
    });
    
    ganttGridCells.forEach(cell => {
        cell.style.width = newWidth + 'px';
        cell.style.minWidth = newWidth + 'px';
    });
    
    // Update month section widths
    const monthSections = document.querySelectorAll('.month-section');
    monthSections.forEach(section => {
        const dayCount = parseInt(section.style.width) / baseWidth;
        section.style.width = (dayCount * newWidth) + 'px';
    });
    
    // Recalculate gantt bar positions
    updateGanttBarPositions(newWidth);
}

function updateGanttBarPositions(dayWidth) {
    const ganttBars = document.querySelectorAll('.gantt-bar');
    ganttBars.forEach(bar => {
        const startDay = parseInt(bar.getAttribute('data-start-day') || '0');
        const duration = parseInt(bar.getAttribute('data-duration') || '1');
        
        bar.style.left = (startDay * dayWidth) + 'px';
        bar.style.width = (duration * dayWidth - 2) + 'px'; // -2 for border
    });
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

// Close modal when clicking outside
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('modal')) {
        closeTaskModal();
    }
});

// Keyboard shortcuts
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeTaskModal();
    }
    
    if (e.ctrlKey || e.metaKey) {
        if (e.key === '=' || e.key === '+') {
            e.preventDefault();
            zoomIn();
        } else if (e.key === '-') {
            e.preventDefault();
            zoomOut();
        }
    }
});
</script>

@endsection