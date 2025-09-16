<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLogin() {
        return view('auth.login');
    }

    public function login(Request $request)
{
    $credentials = $request->validate([
        'nip' => ['required', 'string'],
        'password' => ['required', 'string'],
    ]);

    if (Auth::attempt(['nip' => $credentials['nip'], 'password' => $credentials['password']])) {
        $request->session()->regenerate();

        // Redirect ke index tasks
        return redirect()->route('tasks.index');
    }

    return back()->withErrors([
        'nip' => 'NIP atau password salah',
    ]);
}

    public function showRegister() {
        return view('auth.register');
    }

    public function register(Request $request) {
        // validasi registrasi
        $request->validate([
            'name' => 'required|string|max:255',
            'nip' => 'required|numeric|digits_between:1,8|unique:users,nip',
            'password' => 'required|string|min:6',
        ]);

        User::create([
            'name' => $request->name,
            'nip' => $request->nip,
            'password' => Hash::make($request->password),
        ]);

        return redirect()->route('login')->with('success', 'Registrasi berhasil, silakan login.');
    }

    public function logout() {
        Auth::logout();
        return redirect()->route('login');
    }
}
