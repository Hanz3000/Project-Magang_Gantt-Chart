<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
            ->map(function ($task) {
                $task->start = $task->start ? \Carbon\Carbon::parse($task->start)->format('d-m-Y') : null;
                $task->finish = $task->finish ? \Carbon\Carbon::parse($task->finish)->format('d-m-Y') : null;
                return $task;
            });

        return view('projects.create', compact('parents'));
    }

    public function store(Request $request)
    {
        // Rules dasar
        $rules = [
            'name'        => 'required|string|max:255',
            'parent_id'   => 'nullable|exists:tasks,id',
            'start'       => 'required|date',
            'duration'    => 'required_without:finish|nullable|integer|min:1',
            'description' => 'nullable|string',
        ];

        // Rule untuk finish: tambahkan after_or_equal:start HANYA jika root (parent_id null)
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
            'order'       => $maxOrder + 1,
            'description' => $request->description,
            'user_id'     => Auth::id(),
        ]);

        // Jika ini child, update root finish jika perlu
        if ($request->parent_id) {
            $this->ensureParentFinishCoversChildren($newTask->parent);
        }

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

        // Format dates untuk display di form (Y-m-d untuk input type="date")
        $task->start = $task->start ? Carbon::parse($task->start)->format('Y-m-d') : null;
        $task->finish = $task->finish ? Carbon::parse($task->finish)->format('Y-m-d') : null;
        $task->original_start_date = $task->start; // Untuk offset calculation
        $task->original_finish_date = $task->finish;

        $parents = Task::where('user_id', Auth::id())
            ->where('id', '!=', $task->id)
            ->whereNotIn('id', $this->getDescendantIds($task))
            ->get()
            ->map(function ($t) {
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

        // Rules dasar
        $rules = [
            'name'        => 'required|string|max:255',
            'start'       => 'required|date',
            'duration'    => 'required_without:finish|nullable|integer|min:1',
            'parent_id'   => 'nullable|exists:tasks,id',
            'description' => 'nullable|string|max:1000',
            'move_children' => 'nullable|in:0,1',
            'original_start_date' => 'nullable|date',
            'original_finish_date' => 'nullable|date',
        ];

        // Rule untuk finish: tambahkan after_or_equal:start HANYA jika root (parent_id null)
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

        $oldParentId = $task->parent_id; // Simpan parent lama
        $oldStart = $task->start; // Simpan start lama untuk hitung diff jika diperlukan

        // ===== SOLUSI OFFSET RELATIF: Hitung selisih tanggal =====
        $originalStart = $request->original_start_date
            ? Carbon::parse($request->original_start_date, 'Asia/Jakarta')->startOfDay()
            : Carbon::parse($task->start, 'Asia/Jakarta')->startOfDay();

        $newStart = Carbon::parse($request->start, 'Asia/Jakarta')->startOfDay();

        // Hitung selisih hari (sudah signed: positif jika maju, negatif jika mundur)
        $daysDiff = $originalStart->diffInDays($newStart, false);
        // =========================================================

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
        // =============================================================

        DB::beginTransaction();

        try {
            // Update task utama
            $task->update([
                'name'        => $request->name,
                'duration'    => $duration,
                'start'       => $start,
                'finish'      => $finish,
                'parent_id'   => $request->parent_id,
                'description' => $request->description,
                'level'       => $level,
            ]);

            // ===== HANDLE CHILDREN BERDASARKAN move_children DAN PERUBAHAN PARENT =====
            $moveChildren = $request->input('move_children', '1') === '1';
            $parentChanged = $oldParentId != $request->parent_id;

            $task->fresh(); // Reload task setelah update
            $task->load('children'); // Load children fresh

            if ($task->children && $task->children->count() > 0) {
                if ($moveChildren) {
                    // Jika dicentang: Pindahkan children (shift dates jika ada perubahan tanggal)
                    if ($daysDiff != 0) {
                        $this->updateChildrenWithOffset($task->children, $daysDiff);
                    }
                    // Hierarki tetap ikut (parent_id children sudah = $task->id)
                    // Setelah shift, reconfirm root finish
                    $this->ensureParentFinishCoversChildren($task);
                } else {
                    // Jika tidak dicentang: Jangan pindahkan children
                    // - Jangan shift dates
                    // - Jika parent berubah, detach children: set parent_id mereka ke oldParentId (grandparent) atau null jika root
                    if ($parentChanged) {
                        $detachToParentId = $oldParentId ?: null; // Ke grandparent atau root
                        $newLevel = $oldParentId ? ($task->fresh()->level - 1) : 0; // Adjust level untuk old parent
                        $this->detachChildrenToParent($task->children, $detachToParentId, $newLevel);
                    }
                }
            }
            // ==========================================================================

            // Reorder task utama setelah parent berubah (hanya untuk task utama, children sudah dihandle di atas)
            if ($parentChanged) {
                $this->reorderTasksAfterParentChange($task, $oldParentId, $request->parent_id);
            }

            // Update root recursively jika perlu (untuk parent baru)
            if ($request->parent_id && $parent) {
                $this->ensureParentFinishCoversChildren($parent);
            }

            // Jika ada old parent, cek apakah perlu shrink finish old parent (opsional, jika children di-detach)
            if ($parentChanged && $oldParentId) {
                $oldParent = Task::find($oldParentId);
                if ($oldParent) {
                    $this->updateParentFinishIfNeeded($oldParent);
                }
            }

            DB::commit();

            // Normalize orders setelah semua update
            $this->normalizeOrders();

            $successMessage = 'Task berhasil diupdate!';
            if ($task->children()->count() > 0) {
                if ($moveChildren) {
                    if ($daysDiff != 0) {
                        $successMessage .= ' Sub-task juga telah dipindahkan dengan offset yang sama.';
                    } else {
                        $successMessage .= ' Sub-task tetap mengikuti hierarki.';
                    }
                } else {
                    $successMessage .= ' Sub-task dibiarkan di posisi semula.';
                }
                // Tambah info jika durasi disesuaikan karena children
                if ($adjustedDueToChildren) {
                    $successMessage .= ' Durasi task disesuaikan untuk menutupi sub-task.';
                }
            }

            return redirect()->route('tasks.index')
                ->with('success', $successMessage);
        } catch (\Exception $e) {
            DB::rollback();
            return back()->withErrors(['error' => 'Gagal update task: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Get max finish dari semua descendants (rekursif)
     */
    private function getMaxDescendantFinish($task)
    {
        $maxFinish = null;
        $children = $task->children;

        foreach ($children as $child) {
            $childFinish = $child->finish ? Carbon::parse($child->finish, 'Asia/Jakarta')->startOfDay() : null;
            if ($childFinish && (!$maxFinish || $childFinish > $maxFinish)) {
                $maxFinish = $childFinish;
            }
            // Rekursif untuk sub-children
            $subMax = $this->getMaxDescendantFinish($child);
            if ($subMax && (!$maxFinish || $subMax > $maxFinish)) {
                $maxFinish = $subMax;
            }
        }

        return $maxFinish;
    }

    /**
     * Ensure root finish covers all children in the tree (extend hanya root jika perlu)
     */
    private function ensureParentFinishCoversChildren($parent)
    {
        if (!$parent || !$parent->start) {
            return;
        }

        $maxChildFinish = $this->getMaxDescendantFinish($parent);
        if ($maxChildFinish) {
            // Cari root task
            $root = $this->getRootTask($parent);
            $rootStart = Carbon::parse($root->start, 'Asia/Jakarta')->startOfDay();
            $rootCurrentFinish = Carbon::parse($root->finish, 'Asia/Jakarta')->startOfDay();

            if ($maxChildFinish > $rootCurrentFinish) {
                $root->finish = $maxChildFinish;
                $root->duration = intval($rootStart->diffInDays($maxChildFinish) + 1);
                $root->save();
            }
            // Tidak extend intermediate parents, hanya root
        }
    }

    /**
     * Update children dengan offset relatif
     */
    private function updateChildrenWithOffset($children, $daysDiff)
    {
        foreach ($children as $child) {
            // Hitung tanggal baru untuk child dengan menambahkan offset
            if ($child->start) {
                $newChildStart = Carbon::parse($child->start, 'Asia/Jakarta')
                    ->startOfDay()
                    ->addDays($daysDiff);
                $child->start = $newChildStart;
            }

            if ($child->finish) {
                $newChildFinish = Carbon::parse($child->finish, 'Asia/Jakarta')
                    ->startOfDay()
                    ->addDays($daysDiff);
                $child->finish = $newChildFinish;
            }

            // Update duration berdasarkan new dates
            if ($child->start && $child->finish) {
                $child->duration = intval($child->start->diffInDays($child->finish) + 1);
            }

            $child->save();

            // Rekursif untuk grandchildren (cucu)
            $child->load('children');
            if ($child->children && $child->children->count() > 0) {
                $this->updateChildrenWithOffset($child->children, $daysDiff);
            }
        }
    }

    /**
     * Detach children ke parent lama atau root
     */
    private function detachChildrenToParent($children, $targetParentId, $newLevel)
    {
        foreach ($children as $child) {
            // Set parent_id baru ke target (old parent atau null)
            $child->parent_id = $targetParentId;
            $child->level = $newLevel + 1; // Adjust level

            // Set order sebagai yang terakhir di target parent
            $maxOrderInTarget = Task::where('parent_id', $targetParentId)
                ->where('user_id', Auth::id())
                ->max('order') ?? 0;
            $child->order = $maxOrderInTarget + 1;

            $child->save();

            // Rekursif untuk sub-children (grandchildren asli)
            $child->load('children');
            if ($child->children && $child->children->count() > 0) {
                $this->detachChildrenToParent($child->children, $child->id, $child->level);
            }
        }
    }

    /**
     * Update finish parent jika children di-detach
     */
    private function updateParentFinishIfNeeded($parent)
    {
        $maxFinishStr = $parent->children()->max('finish');
        $maxFinish = $maxFinishStr ? Carbon::parse($maxFinishStr, 'Asia/Jakarta')->startOfDay() : null;

        if ($maxFinish && $maxFinish < Carbon::parse($parent->finish)) {
            $parentStart = Carbon::parse($parent->start, 'Asia/Jakarta')->startOfDay();
            $parent->finish = $maxFinish;
            $parent->duration = intval($parentStart->diffInDays($maxFinish) + 1);
            $parent->save();

            // Rekursif ke atas jika perlu (untuk shrink, extend ke root jika max keseluruhan berubah)
            if ($parent->parent_id) {
                $grandParent = Task::find($parent->parent_id);
                if ($grandParent) {
                    $this->updateParentFinishIfNeeded($grandParent);
                }
            }
        }
    }

    /**
     * Fungsi untuk reorder tasks setelah parent berubah
     */
    private function reorderTasksAfterParentChange($task, $oldParentId, $newParentId)
    {
        if ($newParentId) {
            // Task dipindahkan ke parent baru: Buat menjadi YANG PERTAMA di siblings
            $minOrder = Task::where('parent_id', $newParentId)
                ->where('user_id', Auth::id())
                ->where('id', '!=', $task->id)
                ->min('order') ?? 1;

            $task->order = $minOrder - 1;
            $task->save();
        } else {
            // Task dikembalikan ke root (parent_id = null): Buat menjadi YANG TERAKHIR di root
            $maxOrder = Task::whereNull('parent_id')
                ->where('user_id', Auth::id())
                ->where('id', '!=', $task->id)
                ->max('order') ?? 0;

            $task->order = $maxOrder + 1;
            $task->save();
        }
    }

    /**
     * Update level untuk semua descendants secara rekursif
     * (Tidak digunakan lagi karena dihandle di detach)
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
        // Deprecated: Gunakan ensureParentFinishCoversChildren instead
        $this->ensureParentFinishCoversChildren($task);
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

    /**
     * Delete task dan semua descendants secara rekursif
     */
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

        // Jika ada parent, update parent finish setelah delete (shrink jika perlu)
        $parentId = $task->parent_id;
        try {
            $this->deleteRecursive($task);

            if ($parentId) {
                $parent = Task::find($parentId);
                if ($parent) {
                    $this->updateParentFinishIfNeeded($parent);
                }
            }

            return redirect()->route('tasks.index')
                ->with('success', 'Task dan semua sub-task-nya berhasil dihapus!');
        } catch (\Exception $e) {
            return redirect()->route('tasks.index')
                ->with('error', 'Terjadi kesalahan saat menghapus task: ' . $e->getMessage());
        }
    }
}
