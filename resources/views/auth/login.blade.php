<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Project Management</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            /* Font 'Inter' sangat modern dan bersih */
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
                 style="background-image: url('https://images.pexels.com/photos/271667/pexels-photo-271667.jpeg');">
                
                <div class="absolute inset-0 bg-gradient-to-t from-black/70 to-transparent rounded-l-2xl"></div>
                
                <div class="relative z-10">
                    <h1 class="text-4xl font-bold mb-3 tracking-tight">
                        Selamat Datang Kembali
                    </h1>
                    <p class="text-lg text-gray-200">
                        Masuk untuk melanjutkan dan mengelola proyek Anda.
                    </p>
                </div>
            </div>

            <div class="w-full md:w-1/2 p-8 md:p-12">
                
                <h2 class="text-3xl font-bold text-gray-900 mb-6 text-center">
                    Masuk ke Akun
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
                @if(session('error'))
                    <div class="mb-4 p-4 bg-red-50 text-red-700 rounded-lg text-sm">
                        {{ session('error') }}
                    </div>
                @endif
                @if(session('success'))
                    <div class="mb-4 p-4 bg-green-50 text-green-700 rounded-lg text-sm">
                        {{ session('success') }}
                    </div>
                @endif
                
                <form method="POST" action="{{ route('login') }}" class="space-y-6 mt-6">
                    @csrf
                    
                    <div>
                        <label for="nip" class="block text-sm font-medium text-gray-700 mb-1">NIP</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                                </svg>
                            </span>
                            <input type="text" name="nip" id="nip" 
                                   maxlength="8"
                                   class="block w-full p-3 pl-10 bg-gray-50 border border-gray-300 rounded-lg text-gray-900 focus:ring-blue-500 focus:border-blue-500 transition duration-200" 
                                   placeholder="Masukkan NIP Anda" required value="{{ old('nip') }}">
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
                                <svg id="icon-eye" class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                          d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7
                                             -1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
_
                                </svg>
                                <svg id="icon-eye-slash" class="w-5 h-5 hidden" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                                </svg>
                            </span>
                        </div>
                    </div>
                    
                    <div>
                        <button type="submit" 
                                class="w-full text-white p-3 rounded-lg font-semibold 
                                       bg-gradient-to-r from-blue-600 to-blue-700 
                                       hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 
                                       transform hover:-translate-y-0.5 transition-all duration-300 shadow-md">
                            Masuk
                        </button>
                    </div>
                </form>
                
                <p class="mt-8 text-center text-sm text-gray-600">
                    Belum punya akun? 
                    <a href="{{ route('register') }}" class="font-semibold text-blue-600 hover:text-blue-700 hover:underline">
                        Daftar di sini
                    </a>
                </p>
            </div>
        </div>
    </div>

    <script>
        // Toggle visibility of password
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');
        const iconEye = document.getElementById('icon-eye');
        const iconEyeSlash = document.getElementById('icon-eye-slash');

        togglePassword.addEventListener('click', function () {
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                iconEye.classList.add('hidden');
                iconEyeSlash.classList.remove('hidden');
            } else {
                passwordInput.type = 'password';
                iconEye.classList.remove('hidden');
                iconEyeSlash.classList.add('hidden');
            }
        });

        // Validasi input NIP
        document.getElementById('nip').addEventListener('input', function () {
            this.value = this.value.replace(/\D/g, '');
            if (this.value.length > 8) {
                this.value = this.value.slice(0, 8);
            }
        });
    </script>
</body>
</html>