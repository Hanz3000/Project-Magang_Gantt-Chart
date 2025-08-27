<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use Carbon\Carbon;

class TaskController extends Controller
{
    public function index()
    {
        // Ambil semua tugas dengan urutan berdasarkan kolom order
        $tasks = Task::with('children.children:id,name,parent_id,duration,start,finish,progress,level,order')
                     ->whereNull('parent_id')
                     ->orderBy('order')
                     ->get();

        // Ubah struktur data menjadi flat array untuk kemudahan rendering di Blade & JS
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
            // Format tanggal agar konsisten
            $startDate = $task->start ? Carbon::parse($task->start)->format('Y-m-d') : null;
            $endDate = $task->finish ? Carbon::parse($task->finish)->format('Y-m-d') : null;

            $result[] = [
                'id' => $task->id,
                'name' => $task->name,
                'startDate' => $startDate,
                'endDate' => $endDate,
                'duration' => $task->duration,
                'level' => $level,
                'status' => $task->status ?? 'pending',
                'progress' => $task->progress ?? 0,
                'parent_id' => $task->parent_id,
                'order' => $task->order ?? 0,
                'description' => $task->description ?? null,
                'children' => []
            ];

            // Jika task memiliki children, panggil fungsi ini lagi untuk mereka
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
        $parents = Task::all();
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

        $start = new Carbon($request->start, 'Asia/Jakarta');
        $start = $start->startOfDay();

        $finish = $request->finish
            ? new Carbon($request->finish, 'Asia/Jakarta')
            : $start->copy()->addDays($request->duration - 1);
        $finish = $finish->startOfDay();

        $duration = $request->duration ?: $start->diffInDays($finish) + 1;

        $level = 0;
        $parent = null;

        if ($request->parent_id) {
            $parent = Task::find($request->parent_id);
            $level  = $parent ? $parent->level + 1 : 0;

            if ($start < new Carbon($parent->start, 'Asia/Jakarta')) {
                $start  = new Carbon($parent->start, 'Asia/Jakarta');
                $start  = $start->startOfDay();
                $finish = $start->copy()->addDays($duration - 1);
            }

            if ($finish > new Carbon($parent->finish, 'Asia/Jakarta')) {
                $this->updateParentRecursively($parent, $finish);
            }
        }

        $maxOrder = Task::max('order') ?? 0;

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
        ]);

        return redirect()
            ->route('tasks.index')
            ->with('success', 'Task berhasil ditambahkan!');
    }

    protected function updateParentRecursively($task, $newFinish)
    {
        if (!$task->start || !$task->finish) {
            return;
        }

        $taskStart = new Carbon($task->start, 'Asia/Jakarta');
        $taskStart = $taskStart->startOfDay();

        $taskFinish = new Carbon($task->finish, 'Asia/Jakarta');
        $taskFinish = $taskFinish->startOfDay();

        $newFinish  = $newFinish->startOfDay();

        if ($newFinish > $taskFinish) {
            $task->finish   = $newFinish;
            $task->duration = $taskStart->diffInDays($newFinish) + 1;
            $task->save();

            if ($task->parent_id) {
                $parent = Task::find($task->parent_id);
                $this->updateParentRecursively($parent, $newFinish);
            }
        }
    }   

    public function edit(Task $task)
    {
        $parents = Task::where('id', '!=', $task->id)
                       ->whereNotIn('id', $this->getDescendantIds($task))
                       ->get();

        return view('projects.edit', compact('task', 'parents'));
    }

    public function update(Request $request, Task $task)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'duration' => 'required|integer|min:1',
            'start' => 'required|date',
            'parent_id' => 'nullable|exists:tasks,id',
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

        $task->update([
            'name' => $request->name,
            'duration' => $request->duration,
            'start' => $request->start,
            'parent_id' => $request->parent_id,
            'description' => $request->description,
            'finish' => Carbon::parse($request->start)->addDays($request->duration - 1),
            'level' => $request->parent_id ? (Task::find($request->parent_id)->level + 1) : 0,
        ]);

        $tasks = Task::with('children.children:id,name,parent_id,duration,start,finish,progress,level,order')
                     ->whereNull('parent_id')
                     ->orderBy('order')
                     ->get();
        $structuredTasks = $this->buildTaskTree($tasks);

        return redirect()->route('tasks.index')
                        ->with([
                            'success' => 'Task berhasil diupdate!',
                            'structuredTasks' => $structuredTasks
                        ]);
    }

    private function structureTasks($tasks)
    {
        $structured = [];
        $taskMap = [];

        foreach ($tasks as $task) {
            $startDate = $task->start ? Carbon::parse($task->start)->format('Y-m-d') : null;
            $endDate = $task->finish ? Carbon::parse($task->finish)->format('Y-m-d') : null;

            $taskArray = [
                'id' => $task->id,
                'name' => $task->name,
                'startDate' => $startDate,
                'endDate' => $endDate,
                'duration' => $task->duration,
                'level' => $task->level,
                'status' => $task->status ?? 'pending',
                'progress' => $task->progress ?? 0,
                'parent_id' => $task->parent_id,
                'order' => $task->order ?? 0,
                'description' => $task->description ?? null,
                'children' => []
            ];
            $taskMap[$task->id] = $taskArray;
        }

        foreach ($tasks as $task) {
            if ($task->parent_id && isset($taskMap[$task->parent_id])) {
                $taskMap[$task->parent_id]['children'][] = $taskMap[$task->id];
            } else {
                $structured[] = $taskMap[$task->id];
            }
        }

        return $structured;
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
        try {
            if ($task->children()->count() > 0) {
                return redirect()->route('tasks.index')
                    ->with('error', 'Tidak dapat menghapus task yang memiliki sub-task. Hapus sub-task terlebih dahulu.');
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