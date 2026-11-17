<?php

namespace App\Http\Controllers;

use App\Models\Peminjaman;
use App\Models\Anggota;
use App\Models\Buku;
use App\Models\EksemplarBuku;
use App\Models\Pengaturan;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PeminjamanController extends Controller
{
    public function index(Request $request)
    {
        $query = Peminjaman::with(['anggota', 'eksemplar.buku.kategori', 'user', 'denda']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('anggota', function ($qa) use ($search) {
                    $qa->where('nama', 'like', "%{$search}%")
                       ->orWhere('no_anggota', 'like', "%{$search}%")
                       ->orWhere('rfid_uid', 'like', "%{$search}%");
                })->orWhereHas('eksemplar.buku', function ($qb) use ($search) {
                    $qb->where('judul', 'like', "%{$search}%")
                       ->orWhere('kode_induk_buku', 'like', "%{$search}%");
                })->orWhereHas('eksemplar', function ($qe) use ($search) {
                    $qe->where('kode_eksemplar', 'like', "%{$search}%");
                });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('tgl_mulai')) {
            $query->whereDate('tgl_pinjam', '>=', $request->tgl_mulai);
        }

        if ($request->filled('tgl_selesai')) {
            $query->whereDate('tgl_pinjam', '<=', $request->tgl_selesai);
        }

        $peminjaman = $query->latest('id')->paginate(10)->withQueryString();
        $pengaturan = Pengaturan::getPengaturan();

        return view('peminjaman.index', compact('peminjaman', 'pengaturan'));
    }

    public function create()
    {
        $pengaturan = Pengaturan::getPengaturan();
        $anggotaList = Anggota::where('status', 'aktif')->orderBy('nama', 'asc')->get();

        return view('peminjaman.create', compact('pengaturan', 'anggotaList'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'kode_anggota' => 'required|string',
            'id_eksemplar' => 'nullable|array|min:1',
            'id_eksemplar.*' => 'exists:eksemplar_buku,id',
            'kode_buku' => 'nullable|string', // Fallback single scan
            'tgl_pinjam' => 'required|date',
            'durasi_hari' => 'required|integer|min:1',
            'catatan' => 'nullable|string',
        ]);

        $kodeAnggota = trim($request->kode_anggota);
        $anggota = Anggota::findByCodeOrRfid($kodeAnggota)->first();
        if (!$anggota) {
            return back()->withInput()->with('error', "Anggota dengan kartu RFID / kode '{$kodeAnggota}' tidak ditemukan.");
        }

        // FR-PJM-02: Validasi status anggota
        if ($anggota->status !== 'aktif') {
            return back()->withInput()->with('error', "Peminjaman ditolak! Anggota '{$anggota->nama}' berstatus nonaktif.");
        }

        // FR-PJM-02: Validasi tunggakan denda
        if ($anggota->hasUnpaidFine()) {
            return back()->withInput()->with('error', "Peminjaman ditolak! Anggota '{$anggota->nama}' masih memiliki denda keterlambatan yang belum lunas. Harap lunasi terlebih dahulu.");
        }

        // Kumpulkan list eksemplar yang akan dipinjam
        $eksemplarList = collect();

        if ($request->has('id_eksemplar') && is_array($request->id_eksemplar) && count($request->id_eksemplar) > 0) {
            $eksemplarList = EksemplarBuku::with('buku')->whereIn('id', $request->id_eksemplar)->get();
        } elseif ($request->filled('kode_buku')) {
            $kodeScan = trim($request->kode_buku);
            $eks = EksemplarBuku::with('buku')->where('kode_eksemplar', $kodeScan)->first();
            if (!$eks) {
                $buku = Buku::where('kode_induk_buku', $kodeScan)->first();
                if ($buku) {
                    $eks = $buku->eksemplar()->where('status', 'tersedia')->first();
                }
            }
            if ($eks) {
                $eksemplarList->push($eks);
            }
        }

        if ($eksemplarList->isEmpty()) {
            return back()->withInput()->with('error', "Belum ada buku / eksemplar yang dipilih atau di-scan ke dalam keranjang peminjaman.");
        }

        // FR-PJM-02: Validasi kuota maksimal pinjam
        $pengaturan = Pengaturan::getPengaturan();
        $activeLoansCount = $anggota->peminjamanAktif()->count();
        $totalPinjamBaru = $eksemplarList->count();

        if (($activeLoansCount + $totalPinjamBaru) > $pengaturan->maks_buku_pinjam) {
            return back()->withInput()->with('error', "Peminjaman ditolak! Anggota melebihi batas maksimal peminjaman ({$pengaturan->maks_buku_pinjam} buku). Saat ini sedang meminjam {$activeLoansCount} buku, dan mencoba meminjam {$totalPinjamBaru} buku lagi.");
        }

        // FR-PJM-03: Validasi ketersediaan setiap eksemplar fisik
        foreach ($eksemplarList as $eks) {
            if (!$eks->isTersedia()) {
                return back()->withInput()->with('error', "Peminjaman ditolak! Eksemplar '{$eks->kode_eksemplar}' ({$eks->buku->judul}) sedang tidak tersedia (Status: {$eks->status}, Kondisi: {$eks->kondisi}).");
            }
        }

        $tglPinjam = Carbon::parse($request->tgl_pinjam);
        $tglJatuhTempo = (clone $tglPinjam)->addDays((int)$request->durasi_hari);

        $createdLoans = [];
        DB::transaction(function () use ($anggota, $eksemplarList, $tglPinjam, $tglJatuhTempo, $request, &$createdLoans) {
            foreach ($eksemplarList as $eks) {
                $peminjaman = Peminjaman::create([
                    'id_anggota' => $anggota->id,
                    'id_eksemplar' => $eks->id,
                    'id_user' => Auth::id(),
                    'tgl_pinjam' => $tglPinjam,
                    'tgl_jatuh_tempo' => $tglJatuhTempo,
                    'status' => 'Dipinjam',
                    'catatan' => $request->catatan,
                ]);

                // Update status eksemplar fisik
                $eks->update(['status' => 'dipinjam']);
                $createdLoans[] = $peminjaman;

                ActivityLog::log('Peminjaman Buku (Jalur A)', 'Peminjaman', "Peminjaman eksemplar '{$eks->kode_eksemplar}' ({$eks->buku->judul}) oleh {$anggota->nama} ({$anggota->no_anggota}) berhasil dicatat.");
            }
        });

        $count = count($createdLoans);
        $lastLoanId = $count > 0 ? $createdLoans[0]->id : null;

        return redirect()->route('peminjaman.index')
            ->with('success', "Transaksi peminjaman {$count} buku untuk {$anggota->nama} berhasil disimpan. Jatuh tempo: {$tglJatuhTempo->format('d/m/Y')}.")
            ->with('print_receipt_id', $lastLoanId);
    }

    public function cetakStruk($id)
    {
        $peminjaman = Peminjaman::with(['anggota', 'eksemplar.buku.kategori', 'user'])->findOrFail($id);
        
        // Ambil semua buku yang dipinjam anggota ini pada hari & waktu transaksi yang sama (1 sesi)
        $batchLoans = Peminjaman::with(['eksemplar.buku.kategori'])
            ->where('id_anggota', $peminjaman->id_anggota)
            ->where('tgl_pinjam', $peminjaman->tgl_pinjam)
            ->whereDate('created_at', $peminjaman->created_at->format('Y-m-d'))
            ->get();

        $pengaturan = Pengaturan::getPengaturan();

        return view('peminjaman.cetak_struk', compact('peminjaman', 'batchLoans', 'pengaturan'));
    }
}
