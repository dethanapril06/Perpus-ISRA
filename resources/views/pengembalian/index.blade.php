@extends('layouts.app')

@section('title', 'Transaksi Pengembalian Buku')

@section('content')
<div class="row">
    <div class="col-12">
        <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Transaksi /</span> Pengembalian Buku & Denda Otomatis</h4>

        <!-- Scanner Card Pengembalian -->
        <div class="card mb-4 border-success">
            <div class="card-header bg-label-success py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold text-success"><i class="bx bx-barcode-reader me-2"></i>Pengembalian (Scan Barcode)</h5>
                <span class="badge bg-success">Alur Cepat</span>
            </div>
            <div class="card-body pt-3">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold"><i class="bx bx-broadcast text-primary me-1"></i> Tap Kartu RFID Anggota (Opsional)</label>
                        <div class="input-group input-group-lg">
                            <span class="input-group-text bg-label-primary text-primary"><i class="bx bx-broadcast fs-4"></i></span>
                            <input type="text" id="scanAnggota" class="form-control font-monospace" placeholder="Tempel kartu RFID anggota di sini..." autocomplete="off">
                        </div>
                        <small class="text-muted"><i class="bx bx-check-circle text-primary me-1"></i>Otomatis menampilkan seluruh daftar buku pinjaman saat kartu di-tap.</small>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold"><i class="bx bx-barcode text-success me-1"></i> Scan Barcode Buku Langsung <span class="text-danger">*</span></label>
                        <div class="input-group input-group-lg">
                            <span class="input-group-text bg-success text-white"><i class="bx bx-barcode fs-4"></i></span>
                            <input type="text" id="scanBuku" class="form-control font-monospace" placeholder="Arahkan scanner ke label barcode buku..." autofocus autocomplete="off">
                        </div>
                        <small class="text-muted"><i class="bx bx-check-circle text-success me-1"></i>Otomatis membuka popup konfirmasi pengembalian saat barcode di-scan.</small>
                    </div>
                </div>

                <!-- Hasil Pencarian Peminjaman Aktif -->
                <div id="boxPeminjamanAktif" class="mt-4" style="display: none;">
                    <hr>
                    <h6 class="fw-bold text-dark mb-3"><i class="bx bx-list-check me-1 text-primary"></i> Data Peminjaman Aktif Ditemukan:</h6>
                    <div id="listLoansContainer" class="row g-3"></div>
                </div>
            </div>
        </div>

        <!-- Modal Form Proses Pengembalian -->
        <div class="modal fade" id="modalProsesKembali" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <form action="{{ route('pengembalian.store') }}" method="POST" id="formSubmitPengembalian">
                        @csrf
                        <input type="hidden" name="id_peminjaman" id="modalIdPeminjaman">
                        <div class="modal-header bg-label-primary">
                            <h5 class="modal-title fw-bold"><i class="bx bx-check-shield me-1"></i> Konfirmasi Pengembalian Buku</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-6 mb-3 border-end">
                                    <h6 class="text-muted mb-2">Informasi Peminjam:</h6>
                                    <h5 class="fw-bold text-dark mb-1" id="mAnggotaNama">-</h5>
                                    <p class="text-muted mb-3 font-monospace" id="mAnggotaNo">-</p>

                                    <h6 class="text-muted mb-1">Judul Buku:</h6>
                                    <strong class="text-dark d-block mb-1" id="mBukuJudul">-</strong>
                                    <small class="text-muted font-monospace" id="mBukuKode">-</small>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <div class="mb-2 d-flex justify-content-between">
                                        <span class="text-muted">Tgl. Pinjam:</span>
                                        <strong id="mTglPinjam">-</strong>
                                    </div>
                                    <div class="mb-2 d-flex justify-content-between">
                                        <span class="text-muted">Jatuh Tempo:</span>
                                        <strong id="mJatuhTempo" class="text-danger">-</strong>
                                    </div>
                                    <div class="mb-2 d-flex justify-content-between">
                                        <span class="text-muted">Hari Terlambat:</span>
                                        <span class="badge bg-label-danger fs-6" id="mHariTerlambat">0 Hari</span>
                                    </div>

                                    <!-- Alert Denda Box -->
                                    <div id="boxDendaAlert" class="alert alert-danger p-2 mt-3" style="display: none;">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="small fw-bold">Nominal Denda:</span>
                                            <strong class="fs-5" id="mNominalDenda">Rp 0</strong>
                                        </div>
                                        <small class="d-block mt-1">Tarif: Rp {{ number_format($pengaturan->nominal_per_hari, 0, ',', '.') }} / hari</small>
                                    </div>
                                </div>

                                <div class="col-12"><hr class="my-1"></div>

                                <div class="col-md-6 mb-3 mt-2">
                                    <label class="form-label fw-semibold">Tanggal Dikembalikan <span class="text-danger">*</span></label>
                                    <input type="date" name="tgl_kembali" id="modalTglKembali" class="form-control" value="{{ date('Y-m-d') }}" required>
                                </div>

                                <div class="col-md-6 mb-3 mt-2">
                                    <label class="form-label fw-semibold">Kondisi Fisik Buku <span class="text-danger">*</span></label>
                                    <select name="kondisi_kembali" id="modalKondisiKembali" class="form-select" required>
                                        <option value="baik" selected>Baik (Normal)</option>
                                        <option value="rusak">Rusak (Halaman Robek / Cacat)</option>
                                        <option value="hilang">Hilang (Tidak Dapat Dikembalikan)</option>
                                    </select>
                                </div>

                                <div class="col-12 mb-3">
                                    <label class="form-label">Catatan Pengembalian (Opsional)</label>
                                    <input type="text" name="catatan" class="form-control" placeholder="Contoh: Pengembalian tepat waktu / kondisi baik">
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-success"><i class="bx bx-check me-1"></i> Proses Pengembalian</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Tabel Riwayat Pengembalian Terakhir -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bx bx-history me-2 text-primary"></i>Riwayat Pengembalian Terakhir</h5>
                <span class="badge bg-label-primary">Transaksi Selesai</span>
            </div>
            <div class="table-responsive text-nowrap">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th width="40">No</th>
                            <th>Peminjam (Anggota)</th>
                            <th>Buku</th>
                            <th>Tgl Pinjam & Kembali</th>
                            <th>Kondisi Buku</th>
                            <th>Denda Keterlambatan</th>
                            <th>Status Denda</th>
                        </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
                        @forelse($riwayatPengembalian as $index => $rp)
                        <tr>
                            <td>{{ $riwayatPengembalian->firstItem() + $index }}</td>
                            <td>
                                <strong class="text-dark d-block">{{ $rp->anggota->nama ?? '-' }}</strong>
                                <small class="text-muted font-monospace">{{ $rp->anggota->no_anggota ?? '-' }}</small>
                            </td>
                            <td>
                                <strong class="text-dark d-block text-wrap" style="max-width: 250px;">{{ $rp->eksemplar->buku->judul ?? '-' }}</strong>
                                <small class="text-muted font-monospace"><i class="bx bx-barcode me-1"></i>{{ $rp->eksemplar->kode_eksemplar ?? '-' }}</small>
                            </td>
                            <td>
                                <small class="d-block">Pinjam: {{ $rp->tgl_pinjam->format('d/m/Y') }}</small>
                                <strong class="text-success"><i class="bx bx-check me-1"></i>Kembali: {{ $rp->tgl_kembali ? $rp->tgl_kembali->format('d/m/Y') : '-' }}</strong>
                            </td>
                            <td>
                                @if($rp->kondisi_kembali === 'baik')
                                    <span class="badge bg-label-success">Baik</span>
                                @elseif($rp->kondisi_kembali === 'rusak')
                                    <span class="badge bg-label-warning">Rusak</span>
                                @else
                                    <span class="badge bg-label-danger">Hilang</span>
                                @endif
                            </td>
                            <td>
                                @if($rp->denda && $rp->denda->nominal > 0)
                                    <strong class="text-danger">Rp {{ number_format($rp->denda->nominal, 0, ',', '.') }}</strong>
                                @else
                                    <span class="text-success">Rp 0 (Tepat Waktu)</span>
                                @endif
                            </td>
                            <td>
                                @if($rp->denda)
                                    @if($rp->denda->status_bayar === 'Lunas')
                                        <span class="badge bg-success"><i class="bx bx-check-double me-1"></i> Lunas</span>
                                    @else
                                        <a href="{{ route('denda.index') }}" class="badge bg-danger">
                                            <i class="bx bx-time me-1"></i> Belum Lunas
                                        </a>
                                    @endif
                                @else
                                    <span class="badge bg-label-secondary">-</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">
                                <i class="bx bx-archive-in fs-3 d-block mb-1"></i>
                                Belum ada riwayat pengembalian buku.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer d-flex justify-content-between align-items-center py-2">
                <span class="text-muted small">Menampilkan {{ $riwayatPengembalian->firstItem() ?? 0 }} - {{ $riwayatPengembalian->lastItem() ?? 0 }} dari {{ $riwayatPengembalian->total() }} riwayat</span>
                <div>{{ $riwayatPengembalian->links('pagination::bootstrap-5') }}</div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let activeLoansData = [];
    let isSearchingLoans = false;
    let rfidReturnTimer = null;
    let scanBukuTimer = null;

    function searchActiveLoans(type) {
        if (isSearchingLoans) return;

        let reqData = {};
        if (type === 'buku') {
            const kodeBuku = $('#scanBuku').val().trim();
            if (!kodeBuku) return;
            reqData.kode_buku = kodeBuku;
            $('#scanAnggota').val('');
        } else {
            const kodeAnggota = $('#scanAnggota').val().trim();
            if (!kodeAnggota) return;
            reqData.kode_anggota = kodeAnggota;
            $('#scanBuku').val('');
        }

        isSearchingLoans = true;

        $.ajax({
            url: "{{ route('pengembalian.check_loan') }}",
            method: 'GET',
            data: reqData,
            success: function(res) {
                isSearchingLoans = false;
                if (res.success && res.data.length > 0) {
                    activeLoansData = res.data;
                    playBeep(true);
                    renderLoansList(res.data);
                    
                    // If only 1 loan found (direct scan book), open modal immediately
                    if (res.data.length === 1 && type === 'buku') {
                        openReturnModal(0);
                    }
                }
            },
            error: function(xhr) {
                isSearchingLoans = false;
                playBeep(false);
                $('#boxPeminjamanAktif').slideUp();
                Swal.fire({
                    icon: 'error',
                    title: 'Tidak Ditemukan',
                    text: xhr.responseJSON?.message || 'Data peminjaman aktif tidak ditemukan.',
                    confirmButtonColor: '#696cff'
                });
            }
        });
    }

    function renderLoansList(loans) {
        let html = '';
        loans.forEach((loan, idx) => {
            const isLate = loan.hari_terlambat > 0;
            html += `
                <div class="col-md-6">
                    <div class="card border ${isLate ? 'border-danger' : 'border-primary'} shadow-none h-100">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <h6 class="fw-bold text-dark mb-0">${loan.buku.judul}</h6>
                                    <small class="text-muted font-monospace"><i class="bx bx-barcode me-1"></i>Eks: ${loan.buku.kode_eksemplar}</small>
                                </div>
                                ${isLate 
                                    ? `<span class="badge bg-danger">Terlambat ${loan.hari_terlambat} Hari</span>` 
                                    : `<span class="badge bg-success">Tepat Waktu</span>`}
                            </div>
                            <div class="small mb-2">
                                Peminjam: <strong>${loan.anggota.nama}</strong> (${loan.anggota.no_anggota})<br>
                                Tgl Pinjam: ${loan.tgl_pinjam} • Jatuh Tempo: <strong class="${isLate ? 'text-danger' : ''}">${loan.tgl_jatuh_tempo}</strong>
                            </div>
                            ${isLate ? `
                                <div class="alert alert-danger p-2 mb-2 small d-flex justify-content-between align-items-center">
                                    <span>Estimasi Denda:</span>
                                    <strong class="fs-6">${loan.nominal_denda_formatted}</strong>
                                </div>
                            ` : ''}
                            <button type="button" class="btn btn-sm ${isLate ? 'btn-danger' : 'btn-success'} w-100" onclick="openReturnModal(${idx})">
                                <i class="bx bx-check-circle me-1"></i> Proses Pengembalian Buku Ini
                            </button>
                        </div>
                    </div>
                </div>
            `;
        });
        $('#listLoansContainer').html(html);
        $('#boxPeminjamanAktif').slideDown();
    }

    function openReturnModal(index) {
        const loan = activeLoansData[index];
        if (!loan) return;

        $('#modalIdPeminjaman').val(loan.id);
        $('#mAnggotaNama').text(loan.anggota.nama);
        $('#mAnggotaNo').text(loan.anggota.no_anggota + ' (' + (loan.anggota.kelas_jabatan || '-') + ')');
        $('#mBukuJudul').text(loan.buku.judul);
        $('#mBukuKode').text(loan.buku.kode_eksemplar);
        $('#mTglPinjam').text(loan.tgl_pinjam);
        $('#mJatuhTempo').text(loan.tgl_jatuh_tempo);
        $('#mHariTerlambat').text(loan.hari_terlambat + ' Hari');

        if (loan.hari_terlambat > 0) {
            $('#mNominalDenda').text(loan.nominal_denda_formatted);
            $('#boxDendaAlert').show();
        } else {
            $('#boxDendaAlert').hide();
        }

        $('#modalProsesKembali').modal('show');
    }

    // Event listeners Buku (Barcode)
    $('#btnCariByBuku').click(function() {
        clearTimeout(scanBukuTimer);
        searchActiveLoans('buku');
    });

    $('#scanBuku').on('keydown', function(e) {
        if (e.which === 13 || e.key === 'Enter') {
            e.preventDefault();
            clearTimeout(scanBukuTimer);
            searchActiveLoans('buku');
        }
    });

    // Auto search instan saat barcode buku di-scan (tanpa perlu tekan Enter)
    $('#scanBuku').on('input', function() {
        clearTimeout(scanBukuTimer);
        const val = $(this).val().trim();
        if (val.length >= 3) {
            scanBukuTimer = setTimeout(function() {
                searchActiveLoans('buku');
            }, 180);
        }
    });

    // Event listeners Anggota (RFID)
    $('#btnCariByAnggota').click(function() {
        clearTimeout(rfidReturnTimer);
        searchActiveLoans('anggota');
    });

    $('#scanAnggota').on('keydown', function(e) {
        if (e.which === 13 || e.key === 'Enter') {
            e.preventDefault();
            clearTimeout(rfidReturnTimer);
            searchActiveLoans('anggota');
        }
    });

    // Auto search instan saat kartu RFID di-tap (tanpa perlu tekan Enter)
    $('#scanAnggota').on('input', function() {
        clearTimeout(rfidReturnTimer);
        const val = $(this).val().trim();
        if (val.length >= 3) {
            rfidReturnTimer = setTimeout(function() {
                searchActiveLoans('anggota');
            }, 180);
        }
    });

    // SweetAlert2 submit confirm for return
    $('#formSubmitPengembalian').on('submit', function(e) {
        e.preventDefault();
        const form = this;

        Swal.fire({
            title: 'Konfirmasi Pengembalian',
            text: 'Apakah Anda yakin ingin memproses pengembalian buku ini dan memperbarui stok fisik?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: '<i class="bx bx-check me-1"></i> Ya, Proses Pengembalian',
            cancelButtonText: 'Batal',
            customClass: {
                confirmButton: 'btn btn-success me-2',
                cancelButton: 'btn btn-secondary'
            },
            buttonsStyling: false
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    });
</script>
@endpush
