<?php

namespace App\Http\Controllers;

use App\Models\Laci;
use App\Models\Lemari;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LaciController extends Controller
{
    public function index(Request $request)
    {
        $query = Laci::with(['lemari'])->withCount('buku');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('kode_laci', 'like', "%{$search}%")
                  ->orWhere('deskripsi', 'like', "%{$search}%")
                  ->orWhereHas('lemari', function ($ql) use ($search) {
                      $ql->where('nama_lokasi', 'like', "%{$search}%")
                         ->orWhere('kode_lemari', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('id_lemari')) {
            $query->where('id_lemari', $request->id_lemari);
        }

        $laci = $query->orderBy('kode_laci', 'asc')->paginate(10)->withQueryString();
        $lemariList = Lemari::orderBy('kode_lemari', 'asc')->get();

        return view('laci.index', compact('laci', 'lemariList'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode_laci' => 'required|string|max:50|unique:laci,kode_laci',
            'id_lemari' => 'required|exists:lemari,id',
            'deskripsi' => 'nullable|string',
        ]);

        $laci = Laci::create($validated);
        ActivityLog::log('Tambah Laci', 'Master Lokasi', "Menambahkan laci baru: {$laci->kode_laci}");

        return redirect()->route('laci.index')->with('success', "Laci {$laci->kode_laci} berhasil ditambahkan.");
    }

    public function update(Request $request, Laci $laci)
    {
        $validated = $request->validate([
            'kode_laci' => ['required', 'string', 'max:50', Rule::unique('laci')->ignore($laci->id)],
            'id_lemari' => 'required|exists:lemari,id',
            'deskripsi' => 'nullable|string',
        ]);

        $laci->update($validated);
        ActivityLog::log('Ubah Laci', 'Master Lokasi', "Mengubah laci: {$laci->kode_laci}");

        return redirect()->route('laci.index')->with('success', "Laci {$laci->kode_laci} berhasil diperbarui.");
    }

    public function destroy(Laci $laci)
    {
        $kode = $laci->kode_laci;
        $laci->delete();

        ActivityLog::log('Hapus Laci', 'Master Lokasi', "Menghapus laci: {$kode}");

        return redirect()->route('laci.index')->with('success', "Laci {$kode} berhasil dihapus.");
    }

    public function getByLemari($idLemari)
    {
        $lacis = Laci::where('id_lemari', $idLemari)->orderBy('kode_laci', 'asc')->get(['id', 'kode_laci', 'deskripsi']);
        return response()->json($lacis);
    }
}
