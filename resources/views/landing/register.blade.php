<!DOCTYPE html>
<html lang="id" class="light-style layout-menu-fixed" dir="ltr" data-theme="theme-default">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
    <title>Pendaftaran Anggota Perpustakaan - {{ $pengaturan->nama_perpustakaan }}</title>

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('template/assets/img/favicon/favicon.ico') }}" />

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap" rel="stylesheet" />

    <!-- Icons -->
    <link rel="stylesheet" href="{{ asset('template/assets/vendor/fonts/boxicons.css') }}" />

    <!-- Core CSS -->
    <link rel="stylesheet" href="{{ asset('template/assets/vendor/css/core.css') }}" class="template-customizer-core-css" />
    <link rel="stylesheet" href="{{ asset('template/assets/vendor/css/theme-default.css') }}" class="template-customizer-theme-css" />
    <link rel="stylesheet" href="{{ asset('template/assets/css/demo.css') }}" />

    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.6/dist/sweetalert2.min.css" />

    <style>
        body {
            background-color: #f5f5f9;
        }
        .header-bg {
            background: linear-gradient(135deg, #1e266d 0%, #4338ca 100%);
            color: #ffffff;
            padding: 40px 0 70px 0;
            border-bottom-left-radius: 30px;
            border-bottom-right-radius: 30px;
        }
        .card-register {
            margin-top: -45px;
            border: none;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(67, 89, 113, 0.1);
        }
        .swal2-container {
            z-index: 999999 !important;
        }
    </style>
</head>

<body>

<!-- Header -->
<div class="header-bg text-center">
    <div class="container">
        <a href="{{ route('landing.index') }}" class="text-white-50 text-decoration-none small d-inline-flex align-items-center mb-2">
            <i class="bx bx-arrow-back me-1"></i> Kembali ke Beranda
        </a>
        <h2 class="fw-bold text-white mb-1">Formulir Pendaftaran Anggota Baru</h2>
        <p class="text-white-50 mb-0">{{ $pengaturan->nama_perpustakaan }}</p>
    </div>
</div>

<!-- Main Form Container -->
<div class="container mb-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card card-register">
                <div class="card-body p-4 p-md-5">
                    @if ($errors->any())
                        <div class="alert alert-danger mb-4">
                            <h6 class="alert-heading fw-bold mb-1"><i class="bx bx-error-circle me-1"></i> Terjadi Kesalahan:</h6>
                            <ul class="mb-0 ps-3 small">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('landing.register.post') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        
                        <div class="alert alert-primary p-3 mb-4 d-flex align-items-center gap-3">
                            <i class="bx bx-info-circle fs-2 text-primary"></i>
                            <div class="small">
                                Nomor anggota perpustakaan digital resmi (Barcode ID) akan di-generate otomatis setelah formulir disimpan.
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-semibold">Nama Lengkap <span class="text-danger">*</span></label>
                                <input type="text" name="nama" class="form-control form-control-lg" placeholder="Masukkan nama lengkap sesuai data sekolah" value="{{ old('nama') }}" required>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Jenis Identitas <span class="text-danger">*</span></label>
                                <select name="jenis_identitas" class="form-select form-select-lg" required>
                                    <option value="NIS" {{ old('jenis_identitas') === 'NIS' ? 'selected' : '' }}>NIS (Siswa)</option>
                                    <option value="NIP" {{ old('jenis_identitas') === 'NIP' ? 'selected' : '' }}>NIP (Guru/Staf)</option>
                                    <option value="NIK" {{ old('jenis_identitas') === 'NIK' ? 'selected' : '' }}>NIK / Umum</option>
                                </select>
                            </div>

                            <div class="col-md-8">
                                <label class="form-label fw-semibold">Nomor Identitas (NIS / NIP) <span class="text-danger">*</span></label>
                                <input type="text" name="identitas" class="form-control form-control-lg" placeholder="Contoh: 202401001 / 19850512..." value="{{ old('identitas') }}" required>
                                <small class="text-muted">Nomor unik identitas di sekolah yang belum pernah didaftarkan.</small>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Kelas / Jabatan <span class="text-danger">*</span></label>
                                <input type="text" name="kelas_jabatan" class="form-control" placeholder="Contoh: X MIPA 1 / Guru Bahasa / Staf TU" value="{{ old('kelas_jabatan') }}" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Nomor HP / WhatsApp (Aktif)</label>
                                <input type="text" name="no_hp" class="form-control" placeholder="Contoh: 081234567890" value="{{ old('no_hp') }}">
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold">Alamat Tempat Tinggal</label>
                                <textarea name="alamat" class="form-control" rows="2" placeholder="Alamat rumah / domisili">{{ old('alamat') }}</textarea>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold">Foto Profil Anggota (Opsional)</label>
                                <input type="file" name="foto" class="form-control" accept="image/*">
                                <small class="text-muted">Format: JPG/PNG, ukuran maksimal 2MB. Foto akan dicetak pada kartu anggota.</small>
                            </div>
                        </div>

                        <div class="mt-4 pt-3 border-top d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <a href="{{ route('landing.index') }}" class="btn btn-outline-secondary">
                                <i class="bx bx-x me-1"></i> Batal
                            </a>
                            <button type="submit" class="btn btn-primary btn-lg px-4 fw-bold">
                                <i class="bx bx-check-circle me-1"></i> Kirim Pendaftaran
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="{{ asset('template/assets/vendor/libs/jquery/jquery.js') }}"></script>
<script src="{{ asset('template/assets/vendor/libs/bootstrap/bootstrap.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.6/dist/sweetalert2.all.min.js"></script>

</body>
</html>
