<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;

class TaskController extends Controller
{
    public function index()
    {
        $tasks = Task::where('user_id', Auth::id())
            ->orderBy('order')
            ->get(); // REVISI: Hapus ->with('assignedUser') karena string biasa

        $structuredTasks = $this->buildTaskHierarchy($tasks);

        return view('projects.index', [
            'tasks' => $tasks,
            'structuredTasks' => $structuredTasks,
            'createRoute' => route('tasks.create')
        ]);
    }

    private function buildTaskHierarchy($tasks, $parentId = null, $level = 0)
    {
        $result = [];
        $filteredTasks = $tasks->where('parent_id', $parentId)->sortBy('order');

        foreach ($filteredTasks as $task) {
            $startDate = $task->start ? Carbon::parse($task->start)->format('Y-m-d') : null;
            $endDate   = $task->finish ? Carbon::parse($task->finish)->format('Y-m-d') : null;

            $taskData = [
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
                // REVISI: Tampilkan string langsung, tanpa relasi
                'penanggung_jawab' => $task->penanggung_jawab ?? null,
            ];
            
            $result[] = $taskData;
            
            $children = $this->buildTaskHierarchy($tasks, $task->id, $level + 1);
            if (!empty($children)) {
                $result = array_merge($result, $children);
            }
        }

        return $result;
    }

    private function normalizeOrders()
    {
        $tasks = Task::where('user_id', Auth::id())
            ->orderBy('order')
            ->get();

        $order = 1;
        $this->assignOrders($tasks, null, $order);
    }

    private function assignOrders($tasks, $parentId, &$order)
    {
        $children = $tasks->where('parent_id', $parentId);

        foreach ($children as $child) {
            $child->order = $order++;
            $child->save();
            $this->assignOrders($tasks, $child->id, $order);
        }
    }

    public function create()
    {
        $parents = Task::where('user_id', Auth::id())
            ->get()
            ->map(function ($task) {
                $task->start = $task->start ? Carbon::parse($task->start)->format('d-m-Y') : null;
                $task->finish = $task->finish ? Carbon::parse($task->finish)->format('d-m-Y') : null;
                return $task;
            });

        // REVISI: Hapus query $users, karena input text bebas (tidak perlu daftar user)

        return view('projects.create', compact('parents')); // REVISI: Hapus 'users'
    }

    public function store(Request $request)
    {
        $rules = [
            'name'        => 'required|string|max:255',
            'parent_id'   => 'nullable|exists:tasks,id',
            'start'       => 'required|date',
            'duration'    => 'required_without:finish|nullable|integer|min:1',
            'description' => 'nullable|string',
            // REVISI: Validasi sebagai string - WAJIB DIISI
            'penanggung_jawab' => 'required|string|max:255',
        ];

        $finishRule = 'required_without:duration|date';
        if (!$request->parent_id) {
            $finishRule .= '|after_or_equal:start';
        }
        $rules['finish'] = $finishRule;

        $request->validate($rules);

        $start = Carbon::parse($request->start, 'Asia/Jakarta')->startOfDay();

        if ($request->duration) {
            $finish = $start->copy()->addDays($request->duration - 1);
        } else {
            $finish = Carbon::parse($request->finish, 'Asia/Jakarta')->startOfDay();
        }

        $duration = intval($start->diffInDays($finish) + 1);
        $level = 0;

        if ($request->parent_id) {
            $parent = Task::where('id', $request->parent_id)
                ->where('user_id', Auth::id())
                ->first();

            if ($parent) {
                $level = $parent->level + 1;
                $parentStart = Carbon::parse($parent->start, 'Asia/Jakarta')->startOfDay();
                if ($start < $parentStart) {
                    $start  = $parentStart;
                    $finish = $start->copy()->addDays($duration - 1);
                }
            }
        }

        $maxOrder = 0;
        if ($request->parent_id) {
            $maxOrder = Task::where('parent_id', $request->parent_id)
                ->where('user_id', Auth::id())
                ->max('order') ?? 0;
        } else {
            $maxOrder = Task::whereNull('parent_id')
                ->where('user_id', Auth::id())
                ->max('order') ?? 0;
        }

        $newTask = Task::create([
            'name'        => $request->name,
            'parent_id'   => $request->parent_id,
            'duration'    => $duration,
            'start'       => $start,
            'finish'      => $finish,
            'progress'    => 0,
            'level'       => $level,
            'order'       => $maxOrder + 1,
            'description' => $request->description,
            // REVISI: Simpan sebagai string
            'penanggung_jawab' => $request->penanggung_jawab,
            'user_id'     => Auth::id(),
        ]);

        if ($request->parent_id) {
            $this->ensureParentFinishCoversChildren($newTask->parent);
        }

        $this->normalizeOrders();

        return redirect()->route('tasks.index')->with('success', 'Task berhasil ditambahkan!');
    }

    public function edit(Task $task)
    {
        if ($task->user_id !== Auth::id()) {
            return redirect()->route('tasks.index')->with('error', 'Anda tidak berhak mengedit task ini.');
        }

        $task->original_start_date = $task->start ? Carbon::parse($task->start)->format('Y-m-d') : null;
        $task->original_finish_date = $task->finish ? Carbon::parse($task->finish)->format('Y-m-d') : null;
        
        $task->start = $task->original_start_date;
        $task->finish = $task->original_finish_date;

        $root = $this->getRootTask($task);
        $parents = collect();

        if ($root->id == $task->id) {
            $parents = Task::where('user_id', Auth::id())
                ->whereNull('parent_id')
                ->where('id', '!=', $task->id)
                ->get();
        } else {
            $hierarchyIds = $this->getAllIdsInHierarchy($root);
            $parents = Task::where('user_id', Auth::id())
                ->whereIn('id', $hierarchyIds)
                ->where('id', '!=', $task->id)
                ->whereNotIn('id', $this->getDescendantIds($task))
                ->get();
        }

        $parents = $parents->map(function ($t) {
            $t->start = $t->start ? Carbon::parse($t->start)->format('d-m-Y') : null;
            $t->finish = $t->finish ? Carbon::parse($t->finish)->format('d-m-Y') : null;
            return $t;
        });

        // REVISI: Hapus $users, karena input text bebas

        return view('projects.edit', compact('task', 'parents')); // REVISI: Hapus 'users'
    }

    private function getAllIdsInHierarchy(Task $root)
    {
        $ids = [$root->id];
        $descendants = $this->getDescendantIds($root);
        return array_merge($ids, $descendants);
    }

    public function update(Request $request, Task $task)
    {
        if ($task->user_id !== Auth::id()) {
            return redirect()->route('tasks.index')->with('error', 'Anda tidak berhak mengupdate task ini.');
        }

        $rules = [
            'name'                => 'required|string|max:255',
            'start'               => 'required|date',
            'duration'            => 'required_without:finish|nullable|integer|min:1',
            'parent_id'           => 'nullable|exists:tasks,id',
            'description'         => 'nullable|string|max:1000',
            // REVISI: Validasi sebagai string - WAJIB DIISI
            'penanggung_jawab'    => 'required|string|max:255',
            'move_children'       => 'nullable|in:0,1',
            'original_start_date' => 'nullable|date',
            'original_finish_date' => 'nullable|date',
        ];

        $finishRule = 'required_without:duration|nullable|date';
        if ($task->parent_id === null) {
            $finishRule .= '|after_or_equal:start';
        }
        $rules['finish'] = $finishRule;

        $request->validate($rules);

        if ($request->parent_id) {
            $descendantIds = $this->getDescendantIds($task);
            if (in_array($request->parent_id, $descendantIds) || $request->parent_id == $task->id) {
                return back()->withErrors([
                    'parent_id' => 'Tidak dapat memilih task ini atau child-nya sebagai parent.'
                ]);
            }
        }

        $oldParentId = $task->parent_id; 
        
        $originalStart = $request->original_start_date
            ? Carbon::parse($request->original_start_date, 'Asia/Jakarta')->startOfDay()
            : Carbon::parse($task->start, 'Asia/Jakarta')->startOfDay();

        $newStart = Carbon::parse($request->start, 'Asia/Jakarta')->startOfDay();
        $daysDiff = $originalStart->diffInDays($newStart, false);

        $start = $newStart;
        $duration = null;
        $finish = null;

        if ($request->duration) {
            $duration = $request->duration;
            $finish = $start->copy()->addDays($duration - 1);
        } else {
            $finish = $request->finish ? Carbon::parse($request->finish, 'Asia/Jakarta')->startOfDay() : $start->copy()->addDays(0);
            $duration = intval($start->diffInDays($finish) + 1);
        }

        $level = 0;
        $parent = null;

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

        $adjustedDueToChildren = false;

        DB::beginTransaction();

        try {
            $task->update([
                'name'        => $request->name,
                'duration'    => $duration,
                'start'       => $start,
                'finish'      => $finish,
                'parent_id'   => $request->parent_id,
                'description' => $request->description,
                // REVISI: Update sebagai string
                'penanggung_jawab' => $request->penanggung_jawab,
                'level'       => $level,
            ]);
            
            $moveChildren = $request->input('move_children', '1') === '1';
            $parentChanged = $oldParentId != $request->parent_id;

            $task->fresh(); 
            $task->load('children'); 

            if ($task->children && $task->children->count() > 0) {
                if ($moveChildren) {
                    if ($daysDiff != 0) {
                        $this->updateChildrenWithOffset($task->children, $daysDiff);
                    }
                    $this->ensureParentFinishCoversChildren($task);
                } else {
                    if ($parentChanged) {
                        $detachToParentId = $oldParentId ?: null; 
                        $newLevel = $oldParentId ? ($task->fresh()->level - 1) : 0; 
                        $this->detachChildrenToParent($task->children, $detachToParentId, $newLevel);
                    }
                }
            }
            
            if ($parentChanged) {
                $this->reorderTasksAfterParentChange($task, $oldParentId, $request->parent_id);
            }

            if ($request->parent_id && $parent) {
                $this->ensureParentFinishCoversChildren($parent);
            }

            if ($parentChanged && $oldParentId) {
                $oldParent = Task::find($oldParentId);
                if ($oldParent) {
                    $this->updateParentFinishIfNeeded($oldParent);
                }
            }
            
            // Recalculate progress for the entire chain (new and old paths)
            $this->updateAncestorChain($task->id); // For current path
            if ($parentChanged && $oldParentId) {
                $this->updateAncestorChain($oldParentId); // For old path
            }

            DB::commit();
            $this->normalizeOrders();

            $successMessage = 'Task berhasil diupdate!';
            
            return redirect()->route('tasks.index')->with('success', $successMessage);
        } catch (\Exception $e) {
            DB::rollback();
            return back()->withErrors(['error' => 'Gagal update task: ' . $e->getMessage()])->withInput();
        }
    }

    private function getMaxDescendantFinish($task)
    {
        $maxFinish = null;
        $children = $task->children;

        foreach ($children as $child) {
            $childFinish = $child->finish ? Carbon::parse($child->finish, 'Asia/Jakarta')->startOfDay() : null;
            if ($childFinish && (!$maxFinish || $childFinish > $maxFinish)) {
                $maxFinish = $childFinish;
            }
            $subMax = $this->getMaxDescendantFinish($child);
            if ($subMax && (!$maxFinish || $subMax > $maxFinish)) {
                $maxFinish = $subMax;
            }
        }
        return $maxFinish;
    }

    private function ensureParentFinishCoversChildren($parent)
    {
        if (!$parent || !$parent->start) return;

        $maxChildFinish = $this->getMaxDescendantFinish($parent);
        if ($maxChildFinish) {
            $root = $this->getRootTask($parent);
            $rootStart = Carbon::parse($root->start, 'Asia/Jakarta')->startOfDay();
            $rootCurrentFinish = Carbon::parse($root->finish, 'Asia/Jakarta')->startOfDay();

            if ($maxChildFinish > $rootCurrentFinish) {
                $root->finish = $maxChildFinish;
                $root->duration = intval($rootStart->diffInDays($maxChildFinish) + 1);
                $root->save();
            }
        }
    }

    private function updateChildrenWithOffset($children, $daysDiff)
    {
        foreach ($children as $child) {
            if ($child->start) {
                $newChildStart = Carbon::parse($child->start, 'Asia/Jakarta')->startOfDay()->addDays($daysDiff);
                $child->start = $newChildStart;
            }

            if ($child->finish) {
                $newChildFinish = Carbon::parse($child->finish, 'Asia/Jakarta')->startOfDay()->addDays($daysDiff);
                $child->finish = $newChildFinish;
            }

            if ($child->start && $child->finish) {
                $child->duration = intval($child->start->diffInDays($child->finish) + 1);
            }

            $child->save();

            $child->load('children');
            if ($child->children && $child->children->count() > 0) {
                $this->updateChildrenWithOffset($child->children, $daysDiff);
            }
        }
    }

    private function detachChildrenToParent($children, $targetParentId, $newLevel)
    {
        foreach ($children as $child) {
            $child->parent_id = $targetParentId;
            $child->level = $newLevel + 1;

            $maxOrderInTarget = Task::where('parent_id', $targetParentId)
                ->where('user_id', Auth::id())
                ->max('order') ?? 0;
            $child->order = $maxOrderInTarget + 1;

            $child->save();

            $child->load('children');
            if ($child->children && $child->children->count() > 0) {
                $this->detachChildrenToParent($children, $child->id, $child->level);
            }
        }
    }

    private function updateParentFinishIfNeeded($parent)
    {
        $maxFinishStr = $parent->children()->max('finish');
        $maxFinish = $maxFinishStr ? Carbon::parse($maxFinishStr, 'Asia/Jakarta')->startOfDay() : null;

        if ($maxFinish && $maxFinish < Carbon::parse($parent->finish)) {
            $parentStart = Carbon::parse($parent->start, 'Asia/Jakarta')->startOfDay();
            $parent->finish = $maxFinish;
            $parent->duration = intval($parentStart->diffInDays($maxFinish) + 1);
            $parent->save();

            if ($parent->parent_id) {
                $grandParent = Task::find($parent->parent_id);
                if ($grandParent) {
                    $this->updateParentFinishIfNeeded($grandParent);
                }
            }
        }
    }

    private function reorderTasksAfterParentChange($task, $oldParentId, $newParentId)
    {
        if ($newParentId) {
            $minOrder = Task::where('parent_id', $newParentId)
                ->where('user_id', Auth::id())
                ->where('id', '!=', $task->id)
                ->min('order') ?? 1;

            $task->order = $minOrder - 1;
            $task->save();
        } else {
            $maxOrder = Task::whereNull('parent_id')
                ->where('user_id', Auth::id())
                ->where('id', '!=', $task->id)
                ->max('order') ?? 0;

            $task->order = $maxOrder + 1;
            $task->save();
        }
    }

    private function updateDescendantsLevel($task)
    {
        $children = Task::where('parent_id', $task->id)->get();
        foreach ($children as $child) {
            $child->level = $task->level + 1;
            $child->save();
            $this->updateDescendantsLevel($child);
        }
    }

    protected function getRootTask($task)
    {
        $current = $task;
        while ($current->parent_id) {
            $current = Task::find($current->parent_id);
            if (!$current) break;
        }
        return $current;
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

    private function flattenTaskHierarchy(Task $task)
    {
        $flattened = collect();
        $flattened->push($task);
        $this->collectFlattenedChildren($task, $flattened);
        return $flattened->sortBy('order');
    }

    private function collectFlattenedChildren(Task $parent, &$flattened)
    {
        $parent->load('children');
        foreach ($parent->children->sortBy('order') as $child) {
            $flattened->push($child);
            $this->collectFlattenedChildren($child, $flattened);
        }
    }

    private function deleteRecursive(Task $task)
    {
        $children = $task->children()->get();
        foreach ($children as $child) {
            $this->deleteRecursive($child);
        }
        $task->delete();
    }

    public function destroy(Task $task)
    {
        if ($task->user_id !== Auth::id()) {
            return redirect()->route('tasks.index')->with('error', 'Anda tidak berhak menghapus task ini.');
        }

        $parentId = $task->parent_id;
        try {
            $this->deleteRecursive($task);

            if ($parentId) {
                // Update ancestor chain after deletion
                $this->updateAncestorChain($parentId);
                
                $parent = Task::find($parentId);
                if ($parent) {
                    $this->updateParentFinishIfNeeded($parent);
                }
            }

            return redirect()->route('tasks.index')->with('success', 'Task berhasil dihapus!');
        } catch (\Exception $e) {
            return redirect()->route('tasks.index')->with('error', 'Terjadi kesalahan saat menghapus task: ' . $e->getMessage());
        }
    }

    public function exportGanttPdf(Request $request)
    {
        try {
            $filterIds = null;
            $filterParam = $request->query('filter');
            if ($filterParam) {
                $decoded = json_decode($filterParam, true);
                if (is_array($decoded)) {
                    $filterIds = array_map('intval', $decoded);
                }
            }

            $tasks = Task::where('user_id', Auth::id())
                ->orderBy('order')
                ->get(); // REVISI: Hapus ->with('assignedUser')

            if ($tasks->isEmpty()) {
                return response()->json(['error' => 'Tidak ada tasks untuk diexport.'], 404);
            }

            if ($filterIds && !empty($filterIds)) {
                $tasks = $tasks->filter(function ($t) use ($filterIds) {
                    return in_array($t->id, $filterIds);
                });
                $tasks = $tasks->sortBy('order');
            }

            if ($tasks->isEmpty()) {
                return response()->json(['error' => 'Tidak ada task yang terfilter untuk diexport.'], 404);
            }

            $startDate = $tasks->min('start');
            $endDate = $tasks->max('finish');

            $pdf = Pdf::loadView('pdf.gantt', compact('tasks', 'startDate', 'endDate'));
            return $pdf->stream('gantt_chart_preview.pdf');

        } catch (\Exception $e) {
            Log::error('Export Gantt PDF failed: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Gagal generate PDF: ' . $e->getMessage()], 500);
        }
    }

    public function exportTaskPdf(Task $task)
    {
        try {
            if ($task->user_id !== Auth::id()) {
                abort(403, 'Unauthorized');
            }

            $rootTask = $this->getRootTask($task);
            $rootTask->load(['children' => function ($query) {
                $query->orderBy('order')->with(['children' => function($q) {
                    $q->orderBy('order');
                }]); // REVISI: Hapus ->with('assignedUser')
            }]);

            $tasks = $this->flattenTaskHierarchy($rootTask);

            if ($tasks->isEmpty()) {
                return response()->json(['error' => 'Tidak ada tasks untuk diexport.'], 404);
            }

            $startDate = $tasks->min('start');
            $endDate = $tasks->max('finish');

            $pdf = Pdf::loadView('task.pdf', compact('tasks', 'startDate', 'endDate'));
            return $pdf->stream('task_hierarchy_' . $rootTask->id . '_preview.pdf');

        } catch (\Exception $e) {
            Log::error('Export Task PDF failed: ' . $e->getMessage(), [
                'task_id' => $task->id,
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Gagal generate PDF task: ' . $e->getMessage()], 500);
        }
    }

    // ============================================
    // UPDATED PROGRESS METHOD (WEIGHTED AVERAGE)
    // ============================================

    // Update progress hanya untuk 1 task saja, tanpa mempengaruhi parent atau task lain
    public function updateProgress(Request $request, Task $task)
    {
        // Validasi input
        $request->validate([
            'progress' => 'required|numeric|min:0|max:100'
        ]);

        // Update hanya task yang dipilih
        $task->progress = $request->progress;
        $task->save();

        return response()->json([
            'status' => 'ok',
            'task' => [
                'id' => $task->id,
                'progress' => $task->progress,
            ],
        ]);
    }   

    private function updateAncestorChain($taskId)
    {
        // Ambil task
        $task = Task::find($taskId);

        if (!$task) {
            return;
        }

        // Hitung ulang level berdasarkan parent
        if ($task->parent_id) {
            $parent = Task::find($task->parent_id);
            $task->level = ($parent->level ?? 0) + 1;
        } else {
            $task->level = 0; // root
        }

        $task->save();

        // Update juga descendant/anak-anak
        $children = Task::where('parent_id', $task->id)->get();

        foreach ($children as $child) {
            $this->updateAncestorChain($child->id);
        }
    }

}