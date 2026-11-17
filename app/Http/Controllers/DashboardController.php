<?php

namespace App\Http\Controllers;

use App\Models\Anggota;
use App\Models\Buku;
use App\Models\EksemplarBuku;
use App\Models\Kunjungan;
use App\Models\Peminjaman;
use App\Models\Denda;
use App\Models\Pengaturan;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today();

        // 1. Key Metrics
        $totalAnggota = Anggota::where('status', 'aktif')->count();
        $totalBukuJudul = Buku::count();
        $totalBukuStok = EksemplarBuku::where('status', 'tersedia')->count();
        $totalEksemplar = EksemplarBuku::count();

        $peminjamanAktif = Peminjaman::where('status', 'Dipinjam')->count();
        $peminjamanTerlambat = Peminjaman::where('status', 'Dipinjam')
            ->whereDate('tgl_jatuh_tempo', '<', $today)
            ->count();

        $kunjunganHariIni = Kunjungan::whereDate('tanggal', $today)->count();
        $kunjunganAktifHariIni = Kunjungan::whereDate('tanggal', $today)->where('status', 'Berlangsung')->count();

        $totalDendaBelumLunas = Denda::where('status_bayar', 'Belum Lunas')->sum('nominal');

        // 2. Chart Data: Past 7 Days Activity
        $chartLabels = [];
        $chartKunjungan = [];
        $chartPeminjaman = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $chartLabels[] = $date->translatedFormat('D, d M');

            $chartKunjungan[] = Kunjungan::whereDate('tanggal', $date)->count();
            $chartPeminjaman[] = Peminjaman::whereDate('tgl_pinjam', $date)->count();
        }

        // 3. Quick Lists
        $kunjunganTerbaru = Kunjungan::with('anggota')
            ->whereDate('tanggal', $today)
            ->latest('waktu_masuk')
            ->limit(5)
            ->get();

        $pinjamanMendekatiTempo = Peminjaman::with(['anggota', 'eksemplar.buku'])
            ->where('status', 'Dipinjam')
            ->orderBy('tgl_jatuh_tempo', 'asc')
            ->limit(5)
            ->get();

        return view('dashboard.index', compact(
            'totalAnggota',
            'totalBukuJudul',
            'totalBukuStok',
            'totalEksemplar',
            'peminjamanAktif',
            'peminjamanTerlambat',
            'kunjunganHariIni',
            'kunjunganAktifHariIni',
            'totalDendaBelumLunas',
            'chartLabels',
            'chartKunjungan',
            'chartPeminjaman',
            'kunjunganTerbaru',
            'pinjamanMendekatiTempo'
        ));
    }
}
