<?php

namespace App\Http\Controllers;

use App\Models\Buku;
use App\Models\EksemplarBuku;
use App\Models\Kategori;
use App\Models\Lemari;
use App\Models\Laci;
use App\Models\ActivityLog;
use App\Helpers\XlsxHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class BukuController extends Controller
{
    public function index(Request $request)
    {
        $query = Buku::with(['kategori', 'eksemplar.laci.lemari']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('kode_induk_buku', 'like', "%{$search}%")
                  ->orWhere('judul', 'like', "%{$search}%")
                  ->orWhere('pengarang', 'like', "%{$search}%")
                  ->orWhere('penerbit', 'like', "%{$search}%")
                  ->orWhere('isbn', 'like', "%{$search}%")
                  ->orWhereHas('eksemplar', function ($qe) use ($search) {
                      $qe->where('kode_eksemplar', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('id_kategori')) {
            $query->where('id_kategori', $request->id_kategori);
        }

        $buku = $query->orderBy('judul', 'asc')->paginate(10)->withQueryString();
        $kategoriList = Kategori::orderBy('nama_kategori', 'asc')->get();
        $lemariList = Lemari::with('laci')->orderBy('kode_lemari', 'asc')->get();

        // Auto generated next book code suggestion
        $count = Buku::count() + 1;
        $nextBookCode = 'BK-' . date('Y') . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);

        return view('buku.index', compact('buku', 'kategoriList', 'lemariList', 'nextBookCode'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode_induk_buku' => 'required|string|max:50|unique:buku,kode_induk_buku',
            'judul' => 'required|string|max:255',
            'pengarang' => 'nullable|string|max:255',
            'penerbit' => 'nullable|string|max:255',
            'tahun' => 'nullable|integer|min:1900|max:' . (date('Y') + 1),
            'isbn' => 'nullable|string|max:50',
            'id_kategori' => 'nullable|exists:kategori,id',
            'id_laci' => 'nullable|exists:laci,id',
            'jumlah_eksemplar' => 'required|integer|min:1|max:50',
            'deskripsi' => 'nullable|string',
            'cover' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($request->hasFile('cover')) {
            $validated['cover'] = $request->file('cover')->store('buku', 'public');
        }

        DB::transaction(function () use ($validated, $request) {
            $buku = Buku::create([
                'kode_induk_buku' => $validated['kode_induk_buku'],
                'judul' => $validated['judul'],
                'pengarang' => $validated['pengarang'] ?? null,
                'penerbit' => $validated['penerbit'] ?? null,
                'tahun' => $validated['tahun'] ?? null,
                'isbn' => $validated['isbn'] ?? null,
                'id_kategori' => $validated['id_kategori'] ?? null,
                'deskripsi' => $validated['deskripsi'] ?? null,
                'cover' => $validated['cover'] ?? null,
            ]);

            // Auto-generate N Eksemplar Fisik
            $jml = (int) $validated['jumlah_eksemplar'];
            for ($i = 1; $i <= $jml; $i++) {
                EksemplarBuku::create([
                    'id_buku' => $buku->id,
                    'kode_eksemplar' => $buku->kode_induk_buku . '.' . str_pad($i, 2, '0', STR_PAD_LEFT),
                    'no_inventaris' => 'INV/' . date('Y') . '/' . str_pad($buku->id, 3, '0', STR_PAD_LEFT) . '-' . str_pad($i, 2, '0', STR_PAD_LEFT),
                    'id_laci' => $validated['id_laci'] ?? null,
                    'kondisi' => 'baik',
                    'status' => 'tersedia',
                ]);
            }

            ActivityLog::log('Tambah Buku & Eksemplar', 'Data Buku', "Menambahkan buku '{$buku->judul}' dengan {$jml} eksemplar fisik barcode unik.");
        });

        return redirect()->route('buku.index')->with('success', "Buku '{$validated['judul']}' beserta {$validated['jumlah_eksemplar']} eksemplar fisik berhasil dibuat.");
    }

    public function show(Buku $buku)
    {
        $buku->load([
            'kategori',
            'eksemplar.laci.lemari',
            'eksemplar.peminjaman' => function($q) {
                $q->with('anggota')->latest()->limit(5);
            }
        ]);

        $lemariList = Lemari::with('laci')->orderBy('kode_lemari', 'asc')->get();

        // Next copy code suggestion
        $nextCopyNumber = $buku->eksemplar()->count() + 1;
        $nextCopyCode = $buku->kode_induk_buku . '.' . str_pad($nextCopyNumber, 2, '0', STR_PAD_LEFT);

        return view('buku.show', compact('buku', 'lemariList', 'nextCopyCode'));
    }

    public function update(Request $request, Buku $buku)
    {
        $validated = $request->validate([
            'kode_induk_buku' => ['required', 'string', 'max:50', Rule::unique('buku')->ignore($buku->id)],
            'judul' => 'required|string|max:255',
            'pengarang' => 'nullable|string|max:255',
            'penerbit' => 'nullable|string|max:255',
            'tahun' => 'nullable|integer|min:1900|max:' . (date('Y') + 1),
            'isbn' => 'nullable|string|max:50',
            'id_kategori' => 'nullable|exists:kategori,id',
            'deskripsi' => 'nullable|string',
            'cover' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($request->hasFile('cover')) {
            if ($buku->cover && Storage::disk('public')->exists($buku->cover)) {
                Storage::disk('public')->delete($buku->cover);
            }
            $validated['cover'] = $request->file('cover')->store('buku', 'public');
        }

        $buku->update($validated);
        ActivityLog::log('Ubah Data Buku', 'Data Buku', "Memperbarui katalog buku: {$buku->judul}");

        return redirect()->route('buku.show', $buku->id)->with('success', "Data buku {$buku->judul} berhasil diperbarui.");
    }

    public function destroy(Buku $buku)
    {
        // Check if any copy is currently loaned
        $activeLoans = Peminjaman::whereIn('id_eksemplar', $buku->eksemplar()->pluck('id'))
            ->whereIn('status', ['Dipinjam', 'Terlambat'])
            ->exists();

        if ($activeLoans) {
            return back()->with('error', "Buku '{$buku->judul}' tidak dapat dihapus karena masih ada eksemplar fisik yang sedang dipinjam.");
        }

        $judul = $buku->judul;
        if ($buku->cover && Storage::disk('public')->exists($buku->cover)) {
            Storage::disk('public')->delete($buku->cover);
        }

        $buku->delete();
        ActivityLog::log('Hapus Buku', 'Data Buku', "Menghapus buku beserta seluruh eksemplar fisiknya: {$judul}");

        return redirect()->route('buku.index')->with('success', "Buku {$judul} beserta eksemplarnya berhasil dihapus.");
    }

    // Endpoint Tambah 1 Eksemplar Tambahan
    public function storeEksemplar(Request $request, Buku $buku)
    {
        $validated = $request->validate([
            'kode_eksemplar' => 'required|string|max:50|unique:eksemplar_buku,kode_eksemplar',
            'no_inventaris' => 'nullable|string|max:50',
            'id_laci' => 'nullable|exists:laci,id',
            'kondisi' => 'required|in:baik,rusak,hilang',
            'status' => 'required|in:tersedia,dipinjam,rusak,hilang',
        ]);

        $validated['id_buku'] = $buku->id;
        $eksemplar = EksemplarBuku::create($validated);

        ActivityLog::log('Tambah Eksemplar', 'Data Buku', "Menambahkan fisik eksemplar {$eksemplar->kode_eksemplar} pada buku '{$buku->judul}'");

        return back()->with('success', "Eksemplar fisik {$eksemplar->kode_eksemplar} berhasil ditambahkan.");
    }

    // Endpoint Update Eksemplar Fisik Spesifik
    public function updateEksemplar(Request $request, EksemplarBuku $eksemplar)
    {
        $validated = $request->validate([
            'kode_eksemplar' => ['required', 'string', 'max:50', Rule::unique('eksemplar_buku')->ignore($eksemplar->id)],
            'no_inventaris' => 'nullable|string|max:50',
            'id_laci' => 'nullable|exists:laci,id',
            'kondisi' => 'required|in:baik,rusak,hilang',
            'status' => 'required|in:tersedia,dipinjam,rusak,hilang',
        ]);

        $eksemplar->update($validated);
        ActivityLog::log('Ubah Eksemplar', 'Data Buku', "Memperbarui data fisik eksemplar {$eksemplar->kode_eksemplar}");

        return back()->with('success', "Eksemplar {$eksemplar->kode_eksemplar} berhasil diperbarui.");
    }

    // Endpoint Hapus 1 Eksemplar Fisik
    public function destroyEksemplar(EksemplarBuku $eksemplar)
    {
        if ($eksemplar->peminjaman()->whereIn('status', ['Dipinjam', 'Terlambat'])->exists()) {
            return back()->with('error', "Eksemplar {$eksemplar->kode_eksemplar} sedang dalam masa pinjaman dan tidak dapat dihapus.");
        }

        $code = $eksemplar->kode_eksemplar;
        $eksemplar->delete();

        ActivityLog::log('Hapus Eksemplar', 'Data Buku', "Menghapus eksemplar fisik: {$code}");

        return back()->with('success', "Eksemplar {$code} berhasil dihapus.");
    }

    // Cetak Seluruh Barcode Eksemplar Milik 1 Judul Buku
    public function cetakBarcode($id)
    {
        $buku = Buku::with(['kategori', 'eksemplar.laci.lemari'])->findOrFail($id);
        return view('buku.cetak_barcode', compact('buku'));
    }

    // Cetak 1 Barcode Eksemplar Spesifik
    public function cetakBarcodeSingle($idEksemplar)
    {
        $eksemplar = EksemplarBuku::with(['buku.kategori', 'laci.lemari'])->findOrFail($idEksemplar);
        return view('buku.cetak_barcode_single', compact('eksemplar'));
    }

    // Cetak Massal Seluruh Eksemplar di Perpustakaan
    public function cetakSemuaBarcode(Request $request)
    {
        $query = EksemplarBuku::with(['buku.kategori', 'laci.lemari']);
        if ($request->filled('id_kategori')) {
            $query->whereHas('buku', function($q) use ($request) {
                $q->where('id_kategori', $request->id_kategori);
            });
        }
        $eksemplars = $query->orderBy('kode_eksemplar', 'asc')->get();
        return view('buku.cetak_barcode_massal', compact('eksemplars'));
    }

    // Scanner Lookup API (Support scan kode eksemplar)
    public function lookup(Request $request)
    {
        $code = trim($request->get('code', ''));
        if (!$code) {
            return response()->json(['success' => false, 'message' => 'Kode barcode kosong.'], 400);
        }

        // 1. Cari berdasarkan kode_eksemplar persis
        $eksemplar = EksemplarBuku::with(['buku.kategori', 'laci.lemari'])
            ->where('kode_eksemplar', $code)
            ->first();

        // 2. Jika di-scan adalah kode_induk_buku, ambil eksemplar pertama yang tersedia
        if (!$eksemplar) {
            $buku = Buku::with(['kategori'])->where('kode_induk_buku', $code)->first();
            if ($buku) {
                $eksemplar = $buku->eksemplar()->where('status', 'tersedia')->first();
                if (!$eksemplar) {
                    $eksemplar = $buku->eksemplar()->first();
                }
            }
        }

        if (!$eksemplar) {
            return response()->json(['success' => false, 'message' => "Buku / Eksemplar dengan kode '{$code}' tidak ditemukan."], 404);
        }

        $buku = $eksemplar->buku;
        $lokasiText = $eksemplar->laci 
            ? ($eksemplar->laci->lemari->kode_lemari . ' -> ' . $eksemplar->laci->kode_laci) 
            : 'Belum ditentukan';

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $eksemplar->id, // ID Eksemplar
                'id_buku' => $buku->id,
                'kode_eksemplar' => $eksemplar->kode_eksemplar,
                'kode_induk_buku' => $buku->kode_induk_buku,
                'judul' => $buku->judul,
                'pengarang' => $buku->pengarang,
                'penerbit' => $buku->penerbit,
                'kategori' => $buku->kategori ? $buku->kategori->nama_kategori : '-',
                'lokasi' => $lokasiText,
                'kondisi' => $eksemplar->kondisi,
                'status' => $eksemplar->status,
                'is_available' => $eksemplar->isTersedia(),
                'stok_tersedia' => $buku->stok,
                'total_eksemplar' => $buku->total_eksemplar,
                'cover_url' => $buku->cover ? asset('storage/' . $buku->cover) : null,
            ]
        ]);
    }

    public function downloadTemplateExcel()
    {
        $filePath = public_path('templates/template_import_buku.xlsx');
        if (!file_exists($filePath)) {
            $dir = dirname($filePath);
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            $sampleRows = [
                ['kode_induk_buku', 'kode_eksemplar', 'judul', 'pengarang', 'penerbit', 'tahun', 'isbn', 'kategori', 'kode_laci', 'no_inventaris', 'kondisi', 'status', 'deskripsi'],
                ['BK-2026-0001', 'BK-2026-0001.01', 'Laskar Pelangi', 'Andrea Hirata', 'Bentang Pustaka', '2008', '978-979-3062-79-2', 'Fiksi', 'L-01', 'INV/2026/001-01', 'baik', 'tersedia', 'Kisah 10 anak laskar pelangi di Belitung'],
                ['BK-2026-0001', 'BK-2026-0001.02', 'Laskar Pelangi', 'Andrea Hirata', 'Bentang Pustaka', '2008', '978-979-3062-79-2', 'Fiksi', 'L-01', 'INV/2026/001-02', 'baik', 'tersedia', 'Eksemplar fisik ke-2'],
                ['BK-2026-0002', 'BK-2026-0002.01', 'Bumi Manusia', 'Pramoedya Ananta Toer', 'Hasta Mitra', '2005', '978-979-97312-3-4', 'Sastra', 'L-01', 'INV/2026/002-01', 'baik', 'tersedia', 'Novel tetralogi buru'],
                ['', '', 'Matematika untuk SMA/MA Kelas X', 'B.K. Noormandiri', 'Erlangga', '2022', '978-623-266-614-6', 'Pelajaran', 'L-02', '', 'baik', 'tersedia', 'Buku teks kurikulum merdeka'],
                ['', '', 'Matematika untuk SMA/MA Kelas X', 'B.K. Noormandiri', 'Erlangga', '2022', '978-623-266-614-6', 'Pelajaran', 'L-02', '', 'baik', 'tersedia', 'Eksemplar fisik ke-2 (otomatis .02)'],
            ];
            XlsxHelper::create($filePath, $sampleRows, 'Template Buku & Eksemplar');
        }

        return response()->download($filePath, 'template_import_buku.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function importExcel(Request $request)
    {
        $request->validate([
            'file_excel' => 'required|file|max:10240', // Maks 10MB
            'mode_duplikat' => 'required|in:skip,update',
        ]);

        $file = $request->file('file_excel');
        $ext = strtolower($file->getClientOriginalExtension());
        if ($ext !== 'xlsx') {
            return back()->with('error', 'Format file tidak didukung. Harap unggah file spreadsheet Excel (.xlsx).');
        }

        try {
            $rows = XlsxHelper::read($file->getRealPath());
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal membaca file Excel: ' . $e->getMessage());
        }

        if (count($rows) <= 1) {
            return back()->with('error', 'File Excel kosong atau hanya berisi baris header kolom.');
        }

        // Header mapping
        $header = array_map('trim', array_map('strtolower', $rows[0]));
        $colMap = [
            'kode_induk_buku' => array_search('kode_induk_buku', $header),
            'kode_eksemplar' => array_search('kode_eksemplar', $header),
            'judul' => array_search('judul', $header),
            'pengarang' => array_search('pengarang', $header),
            'penerbit' => array_search('penerbit', $header),
            'tahun' => array_search('tahun', $header),
            'isbn' => array_search('isbn', $header),
            'kategori' => array_search('kategori', $header),
            'kode_laci' => array_search('kode_laci', $header),
            'no_inventaris' => array_search('no_inventaris', $header),
            'kondisi' => array_search('kondisi', $header),
            'status' => array_search('status', $header),
            'deskripsi' => array_search('deskripsi', $header),
        ];

        if ($colMap['judul'] === false) {
            return back()->with('error', "Kolom header 'judul' wajib ada pada file Excel. Pastikan menggunakan format template resmi.");
        }

        $modeDuplikat = $request->mode_duplikat;
        $bukuBaru = 0;
        $eksemplarBaru = 0;
        $eksemplarDiperbarui = 0;
        $eksemplarDilewati = 0;

        DB::beginTransaction();
        try {
            // Cache referensi data master untuk performa tinggi
            $kategoriMap = [];
            foreach (Kategori::all() as $k) {
                $kategoriMap[strtolower(trim($k->nama_kategori))] = $k;
            }

            $laciMap = [];
            foreach (Laci::all() as $l) {
                $laciMap[strtolower(trim($l->kode_laci))] = $l;
            }

            $bukuCodeMap = [];
            $bukuTitleMap = [];
            foreach (Buku::all() as $b) {
                $bukuCodeMap[strtolower(trim($b->kode_induk_buku))] = $b;
                $key = strtolower(trim($b->judul)) . '|' . strtolower(trim($b->pengarang ?? ''));
                $bukuTitleMap[$key] = $b;
            }

            $eksemplarCountMap = [];
            $currentYear = date('Y');
            $bookCount = Buku::count();

            for ($i = 1; $i < count($rows); $i++) {
                $row = $rows[$i];
                $judul = ($colMap['judul'] !== false && isset($row[$colMap['judul']])) ? trim($row[$colMap['judul']]) : '';
                if (!$judul) {
                    continue; // Skip baris tanpa judul
                }

                $kodeInduk = ($colMap['kode_induk_buku'] !== false && isset($row[$colMap['kode_induk_buku']])) ? trim($row[$colMap['kode_induk_buku']]) : '';
                $kodeEksemplar = ($colMap['kode_eksemplar'] !== false && isset($row[$colMap['kode_eksemplar']])) ? trim($row[$colMap['kode_eksemplar']]) : '';
                $pengarang = ($colMap['pengarang'] !== false && isset($row[$colMap['pengarang']])) ? trim($row[$colMap['pengarang']]) : null;
                $penerbit = ($colMap['penerbit'] !== false && isset($row[$colMap['penerbit']])) ? trim($row[$colMap['penerbit']]) : null;
                $tahun = ($colMap['tahun'] !== false && isset($row[$colMap['tahun']]) && is_numeric(trim($row[$colMap['tahun']]))) ? (int)trim($row[$colMap['tahun']]) : null;
                $isbn = ($colMap['isbn'] !== false && isset($row[$colMap['isbn']])) ? trim($row[$colMap['isbn']]) : null;
                $kategoriName = ($colMap['kategori'] !== false && isset($row[$colMap['kategori']])) ? trim($row[$colMap['kategori']]) : '';
                $kodeLaci = ($colMap['kode_laci'] !== false && isset($row[$colMap['kode_laci']])) ? trim($row[$colMap['kode_laci']]) : '';
                $noInventaris = ($colMap['no_inventaris'] !== false && isset($row[$colMap['no_inventaris']])) ? trim($row[$colMap['no_inventaris']]) : null;
                $kondisi = ($colMap['kondisi'] !== false && isset($row[$colMap['kondisi']])) ? strtolower(trim($row[$colMap['kondisi']])) : 'baik';
                if (!in_array($kondisi, ['baik', 'rusak', 'hilang'])) $kondisi = 'baik';
                $status = ($colMap['status'] !== false && isset($row[$colMap['status']])) ? strtolower(trim($row[$colMap['status']])) : 'tersedia';
                if (!in_array($status, ['tersedia', 'dipinjam', 'rusak', 'hilang'])) $status = 'tersedia';
                $deskripsi = ($colMap['deskripsi'] !== false && isset($row[$colMap['deskripsi']])) ? trim($row[$colMap['deskripsi']]) : null;

                // 1. Resolve Kategori
                $kategoriId = null;
                if ($kategoriName) {
                    $katKey = strtolower($kategoriName);
                    if (isset($kategoriMap[$katKey])) {
                        $kategoriId = $kategoriMap[$katKey]->id;
                    } else {
                        $countKat = Kategori::count() + 1;
                        $newKat = Kategori::create([
                            'kode_kategori' => 'KAT-' . str_pad($countKat, 3, '0', STR_PAD_LEFT),
                            'nama_kategori' => $kategoriName,
                        ]);
                        $kategoriMap[$katKey] = $newKat;
                        $kategoriId = $newKat->id;
                    }
                }

                // 2. Resolve Laci Lokasi
                $laciId = null;
                if ($kodeLaci) {
                    $laciKey = strtolower($kodeLaci);
                    if (isset($laciMap[$laciKey])) {
                        $laciId = $laciMap[$laciKey]->id;
                    }
                }

                // 3. Find or Create Buku Induk (Bibliografi)
                $buku = null;
                if ($kodeInduk && isset($bukuCodeMap[strtolower($kodeInduk)])) {
                    $buku = $bukuCodeMap[strtolower($kodeInduk)];
                } else {
                    $titleKey = strtolower($judul) . '|' . strtolower($pengarang ?? '');
                    if (isset($bukuTitleMap[$titleKey])) {
                        $buku = $bukuTitleMap[$titleKey];
                    }
                }

                if (!$buku) {
                    if (!$kodeInduk) {
                        $bookCount++;
                        $kodeInduk = 'BK-' . $currentYear . '-' . str_pad($bookCount, 4, '0', STR_PAD_LEFT);
                    }

                    $buku = Buku::create([
                        'kode_induk_buku' => $kodeInduk,
                        'judul' => $judul,
                        'pengarang' => $pengarang,
                        'penerbit' => $penerbit,
                        'tahun' => $tahun,
                        'isbn' => $isbn,
                        'id_kategori' => $kategoriId,
                        'deskripsi' => $deskripsi,
                    ]);

                    $bukuCodeMap[strtolower($kodeInduk)] = $buku;
                    $titleKey = strtolower($judul) . '|' . strtolower($pengarang ?? '');
                    $bukuTitleMap[$titleKey] = $buku;
                    $bukuBaru++;
                }

                // 4. Resolve Eksemplar Fisik
                if (!isset($eksemplarCountMap[$buku->id])) {
                    $eksemplarCountMap[$buku->id] = $buku->eksemplar()->count();
                }

                $existingEksemplar = null;
                if ($kodeEksemplar) {
                    $existingEksemplar = EksemplarBuku::where('kode_eksemplar', $kodeEksemplar)->first();
                }

                if ($existingEksemplar) {
                    if ($modeDuplikat === 'update') {
                        $existingEksemplar->update([
                            'id_buku' => $buku->id,
                            'no_inventaris' => $noInventaris ?: $existingEksemplar->no_inventaris,
                            'id_laci' => $laciId ?: $existingEksemplar->id_laci,
                            'kondisi' => $kondisi,
                            'status' => $status,
                        ]);
                        $eksemplarDiperbarui++;
                    } else {
                        $eksemplarDilewati++;
                    }
                } else {
                    // Buat eksemplar fisik baru
                    $eksemplarCountMap[$buku->id]++;
                    $seq = $eksemplarCountMap[$buku->id];

                    if (!$kodeEksemplar) {
                        $kodeEksemplar = $buku->kode_induk_buku . '.' . str_pad($seq, 2, '0', STR_PAD_LEFT);
                        while (EksemplarBuku::where('kode_eksemplar', $kodeEksemplar)->exists()) {
                            $seq++;
                            $eksemplarCountMap[$buku->id] = $seq;
                            $kodeEksemplar = $buku->kode_induk_buku . '.' . str_pad($seq, 2, '0', STR_PAD_LEFT);
                        }
                    }

                    if (!$noInventaris) {
                        $noInventaris = 'INV/' . $currentYear . '/' . str_pad($buku->id, 3, '0', STR_PAD_LEFT) . '-' . str_pad($seq, 2, '0', STR_PAD_LEFT);
                    }

                    EksemplarBuku::create([
                        'id_buku' => $buku->id,
                        'kode_eksemplar' => $kodeEksemplar,
                        'no_inventaris' => $noInventaris,
                        'id_laci' => $laciId,
                        'kondisi' => $kondisi,
                        'status' => $status,
                    ]);
                    $eksemplarBaru++;
                }
            }

            DB::commit();

            ActivityLog::log(
                'Impor Buku & Eksemplar',
                'Data Buku',
                "Mengimpor via Excel: {$bukuBaru} judul buku baru, {$eksemplarBaru} eksemplar baru, {$eksemplarDiperbarui} diperbarui, {$eksemplarDilewati} dilewati."
            );

            return redirect()->route('buku.index')->with('success', 
                "Proses impor buku selesai! {$bukuBaru} judul buku baru terdaftar, {$eksemplarBaru} eksemplar fisik berhasil dibuat" . 
                ($eksemplarDiperbarui ? ", {$eksemplarDiperbarui} eksemplar diperbarui" : "") . 
                ($eksemplarDilewati ? ", {$eksemplarDilewati} eksemplar dilewati" : "") . "."
            );
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan saat memproses data Excel: ' . $e->getMessage());
        }
    }
}
