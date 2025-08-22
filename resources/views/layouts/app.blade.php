<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Project Management</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <nav class="bg-blue-600 text-white px-6 py-3">
        <a href="{{ route('projects.index') }}" class="font-bold">Project Management</a>
    </nav>
    <main class="p-6">
        @yield('content')
    </main>
</body>
</html>
