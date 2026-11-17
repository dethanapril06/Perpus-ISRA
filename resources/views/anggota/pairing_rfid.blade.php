@extends('layouts.app')

@section('title', 'Meja Pairing / Aktivasi Kartu RFID Anggota')

@push('styles')
<style>
    .empty-state-card {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
    }
    .student-card {
        transition: all 0.15s ease-in-out;
    }
    .student-card:hover {
        border-color: #696cff !important;
        background-color: #f7f8ff;
    }
</style>
@endpush

@section('content')
<div class="row">
    <div class="col-12">
        <!-- Header Page -->
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
            <div>
                <h4 class="fw-bold mb-1"><span class="text-muted fw-light">Data Anggota /</span> Meja Pairing Kartu RFID</h4>
                <p class="text-muted mb-0 small">Pilih siswa yang sedang menghadap lalu tempelkan kartu RFID fisiknya untuk menautkan secara otomatis.</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('anggota.index') }}" class="btn btn-secondary">
                    <i class="bx bx-arrow-back me-1"></i> Kembali ke Data Anggota
                </a>
            </div>
        </div>

        <!-- Ringkasan Statistik Kartu -->
        <div class="row g-2 mb-3">
            <div class="col-md-4">
                <div class="card bg-label-primary border-0 shadow-none">
                    <div class="card-body p-2 px-3 d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted d-block" style="font-size: 0.75rem;">Total Anggota</span>
                            <h5 class="fw-bold mb-0 text-primary" id="txtStatTotal">{{ $totalAnggota }}</h5>
                        </div>
                        <div class="avatar avatar-sm bg-primary text-white d-flex align-items-center justify-content-center rounded">
                            <i class="bx bx-user fs-5"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-label-warning border-0 shadow-none">
                    <div class="card-body p-2 px-3 d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted d-block" style="font-size: 0.75rem;">Belum Ber-RFID</span>
                            <h5 class="fw-bold mb-0 text-warning" id="txtStatUnpaired">{{ $totalWithoutRfid }}</h5>
                        </div>
                        <div class="avatar avatar-sm bg-warning text-white d-flex align-items-center justify-content-center rounded">
                            <i class="bx bx-broadcast fs-5"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-label-success border-0 shadow-none">
                    <div class="card-body p-2 px-3 d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted d-block" style="font-size: 0.75rem;">Sudah Ber-RFID</span>
                            <h5 class="fw-bold mb-0 text-success" id="txtStatPaired">{{ $totalWithRfid }}</h5>
                        </div>
                        <div class="avatar avatar-sm bg-success text-white d-flex align-items-center justify-content-center rounded">
                            <i class="bx bx-check-shield fs-5"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Meja Kerja Pairing (2 Kolom) -->
        <div class="row">
            <!-- Kolom 1: Pencarian & Daftar Siswa -->
            <div class="col-lg-6 mb-4">
                <div class="card h-100 shadow-sm border">
                    <div class="card-header bg-light py-2 px-3 border-bottom">
                        <span class="fw-semibold text-dark small"><i class="bx bx-list-check me-1 text-primary"></i> 1. Cari & Pilih Siswa</span>
                    </div>
                    <div class="card-body p-3">
                        <!-- Filter Bar -->
                        <div class="row g-2 mb-2">
                            <div class="col-md-6">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text"><i class="bx bx-search"></i></span>
                                    <input type="text" id="filterSearch" class="form-control" placeholder="Cari Nama / NIS..." autocomplete="off">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <select id="filterKelas" class="form-select form-select-sm">
                                    <option value="">Semua Kelas</option>
                                    @foreach($kelasList as $k)
                                        <option value="{{ $k }}">{{ $k }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select id="filterStatusRfid" class="form-select form-select-sm">
                                    <option value="unpaired" selected>Belum Ber-RFID</option>
                                    <option value="">Semua Status</option>
                                    <option value="paired">Sudah Ber-RFID</option>
                                </select>
                            </div>
                        </div>

                        <!-- List Siswa (Scrollable) -->
                        <div id="listAnggotaContainer" style="max-height: 420px; overflow-y: auto;" class="pe-1">
                            <div class="text-center py-4 text-muted small" id="loadingList">
                                <span class="spinner-border spinner-border-sm me-1" role="status"></span> Memuat data siswa...
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Kolom 2: Area Tap Kartu RFID -->
            <div class="col-lg-6 mb-4">
                <!-- State Belum Pilih Siswa -->
                <div id="panelEmptyState" class="card h-100 shadow-sm border-0 border-dashed text-center p-4 empty-state-card">
                    <div class="rounded-circle bg-label-primary mx-auto mb-2 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                        <i class="bx bx-user-check fs-4 text-primary"></i>
                    </div>
                    <h6 class="fw-bold text-dark mb-1" style="font-size: 0.95rem;">Belum Ada Siswa yang Dipilih</h6>
                    <p class="text-muted small mb-0" style="font-size: 0.78rem; max-width: 290px;">
                        Klik salah satu siswa pada daftar di sebelah kiri untuk mulai menautkan kartu fisik RFID.
                    </p>
                </div>

                <!-- State Siswa Terpilih (Aktif Tapping) -->
                <div id="panelActivePairing" class="card h-100 shadow-sm border" style="display: none;">
                    <div class="card-header bg-label-primary py-2 px-3 d-flex justify-content-between align-items-center">
                        <span class="fw-semibold text-primary small"><i class="bx bx-broadcast me-1"></i> Area Tap Kartu RFID</span>
                        <span class="badge bg-primary" style="font-size: 0.72rem;">Standby Reader</span>
                    </div>

                    <div class="card-body p-3">
                        <!-- Info Siswa yang Terpilih (Compact & Clean) -->
                        <div class="p-2 px-3 bg-light rounded border mb-3">
                            <div class="d-flex align-items-center gap-2">
                                <div id="boxAvatarStudent" class="rounded-circle d-flex align-items-center justify-content-center bg-label-primary text-primary fw-bold" style="width: 38px; height: 38px; min-width: 38px; min-height: 38px; flex-shrink: 0; font-size: 0.95rem;">
                                    A
                                </div>
                                <div class="flex-grow-1" style="min-width: 0;">
                                    <h6 class="mb-0 fw-bold text-dark text-truncate" id="txtNamaSiswa" style="font-size: 0.92rem;">-</h6>
                                    <div class="text-muted small mt-1" style="font-size: 0.78rem;">
                                        NIS: <strong id="txtIdentitasSiswa" class="text-secondary">-</strong> • 
                                        Kelas: <strong id="txtKelasSiswa" class="text-secondary">-</strong> • 
                                        <span class="font-monospace text-muted" id="txtNoAnggotaSiswa">-</span>
                                    </div>
                                </div>
                                <div class="text-end flex-shrink-0" id="badgeStatusKartu">
                                </div>
                            </div>
                        </div>

                        <!-- Scanner Area Box (Compact) -->
                        <div class="card border border-primary bg-white p-3 mb-3 shadow-none text-center rounded">
                            <div class="d-flex align-items-center justify-content-center gap-2 mb-1">
                                <i class="bx bx-broadcast fs-5 text-primary"></i>
                                <h6 class="fw-bold text-primary mb-0" style="font-size: 0.92rem;">Tempelkan Kartu RFID Siswa</h6>
                            </div>
                            <p class="text-muted small mb-2" style="font-size: 0.78rem;">Dekatkan kartu RFID ke scanner USB, sistem akan menyimpan secara instan.</p>

                            <!-- Form Input RFID Tap -->
                            <form id="formTapRfid" onsubmit="return false;" class="px-md-2">
                                <div class="position-relative">
                                    <div class="input-group border border-primary rounded shadow-none">
                                        <span class="input-group-text bg-primary text-white border-0 py-2"><i class="bx bx-broadcast"></i></span>
                                        <input type="text" id="inputRfidUid" 
                                               class="form-control font-monospace text-center py-2 fw-bold text-primary border-0" 
                                               style="font-size: 1.05rem; letter-spacing: 1px;" 
                                               placeholder="Scan / Tap kartu di sini..." 
                                               autofocus autocomplete="off">
                                    </div>
                                    <div id="loadingPairingIndicator" class="mt-2 text-primary fw-semibold small" style="display: none; font-size: 0.78rem;">
                                        <span class="spinner-border spinner-border-sm me-1" role="status"></span> Menyimpan kartu RFID...
                                    </div>
                                </div>
                            </form>
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <button type="button" class="btn btn-outline-danger btn-sm" id="btnLepasRfid" style="display: none;">
                                <i class="bx bx-unlink me-1"></i> Lepas Kartu
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm ms-auto" id="btnGantiSiswa">
                                <i class="bx bx-user me-1"></i> Pilih Siswa Lain
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let selectedStudent = null;
    let studentsData = [];
    let rfidScanTimer = null;
    let isSaving = false;

    // 1. Load Data Siswa via AJAX
    function loadStudents() {
        const search = $('#filterSearch').val().trim();
        const kelas = $('#filterKelas').val();
        const statusRfid = $('#filterStatusRfid').val();

        $('#loadingList').show();

        $.ajax({
            url: "{{ route('anggota.search_pairing') }}",
            method: 'GET',
            data: {
                search: search,
                kelas: kelas,
                status_rfid: statusRfid
            },
            success: function(res) {
                if (res.success) {
                    studentsData = res.data;
                    renderStudentsList(res.data);
                }
            },
            error: function() {
                $('#listAnggotaContainer').html('<div class="text-center py-4 text-danger small">Gagal memuat data siswa.</div>');
            }
        });
    }

    // 2. Render List Siswa
    function renderStudentsList(list) {
        const container = $('#listAnggotaContainer');
        container.empty();

        if (list.length === 0) {
            container.html(`
                <div class="text-center py-5 text-muted">
                    <i class="bx bx-user-x fs-1 d-block mb-1 text-secondary"></i>
                    <span>Tidak ada data siswa yang cocok dengan filter.</span>
                </div>
            `);
            return;
        }

        list.forEach(student => {
            const isSelected = selectedStudent && selectedStudent.id === student.id;
            const borderClass = isSelected ? 'border-primary bg-label-primary' : 'border';
            const badgeRfid = student.is_paired 
                ? `<span class="badge bg-label-success font-monospace px-2 py-1" style="font-size: 0.72rem;" title="UID: ${student.rfid_uid}"><i class="bx bx-check me-1"></i>${student.rfid_uid}</span>`
                : `<span class="badge bg-label-secondary px-2 py-1" style="font-size: 0.72rem;"><i class="bx bx-broadcast me-1"></i>Belum RFID</span>`;

            const itemHtml = `
                <div class="card student-card ${borderClass} mb-1 cursor-pointer shadow-none" 
                     data-id="${student.id}" 
                     style="cursor: pointer;">
                    <div class="card-body p-2 d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-2" style="min-width: 0;">
                            <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold ${student.is_paired ? 'bg-success' : 'bg-primary'}"
                                 style="width: 32px; height: 32px; min-width: 32px; min-height: 32px; flex-shrink: 0; font-size: 0.85rem;">
                                ${student.nama.charAt(0).toUpperCase()}
                            </div>
                            <div style="min-width: 0;">
                                <h6 class="mb-0 fw-semibold text-dark text-truncate" style="font-size: 0.85rem;" title="${student.nama}">${student.nama}</h6>
                                <div class="text-muted text-truncate" style="font-size: 0.75rem;">NIS: ${student.identitas || '-'} • Kls: ${student.kelas_jabatan || '-'}</div>
                            </div>
                        </div>
                        <div class="text-end flex-shrink-0 ms-2">
                            ${badgeRfid}
                        </div>
                    </div>
                </div>
            `;
            container.append(itemHtml);
        });

        // Event listener klik kartu murid
        $('.student-card').click(function() {
            const id = $(this).data('id');
            const student = studentsData.find(s => s.id === id);
            if (student) {
                selectStudent(student);
            }
        });
    }

    // 3. Pilih Murid
    function selectStudent(student) {
        selectedStudent = student;

        // Update active highlight di list
        $('.student-card').removeClass('border-primary bg-label-primary').addClass('border');
        $(`.student-card[data-id="${student.id}"]`).removeClass('border').addClass('border-primary bg-label-primary');

        // Isi panel kanan
        $('#txtNamaSiswa').text(student.nama);
        $('#txtIdentitasSiswa').text(student.identitas || '-');
        $('#txtKelasSiswa').text(student.kelas_jabatan || '-');
        $('#txtNoAnggotaSiswa').text(student.no_anggota);
        
        if (student.foto_url) {
            $('#boxAvatarStudent').html(`<img src="${student.foto_url}" alt="${student.nama}" class="rounded-circle" style="width: 38px; height: 38px; object-fit: cover;">`);
        } else {
            $('#boxAvatarStudent').html(`${student.nama.charAt(0).toUpperCase()}`);
        }

        if (student.is_paired) {
            $('#badgeStatusKartu').html(`<span class="badge bg-label-success font-monospace px-2 py-1" style="font-size: 0.75rem;"><i class="bx bx-check-shield me-1"></i>${student.rfid_uid}</span>`);
            $('#inputRfidUid').val(student.rfid_uid);
            $('#btnLepasRfid').show();
        } else {
            $('#badgeStatusKartu').html('<span class="badge bg-label-warning px-2 py-1" style="font-size: 0.75rem;"><i class="bx bx-time me-1"></i>Belum Ber-RFID</span>');
            $('#inputRfidUid').val('');
            $('#btnLepasRfid').hide();
        }

        // Sembunyikan state kosong & tampilkan panel aktif
        $('#panelEmptyState').css('display', 'none');
        $('#panelActivePairing').fadeIn(200);

        // Kursor langsung fokus ke kotak tap RFID
        setTimeout(function() {
            $('#inputRfidUid').focus().select();
        }, 150);
    }

    // 4. Reset & Ganti Murid
    $('#btnGantiSiswa').click(function() {
        selectedStudent = null;
        $('.student-card').removeClass('border-primary bg-label-primary').addClass('border');
        $('#panelActivePairing').hide();
        $('#panelEmptyState').css('display', 'flex');
        $('#filterSearch').focus();
    });

    // Audio Feedback Beep
    function playBeep(isSuccess = true) {
        try {
            const AudioCtx = window.AudioContext || window.webkitAudioContext;
            if (!AudioCtx) return;
            const audioCtx = new AudioCtx();
            const osc = audioCtx.createOscillator();
            const gain = audioCtx.createGain();
            osc.connect(gain);
            gain.connect(audioCtx.destination);
            if (isSuccess) {
                osc.frequency.setValueAtTime(880, audioCtx.currentTime);
                gain.gain.setValueAtTime(0.15, audioCtx.currentTime);
                gain.gain.exponentialRampToValueAtTime(0.01, audioCtx.currentTime + 0.15);
                osc.start();
                osc.stop(audioCtx.currentTime + 0.15);
            } else {
                osc.frequency.setValueAtTime(220, audioCtx.currentTime);
                gain.gain.setValueAtTime(0.2, audioCtx.currentTime);
                gain.gain.exponentialRampToValueAtTime(0.01, audioCtx.currentTime + 0.3);
                osc.start();
                osc.stop(audioCtx.currentTime + 0.3);
            }
        } catch (e) {}
    }

    // 5. Simpan Tautan Kartu RFID ke Murid Terpilih
    function saveRfidPairing() {
        if (!selectedStudent) return;
        const rfidUid = $('#inputRfidUid').val().trim();
        if (!rfidUid || isSaving) return;

        isSaving = true;
        $('#loadingPairingIndicator').show();
        $('#inputRfidUid').prop('readonly', true);

        $.ajax({
            url: "{{ route('anggota.save_rfid') }}",
            method: 'POST',
            data: {
                _token: "{{ csrf_token() }}",
                id: selectedStudent.id,
                rfid_uid: rfidUid
            },
            success: function(res) {
                isSaving = false;
                $('#loadingPairingIndicator').hide();
                $('#inputRfidUid').prop('readonly', false);

                if (res.success) {
                    playBeep(true);
                    
                    // Notifikasi sukses
                    Swal.fire({
                        icon: 'success',
                        title: 'Pairing Berhasil!',
                        text: `Kartu RFID [${rfidUid}] berhasil ditautkan ke ${selectedStudent.nama}!`,
                        timer: 2000,
                        showConfirmButton: false,
                        position: 'top-end',
                        toast: true
                    });

                    // Update data lokal
                    selectedStudent.is_paired = true;
                    selectedStudent.rfid_uid = rfidUid;
                    $('#badgeStatusKartu').html(`<span class="badge bg-success font-monospace fs-6"><i class="bx bx-check-shield me-1"></i>${rfidUid}</span>`);
                    $('#btnLepasRfid').show();

                    // Update statistik
                    let unp = parseInt($('#txtStatUnpaired').text()) || 0;
                    let p = parseInt($('#txtStatPaired').text()) || 0;
                    $('#txtStatUnpaired').text(Math.max(0, unp - 1));
                    $('#txtStatPaired').text(p + 1);

                    // Re-render list
                    loadStudents();

                    // Otomatis bersihkan & siap untuk siswa berikutnya
                    setTimeout(function() {
                        $('#btnGantiSiswa').click();
                    }, 1200);
                }
            },
            error: function(xhr) {
                isSaving = false;
                $('#loadingPairingIndicator').hide();
                $('#inputRfidUid').prop('readonly', false).focus().select();
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

    // 6. Lepas Tautan Kartu
    $('#btnLepasRfid').click(function() {
        if (!selectedStudent) return;

        Swal.fire({
            title: 'Lepas Kartu RFID?',
            text: `Apakah Anda yakin ingin melepas tautan kartu RFID dari ${selectedStudent.nama}?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, Lepaskan',
            cancelButtonText: 'Batal',
            customClass: {
                confirmButton: 'btn btn-danger me-2',
                cancelButton: 'btn btn-secondary'
            },
            buttonsStyling: false
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "{{ route('anggota.remove_rfid') }}",
                    method: 'POST',
                    data: {
                        _token: "{{ csrf_token() }}",
                        id: selectedStudent.id
                    },
                    success: function(res) {
                        if (res.success) {
                            playBeep(true);
                            selectedStudent.is_paired = false;
                            selectedStudent.rfid_uid = null;
                            $('#badgeStatusKartu').html('<span class="badge bg-warning"><i class="bx bx-time me-1"></i>Belum Ada RFID</span>');
                            $('#inputRfidUid').val('').focus();
                            $('#btnLepasRfid').hide();

                            // Update statistik
                            let unp = parseInt($('#txtStatUnpaired').text()) || 0;
                            let p = parseInt($('#txtStatPaired').text()) || 0;
                            $('#txtStatUnpaired').text(unp + 1);
                            $('#txtStatPaired').text(Math.max(0, p - 1));

                            loadStudents();

                            Swal.fire({
                                icon: 'info',
                                title: 'Kartu Dilepas',
                                text: 'Tautan kartu RFID berhasil dilepaskan.',
                                timer: 1500,
                                showConfirmButton: false,
                                position: 'top-end',
                                toast: true
                            });
                        }
                    }
                });
            }
        });
    });

    // Event listener tap RFID otomatis
    $('#inputRfidUid').on('keydown', function(e) {
        if (e.which === 13 || e.key === 'Enter') {
            e.preventDefault();
            clearTimeout(rfidScanTimer);
            saveRfidPairing();
        }
    });

    $('#inputRfidUid').on('input', function() {
        clearTimeout(rfidScanTimer);
        const val = $(this).val().trim();
        if (val.length >= 3) {
            rfidScanTimer = setTimeout(function() {
                saveRfidPairing();
            }, 180);
        }
    });

    // Event filter list
    let searchFilterTimer = null;
    $('#filterSearch').on('input', function() {
        clearTimeout(searchFilterTimer);
        searchFilterTimer = setTimeout(loadStudents, 250);
    });

    $('#filterKelas, #filterStatusRfid').on('change', loadStudents);

    // Initial load
    $(document).ready(function() {
        loadStudents();
    });
</script>
@endpush
