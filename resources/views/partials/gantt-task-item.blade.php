<div class="task-row {{ isset($isChild) && $isChild ? 'task-child' : '' }}" data-task-id="{{ $task->id }}" style="{{ isset($level) ? 'padding-left: ' . ($level * 20) . 'px;' : '' }}">
    <div class="task-cell">
        @if($task->children && $task->children->count() > 0)
        <span class="toggle-collapse" data-task-id="{{ $task->id }}">
            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
            </svg>
        </span>
        @endif
    </div>
    <div class="task-name-cell" style="{{ isset($level) ? 'padding-left: ' . ($level * 16) . 'px;' : '' }}">
        <span class="task-indicator indicator-level-{{ ($task->level ?? $level ?? 0) % 6 }}"></span>
        {{ $task->name }}
    </div>
    <div class="task-cell">
        <span class="duration-badge">{{ $task->duration ?? 0 }}d</span>
    </div>
    <div class="task-cell">
        {{ isset($task->start) && $task->start ? \Carbon\Carbon::parse($task->start)->format('m/d/y') : '-' }}
    </div>
    <div class="task-cell">
        {{ isset($task->finish) && $task->finish ? \Carbon\Carbon::parse($task->finish)->format('m/d/y') : '-' }}
    </div>
</div>

@if(isset($task->children) && $task->children->count() > 0)
<div class="task-children" data-parent-id="{{ $task->id }}">
    @foreach($task->children as $child)
        @include('partials.gantt-task-item', ['task' => $child, 'level' => ($level ?? 0) + 1, 'isChild' => true])
    @endforeach
</div>
@endif