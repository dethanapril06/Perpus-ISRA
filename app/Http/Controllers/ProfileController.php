<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\ActivityLog;

class ProfileController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        return view('profile.index', compact('user'));
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:users,email,' . $user->id,
            'no_telp' => 'nullable|string|max:20',
        ]);

        $user->update($validated);
        ActivityLog::log('Ubah Profil', 'Pengguna', "User {$user->username} memperbarui profil akun.");

        return back()->with('success', 'Profil Anda berhasil diperbarui.');
    }

    public function updatePassword(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->with('error', 'Password saat ini yang Anda masukkan tidak sesuai.');
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        ActivityLog::log('Ubah Password', 'Pengguna', "User {$user->username} mengubah kata sandi.");

        return back()->with('success', 'Kata sandi berhasil diperbarui.');
    }
}
