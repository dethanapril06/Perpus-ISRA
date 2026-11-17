<?php

namespace App\Http\Controllers;

use App\Models\Kategori;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class KategoriController extends Controller
{
    public function index(Request $request)
    {
        $query = Kategori::withCount('buku');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('kode_kategori', 'like', "%{$search}%")
                  ->orWhere('nama_kategori', 'like', "%{$search}%")
                  ->orWhere('deskripsi', 'like', "%{$search}%");
            });
        }

        $kategori = $query->orderBy('kode_kategori', 'asc')->paginate(10)->withQueryString();
        return view('kategori.index', compact('kategori'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode_kategori' => 'required|string|max:50|unique:kategori,kode_kategori',
            'nama_kategori' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
        ]);

        $kat = Kategori::create($validated);
        ActivityLog::log('Tambah Kategori', 'Kategori Buku', "Menambahkan kategori: {$kat->kode_kategori} - {$kat->nama_kategori}");

        return redirect()->route('kategori.index')->with('success', "Kategori {$kat->nama_kategori} berhasil ditambahkan.");
    }

    public function update(Request $request, Kategori $kategori)
    {
        $validated = $request->validate([
            'kode_kategori' => ['required', 'string', 'max:50', Rule::unique('kategori')->ignore($kategori->id)],
            'nama_kategori' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
        ]);

        $kategori->update($validated);
        ActivityLog::log('Ubah Kategori', 'Kategori Buku', "Mengubah kategori: {$kategori->kode_kategori}");

        return redirect()->route('kategori.index')->with('success', "Kategori {$kategori->nama_kategori} berhasil diperbarui.");
    }

    public function destroy(Kategori $kategori)
    {
        // FR-KAT-02: Cegah penghapusan jika masih digunakan buku
        if ($kategori->buku()->count() > 0) {
            return back()->with('error', "Kategori '{$kategori->nama_kategori}' tidak dapat dihapus karena masih digunakan oleh {$kategori->buku()->count()} data buku.");
        }

        $nama = $kategori->nama_kategori;
        $kategori->delete();

        ActivityLog::log('Hapus Kategori', 'Kategori Buku', "Menghapus kategori: {$nama}");

        return redirect()->route('kategori.index')->with('success', "Kategori '{$nama}' berhasil dihapus.");
    }
}
