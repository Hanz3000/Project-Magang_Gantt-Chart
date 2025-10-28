<!DOCTYPE html>
<html>
<head>
    <title>Gantt Chart Export - {{ \Carbon\Carbon::now()->format('d M Y') }}</title>
    @php
        // ===================================================================
        // BLOK PERSIAPAN VARIABEL
        // ===================================================================

        // 1. Definisikan Lebar Kolom (Fixed Layout) - MODIFIKASI: KECILKAN LAGI DATE & DURATION, PANJANGKAN GANTT
        $listNameWidth = 300;     // Lebar kolom Nama Tugas (px) - tetap
        $listDateWidth = 38;      // DIKECILKAN LAGI (dari 42) untuk Tanggal Mulai & Selesai
        $listDurationWidth = 24;  // DIKECILKAN LAGI (dari 28) untuk Durasi
        $ganttColumnWidth = 538;  // DILEBARKAN LAGI (menyerap (4*2) + (4*2) + 2px) untuk Gantt
        
        $totalTableWidth = $listNameWidth + ($listDateWidth * 2) + $listDurationWidth + $ganttColumnWidth;

        // Warna berdasarkan level
        $colors = [
            0 => '#0078d4',
            1 => '#107c10',
            2 => '#881798',
            3 => '#ff8c00',
            4 => '#e81123',
            5 => '#5c2d91'
        ];

        // 2. Hitung Range Proyek Global (Sama seperti sebelumnya)
        $allDates = $tasks->pluck('start')->merge($tasks->pluck('finish'))->filter();
        
        if ($allDates->isEmpty()) {
            $globalStart = \Carbon\Carbon::now()->subDays(10)->startOfDay();
            $globalEnd = \Carbon\Carbon::now()->addDays(20)->startOfDay();
        } else {
            $parsedDates = [];
            foreach ($allDates as $dateStr) {
                try {
                    if (preg_match('/^(\w{3})\s+(\d{1,2})$/', $dateStr, $matches)) {
                        $monthMap = ['Jan' => 1, 'Feb' => 2, 'Mar' => 3, 'Apr' => 4, 'May' => 5, 'Jun' => 6,
                                     'Jul' => 7, 'Aug' => 8, 'Sep' => 9, 'Oct' => 10, 'Nov' => 11, 'Dec' => 12];
                        $month = $monthMap[$matches[1]] ?? 10;
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
            $globalStart = !empty($parsedDates) ? collect($parsedDates)->min() : \Carbon\Carbon::now()->subDays(10)->startOfDay();
            $globalEnd = !empty($parsedDates) ? collect($parsedDates)->max() : \Carbon\Carbon::now()->addDays(20)->startOfDay();
        }

        // Total durasi timeline dalam hari (penting untuk kalkulasi persentase)
        $timelineDays = $globalStart->diffInDays($globalEnd) + 1;
        if ($timelineDays <= 0) {
            $timelineDays = 1; // Hindari pembagian dengan nol
        }

    @endphp
    <style>
        /* Pengaturan Dasar dan Ukuran Halaman */
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

        /* Warna untuk level */
        .level-0 { background-color: #0078d4 !important; color: white !important; }
        .level-1 { background-color: #107c10 !important; color: white !important; }
        .level-2 { background-color: #881798 !important; color: white !important; }
        .level-3 { background-color: #ff8c00 !important; color: white !important; }
        .level-4 { background-color: #e81123 !important; color: white !important; }
        .level-5 { background-color: #5c2d91 !important; color: white !important; }

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

        /* STYLING BARU UNTUK KOLOM GANTT */
        .gantt-container-cell {
            border: 1px solid #e0e0e0;
            padding: 0 2px; /* Padding horizontal agar bar tidak menempel */
            vertical-align: middle;
            height: 25px; 
            position: relative; /* Penting */
            background-color: #fdfdfd;
            /* Garis-garis vertikal tipis sebagai grid background */
            background-image: repeating-linear-gradient(to right, 
                                #f0f0f0 0, 
                                #f0f0f0 1px, 
                                transparent 1px, 
                                transparent 40px); /* Garis setiap 40px */
        }

        .gantt-bar {
            position: relative; /* Diposisikan dengan margin-left */
            height: 18px; /* Tinggi bar */
            margin: 3px 0; /* Margin atas-bawah */
            border-radius: 4px;
            color: white;
            font-size: 8px;
            line-height: 18px;
            text-align: left; /* Teks mulai dari kiri bar */
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            padding: 0 4px; /* Padding di dalam bar */
            box-sizing: border-box;
            border: 1px solid rgba(255,255,255,0.3);
            box-shadow: 0 1px 2px rgba(0,0,0,0.1);
            
            /* width dan margin-left akan di-set inline */
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
        <h2>Gantt Chart {{ $globalStart->format('M Y') }} - {{ $globalEnd->format('M Y') }}</h2>
    </div>

    @if($tasks->isEmpty())
        <div class="empty-state">
            <h3>Tidak Ada Tugas</h3>
            <p>Tambahkan tugas untuk melihat Gantt Chart.</p>
        </div>
    @else
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
                    $taskStart = $task->start ? \Carbon\Carbon::parse($task->start)->format('M d') : '-';
                    $taskFinish = $task->finish ? \Carbon\Carbon::parse($task->finish)->format('M d') : '-';
                    $level = $task->level % 6;
                    $durationText = $task->duration ? $task->duration : '-';
                    $color = $colors[$level];

                    // 2. Parsing Carbon untuk kalkulasi bar (ambil dari kode lama Anda)
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

                    // 3. Kalkulasi Posisi & Lebar Bar (LOGIKA BARU)
                    $barWidthPercent = 0;
                    $barOffsetPercent = 0;

                    if ($taskStartCarbon && $taskFinishCarbon) {
                        // Hitung durasi bar (inklusif)
                        $barDurationDays = $taskStartCarbon->diffInDays($taskFinishCarbon) + 1;
                        
                        // Hitung offset dari awal project
                        $offsetDays = $globalStart->diffInDays($taskStartCarbon);
                        if ($offsetDays < 0) $offsetDays = 0; // Jika tugas mulai sebelum globalStart

                        // Hitung % width dan offset
                        $barWidthPercent = ($barDurationDays / $timelineDays) * 100;
                        $barOffsetPercent = ($offsetDays / $timelineDays) * 100;
                        
                        // Batasi agar tidak overflow
                        if ($barWidthPercent + $barOffsetPercent > 100) {
                            $barWidthPercent = 100 - $barOffsetPercent;
                        }
                    }
                @endphp
                
                <tr style="height: 25px;">
                    <td class="data-cell name-cell" style="width: {{ $listNameWidth }}px;">
                        {!! str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;', $task->level) !!}
                        <span class="task-indicator" style="background-color: {{ $color }};"></span>
                        {{ Str::limit($task->name, 40) }}
                    </td>

                    <td class="data-cell" style="width: {{ $listDateWidth }}px; text-align: center; padding: 5px 1px;">{{ $taskStart }}</td>
                    <td class="data-cell" style="width: {{ $listDateWidth }}px; text-align: center; padding: 5px 1px;">{{ $taskFinish }}</td>
                    <td class="data-cell" style="width: {{ $listDurationWidth }}px; text-align: center; padding: 5px 1px;">{{ $durationText }}</td>
                    
                    <td class="gantt-container-cell" style="width: {{ $ganttColumnWidth }}px;">
                        @if ($barWidthPercent > 0)
                            <div class="gantt-bar level-{{ $level }}" 
                                 style="width: {{ $barWidthPercent }}%; 
                                        margin-left: {{ $barOffsetPercent }}%; 
                                        background-color: {{ $color }} !important;">
                                 
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