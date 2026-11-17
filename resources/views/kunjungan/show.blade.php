@extends('layouts.app')

@section('title', 'Rincian Kunjungan - ' . $kunjungan->anggota->nama)

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
            <h4 class="fw-bold py-3 mb-0">
                <span class="text-muted fw-light"><a href="{{ route('kunjungan.index') }}">Kunjungan</a> /</span> 
                Rincian Kunjungan #{{ $kunjungan->id }}
            </h4>
            <div class="d-flex gap-2">
                @if($kunjungan->status === 'Berlangsung')
                    <form action="{{ route('kunjungan.selesai', $kunjungan->id) }}" method="POST" class="d-inline">
                        @csrf
                        <button type="button" class="btn btn-success btn-action-confirm"
                                data-title="Selesaikan Kunjungan?"
                                data-text="Kunjungan anggota {{ $kunjungan->anggota->nama }} akan ditandai selesai dan waktu keluar akan dicatat."
                                data-icon="question"
                                data-confirm-text="Ya, Checkout Sekarang"
                                data-btn-class="btn-success">
                            <i class="bx bx-check-circle me-1"></i> Selesaikan Kunjungan (Checkout)
                        </button>
                    </form>
                @endif
                <a href="{{ route('kunjungan.index') }}" class="btn btn-secondary">
                    <i class="bx bx-arrow-back me-1"></i> Kembali ke Daftar
                </a>
            </div>
        </div>

        <div class="row">
            <!-- Info Anggota & Status Kunjungan -->
            <div class="col-md-4 mb-4">
                <div class="card h-100">
                    <div class="card-header bg-label-primary py-3">
                        <h6 class="mb-0 fw-bold"><i class="bx bx-user me-2"></i>Data Pengunjung</h6>
                    </div>
                    <div class="card-body pt-3 text-center">
                        <div class="mb-3">
                            @if($kunjungan->anggota->foto)
                                <img src="{{ asset('storage/' . $kunjungan->anggota->foto) }}" alt="{{ $kunjungan->anggota->nama }}" class="rounded-circle shadow-sm" style="width: 90px; height: 90px; object-fit: cover;">
                            @else
                                <div class="avatar avatar-xl mx-auto">
                                    <span class="avatar-initial rounded-circle bg-label-primary fs-3">{{ substr($kunjungan->anggota->nama, 0, 1) }}</span>
                                </div>
                            @endif
                        </div>
                        <h5 class="mb-1 text-dark">{{ $kunjungan->anggota->nama }}</h5>
                        <p class="text-muted mb-2 font-monospace">{{ $kunjungan->anggota->no_anggota }}</p>

                        <div class="mb-3">
                            @if($kunjungan->status === 'Berlangsung')
                                <span class="badge bg-warning fs-6"><i class="bx bx-loader bx-spin me-1"></i> Sedang Berkunjung</span>
                            @else
                                <span class="badge bg-success fs-6"><i class="bx bx-check-circle me-1"></i> Kunjungan Selesai</span>
                            @endif
                        </div>

                        <ul class="list-group list-group-flush text-start small">
                            <li class="list-group-item d-flex justify-content-between">
                                <span class="text-muted">Kelas / Jabatan:</span>
                                <strong>{{ $kunjungan->anggota->kelas_jabatan ?: '-' }}</strong>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span class="text-muted">Tanggal Kunjungan:</span>
                                <strong>{{ $kunjungan->tanggal->format('d/m/Y') }}</strong>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span class="text-muted">Waktu Masuk:</span>
                                <strong class="text-primary">{{ $kunjungan->waktu_masuk }}</strong>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span class="text-muted">Waktu Keluar:</span>
                                <strong>{{ $kunjungan->waktu_keluar ?: '-' }}</strong>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span class="text-muted">Pinjaman Aktif:</span>
                                <span class="badge bg-label-info">{{ $kunjungan->anggota->peminjamanAktif()->count() }} Buku</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Panel Pencatatan Buku yang Dibaca -->
            <div class="col-md-8 mb-4">
                <!-- Box Scanner Buku Bacaan -->
                @if($kunjungan->status === 'Berlangsung')
                <div class="card mb-4 border-info">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div class="d-flex align-items-center">
                                <i class="bx bx-barcode-reader fs-3 text-info me-2"></i>
                                <div>
                                    <h6 class="mb-0 fw-bold text-info">Scan Barcode Buku yang Diambil / Dibaca</h6>
                                    <small class="text-muted">Arahkan scanner ke barcode eksemplar buku fisik yang sedang dibaca anggota</small>
                                </div>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-info" id="btnStartCameraBuku">
                                <i class="bx bx-camera me-1"></i> Scan Kamera
                            </button>
                        </div>

                        <!-- Camera Container -->
                        <div id="readerBukuContainer" class="mb-3 p-3 bg-light rounded text-center border" style="display: none;">
                            <div id="readerBuku" style="max-width: 320px; margin: 0 auto;"></div>
                            <button type="button" class="btn btn-sm btn-secondary mt-2" id="btnCloseCameraBuku">
                                <i class="bx bx-x me-1"></i> Tutup Kamera
                            </button>
                        </div>

                        <form action="{{ route('kunjungan.scan_buku', $kunjungan->id) }}" method="POST" id="formScanBukuBacaan" class="mt-2">
                            @csrf
                            <div class="input-group input-group-lg">
                                <span class="input-group-text bg-info text-white"><i class="bx bx-barcode fs-4"></i></span>
                                <input type="text" name="kode_buku" id="inputKodeBuku" class="form-control font-monospace" placeholder="Arahkan scanner ke label barcode buku..." autofocus required autocomplete="off">
                            </div>
                            <small class="text-muted d-block mt-1"><i class="bx bx-check-circle text-info me-1"></i>Otomatis tercatat ke daftar bacaan saat barcode buku di-scan.</small>
                        </form>
                    </div>
                </div>
                @endif

                <!-- Daftar Buku yang Dibaca -->
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="bx bx-book-open me-2 text-primary"></i>Daftar Buku yang Dibaca ({{ $kunjungan->detailBuku->count() }} Buku)</h5>
                        <span class="badge bg-label-primary">Alur Kunjungan & Baca di Tempat</span>
                    </div>

                    <div class="table-responsive text-nowrap">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th width="40">No</th>
                                    <th>Kode & Judul Buku</th>
                                    <th>Lokasi Rak</th>
                                    <th>Waktu Scan</th>
                                    <th>Status Bacaan</th>
                                    <th width="150" class="text-center">Aksi / Peminjaman</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($kunjungan->detailBuku as $index => $d)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>
                                        <strong class="text-dark d-block text-wrap" style="max-width: 250px;">{{ $d->eksemplar->buku->judul ?? '-' }}</strong>
                                        <small class="text-muted font-monospace"><i class="bx bx-barcode me-1"></i>{{ $d->eksemplar->kode_eksemplar ?? '-' }} • {{ $d->eksemplar->buku->kategori->nama_kategori ?? 'Umum' }}</small>
                                    </td>
                                    <td>
                                        @if($d->eksemplar && $d->eksemplar->laci)
                                            <small class="d-block fw-semibold">{{ $d->eksemplar->laci->lemari->kode_lemari ?? '-' }}</small>
                                            <small class="text-muted">{{ $d->eksemplar->laci->kode_laci }}</small>
                                        @else
                                            <span class="text-muted small">-</span>
                                        @endif
                                    </td>
                                    <td><small class="badge bg-label-secondary">{{ $d->waktu_scan }}</small></td>
                                    <td>
                                        @if($d->status === 'Dibaca di Tempat')
                                            <span class="badge bg-label-info"><i class="bx bx-book-reader me-1"></i> Dibaca di Tempat</span>
                                        @else
                                            <span class="badge bg-label-success"><i class="bx bx-check-double me-1"></i> Jadi Peminjaman</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($d->status === 'Dibaca di Tempat' && $kunjungan->status === 'Berlangsung')
                                            <div class="d-inline-flex gap-1">
                                                <!-- FR-KUN-06 / Jalur B: Opsi langsung jadikan peminjaman tanpa scan ulang ID Card -->
                                                <form action="{{ route('kunjungan.konversi_pinjam', $d->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="button" class="btn btn-sm btn-primary btn-action-confirm"
                                                            data-title="Jadikan Transaksi Peminjaman?"
                                                            data-text="Buku '{{ $d->eksemplar->buku->judul ?? 'Buku' }}' (Eks. {{ $d->eksemplar->kode_eksemplar ?? '' }}) akan dipinjamkan langsung ke {{ $kunjungan->anggota->nama }} tanpa perlu scan ulang kartu."
                                                            data-icon="question"
                                                            data-confirm-text="Ya, Jadikan Pinjaman"
                                                            data-btn-class="btn-primary"
                                                            title="Jadikan Peminjaman">
                                                        <i class="bx bx-book-bookmark me-1"></i> Pinjamkan
                                                    </button>
                                                </form>

                                                <!-- Hapus Bacaan -->
                                                <form action="{{ route('kunjungan.hapus_buku', $d->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="button" class="btn btn-icon btn-sm btn-outline-danger btn-delete-confirm" data-name="buku bacaan '{{ $d->buku->judul ?? '' }}'" title="Hapus dari Daftar">
                                                        <i class="bx bx-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        @else
                                            <span class="badge bg-label-secondary"><i class="bx bx-check"></i> Sudah Diproses</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">
                                        <i class="bx bx-book-reader fs-3 d-block mb-1"></i>
                                        Belum ada buku yang di-scan selama kunjungan ini. Silakan scan barcode buku pada kotak di atas.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let html5QrcodeScannerBuku = null;

    $('#btnStartCameraBuku').click(function() {
        $('#readerBukuContainer').slideDown();
        if (!html5QrcodeScannerBuku) {
            html5QrcodeScannerBuku = new Html5Qrcode("readerBuku");
        }

        Html5Qrcode.getCameras().then(devices => {
            if (devices && devices.length) {
                let cameraId = devices[0].id;
                if (devices.length > 1) {
                    cameraId = devices[1].id;
                }
                html5QrcodeScannerBuku.start(
                    cameraId,
                    { fps: 10, qrbox: { width: 260, height: 180 } },
                    (decodedText, decodedResult) => {
                        playBeep(true);
                        $('#inputKodeBuku').val(decodedText);
                        html5QrcodeScannerBuku.stop();
                        $('#readerBukuContainer').slideUp();
                        $('#formScanBukuBacaan').submit();
                    },
                    (errorMessage) => {}
                );
            }
        }).catch(err => {
            Swal.fire({
                icon: 'error',
                title: 'Kamera Tidak Tersedia',
                text: 'Pastikan izin akses kamera diaktifkan pada peramban web Anda.'
            });
        });
    });

    $('#btnCloseCameraBuku').click(function() {
        if (html5QrcodeScannerBuku) {
            html5QrcodeScannerBuku.stop().then(() => {
                $('#readerBukuContainer').slideUp();
            });
        } else {
            $('#readerBukuContainer').slideUp();
        }
    });

    // Auto submit instan saat barcode buku di-scan (tanpa perlu tekan Enter)
    let scanBukuTimer = null;
    let isSubmittingBuku = false;

    $('#inputKodeBuku').on('keydown', function(e) {
        if (e.which === 13 || e.key === 'Enter') {
            e.preventDefault();
            clearTimeout(scanBukuTimer);
            if (!isSubmittingBuku) {
                isSubmittingBuku = true;
                $('#formScanBukuBacaan').submit();
            }
        }
    });

    $('#inputKodeBuku').on('input', function() {
        clearTimeout(scanBukuTimer);
        const val = $(this).val().trim();
        if (val.length >= 3 && !isSubmittingBuku) {
            scanBukuTimer = setTimeout(function() {
                isSubmittingBuku = true;
                $('#formScanBukuBacaan').submit();
            }, 180);
        }
    });
</script>
@endpush
