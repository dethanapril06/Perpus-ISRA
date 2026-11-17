@extends('layouts.app')

@section('title', 'Log Aktivitas Sistem')

@section('content')
<div class="row">
    <div class="col-12">
        <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Laporan & Audit /</span> Log Aktivitas Pengguna</h4>

        <!-- Filter -->
        <div class="card mb-4">
            <div class="card-body">
                <form action="{{ route('activity-log.index') }}" method="GET" class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Pencarian</label>
                        <input type="text" name="search" class="form-control" placeholder="Cari aktivitas, detail, atau user..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Modul</label>
                        <select name="modul" class="form-select">
                            <option value="">-- Semua Modul --</option>
                            @foreach($modules as $m)
                                <option value="{{ $m }}" {{ request('modul') === $m ? 'selected' : '' }}>{{ $m }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Dari Tanggal</label>
                        <input type="date" name="tgl_mulai" class="form-control" value="{{ request('tgl_mulai') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Sampai Tanggal</label>
                        <input type="date" name="tgl_selesai" class="form-control" value="{{ request('tgl_selesai') }}">
                    </div>
                    <div class="col-md-1 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100" title="Filter"><i class="bx bx-filter-alt"></i></button>
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
                            <th width="60">No</th>
                            <th>Waktu</th>
                            <th>Pengguna</th>
                            <th>Aktivitas</th>
                            <th>Modul</th>
                            <th>Detail Aktivitas</th>
                            <th>IP Address</th>
                        </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
                        @forelse($logs as $index => $log)
                        <tr>
                            <td>{{ $logs->firstItem() + $index }}</td>
                            <td>
                                <span class="fw-semibold d-block">{{ $log->created_at->format('d/m/Y') }}</span>
                                <small class="text-muted">{{ $log->created_at->format('H:i:s') }}</small>
                            </td>
                            <td>
                                @if($log->user)
                                    <span class="fw-bold">{{ $log->user->name }}</span>
                                    <small class="text-muted d-block">({{ $log->user->username }})</small>
                                @else
                                    <span class="text-muted">Sistem / Tamu</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-label-secondary">{{ $log->aktivitas }}</span>
                            </td>
                            <td>
                                <span class="badge bg-label-primary">{{ $log->modul }}</span>
                            </td>
                            <td class="text-wrap" style="max-width: 320px;">
                                <small>{{ $log->detail ?: '-' }}</small>
                            </td>
                            <td><code>{{ $log->ip_address ?: '-' }}</code></td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">
                                <i class="bx bx-history fs-3 d-block mb-1"></i>
                                Belum ada log aktivitas yang tercatat.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer d-flex justify-content-between align-items-center py-2">
                <span class="text-muted small">Menampilkan {{ $logs->firstItem() ?? 0 }} - {{ $logs->lastItem() ?? 0 }} dari {{ $logs->total() }} log</span>
                <div>{{ $logs->links('pagination::bootstrap-5') }}</div>
            </div>
        </div>
    </div>
</div>
@endsection
