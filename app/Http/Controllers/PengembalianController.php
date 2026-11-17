<?php

namespace App\Http\Controllers;

use App\Models\Peminjaman;
use App\Models\Anggota;
use App\Models\Buku;
use App\Models\EksemplarBuku;
use App\Models\Denda;
use App\Models\Pengaturan;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PengembalianController extends Controller
{
    public function index()
    {
        $pengaturan = Pengaturan::getPengaturan();
        $riwayatPengembalian = Peminjaman::with(['anggota', 'eksemplar.buku.kategori', 'denda'])
            ->where('status', 'Selesai')
            ->latest('tgl_kembali')
            ->paginate(10);

        return view('pengembalian.index', compact('pengaturan', 'riwayatPengembalian'));
    }

    public function checkActiveLoan(Request $request)
    {
        $kodeAnggota = trim($request->get('kode_anggota', ''));
        $kodeScan = trim($request->get('kode_buku', '')); // Barcode Eksemplar

        if (!$kodeAnggota && !$kodeScan) {
            return response()->json(['success' => false, 'message' => 'Harap masukkan scan kode anggota atau barcode eksemplar buku.'], 400);
        }

        $pengaturan = Pengaturan::getPengaturan();
        $today = Carbon::today()->startOfDay();

        $query = Peminjaman::with(['anggota', 'eksemplar.buku.kategori', 'eksemplar.laci.lemari'])
            ->whereIn('status', ['Dipinjam', 'Terlambat']);

        if ($kodeAnggota) {
            $query->whereHas('anggota', function ($q) use ($kodeAnggota) {
                $q->where('rfid_uid', $kodeAnggota)
                  ->orWhere('no_anggota', $kodeAnggota)
                  ->orWhere('identitas', $kodeAnggota);
            });
        }

        if ($kodeScan) {
            $query->whereHas('eksemplar', function ($qe) use ($kodeScan) {
                $qe->where('kode_eksemplar', $kodeScan);
            });
        }

        $loans = $query->get()->map(function ($loan) use ($today, $pengaturan) {
            $jatuhTempo = Carbon::parse($loan->tgl_jatuh_tempo)->startOfDay();
            $hariTerlambat = 0;
            $nominalDenda = 0;

            if ($today->gt($jatuhTempo)) {
                $hariTerlambat = (int) $jatuhTempo->diffInDays($today);
                $nominalDenda = $hariTerlambat * $pengaturan->nominal_per_hari;

                if ($pengaturan->maks_denda && $nominalDenda > $pengaturan->maks_denda) {
                    $nominalDenda = $pengaturan->maks_denda;
                }
            }

            return [
                'id' => $loan->id,
                'id_eksemplar' => $loan->eksemplar->id ?? 0,
                'anggota' => [
                    'id' => $loan->anggota->id,
                    'no_anggota' => $loan->anggota->no_anggota,
                    'nama' => $loan->anggota->nama,
                    'kelas_jabatan' => $loan->anggota->kelas_jabatan,
                ],
                'buku' => [
                    'id' => $loan->eksemplar->buku->id ?? 0,
                    'kode_eksemplar' => $loan->eksemplar->kode_eksemplar ?? '-',
                    'kode_induk' => $loan->eksemplar->buku->kode_induk_buku ?? '-',
                    'judul' => $loan->eksemplar->buku->judul ?? '-',
                    'kategori' => $loan->eksemplar->buku->kategori ? $loan->eksemplar->buku->kategori->nama_kategori : '-',
                    'cover_url' => ($loan->eksemplar && $loan->eksemplar->buku->cover) ? asset('storage/' . $loan->eksemplar->buku->cover) : null,
                ],
                'tgl_pinjam' => $loan->tgl_pinjam->format('d/m/Y'),
                'tgl_jatuh_tempo' => $loan->tgl_jatuh_tempo->format('d/m/Y'),
                'hari_terlambat' => $hariTerlambat,
                'nominal_denda' => $nominalDenda,
                'nominal_denda_formatted' => 'Rp ' . number_format($nominalDenda, 0, ',', '.'),
            ];
        });

        if ($loans->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'Tidak ditemukan data peminjaman aktif yang cocok dengan kode yang di-scan.'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $loans,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_peminjaman' => 'required|exists:peminjaman,id',
            'tgl_kembali' => 'required|date',
            'kondisi_kembali' => 'required|in:baik,rusak,hilang',
            'catatan' => 'nullable|string',
        ]);

        $peminjaman = Peminjaman::with(['anggota', 'eksemplar.buku'])->findOrFail($request->id_peminjaman);

        if ($peminjaman->status === 'Selesai') {
            return back()->with('error', 'Transaksi peminjaman ini sudah selesai sebelumnya.');
        }

        $pengaturan = Pengaturan::getPengaturan();
        $tglKembali = Carbon::parse($request->tgl_kembali)->startOfDay();
        $tglJatuhTempo = Carbon::parse($peminjaman->tgl_jatuh_tempo)->startOfDay();

        $hariTerlambat = 0;
        $nominalDenda = 0;

        if ($tglKembali->gt($tglJatuhTempo)) {
            $hariTerlambat = (int) $tglJatuhTempo->diffInDays($tglKembali);
            $nominalDenda = $hariTerlambat * $pengaturan->nominal_per_hari;

            if ($pengaturan->maks_denda && $nominalDenda > $pengaturan->maks_denda) {
                $nominalDenda = $pengaturan->maks_denda;
            }
        }

        DB::transaction(function () use ($peminjaman, $tglKembali, $request, $nominalDenda) {
            // Update peminjaman status
            $peminjaman->update([
                'tgl_kembali' => $tglKembali,
                'status' => 'Selesai',
                'kondisi_kembali' => $request->kondisi_kembali,
                'catatan' => $request->catatan ?: $peminjaman->catatan,
            ]);

            // Update status eksemplar fisik
            $eksemplar = $peminjaman->eksemplar;
            if ($eksemplar) {
                if ($request->kondisi_kembali === 'hilang') {
                    $eksemplar->update(['status' => 'hilang', 'kondisi' => 'hilang']);
                } elseif ($request->kondisi_kembali === 'rusak') {
                    $eksemplar->update(['status' => 'rusak', 'kondisi' => 'rusak']);
                } else {
                    $eksemplar->update(['status' => 'tersedia', 'kondisi' => 'baik']);
                }
            }

            // Create record denda if fine > 0
            if ($nominalDenda > 0) {
                Denda::create([
                    'id_peminjaman' => $peminjaman->id,
                    'nominal' => $nominalDenda,
                    'status_bayar' => 'Belum Lunas',
                ]);
            }

            $bukuJudul = $peminjaman->eksemplar->buku->judul ?? 'Buku';
            $kodeEks = $peminjaman->eksemplar->kode_eksemplar ?? '-';
            ActivityLog::log(
                'Pengembalian Buku', 
                'Pengembalian', 
                "Pengembalian eksemplar '{$kodeEks}' ({$bukuJudul}) oleh {$peminjaman->anggota->nama}. Kondisi: {$request->kondisi_kembali}. Denda: Rp " . number_format($nominalDenda, 0, ',', '.')
            );
        });

        $bukuJudul = $peminjaman->eksemplar->buku->judul ?? 'Buku';
        $msg = "Pengembalian buku '{$bukuJudul}' (Eks. {$peminjaman->eksemplar->kode_eksemplar}) oleh {$peminjaman->anggota->nama} berhasil diproses.";
        if ($nominalDenda > 0) {
            $msg .= " Terdapat denda keterlambatan sebesar Rp " . number_format($nominalDenda, 0, ',', '.') . " (" . $hariTerlambat . " hari terlambat).";
            return redirect()->route('pengembalian.index')->with('warning', $msg);
        }

        return redirect()->route('pengembalian.index')->with('success', $msg);
    }
}
