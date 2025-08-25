@php
    $isParent = $task->children && $task->children->count() > 0;
    $indent = $level * 16; // Reduced indent for Microsoft Project style
@endphp

<!-- Task Row -->
<div class="task-row" 
     data-task-id="{{ $task->id }}" 
     data-parent-id="{{ $task->parent_id ?? '' }}"
     data-level="{{ $level }}">
    
    <!-- Toggle/Indicator Column -->
    <div class="task-cell">
        @if($isParent)
            <svg class="toggle-collapse" 
                 fill="currentColor" viewBox="0 0 20 20"
                 data-task-id="{{ $task->id }}"
                 style="width: 12px; height: 12px;">
                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path>
            </svg>
        @else
            <div class="task-indicator indicator-level-{{ min($level, 5) }}"></div>
        @endif
    </div>
    
    <!-- Task Name Column -->
    <div class="task-name-cell" style="padding-left: {{ $indent }}px;">
        <div class="flex items-center">
            @if($level > 0)
                <svg style="width: 12px; height: 12px; margin-right: 4px; color: #6b7280;" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"></path>
                </svg>
            @else
                <svg style="width: 12px; height: 12px; margin-right: 4px; color: #f59e0b;" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"></path>
                </svg>
            @endif
            <span style="font-weight: {{ $level > 0 ? '400' : '500' }};">{{ $task->name }}</span>
        </div>
    </div>
    
    <!-- Duration Column -->
    <div class="task-cell">
        <span class="duration-badge">{{ $task->duration ?? 0 }}d</span>
    </div>
    
    <!-- Start Date Column -->
<div class="task-cell">
    {{ $task->start ? \Carbon\Carbon::parse($task->start)->format('j-n-y') : '-' }}
</div>

<!-- Finish Date Column -->
<div class="task-cell">
    {{ $task->finish ? \Carbon\Carbon::parse($task->finish)->format('j-n-y') : '-' }}
</div>
</div>

<!-- Recursively render children -->
@if($isParent)
    <div class="task-children" data-parent-id="{{ $task->id }}">
        @foreach($task->children as $child)
            @include('partials.task-item', ['task' => $child, 'level' => $level + 1])
        @endforeach
    </div>
@endif

