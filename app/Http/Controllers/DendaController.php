<?php

namespace App\Http\Controllers;

use App\Models\Denda;
use App\Models\Pengaturan;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DendaController extends Controller
{
    public function index(Request $request)
    {
        $query = Denda::with(['peminjaman.anggota', 'peminjaman.buku']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('peminjaman.anggota', function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('no_anggota', 'like', "%{$search}%");
            })->orWhereHas('peminjaman.buku', function ($q) use ($search) {
                $q->where('judul', 'like', "%{$search}%")
                  ->orWhere('kode_buku', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status_bayar')) {
            $query->where('status_bayar', $request->status_bayar);
        }

        if ($request->filled('tgl_mulai')) {
            $query->whereDate('created_at', '>=', $request->tgl_mulai);
        }

        if ($request->filled('tgl_selesai')) {
            $query->whereDate('created_at', '<=', $request->tgl_selesai);
        }

        $denda = $query->latest('id')->paginate(10)->withQueryString();

        $totalBelumLunas = Denda::where('status_bayar', 'Belum Lunas')->sum('nominal');
        $totalLunas = Denda::where('status_bayar', 'Lunas')->sum('nominal');

        return view('denda.index', compact('denda', 'totalBelumLunas', 'totalLunas'));
    }

    public function bayar(Request $request, Denda $denda)
    {
        if ($denda->status_bayar === 'Lunas') {
            return back()->with('info', 'Denda ini sudah berstatus lunas.');
        }

        $denda->update([
            'status_bayar' => 'Lunas',
            'tgl_bayar' => Carbon::now(),
        ]);

        $anggotaNama = $denda->peminjaman->anggota->nama ?? 'Anggota';
        ActivityLog::log('Pelunasan Denda', 'Denda', "Pelunasan denda sebesar Rp " . number_format($denda->nominal, 0, ',', '.') . " untuk {$anggotaNama}");

        return redirect()->route('denda.index')->with('success', "Pelunasan denda sebesar Rp " . number_format($denda->nominal, 0, ',', '.') . " untuk {$anggotaNama} berhasil disimpan.");
    }

    public function cetakKuitansi($id)
    {
        $denda = Denda::with(['peminjaman.anggota', 'peminjaman.buku', 'peminjaman.user'])->findOrFail($id);
        $pengaturan = Pengaturan::getPengaturan();

        return view('denda.cetak_kuitansi', compact('denda', 'pengaturan'));
    }
}
