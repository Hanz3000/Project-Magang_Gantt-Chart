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
            'tasks' => $tasks, // Data asli untuk rendering rekursif di Blade
            'structuredTasks' => $structuredTasks, // Data untuk JavaScript
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
                'children' => [] // Tambahkan children kosong untuk konsistensi
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
            'name' => 'required|string|max:255',
            'duration' => 'required|integer|min:1',
            'start' => 'required|date',
            'parent_id' => 'nullable|exists:tasks,id',
        ]);

        $start = new Carbon($request->start);
        $finish = (clone $start)->addDays($request->duration - 1);

        $level = 0;
        if ($request->parent_id) {
            $parent = Task::find($request->parent_id);
            $level = $parent ? $parent->level + 1 : 0;
        }

        // Tentukan order untuk tugas baru (misalnya, di akhir urutan)
        $maxOrder = Task::max('order') ?? 0;

        Task::create([
            'name' => $request->name,
            'parent_id' => $request->parent_id,
            'duration' => $request->duration,
            'start' => $request->start,
            'finish' => $finish,
            'progress' => 0,
            'level' => $level,
            'order' => $maxOrder + 1,
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
            $descendantIds = $task->getDescendants();
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
            'finish' => Carbon::parse($request->start)->addDays($request->duration - 1),
            'level' => $request->parent_id ? (Task::find($request->parent_id)->level + 1) : 0,
        ]);

        // Ambil semua tugas dengan urutan berdasarkan kolom order
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

        // Bangun peta tugas tanpa memodifikasi properti children
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

        // Atur hierarki tugas
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