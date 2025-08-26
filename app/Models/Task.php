<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Task extends Model
{
    protected $fillable = [
        'name', 'parent_id', 'duration', 'start', 'finish', 'progress', 'level', 'order'
    ];

    // 🔑 Casting field tanggal ke Carbon instance
    protected $casts = [
        'start' => 'datetime',
        'finish' => 'datetime',
        'order' => 'integer', // Casting untuk kolom order
    ];

    // Relasi ke children (bisa nested)
    public function children(): HasMany
    {
        return $this->hasMany(Task::class, 'parent_id')->with('children')->orderBy('order');
    }

    // Relasi ke parent
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Task::class, 'parent_id');
    }

    // Helper untuk mendapatkan level/depth
    public function getDepthAttribute(): int
    {
        $depth = 0;
        $parent = $this->parent;

        while ($parent) {
            $depth++;
            $parent = $parent->parent;
        }

        return $depth;
    }

    // Helper untuk mendapatkan semua keturunan (descendants)
    public function getDescendants(): array
    {
        $descendants = [];
        foreach ($this->children as $child) {
            $descendants[] = $child->id;
            $descendants = array_merge($descendants, $child->getDescendants());
        }
        return $descendants;
    }

    // Helper untuk mengatur urutan tugas
    public static function reorderTasks(array $taskIds): void
    {
        foreach ($taskIds as $index => $taskId) {
            self::where('id', $taskId)->update(['order' => $index + 1]);
        }
    }
}