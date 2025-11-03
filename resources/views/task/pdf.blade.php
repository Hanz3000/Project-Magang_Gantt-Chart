<!DOCTYPE html>
<html>
<head>
    {{-- Diambil dari kode lama Anda untuk judul yang lebih spesifik --}}
    <title>Gantt Chart - {{ $project->name ?? 'Project' }}</title>
    
    @php
        // ===================================================================
        // BLOK PERSIAPAN VARIABEL (Diambil dari template baru Anda)
        // ===================================================================

        // 1. Definisikan Lebar Kolom (Fixed Layout)
        $listNameWidth = 300;     // Lebar kolom Nama Tugas (px)
        $listDateWidth = 38;      // Lebar kolom Tanggal Mulai & Selesai
        $listDurationWidth = 24;  // Lebar kolom Durasi
        $ganttColumnWidth = 538;  // Lebar kolom Gantt
        
        $totalTableWidth = $listNameWidth + ($listDateWidth * 2) + $listDurationWidth + $ganttColumnWidth;

        // 2. Default Colors berdasarkan level (sama seperti Gantt JS)
        $defaultColors = [
            ['bg' => '#0078d4', 'border' => '#106ebe'],  // Level 0: Biru
            ['bg' => '#107c10', 'border' => '#0e6e0e'],  // Level 1: Hijau
            ['bg' => '#881798', 'border' => '#7a1589'],  // Level 2: Ungu
            ['bg' => '#ff8c00', 'border' => '#e67e00'],  // Level 3: Orange
            ['bg' => '#e81123', 'border' => '#d10e20'],  // Level 4: Merah
            ['bg' => '#5c2d91', 'border' => '#522982'],  // Level 5: Ungu Gelap
        ];

        // 3. Hitung Range Proyek Global (Logika canggih dari template baru Anda)
        // Ini lebih baik karena mem-parsing data asli, termasuk format "Oct 28"
        $allDates = $tasks->pluck('start')->merge($tasks->pluck('finish'))->filter();
        
        if ($allDates->isEmpty()) {
            // Fallback jika tidak ada tugas (menggunakan variabel dari kode lama Anda)
            $globalStart = \Carbon\Carbon::parse($startDate ?? '2025-10-20')->startOfDay();
            $globalEnd = \Carbon\Carbon::parse($endDate ?? '2025-11-22')->startOfDay();
        } else {
            $parsedDates = [];
            foreach ($allDates as $dateStr) {
                try {
                    // Logika parsing "Oct 28" dari template baru Anda
                    if (preg_match('/^(\w{3})\s+(\d{1,2})$/', $dateStr, $matches)) {
                        $monthMap = ['Jan' => 1, 'Feb' => 2, 'Mar' => 3, 'Apr' => 4, 'May' => 5, 'Jun' => 6,
                                     'Jul' => 7, 'Aug' => 8, 'Sep' => 9, 'Oct' => 10, 'Nov' => 11, 'Dec' => 12];
                        $month = $monthMap[$matches[1]] ?? \Carbon\Carbon::now()->month;
                        $year = \Carbon\Carbon::now()->year; 
                        $parsed = \Carbon\Carbon::create($year, $month, $matches[2], 0, 0, 0);
                    } else {
                        $parsed = \Carbon\Carbon::parse($dateStr);
                    }
                    $parsedDates[] = $parsed->startOfDay();
                } catch (\Exception $e) {
                    // Skip invalid dates
                }
            }
            $globalStart = !empty($parsedDates) ? collect($parsedDates)->min() : \Carbon\Carbon::parse($startDate ?? '2025-10-20')->startOfDay();
            $globalEnd = !empty($parsedDates) ? collect($parsedDates)->max() : \Carbon\Carbon::parse($endDate ?? '2025-11-22')->startOfDay();
        }

        // Total durasi timeline dalam hari (penting untuk kalkulasi persentase)
        $timelineDays = $globalStart->diffInDays($globalEnd) + 1;
        if ($timelineDays <= 0) {
            $timelineDays = 1; // Hindari pembagian dengan nol
        }

    @endphp
    <style>
        
        @page {
            size: A4 landscape;
            margin: 10mm;
        }
        body { 
            font-family: Arial, sans-serif; 
            font-size: 11px;
            margin: 0;
            padding: 0;
        }
        h2 {
            text-align: center;
            margin-bottom: 20px;
            font-size: 14px;
        }
        table {
            border-collapse: collapse;
            width: 100%;
        }
        .header-cell {
            background-color: #f0f0f0;
            font-weight: bold;
            text-align: center;
            border: 1px solid #e0e0e0;
            padding: 6px;
        }
        .data-cell {
            border: 1px solid #e0e0e0;
            padding: 5px; /* Padding default */
            vertical-align: middle;
            height: 25px; /* Tinggi row disamakan */
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .name-cell {
            text-align: left;
        }
        .task-indicator {
            display: inline-block;
            width: 10px;
            height: 10px;
            border-radius: 2px;
            margin-right: 5px;
            vertical-align: middle;
            border: 1px solid #fff;
        }
        .gantt-container-cell {
            border: 1px solid #e0e0e0;
            padding: 0 2px;
            vertical-align: middle;
            height: 25px; 
            position: relative;
            background-color: #fdfdfd;
            background-image: repeating-linear-gradient(to right, 
                                #f0f0f0 0, 
                                #f0f0f0 1px, 
                                transparent 1px, 
                                transparent 40px);
        }
        .gantt-bar {
            position: relative;
            height: 18px;
            margin: 3px 0;
            border-radius: 4px;
            color: white;
            font-size: 8px;
            line-height: 18px;
            text-align: left;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            padding: 0 4px;
            box-sizing: border-box;
            border: 1px solid rgba(255,255,255,0.3);
            box-shadow: 0 1px 2px rgba(0,0,0,0.1);
        }
        .empty-state {
            text-align: center;
            padding: 50px;
            color: #666;
            font-size: 12px;
        }
    </style>
</head>
<body>

    <div style="text-align: center; margin-bottom: 15px;">
        {{-- Menggunakan variabel $project dari kode lama dan $globalStart/End dari kode baru --}}
        <h2>Gantt Chart Task Hierarchy: {{ $tasks->first()->name ?? ($project->name ?? 'Project') }} ({{ $globalStart->format('M Y') }} - {{ $globalEnd->format('M Y') }})</h2>
    </div>

    @if($tasks->isEmpty())
        <div class="empty-state">
            <h3>Tidak Ada Tugas</h3>
            <p>Tambahkan tugas untuk melihat Gantt Chart.</p>
        </div>
    @else
    {{-- Struktur tabel utama dari template baru Anda --}}
    <table style="width: {{ $totalTableWidth }}px; table-layout: fixed;">
        <thead>
            <tr>
                <th class="header-cell" style="width: {{ $listNameWidth }}px;">Nama Tugas</th>
                <th class="header-cell" style="width: {{ $listDateWidth }}px;">Tanggal Mulai</th>
                <th class="header-cell" style="width: {{ $listDateWidth }}px;">Tanggal Selesai</th>
                <th class="header-cell" style="width: {{ $listDurationWidth }}px;">Durasi</th>
                <th class="header-cell" style="width: {{ $ganttColumnWidth }}px;"> </th>
            </tr>
        </thead>
        <tbody>
            @foreach ($tasks as $task)
                @php
                    // 1. Parsing untuk tampilan list
                    $taskStartText = $task->start ? \Carbon\Carbon::parse($task->start)->format('M d') : '-';
                    $taskFinishText = $task->finish ? \Carbon\Carbon::parse($task->finish)->format('M d') : '-';
                    $durationText = $task->duration ? $task->duration : '-';
                    
                    // FIX: Warna berdasarkan level (sama seperti Gantt JS), fallback ke $task->color jika ada
                    $levelColor = $defaultColors[$task->level % count($defaultColors)] ?? $defaultColors[0];
                    $color = $task->color ?? $levelColor['bg']; // Prioritaskan DB color, fallback level-based

                    // Indentasi berdasarkan level (20px per level)
                    $indentPx = $task->level * 20;

                    // 2. Parsing Carbon untuk kalkulasi bar (menggunakan logika canggih dari template baru)
                    $taskStartCarbon = null;
                    $taskFinishCarbon = null;
                    if ($task->start) {
                        try {
                            if (preg_match('/^(\w{3})\s+(\d{1,2})$/', $task->start, $matches)) {
                                $monthMap = ['Jan' => 1, 'Feb' => 2, 'Mar' => 3, 'Apr' => 4, 'May' => 5, 'Jun' => 6,
                                             'Jul' => 7, 'Aug' => 8, 'Sep' => 9, 'Oct' => 10, 'Nov' => 11, 'Dec' => 12];
                                $month = $monthMap[$matches[1]] ?? 10;
                                $year = \Carbon\Carbon::now()->year;
                                $taskStartCarbon = \Carbon\Carbon::create($year, $month, $matches[2], 0, 0, 0)->startOfDay();
                            } else {
                                $taskStartCarbon = \Carbon\Carbon::parse($task->start)->startOfDay();
                            }
                        } catch (\Exception $e) { $taskStartCarbon = null; }
                    }
                    if ($task->finish) {
                         try {
                            if (preg_match('/^(\w{3})\s+(\d{1,2})$/', $task->finish, $matches)) {
                                $monthMap = ['Jan' => 1, 'Feb' => 2, 'Mar' => 3, 'Apr' => 4, 'May' => 5, 'Jun' => 6,
                                             'Jul' => 7, 'Aug' => 8, 'Sep' => 9, 'Oct' => 10, 'Nov' => 11, 'Dec' => 12];
                                $month = $monthMap[$matches[1]] ?? 10;
                                $year = \Carbon\Carbon::now()->year;
                                $taskFinishCarbon = \Carbon\Carbon::create($year, $month, $matches[2], 0, 0, 0)->startOfDay();
                            } else {
                                $taskFinishCarbon = \Carbon\Carbon::parse($task->finish)->startOfDay();
                            }
                        } catch (\Exception $e) { $taskFinishCarbon = null; }
                    }

                    // 3. Kalkulasi Posisi & Lebar Bar (Logika dari template baru)
                    $barWidthPercent = 0;
                    $barOffsetPercent = 0;

                    if ($taskStartCarbon && $taskFinishCarbon) {
                        $barDurationDays = $taskStartCarbon->diffInDays($taskFinishCarbon) + 1;
                        $offsetDays = $globalStart->diffInDays($taskStartCarbon);
                        if ($offsetDays < 0) $offsetDays = 0; 

                        $barWidthPercent = ($barDurationDays / $timelineDays) * 100;
                        $barOffsetPercent = ($offsetDays / $timelineDays) * 100;
                        
                        if ($barWidthPercent + $barOffsetPercent > 100) {
                            $barWidthPercent = 100 - $barOffsetPercent;
                        }
                    }
                @endphp
                
                <tr style="height: 25px;">
                    {{-- Kolom 1: Nama Tugas --}}
                    <td class="data-cell name-cell" style="width: {{ $listNameWidth }}px; padding-left: {{ $indentPx }}px;">
                        <span class="task-indicator" style="background-color: {{ $color }};"></span>
                        {{ Str::limit($task->name, 40 - $task->level * 2) }} {{-- Adjust limit untuk level tinggi --}}
                    </td>

                    {{-- Kolom 2, 3, 4: Detail Teks --}}
                    <td class="data-cell" style="width: {{ $listDateWidth }}px; text-align: center; padding: 5px 1px;">{{ $taskStartText }}</td>
                    <td class="data-cell" style="width: {{ $listDateWidth }}px; text-align: center; padding: 5px 1px;">{{ $taskFinishText }}</td>
                    <td class="data-cell" style="width: {{ $listDurationWidth }}px; text-align: center; padding: 5px 1px;">{{ $durationText }}</td>
                    
                    {{-- Kolom 5: Gantt Bar --}}
                    <td class="gantt-container-cell" style="width: {{ $ganttColumnWidth }}px;">
                        @if ($barWidthPercent > 0)
                            <div class="gantt-bar" {{-- Dihapus: class level-* --}}
                                 style="width: {{ $barWidthPercent }}%; 
                                        margin-left: {{ $barOffsetPercent }}%; 
                                        background-color: {{ $color }} !important;
                                        border-color: {{ $levelColor['border'] ?? $color }} !important;">
                                
                                @if ($barWidthPercent > 15)
                                    {{ Str::limit($task->name, 30) }}
                                @endif
                            </div>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    @endif
</body>
</html>