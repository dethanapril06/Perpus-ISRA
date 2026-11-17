<!DOCTYPE html>
<html lang="id" class="light-style layout-menu-fixed" dir="ltr" data-theme="theme-default">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
    <title>Beranda - {{ $pengaturan->nama_perpustakaan }}</title>

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
        .hero-section {
            background: linear-gradient(135deg, #1e266d 0%, #3730a3 50%, #4f46e5 100%);
            color: #ffffff;
            padding: 70px 0 80px 0;
            position: relative;
        }
        .stats-wrapper {
            margin-top: -45px;
            position: relative;
            z-index: 5;
        }
        .stat-card-modern {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            padding: 22px 18px;
            box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.08), 0 8px 10px -6px rgba(15, 23, 42, 0.04);
            transition: all 0.25s ease;
            display: flex;
            align-items: center;
            gap: 16px;
            height: 100%;
        }
        .stat-card-modern:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 30px -10px rgba(15, 23, 42, 0.12);
            border-color: #cbd5e1;
        }
        .stat-icon-wrap {
            width: 52px;
            height: 52px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            color: #ffffff;
            font-size: 24px;
        }
        .stat-icon-indigo {
            background: linear-gradient(135deg, #6366f1 0%, #4338ca 100%);
            box-shadow: 0 6px 14px rgba(99, 102, 241, 0.35);
        }
        .stat-icon-blue {
            background: linear-gradient(135deg, #38bdf8 0%, #0284c7 100%);
            box-shadow: 0 6px 14px rgba(2, 132, 199, 0.35);
        }
        .stat-icon-emerald {
            background: linear-gradient(135deg, #34d399 0%, #059669 100%);
            box-shadow: 0 6px 14px rgba(5, 150, 105, 0.35);
        }
        .stat-icon-amber {
            background: linear-gradient(135deg, #fbbf24 0%, #d97706 100%);
            box-shadow: 0 6px 14px rgba(217, 119, 6, 0.35);
        }
        .stat-number {
            font-size: 26px;
            font-weight: 800;
            color: #0f172a;
            line-height: 1.1;
            margin-bottom: 2px;
        }
        .stat-label {
            font-size: 12px;
            font-weight: 600;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0;
        }
        .book-card {
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            transition: all 0.25s ease;
            height: 100%;
            background: #ffffff;
            overflow: hidden;
        }
        .book-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 14px 28px rgba(30, 41, 59, 0.1);
            border-color: #6366f1;
        }
        .book-cover-wrap {
            height: 220px;
            background: #f8fafc;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            border-bottom: 1px solid #f1f5f9;
        }
        .book-cover-img {
            max-height: 100%;
            width: auto;
            object-fit: cover;
            transition: transform 0.3s ease;
        }
        .book-card:hover .book-cover-img {
            transform: scale(1.05);
        }
        .navbar-public {
            position: sticky;
            top: 0;
            z-index: 1080 !important;
            backdrop-filter: blur(8px);
            background-color: rgba(255, 255, 255, 0.95);
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .swal2-container {
            z-index: 999999 !important;
        }
    </style>
</head>

<body>

<!-- Header Navigation -->
<nav class="navbar navbar-expand-lg navbar-public sticky-top py-3">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center" href="{{ route('landing.index') }}">
            <div class="avatar avatar-sm me-2">
                <span class="avatar-initial rounded-circle bg-primary text-white"><i class="bx bx-book-bookmark"></i></span>
            </div>
            <div>
                <span class="fw-bold text-dark fs-5 d-block lh-1">
                    <span class="d-none d-md-inline">{{ $pengaturan->nama_perpustakaan }}</span>
                    <span class="d-md-none">Perpus ISRA</span>
                </span>
                <small class="text-muted" style="font-size: 11px;">Sistem Informasi Perpustakaan Digital</small>
            </div>
        </a>

        <button class="navbar-toggler border-0 p-1 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent" aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
            <i class="bx bx-menu fs-2 text-dark"></i>
        </button>

        <div class="collapse navbar-collapse" id="navbarContent">
            <ul class="navbar-nav ms-auto align-items-lg-center gap-2 pt-3 pt-lg-0">
                <li class="nav-item">
                    <a class="nav-link fw-semibold text-dark active py-2" href="#katalog">Katalog Buku</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link fw-semibold text-dark py-2" href="#layanan">Layanan & Tata Tertib</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link fw-semibold text-dark py-2" href="#kontak">Kontak</a>
                </li>
                <li class="nav-item ms-lg-2 mt-2 mt-lg-0">
                    <a href="{{ route('landing.register') }}" class="btn btn-primary fw-semibold shadow-sm px-3 w-100 w-lg-auto">
                        <i class="bx bx-user-plus me-1"></i> Daftar Anggota
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- Hero Section -->
<section class="hero-section text-center">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <span class="badge bg-white text-primary fw-bold px-3 py-2 rounded-pill mb-3 text-uppercase shadow-sm">
                    ✨ Portal Resmi Perpustakaan Sekolah
                </span>
                <h1 class="display-5 fw-bold text-white mb-3">Selamat Datang di <span class="d-none d-md-inline">{{ $pengaturan->nama_perpustakaan }}</span><span class="d-md-none">Perpus ISRA</span></h1>
                <p class="lead text-white-50 mb-4 fs-6">
                    Akses ribuan koleksi buku pelajaran, sains, teknologi, novel fiksi, hingga ensiklopedia. Cari katalog buku secara instan atau daftarkan diri Anda menjadi anggota resmi perpustakaan.
                </p>

                <!-- Form Pencarian Cepat Katalog (OPAC) -->
                <form action="{{ route('landing.index') }}" method="GET" class="card p-2 p-md-3 shadow-lg border-0 rounded-pill mx-auto mb-4" style="max-width: 680px;">
                    <div class="input-group">
                        <span class="input-group-text bg-transparent border-0 fs-4 text-muted"><i class="bx bx-search"></i></span>
                        <input type="text" name="q" class="form-control border-0 fs-6" placeholder="Ketik judul buku, pengarang, ISBN, atau topik pelajaran..." value="{{ request('q') }}">
                        <button class="btn btn-primary rounded-pill px-4 fw-bold" type="submit">Cari Buku</button>
                    </div>
                </form>

                <div class="d-flex justify-content-center gap-3 flex-wrap mb-4">
                    <a href="{{ route('landing.register') }}" class="btn btn-warning btn-lg rounded-pill fw-bold shadow">
                        <i class="bx bx-id-card me-1"></i> Daftar Jadi Anggota Sekarang
                    </a>
                    <a href="#katalog" class="btn btn-outline-light btn-lg rounded-pill fw-semibold">
                        <i class="bx bx-book-open me-1"></i> Jelajahi Koleksi
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Statistik Counter Cards (Redesigned Clean & Modern) -->
<section class="stats-wrapper">
    <div class="container">
        <div class="row g-3">
            <!-- 1. Judul Buku -->
            <div class="col-6 col-lg-3">
                <div class="stat-card-modern">
                    <div class="stat-icon-wrap stat-icon-indigo">
                        <i class="bx bx-book-bookmark"></i>
                    </div>
                    <div>
                        <div class="stat-number">{{ number_format($totalJudul, 0, ',', '.') }}</div>
                        <p class="stat-label">Judul Buku</p>
                    </div>
                </div>
            </div>

            <!-- 2. Total Eksemplar Fisik -->
            <div class="col-6 col-lg-3">
                <div class="stat-card-modern">
                    <div class="stat-icon-wrap stat-icon-blue">
                        <i class="bx bx-copy"></i>
                    </div>
                    <div>
                        <div class="stat-number">{{ number_format($totalEksemplar, 0, ',', '.') }}</div>
                        <p class="stat-label">Total Eksemplar</p>
                    </div>
                </div>
            </div>

            <!-- 3. Eksemplar Tersedia -->
            <div class="col-6 col-lg-3">
                <div class="stat-card-modern">
                    <div class="stat-icon-wrap stat-icon-emerald">
                        <i class="bx bx-check-circle"></i>
                    </div>
                    <div>
                        <div class="stat-number text-success">{{ number_format($totalTersedia, 0, ',', '.') }}</div>
                        <p class="stat-label">Siap Dipinjam</p>
                    </div>
                </div>
            </div>

            <!-- 4. Anggota Terdaftar -->
            <div class="col-6 col-lg-3">
                <div class="stat-card-modern">
                    <div class="stat-icon-wrap stat-icon-amber">
                        <i class="bx bx-group"></i>
                    </div>
                    <div>
                        <div class="stat-number text-dark">{{ number_format($totalAnggota, 0, ',', '.') }}</div>
                        <p class="stat-label">Anggota Terdaftar</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Katalog Koleksi Buku (OPAC) -->
<section id="katalog" class="py-5 mt-3">
    <div class="container">
        <div class="d-flex justify-content-between align-items-end mb-4 flex-wrap gap-2">
            <div>
                <span class="badge bg-label-primary fw-semibold mb-1">Koleksi Terkini</span>
                <h3 class="fw-bold text-dark mb-0">Katalog Buku Perpustakaan</h3>
            </div>

            <!-- Filter Kategori Kancing -->
            <div class="d-flex gap-2 flex-wrap">
                <a href="{{ route('landing.index') }}#katalog" class="btn btn-sm {{ !request('id_kategori') ? 'btn-primary' : 'btn-outline-secondary' }}">
                    Semua ({{ $totalJudul }})
                </a>
                @foreach($kategoriList as $kat)
                    <a href="{{ route('landing.index', ['id_kategori' => $kat->id]) }}#katalog" class="btn btn-sm {{ request('id_kategori') == $kat->id ? 'btn-primary' : 'btn-outline-secondary' }}">
                        {{ $kat->nama_kategori }} ({{ $kat->buku_count }})
                    </a>
                @endforeach
            </div>
        </div>

        @if(request('q'))
            <div class="alert alert-info py-2 px-3 mb-4 d-flex justify-content-between align-items-center">
                <span>Hasil pencarian untuk kata kunci: <strong>"{{ request('q') }}"</strong> ({{ $bukuList->total() }} buku ditemukan)</span>
                <a href="{{ route('landing.index') }}#katalog" class="btn btn-sm btn-outline-info">Reset Pencarian</a>
            </div>
        @endif

        <div class="row g-4">
            @forelse($bukuList as $b)
            <div class="col-6 col-md-4 col-lg-3">
                <div class="card book-card">
                    <div class="book-cover-wrap">
                        @if($b->cover)
                            <img src="{{ asset('storage/' . $b->cover) }}" alt="{{ $b->judul }}" class="book-cover-img p-2">
                        @else
                            <div class="text-center p-3 text-muted">
                                <i class="bx bx-book text-secondary" style="font-size: 4rem;"></i>
                            </div>
                        @endif
                    </div>
                    <div class="card-body p-3 d-flex flex-column justify-content-between">
                        <div>
                            <span class="badge bg-label-primary mb-1" style="font-size: 10px;">{{ $b->kategori->nama_kategori ?? 'Umum' }}</span>
                            <h6 class="fw-bold text-dark mb-1 text-truncate" title="{{ $b->judul }}">{{ $b->judul }}</h6>
                            <small class="text-muted d-block mb-2 text-truncate">{{ $b->pengarang ?: 'Pengarang: -' }}</small>
                        </div>
                        
                        <div class="pt-2 border-top">
                            <div class="d-flex justify-content-between align-items-center">
                                @if($b->stok > 0)
                                    <span class="badge bg-label-success" style="font-size: 11px;">
                                        <i class="bx bx-check me-1"></i> {{ $b->stok }} Tersedia
                                    </span>
                                @else
                                    <span class="badge bg-label-danger" style="font-size: 11px;">
                                        <i class="bx bx-x me-1"></i> Sedang Dipinjam
                                    </span>
                                @endif
                                <small class="text-muted font-monospace">{{ $b->kode_induk_buku }}</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @empty
            <div class="col-12 text-center py-5">
                <div class="avatar avatar-xl bg-label-secondary rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center">
                    <i class="bx bx-search fs-1"></i>
                </div>
                <h5>Tidak ada buku yang cocok dengan pencarian</h5>
                <p class="text-muted">Coba gunakan kata kunci lain atau lihat seluruh koleksi.</p>
                <a href="{{ route('landing.index') }}#katalog" class="btn btn-primary">Lihat Semua Koleksi</a>
            </div>
            @endforelse
        </div>

        <div class="d-flex justify-content-center mt-4">
            {{ $bukuList->links('pagination::bootstrap-5') }}
        </div>
    </div>
</section>

<!-- Layanan & Alur Peminjaman -->
<section id="layanan" class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <span class="badge bg-label-primary fw-semibold mb-1">Panduan Pengunjung</span>
            <h3 class="fw-bold text-dark">Alur Layanan Perpustakaan</h3>
            <p class="text-muted">Proses sirkulasi cepat, mudah, dan serba digital menggunakan kartu barcode.</p>
        </div>

        <div class="row g-4">
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm p-4 text-center">
                    <div class="avatar avatar-lg bg-label-primary rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center">
                        <i class="bx bx-user-plus fs-2"></i>
                    </div>
                    <h5 class="fw-bold mb-2">1. Pendaftaran Online</h5>
                    <p class="text-muted small">
                        Daftarkan identitas Anda melalui formulir online dan dapatkan nomor anggota serta barcode resmi perpustakaan secara instan.
                    </p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm p-4 text-center">
                    <div class="avatar avatar-lg bg-label-info rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center">
                        <i class="bx bx-walk fs-2"></i>
                    </div>
                    <h5 class="fw-bold mb-2">2. Presensi & Baca di Tempat</h5>
                    <p class="text-muted small">
                        Tunjukkan kartu anggota ke petugas saat tiba di perpustakaan. Anda bebas membaca dan menjelajahi seluruh koleksi buku di rak.
                    </p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm p-4 text-center">
                    <div class="avatar avatar-lg bg-label-success rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center">
                        <i class="bx bx-book-bookmark fs-2"></i>
                    </div>
                    <h5 class="fw-bold mb-2">3. Peminjaman & Pengembalian</h5>
                    <p class="text-muted small">
                        Bawa buku pilihan ke meja petugas. Petugas akan memindai barcode buku dan mencetak struk peminjaman berdurasi hingga {{ $pengaturan->durasi_pinjam_default }} hari.
                    </p>
                </div>
            </div>
        </div>

        <!-- Banner CTA Registrasi -->
        <div class="card bg-primary text-white border-0 shadow-lg mt-5 p-4 p-md-5 rounded-3">
            <div class="row align-items-center">
                <div class="col-md-8 mb-3 mb-md-0">
                    <h3 class="fw-bold text-white mb-2">Belum Memiliki Kartu Anggota?</h3>
                    <p class="text-white-50 mb-0">Daftarkan diri Anda sekarang untuk menikmati akses peminjaman buku gratis di perpustakaan sekolah.</p>
                </div>
                <div class="col-md-4 text-md-end">
                    <a href="{{ route('landing.register') }}" class="btn btn-warning btn-lg fw-bold shadow">
                        <i class="bx bx-user-check me-1"></i> Isi Formulir Anggota
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Footer -->
<footer id="kontak" class="bg-dark text-white pt-5 pb-4">
    <div class="container">
        <div class="row g-4">
            <div class="col-md-5">
                <div class="d-flex align-items-center mb-3">
                    <div class="avatar avatar-sm me-2">
                        <span class="avatar-initial rounded-circle bg-primary text-white"><i class="bx bx-book-bookmark"></i></span>
                    </div>
                    <h5 class="fw-bold text-white mb-0">{{ $pengaturan->nama_perpustakaan }}</h5>
                </div>
                <p class="text-muted small mb-2">
                    Lembaga penyedia literasi dan sumber belajar civitas akademika dalam mewujudkan generasi unggul, berkarakter, dan berwawasan luas.
                </p>
                <p class="text-muted small mb-0">
                    <i class="bx bx-map me-1 text-primary"></i> {{ $pengaturan->alamat_perpustakaan }}
                </p>
            </div>
            <div class="col-md-3">
                <h6 class="text-white fw-bold mb-3">Tautan Cepat</h6>
                <ul class="list-unstyled text-muted small">
                    <li class="mb-2"><a href="#katalog" class="text-muted text-decoration-none">Pencarian Buku (OPAC)</a></li>
                    <li class="mb-2"><a href="{{ route('landing.register') }}" class="text-muted text-decoration-none">Pendaftaran Anggota Mandiri</a></li>
                    <li class="mb-2"><a href="#layanan" class="text-muted text-decoration-none">Tata Tertib & Ketentuan Pinjam</a></li>
                </ul>
            </div>
            <div class="col-md-4">
                <h6 class="text-white fw-bold mb-3">Jam Operasional & Kontak</h6>
                <ul class="list-unstyled text-muted small">
                    <li class="mb-2"><i class="bx bx-time me-1 text-primary"></i> <strong>Senin - Jumat:</strong> 07.30 - 15.00 WITA</li>
                    <li class="mb-2"><i class="bx bx-time me-1 text-primary"></i> <strong>Sabtu:</strong> 08.00 - 12.00 WITA</li>
                    <li class="mb-2"><i class="bx bx-phone me-1 text-primary"></i> Telp / WhatsApp: {{ $pengaturan->no_telp_perpustakaan }}</li>
                </ul>
            </div>
        </div>
        <hr class="border-secondary my-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 text-muted small">
            <div>© {{ date('Y') }} {{ $pengaturan->nama_perpustakaan }}. All rights reserved.</div>
            <div>
                <a href="{{ route('login') }}" class="text-muted text-decoration-none"><i class="bx bx-lock-alt me-1"></i>Login Petugas</a>
            </div>
        </div>
    </div>
</footer>

<!-- Core JS -->
<script src="{{ asset('template/assets/vendor/libs/jquery/jquery.js') }}"></script>
<script src="{{ asset('template/assets/vendor/libs/popper/popper.js') }}"></script>
<script src="{{ asset('template/assets/vendor/js/bootstrap.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.6/dist/sweetalert2.all.min.js"></script>

@if(session('success'))
<script>
    Swal.fire({
        icon: 'success',
        title: 'Berhasil!',
        text: "{{ session('success') }}",
        confirmButtonColor: '#696cff'
    });
</script>
@endif

<script>
    document.querySelectorAll('#navbarContent .nav-link').forEach(function(link) {
        link.addEventListener('click', function() {
            var navbarCollapse = document.getElementById('navbarContent');
            if (navbarCollapse && navbarCollapse.classList.contains('show')) {
                var bsCollapse = bootstrap.Collapse.getInstance(navbarCollapse);
                if (bsCollapse) {
                    bsCollapse.hide();
                }
            }
        });
    });
</script>

</body>
</html>
