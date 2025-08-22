{{-- resources/views/tasks/edit.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="max-w-lg mx-auto bg-white shadow-md rounded-lg p-6">
    <h2 class="text-xl font-bold mb-4">Edit Task</h2>

    <form action="{{ route('tasks.update', $task->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label for="name" class="block font-medium">Nama Task</label>
            <input type="text" name="name" id="name" 
                   value="{{ old('name', $task->name) }}" 
                   class="w-full border rounded p-2 @error('name') border-red-500 @enderror" 
                   required>
            @error('name')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-3">
            <label for="duration" class="block font-medium">Durasi (hari)</label>
            <input type="number" name="duration" id="duration" 
                   value="{{ old('duration', $task->duration) }}" 
                   class="w-full border rounded p-2 @error('duration') border-red-500 @enderror" 
                   min="1" required>
            @error('duration')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-3">
            <label for="start" class="block font-medium">Tanggal Mulai</label>
            <input type="date" name="start" id="start" 
                   value="{{ old('start', $task->start ? $task->start->format('Y-m-d') : '') }}" 
                   class="w-full border rounded p-2 @error('start') border-red-500 @enderror" 
                   required>
            @error('start')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-3">
            <label for="parent_id" class="block font-medium">Task Induk (opsional)</label>
            <select name="parent_id" id="parent_id" 
                    class="w-full border rounded p-2 @error('parent_id') border-red-500 @enderror">
                <option value="">-- Tidak ada --</option>
                @foreach($parents as $parent)
                    <option value="{{ $parent->id }}" 
                            {{ old('parent_id', $task->parent_id) == $parent->id ? 'selected' : '' }}>
                        {{ $parent->name }}
                    </option>
                @endforeach
            </select>
            @error('parent_id')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-3">
            <label for="description" class="block font-medium">Deskripsi (opsional)</label>
            <textarea name="description" id="description" 
                      class="w-full border rounded p-2 @error('description') border-red-500 @enderror" 
                      rows="3" 
                      placeholder="Masukkan deskripsi task...">{{ old('description', $task->description) }}</textarea>
            @error('description')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex justify-between">
            <a href="{{ route('tasks.index') }}" 
               class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded">
                Batal
            </a>
            <button type="submit" 
                    class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">
                Update Task
            </button>
        </div>
    </form>
</div>
@endsection