<?php

namespace App\Http\Controllers;

use App\Models\Buku;
use App\Models\EksemplarBuku;
use App\Models\Anggota;
use App\Models\Kategori;
use App\Models\Pengaturan;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Carbon\Carbon;

class LandingController extends Controller
{
    public function index(Request $request)
    {
        $pengaturan = Pengaturan::getPengaturan();

        // 1. Koleksi Buku Terbaru / Pencarian Katalog Publik (OPAC)
        $query = Buku::with(['kategori', 'eksemplar.laci.lemari']);

        if ($request->filled('q')) {
            $search = $request->q;
            $query->where(function ($q) use ($search) {
                $q->where('judul', 'like', "%{$search}%")
                  ->orWhere('pengarang', 'like', "%{$search}%")
                  ->orWhere('penerbit', 'like', "%{$search}%")
                  ->orWhere('isbn', 'like', "%{$search}%")
                  ->orWhere('kode_induk_buku', 'like', "%{$search}%");
            });
        }

        if ($request->filled('id_kategori')) {
            $query->where('id_kategori', $request->id_kategori);
        }

        $bukuList = $query->latest('id')->paginate(8)->withQueryString();
        $kategoriList = Kategori::withCount('buku')->orderBy('nama_kategori', 'asc')->get();

        // 2. Statistik Publik
        $totalJudul = Buku::count();
        $totalEksemplar = EksemplarBuku::count();
        $totalTersedia = EksemplarBuku::where('status', 'tersedia')->count();
        $totalAnggota = Anggota::where('status', 'aktif')->count();

        // Next suggestion nomor anggota untuk form pendaftaran
        $nextAnggotaNo = 'ANG-' . date('Y') . '-' . str_pad(Anggota::count() + 1, 3, '0', STR_PAD_LEFT);

        return view('landing.index', compact(
            'pengaturan',
            'bukuList',
            'kategoriList',
            'totalJudul',
            'totalEksemplar',
            'totalTersedia',
            'totalAnggota',
            'nextAnggotaNo'
        ));
    }

    public function showRegister()
    {
        $pengaturan = Pengaturan::getPengaturan();
        $nextAnggotaNo = 'ANG-' . date('Y') . '-' . str_pad(Anggota::count() + 1, 3, '0', STR_PAD_LEFT);

        return view('landing.register', compact('pengaturan', 'nextAnggotaNo'));
    }

    public function storeRegister(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'identitas' => 'required|string|max:50|unique:anggota,identitas',
            'jenis_identitas' => 'required|in:NIS,NIP,NIK',
            'kelas_jabatan' => 'required|string|max:100',
            'no_hp' => 'nullable|string|max:20',
            'alamat' => 'nullable|string',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ], [
            'identitas.unique' => 'Nomor identitas (NIS/NIP) tersebut sudah terdaftar sebagai anggota perpustakaan.',
        ]);

        if ($request->hasFile('foto')) {
            $validated['foto'] = $request->file('foto')->store('anggota', 'public');
        }

        // Generate Nomor Anggota Unik Otomatis
        $count = Anggota::count() + 1;
        $noAnggota = 'ANG-' . date('Y') . '-' . str_pad($count, 3, '0', STR_PAD_LEFT);
        while (Anggota::where('no_anggota', $noAnggota)->exists()) {
            $count++;
            $noAnggota = 'ANG-' . date('Y') . '-' . str_pad($count, 3, '0', STR_PAD_LEFT);
        }

        $anggota = Anggota::create([
            'no_anggota' => $noAnggota,
            'nama' => $validated['nama'],
            'identitas' => $validated['identitas'],
            'kelas_jabatan' => $validated['kelas_jabatan'] . ' (' . $validated['jenis_identitas'] . ': ' . $validated['identitas'] . ')',
            'no_hp' => $validated['no_hp'] ?? null,
            'alamat' => $validated['alamat'] ?? null,
            'foto' => $validated['foto'] ?? null,
            'tgl_daftar' => Carbon::today(),
            'status' => 'aktif',
        ]);

        ActivityLog::log('Pendaftaran Anggota Mandiri', 'Anggota', "Pendaftaran mandiri oleh {$anggota->nama} ({$anggota->no_anggota}) via Website.");

        return redirect()->route('landing.bukti_register', $anggota->id)
            ->with('success', "Selamat, {$anggota->nama}! Pendaftaran anggota perpustakaan Anda berhasil.");
    }

    public function buktiRegister($id)
    {
        $anggota = Anggota::findOrFail($id);
        $pengaturan = Pengaturan::getPengaturan();

        return view('landing.bukti_register', compact('anggota', 'pengaturan'));
    }
}
