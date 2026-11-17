<nav class="layout-navbar container-xxl navbar navbar-expand-xl navbar-detached align-items-center bg-navbar-theme" id="layout-navbar">
    <div class="layout-menu-toggle navbar-nav align-items-xl-center me-3 me-xl-0 d-xl-none">
        <a class="nav-item nav-link px-0 me-xl-4" href="javascript:void(0)">
            <i class="bx bx-menu bx-sm"></i>
        </a>
    </div>

    <div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">
        <!-- Search Quick Barcode -->
        <div class="navbar-nav align-items-center">
            <div class="nav-item d-flex align-items-center">
                <i class="bx bx-barcode-reader fs-4 lh-0 text-primary me-2"></i>
                <span class="badge bg-label-primary fw-semibold d-none d-md-inline-block">
                    Perpustakaan SMA Kr Indonesia Sejahtera v1.1
                </span>
                <span class="badge bg-label-primary fw-semibold d-md-none">
                    Perpus ISRA v1.1
                </span>
            </div>
        </div>

        <ul class="navbar-nav flex-row align-items-center ms-auto">
            <!-- User Dropdown -->
            <li class="nav-item navbar-dropdown dropdown-user dropdown">
                <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown">
                    <div class="avatar avatar-online">
                        <span class="avatar-initial rounded-circle bg-primary text-white">
                            {{ substr(auth()->user()->name ?? 'U', 0, 1) }}
                        </span>
                    </div>
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                        <a class="dropdown-item" href="#">
                            <div class="d-flex">
                                <div class="flex-shrink-0 me-3">
                                    <div class="avatar avatar-online">
                                        <span class="avatar-initial rounded-circle bg-primary text-white">
                                            {{ substr(auth()->user()->name ?? 'U', 0, 1) }}
                                        </span>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <span class="fw-semibold d-block">{{ auth()->user()->name ?? 'Admin' }}</span>
                                    <small class="text-muted text-capitalize">{{ auth()->user()->level ?? 'Admin' }} ({{ auth()->user()->username ?? 'admin' }})</small>
                                </div>
                            </div>
                        </a>
                    </li>
                    <li>
                        <div class="dropdown-divider"></div>
                    </li>
                    <li>
                        <a class="dropdown-item" href="{{ route('profile.index') }}">
                            <i class="bx bx-user me-2"></i>
                            <span class="align-middle">Profil & Password</span>
                        </a>
                    </li>
                    @if(auth()->check() && auth()->user()->isAdmin())
                    <li>
                        <a class="dropdown-item" href="{{ route('pengaturan.index') }}">
                            <i class="bx bx-cog me-2"></i>
                            <span class="align-middle">Pengaturan</span>
                        </a>
                    </li>
                    @endif
                    <li>
                        <div class="dropdown-divider"></div>
                    </li>
                    <li>
                        <form action="{{ route('logout') }}" method="POST" id="logout-form">
                            @csrf
                            <a class="dropdown-item btn-action-confirm" href="javascript:void(0);" 
                               data-title="Keluar dari Sistem?" 
                               data-text="Apakah Anda yakin ingin mengakhiri sesi ini?" 
                               data-icon="question" 
                               data-confirm-text="Ya, Keluar" 
                               data-btn-class="btn-danger">
                                <i class="bx bx-power-off me-2 text-danger"></i>
                                <span class="align-middle text-danger">Keluar (Logout)</span>
                            </a>
                        </form>
                    </li>
                </ul>
            </li>
            <!--/ User -->
        </ul>
    </div>
</nav>
