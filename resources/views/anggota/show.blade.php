@extends('layouts.app')

@section('title', 'Detail Anggota - ' . $anggota->nama)

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-bold py-3 mb-0">
                <span class="text-muted fw-light"><a href="{{ route('anggota.index') }}">Data Anggota</a> /</span> 
                {{ $anggota->nama }}
            </h4>
            <div class="d-flex gap-2">
                <a href="{{ route('anggota.index') }}" class="btn btn-secondary">
                    <i class="bx bx-arrow-back me-1"></i> Kembali
                </a>
            </div>
        </div>

        <div class="row">
            <!-- Profil Card -->
            <div class="col-md-4 mb-4">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <div class="mb-3">
                            @if($anggota->foto)
                                <img src="{{ asset('storage/' . $anggota->foto) }}" alt="{{ $anggota->nama }}" class="rounded-circle shadow-sm" style="width: 120px; height: 120px; object-fit: cover;">
                            @else
                                <div class="avatar avatar-xl mx-auto">
                                    <span class="avatar-initial rounded-circle bg-label-primary fs-2">{{ substr($anggota->nama, 0, 1) }}</span>
                                </div>
                            @endif
                        </div>
                        <h5 class="mb-1 text-dark">{{ $anggota->nama }}</h5>
                        <p class="text-muted mb-2">{{ $anggota->kelas_jabatan ?: 'Anggota Perpustakaan' }}</p>
                        
                        <div class="mb-3">
                            @if($anggota->status === 'aktif')
                                <span class="badge bg-label-success fs-6"><i class="bx bx-check-circle me-1"></i> Status Aktif</span>
                            @else
                                <span class="badge bg-label-danger fs-6"><i class="bx bx-x-circle me-1"></i> Status Nonaktif</span>
                            @endif
                        </div>

                        <!-- RFID Card Section -->
                        <div class="card bg-label-primary p-3 rounded mb-3 text-start">
                            <div class="d-flex align-items-center">
                                <i class="bx bx-broadcast fs-3 text-primary me-2"></i>
                                <div>
                                    <small class="text-muted d-block">UID Kartu RFID</small>
                                    @if($anggota->rfid_uid)
                                        <code class="fw-bold fs-6 text-primary">{{ $anggota->rfid_uid }}</code>
                                    @else
                                        <span class="badge bg-secondary">Belum Di-pairing RFID</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <ul class="list-group list-group-flush text-start">
                            <li class="list-group-item d-flex justify-content-between">
                                <span class="text-muted">No. Identitas:</span>
                                <span class="fw-semibold">{{ $anggota->identitas ?: '-' }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span class="text-muted">No. HP / WA:</span>
                                <span class="fw-semibold">{{ $anggota->no_hp ?: '-' }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span class="text-muted">Tgl. Terdaftar:</span>
                                <span class="fw-semibold">{{ $anggota->tgl_daftar ? $anggota->tgl_daftar->format('d/m/Y') : '-' }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span class="text-muted">Alamat:</span>
                                <span class="fw-semibold text-end">{{ $anggota->alamat ?: '-' }}</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Histori Peminjaman & Kunjungan -->
            <div class="col-md-8 mb-4">
                <!-- Riwayat Peminjaman Aktif & Terakhir -->
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="bx bx-book-bookmark me-2 text-primary"></i>Riwayat Peminjaman Buku</h5>
                    </div>
                    <div class="table-responsive text-nowrap">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Kode & Judul Buku</th>
                                    <th>Tgl Pinjam</th>
                                    <th>Jatuh Tempo</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($anggota->peminjaman as $p)
                                <tr>
                                    <td>
                                        <strong class="d-block text-dark">{{ $p->buku->judul ?? '-' }}</strong>
                                        <small class="text-muted font-monospace">{{ $p->buku->kode_buku ?? '-' }}</small>
                                    </td>
                                    <td>{{ $p->tgl_pinjam->format('d/m/Y') }}</td>
                                    <td>{{ $p->tgl_jatuh_tempo->format('d/m/Y') }}</td>
                                    <td>
                                        @if($p->status === 'Dipinjam')
                                            <span class="badge bg-label-warning">Dipinjam</span>
                                        @elseif($p->status === 'Selesai')
                                            <span class="badge bg-label-success">Selesai</span>
                                        @else
                                            <span class="badge bg-label-danger">Terlambat</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center py-3 text-muted">Belum ada riwayat transaksi peminjaman.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Riwayat Kunjungan -->
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="bx bx-walk me-2 text-info"></i>Riwayat Kunjungan Perpustakaan</h5>
                    </div>
                    <div class="table-responsive text-nowrap">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Waktu Masuk</th>
                                    <th>Waktu Keluar</th>
                                    <th>Status Kunjungan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($anggota->kunjungan as $k)
                                <tr>
                                    <td>{{ $k->tanggal->format('d/m/Y') }}</td>
                                    <td><i class="bx bx-time me-1 text-muted"></i> {{ $k->waktu_masuk }}</td>
                                    <td>{{ $k->waktu_keluar ? $k->waktu_keluar : '-' }}</td>
                                    <td>
                                        @if($k->status === 'Berlangsung')
                                            <span class="badge bg-label-primary">Berlangsung</span>
                                        @else
                                            <span class="badge bg-label-success">Selesai</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center py-3 text-muted">Belum ada data kunjungan.</td>
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
