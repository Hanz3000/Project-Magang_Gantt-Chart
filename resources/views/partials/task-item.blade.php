@php
    $childrenCount = isset($allTasks) ? $allTasks->where('parent_id', $task->id)->count() : 0;
    $isParent = $childrenCount > 0;
    $paddingLeft = ($task->level ?? 0) > 0 ? 32 : 8;
    
    // Z-Index tetap rendah untuk semua task row agar tidak menutupi popup/modal
    // Popup/modal harus punya z-index lebih tinggi (misal 1000+ di CSS)
    $zIndex = 1; 
@endphp

<div class="task-row group transition-colors duration-200 {{ ($task->level ?? 0) > 0 ? 'task-child' : '' }}"
     data-task-id="{{ $task->id }}"
     data-parent-id="{{ $task->parent_id ?? '' }}"
     data-level="{{ $task->level ?? 0 }}"
     style="position: relative; z-index: {{ $zIndex }};"> 
    <div class="task-cell task-toggle-cell">
        @if($isParent)
            <button class="toggle-collapse rotate-90" data-task-id="{{ $task->id }}">
                <svg class="w-3 h-3 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path></svg>
            </button>
        @endif
    </div>

    <div class="task-cell task-name-cell cursor-pointer" style="padding-left: {{ $paddingLeft }}px;" data-task-id="{{ $task->id }}">
        <div class="flex items-center gap-2">
            <div class="task-icon-square"></div>
            <span class="task-name-text {{ $isParent ? 'font-bold text-gray-900' : 'font-medium' }}">
                {{ $task->name }}
            </span>
        </div>
    </div>

    <div class="task-cell task-date-cell">
        <span class="task-date-text">{{ $task->start ? \Carbon\Carbon::parse($task->start)->format('M j, Y') : '-' }}</span>
    </div>

    <div class="task-cell task-date-cell">
        <span class="task-date-text">{{ $task->finish ? \Carbon\Carbon::parse($task->finish)->format('M j, Y') : '-' }}</span>
    </div>

    <div class="task-cell task-duration-cell">
        <span class="duration-badge-modern">{{ $task->duration ?? 0 }}d</span>
    </div>

    <div class="task-progress-cell" style="position: relative;">
        <input type="range" 
       class="task-progress-slider {{ ($task->level ?? 0) == 0 ? 'top-level-slider' : 'sub-level-slider' }}"
       data-task-id="{{ $task->id }}" 
       min="0" max="100" 
       value="{{ $task->progress ?? 0 }}"
       
       style="--progress-value: {{ $task->progress ?? 0 }}%;"
       
       {{-- Tidak ada lagi disabled --}}
       
       {{-- Semua slider: update visual saat drag --}}
       oninput="updateProgressUI(this)"
       
       title="Geser untuk update progres">
               
        <span class="task-progress-label" id="progress-label-{{ $task->id }}">
            {{ $task->progress ?? 0 }}%
        </span>
    </div>
</div>

@if($isParent && isset($allTasks))
    <div class="task-children transition-all duration-300" data-parent-id="{{ $task->id }}" style="position: relative; z-index: {{ $zIndex }};">
        @foreach($allTasks->where('parent_id', $task->id)->sortBy('order') as $child)
            @include('partials.task-item', ['task' => $child, 'allTasks' => $allTasks])
        @endforeach
    </div>
@endif