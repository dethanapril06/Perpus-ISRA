<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }} - Perpustakaan ISRA</title>
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Public Sans', sans-serif;
        }
        body {
            background-color: #ffffff;
            padding: 30px;
            color: #222;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .header h2 {
            font-size: 20px;
            font-weight: 800;
            text-transform: uppercase;
            margin-bottom: 4px;
        }
        .header p {
            font-size: 11px;
            color: #444;
        }
        .report-title {
            text-align: center;
            margin-bottom: 20px;
        }
        .report-title h3 {
            font-size: 16px;
            font-weight: 700;
            text-transform: uppercase;
            margin-bottom: 4px;
        }
        .report-title span {
            font-size: 12px;
            color: #666;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
            font-size: 11px;
        }
        th, td {
            border: 1px solid #333;
            padding: 8px 10px;
            text-align: left;
        }
        th {
            background-color: #f0f0f0;
            font-weight: 700;
            text-transform: uppercase;
        }
        .signatures {
            display: flex;
            justify-content: flex-end;
            margin-top: 30px;
            text-align: center;
        }
        .sig-box {
            width: 220px;
            font-size: 12px;
        }
        .sig-space {
            height: 60px;
        }
        .btn-print {
            background: #696cff;
            color: white;
            border: none;
            padding: 10px 24px;
            font-size: 14px;
            font-weight: 600;
            border-radius: 8px;
            cursor: pointer;
            margin-bottom: 20px;
        }
        @media print {
            body {
                padding: 15px;
            }
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body>

<div class="no-print" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <div style="font-weight: bold; font-size: 16px;">Pratinjau Cetak / Ekspor Laporan</div>
    <div style="display: flex; gap: 10px;">
        <button class="btn-print" onclick="window.print()">🖨️ Cetak / Simpan PDF</button>
        <a href="{{ route('laporan.export_excel', request()->query()) }}" class="btn-print" style="background:#28a745; text-decoration:none; display:inline-flex; align-items:center;">📥 Ekspor Excel</a>
        <button class="btn-print" style="background:#8592a3;" onclick="window.close()">Tutup</button>
    </div>
</div>

<div class="header">
    <h2>{{ $pengaturan->nama_perpustakaan }}</h2>
    <p>{{ $pengaturan->alamat_perpustakaan }} • Telp: {{ $pengaturan->no_telp_perpustakaan }}</p>
</div>

<div class="report-title">
    <h3>{{ $title }}</h3>
    @if($jenis !== 'buku')
        <span>Periode: {{ \Carbon\Carbon::parse($tglMulai)->format('d/m/Y') }} s/d {{ \Carbon\Carbon::parse($tglSelesai)->format('d/m/Y') }}</span>
    @else
        <span>Dicetak pada: {{ date('d F Y H:i') }}</span>
    @endif
</div>

@if($jenis === 'peminjaman')
<table>
    <thead>
        <tr>
            <th width="30">No</th>
            <th>No. Anggota</th>
            <th>Nama Peminjam</th>
            <th>Kode Buku</th>
            <th>Judul Buku</th>
            <th>Tgl Pinjam</th>
            <th>Jatuh Tempo</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        @forelse($data as $index => $item)
        <tr>
            <td>{{ $index + 1 }}</td>
            <td>{{ $item->anggota->no_anggota ?? '-' }}</td>
            <td>{{ $item->anggota->nama ?? '-' }}</td>
            <td>{{ $item->buku->kode_buku ?? '-' }}</td>
            <td>{{ $item->buku->judul ?? '-' }}</td>
            <td>{{ $item->tgl_pinjam->format('d/m/Y') }}</td>
            <td>{{ $item->tgl_jatuh_tempo->format('d/m/Y') }}</td>
            <td>{{ $item->status }}</td>
        </tr>
        @empty
        <tr><td colspan="8" style="text-align: center;">Tidak ada data peminjaman.</td></tr>
        @endforelse
    </tbody>
</table>

@elseif($jenis === 'pengembalian')
<table>
    <thead>
        <tr>
            <th width="30">No</th>
            <th>Nama Peminjam</th>
            <th>Kode & Judul Buku</th>
            <th>Tgl Pinjam</th>
            <th>Tgl Kembali</th>
            <th>Kondisi Buku</th>
            <th>Denda</th>
        </tr>
    </thead>
    <tbody>
        @forelse($data as $index => $item)
        <tr>
            <td>{{ $index + 1 }}</td>
            <td>{{ $item->anggota->nama ?? '-' }}</td>
            <td>{{ $item->buku->kode_buku ?? '-' }} - {{ $item->buku->judul ?? '-' }}</td>
            <td>{{ $item->tgl_pinjam->format('d/m/Y') }}</td>
            <td>{{ $item->tgl_kembali ? $item->tgl_kembali->format('d/m/Y') : '-' }}</td>
            <td style="text-transform: capitalize;">{{ $item->kondisi_kembali }}</td>
            <td>Rp {{ number_format($item->denda->nominal ?? 0, 0, ',', '.') }}</td>
        </tr>
        @empty
        <tr><td colspan="7" style="text-align: center;">Tidak ada data pengembalian.</td></tr>
        @endforelse
    </tbody>
</table>

@elseif($jenis === 'denda')
<table>
    <thead>
        <tr>
            <th width="30">No</th>
            <th>Peminjam</th>
            <th>Buku</th>
            <th>Tgl Tercatat</th>
            <th>Nominal Denda</th>
            <th>Status Pembayaran</th>
            <th>Tgl Lunas</th>
        </tr>
    </thead>
    <tbody>
        @php $totalNominal = 0; @endphp
        @forelse($data as $index => $item)
        @php $totalNominal += $item->nominal; @endphp
        <tr>
            <td>{{ $index + 1 }}</td>
            <td>{{ $item->peminjaman->anggota->nama ?? '-' }}</td>
            <td>{{ $item->peminjaman->buku->judul ?? '-' }}</td>
            <td>{{ $item->created_at->format('d/m/Y') }}</td>
            <td>Rp {{ number_format($item->nominal, 0, ',', '.') }}</td>
            <td>{{ $item->status_bayar }}</td>
            <td>{{ $item->tgl_bayar ? $item->tgl_bayar->format('d/m/Y') : '-' }}</td>
        </tr>
        @empty
        <tr><td colspan="7" style="text-align: center;">Tidak ada data denda.</td></tr>
        @endforelse
        @if($data->count() > 0)
        <tr>
            <th colspan="4" style="text-align: right;">TOTAL:</th>
            <th colspan="3">Rp {{ number_format($totalNominal, 0, ',', '.') }}</th>
        </tr>
        @endif
    </tbody>
</table>

@elseif($jenis === 'kunjungan')
<table>
    <thead>
        <tr>
            <th width="30">No</th>
            <th>No. Anggota</th>
            <th>Nama Anggota</th>
            <th>Tanggal</th>
            <th>Waktu Masuk</th>
            <th>Waktu Keluar</th>
            <th>Jml Buku Dibaca</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        @forelse($data as $index => $item)
        <tr>
            <td>{{ $index + 1 }}</td>
            <td>{{ $item->anggota->no_anggota ?? '-' }}</td>
            <td>{{ $item->anggota->nama ?? '-' }}</td>
            <td>{{ $item->tanggal->format('d/m/Y') }}</td>
            <td>{{ $item->waktu_masuk }}</td>
            <td>{{ $item->waktu_keluar ?: '-' }}</td>
            <td>{{ $item->detailBuku->count() }}</td>
            <td>{{ $item->status }}</td>
        </tr>
        @empty
        <tr><td colspan="8" style="text-align: center;">Tidak ada data kunjungan.</td></tr>
        @endforelse
    </tbody>
</table>

@elseif($jenis === 'buku')
<table>
    <thead>
        <tr>
            <th width="30">No</th>
            <th>Kode Eksemplar</th>
            <th>Judul Buku</th>
            <th>Pengarang</th>
            <th>Kategori</th>
            <th>Lokasi Rak/Laci</th>
            <th>Kondisi</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        @forelse($data as $index => $item)
        <tr>
            <td>{{ $index + 1 }}</td>
            <td>{{ $item->kode_eksemplar }}</td>
            <td>{{ $item->buku->judul ?? '-' }}</td>
            <td>{{ $item->buku->pengarang ?? '-' }}</td>
            <td>{{ $item->buku->kategori->nama_kategori ?? 'Umum' }}</td>
            <td>{{ $item->laci ? ($item->laci->lemari->kode_lemari . ' - ' . $item->laci->kode_laci) : '-' }}</td>
            <td style="text-transform: capitalize;">{{ $item->kondisi }}</td>
            <td style="text-transform: capitalize;">{{ $item->status }}</td>
        </tr>
        @empty
        <tr><td colspan="8" style="text-align: center;">Tidak ada data eksemplar buku.</td></tr>
        @endforelse
    </tbody>
</table>
@endif

<div class="signatures">
    <div class="sig-box">
        <div>Kupang, {{ date('d F Y') }}</div>
        <div>Mengetahui,</div>
        <div>Petugas Perpustakaan</div>
        <div class="sig-space"></div>
        <strong>{{ auth()->user()->name ?? 'Admin Perpustakaan' }}</strong>
    </div>
</div>

</body>
</html>
