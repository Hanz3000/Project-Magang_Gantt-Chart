<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use Carbon\Carbon; 

class TaskController extends Controller
{
     public function index()
    {
        // Ambil hanya parent tasks (level 0) beserta semua level children-nya
        $tasks = Task::with('children.children:id,name,parent_id,duration,start,finish,progress,level') // Eager load nested children
                     ->whereNull('parent_id')
                     ->get();

        // Ubah struktur data menjadi flat array untuk kemudahan rendering di Blade & JS
        $structuredTasks = $this->buildTaskTree($tasks);

        return view('projects.index', [
            'tasks' => $tasks, // Data asli untuk rendering rekursif di Blade
            'structuredTasks' => $structuredTasks, // Data untuk JavaScript
            'createRoute' => route('tasks.create') // Menambahkan route untuk tombol "Add Task"
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
            ];

            // Jika task memiliki children, panggil fungsi ini lagi untuk mereka
            if ($task->children->isNotEmpty()) {
                $result = array_merge($result, $this->buildTaskTree($task->children, $level + 1));
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
            'name'      => 'required|string|max:255',
            'duration'  => 'required|integer|min:1',
            'start'     => 'required|date',
            'parent_id' => 'nullable|exists:tasks,id',
        ]);

        $start  = new Carbon($request->start);
        $finish = (clone $start)->addDays($request->duration - 1);

        $level = 0;
        if ($request->parent_id) {
            $parent = Task::find($request->parent_id);
            $level  = $parent ? $parent->level + 1 : 0;
        }

        Task::create([
            'name'      => $request->name,
            'parent_id' => $request->parent_id,
            'duration'  => $request->duration,
            'start'     => $request->start,
            'finish'    => $finish,
            'progress'  => 0,
            'level'     => $level,
        ]);

        return redirect()->route('tasks.index')->with('success', 'Task berhasil ditambahkan!');
    }


public function edit(Task $task)
{
    // Ambil semua task yang bisa dijadikan parent (kecuali task yang sedang diedit dan child-nya)
    $parents = Task::where('id', '!=', $task->id)
                   ->whereNotIn('id', $this->getDescendantIds($task))
                   ->get();
    
    return view('projects.edit', compact('task', 'parents'));

}

/**
 * Update the specified task in storage.
 */
public function update(Request $request, Task $task)
{
    $request->validate([
        'name' => 'required|string|max:255',
        'duration' => 'required|integer|min:1',
        'start' => 'required|date',
        'parent_id' => 'nullable|exists:tasks,id',
        'description' => 'nullable|string|max:1000'
    ]);

    // Validasi circular dependency
    if ($request->parent_id) {
        $descendantIds = $this->getDescendantIds($task);
        if (in_array($request->parent_id, $descendantIds) || $request->parent_id == $task->id) {
            return back()->withErrors([
                'parent_id' => 'Tidak dapat memilih task ini atau child-nya sebagai parent.'
            ]);
        }
    }

    // Update data task
    $task->update([
        'name' => $request->name,
        'duration' => $request->duration,
        'start' => $request->start,
        'parent_id' => $request->parent_id,
        'description' => $request->description,
        'finish' => \Carbon\Carbon::parse($request->start)->addDays($request->duration - 1)
    ]);

    return redirect()->route('tasks.index')
                    ->with('success', 'Task berhasil diupdate!');
}

/**
 * Get all descendant IDs of a task (children, grandchildren, etc.)
 */
private function getDescendantIds(Task $task)
{
    $descendants = [];
    $this->collectDescendants($task, $descendants);
    return $descendants;
}

/**
 * Recursively collect descendant IDs
 */
private function collectDescendants(Task $task, &$descendants)
{
    foreach ($task->children as $child) {
        $descendants[] = $child->id;
        $this->collectDescendants($child, $descendants);
    }
}

/**
 * Remove the specified task from storage.
 */
public function destroy(Task $task)
{
    try {
        // Cek apakah task punya children
        if ($task->children()->count() > 0) {
            return redirect()->route('projects.index')
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
