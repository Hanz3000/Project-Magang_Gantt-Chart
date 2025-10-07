@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto bg-white shadow-lg rounded-2xl p-8" x-data="{ open: false }">
    <div class="flex items-center space-x-4 mb-6">
        <div class="w-20 h-20 bg-gradient-to-tr from-blue-500 to-indigo-600 text-white rounded-full flex items-center justify-center text-2xl font-bold shadow-lg">
            {{ strtoupper(substr($user->name, 0, 2)) }}
        </div>
        <div>
            <h2 class="text-2xl font-bold text-gray-800">{{ $user->name }}</h2>
            <p class="text-gray-500">NIP: {{ $user->nip ?? '-' }}</p>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-4 p-3 bg-green-100 text-green-700 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <div class="p-4 bg-gray-50 rounded-lg border">
            <p class="text-sm text-gray-500">Nama Lengkap</p>
            <p class="font-semibold text-gray-800">{{ $user->name }}</p>
        </div>
        <div class="p-4 bg-gray-50 rounded-lg border">
            <p class="text-sm text-gray-500">NIP</p>
            <p class="font-semibold text-gray-800">{{ $user->nip ?? '-' }}</p>
        </div>
        <div class="p-4 bg-gray-50 rounded-lg border">
            <p class="text-sm text-gray-500">Tanggal Daftar</p>
            <p class="font-semibold text-gray-800">{{ $user->created_at->format('d M Y') }}</p>
        </div>
        
        <div class="p-4 bg-gray-50 rounded-lg border">
            <p class="text-sm text-gray-500">Status Akun</p>
            <p class="font-semibold text-green-600">Aktif</p>
        </div>
    </div>

    <div class="flex justify-between">
        <button @click="open = true"
            class="px-5 py-2 bg-yellow-500 text-white rounded-lg shadow hover:bg-yellow-600 transition">
            Ganti Kata Sandi
        </button>
        <a href="{{ route('tasks.index') }}" 
           class="px-5 py-2 bg-blue-600 text-white rounded-lg shadow hover:bg-blue-700 transition">
           Kembali
        </a>
    </div>

    <!-- Modal -->
    <div x-show="open" 
         class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50"
         x-cloak>
        <div class="bg-white rounded-xl shadow-2xl p-6 w-full max-w-md">
            <h3 class="text-xl font-semibold mb-4 text-gray-800">Ganti Kata Sandi</h3>

            <form method="POST" action="{{ route('profil.password.update') }}" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-sm font-medium text-gray-700">Kata Sandi Baru</label>
                    <input type="password" name="password" required
                           class="w-full mt-1 px-3 py-2 border rounded-lg shadow-sm focus:ring focus:ring-blue-200">
                    @error('password')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Konfirmasi Kata Sandi</label>
                    <input type="password" name="password_confirmation" required
                           class="w-full mt-1 px-3 py-2 border rounded-lg shadow-sm focus:ring focus:ring-blue-200">
                </div>

                <div class="flex justify-between mt-6">
                    <button type="button" @click="open = false"
                        class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 transition">
                        Batal
                    </button>
                    <button type="submit"
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg shadow hover:bg-blue-700 transition">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://unpkg.com/alpinejs" defer></script>
@endsection
