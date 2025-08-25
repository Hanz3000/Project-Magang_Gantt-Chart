{{-- resources/views/partials/gantt-task-item.blade.php --}}
@php
    $hasChildren = isset($task->children) && $task->children->count() > 0;
    $taskLevel = $level ?? 0;
    $maxLevel = 5;
    $indicatorClass = 'indicator-level-' . ($taskLevel % 6);
    $startDate = $task->start_date ? $task->start_date->format('m/d/y') : '';
    $endDate = $task->end_date ? $task->end_date->format('m/d/y') : '';
    $duration = $task->duration ?? ($task->start_date && $task->end_date ? $task->start_date->diffInDays($task->end_date) + 1 : 0);
    $taskName = $task->name ?? $task->title ?? 'Untitled Task';
@endphp

<div class="task-row" data-task-id="{{ $task->id }}" data-level="{{ $taskLevel }}">
    <div class="task-cell">
        @if($hasChildren)
            <div class="toggle-collapse rotate-90" data-task-id="{{ $task->id }}" title="Click to collapse/expand">
                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                </svg>
            </div>
        @else
            <div style="width: 16px;"></div>
        @endif
        <div class="task-indicator {{ $indicatorClass }}"></div>
    </div>
    
    <div class="task-name-cell" style="padding-left: {{ ($taskLevel * 16) + 4 }}px;" title="{{ $taskName }}">
        {{ $taskName }}
    </div>
    
    <div class="task-cell">
        @if($duration > 0)
            <span class="duration-badge">{{ $duration }}d</span>
        @else
            <span class="duration-badge">-</span>
        @endif
    </div>
    
    <div class="task-cell" title="{{ $task->start_date ? $task->start_date->format('F d, Y') : 'No start date' }}">
        {{ $startDate ?: '-' }}
    </div>
    
    <div class="task-cell" title="{{ $task->end_date ? $task->end_date->format('F d, Y') : 'No end date' }}">
        {{ $endDate ?: '-' }}
    </div>
</div>

@if($hasChildren)
    <div class="task-children" data-parent-id="{{ $task->id }}" style="display: block;">
        @foreach($task->children as $childTask)
            @include('partials.gantt-task-item', [
                'task' => $childTask, 
                'level' => $taskLevel + 1
            ])
        @endforeach
    </div>
@endif