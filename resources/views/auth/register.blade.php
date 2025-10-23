<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun - Project Management</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            /* Font 'Inter' yang modern dan bersih */
            font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
    </style>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="bg-gray-50">

    <div class="flex items-center justify-center min-h-screen p-4">
        
        <div class="relative flex w-full max-w-4xl bg-white shadow-2xl rounded-2xl overflow-hidden">
            
            <div class="hidden md:flex md:w-1/2 relative items-end justify-start p-12 text-white bg-cover bg-center" 
                 style="background-image: url('https://images.unsplash.com/photo-1522202176988-66273c2fd55f?auto=format&fit=crop&w=800&q=80');">
                
                <div class="absolute inset-0 bg-gradient-to-t from-black/70 to-transparent rounded-l-2xl"></div>
                
                <div class="relative z-10">
                    <h1 class="text-4xl font-bold mb-3 tracking-tight">
                        Buat Akun Baru
                    </h1>
                    <p class="text-lg text-gray-200">
                        Isi data diri Anda untuk memulai mengelola proyek.
                    </p>
                </div>
            </div>

            <div class="w-full md:w-1/2 p-8 md:p-12">
                
                <h2 class="text-3xl font-bold text-gray-900 mb-6 text-center">
                    Daftar Akun
                </h2>

                @if ($errors->any())
                    <div class="mb-4 p-4 bg-red-50 text-red-700 rounded-lg">
                        <ul class="list-disc list-inside text-sm">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                @if(session('success'))
                    <div class="mb-4 p-4 bg-green-50 text-green-700 rounded-lg text-sm">
                        {{ session('success') }}
                    </div>
                @endif
                
                <form method="POST" action="{{ route('register') }}" class="space-y-5 mt-6">
                    @csrf
                    
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                                </svg>
                            </span>
                            <input type="text" name="name" id="name" 
                                   class="block w-full p-3 pl-10 bg-gray-50 border border-gray-300 rounded-lg text-gray-900 focus:ring-blue-500 focus:border-blue-500 transition duration-200" 
                                   placeholder="Masukkan nama lengkap Anda" required value="{{ old('name') }}">
                        </div>
                    </div>

                    <div>
                        <label for="nip" class="block text-sm font-medium text-gray-700 mb-1">NIP</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                  <path fill-rule="evenodd" d="M10 2a2 2 0 00-2 2v1H6a2 2 0 00-2 2v9a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2V4a2 2 0 00-2-2zM8 5V4h4v1H8zM6 9v7h8V9H6zm2-2h4v1H8V7z" clip-rule="evenodd" />
                                </svg>
                            </span>
                            <input type="text" name="nip" id="nip" 
                                   maxlength="8"
                                   class="block w-full p-3 pl-10 bg-gray-50 border border-gray-300 rounded-lg text-gray-900 focus:ring-blue-500 focus:border-blue-500 transition duration-200" 
                                   placeholder="Masukkan NIP (maks 8 digit)" required value="{{ old('nip') }}">
                        </div>
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
  <path fill-rule="evenodd" d="M10 2a4 4 0 00-4 4v3H5a1 1 0 00-1 1v7a1 1 0 001 1h10a1 1 0 001-1v-7a1 1 0 00-1-1h-1V6a4 4 0 00-4-4zm0 2a2 2 0 012 2v3H8V6a2 2 0 012-2zm-1 9a1 1 0 112 0v2a1 1 0 11-2 0v-2z" clip-rule="evenodd" />
</svg>
                            </span>
                            <input type="password" name="password" id="password" 
                                   class="block w-full p-3 pl-10 pr-10 bg-gray-50 border border-gray-300 rounded-lg text-gray-900 focus:ring-blue-500 focus:border-blue-500 transition duration-200" 
                                   placeholder="Masukkan password" required>
                            <span id="togglePassword" class="absolute inset-y-0 right-0 flex items-center pr-3 cursor-pointer text-gray-400 hover:text-gray-600">
                                <svg id="icon-eye-pass" class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                <svg id="icon-eye-slash-pass" class="w-5 h-5 hidden" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" /></svg>
                            </span>
                        </div>
                    </div>
                    
                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Konfirmasi Password</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
  <path fill-rule="evenodd" d="M10 2a4 4 0 00-4 4v3H5a1 1 0 00-1 1v7a1 1 0 001 1h10a1 1 0 001-1v-7a1 1 0 00-1-1h-1V6a4 4 0 00-4-4zm0 2a2 2 0 012 2v3H8V6a2 2 0 012-2zm-1 9a1 1 0 112 0v2a1 1 0 11-2 0v-2z" clip-rule="evenodd" />
</svg>
                            </span>
                            <input type="password" name="password_confirmation" id="password_confirmation" 
                                   class="block w-full p-3 pl-10 pr-10 bg-gray-50 border border-gray-300 rounded-lg text-gray-900 focus:ring-blue-500 focus:border-blue-500 transition duration-200" 
                                   placeholder="Konfirmasi password Anda" required>
                            <span id="togglePasswordConfirmation" class="absolute inset-y-0 right-0 flex items-center pr-3 cursor-pointer text-gray-400 hover:text-gray-600">
                                <svg id="icon-eye-confirm" class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                <svg id="icon-eye-slash-confirm" class="w-5 h-5 hidden" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" /></svg>
                            </span>
                        </div>
                    </div>
                    
                    <div>
                        <button type="submit" 
                                class="w-full text-white p-3 rounded-lg font-semibold 
                                       bg-gradient-to-r from-blue-600 to-blue-700 
                                       hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 
                                       transform hover:-translate-y-0.5 transition-all duration-300 shadow-md">
                            Daftar
                        </button>
                    </div>
                </form>
                
                <p class="mt-8 text-center text-sm text-gray-600">
                    Sudah punya akun? 
                    <a href="{{ route('login') }}" class="font-semibold text-blue-600 hover:text-blue-700 hover:underline">
                        Masuk di sini
                    </a>
                </p>
            </div>
        </div>
    </div>

    <script>
        // Validasi input NIP (Hanya angka, maks 8)
        document.getElementById('nip').addEventListener('input', function () {
            this.value = this.value.replace(/\D/g, ''); // Hapus non-angka
            if (this.value.length > 8) {
                this.value = this.value.slice(0, 8); // Batasi 8 digit
            }
        });

        // Toggle untuk Password
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');
        const iconEyePass = document.getElementById('icon-eye-pass');
        const iconEyeSlashPass = document.getElementById('icon-eye-slash-pass');

        togglePassword.addEventListener('click', function () {
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                iconEyePass.classList.add('hidden');
                iconEyeSlashPass.classList.remove('hidden');
            } else {
                passwordInput.type = 'password';
                iconEyePass.classList.remove('hidden');
                iconEyeSlashPass.classList.add('hidden');
            }
        });

        // Toggle untuk Konfirmasi Password
        const togglePasswordConfirm = document.getElementById('togglePasswordConfirmation');
        const passwordInputConfirm = document.getElementById('password_confirmation');
        const iconEyeConfirm = document.getElementById('icon-eye-confirm');
        const iconEyeSlashConfirm = document.getElementById('icon-eye-slash-confirm');

        togglePasswordConfirm.addEventListener('click', function () {
            if (passwordInputConfirm.type === 'password') {
                passwordInputConfirm.type = 'text';
                iconEyeConfirm.classList.add('hidden');
                iconEyeSlashConfirm.classList.remove('hidden');
            } else {
                passwordInputConfirm.type = 'password';
                iconEyeConfirm.classList.remove('hidden');
                iconEyeSlashConfirm.classList.add('hidden');
            }
        });
    </script>
</body>
</html>