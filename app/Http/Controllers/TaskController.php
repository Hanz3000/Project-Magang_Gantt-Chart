<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function index()
    {
        $tasks = Task::with('children')->whereNull('parent_id')->get();
        return view('projects.index', compact('tasks'));
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

    $start  = new \Carbon\Carbon($request->start);
    $finish = (clone $start)->addDays($request->duration - 1);

    // Cari level berdasarkan parent (jika ada)
    $level = 0;
    if ($request->parent_id) {
        $parent = Task::find($request->parent_id);
        $level  = $parent ? $parent->depth + 1 : 0; 
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
        // Check if task has children
        if ($task->children()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak dapat menghapus task yang memiliki sub-task. Hapus sub-task terlebih dahulu.'
            ], 400);
        }

        $task->delete();

        return response()->json([
            'success' => true,
            'message' => 'Task berhasil dihapus!'
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Terjadi kesalahan saat menghapus task: ' . $e->getMessage()
        ], 500);
    }
}


}
