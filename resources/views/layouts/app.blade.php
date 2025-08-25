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
    <nav class="bg-blue-600 text-white px-6 py-3">
        <a href="{{ route('projects.index') }}" class="font-bold">Project Management</a>
    </nav>

    <!-- Main content fleksibel -->
    <main class="flex-1 p-6 overflow-visible">
        @yield('content')
    </main>
</body>
</html>
