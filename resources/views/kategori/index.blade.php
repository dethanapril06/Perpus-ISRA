@extends('layouts.app')

@section('title', 'Master Kategori Buku')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-bold py-3 mb-0"><span class="text-muted fw-light">Master Data /</span> Kategori Buku</h4>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambahKategori">
                <i class="bx bx-plus me-1"></i> Tambah Kategori
            </button>
        </div>

        <!-- Filter -->
        <div class="card mb-4">
            <div class="card-body">
                <form action="{{ route('kategori.index') }}" method="GET" class="row g-3">
                    <div class="col-md-10">
                        <div class="input-group">
                            <span class="input-group-text"><i class="bx bx-search"></i></span>
                            <input type="text" name="search" class="form-control" placeholder="Cari kode kategori, nama kategori, deskripsi..." value="{{ request('search') }}">
                        </div>
                    </div>
                    <div class="col-md-2 d-flex gap-2">
                        <button type="submit" class="btn btn-primary w-100"><i class="bx bx-filter-alt"></i> Cari</button>
                        @if(request('search'))
                            <a href="{{ route('kategori.index') }}" class="btn btn-outline-secondary"><i class="bx bx-reset"></i></a>
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
                            <th width="60">No</th>
                            <th>Kode Kategori</th>
                            <th>Nama Kategori</th>
                            <th>Jumlah Buku</th>
                            <th>Deskripsi</th>
                            <th width="120" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
                        @forelse($kategori as $index => $item)
                        <tr>
                            <td>{{ $kategori->firstItem() + $index }}</td>
                            <td><span class="badge bg-label-primary fs-6">{{ $item->kode_kategori }}</span></td>
                            <td><strong>{{ $item->nama_kategori }}</strong></td>
                            <td>
                                <a href="{{ route('buku.index', ['id_kategori' => $item->id]) }}" class="badge bg-label-info">
                                    <i class="bx bx-book me-1"></i> {{ $item->buku_count }} Buku
                                </a>
                            </td>
                            <td class="text-wrap">{{ $item->deskripsi ?: '-' }}</td>
                            <td class="text-center">
                                <div class="d-inline-flex gap-1">
                                    <button type="button" class="btn btn-icon btn-sm btn-outline-warning" data-bs-toggle="modal" data-bs-target="#modalEditKategori{{ $item->id }}" title="Edit Kategori">
                                        <i class="bx bx-edit-alt"></i>
                                    </button>

                                    <form action="{{ route('kategori.destroy', $item->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" class="btn btn-icon btn-sm btn-outline-danger btn-delete-confirm" data-name="kategori {{ $item->nama_kategori }} ({{ $item->kode_kategori }})" title="Hapus Kategori">
                                            <i class="bx bx-trash"></i>
                                        </button>
                                    </form>
                                </div>

                                <!-- Modal Edit Kategori -->
                                <div class="modal fade text-start" id="modalEditKategori{{ $item->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content">
                                            <form action="{{ route('kategori.update', $item->id) }}" method="POST">
                                                @csrf
                                                @method('PUT')
                                                <div class="modal-header">
                                                    <h5 class="modal-title"><i class="bx bx-edit me-1 text-warning"></i> Edit Kategori: {{ $item->nama_kategori }}</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <label class="form-label">Kode Kategori <span class="text-danger">*</span></label>
                                                        <input type="text" name="kode_kategori" class="form-control" value="{{ old('kode_kategori', $item->kode_kategori) }}" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Nama Kategori <span class="text-danger">*</span></label>
                                                        <input type="text" name="nama_kategori" class="form-control" value="{{ old('nama_kategori', $item->nama_kategori) }}" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Deskripsi</label>
                                                        <textarea name="deskripsi" class="form-control" rows="3">{{ old('deskripsi', $item->deskripsi) }}</textarea>
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
                            <td colspan="6" class="text-center py-4 text-muted">
                                <i class="bx bx-category fs-3 d-block mb-1"></i>
                                Belum ada data kategori.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer d-flex justify-content-between align-items-center py-2">
                <span class="text-muted small">Menampilkan {{ $kategori->firstItem() ?? 0 }} - {{ $kategori->lastItem() ?? 0 }} dari {{ $kategori->total() }} kategori</span>
                <div>{{ $kategori->links('pagination::bootstrap-5') }}</div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah Kategori -->
<div class="modal fade" id="modalTambahKategori" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('kategori.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bx bx-plus-circle me-1 text-primary"></i> Tambah Kategori Buku</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Kode Kategori <span class="text-danger">*</span></label>
                        <input type="text" name="kode_kategori" class="form-control" placeholder="Contoh: KAT-005" value="{{ old('kode_kategori') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nama Kategori <span class="text-danger">*</span></label>
                        <input type="text" name="nama_kategori" class="form-control" placeholder="Contoh: Novel & Fiksi" value="{{ old('nama_kategori') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Deskripsi</label>
                        <textarea name="deskripsi" class="form-control" rows="3" placeholder="Keterangan singkat kategori">{{ old('deskripsi') }}</textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary"><i class="bx bx-check me-1"></i> Simpan Kategori</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
