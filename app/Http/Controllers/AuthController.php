<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\ActivityLog;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $remember = $request->has('remember');

        if (Auth::attempt(['username' => $credentials['username'], 'password' => $credentials['password']], $remember)) {
            $user = Auth::user();

            if ($user->status !== 'aktif') {
                Auth::logout();
                ActivityLog::log('Login Ditolak', 'Autentikasi', "Percobaan login gagal untuk user nonaktif: {$credentials['username']}");
                return back()->withInput()->with('error', 'Akun Anda berstatus nonaktif. Silakan hubungi administrator.');
            }

            $request->session()->regenerate();
            ActivityLog::log('Login Berhasil', 'Autentikasi', "User {$user->name} ({$user->username}) berhasil masuk ke sistem.", $user->id);

            return redirect()->intended(route('dashboard'))->with('success', "Selamat datang kembali, {$user->name}!");
        }

        ActivityLog::log('Login Gagal', 'Autentikasi', "Percobaan login gagal dengan username: {$credentials['username']}");
        return back()->withInput()->with('error', 'Username atau password yang Anda masukkan salah.');
    }

    public function logout(Request $request)
    {
        if (Auth::check()) {
            ActivityLog::log('Logout', 'Autentikasi', "User " . Auth::user()->name . " keluar dari sistem.");
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Anda telah berhasil keluar dari sistem.');
    }
}
