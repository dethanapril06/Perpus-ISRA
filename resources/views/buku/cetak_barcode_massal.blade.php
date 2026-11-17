<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Massal Barcode Eksemplar - Perpustakaan ISRA</title>
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
            padding: 20px;
        }
        .header-action {
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
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
        }
        .grid-labels {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
        }
        .barcode-label {
            border: 1px solid #999;
            border-radius: 6px;
            padding: 8px 10px;
            background: #ffffff;
            text-align: center;
            page-break-inside: avoid;
        }
        .label-header {
            font-size: 9px;
            font-weight: 800;
            text-transform: uppercase;
            color: #333;
            border-bottom: 1px dashed #aaa;
            padding-bottom: 3px;
            margin-bottom: 4px;
        }
        .book-title {
            font-size: 10px;
            font-weight: 700;
            color: #111;
            line-height: 1.2;
            margin-bottom: 3px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .book-location {
            font-size: 8.5px;
            color: #555;
            margin-bottom: 4px;
        }
        .barcode-area {
            display: flex;
            justify-content: center;
            margin-bottom: 2px;
        }
        .barcode-area svg {
            height: 32px;
            max-width: 100%;
        }
        .barcode-number {
            font-family: monospace;
            font-size: 10px;
            font-weight: 800;
            color: #000;
            letter-spacing: 1px;
        }

        @media print {
            body {
                background: none;
                padding: 0;
            }
            .no-print {
                display: none !important;
            }
            .grid-labels {
                gap: 10px;
            }
            .barcode-label {
                border: 1px solid #000;
            }
        }
    </style>
</head>
<body>

<div class="header-action no-print">
    <div>
        <h3>Cetak Massal Barcode Seluruh Eksemplar (Total: {{ $eksemplars->count() }} Fisik Buku)</h3>
        <p style="color: #666; font-size: 12px; margin-top: 2px;">Setiap label mewakili 1 fisik eksemplar buku dengan barcode unik.</p>
    </div>
    <div style="display: flex; gap: 10px;">
        <button class="btn-print" onclick="window.print()">🖨️ Cetak Semua Barcode</button>
        <button class="btn-print" style="background:#8592a3;" onclick="window.close()">Tutup</button>
    </div>
</div>

<div class="grid-labels">
    @foreach($eksemplars as $eks)
    <div class="barcode-label">
        <div class="label-header">PERPUSTAKAAN ISRA</div>
        <div class="book-title">{{ $eks->buku->judul }}</div>
        <div class="book-location">
            Rak: {{ $eks->laci ? ($eks->laci->lemari->kode_lemari . '/' . $eks->laci->kode_laci) : 'Umum' }}
            • {{ $eks->buku->kategori->nama_kategori ?? 'Umum' }}
        </div>
        <div class="barcode-area">
            {!! \App\Helpers\BarcodeHelper::generateBarcodeSVG($eks->kode_eksemplar, 1.5, 32) !!}
        </div>
        <div class="barcode-number">{{ $eks->kode_eksemplar }}</div>
    </div>
    @endforeach
</div>

</body>
</html>
