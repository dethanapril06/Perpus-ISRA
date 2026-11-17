<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kuitansi Pembayaran Denda #{{ $denda->id }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Public Sans', sans-serif;
        }
        body {
            background-color: #f4f5fa;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }
        .kuitansi-container {
            width: 480px;
            background: #ffffff;
            border: 2px solid #28a745;
            border-radius: 10px;
            padding: 24px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            font-size: 12px;
            color: #333;
            position: relative;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #eee;
            padding-bottom: 12px;
            margin-bottom: 16px;
        }
        .header h3 {
            font-size: 16px;
            font-weight: 800;
            color: #28a745;
            text-transform: uppercase;
        }
        .header p {
            font-size: 10px;
            color: #666;
        }
        .title {
            text-align: center;
            font-size: 13px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 15px;
            color: #111;
        }
        .row-data {
            display: flex;
            margin-bottom: 8px;
        }
        .row-data .label {
            width: 140px;
            color: #666;
        }
        .row-data .value {
            flex: 1;
            font-weight: 600;
            color: #222;
        }
        .amount-box {
            background: #e8f5e9;
            border: 1px solid #c8e6c9;
            color: #2e7d32;
            padding: 12px;
            border-radius: 8px;
            text-align: center;
            margin: 16px 0;
        }
        .amount-box .nominal {
            font-size: 20px;
            font-weight: 800;
        }
        .stamp-lunas {
            position: absolute;
            right: 30px;
            bottom: 60px;
            border: 3px solid #28a745;
            color: #28a745;
            font-size: 22px;
            font-weight: 900;
            text-transform: uppercase;
            padding: 6px 16px;
            border-radius: 8px;
            transform: rotate(-12deg);
            opacity: 0.85;
            letter-spacing: 2px;
        }
        .signatures {
            display: flex;
            justify-content: space-between;
            margin-top: 30px;
            text-align: center;
        }
        .sig-box {
            width: 160px;
        }
        .sig-space {
            height: 45px;
        }
        .btn-print {
            background: #28a745;
            color: white;
            border: none;
            padding: 10px 24px;
            font-size: 14px;
            font-weight: 600;
            border-radius: 8px;
            cursor: pointer;
        }

        @media print {
            body {
                background: none;
                padding: 0;
            }
            .no-print {
                display: none !important;
            }
            .kuitansi-container {
                box-shadow: none;
                border: 1.5px solid #000;
            }
        }
    </style>
</head>
<body>

<div style="display: flex; flex-direction: column; align-items: center; gap: 15px;">
    <div class="no-print" style="display: flex; gap: 10px;">
        <button class="btn-print" onclick="window.print()">🖨️ Cetak Kuitansi</button>
        <button class="btn-print" style="background:#8592a3;" onclick="window.close()">Tutup</button>
    </div>

    <div class="kuitansi-container">
        <div class="header">
            <h3>{{ $pengaturan->nama_perpustakaan }}</h3>
            <p>{{ $pengaturan->alamat_perpustakaan }} • Telp: {{ $pengaturan->no_telp_perpustakaan }}</p>
        </div>

        <div class="title">KUITANSI PELUNASAN DENDA</div>

        <div class="row-data">
            <div class="label">No. Kuitansi:</div>
            <div class="value">#KUI-{{ str_pad($denda->id, 5, '0', STR_PAD_LEFT) }}</div>
        </div>
        <div class="row-data">
            <div class="label">Telah Diterima Dari:</div>
            <div class="value">{{ $denda->peminjaman->anggota->nama ?? '-' }} ({{ $denda->peminjaman->anggota->no_anggota ?? '-' }})</div>
        </div>
        <div class="row-data">
            <div class="label">Buku Terkait:</div>
            <div class="value">{{ $denda->peminjaman->buku->judul ?? '-' }}</div>
        </div>
        <div class="row-data">
            <div class="label">Untuk Pembayaran:</div>
            <div class="value">Denda Keterlambatan Pengembalian Buku</div>
        </div>
        <div class="row-data">
            <div class="label">Tgl. Pembayaran:</div>
            <div class="value">{{ $denda->tgl_bayar ? $denda->tgl_bayar->format('d F Y H:i') : date('d F Y') }}</div>
        </div>

        <div class="amount-box">
            <div style="font-size: 10px; text-transform: uppercase;">JUMLAH PEMBAYARAN:</div>
            <div class="nominal">Rp {{ number_format($denda->nominal, 0, ',', '.') }}</div>
        </div>

        <div class="stamp-lunas">LUNAS</div>

        <div class="signatures">
            <div class="sig-box">
                <div>Pembayar,</div>
                <div class="sig-space"></div>
                <strong>{{ $denda->peminjaman->anggota->nama ?? 'Anggota' }}</strong>
            </div>
            <div class="sig-box">
                <div>Petugas Perpustakaan,</div>
                <div class="sig-space"></div>
                <strong>{{ auth()->user()->name ?? 'Admin' }}</strong>
            </div>
        </div>
    </div>
</div>

</body>
</html>
