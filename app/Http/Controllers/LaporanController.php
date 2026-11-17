<?php

namespace App\Http\Controllers;

use App\Models\Peminjaman;
use App\Models\Kunjungan;
use App\Models\Denda;
use App\Models\Buku;
use App\Models\EksemplarBuku;
use App\Models\Kategori;
use App\Models\Pengaturan;
use Illuminate\Http\Request;
use Carbon\Carbon;

class LaporanController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->get('tab', 'peminjaman');
        $tglMulai = $request->get('tgl_mulai', Carbon::today()->startOfMonth()->format('Y-m-d'));
        $tglSelesai = $request->get('tgl_selesai', Carbon::today()->format('Y-m-d'));

        $kategoriList = Kategori::orderBy('nama_kategori', 'asc')->get();
        $pengaturan = Pengaturan::getPengaturan();

        // 1. Data Peminjaman
        $peminjamanQuery = Peminjaman::with(['anggota', 'eksemplar.buku.kategori', 'user', 'denda'])
            ->whereDate('tgl_pinjam', '>=', $tglMulai)
            ->whereDate('tgl_pinjam', '<=', $tglSelesai);

        if ($request->filled('status_pinjam')) {
            $peminjamanQuery->where('status', $request->status_pinjam);
        }
        $dataPeminjaman = $peminjamanQuery->latest('tgl_pinjam')->get();

        // 2. Data Pengembalian
        $pengembalianQuery = Peminjaman::with(['anggota', 'eksemplar.buku', 'denda'])
            ->where('status', 'Selesai')
            ->whereNotNull('tgl_kembali')
            ->whereDate('tgl_kembali', '>=', $tglMulai)
            ->whereDate('tgl_kembali', '<=', $tglSelesai);

        if ($request->filled('kondisi_kembali')) {
            $pengembalianQuery->where('kondisi_kembali', $request->kondisi_kembali);
        }
        $dataPengembalian = $pengembalianQuery->latest('tgl_kembali')->get();

        // 3. Data Denda
        $dendaQuery = Denda::with(['peminjaman.anggota', 'peminjaman.eksemplar.buku'])
            ->whereDate('created_at', '>=', $tglMulai)
            ->whereDate('created_at', '<=', $tglSelesai);

        if ($request->filled('status_bayar')) {
            $dendaQuery->where('status_bayar', $request->status_bayar);
        }
        $dataDenda = $dendaQuery->latest('id')->get();

        // 4. Data Kunjungan
        $kunjunganQuery = Kunjungan::with(['anggota', 'detailBuku.eksemplar.buku'])
            ->whereDate('tanggal', '>=', $tglMulai)
            ->whereDate('tanggal', '<=', $tglSelesai);

        if ($request->filled('status_kunjungan')) {
            $kunjunganQuery->where('status', $request->status_kunjungan);
        }
        $dataKunjungan = $kunjunganQuery->latest('tanggal')->get();

        // 5. Data Stok & Eksemplar Buku
        $eksemplarQuery = EksemplarBuku::with(['buku.kategori', 'laci.lemari']);
        if ($request->filled('id_kategori')) {
            $eksemplarQuery->whereHas('buku', function($q) use ($request) {
                $q->where('id_kategori', $request->id_kategori);
            });
        }
        if ($request->filled('status_eksemplar')) {
            $eksemplarQuery->where('status', $request->status_eksemplar);
        }
        $dataEksemplar = $eksemplarQuery->orderBy('kode_eksemplar', 'asc')->get();

        return view('laporan.index', compact(
            'tab',
            'tglMulai',
            'tglSelesai',
            'kategoriList',
            'pengaturan',
            'dataPeminjaman',
            'dataPengembalian',
            'dataDenda',
            'dataKunjungan',
            'dataEksemplar'
        ));
    }

    public function cetak(Request $request)
    {
        $jenis = $request->get('jenis', 'peminjaman');
        $tglMulai = $request->get('tgl_mulai', Carbon::today()->startOfMonth()->format('Y-m-d'));
        $tglSelesai = $request->get('tgl_selesai', Carbon::today()->format('Y-m-d'));
        $pengaturan = Pengaturan::getPengaturan();

        $data = null;
        $title = '';

        if ($jenis === 'peminjaman') {
            $title = 'Laporan Transaksi Peminjaman Buku';
            $q = Peminjaman::with(['anggota', 'eksemplar.buku.kategori', 'user', 'denda'])
                ->whereDate('tgl_pinjam', '>=', $tglMulai)
                ->whereDate('tgl_pinjam', '<=', $tglSelesai);
            if ($request->filled('status')) $q->where('status', $request->status);
            $data = $q->latest('tgl_pinjam')->get();
        } elseif ($jenis === 'pengembalian') {
            $title = 'Laporan Transaksi Pengembalian Buku';
            $q = Peminjaman::with(['anggota', 'eksemplar.buku', 'denda'])
                ->where('status', 'Selesai')
                ->whereNotNull('tgl_kembali')
                ->whereDate('tgl_kembali', '>=', $tglMulai)
                ->whereDate('tgl_kembali', '<=', $tglSelesai);
            if ($request->filled('kondisi')) $q->where('kondisi_kembali', $request->kondisi);
            $data = $q->latest('tgl_kembali')->get();
        } elseif ($jenis === 'denda') {
            $title = 'Laporan Denda & Keterlambatan';
            $q = Denda::with(['peminjaman.anggota', 'peminjaman.eksemplar.buku'])
                ->whereDate('created_at', '>=', $tglMulai)
                ->whereDate('created_at', '<=', $tglSelesai);
            if ($request->filled('status_bayar')) $q->where('status_bayar', $request->status_bayar);
            $data = $q->latest('id')->get();
        } elseif ($jenis === 'kunjungan') {
            $title = 'Laporan Kunjungan Anggota Perpustakaan';
            $q = Kunjungan::with(['anggota', 'detailBuku.eksemplar.buku'])
                ->whereDate('tanggal', '>=', $tglMulai)
                ->whereDate('tanggal', '<=', $tglSelesai);
            if ($request->filled('status')) $q->where('status', $request->status);
            $data = $q->latest('tanggal')->get();
        } elseif ($jenis === 'buku') {
            $title = 'Laporan Inventaris Eksemplar & Stok Buku';
            $q = EksemplarBuku::with(['buku.kategori', 'laci.lemari']);
            if ($request->filled('id_kategori')) {
                $q->whereHas('buku', function($qb) use ($request) {
                    $qb->where('id_kategori', $request->id_kategori);
                });
            }
            if ($request->filled('status')) $q->where('status', $request->status);
            $data = $q->orderBy('kode_eksemplar', 'asc')->get();
        }

        return view('laporan.cetak', compact('jenis', 'title', 'tglMulai', 'tglSelesai', 'pengaturan', 'data'));
    }
}
