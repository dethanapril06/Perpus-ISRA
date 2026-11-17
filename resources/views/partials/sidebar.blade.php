<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
    <div class="app-brand demo">
        <a href="{{ route('dashboard') }}" class="app-brand-link">
            <span class="app-brand-logo demo">
                <i class="bx bxs-book-reader text-primary" style="font-size: 2rem;"></i>
            </span>
            <span class="app-brand-text demo menu-text fw-bolder ms-2 text-capitalize" style="font-size: 1.25rem;">ISRA Perpus</span>
        </a>

        <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto d-block d-xl-none">
            <i class="bx bx-chevron-left bx-sm align-middle"></i>
        </a>
    </div>

    <div class="menu-inner-shadow"></div>

    <ul class="menu-inner py-1">
        <!-- Dashboard -->
        <li class="menu-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <a href="{{ route('dashboard') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-home-circle"></i>
                <div>Dashboard</div>
            </a>
        </li>

        <!-- Header Transaksi & Operasional -->
        <li class="menu-header small text-uppercase">
            <span class="menu-header-text">Layanan & Transaksi</span>
        </li>

        <!-- Kunjungan -->
        <li class="menu-item {{ request()->routeIs('kunjungan.*') ? 'active' : '' }}">
            <a href="{{ route('kunjungan.index') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-walk"></i>
                <div>Data Kunjungan</div>
            </a>
        </li>

        <!-- Peminjaman -->
        <li class="menu-item {{ request()->routeIs('peminjaman.*') ? 'active' : '' }}">
            <a href="{{ route('peminjaman.index') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-book-bookmark"></i>
                <div>Peminjaman Buku</div>
            </a>
        </li>

        <!-- Pengembalian -->
        <li class="menu-item {{ request()->routeIs('pengembalian.*') ? 'active' : '' }}">
            <a href="{{ route('pengembalian.index') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-book-content"></i>
                <div>Pengembalian Buku</div>
            </a>
        </li>

        <!-- Data Denda -->
        <li class="menu-item {{ request()->routeIs('denda.*') ? 'active' : '' }}">
            <a href="{{ route('denda.index') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-money"></i>
                <div>Data Denda</div>
            </a>
        </li>

        <!-- Header Master Data -->
        <li class="menu-header small text-uppercase">
            <span class="menu-header-text">Master Data</span>
        </li>

        <!-- Anggota -->
        <li class="menu-item {{ request()->routeIs('anggota.index') || request()->routeIs('anggota.show') ? 'active' : '' }}">
            <a href="{{ route('anggota.index') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-id-card"></i>
                <div>Data Anggota</div>
            </a>
        </li>

        <!-- Pairing RFID -->
        <li class="menu-item {{ request()->routeIs('anggota.pairing_rfid') ? 'active' : '' }}">
            <a href="{{ route('anggota.pairing_rfid') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-broadcast"></i>
                <div>Pairing Kartu RFID</div>
            </a>
        </li>

        <!-- Buku -->
        <li class="menu-item {{ request()->routeIs('buku.*') ? 'active' : '' }}">
            <a href="{{ route('buku.index') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-book"></i>
                <div>Data Buku & Barcode</div>
            </a>
        </li>

        <!-- Kategori Buku -->
        <li class="menu-item {{ request()->routeIs('kategori.*') ? 'active' : '' }}">
            <a href="{{ route('kategori.index') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-category"></i>
                <div>Kategori Buku</div>
            </a>
        </li>

        <!-- Lokasi Penyimpanan (Lemari & Laci) -->
        <li class="menu-item {{ request()->routeIs('lemari.*') || request()->routeIs('laci.*') ? 'active open' : '' }}">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-cabinet"></i>
                <div>Lokasi Rak & Laci</div>
            </a>
            <ul class="menu-sub">
                <li class="menu-item {{ request()->routeIs('lemari.*') ? 'active' : '' }}">
                    <a href="{{ route('lemari.index') }}" class="menu-link">
                        <div>Data Lemari</div>
                    </a>
                </li>
                <li class="menu-item {{ request()->routeIs('laci.*') ? 'active' : '' }}">
                    <a href="{{ route('laci.index') }}" class="menu-link">
                        <div>Data Laci</div>
                    </a>
                </li>
            </ul>
        </li>

        <!-- Header Laporan & Analitik -->
        <li class="menu-header small text-uppercase">
            <span class="menu-header-text">Laporan & Audit</span>
        </li>

        <!-- Laporan -->
        <li class="menu-item {{ request()->routeIs('laporan.*') ? 'active' : '' }}">
            <a href="{{ route('laporan.index') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-file"></i>
                <div>Laporan Perpustakaan</div>
            </a>
        </li>

        <!-- Activity Log -->
        <li class="menu-item {{ request()->routeIs('activity-log.*') ? 'active' : '' }}">
            <a href="{{ route('activity-log.index') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-history"></i>
                <div>Log Aktivitas</div>
            </a>
        </li>

        <!-- Header Pengaturan & Akun -->
        <li class="menu-header small text-uppercase">
            <span class="menu-header-text">Pengaturan Sistem</span>
        </li>

        <!-- Manajemen Admin / Pengguna -->
        @if(auth()->check() && auth()->user()->isAdmin())
        <li class="menu-item {{ request()->routeIs('users.*') ? 'active' : '' }}">
            <a href="{{ route('users.index') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-user-circle"></i>
                <div>Data Admin / Petugas</div>
            </a>
        </li>

        <!-- Pengaturan Denda & Sistem -->
        <li class="menu-item {{ request()->routeIs('pengaturan.*') ? 'active' : '' }}">
            <a href="{{ route('pengaturan.index') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-cog"></i>
                <div>Pengaturan Perpustakaan</div>
            </a>
        </li>
        @endif
    </ul>
</aside>
