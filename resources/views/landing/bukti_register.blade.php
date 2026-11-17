<!DOCTYPE html>
<html lang="id" class="light-style layout-menu-fixed" dir="ltr" data-theme="theme-default">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
    <title>Bukti Pendaftaran Anggota - {{ $anggota->nama }}</title>

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('template/assets/img/favicon/favicon.ico') }}" />

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap" rel="stylesheet" />

    <!-- Icons -->
    <link rel="stylesheet" href="{{ asset('template/assets/vendor/fonts/boxicons.css') }}" />

    <!-- Core CSS -->
    <link rel="stylesheet" href="{{ asset('template/assets/vendor/css/core.css') }}" />
    <link rel="stylesheet" href="{{ asset('template/assets/vendor/css/theme-default.css') }}" />

    <style>
        body {
            background-color: #f5f5f9;
            padding: 30px 15px;
        }
        .id-card {
            width: 380px;
            height: 235px;
            border-radius: 12px;
            background: linear-gradient(135deg, #1e266d 0%, #4338ca 100%);
            color: #ffffff;
            padding: 16px;
            box-shadow: 0 12px 30px rgba(67, 89, 113, 0.25);
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            margin: 0 auto;
        }
        .id-card::before {
            content: '';
            position: absolute;
            top: -40px;
            right: -40px;
            width: 140px;
            height: 140px;
            background: rgba(255, 255, 255, 0.08);
            border-radius: 50%;
        }
        .card-header-text h6 {
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 0.5px;
            margin-bottom: 1px;
            color: #ffffff;
        }
        .card-header-text small {
            font-size: 8px;
            color: rgba(255, 255, 255, 0.75);
            display: block;
        }
        .avatar-box {
            width: 60px;
            height: 75px;
            border-radius: 6px;
            border: 2px solid rgba(255, 255, 255, 0.8);
            object-fit: cover;
            background: #ffffff;
        }
        .member-info h5 {
            font-size: 14px;
            font-weight: 700;
            color: #ffffff;
            margin-bottom: 2px;
        }
        .member-info small {
            font-size: 9.5px;
            color: rgba(255, 255, 255, 0.85);
            display: block;
        }
        .barcode-card-area {
            background: #ffffff;
            border-radius: 6px;
            padding: 4px 8px;
            display: inline-flex;
            flex-direction: column;
            align-items: center;
        }
        .barcode-card-area svg {
            height: 26px;
            max-width: 170px;
        }
        .barcode-card-area span {
            font-family: monospace;
            font-size: 8.5px;
            font-weight: 800;
            color: #000;
            margin-top: 1px;
        }

        @media print {
            body {
                background: none;
                padding: 0;
            }
            .no-print {
                display: none !important;
            }
            .id-card {
                box-shadow: none;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>

<body>

<div class="container" style="max-width: 680px;">
    <div class="card shadow-sm border-0 mb-4 text-center p-4">
        <div class="avatar avatar-xl bg-label-success rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center">
            <i class="bx bx-check fs-1 text-success"></i>
        </div>
        <h4 class="fw-bold text-dark mb-1">Pendaftaran Anggota Berhasil!</h4>
        <p class="text-muted small mb-4">
            Selamat, <strong>{{ $anggota->nama }}</strong>! Data Anda telah terdaftar dalam sistem perpustakaan.<br>
            Simpan atau cetak bukti pendaftaran ini untuk ditunjukkan kepada petugas saat pairing/aktivasi kartu RFID Anda.
        </p>

        <!-- KARTU BUKTI PENDAFTARAN DIGITAL -->
        <div class="id-card text-start mb-4">
            <!-- Header Kartu -->
            <div class="d-flex align-items-center justify-content-between">
                <div class="card-header-text">
                    <h6 class="text-uppercase">{{ $pengaturan->nama_perpustakaan }}</h6>
                    <small>BUKTI PENDAFTARAN ANGGOTA</small>
                </div>
                <i class="bx bx-book-bookmark fs-3 text-white-50"></i>
            </div>

            <!-- Body Kartu -->
            <div class="d-flex align-items-center gap-3 my-2">
                @if($anggota->foto)
                    <img src="{{ asset('storage/' . $anggota->foto) }}" alt="{{ $anggota->nama }}" class="avatar-box">
                @else
                    <div class="avatar-box d-flex align-items-center justify-content-center text-dark">
                        <i class="bx bx-user fs-2 text-secondary"></i>
                    </div>
                @endif
                <div class="member-info">
                    <h5 class="text-truncate" style="max-width: 250px;">{{ $anggota->nama }}</h5>
                    <small>No. ID / NIS: <strong>{{ $anggota->identitas }}</strong></small>
                    <small>Kelas / Jabatan: {{ $anggota->kelas_jabatan }}</small>
                    <small>Status: <span class="badge bg-success" style="font-size: 8px;">Aktif</span></small>
                </div>
            </div>

            <!-- Footer Barcode Kartu -->
            <div class="d-flex justify-content-between align-items-end">
                <div class="barcode-card-area">
                    {!! \App\Helpers\BarcodeHelper::generateBarcodeSVG($anggota->no_anggota, 1.3, 26) !!}
                    <span>{{ $anggota->no_anggota }}</span>
                </div>
                <small style="font-size: 8px; color: rgba(255, 255, 255, 0.7);">
                    Berlaku s/d: {{ date('d/m/Y', strtotime('+3 years')) }}
                </small>
            </div>
        </div>

        <!-- Tombol Aksi -->
        <div class="no-print d-flex justify-content-center gap-2 flex-wrap">
            <button class="btn btn-primary px-4 fw-bold" onclick="window.print()">
                <i class="bx bx-printer me-1"></i> Cetak Bukti Pendaftaran
            </button>
            <a href="{{ route('landing.index') }}" class="btn btn-outline-secondary">
                <i class="bx bx-home me-1"></i> Kembali ke Beranda
            </a>
        </div>
    </div>
</div>

</body>
</html>
