@extends('layouts.app')

@section('title', 'Transaksi Peminjaman Baru (Multi-Buku)')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
            <h4 class="fw-bold py-3 mb-0">
                <span class="text-muted fw-light"><a href="{{ route('peminjaman.index') }}">Peminjaman</a> /</span> 
                Transaksi Baru
            </h4>
            <a href="{{ route('peminjaman.index') }}" class="btn btn-secondary">
                <i class="bx bx-arrow-back me-1"></i> Kembali
            </a>
        </div>

        <form action="{{ route('peminjaman.store') }}" method="POST" id="formPeminjaman">
            @csrf
            <div class="row">
                <!-- STEP 1: SCAN KARTU ANGGOTA -->
                <div class="col-md-5 mb-4">
                    <div class="card h-100 border-primary">
                        <div class="card-header bg-label-primary d-flex justify-content-between align-items-center py-3">
                            <h6 class="mb-0 fw-bold"><span class="badge bg-primary me-2">1</span> Identifikasi Anggota (Tap RFID)</h6>
                            <i class="bx bx-broadcast fs-4 text-primary"></i>
                        </div>
                        <div class="card-body pt-3">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Tap Kartu RFID Anggota <span class="text-danger">*</span></label>
                                <div class="input-group input-group-lg">
                                    <span class="input-group-text bg-primary text-white"><i class="bx bx-broadcast fs-4"></i></span>
                                    <input type="text" name="kode_anggota" id="inputKodeAnggota" class="form-control font-monospace" placeholder="Tempel kartu RFID pada reader..." value="{{ old('kode_anggota') }}" autofocus required autocomplete="off">
                                </div>
                                <small class="text-muted"><i class="bx bx-check-circle text-primary me-1"></i>Otomatis mendeteksi saat kartu RFID ditempelkan ke reader.</small>
                            </div>

                            <!-- Preview Anggota Box -->
                            <div id="boxAnggotaPreview" class="p-3 bg-light rounded border mt-3" style="display: none;">
                                <div class="d-flex align-items-center gap-3">
                                    <img id="imgAnggota" src="" class="rounded-circle border" style="width: 60px; height: 60px; object-fit: cover; display: none;">
                                    <div id="avatarFallback" class="avatar avatar-md">
                                        <span class="avatar-initial rounded-circle bg-primary fs-4" id="txtAvatarInitial">A</span>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1 fw-bold text-dark" id="txtAnggotaNama">-</h6>
                                        <div class="small text-muted mb-1">
                                            ID: <strong id="txtAnggotaIdentitas">-</strong> • Kls: <strong id="txtAnggotaKelas">-</strong>
                                        </div>
                                        <div id="badgeAnggotaStatus"></div>
                                    </div>
                                </div>

                                <hr class="my-2">

                                <div class="small">
                                    <div class="d-flex justify-content-between mb-1">
                                        <span>Pinjaman Aktif Saat Ini:</span>
                                        <strong id="txtActiveLoans" class="text-primary">0 Buku</strong>
                                    </div>
                                    <div class="d-flex justify-content-between mb-1">
                                        <span>Maksimal Kuota Pinjam:</span>
                                        <strong>{{ $pengaturan->maks_buku_pinjam }} Buku</strong>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <span>Sisa Kuota yang Tersedia:</span>
                                        <strong id="txtSisaKuota" class="text-success">{{ $pengaturan->maks_buku_pinjam }} Buku</strong>
                                    </div>
                                </div>
                                <div id="txtDendaWarning" class="mt-2"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- STEP 2: SCANNER BUKU CEPAT (CONTINUOUS SCAN) -->
                <div class="col-md-7 mb-4">
                    <div class="card h-100 border-info">
                        <div class="card-header bg-label-info d-flex justify-content-between align-items-center py-3">
                            <h6 class="mb-0 fw-bold"><span class="badge bg-info me-2">2</span> Scan Barcode Eksemplar Buku</h6>
                            <span class="badge bg-info" id="badgeCartCount">0 Buku di Keranjang</span>
                        </div>
                        <div class="card-body pt-3">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Scan Barcode Buku Fisik <span class="text-danger">*</span></label>
                                <div class="input-group input-group-lg">
                                    <span class="input-group-text bg-info text-white"><i class="bx bx-barcode fs-4"></i></span>
                                    <input type="text" id="inputKodeBuku" class="form-control font-monospace form-control-lg" placeholder="Arahkan scanner ke label barcode buku..." autocomplete="off">
                                </div>
                                <small class="text-muted"><i class="bx bx-check-circle text-info me-1"></i>Setiap buku yang di-scan otomatis masuk ke tabel keranjang di bawah.</small>
                            </div>

                            <!-- KERANJANG DAFTAR BUKU YANG AKAN DIPINJAM -->
                            <div class="table-responsive border rounded" style="max-height: 240px; overflow-y: auto;">
                                <table class="table table-sm table-hover mb-0" id="tableCart">
                                    <thead class="table-light sticky-top">
                                        <tr>
                                            <th>Buku & Eksemplar</th>
                                            <th>Lokasi Rak</th>
                                            <th width="80" class="text-center">Status</th>
                                            <th width="40" class="text-center">Hapus</th>
                                        </tr>
                                    </thead>
                                    <tbody id="cartTableBody">
                                        <tr id="emptyCartRow">
                                            <td colspan="4" class="text-center py-4 text-muted">
                                                <i class="bx bx-cart fs-3 d-block mb-1 text-secondary"></i>
                                                Keranjang masih kosong. Silakan scan barcode buku fisik di atas.
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- STEP 3: DETAIL DURASI, CATATAN & SUBMIT -->
                <div class="col-12 mb-4">
                    <div class="card">
                        <div class="card-header bg-label-secondary py-3">
                            <h6 class="mb-0 fw-bold"><span class="badge bg-secondary me-2">3</span> Detail Masa Pinjam & Simpan Transaksi</h6>
                        </div>
                        <div class="card-body pt-3">
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label fw-semibold">Tanggal Peminjaman <span class="text-danger">*</span></label>
                                    <input type="date" name="tgl_pinjam" id="inputTglPinjam" class="form-control" value="{{ date('Y-m-d') }}" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label fw-semibold">Durasi Peminjaman (Hari) <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="number" name="durasi_hari" id="inputDurasiHari" class="form-control" value="{{ $pengaturan->durasi_pinjam_default }}" min="1" required>
                                        <span class="input-group-text">Hari</span>
                                    </div>
                                    <small class="text-muted">Standar perpustakaan: {{ $pengaturan->durasi_pinjam_default }} hari.</small>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label fw-semibold">Estimasi Tanggal Jatuh Tempo</label>
                                    <input type="text" id="txtEstimasiJatuhTempo" class="form-control fw-bold text-primary bg-light" readonly>
                                </div>
                                <div class="col-12 mb-3">
                                    <label class="form-label">Catatan Transaksi (Opsional)</label>
                                    <input type="text" name="catatan" class="form-control" placeholder="Contoh: Peminjaman untuk tugas kelompok / riset">
                                </div>
                            </div>

                            <div class="mt-3 text-end">
                                <button type="button" class="btn btn-outline-secondary me-2" onclick="window.location.reload();">
                                    <i class="bx bx-reset me-1"></i> Batal / Reset
                                </button>
                                <button type="submit" class="btn btn-primary btn-lg px-4" id="btnSimpanPeminjaman">
                                    <i class="bx bx-check-double me-1"></i> Simpan Peminjaman (<span id="txtBtnCount">0</span> Buku)
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Hidden container for cart ID inputs -->
            <div id="hiddenCartInputs"></div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const maxBukuPinjam = {{ $pengaturan->maks_buku_pinjam }};
    let currentAnggota = null;
    let activeLoansCount = 0;
    let cartItems = []; // Array of Eksemplar Objects

    // Hitung Estimasi Jatuh Tempo
    function updateEstimasiJatuhTempo() {
        const tglPinjam = $('#inputTglPinjam').val();
        const durasi = parseInt($('#inputDurasiHari').val()) || 0;

        if (tglPinjam && durasi > 0) {
            const date = new Date(tglPinjam);
            date.setDate(date.getDate() + durasi);
            const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
            $('#txtEstimasiJatuhTempo').val(date.toLocaleDateString('id-ID', options));
        } else {
            $('#txtEstimasiJatuhTempo').val('-');
        }
    }

    $('#inputTglPinjam, #inputDurasiHari').on('change input', updateEstimasiJatuhTempo);
    updateEstimasiJatuhTempo();

    // 1. AJAX Lookup Anggota
    let isLookingUpAnggota = false;
    let rfidMemberTimer = null;

    function lookupAnggota() {
        if (isLookingUpAnggota) return;
        const code = $('#inputKodeAnggota').val().trim();
        if (!code) return;

        isLookingUpAnggota = true;

        $.ajax({
            url: "{{ route('anggota.lookup') }}",
            method: 'GET',
            data: { code: code },
            success: function(res) {
                isLookingUpAnggota = false;
                if (res.success) {
                    const data = res.data;
                    currentAnggota = data;
                    activeLoansCount = data.active_loans_count || 0;

                    $('#boxAnggotaPreview').slideDown();
                    $('#txtAnggotaNama').text(data.nama);
                    $('#txtAnggotaIdentitas').text(data.identitas || data.no_anggota);
                    $('#txtAnggotaKelas').text(data.kelas_jabatan || '-');
                    $('#txtActiveLoans').text(activeLoansCount + ' Buku');

                    const sisaKuota = Math.max(0, maxBukuPinjam - activeLoansCount);
                    $('#txtSisaKuota').text(sisaKuota + ' Buku');

                    if (data.foto_url) {
                        $('#imgAnggota').attr('src', data.foto_url).show();
                        $('#avatarFallback').hide();
                    } else {
                        $('#imgAnggota').hide();
                        $('#txtAvatarInitial').text(data.nama.charAt(0).toUpperCase());
                        $('#avatarFallback').show();
                    }

                    // Check status aktif
                    if (data.status === 'aktif') {
                        $('#badgeAnggotaStatus').html('<span class="badge bg-label-success">Akun Aktif</span>');
                    } else {
                        $('#badgeAnggotaStatus').html('<span class="badge bg-label-danger">Akun Nonaktif</span>');
                        playBeep(false);
                        Swal.fire({
                            icon: 'error',
                            title: 'Anggota Nonaktif',
                            text: `Anggota '${data.nama}' berstatus nonaktif dan tidak dapat meminjam.`,
                            confirmButtonColor: '#696cff'
                        });
                        return;
                    }

                    // Check denda
                    if (data.has_unpaid_fine) {
                        $('#txtDendaWarning').html('<div class="alert alert-danger p-2 mb-0 small"><i class="bx bx-error me-1"></i> Memiliki denda belum lunas!</div>');
                        playBeep(false);
                        Swal.fire({
                            icon: 'warning',
                            title: 'Tunggakan Denda',
                            text: `Anggota '${data.nama}' memiliki denda keterlambatan yang belum lunas. Harap lunasi terlebih dahulu di menu Denda.`,
                            confirmButtonColor: '#696cff'
                        });
                        return;
                    } else {
                        $('#txtDendaWarning').empty();
                    }

                    // Check if quota full
                    if (activeLoansCount >= maxBukuPinjam) {
                        playBeep(false);
                        Swal.fire({
                            icon: 'warning',
                            title: 'Kuota Pinjam Penuh',
                            text: `Anggota '${data.nama}' telah meminjam ${activeLoansCount} buku (Batas Maksimal: ${maxBukuPinjam} buku).`,
                            confirmButtonColor: '#696cff'
                        });
                        return;
                    }

                    playBeep(true);
                    // Autofocus ke scanner buku
                    $('#inputKodeBuku').focus();
                }
            },
            error: function(xhr) {
                isLookingUpAnggota = false;
                currentAnggota = null;
                $('#boxAnggotaPreview').slideUp();
                playBeep(false);
                Swal.fire({
                    icon: 'error',
                    title: 'Tidak Ditemukan',
                    text: xhr.responseJSON?.message || 'Data anggota tidak ditemukan.',
                    confirmButtonColor: '#696cff'
                });
            }
        });
    }

    $('#btnCariAnggota').click(function() {
        clearTimeout(rfidMemberTimer);
        lookupAnggota();
    });

    $('#inputKodeAnggota').on('keydown', function(e) {
        if (e.which === 13 || e.key === 'Enter') {
            e.preventDefault();
            clearTimeout(rfidMemberTimer);
            lookupAnggota();
        }
    });

    // Auto lookup instan saat kartu RFID ditempelkan (tanpa perlu tekan Enter / klik tombol)
    $('#inputKodeAnggota').on('input', function() {
        clearTimeout(rfidMemberTimer);
        const val = $(this).val().trim();
        if (val.length >= 3) {
            rfidMemberTimer = setTimeout(function() {
                lookupAnggota();
            }, 180);
        }
    });

    // 2. AJAX Lookup & Tambah Buku ke Keranjang (Continuous Scanner Handler)
    let isLookingUpBuku = false;
    let scanBukuTimer = null;

    function lookupAndAddToCart() {
        if (isLookingUpBuku) return;
        const code = $('#inputKodeBuku').val().trim();
        if (!code) return;

        // Validasi identitas anggota sudah di-scan
        if (!currentAnggota) {
            playBeep(false);
            Swal.fire({
                icon: 'warning',
                title: 'Scan Anggota Terlebih Dahulu',
                text: 'Harap scan atau tap kartu RFID anggota (Langkah 1) sebelum memindai buku.',
                confirmButtonColor: '#696cff'
            });
            $('#inputKodeAnggota').focus();
            return;
        }

        // Langsung bersihkan field input agar scan berikutnya tidak bertumpuk / bercampur teks!
        $('#inputKodeBuku').val('');
        isLookingUpBuku = true;

        $.ajax({
            url: "{{ route('buku.lookup') }}",
            method: 'GET',
            data: { code: code },
            success: function(res) {
                isLookingUpBuku = false;
                if (res.success) {
                    const data = res.data;

                    // 1. Cek apakah eksemplar ini sudah ada di keranjang
                    const alreadyInCart = cartItems.some(item => item.id === data.id);
                    if (alreadyInCart) {
                        playBeep(false);
                        Swal.fire({
                            icon: 'warning',
                            title: 'Sudah Ada di Keranjang',
                            text: `Eksemplar '${data.kode_eksemplar}' (${data.judul}) sudah ada dalam daftar keranjang pinjam.`,
                            confirmButtonColor: '#696cff'
                        });
                        $('#inputKodeBuku').focus();
                        return;
                    }

                    // 2. Cek kuota maksimal pinjam
                    const currentTotal = activeLoansCount + cartItems.length + 1;
                    if (currentTotal > maxBukuPinjam) {
                        playBeep(false);
                        Swal.fire({
                            icon: 'warning',
                            title: 'Batas Kuota Pinjam Terlampaui',
                            text: `Maksimal peminjaman adalah ${maxBukuPinjam} buku. Anggota sudah memiliki ${activeLoansCount} pinjaman aktif, dan ${cartItems.length} buku di keranjang.`,
                            confirmButtonColor: '#696cff'
                        });
                        $('#inputKodeBuku').focus();
                        return;
                    }

                    // 3. Cek ketersediaan eksemplar
                    if (!data.is_available) {
                        playBeep(false);
                        Swal.fire({
                            icon: 'error',
                            title: 'Eksemplar Tidak Tersedia',
                            text: `Eksemplar '${data.kode_eksemplar}' (${data.judul}) sedang berstatus '${data.status}' (Kondisi: ${data.kondisi}) dan tidak dapat dipinjam.`,
                            confirmButtonColor: '#696cff'
                        });
                        $('#inputKodeBuku').focus();
                        return;
                    }

                    // Tambahkan ke array keranjang
                    cartItems.push(data);
                    playBeep(true);
                    renderCart();

                    // Tetap fokus di input buku untuk scan instan berikutnya
                    $('#inputKodeBuku').focus();
                }
            },
            error: function(xhr) {
                isLookingUpBuku = false;
                playBeep(false);
                Swal.fire({
                    icon: 'error',
                    title: 'Buku Tidak Ditemukan',
                    text: xhr.responseJSON?.message || `Kode barcode '${code}' tidak ditemukan di katalog perpustakaan.`,
                    confirmButtonColor: '#696cff'
                });
                $('#inputKodeBuku').focus();
            }
        });
    }

    $('#btnTambahBuku').click(function() {
        clearTimeout(scanBukuTimer);
        lookupAndAddToCart();
    });

    $('#inputKodeBuku').on('keydown', function(e) {
        if (e.which === 13 || e.key === 'Enter') {
            e.preventDefault();
            clearTimeout(scanBukuTimer);
            lookupAndAddToCart();
        }
    });

    // Auto add ke keranjang instan saat barcode buku di-scan (tanpa perlu tekan Enter / klik tombol)
    $('#inputKodeBuku').on('input', function() {
        clearTimeout(scanBukuTimer);
        const val = $(this).val().trim();
        if (val.length >= 3) {
            scanBukuTimer = setTimeout(function() {
                lookupAndAddToCart();
            }, 180);
        }
    });

    // 3. Render Tabel Keranjang Buku
    function renderCart() {
        const tbody = $('#cartTableBody');
        const hiddenContainer = $('#hiddenCartInputs');
        tbody.empty();
        hiddenContainer.empty();

        if (cartItems.length === 0) {
            tbody.html(`
                <tr id="emptyCartRow">
                    <td colspan="4" class="text-center py-4 text-muted">
                        <i class="bx bx-cart fs-3 d-block mb-1 text-secondary"></i>
                        Keranjang masih kosong. Silakan scan barcode buku fisik di atas.
                    </td>
                </tr>
            `);
            $('#badgeCartCount').text('0 Buku di Keranjang').removeClass('bg-success').addClass('bg-info');
            $('#txtBtnCount').text('0');
            return;
        }

        cartItems.forEach((item, index) => {
            tbody.append(`
                <tr>
                    <td>
                        <div class="d-flex align-items-center">
                            <span class="badge bg-label-dark font-monospace me-2">${item.kode_eksemplar}</span>
                            <div>
                                <strong class="d-block text-dark text-wrap" style="max-width: 220px;">${item.judul}</strong>
                                <small class="text-muted">${item.pengarang || '-'} • ${item.kategori}</small>
                            </div>
                        </div>
                    </td>
                    <td>
                        <small class="text-muted">${item.lokasi}</small>
                    </td>
                    <td class="text-center">
                        <span class="badge bg-label-success">Tersedia</span>
                    </td>
                    <td class="text-center">
                        <button type="button" class="btn btn-icon btn-xs btn-outline-danger" onclick="removeFromCart(${index})" title="Hapus dari keranjang">
                            <i class="bx bx-trash"></i>
                        </button>
                    </td>
                </tr>
            `);

            // Tambahkan hidden input untuk dikirim via POST
            hiddenContainer.append(`<input type="hidden" name="id_eksemplar[]" value="${item.id}">`);
        });

        $('#badgeCartCount').text(`${cartItems.length} Buku di Keranjang`).removeClass('bg-info').addClass('bg-success');
        $('#txtBtnCount').text(cartItems.length);
    }

    // 4. Hapus Item dari Keranjang
    window.removeFromCart = function(index) {
        cartItems.splice(index, 1);
        renderCart();
        $('#inputKodeBuku').focus();
    };

    // 5. Validasi Sebelum Submit Form
    $('#formPeminjaman').on('submit', function(e) {
        if (!currentAnggota) {
            e.preventDefault();
            Swal.fire({
                icon: 'warning',
                title: 'Anggota Belum Di-scan',
                text: 'Harap scan kartu anggota terlebih dahulu.',
                confirmButtonColor: '#696cff'
            });
            $('#inputKodeAnggota').focus();
            return false;
        }

        if (cartItems.length === 0) {
            e.preventDefault();
            Swal.fire({
                icon: 'warning',
                title: 'Keranjang Buku Kosong',
                text: 'Harap scan minimal 1 buku ke dalam keranjang peminjaman.',
                confirmButtonColor: '#696cff'
            });
            $('#inputKodeBuku').focus();
            return false;
        }

        return true;
    });
</script>
@endpush
