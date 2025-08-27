@extends('layouts.app')

@section('content')
<div class="max-w-lg mx-auto bg-white shadow-md rounded-lg p-6">
    <h2 class="text-xl font-bold mb-4">Tambah Task Baru</h2>

    <form action="{{ route('tasks.store') }}" method="POST" id="taskForm">
        @csrf

        <div class="mb-3">
            <label for="parent_id" class="block font-medium">Pilih Task Utama (Jika Sub-Task)</label>
            <select name="parent_id" id="parent_id" 
                    class="w-full border rounded p-2 @error('parent_id') border-red-500 @enderror">
                <option value="">-- Tidak ada (Task Baru) --</option>
                @foreach($parents as $parent)
                    <option value="{{ $parent->id }}" 
                            {{ old('parent_id') == $parent->id ? 'selected' : '' }}>
                        {{ $parent->name }}
                    </option>
                @endforeach
            </select>
            @error('parent_id')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-3">
            <label for="name" class="block font-medium">Nama Task</label>
            <input type="text" name="name" id="name" 
                   value="{{ old('name') }}" 
                   class="w-full border rounded p-2 @error('name') border-red-500 @enderror" 
                   required>
            @error('name')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-3">
            <label for="start" class="block font-medium">Tanggal Mulai</label>
            <input type="date" name="start" id="start" 
                   value="{{ old('start') }}" 
                   class="w-full border rounded p-2 @error('start') border-red-500 @enderror" 
                   required>
            @error('start')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-3">
            <label for="finish" class="block font-medium">Tanggal Selesai</label>
            <input type="date" name="finish" id="finish" 
                   value="{{ old('finish') }}" 
                   class="w-full border rounded p-2 @error('finish') border-red-500 @enderror">
            @error('finish')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-3">
            <label for="duration" class="block font-medium">Durasi (hari)</label>
            <input type="number" name="duration" id="duration" 
                   value="{{ old('duration') }}" 
                   class="w-full border rounded p-2 @error('duration') border-red-500 @enderror" 
                   min="1">
            @error('duration')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-3">
            <label for="description" class="block font-medium">Deskripsi (opsional)</label>
            <textarea name="description" id="description" 
                      class="w-full border rounded p-2 @error('description') border-red-500 @enderror" 
                      rows="3" 
                      placeholder="Masukkan deskripsi task...">{{ old('description') }}</textarea>
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
                Simpan Task
            </button>
        </div>
    </form>
</div>
@endsection

<!-- Tambahkan CDN jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Tambahkan CDN Select2 -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<!-- Tambahkan logika JavaScript -->
<script>
    $(document).ready(function() {
        // Inisialisasi Select2
        $('#parent_id').select2({
            placeholder: "Kosongi jika task baru",
            allowClear: true,
            width: '100%'
        });

        let lastChanged = ''; // Lacak field terakhir yang diubah

        // Event listener dengan tracking lastChanged
        $('#start').on('change', function() {
            lastChanged = 'start';
            updateDates();
        });
        $('#finish').on('change', function() {
            lastChanged = 'finish';
            updateDates();
        });
        $('#duration').on('change', function() {
            lastChanged = 'duration';
            updateDates();
        });

        // Fungsi untuk memperbarui tanggal atau durasi berdasarkan lastChanged
        function updateDates() {
            const startVal = $('#start').val();
            const finishVal = $('#finish').val();
            const durationVal = $('#duration').val();

            if (!startVal) return; // Butuh tanggal mulai untuk semua perhitungan

            if (lastChanged === 'finish' && finishVal) {
                // Hitung durasi jika terakhir ubah finish
                const start = new Date(startVal);
                const finish = new Date(finishVal);
                const diff = Math.ceil((finish - start) / (1000 * 60 * 60 * 24)) + 1;
                if (diff > 0) {
                    $('#duration').val(diff);
                } else {
                    alert('Tanggal Selesai tidak boleh sebelum Tanggal Mulai!');
                    $('#finish').val('');
                    $('#duration').val('');
                }
            } else if (lastChanged === 'duration' && durationVal) {
                // Hitung finish jika terakhir ubah duration
                const start = new Date(startVal);
                const finish = new Date(start);
                finish.setDate(start.getDate() + parseInt(durationVal) - 1);
                $('#finish').val(finish.toISOString().split('T')[0]);
            } else if (lastChanged === 'start') {
                if (durationVal) {
                    // Prioritaskan hitung finish berdasarkan duration jika ada
                    const start = new Date(startVal);
                    const finish = new Date(start);
                    finish.setDate(start.getDate() + parseInt(durationVal) - 1);
                    $('#finish').val(finish.toISOString().split('T')[0]);
                } else if (finishVal) {
                    // Hitung durasi berdasarkan finish jika duration kosong
                    const start = new Date(startVal);
                    const finish = new Date(finishVal);
                    const diff = Math.ceil((finish - start) / (1000 * 60 * 60 * 24)) + 1;
                    if (diff > 0) {
                        $('#duration').val(diff);
                    } else {
                        alert('Tanggal Selesai tidak boleh sebelum Tanggal Mulai!');
                        $('#finish').val('');
                        $('#duration').val('');
                    }
                }
            }
        }

        // Validasi frontend sebelum submit
        $('#taskForm').on('submit', function(e) {
            const duration = $('#duration').val();
            const start = $('#start').val();
            const finish = $('#finish').val();

            if (!duration && (!start || !finish)) {
                e.preventDefault();
                alert('Jika Durasi kosong, Tanggal Mulai dan Tanggal Selesai wajib diisi!');
            } else if (finish && start) {
                const startDate = new Date(start);
                const finishDate = new Date(finish);
                if (finishDate <= startDate) {
                    e.preventDefault();
                    alert('Tanggal Selesai harus setelah Tanggal Mulai!');
                    $('#finish').val('');
                    $('#duration').val('');
                }
            }
        });
    });
</script>