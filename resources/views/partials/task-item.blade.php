@php
    $level = $level ?? 0;
    $isParent = $task->children && $task->children->count() > 0;
    $paddingLeft = $level > 0 ? 32 : 8;
@endphp

<!-- Task Row -->
<div class="task-row group transition-colors duration-200 {{ $level > 0 ? 'task-child' : '' }}"
     data-task-id="{{ $task->id }}"
     data-parent-id="{{ $task->parent_id ?? '' }}"
     data-level="{{ $level }}">

    <!-- Toggle Column (40px) -->
    <div class="task-cell task-toggle-cell">
        @if($isParent)
            <button class="toggle-collapse rotate-90"
                    data-task-id="{{ $task->id }}"
                    aria-label="Toggle subtasks">
                <svg class="w-3 h-3 text-gray-700 transform transition-transform duration-200"
                     fill="none"
                     stroke="currentColor"
                     viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path>
                </svg>
            </button>
        @endif
    </div>

    <!-- Task Name Column (dengan icon dan indentasi) -->
    <div class="task-cell task-name-cell cursor-pointer"
         data-task-id="{{ $task->id }}"
         style="padding-left: {{ $paddingLeft }}px;">
        <div class="flex items-center gap-2">
            @if($level > 0)
                <!-- Child Task: Square Green Icon -->
                <div class="task-icon-square task-icon-green"></div>
            @else
                <!-- Parent Task: Square Blue Icon -->
                <div class="task-icon-square task-icon-blue"></div>
            @endif
            <span class="task-name-text {{ $level === 0 ? 'font-semibold' : 'font-medium' }}">
                {{ $task->name }}
            </span>
        </div>
    </div>

    <!-- Start Date Column -->
    <div class="task-cell task-date-cell">
        <span class="task-date-text">
            {{ $task->start ? \Carbon\Carbon::parse($task->start)->format('M j, Y') : '-' }}
        </span>
    </div>

    <!-- End Date Column -->
    <div class="task-cell task-date-cell">
        <span class="task-date-text">
            {{ $task->finish ? \Carbon\Carbon::parse($task->finish)->format('M j, Y') : '-' }}
        </span>
    </div>

    <!-- Duration Column -->
    <div class="task-cell task-duration-cell">
        <span class="duration-badge-modern"
              data-task-id="{{ $task->id }}"
              data-parent-id="{{ $task->parent_id ?? '' }}"
              data-level="{{ $level }}"
              id="duration-{{ $task->id }}">
            {{ $task->duration ?? 0 }}d
        </span>
    </div>
</div>

<!-- Recursively render children -->
@if($isParent)
    <div class="task-children transition-all duration-300 ease-in-out"
         data-parent-id="{{ $task->id }}">
        @foreach($task->children as $child)
            @include('partials.task-item', ['task' => $child, 'level' => $level + 1])
        @endforeach
    </div>
@endif