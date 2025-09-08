<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Project Management</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        html, body {
            height: 100%;
            overflow-y: auto;   /* scroll hanya di kanan */
            overflow-x: hidden; /* cegah scroll horizontal */
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen flex flex-col">
    <!-- Navbar -->
   <nav class="bg-blue-600 text-white px-6 py-3 flex justify-between items-center">
    <a href="{{ route('projects.index') }}" class="font-bold text-lg">
        Project Management
    </a>

    @auth
        @if(Route::currentRouteName() === 'tasks.index')
            <div class="flex items-center gap-4">
                <span>Halo, <strong>{{ Auth::user()->name }}</strong></span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="bg-red-500 hover:bg-red-600 px-3 py-1 rounded">
                        Logout
                    </button>
                </form>
            </div>
        @endif
    @endauth

    @guest
        <div class="flex items-center gap-4">
            <a href="{{ route('login') }}" class="hover:underline">Login</a>
            <a href="{{ route('register') }}" class="hover:underline">Register</a>
        </div>
    @endguest
</nav>


    <!-- Main content fleksibel -->
    <main class="flex-1 p-6 overflow-visible">
        @yield('content')
    </main>
</body>
</html>
