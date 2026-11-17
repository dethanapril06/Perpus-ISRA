<?php

namespace App\Http\Controllers;

use App\Models\Anggota;
use App\Models\ActivityLog;
use App\Helpers\XlsxHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class AnggotaController extends Controller
{
    public function index(Request $request)
    {
        $query = Anggota::withCount(['kunjungan', 'peminjaman']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('no_anggota', 'like', "%{$search}%")
                  ->orWhere('rfid_uid', 'like', "%{$search}%")
                  ->orWhere('nama', 'like', "%{$search}%")
                  ->orWhere('identitas', 'like', "%{$search}%")
                  ->orWhere('kelas_jabatan', 'like', "%{$search}%")
                  ->orWhere('no_hp', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $anggota = $query->orderBy('nama', 'asc')->paginate(10)->withQueryString();

        // Auto generated next code suggestion
        $count = Anggota::count() + 1;
        $nextCode = 'ANG-' . date('Y') . '-' . str_pad($count, 3, '0', STR_PAD_LEFT);

        return view('anggota.index', compact('anggota', 'nextCode'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'no_anggota' => 'required|string|max:50|unique:anggota,no_anggota',
            'rfid_uid' => 'nullable|string|max:50|unique:anggota,rfid_uid',
            'nama' => 'required|string|max:255',
            'identitas' => 'nullable|string|max:50',
            'kelas_jabatan' => 'nullable|string|max:100',
            'no_hp' => 'nullable|string|max:20',
            'alamat' => 'nullable|string',
            'tgl_daftar' => 'required|date',
            'status' => 'required|in:aktif,nonaktif',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($request->hasFile('foto')) {
            $validated['foto'] = $request->file('foto')->store('anggota', 'public');
        }

        $anggota = Anggota::create($validated);
        ActivityLog::log('Tambah Anggota', 'Data Anggota', "Menambahkan anggota baru: {$anggota->nama} ({$anggota->no_anggota})");

        return redirect()->route('anggota.index')->with('success', "Anggota {$anggota->nama} berhasil didaftarkan.");
    }

    public function show(Anggota $anggotum)
    {
        $anggota = $anggotum->load(['kunjungan' => function($q) {
            $q->latest()->limit(10);
        }, 'peminjaman' => function($q) {
            $q->latest()->limit(10);
        }]);

        return view('anggota.show', compact('anggota'));
    }

    public function update(Request $request, Anggota $anggotum)
    {
        $anggota = $anggotum;
        $validated = $request->validate([
            'no_anggota' => ['required', 'string', 'max:50', Rule::unique('anggota')->ignore($anggota->id)],
            'rfid_uid' => ['nullable', 'string', 'max:50', Rule::unique('anggota')->ignore($anggota->id)],
            'nama' => 'required|string|max:255',
            'identitas' => 'nullable|string|max:50',
            'kelas_jabatan' => 'nullable|string|max:100',
            'no_hp' => 'nullable|string|max:20',
            'alamat' => 'nullable|string',
            'tgl_daftar' => 'required|date',
            'status' => 'required|in:aktif,nonaktif',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($request->hasFile('foto')) {
            if ($anggota->foto && Storage::disk('public')->exists($anggota->foto)) {
                Storage::disk('public')->delete($anggota->foto);
            }
            $validated['foto'] = $request->file('foto')->store('anggota', 'public');
        }

        $anggota->update($validated);
        ActivityLog::log('Ubah Anggota', 'Data Anggota', "Memperbarui data anggota: {$anggota->nama} ({$anggota->no_anggota})");

        return redirect()->route('anggota.index')->with('success', "Data anggota {$anggota->nama} berhasil diperbarui.");
    }

    public function destroy(Anggota $anggotum)
    {
        $anggota = $anggotum;
        $nama = $anggota->nama;
        $code = $anggota->no_anggota;

        if ($anggota->foto && Storage::disk('public')->exists($anggota->foto)) {
            Storage::disk('public')->delete($anggota->foto);
        }

        $anggota->delete();
        ActivityLog::log('Hapus Anggota', 'Data Anggota', "Menghapus anggota: {$nama} ({$code})");

        return redirect()->route('anggota.index')->with('success', "Anggota {$nama} berhasil dihapus.");
    }

    // Lookup API for RFID / Barcode scanner in Peminjaman & Kunjungan
    public function lookup(Request $request)
    {
        $code = trim($request->get('code', ''));
        if (!$code) {
            return response()->json(['success' => false, 'message' => 'Kode atau UID RFID anggota kosong.'], 400);
        }

        $anggota = Anggota::with(['peminjamanAktif.buku'])
            ->findByCodeOrRfid($code)
            ->first();

        if (!$anggota) {
            return response()->json(['success' => false, 'message' => 'Data anggota dengan kartu RFID / kode tersebut tidak ditemukan.'], 404);
        }

        // Check active fines
        $hasUnpaidFine = $anggota->hasUnpaidFine();
        $activeLoansCount = $anggota->peminjamanAktif->count();

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $anggota->id,
                'no_anggota' => $anggota->no_anggota,
                'rfid_uid' => $anggota->rfid_uid,
                'nama' => $anggota->nama,
                'identitas' => $anggota->identitas,
                'kelas_jabatan' => $anggota->kelas_jabatan,
                'no_hp' => $anggota->no_hp,
                'status' => $anggota->status,
                'foto_url' => $anggota->foto ? asset('storage/' . $anggota->foto) : null,
                'has_unpaid_fine' => $hasUnpaidFine,
                'active_loans_count' => $activeLoansCount,
                'active_loans' => $anggota->peminjamanAktif,
            ]
        ]);
    }

    public function downloadTemplateExcel()
    {
        $filePath = public_path('templates/template_import_anggota.xlsx');
        if (!file_exists($filePath)) {
            $dir = dirname($filePath);
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            $sampleRows = [
                ['no_anggota', 'rfid_uid', 'nama', 'identitas', 'kelas_jabatan', 'no_hp', 'alamat', 'tgl_daftar', 'status'],
                ['ANG-2026-001', '04A1B2C3D4', 'Ahmad Dahlan', '202401001', 'X IPA 1', '081234567891', 'Jl. Merdeka No. 12', '2026-01-15', 'aktif'],
                ['ANG-2026-002', '04F5E6D7C8', 'Siti Nurhaliza', '202401002', 'X IPA 2', '081234567892', 'Jl. Sudirman No. 45', '2026-01-15', 'aktif'],
                ['ANG-2026-003', '', 'Budi Santoso', '202401003', 'XI IPS 1', '081234567893', 'Jl. Pahlawan No. 78', '2026-01-16', 'aktif'],
                ['ANG-2026-004', '', 'Dewi Sartika', '19850512001', 'Guru Bahasa', '081234567894', 'Jl. Diponegoro No. 05', '2026-01-10', 'aktif'],
            ];
            XlsxHelper::create($filePath, $sampleRows, 'Template Anggota');
        }

        return response()->download($filePath, 'template_import_anggota.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function importExcel(Request $request)
    {
        $request->validate([
            'file_excel' => 'required|file|max:10240', // 10MB
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
            return back()->with('error', 'File Excel kosong atau hanya berisi baris judul/header.');
        }

        // Header mapping
        $header = array_map('trim', array_map('strtolower', $rows[0]));
        $colMap = [
            'no_anggota' => array_search('no_anggota', $header),
            'rfid_uid' => array_search('rfid_uid', $header),
            'nama' => array_search('nama', $header),
            'identitas' => array_search('identitas', $header),
            'kelas_jabatan' => array_search('kelas_jabatan', $header),
            'no_hp' => array_search('no_hp', $header),
            'alamat' => array_search('alamat', $header),
            'tgl_daftar' => array_search('tgl_daftar', $header),
            'status' => array_search('status', $header),
        ];

        if ($colMap['nama'] === false) {
            return back()->with('error', "Kolom header 'nama' tidak ditemukan di file Excel. Pastikan menggunakan format template resmi.");
        }

        $modeDuplikat = $request->mode_duplikat;
        $sukses = 0;
        $diperbarui = 0;
        $dilewati = 0;
        $currentCount = Anggota::count();
        $currentYear = date('Y');

        DB::beginTransaction();
        try {
            for ($i = 1; $i < count($rows); $i++) {
                $row = $rows[$i];
                $nama = isset($colMap['nama']) && isset($row[$colMap['nama']]) ? trim($row[$colMap['nama']]) : '';
                if (!$nama) {
                    continue; // Skip baris kosong
                }

                $noAnggota = ($colMap['no_anggota'] !== false && isset($row[$colMap['no_anggota']])) ? trim($row[$colMap['no_anggota']]) : '';
                $rfidUid = ($colMap['rfid_uid'] !== false && isset($row[$colMap['rfid_uid']])) ? trim($row[$colMap['rfid_uid']]) : null;
                if ($rfidUid === '') $rfidUid = null;

                $identitas = ($colMap['identitas'] !== false && isset($row[$colMap['identitas']])) ? trim($row[$colMap['identitas']]) : null;
                $kelasJabatan = ($colMap['kelas_jabatan'] !== false && isset($row[$colMap['kelas_jabatan']])) ? trim($row[$colMap['kelas_jabatan']]) : null;
                $noHp = ($colMap['no_hp'] !== false && isset($row[$colMap['no_hp']])) ? trim($row[$colMap['no_hp']]) : null;
                $alamat = ($colMap['alamat'] !== false && isset($row[$colMap['alamat']])) ? trim($row[$colMap['alamat']]) : null;

                $tglDaftarRaw = ($colMap['tgl_daftar'] !== false && isset($row[$colMap['tgl_daftar']])) ? trim($row[$colMap['tgl_daftar']]) : '';
                $tglDaftar = null;
                if ($tglDaftarRaw) {
                    try {
                        $tglDaftar = Carbon::parse($tglDaftarRaw)->format('Y-m-d');
                    } catch (\Exception $e) {
                        $tglDaftar = date('Y-m-d');
                    }
                } else {
                    $tglDaftar = date('Y-m-d');
                }

                $statusRaw = ($colMap['status'] !== false && isset($row[$colMap['status']])) ? strtolower(trim($row[$colMap['status']])) : 'aktif';
                $status = ($statusRaw === 'nonaktif') ? 'nonaktif' : 'aktif';

                // Cek duplikasi anggota berdasarkan no_anggota atau identitas
                $existing = null;
                if ($noAnggota) {
                    $existing = Anggota::where('no_anggota', $noAnggota)->first();
                }
                if (!$existing && $identitas) {
                    $existing = Anggota::where('identitas', $identitas)->first();
                }

                // Cek rfid_uid tidak bentrok dengan anggota lain
                if ($rfidUid) {
                    $rfidOwner = Anggota::where('rfid_uid', $rfidUid)->first();
                    if ($rfidOwner && (!$existing || $existing->id !== $rfidOwner->id)) {
                        $rfidUid = null;
                    }
                }

                if ($existing) {
                    if ($modeDuplikat === 'update') {
                        $existing->update([
                            'nama' => $nama,
                            'rfid_uid' => $rfidUid ?: $existing->rfid_uid,
                            'identitas' => $identitas ?: $existing->identitas,
                            'kelas_jabatan' => $kelasJabatan ?: $existing->kelas_jabatan,
                            'no_hp' => $noHp ?: $existing->no_hp,
                            'alamat' => $alamat ?: $existing->alamat,
                            'status' => $status,
                        ]);
                        $diperbarui++;
                    } else {
                        $dilewati++;
                    }
                } else {
                    // Generate no_anggota jika belum ada
                    if (!$noAnggota) {
                        $currentCount++;
                        $noAnggota = 'ANG-' . $currentYear . '-' . str_pad($currentCount, 3, '0', STR_PAD_LEFT);
                    }

                    Anggota::create([
                        'no_anggota' => $noAnggota,
                        'rfid_uid' => $rfidUid,
                        'nama' => $nama,
                        'identitas' => $identitas,
                        'kelas_jabatan' => $kelasJabatan,
                        'no_hp' => $noHp,
                        'alamat' => $alamat,
                        'tgl_daftar' => $tglDaftar,
                        'status' => $status,
                    ]);
                    $sukses++;
                }
            }

            DB::commit();
            ActivityLog::log('Impor Anggota', 'Data Anggota', "Mengimpor {$sukses} anggota baru, {$diperbarui} diperbarui, {$dilewati} dilewati via Excel.");

            return redirect()->route('anggota.index')->with('success', "Proses impor selesai! {$sukses} anggota berhasil ditambahkan, {$diperbarui} diperbarui, dan {$dilewati} dilewati.");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage());
        }
    }

    public function pairingRfid(Request $request)
    {
        $kelasList = Anggota::select('kelas_jabatan')
            ->distinct()
            ->whereNotNull('kelas_jabatan')
            ->where('kelas_jabatan', '!=', '')
            ->orderBy('kelas_jabatan', 'asc')
            ->pluck('kelas_jabatan');

        $totalAnggota = Anggota::count();
        $totalWithRfid = Anggota::whereNotNull('rfid_uid')->where('rfid_uid', '!=', '')->count();
        $totalWithoutRfid = $totalAnggota - $totalWithRfid;

        return view('anggota.pairing_rfid', compact('kelasList', 'totalAnggota', 'totalWithRfid', 'totalWithoutRfid'));
    }

    public function searchForPairing(Request $request)
    {
        $query = Anggota::query();

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('identitas', 'like', "%{$search}%")
                  ->orWhere('no_anggota', 'like', "%{$search}%")
                  ->orWhere('rfid_uid', 'like', "%{$search}%");
            });
        }

        if ($request->filled('kelas')) {
            $query->where('kelas_jabatan', $request->kelas);
        }

        if ($request->filled('status_rfid')) {
            if ($request->status_rfid === 'unpaired') {
                $query->where(function ($q) {
                    $q->whereNull('rfid_uid')->orWhere('rfid_uid', '');
                });
            } elseif ($request->status_rfid === 'paired') {
                $query->whereNotNull('rfid_uid')->where('rfid_uid', '!=', '');
            }
        }

        $anggota = $query->orderBy('nama', 'asc')->limit(60)->get()->map(function ($item) {
            return [
                'id' => $item->id,
                'no_anggota' => $item->no_anggota,
                'rfid_uid' => $item->rfid_uid,
                'nama' => $item->nama,
                'identitas' => $item->identitas ?: '-',
                'kelas_jabatan' => $item->kelas_jabatan ?: '-',
                'foto_url' => $item->foto ? asset('storage/' . $item->foto) : null,
                'status' => $item->status,
                'is_paired' => !empty($item->rfid_uid),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $anggota,
            'total' => count($anggota)
        ]);
    }

    public function saveRfid(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:anggota,id',
            'rfid_uid' => 'required|string|max:50',
        ]);

        $id = $request->id;
        $rfidUid = trim($request->rfid_uid);

        // Cek apakah UID sudah terdaftar pada anggota lain
        $duplicate = Anggota::where('rfid_uid', $rfidUid)->where('id', '!=', $id)->first();
        if ($duplicate) {
            $idText = $duplicate->identitas ?: $duplicate->no_anggota;
            return response()->json([
                'success' => false,
                'message' => "Kartu RFID ini ({$rfidUid}) sudah digunakan oleh anggota lain: {$duplicate->nama} ({$idText}, {$duplicate->kelas_jabatan})."
            ], 422);
        }

        $anggota = Anggota::findOrFail($id);
        $oldRfid = $anggota->rfid_uid;
        $anggota->update(['rfid_uid' => $rfidUid]);

        ActivityLog::log(
            'Pairing RFID',
            'Data Anggota',
            "Menautkan kartu RFID [{$rfidUid}] ke anggota {$anggota->nama} ({$anggota->no_anggota})" . ($oldRfid ? " menggantikan [{$oldRfid}]" : "")
        );

        return response()->json([
            'success' => true,
            'message' => "Kartu RFID ({$rfidUid}) berhasil ditautkan ke {$anggota->nama}!",
            'data' => [
                'id' => $anggota->id,
                'rfid_uid' => $anggota->rfid_uid,
                'nama' => $anggota->nama,
                'no_anggota' => $anggota->no_anggota,
                'kelas_jabatan' => $anggota->kelas_jabatan,
            ]
        ]);
    }

    public function removeRfid(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:anggota,id',
        ]);

        $anggota = Anggota::findOrFail($request->id);
        $oldRfid = $anggota->rfid_uid;
        $anggota->update(['rfid_uid' => null]);

        ActivityLog::log(
            'Lepas RFID',
            'Data Anggota',
            "Melepaskan tautan kartu RFID [{$oldRfid}] dari anggota {$anggota->nama} ({$anggota->no_anggota})"
        );

        return response()->json([
            'success' => true,
            'message' => "Kartu RFID berhasil dilepas dari {$anggota->nama}.",
            'data' => $anggota
        ]);
    }
}
