@extends('layouts.app')

@section('title', 'Data Denda & Pembayaran')

@section('content')
<div class="row">
    <div class="col-12">
        <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Keuangan /</span> Data Denda Keterlambatan</h4>

        <!-- Summary Cards -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card bg-label-danger border-danger">
                    <div class="card-body d-flex justify-content-between align-items-center p-3">
                        <div>
                            <span class="d-block text-danger fw-semibold">Total Denda Belum Lunas</span>
                            <h3 class="mb-0 fw-bold text-danger">Rp {{ number_format($totalBelumLunas, 0, ',', '.') }}</h3>
                        </div>
                        <div class="avatar avatar-md bg-danger text-white rounded p-2">
                            <i class="bx bx-time-five fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card bg-label-success border-success">
                    <div class="card-body d-flex justify-content-between align-items-center p-3">
                        <div>
                            <span class="d-block text-success fw-semibold">Total Denda Terbayar (Lunas)</span>
                            <h3 class="mb-0 fw-bold text-success">Rp {{ number_format($totalLunas, 0, ',', '.') }}</h3>
                        </div>
                        <div class="avatar avatar-md bg-success text-white rounded p-2">
                            <i class="bx bx-check-double fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter -->
        <div class="card mb-4">
            <div class="card-body">
                <form action="{{ route('denda.index') }}" method="GET" class="row g-3">
                    <div class="col-md-4">
                        <div class="input-group">
                            <span class="input-group-text"><i class="bx bx-search"></i></span>
                            <input type="text" name="search" class="form-control" placeholder="Cari nama anggota, no anggota, judul buku..." value="{{ request('search') }}">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <select name="status_bayar" class="form-select">
                            <option value="">-- Semua Status --</option>
                            <option value="Belum Lunas" {{ request('status_bayar') === 'Belum Lunas' ? 'selected' : '' }}>Belum Lunas</option>
                            <option value="Lunas" {{ request('status_bayar') === 'Lunas' ? 'selected' : '' }}>Lunas</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <input type="date" name="tgl_mulai" class="form-control" placeholder="Dari Tanggal" value="{{ request('tgl_mulai') }}">
                    </div>
                    <div class="col-md-2">
                        <input type="date" name="tgl_selesai" class="form-control" placeholder="Sampai Tanggal" value="{{ request('tgl_selesai') }}">
                    </div>
                    <div class="col-md-2 d-flex gap-2">
                        <button type="submit" class="btn btn-primary w-100"><i class="bx bx-filter-alt"></i> Filter</button>
                        @if(request()->hasAny(['search', 'status_bayar', 'tgl_mulai', 'tgl_selesai']))
                            <a href="{{ route('denda.index') }}" class="btn btn-outline-secondary"><i class="bx bx-reset"></i></a>
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
                            <th>Anggota (Peminjam)</th>
                            <th>Buku Terkait</th>
                            <th>Tgl Jatuh Tempo & Kembali</th>
                            <th>Nominal Denda</th>
                            <th>Status Bayar</th>
                            <th>Tgl Pembayaran</th>
                            <th width="140" class="text-center">Aksi / Pelunasan</th>
                        </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
                        @forelse($denda as $index => $d)
                        <tr>
                            <td>{{ $denda->firstItem() + $index }}</td>
                            <td>
                                <strong class="text-dark d-block">{{ $d->peminjaman->anggota->nama ?? '-' }}</strong>
                                <small class="text-muted font-monospace">{{ $d->peminjaman->anggota->no_anggota ?? '-' }}</small>
                            </td>
                            <td>
                                <strong class="text-dark d-block text-wrap" style="max-width: 230px;">{{ $d->peminjaman->buku->judul ?? '-' }}</strong>
                                <small class="text-muted font-monospace">{{ $d->peminjaman->buku->kode_buku ?? '-' }}</small>
                            </td>
                            <td>
                                <small class="d-block text-danger">Tempo: {{ $d->peminjaman->tgl_jatuh_tempo ? $d->peminjaman->tgl_jatuh_tempo->format('d/m/Y') : '-' }}</small>
                                <small class="text-muted">Kembali: {{ $d->peminjaman->tgl_kembali ? $d->peminjaman->tgl_kembali->format('d/m/Y') : '-' }}</small>
                            </td>
                            <td>
                                <strong class="fs-6 text-danger">Rp {{ number_format($d->nominal, 0, ',', '.') }}</strong>
                            </td>
                            <td>
                                @if($d->status_bayar === 'Lunas')
                                    <span class="badge bg-success"><i class="bx bx-check-double me-1"></i> Lunas</span>
                                @else
                                    <span class="badge bg-danger"><i class="bx bx-time me-1"></i> Belum Lunas</span>
                                @endif
                            </td>
                            <td>
                                @if($d->tgl_bayar)
                                    <small>{{ $d->tgl_bayar->format('d/m/Y H:i') }}</small>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="d-inline-flex gap-1">
                                    @if($d->status_bayar === 'Belum Lunas')
                                        <form action="{{ route('denda.bayar', $d->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('PUT')
                                            <button type="button" class="btn btn-sm btn-success btn-action-confirm"
                                                    data-title="Pelunasan Denda?"
                                                    data-text="Konfirmasi pembayaran denda sebesar Rp {{ number_format($d->nominal, 0, ',', '.') }} dari {{ $d->peminjaman->anggota->nama }}?"
                                                    data-icon="question"
                                                    data-confirm-text="Ya, Tandai Lunas"
                                                    data-btn-class="btn-success"
                                                    title="Tandai Lunas">
                                                <i class="bx bx-check me-1"></i> Bayar
                                            </button>
                                        </form>
                                    @else
                                        <a href="{{ route('denda.cetak_kuitansi', $d->id) }}" target="_blank" class="btn btn-sm btn-outline-info" title="Cetak Kuitansi">
                                            <i class="bx bx-receipt me-1"></i> Kuitansi
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">
                                <i class="bx bx-money fs-3 d-block mb-1"></i>
                                Tidak ada data denda keterlambatan.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer d-flex justify-content-between align-items-center py-2">
                <span class="text-muted small">Menampilkan {{ $denda->firstItem() ?? 0 }} - {{ $denda->lastItem() ?? 0 }} dari {{ $denda->total() }} denda</span>
                <div>{{ $denda->links('pagination::bootstrap-5') }}</div>
            </div>
        </div>
    </div>
</div>
@endsection
