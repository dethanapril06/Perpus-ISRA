@extends('layouts.app')

@section('title', 'Master Data Laci')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-bold py-3 mb-0"><span class="text-muted fw-light">Master Lokasi /</span> Data Laci (Sub-Rak)</h4>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambahLaci">
                <i class="bx bx-plus me-1"></i> Tambah Laci
            </button>
        </div>

        <!-- Filter -->
        <div class="card mb-4">
            <div class="card-body">
                <form action="{{ route('laci.index') }}" method="GET" class="row g-3">
                    <div class="col-md-6">
                        <div class="input-group">
                            <span class="input-group-text"><i class="bx bx-search"></i></span>
                            <input type="text" name="search" class="form-control" placeholder="Cari kode laci, keterangan..." value="{{ request('search') }}">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <select name="id_lemari" class="form-select">
                            <option value="">-- Semua Lemari Induk --</option>
                            @foreach($lemariList as $lem)
                                <option value="{{ $lem->id }}" {{ request('id_lemari') == $lem->id ? 'selected' : '' }}>
                                    {{ $lem->kode_lemari }} - {{ $lem->nama_lokasi }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 d-flex gap-2">
                        <button type="submit" class="btn btn-primary w-100"><i class="bx bx-filter-alt"></i> Filter</button>
                        @if(request()->hasAny(['search', 'id_lemari']))
                            <a href="{{ route('laci.index') }}" class="btn btn-outline-secondary"><i class="bx bx-reset"></i></a>
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
                            <th>Kode Laci</th>
                            <th>Lemari Induk</th>
                            <th>Jumlah Buku</th>
                            <th>Keterangan / Deskripsi Isi</th>
                            <th width="120" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
                        @forelse($laci as $index => $item)
                        <tr>
                            <td>{{ $laci->firstItem() + $index }}</td>
                            <td><span class="badge bg-label-info fs-6">{{ $item->kode_laci }}</span></td>
                            <td>
                                <strong>{{ $item->lemari->kode_lemari ?? '-' }}</strong> - 
                                <span class="text-muted">{{ $item->lemari->nama_lokasi ?? '-' }}</span>
                            </td>
                            <td>
                                <span class="badge bg-label-primary">
                                    <i class="bx bx-book me-1"></i> {{ $item->buku_count }} Buku
                                </span>
                            </td>
                            <td class="text-wrap">{{ $item->deskripsi ?: '-' }}</td>
                            <td class="text-center">
                                <div class="d-inline-flex gap-1">
                                    <button type="button" class="btn btn-icon btn-sm btn-outline-warning" data-bs-toggle="modal" data-bs-target="#modalEditLaci{{ $item->id }}" title="Edit Laci">
                                        <i class="bx bx-edit-alt"></i>
                                    </button>

                                    <form action="{{ route('laci.destroy', $item->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" class="btn btn-icon btn-sm btn-outline-danger btn-delete-confirm" data-name="laci {{ $item->kode_laci }}" title="Hapus Laci">
                                            <i class="bx bx-trash"></i>
                                        </button>
                                    </form>
                                </div>

                                <!-- Modal Edit Laci -->
                                <div class="modal fade text-start" id="modalEditLaci{{ $item->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content">
                                            <form action="{{ route('laci.update', $item->id) }}" method="POST">
                                                @csrf
                                                @method('PUT')
                                                <div class="modal-header">
                                                    <h5 class="modal-title"><i class="bx bx-edit me-1 text-warning"></i> Edit Laci: {{ $item->kode_laci }}</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <label class="form-label">Kode Laci <span class="text-danger">*</span></label>
                                                        <input type="text" name="kode_laci" class="form-control" value="{{ old('kode_laci', $item->kode_laci) }}" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Lemari Induk <span class="text-danger">*</span></label>
                                                        <select name="id_lemari" class="form-select" required>
                                                            @foreach($lemariList as $lem)
                                                                <option value="{{ $lem->id }}" {{ (old('id_lemari', $item->id_lemari) == $lem->id) ? 'selected' : '' }}>
                                                                    {{ $lem->kode_lemari }} - {{ $lem->nama_lokasi }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Deskripsi / Keterangan</label>
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
                                Belum ada data laci.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer d-flex justify-content-between align-items-center py-2">
                <span class="text-muted small">Menampilkan {{ $laci->firstItem() ?? 0 }} - {{ $laci->lastItem() ?? 0 }} dari {{ $laci->total() }} laci</span>
                <div>{{ $laci->links('pagination::bootstrap-5') }}</div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah Laci -->
<div class="modal fade" id="modalTambahLaci" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('laci.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bx bx-plus-circle me-1 text-primary"></i> Tambah Laci Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Kode Laci <span class="text-danger">*</span></label>
                        <input type="text" name="kode_laci" class="form-control" placeholder="Contoh: LAC-A1" value="{{ old('kode_laci') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Lemari Induk <span class="text-danger">*</span></label>
                        <select name="id_lemari" class="form-select" required>
                            <option value="">-- Pilih Lemari Induk --</option>
                            @foreach($lemariList as $lem)
                                <option value="{{ $lem->id }}" {{ old('id_lemari') == $lem->id ? 'selected' : '' }}>
                                    {{ $lem->kode_lemari }} - {{ $lem->nama_lokasi }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Deskripsi / Keterangan</label>
                        <textarea name="deskripsi" class="form-control" rows="3" placeholder="Keterangan peruntukan rak/laci">{{ old('deskripsi') }}</textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary"><i class="bx bx-check me-1"></i> Simpan Laci</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
