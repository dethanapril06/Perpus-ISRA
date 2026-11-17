<?php

namespace App\Http\Controllers;

use App\Models\Kunjungan;
use App\Models\DetailKunjunganBuku;
use App\Models\Anggota;
use App\Models\Buku;
use App\Models\EksemplarBuku;
use App\Models\Peminjaman;
use App\Models\Pengaturan;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class KunjunganController extends Controller
{
    public function index(Request $request)
    {
        $query = Kunjungan::with(['anggota', 'detailBuku.eksemplar.buku']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('anggota', function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('no_anggota', 'like', "%{$search}%")
                  ->orWhere('rfid_uid', 'like', "%{$search}%")
                  ->orWhere('identitas', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('tanggal')) {
            $query->whereDate('tanggal', $request->tanggal);
        }

        $kunjungan = $query->latest('id')->paginate(10)->withQueryString();

        // Kunjungan yang sedang berlangsung hari ini
        $kunjunganAktif = Kunjungan::with(['anggota', 'detailBuku.eksemplar.buku'])
            ->where('status', 'Berlangsung')
            ->whereDate('tanggal', Carbon::today())
            ->latest('waktu_masuk')
            ->get();

        return view('kunjungan.index', compact('kunjungan', 'kunjunganAktif'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'kode_anggota' => 'required|string',
        ]);

        $code = trim($request->kode_anggota);
        $anggota = Anggota::findByCodeOrRfid($code)->first();

        if (!$anggota) {
            return back()->with('error', "Anggota dengan kartu RFID / kode '{$code}' tidak ditemukan di sistem.");
        }

        if ($anggota->status !== 'aktif') {
            return back()->with('error', "Anggota '{$anggota->nama}' berstatus nonaktif. Tidak dapat melakukan kunjungan.");
        }

        // Check if member already has active visit today
        $existingVisit = Kunjungan::where('id_anggota', $anggota->id)
            ->where('status', 'Berlangsung')
            ->whereDate('tanggal', Carbon::today())
            ->first();

        if ($existingVisit) {
            return redirect()->route('kunjungan.show', $existingVisit->id)->with('info', "Anggota '{$anggota->nama}' sudah memiliki kunjungan yang sedang berlangsung.");
        }

        $kunjungan = Kunjungan::create([
            'id_anggota' => $anggota->id,
            'tanggal' => Carbon::today(),
            'waktu_masuk' => Carbon::now()->format('H:i:s'),
            'status' => 'Berlangsung',
        ]);

        ActivityLog::log('Check-in Kunjungan', 'Kunjungan', "Anggota {$anggota->nama} ({$anggota->no_anggota}) check-in kunjungan perpustakaan.");

        return redirect()->route('kunjungan.show', $kunjungan->id)->with('success', "Check-in berhasil! Kunjungan {$anggota->nama} dimulai.");
    }

    public function show(Kunjungan $kunjungan)
    {
        $kunjungan->load([
            'anggota',
            'detailBuku.eksemplar.buku.kategori',
            'detailBuku.eksemplar.laci.lemari',
            'peminjaman.eksemplar.buku'
        ]);

        return view('kunjungan.show', compact('kunjungan'));
    }

    public function scanBuku(Request $request, Kunjungan $kunjungan)
    {
        if ($kunjungan->status !== 'Berlangsung') {
            return back()->with('error', 'Kunjungan ini sudah selesai. Tidak dapat menambahkan buku bacaan.');
        }

        $request->validate([
            'kode_buku' => 'required|string',
        ]);

        $kodeScan = trim($request->kode_buku);

        // Cari eksemplar berdasarkan kode_eksemplar
        $eksemplar = EksemplarBuku::with('buku')->where('kode_eksemplar', $kodeScan)->first();

        // Fallback jika scan kode induk
        if (!$eksemplar) {
            $buku = Buku::where('kode_induk_buku', $kodeScan)->first();
            if ($buku) {
                $eksemplar = $buku->eksemplar()->where('status', 'tersedia')->first();
            }
        }

        if (!$eksemplar) {
            return back()->with('error', "Eksemplar buku dengan kode barcode '{$kodeScan}' tidak ditemukan.");
        }

        // Check if copy already scanned in this visit
        $exists = DetailKunjunganBuku::where('id_kunjungan', $kunjungan->id)
            ->where('id_eksemplar', $eksemplar->id)
            ->exists();

        if ($exists) {
            return back()->with('warning', "Buku '{$eksemplar->buku->judul}' ({$eksemplar->kode_eksemplar}) sudah ada di daftar bacaan kunjungan ini.");
        }

        DetailKunjunganBuku::create([
            'id_kunjungan' => $kunjungan->id,
            'id_eksemplar' => $eksemplar->id,
            'waktu_scan' => Carbon::now()->format('H:i:s'),
            'status' => 'Dibaca di Tempat',
        ]);

        ActivityLog::log('Scan Baca Buku', 'Kunjungan', "Eksemplar '{$eksemplar->kode_eksemplar}' ({$eksemplar->buku->judul}) dicatat dibaca pada kunjungan #{$kunjungan->id} oleh {$kunjungan->anggota->nama}");

        return back()->with('success', "Buku '{$eksemplar->buku->judul}' ({$eksemplar->kode_eksemplar}) berhasil ditambahkan ke daftar bacaan.");
    }

    public function hapusBuku(DetailKunjunganBuku $detail)
    {
        $judul = $detail->eksemplar->buku->judul ?? 'Buku';
        $detail->delete();

        return back()->with('success', "Buku '{$judul}' dihapus dari daftar bacaan kunjungan.");
    }

    public function selesai(Kunjungan $kunjungan)
    {
        $kunjungan->update([
            'waktu_keluar' => Carbon::now()->format('H:i:s'),
            'status' => 'Selesai',
        ]);

        ActivityLog::log('Checkout Kunjungan', 'Kunjungan', "Kunjungan #{$kunjungan->id} anggota {$kunjungan->anggota->nama} telah selesai.");

        return redirect()->route('kunjungan.index')->with('success', "Kunjungan anggota {$kunjungan->anggota->nama} berhasil diselesaikan.");
    }

    // Jalur B: Peminjaman dari data kunjungan yang sedang berlangsung
    public function konversiPinjam(Request $request, DetailKunjunganBuku $detail)
    {
        $kunjungan = $detail->kunjungan;
        $anggota = $kunjungan->anggota;
        $eksemplar = $detail->eksemplar;
        $buku = $eksemplar->buku;

        // 1. Validasi status anggota
        if ($anggota->status !== 'aktif') {
            return back()->with('error', "Anggota '{$anggota->nama}' berstatus nonaktif.");
        }

        // 2. Validasi denda belum lunas
        if ($anggota->hasUnpaidFine()) {
            return back()->with('error', "Anggota '{$anggota->nama}' masih memiliki tanggungan denda yang belum lunas. Harap lunasi terlebih dahulu.");
        }

        // 3. Validasi batas maksimal pinjam
        $pengaturan = Pengaturan::getPengaturan();
        $activeLoansCount = $anggota->peminjamanAktif()->count();

        if ($activeLoansCount >= $pengaturan->maks_buku_pinjam) {
            return back()->with('error', "Anggota telah mencapai batas maksimal peminjaman ({$pengaturan->maks_buku_pinjam} buku). Peminjaman aktif saat ini: {$activeLoansCount} buku.");
        }

        // 4. Validasi ketersediaan eksemplar
        if (!$eksemplar->isTersedia()) {
            return back()->with('error', "Eksemplar buku '{$eksemplar->kode_eksemplar}' ({$buku->judul}) saat ini berstatus {$eksemplar->status} (Kondisi: {$eksemplar->kondisi}) dan tidak tersedia untuk dipinjam.");
        }

        DB::transaction(function () use ($kunjungan, $anggota, $buku, $eksemplar, $detail, $pengaturan) {
            $tglPinjam = Carbon::today();
            $tglJatuhTempo = Carbon::today()->addDays($pengaturan->durasi_pinjam_default);

            // Create Peminjaman
            $peminjaman = Peminjaman::create([
                'id_kunjungan' => $kunjungan->id,
                'id_anggota' => $anggota->id,
                'id_eksemplar' => $eksemplar->id,
                'id_user' => Auth::id(),
                'tgl_pinjam' => $tglPinjam,
                'tgl_jatuh_tempo' => $tglJatuhTempo,
                'status' => 'Dipinjam',
            ]);

            // Update status eksemplar fisik
            $eksemplar->update(['status' => 'dipinjam']);

            // Update status detail kunjungan
            $detail->update(['status' => 'Dijadikan Peminjaman']);

            ActivityLog::log('Peminjaman Buku (Jalur B)', 'Peminjaman', "Eksemplar '{$eksemplar->kode_eksemplar}' ({$buku->judul}) dipinjam oleh {$anggota->nama} melalui kunjungan aktif #{$kunjungan->id}");
        });

        return back()->with('success', "Buku '{$buku->judul}' (Eks. {$eksemplar->kode_eksemplar}) berhasil dijadikan transaksi peminjaman. Jatuh tempo: " . Carbon::today()->addDays($pengaturan->durasi_pinjam_default)->format('d/m/Y'));
    }
}
