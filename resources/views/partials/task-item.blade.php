@php
    $isParent = $task->children && $task->children->count() > 0;
    $indent = $level * 20; // Slightly increased for better hierarchy visibility
@endphp

<!-- Task Row -->
<div class="task-row group hover:bg-gray-50 transition-colors duration-200"
     data-task-id="{{ $task->id }}"
     data-parent-id="{{ $task->parent_id ?? '' }}"
     data-level="{{ $level }}"
     style="border-left: {{ $level > 0 ? '2px solid #e5e7eb' : 'none' }};">

    <div class="task-cell flex items-center justify-center w-8">
        @if($isParent)
            <button class="toggle-collapse p-0.5 rounded hover:bg-gray-200 transition-colors duration-150"
                    data-task-id="{{ $task->id }}"
                    aria-label="Toggle subtasks">
                <svg class="w-3.5 h-3.5 text-gray-600 transform transition-transform duration-200"
                     fill="none"
                     stroke="currentColor"
                     viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </button>
        @else
            <!-- Kosongkan kolom ini, hanya biarkan ruang kosong -->
        @endif
    </div>

    <!-- Task Name Column -->
    <div class="task-name-cell flex-1 py-3 px-2"
         data-task-id="{{ $task->id }}"
         style="padding-left: {{ $level > 0 ? ($indent - 12) : $indent + 8 }}px;"> <!-- Mengurangi padding untuk subtask -->
        
        <div class="flex items-center {{ $level > 0 ? 'space-x-0.5' : 'space-x-1' }}"> <!-- Jarak lebih kecil untuk subtask -->
            @if($level > 0)
                <!-- Subtask Icon -->
                <div class="flex-shrink-0">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
            @else
                <!-- Parent Task Icon -->
                <div class="flex-shrink-0">
                    <div class="w-4 h-4 bg-gradient-to-br from-blue-500 to-blue-600 rounded flex items-center justify-center">
                        <svg class="w-2.5 h-2.5 text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"></path>
                        </svg>
                    </div>
                </div>
            @endif
               
            <span class="text-sm font-medium text-gray-900 group-hover:text-gray-700 transition-colors duration-200
                        @if($level === 0) text-base font-semibold @endif">
                {{ $task->name }}
            </span>
        </div>
    </div>

    <!-- Duration Column -->
    <div class="task-cell w-20 text-center">
        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium duration-badge"
              data-level="{{ $level }}"
              @if($level === 0) class="bg-blue-500 text-white"
              @elseif($level === 1) class="bg-green-500 text-white"
              @elseif($level === 2) class="bg-purple-500 text-white"
              @elseif($level === 3) class="bg-orange-500 text-white"
              @elseif($level === 4) class="bg-red-500 text-white"
              @else class="bg-gray-500 text-white"
              @endif>
            {{ $task->duration ?? 0 }}d
        </span>
    </div>

    <!-- Start Date Column -->
    <div class="task-cell w-24 text-center">
        <span class="text-sm text-gray-600 font-mono">
            {{ $task->start ? \Carbon\Carbon::parse($task->start)->format('j-n-y') : '-' }}
        </span>
    </div>

    <!-- Finish Date Column -->
    <div class="task-cell w-24 text-center">
        <span class="text-sm text-gray-600 font-mono">
            {{ $task->finish ? \Carbon\Carbon::parse($task->finish)->format('j-n-y') : '-' }}
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