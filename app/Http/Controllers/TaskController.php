<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class TaskController extends Controller
{
    public function index()
    {
        $tasks = Task::with('children.children:id,name,parent_id,duration,start,finish,progress,level,order,description,user_id')
            ->whereNull('parent_id')
            ->where('user_id', Auth::id()) // hanya tampilkan task user login
            ->orderBy('order')
            ->get();

        $structuredTasks = $this->buildTaskTree($tasks);

        return view('projects.index', [
            'tasks' => $tasks,
            'structuredTasks' => $structuredTasks,
            'createRoute' => route('tasks.create')
        ]);
    }

    private function buildTaskTree($tasks, $level = 0)
    {
        $result = [];
        foreach ($tasks as $task) {
            $startDate = $task->start ? Carbon::parse($task->start)->format('Y-m-d') : null;
            $endDate   = $task->finish ? Carbon::parse($task->finish)->format('Y-m-d') : null;

            $result[] = [
                'id'          => $task->id,
                'name'        => $task->name,
                'startDate'   => $startDate,
                'endDate'     => $endDate,
                'duration'    => $task->duration,
                'level'       => $level,
                'status'      => $task->status ?? 'pending',
                'progress'    => $task->progress ?? 0,
                'parent_id'   => $task->parent_id,
                'order'       => $task->order ?? 0,
                'description' => $task->description ?? null,
                'children'    => []
            ];

            if ($task->children->isNotEmpty()) {
                $childTasks = $this->buildTaskTree($task->children, $level + 1);
                $result[array_key_last($result)]['children'] = $childTasks;
                $result = array_merge($result, $childTasks);
            }
        }
        return $result;
    }

    public function create()
{
    // hanya ambil parent task milik user login
    $parents = Task::where('user_id', Auth::id())
        ->get()
        ->map(function($task) {
            $task->start = $task->start ? \Carbon\Carbon::parse($task->start)->format('d-m-Y') : null;
            $task->finish = $task->finish ? \Carbon\Carbon::parse($task->finish)->format('d-m-Y') : null;
            return $task;
        });

    return view('projects.create', compact('parents'));
}

    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'parent_id'   => 'nullable|exists:tasks,id',
            'start'       => 'required|date',
            'finish'      => 'required_if:duration,null|date|after_or_equal:start',
            'duration'    => 'nullable|integer|min:1',
            'description' => 'nullable|string',
        ]);

        $start = Carbon::parse($request->start, 'Asia/Jakarta')->startOfDay();

        $finish = $request->finish
            ? Carbon::parse($request->finish, 'Asia/Jakarta')->startOfDay()
            : $start->copy()->addDays($request->duration - 1);

        $duration = $request->duration ?: intval($start->diffInDays($finish) + 1);

        $level    = 0;

        if ($request->parent_id) {
            $parent = Task::where('id', $request->parent_id)
                ->where('user_id', Auth::id()) // parent juga harus milik user login
                ->first();

            if ($parent) {
                $level = $parent->level + 1;

                $parentStart = Carbon::parse($parent->start, 'Asia/Jakarta')->startOfDay();
                if ($start < $parentStart) {
                    $start  = $parentStart;
                    $finish = $start->copy()->addDays($duration - 1);
                }

                $parentFinish = Carbon::parse($parent->finish, 'Asia/Jakarta')->startOfDay();
                if ($finish > $parentFinish) {
                    $this->updateParentRecursively($parent, $finish);
                }
            }
        }

        $maxOrder = Task::where('user_id', Auth::id())->max('order') ?? 0;

        Task::create([
            'name'        => $request->name,
            'parent_id'   => $request->parent_id,
            'duration'    => $duration,
            'start'       => $start,
            'finish'      => $finish,
            'progress'    => 0,
            'level'       => $level,
            'order'       => $maxOrder + 1,
            'description' => $request->description,
            'user_id'     => Auth::id(), // tambahkan user_id
        ]);

        return redirect()->route('tasks.index')
            ->with('success', 'Task berhasil ditambahkan!');
    }

    protected function updateParentRecursively($task, $newFinish)
    {
        if (!$task->start || !$task->finish) {
            return;
        }

        $taskStart  = Carbon::parse($task->start, 'Asia/Jakarta')->startOfDay();
        $taskFinish = Carbon::parse($task->finish, 'Asia/Jakarta')->startOfDay();
        $newFinish  = $newFinish->startOfDay();

        if ($newFinish > $taskFinish) {
            $task->finish   = $newFinish;
            $task->duration = intval($taskStart->diffInDays($newFinish) + 1);
            $task->save();

            if ($task->parent_id) {
                $parent = Task::find($task->parent_id);
                if ($parent && $parent->user_id === Auth::id()) {
                    $this->updateParentRecursively($parent, $newFinish);
                }
            }
        }
    }

    public function edit(Task $task)
{
    if ($task->user_id !== Auth::id()) {
        return redirect()->route('tasks.index')->with('error', 'Anda tidak berhak mengedit task ini.');
    }

    $parents = Task::where('user_id', Auth::id())
        ->where('id', '!=', $task->id)
        ->whereNotIn('id', $this->getDescendantIds($task))
        ->get()
        ->map(function($task) {
            $task->start = $task->start ? \Carbon\Carbon::parse($task->start)->format('d-m-Y') : null;
            $task->finish = $task->finish ? \Carbon\Carbon::parse($task->finish)->format('d-m-Y') : null;
            return $task;
        });

    return view('projects.edit', compact('task', 'parents'));
}

    public function update(Request $request, Task $task)
    {
        if ($task->user_id !== Auth::id()) {
            return redirect()->route('tasks.index')->with('error', 'Anda tidak berhak mengupdate task ini.');
        }

        $request->validate([
            'name'        => 'required|string|max:255',
            'duration'    => 'required|integer|min:1',
            'start'       => 'required|date',
            'parent_id'   => 'nullable|exists:tasks,id',
            'description' => 'nullable|string|max:1000'
        ]);

        if ($request->parent_id) {
            $descendantIds = $this->getDescendantIds($task);
            if (in_array($request->parent_id, $descendantIds) || $request->parent_id == $task->id) {
                return back()->withErrors([
                    'parent_id' => 'Tidak dapat memilih task ini atau child-nya sebagai parent.'
                ]);
            }
        }

        $start    = Carbon::parse($request->start, 'Asia/Jakarta')->startOfDay();
        $duration = $request->duration;
        $finish   = $start->copy()->addDays($duration - 1);
        $level    = 0;

        if ($request->parent_id) {
            $parent = Task::where('id', $request->parent_id)
                ->where('user_id', Auth::id())
                ->first();

            if ($parent) {
                $parentStart = Carbon::parse($parent->start, 'Asia/Jakarta')->startOfDay();
                if ($start < $parentStart) {
                    $start  = $parentStart;
                    $finish = $start->copy()->addDays($duration - 1);
                }

                $level = $parent->level + 1;
            }
        }

        $task->update([
            'name'        => $request->name,
            'duration'    => $duration,
            'start'       => $start,
            'finish'      => $finish,
            'parent_id'   => $request->parent_id,
            'description' => $request->description,
            'level'       => $level,
        ]);

        if ($request->parent_id && isset($parent)) {
            $maxFinishStr = $parent->children()->max('finish');
            $maxFinish    = $maxFinishStr ? Carbon::parse($maxFinishStr, 'Asia/Jakarta')->startOfDay() : null;

            if ($maxFinish) {
                $this->updateParentRecursively($parent, $maxFinish);
            }
        }

        return redirect()->route('tasks.index')
            ->with('success', 'Task berhasil diupdate!');
    }

    private function getDescendantIds(Task $task)
    {
        $descendants = [];
        $this->collectDescendants($task, $descendants);
        return $descendants;
    }

    private function collectDescendants(Task $task, &$descendants)
    {
        foreach ($task->children as $child) {
            $descendants[] = $child->id;
            $this->collectDescendants($child, $descendants);
        }
    }

    public function destroy(Task $task)
    {
        if ($task->user_id !== Auth::id()) {
            return redirect()->route('tasks.index')->with('error', 'Anda tidak berhak menghapus task ini.');
        }

        try {
            if ($task->children()->count() > 0) {
                $task->children()->delete();
                $task->delete();
                return redirect()->route('tasks.index')
                    ->with('warning', 'Tugas utama dan semua sub-task-nya telah dihapus!');
            }

            $task->delete();

            return redirect()->route('tasks.index')
                ->with('success', 'Task berhasil dihapus!');
        } catch (\Exception $e) {
            return redirect()->route('tasks.index')
                ->with('error', 'Terjadi kesalahan saat menghapus task: ' . $e->getMessage());
        }
    }
}
