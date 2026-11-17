<?php

namespace App\Http\Controllers;

use App\Models\Pengaturan;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class PengaturanController extends Controller
{
    public function index()
    {
        $pengaturan = Pengaturan::getPengaturan();
        return view('pengaturan.index', compact('pengaturan'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'nominal_per_hari' => 'required|numeric|min:0',
            'durasi_pinjam_default' => 'required|integer|min:1',
            'maks_buku_pinjam' => 'required|integer|min:1',
            'maks_denda' => 'nullable|numeric|min:0',
            'nama_perpustakaan' => 'required|string|max:255',
            'alamat_perpustakaan' => 'nullable|string',
            'no_telp_perpustakaan' => 'nullable|string|max:50',
        ]);

        $pengaturan = Pengaturan::getPengaturan();
        $pengaturan->update($validated);

        ActivityLog::log('Ubah Pengaturan', 'Pengaturan', 'Memperbarui konfigurasi parameter perpustakaan dan denda.');

        return redirect()->route('pengaturan.index')->with('success', 'Pengaturan perpustakaan berhasil diperbarui.');
    }
}
