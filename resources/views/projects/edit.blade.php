<!-- resources/views/tasks/edit.blade.php -->
@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50 py-6 px-4 sm:px-6 lg:px-8">
    <div class="max-w-7xl mx-auto">
        <!-- Header horizontal seperti gambar -->
        <div class="flex items-center mb-8">
            <div class="flex items-center justify-center w-12 h-12 bg-gradient-to-r from-blue-500 to-indigo-600 rounded-xl shadow-lg mr-4">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                         d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                    </path>
                </svg>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-slate-900">Edit Task</h1>
                <p class="text-slate-600">Perbarui informasi task yang dipilih</p>
            </div>
        </div>

        <!-- Main Form Card -->
        <div class="bg-white rounded-3xl shadow-xl border border-slate-200/50 overflow-hidden">
            <div class="p-6 lg:p-8">
                <form action="{{ route('tasks.update', $task->id) }}" method="POST" id="taskForm">
                    @csrf
                    @method('PUT')

                    <!-- Hidden fields untuk tracking perubahan tanggal -->
                    <input type="hidden" name="original_start_date" id="original_start_date" value="{{ $task->start ? $task->start->format('Y-m-d') : '' }}">
                    <input type="hidden" name="original_finish_date" id="original_finish_date" value="{{ $task->finish ? $task->finish->format('Y-m-d') : '' }}">
                    <input type="hidden" name="move_children" id="move_children" value="1">

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
                                       value="{{ old('name', $task->name) }}" 
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
                                                data-start="{{ $parent->start ? $parent->start->format('Y-m-d') : '' }}" 
                                                data-finish="{{ $parent->finish ? $parent->finish->format('Y-m-d') : '' }}"
                                                {{ old('parent_id', $task->parent_id) == $parent->id ? 'selected' : '' }}>
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
                        <div class="p-4 bg-blue-50 border border-blue-200 rounded-xl" id="parentInfo" 
                             @if(!$task->parent_id) hidden @endif>
                            <div class="flex items-start space-x-3">
                                <svg class="w-5 h-5 text-blue-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <div class="text-sm text-blue-800">
                                    <p class="font-medium">Informasi Task Utama</p>
                                    <p id="parentInfoText">
                                        @if($task->parent_id && $task->parent)
                                            Sub-task dimulai {{ \Carbon\Carbon::parse($task->parent->start)->format('d-m-Y') }} dan selesai {{ \Carbon\Carbon::parse($task->parent->finish)->format('d-m-Y') }}.
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Warning untuk task dengan anak -->
                    @if($task->children && $task->children->count() > 0)
                    <div class="mb-6">
                        <div class="p-4 bg-amber-50 border border-amber-200 rounded-xl">
                            <div class="flex items-start space-x-3">
                                <svg class="w-5 h-5 text-amber-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                </svg>
                                <div class="flex-1">
                                    <div class="text-sm text-amber-800">
                                        <p class="font-medium mb-2">Task ini memiliki {{ $task->children->count() }} sub-task</p>
                                        <div class="space-y-1 mb-3">
                                            @foreach($task->children as $child)
                                            <div class="text-xs bg-white/50 px-2 py-1 rounded">
                                                • {{ $child->name }} 
                                                <span class="text-amber-600">
                                                    ({{ $child->start ? $child->start->format('d/m/Y') : '-' }} - {{ $child->finish ? $child->finish->format('d/m/Y') : '-' }})
                                                </span>
                                            </div>
                                            @endforeach
                                        </div>
                                        <label class="flex items-center space-x-2 cursor-pointer">
                                            <input type="checkbox" id="move_children_checkbox" checked 
                                                   class="w-4 h-4 text-blue-600 bg-white border-amber-300 rounded focus:ring-blue-500 focus:ring-2">
                                            <span class="font-medium">Pindahkan juga semua sub-task (pertahankan jarak relatif)</span>
                                        </label>
                                        <p class="text-xs mt-2 text-amber-700">
                                            ✓ Jika dicentang: Sub-task akan ikut bergeser sesuai perubahan tanggal task ini<br>
                                            ✗ Jika tidak: Hanya task ini yang akan dipindah, sub-task tetap di posisi semula
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

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
                                <input type="date" name="start" id="start"
                                       value="{{ old('start', $task->start ? $task->start->format('Y-m-d') : '') }}"
                                       class="w-full px-4 py-3 bg-slate-50 border-2 rounded-xl text-slate-900
                                              focus:bg-white focus:outline-none focus:ring-4 focus:ring-blue-500/10 transition-all duration-200
                                              @error('start') border-red-300 focus:border-red-500 @else border-slate-200 focus:border-blue-500 @enderror"
                                       required>
                                @error('start')
                                    <div class="mt-2 flex items-center space-x-2 text-red-600">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        <span class="text-sm">{{ $message }}</span>
                                    </div>
                                @enderror
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
                                <input type="date" name="finish" id="finish"
                                       value="{{ old('finish', $task->finish ? $task->finish->format('Y-m-d') : '') }}"
                                       class="w-full px-4 py-3 bg-slate-50 border-2 rounded-xl text-slate-900
                                              focus:bg-white focus:outline-none focus:ring-4 focus:ring-blue-500/10 transition-all duration-200
                                              @error('finish') border-red-300 focus:border-red-500 @else border-slate-200 focus:border-blue-500 @enderror">
                                @error('finish')
                                    <div class="mt-2 flex items-center space-x-2 text-red-600">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        <span class="text-sm">{{ $message }}</span>
                                    </div>
                                @enderror
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
                                       value="{{ old('duration', $task->duration) }}" 
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
                                      placeholder="Masukkan deskripsi task secara detail...">{{ old('description', $task->description) }}</textarea>
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
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                            </svg>
                            Update Task
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
            placeholder: "Kosongi jika tidak ada induk",
            allowClear: true,
            width: '100%'
        });

        let lastChanged = ''; // Lacak field terakhir diubah

        // Sync checkbox dengan hidden input
        $('#move_children_checkbox').on('change', function() {
            $('#move_children').val(this.checked ? '1' : '0');
        });

        // Event listener pilih parent
        $('#parent_id').on('change', function() {
            const parentId = $(this).val();
            const parentInfo = $('#parentInfo');
            const parentInfoText = $('#parentInfoText');

            if (parentId) {
                parentInfo.show();
                const parentStart = $(this).find('option:selected').data('start');
                const parentFinish = $(this).find('option:selected').data('finish');
                const startFormatted = new Date(parentStart).toLocaleDateString('en-GB'); // ✅ BENAR
const finishFormatted = new Date(parentFinish).toLocaleDateString('en-GB');
                parentInfoText.text(
                    `Sub-task mulai ${startFormatted}, selesai idealnya ${finishFormatted}. 
                     Melebihi, task utama diperpanjang.`
                );
                // Autofit tanggal mulai ke hari setelah tanggal selesai parent
                if (parentFinish) {
                    const start = new Date(parentFinish);
                    start.setDate(start.getDate() + 1); // Tambah 1 hari setelah tanggal selesai parent
                    $('#start').val(start.toISOString().split('T')[0]);
                    const durationVal = $('#duration').val();
                    if (durationVal) {
                        const finish = new Date(start);
                        finish.setDate(start.getDate() + parseInt(durationVal) - 1);
                        $('#finish').val(finish.toISOString().split('T')[0]);
                    }
                }
            } else {
                parentInfo.hide();
            }
            lastChanged = 'parent';
            updateDatePickers();
        });

        // Tracking perubahan field
        $('#start').on('change', function() {
            lastChanged = 'start';
            updateDatePickers();
        });
        $('#finish').on('change', function() {
            lastChanged = 'finish';
            updateDatePickers();
        });
        $('#duration').on('change', function() {
            lastChanged = 'duration';
            updateDatePickers();
        });

        // Fungsi update date picker
        function updateDatePickers() {
            const startVal = $('#start').val();
            const finishVal = $('#finish').val();
            const durationVal = $('#duration').val();

            // Hapus batasan min untuk start dan finish
            $('#start').removeAttr('min');
            $('#finish').removeAttr('min');

            // Durasi → hitung finish
            if ((lastChanged === 'start' || lastChanged === 'duration' || lastChanged === 'parent') && durationVal && startVal) {
                const start = new Date(startVal);
                const finish = new Date(start);
                finish.setDate(start.getDate() + parseInt(durationVal) - 1);
                $('#finish').val(finish.toISOString().split('T')[0]);
            }

            // Finish → hitung durasi
            if (lastChanged === 'finish' && finishVal && startVal) {
                const start = new Date(startVal);
                const finish = new Date(finishVal);
                const diff = Math.ceil((finish - start) / (1000 * 60 * 60 * 24)) + 1;
                if (diff > 0) $('#duration').val(diff);
            }
        }

        // Validasi tanggal saat input
        function validateDateInputs() {
            // Validasi input finish (pastikan tidak sebelum start)
            $('#finish').off('input').on('input', function() {
                const startVal = $('#start').val();
                if (startVal) {
                    const minDateObj = new Date(startVal);
                    const selectedDate = new Date(this.value);
                    if (selectedDate < minDateObj) {
                        this.value = startVal;
                        alert('Tanggal Selesai tidak boleh sebelum Tanggal Mulai!');
                        updateDatePickers();
                    }
                }
            });
        }

        // Jalankan awal
        updateDatePickers();
        validateDateInputs();

        // Validasi sebelum submit
        $('#taskForm').on('submit', function(e) {
            const duration = $('#duration').val();
            const start = $('#start').val();
            const finish = $('#finish').val();

            // Validasi dasar
            if (!duration && (!start || !finish)) {
                e.preventDefault();
                alert('Jika Durasi kosong, Tanggal Mulai dan Tanggal Selesai wajib diisi!');
                return false;
            }

            if (finish && start) {
                const startDate = new Date(start);
                const finishDate = new Date(finish);

                if (finishDate < startDate) {
                    e.preventDefault();
                    alert('Tanggal Selesai tidak boleh sebelum Tanggal Mulai!');
                    $('#finish').val('');
                    $('#duration').val('');
                    return false;
                }
            }
        });
    });
</script>