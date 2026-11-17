<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Label Barcode Eksemplar - {{ $eksemplar->kode_eksemplar }}</title>
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
        .container {
            display: flex;
            flex-direction: column;
            gap: 20px;
            align-items: center;
        }
        .barcode-label {
            width: 280px;
            border: 1.5px solid #333;
            border-radius: 8px;
            padding: 10px;
            background: #ffffff;
            text-align: center;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            page-break-inside: avoid;
        }
        .label-header {
            font-size: 10px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #222;
            border-bottom: 1px dashed #666;
            padding-bottom: 4px;
            margin-bottom: 6px;
        }
        .book-title {
            font-size: 11px;
            font-weight: 700;
            color: #111;
            line-height: 1.2;
            margin-bottom: 4px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .book-location {
            font-size: 9px;
            color: #555;
            margin-bottom: 6px;
        }
        .barcode-area {
            display: flex;
            justify-content: center;
            margin-bottom: 4px;
        }
        .barcode-area svg {
            height: 38px;
            max-width: 240px;
        }
        .barcode-number {
            font-family: monospace;
            font-size: 12px;
            font-weight: 800;
            color: #000;
            letter-spacing: 1.5px;
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
            box-shadow: 0 4px 12px rgba(105, 108, 255, 0.4);
        }
        .btn-print:hover {
            background: #5f61e6;
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
            .barcode-label {
                box-shadow: none;
                border: 1px solid #000;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <div class="no-print" style="display: flex; gap: 10px;">
        <button class="btn-print" onclick="window.print()">🖨️ Cetak Label Eksemplar Ini</button>
        <button class="btn-print" style="background:#8592a3;" onclick="window.close()">Tutup</button>
    </div>

    <div class="barcode-label">
        <div class="label-header">PERPUSTAKAAN ISRA</div>
        <div class="book-title">{{ $eksemplar->buku->judul }}</div>
        <div class="book-location">
            Rak: {{ $eksemplar->laci ? ($eksemplar->laci->lemari->kode_lemari . ' / ' . $eksemplar->laci->kode_laci) : 'Umum' }}
            • {{ $eksemplar->buku->kategori->nama_kategori ?? 'Umum' }}
        </div>
        <div class="barcode-area">
            {!! \App\Helpers\BarcodeHelper::generateBarcodeSVG($eksemplar->kode_eksemplar, 1.8, 38) !!}
        </div>
        <div class="barcode-number">{{ $eksemplar->kode_eksemplar }}</div>
    </div>
</div>

</body>
</html>
