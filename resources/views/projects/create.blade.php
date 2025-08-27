@extends('layouts.app')

@section('content')
<div class="max-w-lg mx-auto bg-white shadow-md rounded-lg p-6">
    <h2 class="text-xl font-bold mb-4">Tambah Task Baru</h2>

    <form action="{{ route('tasks.store') }}" method="POST" id="taskForm">
        @csrf

        <div class="mb-3">
            <label for="parent_id" class="block font-medium">Pilih Task Utama atau Sub Task</label>
            <select name="parent_id" id="parent_id" 
                    class="w-full border rounded p-2 @error('parent_id') border-red-500 @enderror">
                <option value="">-- Tidak ada (Task Baru) --</option>
                @foreach($parents as $parent)
                    <option value="{{ $parent->id }}" 
                            data-start="{{ $parent->start }}" 
                            data-finish="{{ $parent->finish }}"
                            {{ old('parent_id') == $parent->id ? 'selected' : '' }}>
                        {{ $parent->name }}
                    </option>
                @endforeach
            </select>
            @error('parent_id')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
            <p class="text-sm text-gray-600 mt-1" id="parentInfo" hidden>
                Sub-task mulai 03-08-2025, selesai idealnya 17-08-2025. Melebihi, task utama diperpanjang.
            </p>
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

        // Event listener untuk memperbarui pesan parent info
        $('#parent_id').on('change', function() {
            const parentId = $(this).val();
            const parentInfo = $('#parentInfo');
            if (parentId) {
                parentInfo.show();
                const start = $(this).find('option:selected').data('start');
                const finish = $(this).find('option:selected').data('finish');
                const startFormatted = new Date(start).toLocaleDateString('en-GB', { day: '2-digit', month: '2-digit', year: 'numeric' });
                const finishFormatted = new Date(finish).toLocaleDateString('en-GB', { day: '2-digit', month: '2-digit', year: 'numeric' });
                parentInfo.text(`Sub-task mulai ${startFormatted}, selesai idealnya ${finishFormatted}. Melebihi, task utama diperpanjang.`);
            } else {
                parentInfo.hide();
            }
            lastChanged = 'parent';
            updateDatePickers();
            disableInvalidDates();
        });

        // Event listener dengan tracking lastChanged
        $('#start').on('change', function() {
            lastChanged = 'start';
            updateDatePickers();
            disableInvalidDates();
        });
        $('#finish').on('change', function() {
            lastChanged = 'finish';
            updateDatePickers();
        });
        $('#duration').on('change', function() {
            lastChanged = 'duration';
            updateDatePickers();
            disableInvalidDates();
        });

        // Fungsi untuk memperbarui date pickers
        function updateDatePickers() {
            const startVal = $('#start').val();
            const finishVal = $('#finish').val();
            const durationVal = $('#duration').val();
            const parentId = $('#parent_id').val();
            let parentStart = null;
            let parentFinish = null;

            if (parentId) {
                parentStart = $('#parent_id option:selected').data('start');
                parentFinish = $('#parent_id option:selected').data('finish');
            }

            // Set min untuk start berdasarkan parentStart jika ada, jika tidak biarkan bebas
            if (parentStart) {
                $('#start').attr('min', parentStart);
            } else {
                $('#start').removeAttr('min'); // Biarkan bebas tanpa batas minimum
            }

            // Set min untuk finish berdasarkan start
            if (startVal) {
                $('#finish').attr('min', startVal);
            } else if (parentStart) {
                $('#finish').attr('min', parentStart); // Jika start belum diisi, gunakan parentStart
            } else {
                $('#finish').removeAttr('min'); // Biarkan bebas jika tidak ada batasan
            }

            // Hitung finish jika duration diubah
            if (lastChanged === 'duration' && durationVal && startVal) {
                const start = new Date(startVal);
                const finish = new Date(start);
                finish.setDate(start.getDate() + parseInt(durationVal) - 1);
                const newFinish = finish.toISOString().split('T')[0];
                $('#finish').val(newFinish);
                $('#finish').attr('min', startVal); // Pastikan finish tidak sebelum start
            }

            // Hitung duration jika finish diubah
            if (lastChanged === 'finish' && finishVal && startVal) {
                const start = new Date(startVal);
                const finish = new Date(finishVal);
                const diff = Math.ceil((finish - start) / (1000 * 60 * 60 * 24)) + 1;
                if (diff > 0) {
                    $('#duration').val(diff);
                }
            }

            // Validasi terhadap parent (tanpa alert, hanya set ulang)
            if (parentStart && startVal && new Date(startVal) < new Date(parentStart)) {
                $('#start').val(parentStart);
                updateDatePickers(); // Rekursif untuk menyesuaikan finish
            }
            if (parentFinish && finishVal && new Date(finishVal) > new Date(parentFinish)) {
                // Tidak perlu blok, perpanjangan di backend
            }
        }

        // Fungsi untuk menonaktifkan tanggal tidak valid di date picker
        function disableInvalidDates() {
            const parentId = $('#parent_id').val();
            let minDate = null;

            if (parentId) {
                minDate = $('#parent_id option:selected').data('start');
            } else {
                minDate = null; // Tidak ada batas minimum jika tidak ada parent
            }

            const startInput = document.getElementById('start');
            const startMin = startInput.min || minDate;

            // Menonaktifkan tanggal sebelum minDate di date picker
            const disableDates = function(input) {
                const datePicker = input;
                if (datePicker.type === 'date' && startMin) {
                    const minDateObj = new Date(startMin);
                    minDateObj.setDate(minDateObj.getDate()); // Pastikan tanggal minimum

                    const picker = datePicker;
                    picker.addEventListener('input', function() {
                        const selectedDate = new Date(this.value);
                        if (selectedDate < minDateObj) {
                            this.value = startMin; // Set ulang ke min jika invalid
                        }
                    });
                }
            };

            disableDates(document.getElementById('start'));
            disableDates(document.getElementById('finish')); // Terapkan juga pada finish
        }

        // Panggil saat load untuk set initial min dan disable tanggal
        updateDatePickers();
        disableInvalidDates();

        // Validasi frontend sebelum submit
        $('#taskForm').on('submit', function(e) {
            const duration = $('#duration').val();
            const start = $('#start').val();
            const finish = $('#finish').val();
            const parentId = $('#parent_id').val();
            const parentStart = parentId ? $('#parent_id option:selected').data('start') : null;
            const parentFinish = parentId ? $('#parent_id option:selected').data('finish') : null;

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
                if (parentStart && new Date(start) < new Date(parentStart)) {
                    e.preventDefault();
                    alert('Tanggal Mulai sub-task tidak boleh sebelum Tanggal Mulai task utama!');
                    $('#start').val(parentStart);
                }
                if (parentFinish && new Date(finish) > new Date(parentFinish)) {
                    alert('Tanggal Selesai sub-task melebihi task utama. Task utama akan diperpanjang.');
                }
            }
        });
    });
</script>