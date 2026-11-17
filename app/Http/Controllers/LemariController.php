<?php

namespace App\Http\Controllers;

use App\Models\Lemari;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LemariController extends Controller
{
    public function index(Request $request)
    {
        $query = Lemari::withCount('laci');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('kode_lemari', 'like', "%{$search}%")
                  ->orWhere('nama_lokasi', 'like', "%{$search}%")
                  ->orWhere('deskripsi', 'like', "%{$search}%");
            });
        }

        $lemari = $query->orderBy('kode_lemari', 'asc')->paginate(10)->withQueryString();
        return view('lemari.index', compact('lemari'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode_lemari' => 'required|string|max:50|unique:lemari,kode_lemari',
            'nama_lokasi' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
        ]);

        $lemari = Lemari::create($validated);
        ActivityLog::log('Tambah Lemari', 'Master Lokasi', "Menambahkan lemari baru: {$lemari->kode_lemari} - {$lemari->nama_lokasi}");

        return redirect()->route('lemari.index')->with('success', "Lemari {$lemari->kode_lemari} berhasil ditambahkan.");
    }

    public function update(Request $request, Lemari $lemari)
    {
        $validated = $request->validate([
            'kode_lemari' => ['required', 'string', 'max:50', Rule::unique('lemari')->ignore($lemari->id)],
            'nama_lokasi' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
        ]);

        $lemari->update($validated);
        ActivityLog::log('Ubah Lemari', 'Master Lokasi', "Mengubah lemari: {$lemari->kode_lemari}");

        return redirect()->route('lemari.index')->with('success', "Lemari {$lemari->kode_lemari} berhasil diperbarui.");
    }

    public function destroy(Lemari $lemari)
    {
        $kode = $lemari->kode_lemari;
        $lemari->delete();

        ActivityLog::log('Hapus Lemari', 'Master Lokasi', "Menghapus lemari: {$kode}");

        return redirect()->route('lemari.index')->with('success', "Lemari {$kode} beserta laci terkait berhasil dihapus.");
    }
}
