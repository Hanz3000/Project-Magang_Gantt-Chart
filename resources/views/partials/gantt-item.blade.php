{{-- partials/gantt-item.blade.php --}}
<div class="gantt-row" 
     data-task-id="{{ $task->id }}" 
     data-level="{{ $level }}">
     
    <div class="relative w-full">
        @if($task->start && $task->finish)
            @php
                $startDate = \Carbon\Carbon::parse($task->start);
                $finishDate = \Carbon\Carbon::parse($task->finish);
                $startOffset = $minDate->diffInDays($startDate);
                $duration = $startDate->diffInDays($finishDate) + 1;
            @endphp
            
            {{-- Background grid --}}
            @for($i = 0; $i < $totalDays; $i++)
                @php
                    $currentDate = $minDate->copy()->addDays($i);
                    $dayOfWeek = $currentDate->dayOfWeek;
                    $isWeekend = in_array($dayOfWeek, [0, 6]);
                    $isToday = $currentDate->isToday();
                @endphp
                <div class="gantt-grid-cell {{ $isWeekend ? 'weekend' : '' }} {{ $isToday ? 'today' : '' }}" 
                     style="left: {{ $i * 24 }}px; position: absolute;"></div>
            @endfor
            
            {{-- Task bar --}}
            <div class="gantt-bar level-{{ $level % 6 }}"
                 style="left: {{ $startOffset * 24 }}px; width: {{ max($duration * 24, 24) }}px;"
                 data-task-id="{{ $task->id }}"
                 data-task-name="{{ $task->name }}"
                 data-start-date="{{ $task->start }}"
                 data-end-date="{{ $task->finish }}"
                 data-description="{{ $task->description ?? '' }}">
                 
                {{-- Task name --}}
                @if($duration * 24 > 60)
                    <span class="truncate">{{ $task->name }}</span>
                @endif
            </div>
            
        @else
            {{-- Background grid for tasks without dates --}}
            @for($i = 0; $i < $totalDays; $i++)
                @php
                    $currentDate = $minDate->copy()->addDays($i);
                    $dayOfWeek = $currentDate->dayOfWeek;
                    $isWeekend = in_array($dayOfWeek, [0, 6]);
                    $isToday = $currentDate->isToday();
                @endphp
                <div class="gantt-grid-cell {{ $isWeekend ? 'weekend' : '' }} {{ $isToday ? 'today' : '' }}" 
                     style="left: {{ $i * 24 }}px; position: absolute;"></div>
            @endfor
            
            {{-- Placeholder for tasks without dates --}}
            <div class="gantt-bar"
                 style="left: 24px; width: 72px; background: #9ca3af; border-color: #6b7280;"
                 data-task-id="{{ $task->id }}"
                 data-task-name="{{ $task->name }}"
                 data-start-date=""
                 data-end-date=""
                 data-description="{{ $task->description ?? '' }}">
                <span class="truncate text-white">{{ Str::limit($task->name, 8) }}</span>
            </div>
        @endif
    </div>
</div>

{{-- Recursive call for children --}}
@if($task->children && $task->children->count() > 0)
    <div class="gantt-children" data-parent-id="{{ $task->id }}">
        @foreach($task->children as $childTask)
            @include('partials.gantt-item', [
                'task' => $childTask, 
                'level' => $level + 1, 
                'minDate' => $minDate, 
                'totalDays' => $totalDays
            ])
        @endforeach
    </div>
@endif

