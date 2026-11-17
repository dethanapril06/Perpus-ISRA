@extends('layouts.app')

@section('title', 'Manajemen Admin & Pengguna')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-bold py-3 mb-0"><span class="text-muted fw-light">Pengaturan /</span> Data Admin & Petugas</h4>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambahUser">
                <i class="bx bx-plus me-1"></i> Tambah Pengguna Baru
            </button>
        </div>

        <!-- Filter Card -->
        <div class="card mb-4">
            <div class="card-body">
                <form action="{{ route('users.index') }}" method="GET" class="row g-3 align-items-center">
                    <div class="col-md-5">
                        <div class="input-group">
                            <span class="input-group-text"><i class="bx bx-search"></i></span>
                            <input type="text" name="search" class="form-control" placeholder="Cari nama, username, email, telepon..." value="{{ request('search') }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <select name="level" class="form-select">
                            <option value="">-- Semua Level/Role --</option>
                            <option value="admin" {{ request('level') === 'admin' ? 'selected' : '' }}>Admin</option>
                            <option value="petugas" {{ request('level') === 'petugas' ? 'selected' : '' }}>Petugas</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="status" class="form-select">
                            <option value="">-- Semua Status --</option>
                            <option value="aktif" {{ request('status') === 'aktif' ? 'selected' : '' }}>Aktif</option>
                            <option value="nonaktif" {{ request('status') === 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex gap-2">
                        <button type="submit" class="btn btn-primary w-100"><i class="bx bx-filter-alt"></i> Filter</button>
                        @if(request()->hasAny(['search', 'level', 'status']))
                            <a href="{{ route('users.index') }}" class="btn btn-outline-secondary"><i class="bx bx-reset"></i></a>
                        @endif
                    </div>
                </form>
            </div>
        </div>

        <!-- Table Card -->
        <div class="card">
            <div class="table-responsive text-nowrap">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th width="60">No</th>
                            <th>Nama Lengkap</th>
                            <th>Username</th>
                            <th>Kontak</th>
                            <th>Role / Level</th>
                            <th>Status Akun</th>
                            <th width="120" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
                        @forelse($users as $index => $u)
                        <tr>
                            <td>{{ $users->firstItem() + $index }}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar avatar-sm me-2">
                                        <span class="avatar-initial rounded-circle {{ $u->level === 'admin' ? 'bg-label-primary' : 'bg-label-info' }}">
                                            {{ substr($u->name, 0, 1) }}
                                        </span>
                                    </div>
                                    <div>
                                        <span class="fw-bold d-block">{{ $u->name }}</span>
                                        @if($u->id === auth()->id())
                                            <span class="badge bg-label-success badge-xs">Akun Anda</span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td><code>{{ $u->username }}</code></td>
                            <td>
                                <small class="d-block"><i class="bx bx-envelope me-1"></i>{{ $u->email ?: '-' }}</small>
                                <small class="text-muted"><i class="bx bx-phone me-1"></i>{{ $u->no_telp ?: '-' }}</small>
                            </td>
                            <td>
                                @if($u->level === 'admin')
                                    <span class="badge bg-primary text-uppercase">Admin</span>
                                @else
                                    <span class="badge bg-info text-uppercase">Petugas</span>
                                @endif
                            </td>
                            <td>
                                @if($u->status === 'aktif')
                                    <span class="badge bg-label-success"><i class="bx bx-check-circle me-1"></i> Aktif</span>
                                @else
                                    <span class="badge bg-label-danger"><i class="bx bx-x-circle me-1"></i> Nonaktif</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="d-inline-flex gap-1">
                                    <!-- Tombol Edit Modal -->
                                    <button type="button" class="btn btn-icon btn-sm btn-outline-warning" data-bs-toggle="modal" data-bs-target="#modalEditUser{{ $u->id }}" title="Edit Pengguna">
                                        <i class="bx bx-edit-alt"></i>
                                    </button>

                                    <!-- Tombol Hapus -->
                                    @if($u->id !== auth()->id())
                                    <form action="{{ route('users.destroy', $u->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" class="btn btn-icon btn-sm btn-outline-danger btn-delete-confirm" data-name="pengguna {{ $u->name }} ({{ $u->username }})" title="Hapus Pengguna">
                                            <i class="bx bx-trash"></i>
                                        </button>
                                    </form>
                                    @endif
                                </div>

                                <!-- Modal Edit User -->
                                <div class="modal fade text-start" id="modalEditUser{{ $u->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content">
                                            <form action="{{ route('users.update', $u->id) }}" method="POST">
                                                @csrf
                                                @method('PUT')
                                                <div class="modal-header">
                                                    <h5 class="modal-title"><i class="bx bx-edit me-1 text-warning"></i> Edit Pengguna: {{ $u->name }}</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                                                        <input type="text" name="name" class="form-control" value="{{ old('name', $u->name) }}" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Username <span class="text-danger">*</span></label>
                                                        <input type="text" name="username" class="form-control" value="{{ old('username', $u->username) }}" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Email</label>
                                                        <input type="email" name="email" class="form-control" value="{{ old('email', $u->email) }}">
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">No. Telepon / HP</label>
                                                        <input type="text" name="no_telp" class="form-control" value="{{ old('no_telp', $u->no_telp) }}">
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Password Baru</label>
                                                        <input type="password" name="password" class="form-control" placeholder="Kosongkan jika tidak ingin mengubah password">
                                                        <small class="text-muted">Minimal 6 karakter jika diisi.</small>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-md-6 mb-3">
                                                            <label class="form-label">Level / Role <span class="text-danger">*</span></label>
                                                            <select name="level" class="form-select" required>
                                                                <option value="admin" {{ $u->level === 'admin' ? 'selected' : '' }}>Admin</option>
                                                                <option value="petugas" {{ $u->level === 'petugas' ? 'selected' : '' }}>Petugas</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-md-6 mb-3">
                                                            <label class="form-label">Status Akun <span class="text-danger">*</span></label>
                                                            <select name="status" class="form-select" required>
                                                                <option value="aktif" {{ $u->status === 'aktif' ? 'selected' : '' }}>Aktif</option>
                                                                <option value="nonaktif" {{ $u->status === 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                                                            </select>
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
                                <i class="bx bx-info-circle fs-3 d-block mb-1"></i>
                                Tidak ada data pengguna yang ditemukan.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer d-flex justify-content-between align-items-center py-2">
                <span class="text-muted small">Menampilkan {{ $users->firstItem() ?? 0 }} - {{ $users->lastItem() ?? 0 }} dari {{ $users->total() }} pengguna</span>
                <div>{{ $users->links('pagination::bootstrap-5') }}</div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah User -->
<div class="modal fade" id="modalTambahUser" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('users.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bx bx-user-plus me-1 text-primary"></i> Tambah Pengguna Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="Contoh: Budi Santoso" value="{{ old('name') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Username <span class="text-danger">*</span></label>
                        <input type="text" name="username" class="form-control" placeholder="Contoh: budi_perpus" value="{{ old('username') }}" required>
                        <small class="text-muted">Gunakan huruf, angka, tanda strip atau underscore.</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password <span class="text-danger">*</span></label>
                        <input type="password" name="password" class="form-control" placeholder="Minimal 6 karakter" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" placeholder="budi@example.com" value="{{ old('email') }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">No. Telepon / HP</label>
                        <input type="text" name="no_telp" class="form-control" placeholder="08xxxxxxxxxx" value="{{ old('no_telp') }}">
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Level / Role <span class="text-danger">*</span></label>
                            <select name="level" class="form-select" required>
                                <option value="admin" {{ old('level') === 'admin' ? 'selected' : '' }}>Admin</option>
                                <option value="petugas" {{ old('level') === 'petugas' ? 'selected' : '' }}>Petugas</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Status Akun <span class="text-danger">*</span></label>
                            <select name="status" class="form-select" required>
                                <option value="aktif" selected>Aktif</option>
                                <option value="nonaktif">Nonaktif</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary"><i class="bx bx-check me-1"></i> Simpan Pengguna</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
