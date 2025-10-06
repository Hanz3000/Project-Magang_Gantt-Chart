@php
    // Tidak lagi perlu cek children dari relasi Eloquent
    // Karena structuredTasks sudah flat dari controller
    $isParent = isset($allTasks) && $allTasks->where('parent_id', $task->id)->count() > 0;
    $paddingLeft = ($task->level ?? 0) > 0 ? 32 : 8;
@endphp

<!-- Task Row -->
<div class="task-row group transition-colors duration-200 {{ ($task->level ?? 0) > 0 ? 'task-child' : '' }}"
     data-task-id="{{ $task->id }}"
     data-parent-id="{{ $task->parent_id ?? '' }}"
     data-level="{{ $task->level ?? 0 }}">

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
            @if(($task->level ?? 0) > 0)
                <!-- Child Task: Square Icon (warna akan diset oleh JavaScript) -->
                <div class="task-icon-square"></div>
            @else
                <!-- Parent Task: Square Icon (warna akan diset oleh JavaScript) -->
                <div class="task-icon-square"></div>
            @endif
            <span class="task-name-text {{ ($task->level ?? 0) === 0 ? 'font-semibold' : 'font-medium' }}">
                {{ is_array($task) ? $task['name'] : $task->name }}
            </span>
        </div>
    </div>

    <!-- Start Date Column -->
    <div class="task-cell task-date-cell">
        <span class="task-date-text">
            @if(is_array($task))
                {{ isset($task['start']) ? \Carbon\Carbon::parse($task['start'])->format('M j, Y') : '-' }}
            @else
                {{ $task->start ? \Carbon\Carbon::parse($task->start)->format('M j, Y') : '-' }}
            @endif
        </span>
    </div>

    <!-- End Date Column -->
    <div class="task-cell task-date-cell">
        <span class="task-date-text">
            @if(is_array($task))
                {{ isset($task['finish']) ? \Carbon\Carbon::parse($task['finish'])->format('M j, Y') : '-' }}
            @else
                {{ $task->finish ? \Carbon\Carbon::parse($task->finish)->format('M j, Y') : '-' }}
            @endif
        </span>
    </div>

    <!-- Duration Column -->
    <div class="task-cell task-duration-cell">
        <span class="duration-badge-modern"
              data-task-id="{{ is_array($task) ? $task['id'] : $task->id }}"
              data-parent-id="{{ is_array($task) ? ($task['parent_id'] ?? '') : ($task->parent_id ?? '') }}"
              data-level="{{ is_array($task) ? ($task['level'] ?? 0) : ($task->level ?? 0) }}"
              id="duration-{{ is_array($task) ? $task['id'] : $task->id }}">
            {{ is_array($task) ? ($task['duration'] ?? 0) : ($task->duration ?? 0) }}d
        </span>
    </div>
</div>

<!-- Recursively render children jika ada -->
@if($isParent && isset($allTasks))
    <div class="task-children transition-all duration-300 ease-in-out"
         data-parent-id="{{ is_array($task) ? $task['id'] : $task->id }}">
        @foreach($allTasks->where('parent_id', is_array($task) ? $task['id'] : $task->id) as $child)
            @include('partials.task-item', ['task' => $child, 'allTasks' => $allTasks])
        @endforeach
    </div>
@endif