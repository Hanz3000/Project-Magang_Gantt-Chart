<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Project Management</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        // Konfigurasi Tailwind tetap di sini
        tailwind.config = {
            theme: {
                extend: {
                    backgroundImage: {
                        'blue-gradient': 'linear-gradient(135deg, #3B82F6 0%, #1E40AF 50%, #1E3A8A 100%)',
                    }
                }
            }
        }
    </script>
    <style>
        /* Kita biarkan ini kosong karena semua style sudah pindah ke kelas Tailwind */
        html,
        body {
            height: 100%;
            overflow-y: auto;
            overflow-x: hidden;
            margin: 0;
            padding: 0;
        }
    </style>
</head>

<body class="bg-gray-100 min-h-screen flex flex-col overflow-x-hidden">

    <nav class="bg-blue-gradient text-white shadow-lg relative z-40">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row justify-between items-center py-4 min-h-[80px]">

                <a href="{{ route('tasks.index') }}" class="relative z-10 group flex items-center justify-center md:justify-start">
                    <div class="flex items-center space-x-3">
                        <div class="w-8 h-8 bg-white/20 rounded-lg flex items-center justify-center backdrop-blur-sm shadow-inner">
                            <svg class="w-5 h-5 text-white" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M6.78 3.78a.75.75 0 0 0-1.06-1.06L3.75 4.69l-.47-.47a.75.75 0 0 0-1.06 1.06l1 1a.75.75 0 0 0 1.06 0l2.5-2.5ZM11 17.5c0 .342.027.679.078 1.007H9.75a.75.75 0 0 1-.102-1.493l.102-.007h1.268A6.6 6.6 0 0 0 11 17.5Zm6.5-6.5a6.47 6.47 0 0 1 3.466 1h.284l.102-.007a.75.75 0 0 0-.102-1.493H9.75l-.102.007A.75.75 0 0 0 9.75 12h4.284a6.47 6.47 0 0 1 3.466-1Zm3.75-7H9.75l-.102.007A.75.75 0 0 0 9.75 5.5h11.5l.102-.007A.75.75 0 0 0 21.25 4ZM6.78 16.78a.75.75 0 1 0-1.06-1.06l-1.97 1.97l-.47-.47a.75.75 0 0 0-1.06 1.06l1 1a.75.75 0 0 0 1.06 0l2.5-2.5Zm0-7.56a.75.75 0 0 1 0 1.06l-2.5 2.5a.75.75 0 0 1-1.06 0l-1-1a.75.75 0 1 1 1.06-1.06l.47.47l1.97-1.97a.75.75 0 0 1 1.06 0ZM23 17.5a5.5 5.5 0 1 0-11 0a5.5 5.5 0 0 0 11 0Zm-5 .5l.001 2.503a.5.5 0 1 1-1 0V18h-2.505a.5.5 0 0 1 0-1H17v-2.5a.5.5 0 1 1 1 0V17h2.497a.5.5 0 0 1 0 1H18Z" />
                            </svg>
                        </div>
                        <span class="font-bold text-lg sm:text-xl tracking-wide group-hover:text-blue-100 transition-colors duration-300">
                            PROJECT MANAGEMENT
                        </span>
                    </div>
                </a>

                @auth
                <div class="relative z-50 mt-4 md:mt-0">
                    <button id="user-menu-button" type="button" class="flex items-center space-x-2 bg-white/10 backdrop-blur-sm rounded-full p-2 transition-colors duration-200 hover:bg-white/20 focus:outline-none focus:ring-2 focus:ring-white/50" aria-expanded="false" aria-haspopup="true">
                        <div class="w-8 h-8 bg-white/20 rounded-full flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 12c2.7 0 5-2.3 5-5s-2.3-5-5-5-5 2.3-5 5 2.3 5 5 5zm0 2c-3.3 0-10 1.7-10 5v2h20v-2c0-3.3-6.7-5-10-5z" />
                            </svg>
                        </div>
                        <span class="text-sm font-semibold pr-1">
                            {{ Auth::user()->name }}
                        </span>
                        <svg id="user-menu-arrow" class="w-4 h-4 text-white transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>

                    <div id="user-dropdown" class="hidden opacity-0 scale-95 -translate-y-2 transform transition-all duration-200 ease-out absolute right-0 top-full mt-2 w-56 origin-top-right bg-white rounded-lg shadow-xl ring-1 ring-black ring-opacity-5 divide-y divide-gray-100 focus:outline-none">
    <div class="py-1">
        <a href="{{ route('profil') }}" class="flex items-center gap-3 px-4 py-3 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900 transition-all duration-150 rounded-lg block">
            <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
            </svg>
            <span>Profil</span>
        </a>
        {{-- TAMBAH: Button Export PDF di Dropdown User --}}
        <a id="globalExportLink" class="flex items-center gap-3 px-4 py-3 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900 transition-all duration-150 rounded-lg block" onclick="handleGlobalExport(event)">
           <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 36 36" class="w-5 h-5 fill-current">
                            <path d="M17,22a1,1,0,0,1,1-1h8V6a2,2,0,0,0-2-2H10.87L4,10.86V30a2,2,0,0,0,2,2H24a2,2,0,0,0,2-2V23H18A1,1,0,0,1,17,22ZM12,12H6v-.32L11.69,6H12Z"></path>
                            <path d="M29.32,16.35a1,1,0,0,0-1.41,1.41L31.16,21H26v2h5.19l-3.28,3.28a1,1,0,1,0,1.41,1.41L35,22Z"></path>
                        </svg>
            <span>Export PDF</span>
        </a>
    </div>
    <div class="py-1">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="flex items-center gap-3 w-full text-left px-4 py-3 text-sm text-red-600 hover:bg-gray-100 hover:text-red-700 transition-all duration-150 rounded-lg">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                </svg>
                <span>Logout</span>
            </button>
        </form>
    </div>
</div>
                </div>
                @endauth

                @guest
                <div class="relative z-10 flex items-center gap-3 mt-4 md:mt-0">
                    <a href="{{ route('login') }}" class="font-medium px-4 py-2 rounded-lg hover:bg-white/10 transition-colors duration-200 text-sm">
                        Login
                    </a>
                    <a href="{{ route('register') }}" class="font-medium bg-white/20 hover:bg-white/30 px-4 py-2 rounded-lg backdrop-blur-sm transition-colors duration-200 text-sm">
                        Register
                    </a>
                </div>
                @endguest
            </div>
        </div>
    </nav>

    <main class="flex-1 p-4 sm:p-6 lg:p-8 overflow-auto bg-gray-100 w-full">
        @yield('content')
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Cek jika elemen-elemen ada (hanya untuk user yang terotentikasi)
            const menuButton = document.getElementById('user-menu-button');
            const dropdown = document.getElementById('user-dropdown');
            const arrow = document.getElementById('user-menu-arrow');

            if (menuButton && dropdown && arrow) {
                
                // Fungsi terpusat untuk menampilkan/menyembunyikan dropdown
                function toggleDropdown(show) {
                    if (show) {
                        dropdown.classList.remove('hidden');
                        // Trik untuk memastikan transisi 'show' berjalan
                        requestAnimationFrame(() => {
                            dropdown.classList.remove('opacity-0', 'scale-95', '-translate-y-2');
                            dropdown.classList.add('opacity-100', 'scale-100', 'translate-y-0');
                        });
                        arrow.style.transform = 'rotate(180deg)';
                        menuButton.setAttribute('aria-expanded', 'true');
                    } else {
                        // Mulai transisi 'hide'
                        dropdown.classList.remove('opacity-100', 'scale-100', 'translate-y-0');
                        dropdown.classList.add('opacity-0', 'scale-95', '-translate-y-2');
                        arrow.style.transform = 'rotate(0deg)';
                        menuButton.setAttribute('aria-expanded', 'false');
                        // Sembunyikan elemen setelah animasi selesai (200ms)
                        setTimeout(() => {
                            dropdown.classList.add('hidden');
                        }, 200);
                    }
                }

                // Event listener untuk tombol menu
                menuButton.addEventListener('click', function(event) {
                    event.stopPropagation(); // Mencegah event 'click' di 'document'
                    const isHidden = dropdown.classList.contains('hidden');
                    toggleDropdown(isHidden); // Toggle berdasarkan state saat ini
                });

                // Event listener untuk menutup dropdown saat klik di luar
                document.addEventListener('click', function(event) {
                    // Jika dropdown sedang terbuka dan klik BUKAN di dalam tombol ATAU dropdown
                    if (!dropdown.classList.contains('hidden') &&
                        !menuButton.contains(event.target) &&
                        !dropdown.contains(event.target)) {
                        toggleDropdown(false); // Sembunyikan dropdown
                    }
                });
            }
            
        });

        function handleGlobalExport(event) {
            event.preventDefault();
            
            // Check jika di halaman gantt (filteredTaskIdsToShow defined)
            if (typeof filteredTaskIdsToShow !== 'undefined' && filteredTaskIdsToShow && filteredTaskIdsToShow.size > 0) {
                // Ada filter: Kirim array ID visible
                const filterArray = Array.from(filteredTaskIdsToShow).map(id => parseInt(id));
                const url = `{{ route('gantt.export.pdf') }}?filter=${encodeURIComponent(JSON.stringify(filterArray))}`;
                window.open(url, '_blank');
            } else {
                // No filter: Export semua
                window.open("{{ route('gantt.export.pdf') }}", '_blank');
            }
        }
    </script>
</body>

</html>