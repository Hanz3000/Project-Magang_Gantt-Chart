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
    
    <!-- Toggle/Indicator Column -->
    <div class="task-cell flex items-center justify-center w-8">
        @if($isParent)
            <button class="toggle-collapse p-1 rounded hover:bg-gray-200 transition-colors duration-150" 
                    data-task-id="{{ $task->id }}"
                    aria-label="Toggle subtasks">
                <svg class="w-4 h-4 text-gray-600 transform transition-transform duration-200" 
                     fill="none" 
                     stroke="currentColor" 
                     viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </button>
        @else
            <div class="w-2 h-2 rounded-full bg-gradient-to-r 
                        @if($level === 1) from-blue-400 to-blue-500
                        @elseif($level === 2) from-green-400 to-green-500
                        @elseif($level === 3) from-purple-400 to-purple-500
                        @elseif($level === 4) from-orange-400 to-orange-500
                        @else from-gray-400 to-gray-500
                        @endif"></div>
        @endif
    </div>

    <!-- Task Name Column -->
    <div class="task-name-cell flex-1 py-3 px-2" 
         data-task-id="{{ $task->id }}" 
         style="padding-left: {{ $indent + 8 }}px;">
        <div class="flex items-center space-x-3">
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
        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium
                     @if($task->duration > 10) bg-red-100 text-red-800
                     @elseif($task->duration > 5) bg-yellow-100 text-yellow-800
                     @else bg-green-100 text-green-800
                     @endif">
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