@extends('layouts.auth')

@section('title', 'Login Sistem Perpustakaan')

@section('content')
<div class="card">
    <div class="card-body">
        <!-- Logo -->
        <div class="app-brand justify-content-center mb-4">
            <a href="{{ url('/') }}" class="app-brand-link gap-2">
                <i class="bx bxs-book-reader text-primary" style="font-size: 2.5rem;"></i>
                <span class="app-brand-text demo text-body fw-bolder" style="font-size: 1.5rem;">ISRA Perpus</span>
            </a>
        </div>
        <!-- /Logo -->
        
        <h4 class="mb-2 text-center">Selamat Datang! 👋</h4>
        <p class="mb-4 text-center text-muted">Silakan masuk menggunakan akun admin / petugas perpustakaan Anda</p>

        <form id="formAuthentication" class="mb-3" action="{{ route('login.post') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <div class="input-group input-group-merge">
                    <span class="input-group-text"><i class="bx bx-user"></i></span>
                    <input type="text" class="form-control @error('username') is-invalid @enderror" id="username" name="username" value="{{ old('username') }}" placeholder="Masukkan username Anda" autofocus required />
                </div>
                @error('username')
                    <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3 form-password-toggle">
                <div class="d-flex justify-content-between">
                    <label class="form-label" for="password">Password</label>
                </div>
                <div class="input-group input-group-merge">
                    <span class="input-group-text"><i class="bx bx-lock-alt"></i></span>
                    <input type="password" id="password" class="form-control @error('password') is-invalid @enderror" name="password" placeholder="············" aria-describedby="password" required />
                    <span class="input-group-text cursor-pointer"><i class="bx bx-hide"></i></span>
                </div>
                @error('password')
                    <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="remember" name="remember" />
                    <label class="form-check-label" for="remember"> Ingat Saya di Perangkat Ini </label>
                </div>
            </div>

            <div class="mb-3">
                <button class="btn btn-primary d-grid w-100 py-2 fw-semibold" type="submit">
                    <i class="bx bx-log-in-circle me-1"></i> Masuk ke Sistem
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
