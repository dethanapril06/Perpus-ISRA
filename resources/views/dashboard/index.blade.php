@extends('layouts.app')

@section('title', 'Dashboard Perpustakaan')

@section('content')
<div class="row">
    <!-- Welcome Card Banner -->
    <div class="col-12 mb-4">
        <div class="card bg-primary text-white">
            <div class="d-flex align-items-end row">
                <div class="col-sm-7">
                    <div class="card-body">
                        <h4 class="card-title text-white mb-2">Selamat Datang, {{ auth()->user()->name }}! 🎉</h4>
                        <p class="mb-3">
                            Sistem Informasi Perpustakaan ISRA siap membantu operasional peminjaman, pengembalian, dan presensi kunjungan dengan scan barcode cepat.
                        </p>
                        <div class="d-flex gap-2 flex-wrap">
                            <a href="{{ route('kunjungan.index') }}" class="btn btn-warning btn-sm fw-semibold">
                                <i class="bx bx-walk me-1"></i> Scan Kunjungan
                            </a>
                            <a href="{{ route('peminjaman.create') }}" class="btn btn-light text-primary btn-sm fw-semibold">
                                <i class="bx bx-barcode-reader me-1"></i> Scan Peminjaman
                            </a>
                            <a href="{{ route('pengembalian.index') }}" class="btn btn-success btn-sm fw-semibold">
                                <i class="bx bx-check-double me-1"></i> Scan Pengembalian
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-sm-5 text-center text-sm-left">
                    <div class="card-body pb-0 px-0 px-md-4">
                        <img src="{{ asset('template/assets/img/illustrations/man-with-laptop-light.png') }}" height="140" alt="Dashboard Illustration" />
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 6 Metric Cards -->
    <div class="col-lg-2 col-md-4 col-6 mb-4">
        <div class="card h-100">
            <div class="card-body p-3">
                <div class="card-title d-flex align-items-start justify-content-between mb-2">
                    <div class="avatar flex-shrink-0">
                        <span class="avatar-initial rounded bg-label-primary"><i class="bx bx-id-card"></i></span>
                    </div>
                </div>
                <span class="fw-semibold d-block mb-1 small text-muted">Total Anggota</span>
                <h4 class="card-title mb-0 fw-bold text-dark">{{ $totalAnggota }}</h4>
                <small class="text-success"><i class="bx bx-check"></i> Aktif</small>
            </div>
        </div>
    </div>

    <div class="col-lg-2 col-md-4 col-6 mb-4">
        <div class="card h-100">
            <div class="card-body p-3">
                <div class="card-title d-flex align-items-start justify-content-between mb-2">
                    <div class="avatar flex-shrink-0">
                        <span class="avatar-initial rounded bg-label-info"><i class="bx bx-book"></i></span>
                    </div>
                </div>
                <span class="fw-semibold d-block mb-1 small text-muted">Katalog Buku</span>
                <h4 class="card-title mb-0 fw-bold text-dark">{{ $totalBukuJudul }}</h4>
                <small class="text-muted">{{ $totalBukuStok }} Eksemplar</small>
            </div>
        </div>
    </div>

    <div class="col-lg-2 col-md-4 col-6 mb-4">
        <div class="card h-100">
            <div class="card-body p-3">
                <div class="card-title d-flex align-items-start justify-content-between mb-2">
                    <div class="avatar flex-shrink-0">
                        <span class="avatar-initial rounded bg-label-warning"><i class="bx bx-book-bookmark"></i></span>
                    </div>
                </div>
                <span class="fw-semibold d-block mb-1 small text-muted">Pinjaman Aktif</span>
                <h4 class="card-title mb-0 fw-bold text-warning">{{ $peminjamanAktif }}</h4>
                <small class="text-muted">Sedang dipinjam</small>
            </div>
        </div>
    </div>

    <div class="col-lg-2 col-md-4 col-6 mb-4">
        <div class="card h-100">
            <div class="card-body p-3">
                <div class="card-title d-flex align-items-start justify-content-between mb-2">
                    <div class="avatar flex-shrink-0">
                        <span class="avatar-initial rounded bg-label-danger"><i class="bx bx-time-five"></i></span>
                    </div>
                </div>
                <span class="fw-semibold d-block mb-1 small text-muted">Buku Terlambat</span>
                <h4 class="card-title mb-0 fw-bold text-danger">{{ $peminjamanTerlambat }}</h4>
                <small class="text-danger">Lewat jatuh tempo</small>
            </div>
        </div>
    </div>

    <div class="col-lg-2 col-md-4 col-6 mb-4">
        <div class="card h-100">
            <div class="card-body p-3">
                <div class="card-title d-flex align-items-start justify-content-between mb-2">
                    <div class="avatar flex-shrink-0">
                        <span class="avatar-initial rounded bg-label-success"><i class="bx bx-walk"></i></span>
                    </div>
                </div>
                <span class="fw-semibold d-block mb-1 small text-muted">Kunjungan Hari Ini</span>
                <h4 class="card-title mb-0 fw-bold text-success">{{ $kunjunganHariIni }}</h4>
                <small class="text-muted">{{ $kunjunganAktifHariIni }} sedang di tempat</small>
            </div>
        </div>
    </div>

    <div class="col-lg-2 col-md-4 col-6 mb-4">
        <div class="card h-100">
            <div class="card-body p-3">
                <div class="card-title d-flex align-items-start justify-content-between mb-2">
                    <div class="avatar flex-shrink-0">
                        <span class="avatar-initial rounded bg-label-secondary"><i class="bx bx-money"></i></span>
                    </div>
                </div>
                <span class="fw-semibold d-block mb-1 small text-muted">Denda Tertunggak</span>
                <h5 class="card-title mb-0 fw-bold text-danger">Rp {{ number_format($totalDendaBelumLunas, 0, ',', '.') }}</h5>
                <small class="text-muted">Belum lunas</small>
            </div>
        </div>
    </div>

    <!-- Chart Activity Section -->
    <div class="col-12 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0"><i class="bx bx-line-chart me-2 text-primary"></i>Tren Kunjungan & Peminjaman (7 Hari Terakhir)</h5>
                <span class="badge bg-label-primary">Grafik Real-Time</span>
            </div>
            <div class="card-body">
                <div id="activityTrendChart" style="min-height: 280px;"></div>
            </div>
        </div>
    </div>

    <!-- 2 Quick Tables -->
    <!-- Kunjungan Hari Ini -->
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0"><i class="bx bx-walk me-2 text-info"></i>Kunjungan Hari Ini</h5>
                <a href="{{ route('kunjungan.index') }}" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
            </div>
            <div class="table-responsive text-nowrap">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Anggota</th>
                            <th>Waktu Masuk</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($kunjunganTerbaru as $kt)
                        <tr>
                            <td>
                                <strong class="text-dark d-block">{{ $kt->anggota->nama ?? '-' }}</strong>
                                <small class="text-muted font-monospace">{{ $kt->anggota->no_anggota ?? '-' }}</small>
                            </td>
                            <td><i class="bx bx-time me-1 text-muted"></i>{{ $kt->waktu_masuk }}</td>
                            <td>
                                @if($kt->status === 'Berlangsung')
                                    <span class="badge bg-warning">Di Tempat</span>
                                @else
                                    <span class="badge bg-success">Selesai</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="text-center py-3 text-muted">Belum ada kunjungan tercatat hari ini.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Peminjaman Mendekati Jatuh Tempo -->
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0"><i class="bx bx-alarm-exclamation me-2 text-warning"></i>Jatuh Tempo Terdekat</h5>
                <a href="{{ route('peminjaman.index') }}" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
            </div>
            <div class="table-responsive text-nowrap">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Peminjam & Buku</th>
                            <th>Jatuh Tempo</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pinjamanMendekatiTempo as $pm)
                        @php
                            $isLate = Carbon\Carbon::today()->gt($pm->tgl_jatuh_tempo);
                        @endphp
                        <tr>
                            <td>
                                <strong class="text-dark d-block">{{ $pm->anggota->nama ?? '-' }}</strong>
                                <small class="text-muted text-truncate d-inline-block" style="max-width: 200px;">{{ $pm->buku->judul ?? '-' }}</small>
                            </td>
                            <td>
                                <span class="{{ $isLate ? 'text-danger fw-bold' : '' }}">{{ $pm->tgl_jatuh_tempo->format('d/m/Y') }}</span>
                            </td>
                            <td>
                                @if($isLate)
                                    <span class="badge bg-danger">Terlambat</span>
                                @else
                                    <span class="badge bg-warning">Dipinjam</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="text-center py-3 text-muted">Tidak ada peminjaman aktif.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const options = {
            series: [
                {
                    name: 'Kunjungan Anggota',
                    data: @json($chartKunjungan)
                },
                {
                    name: 'Peminjaman Buku',
                    data: @json($chartPeminjaman)
                }
            ],
            chart: {
                height: 280,
                type: 'area',
                toolbar: { show: false },
                parentHeightOffset: 0
            },
            colors: ['#696cff', '#03c3ec'],
            dataLabels: { enabled: false },
            stroke: { curve: 'smooth', width: 2 },
            xaxis: {
                categories: @json($chartLabels),
                axisBorder: { show: false },
                axisTicks: { show: false },
                labels: { style: { colors: '#a1acb8', fontSize: '12px' } }
            },
            yaxis: {
                labels: { style: { colors: '#a1acb8', fontSize: '12px' } }
            },
            grid: {
                borderColor: '#eceef1',
                padding: { top: 0, bottom: -8, left: 20, right: 20 }
            }
        };

        const chart = new ApexCharts(document.querySelector("#activityTrendChart"), options);
        chart.render();
    });
</script>
@endpush
