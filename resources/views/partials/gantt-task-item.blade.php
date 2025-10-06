<div class="task-row {{ isset($isChild) && $isChild ? 'task-child' : '' }}" data-task-id="{{ $task->id }}">
    <!-- Toggle Cell -->
    <div class="task-toggle-cell">
        @if($task->children && $task->children->count() > 0)
        <button class="toggle-collapse" data-task-id="{{ $task->id }}" aria-label="Toggle children">
            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
            </svg>
        </button>
        @endif
    </div>

    <!-- Name Cell with indentation -->
    <div class="task-name-cell" data-task-id="{{ $task->id }}">
        <div style="display: flex; align-items: center; padding-left: {{ isset($level) ? ($level * 16) . 'px' : '0' }};">
            <span class="task-icon-square indicator-level-{{ ($task->level ?? $level ?? 0) % 6 }}"></span>
            <span class="task-name-text">{{ $task->name }}</span>
        </div>
    </div>

    <!-- Start Date Cell -->
    <div class="task-cell">
        <span class="task-date-text">{{ isset($task->start) && $task->start ? \Carbon\Carbon::parse($task->start)->format('m/d/y') : '-' }}</span>
    </div>

    <!-- Finish Date Cell -->
    <div class="task-cell">
        <span class="task-date-text">{{ isset($task->finish) && $task->finish ? \Carbon\Carbon::parse($task->finish)->format('m/d/y') : '-' }}</span>
    </div>

    <!-- Duration Cell -->
    <div class="task-duration-cell">
        <span class="duration-badge-modern">{{ $task->duration ?? 0 }}d</span>
    </div>
</div>

@if(isset($task->children) && $task->children->count() > 0)
<div class="task-children" data-parent-id="{{ $task->id }}">
    @foreach($task->children as $child)
        @include('partials.gantt-task-item', ['task' => $child, 'level' => ($level ?? 0) + 1, 'isChild' => true])
    @endforeach
</div>
@endif