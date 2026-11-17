@extends('layouts.app')

@section('title', 'Transaksi Peminjaman Buku')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
            <h4 class="fw-bold py-3 mb-0"><span class="text-muted fw-light">Transaksi /</span> Peminjaman Buku</h4>
            <div class="d-flex gap-2">
                <a href="{{ route('peminjaman.create') }}" class="btn btn-primary">
                    <i class="bx bx-barcode-reader me-1"></i> Peminjaman Baru (Scan Barcode)
                </a>
            </div>
        </div>

        <!-- Pop-up auto print trigger if just saved -->
        @if(session('print_receipt_id'))
        <div class="alert alert-success alert-dismissible d-flex align-items-center mb-4" role="alert">
            <i class="bx bx-check-circle fs-3 me-2"></i>
            <div class="flex-grow-1">
                <strong>Transaksi Berhasil Disimpan!</strong> Silakan cetak bukti/struk peminjaman untuk diserahkan ke anggota.
            </div>
            <a href="{{ route('peminjaman.cetak_struk', session('print_receipt_id')) }}" target="_blank" class="btn btn-sm btn-dark me-2">
                <i class="bx bx-printer me-1"></i> Cetak Struk Peminjaman
            </a>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif

        <!-- Filter -->
        <div class="card mb-4">
            <div class="card-body">
                <form action="{{ route('peminjaman.index') }}" method="GET" class="row g-3">
                    <div class="col-md-4">
                        <div class="input-group">
                            <span class="input-group-text"><i class="bx bx-search"></i></span>
                            <input type="text" name="search" class="form-control" placeholder="Cari nama anggota, no anggota, judul buku..." value="{{ request('search') }}">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <select name="status" class="form-select">
                            <option value="">-- Semua Status --</option>
                            <option value="Dipinjam" {{ request('status') === 'Dipinjam' ? 'selected' : '' }}>Sedang Dipinjam</option>
                            <option value="Selesai" {{ request('status') === 'Selesai' ? 'selected' : '' }}>Sudah Selesai</option>
                            <option value="Terlambat" {{ request('status') === 'Terlambat' ? 'selected' : '' }}>Terlambat</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <input type="date" name="tgl_mulai" class="form-control" placeholder="Dari Tgl Pinjam" value="{{ request('tgl_mulai') }}">
                    </div>
                    <div class="col-md-2">
                        <input type="date" name="tgl_selesai" class="form-control" placeholder="Sampai Tgl Pinjam" value="{{ request('tgl_selesai') }}">
                    </div>
                    <div class="col-md-2 d-flex gap-2">
                        <button type="submit" class="btn btn-primary w-100"><i class="bx bx-filter-alt"></i> Filter</button>
                        @if(request()->hasAny(['search', 'status', 'tgl_mulai', 'tgl_selesai']))
                            <a href="{{ route('peminjaman.index') }}" class="btn btn-outline-secondary"><i class="bx bx-reset"></i></a>
                        @endif
                    </div>
                </form>
            </div>
        </div>

        <!-- Table -->
        <div class="card">
            <div class="table-responsive text-nowrap">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th width="40">No</th>
                            <th>Peminjam (Anggota)</th>
                            <th>Buku yang Dipinjam</th>
                            <th>Tanggal Pinjam</th>
                            <th>Jatuh Tempo</th>
                            <th>Tanggal Kembali</th>
                            <th>Status Transaksi</th>
                            <th width="100" class="text-center">Struk / Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
                        @forelse($peminjaman as $index => $p)
                        @php
                            $isOverdue = ($p->status === 'Dipinjam' && Carbon\Carbon::today()->gt($p->tgl_jatuh_tempo));
                        @endphp
                        <tr>
                            <td>{{ $peminjaman->firstItem() + $index }}</td>
                            <td>
                                <a href="{{ route('anggota.show', $p->anggota->id ?? 0) }}" class="fw-bold text-dark d-block">
                                    {{ $p->anggota->nama ?? '-' }}
                                </a>
                                <small class="text-muted font-monospace">{{ $p->anggota->no_anggota ?? '-' }} • {{ $p->anggota->kelas_jabatan ?? '-' }}</small>
                            </td>
                            <td>
                                <strong class="text-dark d-block text-wrap" style="max-width: 250px;">{{ $p->eksemplar->buku->judul ?? '-' }}</strong>
                                <small class="text-muted font-monospace"><i class="bx bx-barcode me-1"></i>{{ $p->eksemplar->kode_eksemplar ?? '-' }}</small>
                            </td>
                            <td>{{ $p->tgl_pinjam->format('d/m/Y') }}</td>
                            <td>
                                <span class="{{ $isOverdue ? 'text-danger fw-bold' : '' }}">
                                    {{ $p->tgl_jatuh_tempo->format('d/m/Y') }}
                                </span>
                                @if($isOverdue)
                                    <small class="badge bg-label-danger d-block">Lewat Jatuh Tempo</small>
                                @endif
                            </td>
                            <td>
                                @if($p->tgl_kembali)
                                    {{ $p->tgl_kembali->format('d/m/Y') }}
                                    @if($p->kondisi_kembali)
                                        <small class="text-muted d-block text-capitalize">Kondisi: {{ $p->kondisi_kembali }}</small>
                                    @endif
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($p->status === 'Dipinjam')
                                    @if($isOverdue)
                                        <span class="badge bg-danger">Terlambat</span>
                                    @else
                                        <span class="badge bg-warning">Dipinjam</span>
                                    @endif
                                @elseif($p->status === 'Selesai')
                                    <span class="badge bg-success"><i class="bx bx-check me-1"></i> Selesai</span>
                                @else
                                    <span class="badge bg-danger">Terlambat</span>
                                @endif

                                @if($p->denda)
                                    <small class="d-block mt-1">
                                        Denda: <strong class="{{ $p->denda->status_bayar === 'Lunas' ? 'text-success' : 'text-danger' }}">
                                            Rp {{ number_format($p->denda->nominal, 0, ',', '.') }} ({{ $p->denda->status_bayar }})
                                        </strong>
                                    </small>
                                @endif
                            </td>
                            <td class="text-center">
                                <a href="{{ route('peminjaman.cetak_struk', $p->id) }}" target="_blank" class="btn btn-icon btn-sm btn-outline-secondary" title="Cetak Struk Pinjam">
                                    <i class="bx bx-receipt"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">
                                <i class="bx bx-book-bookmark fs-3 d-block mb-1"></i>
                                Belum ada data transaksi peminjaman.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer d-flex justify-content-between align-items-center py-2">
                <span class="text-muted small">Menampilkan {{ $peminjaman->firstItem() ?? 0 }} - {{ $peminjaman->lastItem() ?? 0 }} dari {{ $peminjaman->total() }} transaksi</span>
                <div>{{ $peminjaman->links('pagination::bootstrap-5') }}</div>
            </div>
        </div>
    </div>
</div>
@endsection
