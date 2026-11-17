@extends('layouts.app')

@section('title', 'Master Data Anggota')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
            <h4 class="fw-bold py-3 mb-0"><span class="text-muted fw-light">Master Data /</span> Data Anggota Perpustakaan</h4>
            <div class="d-flex gap-2">
                <a href="{{ route('anggota.pairing_rfid') }}" class="btn btn-info text-white">
                    <i class="bx bx-broadcast me-1"></i> Aktivasi / Pairing RFID
                </a>
                <button type="button" class="btn btn-outline-success" data-bs-toggle="modal" data-bs-target="#modalImportExcel">
                    <i class="bx bx-upload me-1"></i> Impor Excel (.xlsx)
                </button>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambahAnggota">
                    <i class="bx bx-user-plus me-1"></i> Tambah Anggota
                </button>
            </div>
        </div>

        <!-- Filter -->
        <div class="card mb-4">
            <div class="card-body">
                <form action="{{ route('anggota.index') }}" method="GET" class="row g-3">
                    <div class="col-md-7">
                        <div class="input-group">
                            <span class="input-group-text"><i class="bx bx-search"></i></span>
                            <input type="text" name="search" class="form-control" placeholder="Cari No. Anggota, Nama, NIS/NIP, Kelas/Jabatan, No. HP..." value="{{ request('search') }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <select name="status" class="form-select">
                            <option value="">-- Semua Status --</option>
                            <option value="aktif" {{ request('status') === 'aktif' ? 'selected' : '' }}>Aktif</option>
                            <option value="nonaktif" {{ request('status') === 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex gap-2">
                        <button type="submit" class="btn btn-primary w-100"><i class="bx bx-filter-alt"></i> Filter</button>
                        @if(request()->hasAny(['search', 'status']))
                            <a href="{{ route('anggota.index') }}" class="btn btn-outline-secondary"><i class="bx bx-reset"></i></a>
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
                            <th width="50">No</th>
                            <th>No. Anggota / RFID</th>
                            <th>Nama & Identitas</th>
                            <th>Kelas / Jabatan</th>
                            {{-- <th>Kontak & Alamat</th> --}}
                            <th>Status</th>
                            <th width="120" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
                        @forelse($anggota as $index => $a)
                        <tr>
                            <td>{{ $anggota->firstItem() + $index }}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    @if($a->foto)
                                        <img src="{{ asset('storage/' . $a->foto) }}" alt="{{ $a->nama }}" class="rounded-circle me-2" style="width: 36px; height: 36px; object-fit: cover;">
                                    @else
                                        <div class="avatar avatar-sm me-2">
                                            <span class="avatar-initial rounded-circle bg-label-primary">{{ substr($a->nama, 0, 1) }}</span>
                                        </div>
                                    @endif
                                    <div>
                                        <span class="badge bg-label-dark font-monospace d-block mb-1">{{ $a->no_anggota }}</span>
                                        @if($a->rfid_uid)
                                            <span class="badge bg-label-primary font-monospace" title="UID Kartu RFID"><i class="bx bx-broadcast me-1"></i>{{ $a->rfid_uid }}</span>
                                        @else
                                            <span class="badge bg-label-secondary small" title="Belum di-pairing kartu RFID"><i class="bx bx-broadcast me-1"></i>No RFID</span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>
                                <a href="{{ route('anggota.show', $a->id) }}" class="fw-bold text-primary">{{ $a->nama }}</a>
                                <small class="text-muted d-block">{{ $a->identitas ?: 'NIS/NIP: -' }}</small>
                            </td>
                            <td><span class="badge bg-label-info">{{ $a->kelas_jabatan ?: '-' }}</span></td>
                            {{-- <td>
                                <small class="d-block"><i class="bx bx-phone me-1"></i>{{ $a->no_hp ?: '-' }}</small>
                                <small class="text-muted text-truncate d-inline-block" style="max-width: 150px;">{{ $a->alamat ?: '-' }}</small>
                            </td> --}}
                            <td>
                                @if($a->status === 'aktif')
                                    <span class="badge bg-label-success"><i class="bx bx-check-circle me-1"></i> Aktif</span>
                                @else
                                    <span class="badge bg-label-danger"><i class="bx bx-x-circle me-1"></i> Nonaktif</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="d-inline-flex gap-1">
                                    <!-- Pairing RFID Cepat -->
                                    <button type="button" class="btn btn-icon btn-sm btn-outline-info btn-trigger-pairing" 
                                            data-id="{{ $a->id }}" 
                                            data-nama="{{ $a->nama }}" 
                                            data-identitas="{{ $a->identitas ?: $a->no_anggota }}" 
                                            data-rfid="{{ $a->rfid_uid }}"
                                            title="Tautkan / Ganti Kartu RFID">
                                        <i class="bx bx-broadcast"></i>
                                    </button>

                                    <!-- Detail Anggota -->
                                    <a href="{{ route('anggota.show', $a->id) }}" class="btn btn-icon btn-sm btn-outline-primary" title="Lihat Profil & Histori">
                                        <i class="bx bx-show"></i>
                                    </a>

                                    <!-- Edit Modal -->
                                    <button type="button" class="btn btn-icon btn-sm btn-outline-warning" data-bs-toggle="modal" data-bs-target="#modalEditAnggota{{ $a->id }}" title="Edit Data">
                                        <i class="bx bx-edit-alt"></i>
                                    </button>

                                    <!-- Hapus Data -->
                                    <form action="{{ route('anggota.destroy', $a->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" class="btn btn-icon btn-sm btn-outline-danger btn-delete-confirm" data-name="anggota {{ $a->nama }} ({{ $a->no_anggota }})" title="Hapus Anggota">
                                            <i class="bx bx-trash"></i>
                                        </button>
                                    </form>
                                </div>

                                <!-- Modal Edit Anggota -->
                                <div class="modal fade text-start" id="modalEditAnggota{{ $a->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered modal-lg">
                                        <div class="modal-content">
                                            <form action="{{ route('anggota.update', $a->id) }}" method="POST" enctype="multipart/form-data">
                                                @csrf
                                                @method('PUT')
                                                <div class="modal-header">
                                                    <h5 class="modal-title"><i class="bx bx-edit me-1 text-warning"></i> Edit Anggota: {{ $a->nama }}</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="row">
                                                        <div class="col-md-6 mb-3">
                                                            <label class="form-label">No. Anggota (ID Unik) <span class="text-danger">*</span></label>
                                                            <input type="text" name="no_anggota" class="form-control font-monospace" value="{{ old('no_anggota', $a->no_anggota) }}" required>
                                                        </div>
                                                        <div class="col-md-6 mb-3">
                                                            <label class="form-label fw-bold"><i class="bx bx-broadcast text-primary me-1"></i> UID Kartu RFID</label>
                                                            <div class="input-group">
                                                                <span class="input-group-text bg-label-primary"><i class="bx bx-broadcast"></i></span>
                                                                <input type="text" name="rfid_uid" class="form-control font-monospace" placeholder="Tap kartu RFID di reader..." value="{{ old('rfid_uid', $a->rfid_uid) }}" autocomplete="off">
                                                            </div>
                                                            <small class="text-muted">Tap kartu RFID untuk mengaitkan kartu ke anggota ini.</small>
                                                        </div>
                                                        <div class="col-md-6 mb-3">
                                                            <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                                                            <input type="text" name="nama" class="form-control" value="{{ old('nama', $a->nama) }}" required>
                                                        </div>
                                                        <div class="col-md-6 mb-3">
                                                            <label class="form-label">Identitas (NIS / NIP / KTP)</label>
                                                            <input type="text" name="identitas" class="form-control" value="{{ old('identitas', $a->identitas) }}">
                                                        </div>
                                                        <div class="col-md-6 mb-3">
                                                            <label class="form-label">Kelas / Jabatan</label>
                                                            <input type="text" name="kelas_jabatan" class="form-control" value="{{ old('kelas_jabatan', $a->kelas_jabatan) }}">
                                                        </div>
                                                        <div class="col-md-6 mb-3">
                                                            <label class="form-label">No. HP / WhatsApp</label>
                                                            <input type="text" name="no_hp" class="form-control" value="{{ old('no_hp', $a->no_hp) }}">
                                                        </div>
                                                        <div class="col-md-6 mb-3">
                                                            <label class="form-label">Tanggal Terdaftar <span class="text-danger">*</span></label>
                                                            <input type="date" name="tgl_daftar" class="form-control" value="{{ old('tgl_daftar', $a->tgl_daftar ? $a->tgl_daftar->format('Y-m-d') : date('Y-m-d')) }}" required>
                                                        </div>
                                                        <div class="col-md-6 mb-3">
                                                            <label class="form-label">Status Anggota <span class="text-danger">*</span></label>
                                                            <select name="status" class="form-select" required>
                                                                <option value="aktif" {{ $a->status === 'aktif' ? 'selected' : '' }}>Aktif</option>
                                                                <option value="nonaktif" {{ $a->status === 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-md-6 mb-3">
                                                            <label class="form-label">Foto Anggota (Opsional)</label>
                                                            <input type="file" name="foto" class="form-control" accept="image/*">
                                                            @if($a->foto)
                                                                <small class="text-success d-block mt-1"><i class="bx bx-check"></i> Sudah memiliki foto</small>
                                                            @endif
                                                        </div>
                                                        <div class="col-12 mb-3">
                                                            <label class="form-label">Alamat Lengkap</label>
                                                            <textarea name="alamat" class="form-control" rows="2">{{ old('alamat', $a->alamat) }}</textarea>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                                                    <button type="submit" class="btn btn-primary"><i class="bx bx-save me-1"></i> Simpan Perubahan</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">
                                <i class="bx bx-id-card fs-3 d-block mb-1"></i>
                                Belum ada data anggota yang terdaftar.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer d-flex justify-content-between align-items-center py-2">
                <span class="text-muted small">Menampilkan {{ $anggota->firstItem() ?? 0 }} - {{ $anggota->lastItem() ?? 0 }} dari {{ $anggota->total() }} anggota</span>
                <div>{{ $anggota->links('pagination::bootstrap-5') }}</div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah Anggota -->
<div class="modal fade" id="modalTambahAnggota" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <form action="{{ route('anggota.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bx bx-user-plus me-1 text-primary"></i> Pendaftaran Anggota Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">No. Anggota (ID Unik) <span class="text-danger">*</span></label>
                            <input type="text" name="no_anggota" class="form-control font-monospace" value="{{ old('no_anggota', $nextCode) }}" required>
                            <small class="text-muted">Nomor induk keanggotaan perpustakaan.</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold"><i class="bx bx-broadcast text-primary me-1"></i> UID Kartu RFID</label>
                            <div class="input-group">
                                <span class="input-group-text bg-label-primary"><i class="bx bx-broadcast"></i></span>
                                <input type="text" name="rfid_uid" class="form-control font-monospace" placeholder="Tempel kartu RFID di reader..." value="{{ old('rfid_uid') }}" autocomplete="off">
                            </div>
                            <small class="text-muted">Tap kartu RFID anggota pada reader untuk mengisi otomatis.</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" name="nama" class="form-control" placeholder="Contoh: Budi Pratama" value="{{ old('nama') }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Identitas (NIS / NIP / KTP)</label>
                            <input type="text" name="identitas" class="form-control" placeholder="Contoh: 202401019" value="{{ old('identitas') }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Kelas / Jabatan</label>
                            <input type="text" name="kelas_jabatan" class="form-control" placeholder="Contoh: XII IPA 2 / Siswa" value="{{ old('kelas_jabatan') }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">No. HP / WhatsApp</label>
                            <input type="text" name="no_hp" class="form-control" placeholder="08xxxxxxxxxx" value="{{ old('no_hp') }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tanggal Terdaftar <span class="text-danger">*</span></label>
                            <input type="date" name="tgl_daftar" class="form-control" value="{{ old('tgl_daftar', date('Y-m-d')) }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Status Anggota <span class="text-danger">*</span></label>
                            <select name="status" class="form-select" required>
                                <option value="aktif" selected>Aktif</option>
                                <option value="nonaktif">Nonaktif</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Foto Profil Anggota (Opsional)</label>
                            <input type="file" name="foto" class="form-control" accept="image/*">
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label">Alamat Lengkap</label>
                            <textarea name="alamat" class="form-control" rows="2" placeholder="Alamat domisili anggota">{{ old('alamat') }}</textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary"><i class="bx bx-check me-1"></i> Daftarkan Anggota</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Impor Data Anggota dari Excel -->
<div class="modal fade" id="modalImportExcel" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('anggota.import_excel') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header bg-label-success">
                    <h5 class="modal-title fw-bold text-success"><i class="bx bx-file me-1"></i> Impor Data Anggota (Excel .xlsx)</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Banner Unduh Template -->
                    <div class="card bg-label-primary p-3 mb-3 border-0">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <h6 class="fw-bold mb-1 text-primary"><i class="bx bx-spreadsheet me-1"></i> Template Excel Resmi</h6>
                                <small class="text-muted d-block">Gunakan format kolom template agar data terbaca sempurna.</small>
                            </div>
                            <a href="{{ route('anggota.template_excel') }}" class="btn btn-sm btn-primary">
                                <i class="bx bx-download me-1"></i> Unduh (.xlsx)
                            </a>
                        </div>
                    </div>

                    <!-- Pilih File -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">Pilih File Spreadsheet Excel (.xlsx) <span class="text-danger">*</span></label>
                        <input type="file" name="file_excel" class="form-control" accept=".xlsx" required>
                        <small class="text-muted">Hanya file berekstensi <strong>.xlsx</strong> (Maksimal 10 MB).</small>
                    </div>

                    <!-- Pilihan Penanganan Duplikasi -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">Jika No. Anggota / Identitas Sudah Ada:</label>
                        <div class="form-check mb-1">
                            <input class="form-check-input" type="radio" name="mode_duplikat" id="modeSkip" value="skip" checked>
                            <label class="form-check-label" for="modeSkip">
                                <strong>Lewati (Skip)</strong> - Data yang sudah ada tidak akan diubah/ditimpa.
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="mode_duplikat" id="modeUpdate" value="update">
                            <label class="form-check-label" for="modeUpdate">
                                <strong>Perbarui (Update)</strong> - Data lama akan diperbarui dengan data dari file Excel.
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success"><i class="bx bx-upload me-1"></i> Mulai Impor Data</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Pairing RFID Satuan -->
<div class="modal fade" id="modalPairingSingle" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-label-info">
                <h5 class="modal-title fw-bold text-info"><i class="bx bx-broadcast me-1"></i> Tautkan Kartu RFID Siswa</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center p-4">
                <div class="avatar avatar-md bg-label-primary mx-auto mb-2 rounded-circle">
                    <i class="bx bx-user fs-3"></i>
                </div>
                <h5 class="fw-bold text-dark mb-0" id="singleNama">-</h5>
                <small class="text-muted d-block mb-3" id="singleIdentitas">-</small>

                <div class="card border border-primary p-3 mb-2 bg-light">
                    <label class="form-label fw-bold text-primary mb-1">Tempelkan Kartu RFID ke Reader USB</label>
                    <input type="text" id="singleRfidInput" class="form-control font-monospace text-center fs-4 py-2 fw-bold text-primary" placeholder="[ Tempel Kartu di Sini ]" autocomplete="off">
                    <small class="text-muted mt-1 d-block">Kartu otomatis disimpan seketika saat di-tap.</small>
                    <div id="singleLoadingIndicator" class="mt-2 text-primary small fw-semibold" style="display: none;">
                        <span class="spinner-border spinner-border-sm me-1"></span> Menyimpan kartu RFID...
                    </div>
                </div>
            </div>
            <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-outline-danger btn-sm" id="btnSingleRemoveRfid" style="display: none;">
                    <i class="bx bx-unlink me-1"></i> Lepas Kartu
                </button>
                <button type="button" class="btn btn-outline-secondary ms-auto" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let activeSingleMemberId = null;
    let singleScanTimer = null;
    let isSingleSaving = false;

    $('.btn-trigger-pairing').click(function() {
        activeSingleMemberId = $(this).data('id');
        const nama = $(this).data('nama');
        const identitas = $(this).data('identitas');
        const rfid = $(this).data('rfid');

        $('#singleNama').text(nama);
        $('#singleIdentitas').text('ID / NIS: ' + identitas);
        $('#singleRfidInput').val(rfid || '');

        if (rfid) {
            $('#btnSingleRemoveRfid').show();
        } else {
            $('#btnSingleRemoveRfid').hide();
        }

        $('#modalPairingSingle').modal('show');
        setTimeout(function() {
            $('#singleRfidInput').focus().select();
        }, 400);
    });

    function saveSingleRfid() {
        if (!activeSingleMemberId || isSingleSaving) return;
        const uid = $('#singleRfidInput').val().trim();
        if (!uid) return;

        isSingleSaving = true;
        $('#singleLoadingIndicator').show();
        $('#singleRfidInput').prop('readonly', true);

        $.ajax({
            url: "{{ route('anggota.save_rfid') }}",
            method: 'POST',
            data: {
                _token: "{{ csrf_token() }}",
                id: activeSingleMemberId,
                rfid_uid: uid
            },
            success: function(res) {
                isSingleSaving = false;
                $('#singleLoadingIndicator').hide();
                $('#singleRfidInput').prop('readonly', false);

                if (res.success) {
                    playBeep(true);
                    $('#modalPairingSingle').modal('hide');

                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil Ditautkan!',
                        text: `Kartu RFID [${uid}] berhasil ditautkan ke ${res.data.nama}!`,
                        timer: 2000,
                        showConfirmButton: false,
                        position: 'top-end',
                        toast: true
                    });

                    setTimeout(function() {
                        location.reload();
                    }, 1200);
                }
            },
            error: function(xhr) {
                isSingleSaving = false;
                $('#singleLoadingIndicator').hide();
                $('#singleRfidInput').prop('readonly', false).focus().select();
                playBeep(false);

                Swal.fire({
                    icon: 'error',
                    title: 'Gagal Menautkan Kartu',
                    text: xhr.responseJSON?.message || 'Terjadi kesalahan saat menyimpan kartu RFID.',
                    confirmButtonColor: '#696cff'
                });
            }
        });
    }

    // Auto submit RFID di modal single
    $('#singleRfidInput').on('keydown', function(e) {
        if (e.which === 13 || e.key === 'Enter') {
            e.preventDefault();
            clearTimeout(singleScanTimer);
            saveSingleRfid();
        }
    });

    $('#singleRfidInput').on('input', function() {
        clearTimeout(singleScanTimer);
        const val = $(this).val().trim();
        if (val.length >= 3) {
            singleScanTimer = setTimeout(saveSingleRfid, 180);
        }
    });

    // Lepas kartu RFID di modal single
    $('#btnSingleRemoveRfid').click(function() {
        if (!activeSingleMemberId) return;

        $.ajax({
            url: "{{ route('anggota.remove_rfid') }}",
            method: 'POST',
            data: {
                _token: "{{ csrf_token() }}",
                id: activeSingleMemberId
            },
            success: function(res) {
                playBeep(true);
                $('#modalPairingSingle').modal('hide');
                Swal.fire({
                    icon: 'info',
                    title: 'Kartu Dilepas',
                    text: 'Tautan kartu RFID berhasil dilepaskan.',
                    timer: 1500,
                    showConfirmButton: false,
                    position: 'top-end',
                    toast: true
                });
                setTimeout(function() { location.reload(); }, 1000);
            }
        });
    });
</script>
@endpush
