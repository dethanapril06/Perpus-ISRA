<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Peminjaman #{{ $peminjaman->id }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@400;600;700;800&family=Courier+Prime:wght@400;700&display=swap" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            background-color: #f4f5fa;
            font-family: 'Public Sans', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }
        .receipt-container {
            width: 320px;
            background: #ffffff;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            font-size: 11px;
            color: #333;
        }
        .header {
            text-align: center;
            border-bottom: 1px dashed #666;
            padding-bottom: 10px;
            margin-bottom: 12px;
        }
        .header h3 {
            font-size: 14px;
            font-weight: 800;
            color: #111;
            text-transform: uppercase;
        }
        .header p {
            font-size: 9px;
            color: #666;
            line-height: 1.3;
        }
        .section-title {
            font-weight: 700;
            text-transform: uppercase;
            font-size: 10px;
            margin-bottom: 6px;
            color: #444;
        }
        .row-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 4px;
        }
        .row-item span:first-child {
            color: #666;
        }
        .row-item span:last-child {
            font-weight: 600;
            text-align: right;
        }
        .divider {
            border-top: 1px dashed #aaa;
            margin: 10px 0;
        }
        .book-box {
            background: #f8f9fa;
            border: 1px solid #eee;
            border-radius: 6px;
            padding: 8px;
            margin: 8px 0;
        }
        .due-date-box {
            background: #fff3cd;
            border: 1px solid #ffeeba;
            color: #856404;
            padding: 8px;
            border-radius: 6px;
            text-align: center;
            margin: 10px 0;
        }
        .due-date-box .label {
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
        }
        .due-date-box .date {
            font-size: 14px;
            font-weight: 800;
        }
        .footer {
            text-align: center;
            font-size: 8.5px;
            color: #777;
            margin-top: 12px;
            line-height: 1.3;
        }
        .barcode-section {
            text-align: center;
            margin: 10px 0;
        }
        .barcode-section svg {
            max-width: 180px;
            height: 30px;
        }
        .btn-print {
            background: #696cff;
            color: white;
            border: none;
            padding: 8px 20px;
            font-size: 13px;
            font-weight: 600;
            border-radius: 6px;
            cursor: pointer;
        }

        @media print {
            body {
                background: none;
                padding: 0;
                min-height: auto;
            }
            .no-print {
                display: none !important;
            }
            .receipt-container {
                box-shadow: none;
                border: none;
                width: 100%;
                max-width: 280px;
                padding: 5px;
            }
        }
    </style>
</head>
<body>

<div style="display: flex; flex-direction: column; align-items: center; gap: 15px;">
    <div class="no-print" style="display: flex; gap: 10px;">
        <button class="btn-print" onclick="window.print()">🖨️ Cetak Struk</button>
        <button class="btn-print" style="background: #8592a3;" onclick="window.close()">Tutup</button>
    </div>

    <div class="receipt-container">
        <div class="header">
            <h3>{{ $pengaturan->nama_perpustakaan }}</h3>
            <p>{{ $pengaturan->alamat_perpustakaan }}<br>Telp: {{ $pengaturan->no_telp_perpustakaan }}</p>
        </div>

        <div class="section-title">BUKTI PEMINJAMAN BUKU</div>
        <div class="row-item">
            <span>No. Transaksi:</span>
            <span>#TRX-{{ str_pad($peminjaman->id, 5, '0', STR_PAD_LEFT) }}</span>
        </div>
        <div class="row-item">
            <span>Tgl Pinjam:</span>
            <span>{{ $peminjaman->tgl_pinjam->format('d/m/Y') }}</span>
        </div>
        <div class="row-item">
            <span>Petugas:</span>
            <span>{{ $peminjaman->user->name ?? 'Petugas' }}</span>
        </div>

        <div class="divider"></div>

        <div class="section-title">DATA PEMINJAM</div>
        <div class="row-item">
            <span>No. Anggota:</span>
            <span>{{ $peminjaman->anggota->no_anggota ?? '-' }}</span>
        </div>
        <div class="row-item">
            <span>Nama:</span>
            <span>{{ $peminjaman->anggota->nama ?? '-' }}</span>
        </div>
        <div class="row-item">
            <span>Kelas/Jabatan:</span>
            <span>{{ $peminjaman->anggota->kelas_jabatan ?? '-' }}</span>
        </div>

        <div class="divider"></div>

        <div class="section-title">BUKU YANG DIPINJAM ({{ isset($batchLoans) ? $batchLoans->count() : 1 }} BUKU)</div>
        @if(isset($batchLoans) && $batchLoans->count() > 0)
            @foreach($batchLoans as $bLoan)
                <div class="book-box">
                    <div style="font-weight: 700; font-size: 11px; margin-bottom: 2px;">{{ $bLoan->eksemplar->buku->judul ?? '-' }}</div>
                    <div style="font-size: 9px; color: #555;">Kode Eksemplar: <strong>{{ $bLoan->eksemplar->kode_eksemplar ?? '-' }}</strong></div>
                    <div style="font-size: 9px; color: #555;">Kategori: {{ $bLoan->eksemplar->buku->kategori->nama_kategori ?? '-' }}</div>
                </div>
            @endforeach
        @else
            <div class="book-box">
                <div style="font-weight: 700; font-size: 11px; margin-bottom: 2px;">{{ $peminjaman->eksemplar->buku->judul ?? '-' }}</div>
                <div style="font-size: 9px; color: #555;">Kode Eksemplar: <strong>{{ $peminjaman->eksemplar->kode_eksemplar ?? '-' }}</strong></div>
                <div style="font-size: 9px; color: #555;">Kategori: {{ $peminjaman->eksemplar->buku->kategori->nama_kategori ?? '-' }}</div>
            </div>
        @endif

        <div class="due-date-box">
            <div class="label">BATAS PENGEMBALIAN (JATUH TEMPO):</div>
            <div class="date">{{ $peminjaman->tgl_jatuh_tempo->format('d F Y') }}</div>
        </div>

        <div class="barcode-section">
            {!! \App\Helpers\BarcodeHelper::generateBarcodeSVG('TRX' . $peminjaman->id, 1.5, 28) !!}
            <div style="font-family: monospace; font-size: 9px; margin-top: 2px;">TRX-{{ str_pad($peminjaman->id, 5, '0', STR_PAD_LEFT) }}</div>
        </div>

        <div class="footer">
            Harap kembalikan buku tepat waktu.<br>
            Denda keterlambatan Rp {{ number_format($pengaturan->nominal_per_hari, 0, ',', '.') }} / hari.<br>
            <em>Terima kasih telah membaca di perpustakaan kami.</em>
        </div>
    </div>
</div>

</body>
</html>
