<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Project Management</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    backgroundImage: {
                        'blue-gradient': 'linear-gradient(135deg, #3B82F6 0%, #1E40AF 50%, #1E3A8A 100%)',
                    }
                }
            }
        }
        
        // Fungsi untuk toggle dropdown
        function toggleDropdown() {
    const dropdown = document.getElementById('user-dropdown');
    dropdown.classList.toggle('hidden');
    
    // pastikan posisinya absolute terhadap body
    dropdown.style.position = "absolute";
    dropdown.style.top = (userSection.offsetTop + userSection.offsetHeight) + "px";
    dropdown.style.right = "20px"; // sesuaikan
    document.body.appendChild(dropdown);
}

        
        // Tutup dropdown ketika klik di luar area
        document.addEventListener('DOMContentLoaded', function() {
            document.addEventListener('click', function(event) {
                const dropdown = document.getElementById('user-dropdown');
                const userSection = document.getElementById('user-section');
                
                if (!userSection.contains(event.target)) {
                    dropdown.classList.add('hidden');
                }
            });
        });
    </script>
    <style>
    html, body {
        height: 100%;
        overflow-y: auto;
        overflow-x: hidden;
        margin: 0;
        padding: 0;
    }
    
    /* Fixed height untuk header */
    .header-gradient {
        background: linear-gradient(135deg, #3B82F6 0%, #1E40AF 50%, #1E3A8A 100%);
        box-shadow: 0 4px 20px rgba(30, 58, 138, 0.3);
        position: relative;
        z-index: 50;
        min-height: 80px !important; /* Tambahkan !important */
        display: flex;
        align-items: center;
    }
    
    /* Container flex yang konsisten */
    .header-container {
        width: 100%;
        display: flex;
        flex-direction: column;
        align-items: stretch;
        min-height: 80px;
    }
    
    @media (min-width: 640px) {
        .header-container {
            flex-direction: row;
            justify-content: between;
            align-items: center;
        }
    }

    /* Modern button hover effects */
    .modern-logout-btn {
        background: linear-gradient(45deg, #EF4444, #DC2626);
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(239, 68, 68, 0.3);
    }
    
    .modern-logout-btn:hover {
        background: linear-gradient(45deg, #DC2626, #B91C1C);
        box-shadow: 0 6px 20px rgba(239, 68, 68, 0.4);
        transform: translateY(-2px);
    }
    
    .auth-link {
        position: relative;
        transition: all 0.3s ease;
    }
    
    .auth-link:hover {
        transform: translateY(-1px);
    }
    
    .auth-link::after {
        content: '';
        position: absolute;
        width: 0;
        height: 2px;
        bottom: -4px;
        left: 50%;
        background-color: white;
        transition: all 0.3s ease;
    }
    
    .auth-link:hover::after {
        width: 100%;
        left: 0;
    }
    
    /* Responsive fixes dengan height yang konsisten */
    @media (max-width: 768px) {
        .header-gradient {
            padding: 1rem !important;
            min-height: 80px !important;
        }
        
        .nav-brand {
            font-size: 1.125rem;
        }
        
        .user-section {
            gap: 0.75rem;
        }
        
        .modern-logout-btn {
            padding: 0.5rem 0.75rem;
            font-size: 0.875rem;
        }
    }
    
    @media (max-width: 640px) {
        .header-gradient {
            flex-direction: column;
            gap: 1rem;
            align-items: stretch;
            min-height: 120px !important; /* Lebih tinggi untuk mobile */
            justify-content: center;
        }
        
        .nav-brand {
            justify-content: center;
            text-align: center;
        }
        
        .user-section {
            justify-content: center;
            text-align: center;
        }
    }
    
    /* Dropdown styles */
   .user-dropdown {
    position: absolute;
    top: 100%;
    right: 0;
    margin-top: 0.5rem;
    background: white;
    border-radius: 0.5rem;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
    min-width: 180px;
    z-index: 9999; /* pastikan lebih tinggi */
}

    
    @keyframes dropdownFade {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .dropdown-item {
        display: flex;
        align-items: center;
        padding: 0.75rem 1rem;
        color: #4B5563;
        text-decoration: none;
        transition: all 0.2s ease;
        border-bottom: 1px solid #F3F4F6;
    }
    
    .dropdown-item:last-child {
        border-bottom: none;
    }
    
    .dropdown-item:hover {
        background-color: #F9FAFB;
        color: #1F2937;
    }
    
    .dropdown-item svg {
        margin-right: 0.75rem;
        width: 1rem;
        height: 1rem;
    }
    
    .user-section-clickable {
        cursor: pointer;
        transition: all 0.2s ease;
    }
    
    .user-section-clickable:hover {
        background-color: rgba(255, 255, 255, 0.15) !important;
    }

    
</style>
</head>
<body class="bg-gray-50 min-h-screen flex flex-col overflow-x-hidden">
    <!-- Modern Navbar with Gradient -->
   <nav class="header-gradient text-white px-4 sm:px-6 lg:px-8 py-4 flex flex-col sm:flex-row justify-between items-center relative">

        <!-- Subtle pattern overlay for depth -->
        <div class="absolute inset-0 opacity-10">
            <div class="absolute inset-0" style="background-image: radial-gradient(circle at 20% 50%, rgba(255,255,255,0.1) 0%, transparent 50%), radial-gradient(circle at 80% 20%, rgba(255,255,255,0.1) 0%, transparent 50%);"></div>
        </div>
        
        <a href="{{ route('projects.index') }}" class="relative z-10 group nav-brand w-full sm:w-auto">
  <div class="flex items-center space-x-3">
    <div class="w-8 h-8 bg-white bg-opacity-20 rounded-lg flex items-center justify-center backdrop-blur-sm">
      <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
              d="M3 7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V7z" />
      </svg>
    </div>
    <span class="font-bold text-lg sm:text-xl tracking-wide group-hover:text-blue-100 transition-colors duration-300">
      PROJECT MANAGEMENT
    </span>
  </div>
</a>



        <!-- User Section -->
        @auth
            <div id="user-section" class="relative z-10 user-section w-full sm:w-auto">
                <!-- User Greeting - Clickable untuk membuka dropdown -->
                <div onclick="toggleDropdown()" class="user-section-clickable flex items-center space-x-2 bg-white bg-opacity-10 backdrop-blur-sm rounded-full px-3 sm:px-4 py-2 cursor-pointer">
                    <div class="w-8 h-8 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                    </div>
                    <span class="text-xs sm:text-sm">
                        <strong class="font-semibold">{{ Auth::user()->name }}</strong>
                    </span>
                    <!-- Dropdown arrow -->
                    <svg class="w-4 h-4 text-white transition-transform duration-200 dropdown-arrow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </div>
                
                <!-- Dropdown Menu -->
                <div id="user-dropdown" class="user-dropdown hidden">
                    <a href="#" class="dropdown-item">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        Profil
                    </a>
                    <a href="#" class="dropdown-item">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        Pengaturan
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="dropdown-item w-full text-left text-red-600 hover:text-red-700">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                            </svg>
                            Logout
                        </button>
                    </form>
                </div>
            </div>
        @endauth

        <!-- Guest Links -->
        @guest
            <div class="relative z-10 flex flex-col sm:flex-row items-center gap-3 sm:gap-6 user-section w-full sm:w-auto">
                <a href="{{ route('login') }}" class="auth-link text-white hover:text-blue-100 font-medium px-3 sm:px-4 py-2 rounded-lg hover:bg-white hover:bg-opacity-10 transition-all duration-300 text-sm">
                    Login
                </a>
                <a href="{{ route('register') }}" class="auth-link bg-white bg-opacity-20 hover:bg-opacity-30 text-white font-medium px-3 sm:px-4 py-2 rounded-lg backdrop-blur-sm transition-all duration-300 border border-white border-opacity-20 text-sm">
                    Register
                </a>
            </div>
        @endguest
    </nav>

    <!-- Main content fleksibel -->
    <main class="flex-1 p-3 sm:p-4 lg:p-6 overflow-visible bg-gray-50 w-full">
        @yield('content')
    </main>

    <script>
        // Tambahkan animasi untuk dropdown arrow
        document.addEventListener('DOMContentLoaded', function() {
            const userSection = document.getElementById('user-section');
            const dropdownArrow = document.querySelector('.dropdown-arrow');
            
            userSection.addEventListener('click', function(event) {
                // Mencegah event bubbling untuk menghindari penutupan dropdown saat diklik
                event.stopPropagation();
                
                // Toggle rotasi panah dropdown
                const dropdown = document.getElementById('user-dropdown');
                if (dropdown.classList.contains('hidden')) {
                    dropdownArrow.style.transform = 'rotate(180deg)';
                } else {
                    dropdownArrow.style.transform = 'rotate(0deg)';
                }
            });
        });
    </script>
</body>
</html>