@extends('layouts.app')

@section('title', 'Master Data Lemari')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-bold py-3 mb-0"><span class="text-muted fw-light">Master Lokasi /</span> Data Lemari</h4>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambahLemari">
                <i class="bx bx-plus me-1"></i> Tambah Lemari
            </button>
        </div>

        <!-- Filter -->
        <div class="card mb-4">
            <div class="card-body">
                <form action="{{ route('lemari.index') }}" method="GET" class="row g-3">
                    <div class="col-md-10">
                        <div class="input-group">
                            <span class="input-group-text"><i class="bx bx-search"></i></span>
                            <input type="text" name="search" class="form-control" placeholder="Cari kode lemari, nama lokasi, deskripsi..." value="{{ request('search') }}">
                        </div>
                    </div>
                    <div class="col-md-2 d-flex gap-2">
                        <button type="submit" class="btn btn-primary w-100"><i class="bx bx-filter-alt"></i> Cari</button>
                        @if(request('search'))
                            <a href="{{ route('lemari.index') }}" class="btn btn-outline-secondary"><i class="bx bx-reset"></i></a>
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
                            <th>Kode Lemari</th>
                            <th>Nama / Lokasi Lemari</th>
                            <th>Jumlah Laci</th>
                            <th>Deskripsi / Keterangan</th>
                            <th width="120" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
                        @forelse($lemari as $index => $item)
                        <tr>
                            <td>{{ $lemari->firstItem() + $index }}</td>
                            <td><span class="badge bg-label-primary fs-6">{{ $item->kode_lemari }}</span></td>
                            <td><strong class="text-dark">{{ $item->nama_lokasi }}</strong></td>
                            <td>
                                <a href="{{ route('laci.index', ['id_lemari' => $item->id]) }}" class="badge bg-label-info">
                                    <i class="bx bx-cabinet me-1"></i> {{ $item->laci_count }} Laci
                                </a>
                            </td>
                            <td class="text-wrap">{{ $item->deskripsi ?: '-' }}</td>
                            <td class="text-center">
                                <div class="d-inline-flex gap-1">
                                    <button type="button" class="btn btn-icon btn-sm btn-outline-warning" data-bs-toggle="modal" data-bs-target="#modalEditLemari{{ $item->id }}" title="Edit Lemari">
                                        <i class="bx bx-edit-alt"></i>
                                    </button>

                                    <form action="{{ route('lemari.destroy', $item->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" class="btn btn-icon btn-sm btn-outline-danger btn-delete-confirm" data-name="lemari {{ $item->kode_lemari }} ({{ $item->nama_lokasi }})" title="Hapus Lemari">
                                            <i class="bx bx-trash"></i>
                                        </button>
                                    </form>
                                </div>

                                <!-- Modal Edit Lemari -->
                                <div class="modal fade text-start" id="modalEditLemari{{ $item->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content">
                                            <form action="{{ route('lemari.update', $item->id) }}" method="POST">
                                                @csrf
                                                @method('PUT')
                                                <div class="modal-header">
                                                    <h5 class="modal-title"><i class="bx bx-edit me-1 text-warning"></i> Edit Lemari: {{ $item->kode_lemari }}</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <label class="form-label">Kode Lemari <span class="text-danger">*</span></label>
                                                        <input type="text" name="kode_lemari" class="form-control" value="{{ old('kode_lemari', $item->kode_lemari) }}" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Nama / Lokasi Lemari <span class="text-danger">*</span></label>
                                                        <input type="text" name="nama_lokasi" class="form-control" value="{{ old('nama_lokasi', $item->nama_lokasi) }}" required>
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
                                <i class="bx bx-cabinet fs-3 d-block mb-1"></i>
                                Belum ada data lemari.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer d-flex justify-content-between align-items-center py-2">
                <span class="text-muted small">Menampilkan {{ $lemari->firstItem() ?? 0 }} - {{ $lemari->lastItem() ?? 0 }} dari {{ $lemari->total() }} lemari</span>
                <div>{{ $lemari->links('pagination::bootstrap-5') }}</div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah Lemari -->
<div class="modal fade" id="modalTambahLemari" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('lemari.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bx bx-plus-circle me-1 text-primary"></i> Tambah Lemari Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Kode Lemari <span class="text-danger">*</span></label>
                        <input type="text" name="kode_lemari" class="form-control" placeholder="Contoh: LMR-01" value="{{ old('kode_lemari') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nama / Lokasi Lemari <span class="text-danger">*</span></label>
                        <input type="text" name="nama_lokasi" class="form-control" placeholder="Contoh: Lemari A (Lantai 1 - Sayap Kiri)" value="{{ old('nama_lokasi') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Deskripsi / Keterangan</label>
                        <textarea name="deskripsi" class="form-control" rows="3" placeholder="Keterangan peruntukan lemari">{{ old('deskripsi') }}</textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary"><i class="bx bx-check me-1"></i> Simpan Lemari</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
