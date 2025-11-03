<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class ProjectController extends Controller
{
    public function index()
    {
        $tasks = Task::whereNull('parent_id')
                    ->with(['children' => function($query) {
                        $query->with('children'); // Load grandchildren
                    }])
                    ->get();

        $allStarts = collect();
        $allFinishes = collect();
        
        $this->getAllTaskDates($tasks, $allStarts, $allFinishes);
        
        $minDate = $allStarts->min() ? \Carbon\Carbon::parse($allStarts->min()) : today();
        $maxDate = $allFinishes->max() ? \Carbon\Carbon::parse($allFinishes->max()) : today()->addDays(30);
        $totalDays = $minDate->diffInDays($maxDate) + 1;
        
        // Ensure minimum span
        if($totalDays < 30) {
            $maxDate = $minDate->copy()->addDays(30);
            $totalDays = 30;
        }

        return view('projects.index', compact('tasks', 'minDate', 'maxDate', 'totalDays'));
    }

    private function getAllTaskDates($tasks, &$allStarts, &$allFinishes)
    {
        foreach($tasks as $task) {
            if($task->start) $allStarts->push($task->start);
            if($task->finish) $allFinishes->push($task->finish);
            
            if($task->children && $task->children->count() > 0) {
                $this->getAllTaskDates($task->children, $allStarts, $allFinishes);
            }
        }
    }
}
