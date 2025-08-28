@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50 py-6 px-4 sm:px-6 lg:px-8">
    <div class="max-w-7xl mx-auto">
        <!-- Header horizontal seperti gambar -->
        <div class="flex items-center mb-8">
    <div class="flex items-center justify-center w-12 h-12 bg-gradient-to-r from-blue-500 to-indigo-600 rounded-xl shadow-lg mr-4">
        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                 d="M9 5h6M9 9h6m-7 4h8m-5 4h2M4 6h16M4 6a2 2 0 012-2h12a2 2 0 012 2v12a2 2 0 01-2 2H6a2 2 0 01-2-2V6z">
            </path>
        </svg>
    </div>
    <div>
        <h1 class="text-2xl font-bold text-slate-900">Tambah Task Baru</h1>
        <p class="text-slate-600">Kelola dan atur semua task pemasukan</p>
    </div>
</div>


        <!-- Main Form Card -->
        <div class="bg-white rounded-3xl shadow-xl border border-slate-200/50 overflow-hidden">
            <div class="p-6 lg:p-8">
                <form action="{{ route('tasks.store') }}" method="POST" id="taskForm">
                    @csrf

                    <!-- Row 1: Task Name & Parent Task Selection -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                        <!-- Task Name (First Column) -->
                        <div class="space-y-3">
                            <label for="name" class="flex items-center text-sm font-semibold text-slate-700">
                                <div class="w-2 h-2 bg-blue-500 rounded-full mr-3"></div>
                                Nama Task <span class="text-red-500 text-sm ml-1">*</span>
                            </label>
                            <div class="relative">
                                <input type="text" name="name" id="name" 
                                       value="{{ old('name') }}" 
                                       class="w-full px-4 py-3 bg-slate-50 border-2 rounded-xl text-slate-900 placeholder-slate-400
                                              focus:bg-white focus:outline-none focus:ring-4 focus:ring-blue-500/10 transition-all duration-200
                                              @error('name') border-red-300 focus:border-red-500 @else border-slate-200 focus:border-blue-500 @enderror" 
                                       placeholder="Masukkan nama task..."
                                       required>
                                @error('name')
                                    <div class="mt-2 flex items-center space-x-2 text-red-600">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        <span class="text-sm">{{ $message }}</span>
                                    </div>
                                @enderror
                            </div>
                        </div>

                        <!-- Parent Task Selection (Second Column) -->
                        <div class="space-y-3">
                            <label for="parent_id" class="flex items-center text-sm font-semibold text-slate-700">
                                <div class="w-2 h-2 bg-blue-500 rounded-full mr-3"></div>
                                Pilih Task Utama atau Sub Task
                            </label>
                            <div class="relative">
                                <select name="parent_id" id="parent_id" 
                                        class="w-full pl-4 pr-10 py-3 bg-slate-50 border-2 rounded-xl text-slate-900 
                                               focus:border-blue-500 focus:bg-white focus:outline-none focus:ring-4 focus:ring-blue-500/10 
                                               transition-all duration-200 appearance-none
                                               @error('parent_id') border-red-300 focus:border-red-500 @else border-slate-200 @enderror">
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
                                <div class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none">
                                    <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </div>
                            </div>
                            @error('parent_id')
                                <div class="mt-2 flex items-center space-x-2 text-red-600">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <span class="text-sm">{{ $message }}</span>
                                </div>
                            @enderror
                        </div>
                    </div>

                    <!-- Parent Info (Full Width) -->
                    <div class="mb-6">
                        <div class="p-4 bg-blue-50 border border-blue-200 rounded-xl" id="parentInfo" hidden>
                            <div class="flex items-start space-x-3">
                                <svg class="w-5 h-5 text-blue-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <div class="text-sm text-blue-800">
                                    <p class="font-medium">Informasi Task Utama</p>
                                    <p id="parentInfoText">Sub-task mulai 03-08-2025, selesai idealnya 17-08-2025. Melebihi, task utama diperpanjang.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Row 2: Date Fields & Duration -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                        <!-- Start Date -->
                        <div class="space-y-3">
    <label for="start" class="flex items-center text-sm font-semibold text-slate-700">
        <svg class="w-4 h-4 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2z"></path>
        </svg>
        Tanggal Mulai <span class="text-red-500 text-sm ml-1">*</span>
    </label>
    <div class="relative">
        <input type="text" name="start" id="start"
               value="{{ old('start') }}"
               class="w-full px-4 py-3 bg-slate-50 border-2 rounded-xl text-slate-900
                      focus:bg-white focus:outline-none focus:ring-4 focus:ring-blue-500/10 transition-all duration-200"
               required>
    </div>
</div>

<!-- End Date -->
<div class="space-y-3">
    <label for="finish" class="flex items-center text-sm font-semibold text-slate-700">
        <svg class="w-4 h-4 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
        </svg>
        Tanggal Selesai
    </label>
    <div class="relative">
        <input type="text" name="finish" id="finish"
               value="{{ old('finish') }}"
               class="w-full px-4 py-3 bg-slate-50 border-2 rounded-xl text-slate-900
                      focus:bg-white focus:outline-none focus:ring-4 focus:ring-blue-500/10 transition-all duration-200">
    </div>
</div>


                        <!-- Duration -->
                        <div class="space-y-3">
                            <label for="duration" class="flex items-center text-sm font-semibold text-slate-700">
                                <svg class="w-4 h-4 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Durasi
                            </label>
                            <div class="relative">
                                <input type="number" name="duration" id="duration" 
                                       value="{{ old('duration') }}" 
                                       min="1"
                                       class="w-full px-4 py-3 bg-slate-50 border-2 rounded-xl text-slate-900 placeholder-slate-400
                                              focus:bg-white focus:outline-none focus:ring-4 focus:ring-blue-500/10 transition-all duration-200
                                              @error('duration') border-red-300 focus:border-red-500 @else border-slate-200 focus:border-blue-500 @enderror" 
                                       placeholder="Durasi (hari)...">
                                @error('duration')
                                    <div class="mt-2 flex items-center space-x-2 text-red-600">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        <span class="text-sm">{{ $message }}</span>
                                    </div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Row 3: Description (Full Width) -->
                    <div class="mb-8">
                        <div class="space-y-3">
                            <label for="description" class="flex items-center text-sm font-semibold text-slate-700">
                                <svg class="w-4 h-4 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                Deskripsi <span class="text-slate-500 text-xs">(opsional)</span>
                            </label>
                            <textarea name="description" id="description" 
                                      rows="3"
                                      class="w-full px-4 py-3 bg-slate-50 border-2 rounded-xl text-slate-900 placeholder-slate-400
                                             focus:bg-white focus:outline-none focus:ring-4 focus:ring-blue-500/10 transition-all duration-200 resize-none
                                             @error('description') border-red-300 focus:border-red-500 @else border-slate-200 focus:border-blue-500 @enderror" 
                                      placeholder="Masukkan deskripsi task secara detail...">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="mt-2 flex items-center space-x-2 text-red-600">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <span class="text-sm">{{ $message }}</span>
                                </div>
                            @enderror
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex flex-col sm:flex-row gap-4 justify-center sm:justify-end">
                        <a href="{{ route('tasks.index') }}" 
                           class="flex items-center justify-center px-8 py-3 bg-slate-100 hover:bg-slate-200 text-slate-700 
                                  font-semibold rounded-xl transition-all duration-200 hover:shadow-md border border-slate-200
                                  focus:outline-none focus:ring-4 focus:ring-slate-500/10">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                            </svg>
                            Batal
                        </a>
                        
                        <button type="submit" 
                                class="flex items-center justify-center px-8 py-3 bg-gradient-to-r from-blue-500 to-indigo-600 
                                       hover:from-blue-600 hover:to-indigo-700 text-white font-semibold rounded-xl 
                                       transition-all duration-200 hover:shadow-lg hover:shadow-blue-500/25 
                                       focus:outline-none focus:ring-4 focus:ring-blue-500/20
                                       transform hover:scale-[1.02] active:scale-[0.98]">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path>
                            </svg>
                            Simpan Task
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

<!-- Tambahkan CDN jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<!-- Tambahkan CDN Select2 -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<!-- Custom Select2 Styling -->
<style>
.select2-container--default .select2-selection--single {
    height: 48px !important;
    border: 2px solid #e2e8f0 !important;
    border-radius: 12px !important;
    background-color: #f8fafc !important;
    padding: 0 16px !important;
    display: flex !important;
    align-items: center !important;
}

.select2-container--default .select2-selection--single .select2-selection__rendered {
    color: #0f172a !important;
    line-height: 48px !important;
    padding: 0 !important;
}

.select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 48px !important;
    right: 16px !important;
}

.select2-container--default.select2-container--focus .select2-selection--single {
    border-color: #3b82f6 !important;
    background-color: white !important;
    box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1) !important;
}

.select2-dropdown {
    border: 2px solid #e2e8f0 !important;
    border-radius: 12px !important;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04) !important;
}

.select2-container--default .select2-results__option--highlighted[aria-selected] {
    background-color: #3b82f6 !important;
}

.select2-container--default .select2-search--dropdown .select2-search__field {
    border: 2px solid #e2e8f0 !important;
    border-radius: 8px !important;
    padding: 8px 12px !important;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .grid-cols-3 {
        grid-template-columns: repeat(1, minmax(0, 1fr));
    }
}
</style>

<script>
$(document).ready(function() {
    // Inisialisasi Select2
    $('#parent_id').select2({
        placeholder: "Kosongi jika task baru",
        allowClear: true,
        width: '100%'
    });

    let lastChanged = ''; // track field terakhir diubah

    // Inisialisasi Flatpickr Start & Finish
    const startPicker = flatpickr("#start", {
        dateFormat: "d-m-Y",
        locale: "id",
        allowInput: true,
        onChange: function(selectedDates) {
            if (selectedDates.length) {
                lastChanged = 'start';
                updateDatePickers();
            }
        }
    });

    const finishPicker = flatpickr("#finish", {
        dateFormat: "d-m-Y",
        locale: "id",
        allowInput: true,
        onChange: function(selectedDates) {
            if (selectedDates.length) {
                lastChanged = 'finish';
                updateDatePickers();
            }
        }
    });

    // Event duration
    $('#duration').on('change', function() {
        lastChanged = 'duration';
        updateDatePickers();
    });

    // Event parent
    $('#parent_id').on('change', function() {
        const parentId = $(this).val();
        const parentInfo = $('#parentInfo');
        const parentInfoText = $('#parentInfoText');

        if (parentId) {
            parentInfo.show();
            const start = $(this).find('option:selected').data('start'); // format yyyy-mm-dd
            const finish = $(this).find('option:selected').data('finish');
            parentInfoText.text(`Sub-task mulai ${formatDate(start)}, selesai idealnya ${formatDate(finish)}. Jika melebihi, task utama akan diperpanjang otomatis.`);
        } else {
            parentInfo.hide();
        }
        lastChanged = 'parent';
        updateDatePickers();
    });

    // Helper format yyyy-mm-dd -> dd-mm-yyyy
function formatDate(dateStr) {
    if (!dateStr) return "";
    // ambil hanya bagian tanggal sebelum spasi
    const cleanDate = dateStr.split(" ")[0]; 
    const parts = cleanDate.split("-");
    return `${parts[2]}-${parts[1]}-${parts[0]}`;
}


    // Update date pickers sesuai rules
    function updateDatePickers() {
        const startVal = $('#start').val();
        const finishVal = $('#finish').val();
        const durationVal = $('#duration').val();
        const parentId = $('#parent_id').val();

        let parentStart = null;
        let parentFinish = null;

        if (parentId) {
            parentStart = $('#parent_id option:selected').data('start'); // yyyy-mm-dd
            parentFinish = $('#parent_id option:selected').data('finish');
        }

        // Atur minDate berdasarkan parent
        if (parentStart) {
            startPicker.set('minDate', parentStart);
            finishPicker.set('minDate', parentStart);
        } else {
            startPicker.set('minDate', null);
            finishPicker.set('minDate', null);
        }

        // Jika start diisi, atur minDate finish = start
        if (startVal) {
            const parts = startVal.split("-");
            const startDate = new Date(parts[2], parts[1] - 1, parts[0]);
            finishPicker.set('minDate', startDate);
        }

        // Hitung finish dari duration
        if (lastChanged === 'duration' && durationVal && startVal) {
            const parts = startVal.split("-");
            const startDate = new Date(parts[2], parts[1] - 1, parts[0]);
            const finishDate = new Date(startDate);
            finishDate.setDate(startDate.getDate() + parseInt(durationVal) - 1);
            finishPicker.setDate(finishDate, true);
        }

        // Hitung duration dari finish
        if (lastChanged === 'finish' && finishVal && startVal) {
            const [sd, sm, sy] = startVal.split("-");
            const [fd, fm, fy] = finishVal.split("-");
            const startDate = new Date(sy, sm - 1, sd);
            const finishDate = new Date(fy, fm - 1, fd);
            const diff = Math.ceil((finishDate - startDate) / (1000 * 60 * 60 * 24)) + 1;
            if (diff > 0) {
                $('#duration').val(diff);
            }
        }

        // Validasi start < parentStart
if (parentStart && startVal) {
    const [sd, sm, sy] = startVal.split("-");
    const startDate = new Date(sy, sm - 1, sd);
    const parentDate = new Date(parentStart);

    if (startDate < parentDate) {
        // Set tanpa trigger onChange untuk hindari loop
        startPicker.setDate(parentDate, false); 
    }
}

    }

    // Validasi sebelum submit
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
            const [sd, sm, sy] = start.split("-");
            const [fd, fm, fy] = finish.split("-");
            const startDate = new Date(sy, sm - 1, sd);
            const finishDate = new Date(fy, fm - 1, fd);

            if (finishDate <= startDate) {
                e.preventDefault();
                alert('Tanggal Selesai harus setelah Tanggal Mulai!');
                $('#finish').val('');
                $('#duration').val('');
            }
            if (parentStart && startDate < new Date(parentStart)) {
                e.preventDefault();
                alert('Tanggal Mulai sub-task tidak boleh sebelum Tanggal Mulai task utama!');
                startPicker.setDate(parentStart, true);
            }
            if (parentFinish && finishDate > new Date(parentFinish)) {
    alert('Tanggal Selesai sub-task melebihi task utama. Task utama akan diperpanjang.');

    // Update task utama agar ikut mundur
    $('#parent_finish').val($('#finish').val());
}
        }
    });
});
</script>