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
        // Ambil SEMUA tasks milik user, bukan hanya root
        $tasks = Task::where('user_id', Auth::id())
            ->orderBy('order')
            ->get();

        // Build struktur hierarkis yang benar
        $structuredTasks = $this->buildTaskHierarchy($tasks);

        return view('projects.index', [
            'tasks' => $tasks,
            'structuredTasks' => $structuredTasks,
            'createRoute' => route('tasks.create')
        ]);
    }

    /**
     * Build hierarki task dengan urutan yang benar
     * Task akan muncul dalam urutan: Parent → Children → Next Parent
     */
    private function buildTaskHierarchy($tasks, $parentId = null, $level = 0)
    {
        $result = [];
        
        // Filter tasks berdasarkan parent_id
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
            ];

            // Tambahkan task ke hasil
            $result[] = $taskData;

            // Rekursif untuk children
            $children = $this->buildTaskHierarchy($tasks, $task->id, $level + 1);
            if (!empty($children)) {
                // Tambahkan children langsung setelah parent
                $result = array_merge($result, $children);
            }
        }
        
        return $result;
    }

    /**
     * Normalize orders secara rekursif untuk memastikan urutan sequential global
     * tanpa mengubah relative order per level
     */
    private function normalizeOrders()
    {
        $tasks = Task::where('user_id', Auth::id())
            ->orderBy('order')
            ->get();

        $order = 1;
        $this->assignOrders($tasks, null, $order);
    }

    /**
     * Assign orders rekursif, preserving relative order dari collection yang sudah di-sort
     */
    private function assignOrders($tasks, $parentId, &$order)
    {
        // Ambil children dalam urutan yang ada di collection (sudah global sorted)
        $children = $tasks->where('parent_id', $parentId);

        foreach ($children as $child) {
            $child->order = $order++;
            $child->save();

            // Rekursif untuk sub-children
            $this->assignOrders($tasks, $child->id, $order);
        }
    }

    public function create()
    {
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

                $parentFinish = Carbon::parse($parent->finish, 'Asia/Jakarta')->startOfDay();
                if ($finish > $parentFinish) {
                    $this->updateParentRecursively($parent, $finish);
                }
            }
        }

        // Saat create, set order sebagai yang TERAKHIR di parent (atau root)
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
            'order'       => $maxOrder + 1,  // Yang terakhir untuk task baru
            'description' => $request->description,
            'user_id'     => Auth::id(),
        ]);

        // Normalize orders setelah create untuk memastikan urutan benar
        $this->normalizeOrders();

        return redirect()->route('tasks.index')
            ->with('success', 'Task berhasil ditambahkan!');
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
            ->map(function($t) {
                $t->start = $t->start ? \Carbon\Carbon::parse($t->start)->format('d-m-Y') : null;
                $t->finish = $t->finish ? \Carbon\Carbon::parse($t->finish)->format('d-m-Y') : null;
                return $t;
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

        $oldParentId = $task->parent_id; // Simpan parent lama

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

        // Reorder tasks setelah parent berubah
        if ($oldParentId != $request->parent_id) {
            $this->reorderTasksAfterParentChange($task, $oldParentId, $request->parent_id);
        }

        // Update parent recursively jika perlu
        if ($request->parent_id && isset($parent)) {
            $maxFinishStr = $parent->children()->max('finish');
            $maxFinish    = $maxFinishStr ? Carbon::parse($maxFinishStr, 'Asia/Jakarta')->startOfDay() : null;

            if ($maxFinish) {
                $this->updateParentRecursively($parent, $maxFinish);
            }
        }

        // Normalize orders setelah update untuk memastikan urutan benar
        $this->normalizeOrders();

        return redirect()->route('tasks.index')
            ->with('success', 'Task berhasil diupdate!');
    }

    /**
     * Fungsi untuk reorder tasks setelah parent berubah
     * - Saat pindah ke parent baru (level > 0): menjadi YANG PERTAMA di siblings
     * - Saat pindah ke root (level = 0): menjadi YANG TERAKHIR di root (untuk menghindari muncul di atas existing roots seperti Magang)
     */
    private function reorderTasksAfterParentChange($task, $oldParentId, $newParentId)
    {
        if ($newParentId) {
            // Task dipindahkan ke parent baru: Buat menjadi YANG PERTAMA di siblings
            $minOrder = Task::where('parent_id', $newParentId)
                ->where('user_id', Auth::id())
                ->where('id', '!=', $task->id)
                ->min('order') ?? 1;  // Default 1 jika kosong
            
            // Set order lebih kecil dari yang terkecil (jadi yang pertama)
            $task->order = $minOrder - 1;
            $task->save();
        } else {
            // Task dikembalikan ke root (parent_id = null): Buat menjadi YANG TERAKHIR di root
            $maxOrder = Task::whereNull('parent_id')
                ->where('user_id', Auth::id())
                ->where('id', '!=', $task->id)
                ->max('order') ?? 0;
            
            // Set order setelah yang terbesar
            $task->order = $maxOrder + 1;
            $task->save();
        }

        // Update level untuk semua descendants
        $this->updateDescendantsLevel($task);
    }

    /**
     * Update level untuk semua descendants secara rekursif
     */
    private function updateDescendantsLevel($task)
    {
        $children = Task::where('parent_id', $task->id)->get();
        
        foreach ($children as $child) {
            $child->level = $task->level + 1;
            $child->save();
            
            // Rekursif untuk grandchildren
            $this->updateDescendantsLevel($child);
        }
    }

    protected function updateParentRecursively($task, $newFinish, $childLevel = null)
    {
        if (!$task->start || !$task->finish) {
            return;
        }

        $taskStart  = Carbon::parse($task->start, 'Asia/Jakarta')->startOfDay();
        $taskFinish = Carbon::parse($task->finish, 'Asia/Jakarta')->startOfDay();
        $newFinish  = Carbon::parse($newFinish, 'Asia/Jakarta')->startOfDay();

        if ($childLevel === null) {
            $childLevel = $task->level + 1;
        }

        if ($newFinish > $taskFinish) {
            if ($childLevel == 1) {
                $task->finish   = $newFinish;
                $task->duration = intval($taskStart->diffInDays($newFinish)) + 1;
                $task->save();
                
                if ($task->level > 0 && $task->parent_id) {
                    $root = $this->getRootTask($task);
                    if ($root && $root->id !== $task->id) {
                        $rootStart = Carbon::parse($root->start, 'Asia/Jakarta')->startOfDay();
                        $rootFinish = Carbon::parse($root->finish, 'Asia/Jakarta')->startOfDay();
                        if ($newFinish > $rootFinish) {
                            $root->finish   = $newFinish;
                            $root->duration = intval($rootStart->diffInDays($newFinish)) + 1;
                            $root->save();
                        }
                    }
                }
            } 
            else if ($childLevel >= 2) {
                $root = $this->getRootTask($task);
                if ($root && $root->id !== $task->id) {
                    $rootStart = Carbon::parse($root->start, 'Asia/Jakarta')->startOfDay();
                    $rootFinish = Carbon::parse($root->finish, 'Asia/Jakarta')->startOfDay();
                    if ($newFinish > $rootFinish) {
                        $root->finish   = $newFinish;
                        $root->duration = intval($rootStart->diffInDays($newFinish)) + 1;
                        $root->save();
                    }
                }
            }
            else if ($childLevel == 0) {
                $task->finish   = $newFinish;
                $task->duration = intval($taskStart->diffInDays($newFinish)) + 1;
                $task->save();
            }
        }

        if ($task->parent_id) {
            $parent = Task::find($task->parent_id);
            if ($parent && $parent->user_id === Auth::id()) {
                $this->updateParentRecursively($parent, $newFinish, $childLevel);
            }
        }
    }

    protected function getRootTask($task)
    {
        while ($task->parent_id) {
            $task = Task::find($task->parent_id);
        }
        return $task;
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