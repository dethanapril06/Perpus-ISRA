@extends('layouts.app')

@section('title', 'Manajemen Kunjungan Perpustakaan')

@section('content')
<div class="row">
    <div class="col-12">
        <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Layanan /</span> Presensi & Kunjungan Anggota</h4>

        <!-- Scanner Card Check-in (Tap RFID) -->
        <div class="card mb-4 border-primary shadow-sm">
            <div class="card-body py-4">
                <div class="row justify-content-center text-center">
                    <div class="col-lg-8 col-md-10">
                        <div class="avatar avatar-md bg-label-primary mx-auto mb-2">
                            <i class="bx bx-broadcast fs-3"></i>
                        </div>
                        <h4 class="card-title mb-1 text-primary fw-bold">Presensi Kunjungan (Tap Kartu RFID)</h4>
                        <p class="text-muted mb-3">Tempelkan kartu RFID anggota pada reader untuk check-in presensi perpustakaan.</p>

                        <form action="{{ route('kunjungan.store') }}" method="POST" id="formScanKunjungan">
                            @csrf
                            <div class="position-relative">
                                <div class="input-group input-group-lg shadow-sm border border-2 border-primary rounded-3 overflow-hidden">
                                    <span class="input-group-text bg-primary text-white border-0"><i class="bx bx-broadcast fs-3"></i></span>
                                    <input type="text" name="kode_anggota" id="inputKodeAnggota" 
                                           class="form-control font-monospace text-center fs-4 py-3 border-0" 
                                           placeholder="[ Tempelkan Kartu RFID di Sini ]" 
                                           autofocus required autocomplete="off">
                                </div>
                                <div id="loadingIndicator" class="mt-3 text-primary fw-bold" style="display: none;">
                                    <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                    Kartu RFID terdeteksi! Sedang memproses presensi kunjungan...
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Kunjungan Sedang Berlangsung Hari Ini -->
        @if($kunjunganAktif->count() > 0)
        <div class="card mb-4 border-warning">
            <div class="card-header bg-label-warning d-flex justify-content-between align-items-center py-2">
                <h6 class="mb-0 fw-bold text-dark">
                    <i class="bx bx-walk me-1 text-warning"></i> Kunjungan Sedang Berlangsung Hari Ini ({{ $kunjunganAktif->count() }} Anggota)
                </h6>
                <span class="badge bg-warning scanner-badge">Aktif di Tempat</span>
            </div>
            <div class="card-body pt-3 pb-2">
                <div class="row g-3">
                    @foreach($kunjunganAktif as $ka)
                    <div class="col-md-4 col-sm-6">
                        <div class="card border shadow-none h-100">
                            <div class="card-body p-3">
                                <div class="d-flex align-items-center mb-2">
                                    @if($ka->anggota->foto)
                                        <img src="{{ asset('storage/' . $ka->anggota->foto) }}" class="rounded-circle me-2" style="width: 38px; height: 38px; object-fit: cover;">
                                    @else
                                        <div class="avatar avatar-sm me-2">
                                            <span class="avatar-initial rounded-circle bg-label-primary">{{ substr($ka->anggota->nama, 0, 1) }}</span>
                                        </div>
                                    @endif
                                    <div>
                                        <h6 class="mb-0 fw-bold text-dark">{{ $ka->anggota->nama }}</h6>
                                        <small class="text-muted font-monospace">{{ $ka->anggota->no_anggota }}</small>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-between small text-muted mb-2">
                                    <span><i class="bx bx-time me-1"></i>Masuk: <strong>{{ $ka->waktu_masuk }}</strong></span>
                                    <span><i class="bx bx-book-reader me-1"></i><strong>{{ $ka->detailBuku->count() }}</strong> Buku Dibaca</span>
                                </div>
                                <div class="d-flex gap-1">
                                    <a href="{{ route('kunjungan.show', $ka->id) }}" class="btn btn-sm btn-primary w-100">
                                        <i class="bx bx-book me-1"></i> Scan Buku / Kelola
                                    </a>
                                    <form action="{{ route('kunjungan.selesai', $ka->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="button" class="btn btn-sm btn-outline-success btn-action-confirm" 
                                                data-title="Selesaikan Kunjungan?" 
                                                data-text="Selesaikan kunjungan untuk anggota {{ $ka->anggota->nama }}?" 
                                                data-icon="question"
                                                data-confirm-text="Ya, Checkout"
                                                data-btn-class="btn-success"
                                                title="Selesai Kunjungan">
                                            <i class="bx bx-check"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        <!-- Riwayat Semua Kunjungan Table -->
        <div class="card">
            <div class="card-header pb-0">
                <form action="{{ route('kunjungan.index') }}" method="GET" class="row g-3 mb-3">
                    <div class="col-md-5">
                        <div class="input-group">
                            <span class="input-group-text"><i class="bx bx-search"></i></span>
                            <input type="text" name="search" class="form-control" placeholder="Cari nama anggota, no anggota..." value="{{ request('search') }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <input type="date" name="tanggal" class="form-control" value="{{ request('tanggal') }}">
                    </div>
                    <div class="col-md-2">
                        <select name="status" class="form-select">
                            <option value="">-- Semua Status --</option>
                            <option value="Berlangsung" {{ request('status') === 'Berlangsung' ? 'selected' : '' }}>Berlangsung</option>
                            <option value="Selesai" {{ request('status') === 'Selesai' ? 'selected' : '' }}>Selesai</option>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex gap-2">
                        <button type="submit" class="btn btn-primary w-100"><i class="bx bx-filter-alt"></i> Filter</button>
                        @if(request()->hasAny(['search', 'tanggal', 'status']))
                            <a href="{{ route('kunjungan.index') }}" class="btn btn-outline-secondary"><i class="bx bx-reset"></i></a>
                        @endif
                    </div>
                </form>
            </div>

            <div class="table-responsive text-nowrap">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th width="50">No</th>
                            <th>Anggota</th>
                            <th>Tanggal Kunjungan</th>
                            <th>Waktu Masuk & Keluar</th>
                            <th>Buku Dibaca</th>
                            <th>Status</th>
                            <th width="120" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
                        @forelse($kunjungan as $index => $k)
                        <tr>
                            <td>{{ $kunjungan->firstItem() + $index }}</td>
                            <td>
                                <strong class="text-dark d-block">{{ $k->anggota->nama ?? '-' }}</strong>
                                <small class="text-muted font-monospace">{{ $k->anggota->no_anggota ?? '-' }} • {{ $k->anggota->kelas_jabatan ?? '-' }}</small>
                            </td>
                            <td>{{ $k->tanggal->format('d/m/Y') }}</td>
                            <td>
                                <span class="badge bg-label-info"><i class="bx bx-log-in me-1"></i>{{ $k->waktu_masuk }}</span>
                                @if($k->waktu_keluar)
                                    <span class="badge bg-label-secondary ms-1"><i class="bx bx-log-out me-1"></i>{{ $k->waktu_keluar }}</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-label-primary">
                                    <i class="bx bx-book-open me-1"></i> {{ $k->detailBuku->count() }} Buku
                                </span>
                            </td>
                            <td>
                                @if($k->status === 'Berlangsung')
                                    <span class="badge bg-warning"><i class="bx bx-loader bx-spin me-1"></i> Berlangsung</span>
                                @else
                                    <span class="badge bg-success"><i class="bx bx-check-circle me-1"></i> Selesai</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <a href="{{ route('kunjungan.show', $k->id) }}" class="btn btn-sm btn-outline-primary" title="Lihat Rincian">
                                    <i class="bx bx-show me-1"></i> Rincian
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">
                                <i class="bx bx-walk fs-3 d-block mb-1"></i>
                                Belum ada data kunjungan yang tercatat.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer d-flex justify-content-between align-items-center py-2">
                <span class="text-muted small">Menampilkan {{ $kunjungan->firstItem() ?? 0 }} - {{ $kunjungan->lastItem() ?? 0 }} dari {{ $kunjungan->total() }} kunjungan</span>
                <div>{{ $kunjungan->links('pagination::bootstrap-5') }}</div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        const $input = $('#inputKodeAnggota');
        const $form = $('#formScanKunjungan');
        const $loading = $('#loadingIndicator');
        let scanTimer = null;
        let isSubmitting = false;

        function submitCheckin() {
            const val = $input.val().trim();
            if (val.length >= 3 && !isSubmitting) {
                isSubmitting = true;
                $loading.slideDown(150);
                $input.prop('readonly', true);
                if (typeof playBeep === 'function') {
                    playBeep(true);
                }
                $form.submit();
            }
        }

        // 1. Eksekusi instan jika RFID Reader mengirim tombol Enter
        $input.on('keydown', function(e) {
            if (e.which === 13) {
                e.preventDefault();
                clearTimeout(scanTimer);
                submitCheckin();
            }
        });

        // 2. Eksekusi otomatis setelah reader selesai membaca UID kartu (debounce 200ms)
        $input.on('input', function() {
            clearTimeout(scanTimer);
            const val = $(this).val().trim();
            if (val.length >= 4) {
                scanTimer = setTimeout(function() {
                    submitCheckin();
                }, 200);
            }
        });

        // 3. Jaga agar kursor selalu fokus stanby di kotak RFID
        $(document).on('click', function(e) {
            if (!$(e.target).is('a, button, input, select, textarea, .btn')) {
                $input.focus();
            }
        });
        $input.focus();
    });
</script>
@endpush
