@extends('layouts.app')

@section('content')
<style>
/* Microsoft Project Style Gantt Chart - Enhanced Version */
.gantt-container {
    display: flex;
    flex-direction: column;
    min-height: calc(100vh - 120px);
    overflow: hidden;
    background: #ffffff;
    border: 1px solid #d1d5db;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    max-width: 100vw;
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
    overflow-y: auto;
    overflow-x: hidden;
    min-height: 0;
    background: white;
    max-height: calc(100vh - 250px);
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

/* Gantt Content Container */
.gantt-content-container {
    flex: 1;
    overflow: auto;
    position: relative;
    background: #ffffff;
    max-height: calc(100vh - 250px);
    max-width: 100%;
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
    border: 1px solid rgba(0,0,0,0.1);
    box-sizing: border-box;
}

.gantt-bar:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
    z-index: 10;
}

/* Task Colors by Level */
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
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
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
    0%, 100% { opacity: 1; }
    50% { opacity: 0.7; }
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
                <div class="task-header-cell"></div>
                <div class="task-header-cell" style="text-align: left;">Task Name</div>
                <div class="task-header-cell">Duration</div>
                <div class="task-header-cell">Start</div>
                <div class="task-header-cell">Finish</div>
            </div>
        </div>
        
        <!-- Timeline Header Section -->
        <div class="timeline-header-section" id="timelineHeaderSection">
            <div id="monthHeaderContainer">
                <!-- Month headers will be generated by JavaScript -->
            </div>
            <div id="dayHeaderContainer">
                <!-- Day headers will be generated by JavaScript -->
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
                        @include('partials.gantt-task-item', ['task' => $task, 'level' => 0])
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
@if(isset($tasks) && $tasks->count() > 0)
    @php
    // Helper untuk mengubah objek Carbon menjadi string tanggal Y-m-d
    function formatDate($date) {
        if ($date instanceof \Carbon\Carbon) {
            return $date->format('Y-m-d');
        }
        return $date; // Kembalikan apa adanya jika bukan objek Carbon
    }

    $mappedTasks = $tasks->map(function($task) {
        // Pastikan start dan finish adalah objek Carbon sebelum memformat
        $startDate = $task->start ? \Carbon\Carbon::parse($task->start) : null;
        $endDate = $task->finish ? \Carbon\Carbon::parse($task->finish) : null;

        return [
            'id' => $task->id,
            'name' => $task->name ?? $task->title,
            // Gunakan nama kolom yang benar: 'start' dan 'finish'
            'startDate' => $startDate ? $startDate->format('Y-m-d') : null,
            'endDate' => $endDate ? $endDate->format('Y-m-d') : null,
            'duration' => $task->duration,
            'level' => $task->level ?? 0,
            'status' => $task->status ?? 'pending',
            'progress' => $task->progress ?? 0,
            'children' => [] // Anda bisa mengisi ini jika ada relasi
        ];
    });
@endphp
    tasksData = @json($mappedTasks);
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
            monthYear: currentDay.toLocaleDateString('en-US', { month: 'short', year: 'numeric' })
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
                name: day.date.toLocaleDateString('en-US', { month: 'short', year: 'numeric' }),
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

// Update Gantt chart bars
function updateGanttChart() {
    const ganttRowsContainer = document.getElementById('ganttRowsContainer');
    if (!ganttRowsContainer) return;
    
    let ganttHTML = '';
    
    if (tasksData.length > 0) {
        tasksData.forEach(task => {
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
    if (!task.startDate || !task.endDate) return null;
    
    const taskStart = new Date(task.startDate);
    const taskEnd = new Date(task.endDate);
    
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
    
    const levelClass = `level-${task.level % 6}`;
    const progressWidth = task.progress ? (barWidth * task.progress / 100) : 0;
    
    let taskBarHTML = `
        <div class="gantt-bar ${levelClass}" 
             style="left: ${barLeft}px; width: ${barWidth}px;"
             data-task-id="${task.id}"
             data-start-day="${startDayOffset}"
             data-duration="${task.duration}"
             title="${task.name} (${task.duration} days) - ${task.progress}% complete">
    `;
    
    // Add progress indicator if exists
    if (task.progress > 0) {
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
        // You can customize this to show task details, edit form, etc.
        console.log('Task clicked:', task);
        
        // Example: Show task details in a modal or redirect to edit page
        // window.location.href = `/tasks/${taskId}/edit`;
        
        // Or trigger a custom event
        document.dispatchEvent(new CustomEvent('taskSelected', {
            detail: { task: task }
        }));
    }
}

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
    
    if (e.key === 'Escape') {
        // Close any open modals or deselect items
        document.querySelectorAll('.gantt-bar.selected').forEach(bar => {
            bar.classList.remove('selected');
        });
    }
});

// Utility functions
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { 
        month: '2-digit', 
        day: '2-digit', 
        year: '2-digit' 
    });
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
        return tasksData.filter(task => {
            if (!task.startDate || !task.endDate) return false;
            const taskStart = new Date(task.startDate);
            const taskEnd = new Date(task.endDate);
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
    window.GanttChart.updateTask(updatedTask.id, updatedTask);
});

document.addEventListener('taskDeleted', function(e) {
    const taskId = e.detail.taskId;
    window.GanttChart.removeTask(taskId);
});
</script>

@endsection