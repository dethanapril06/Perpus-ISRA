@extends('layouts.app')

@section('title', 'Detail Buku & Eksemplar - ' . $buku->judul)

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
            <h4 class="fw-bold py-3 mb-0">
                <span class="text-muted fw-light"><a href="{{ route('buku.index') }}">Katalog Buku</a> /</span> 
                {{ $buku->judul }}
            </h4>
            <div class="d-flex gap-2">
                <a href="{{ route('buku.cetak_barcode', $buku->id) }}" target="_blank" class="btn btn-outline-info">
                    <i class="bx bx-printer me-1"></i> Cetak Semua Barcode Eksemplar
                </a>
                <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#modalEditBuku">
                    <i class="bx bx-edit me-1"></i> Edit Data Judul
                </button>
                <a href="{{ route('buku.index') }}" class="btn btn-secondary">
                    <i class="bx bx-arrow-back me-1"></i> Kembali
                </a>
            </div>
        </div>

        <div class="row">
            <!-- Info Bibliografi Card -->
            <div class="col-md-4 mb-4">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <div class="mb-3">
                            @if($buku->cover)
                                <img src="{{ asset('storage/' . $buku->cover) }}" alt="{{ $buku->judul }}" class="rounded shadow-sm" style="width: 140px; height: 190px; object-fit: cover;">
                            @else
                                <div class="rounded bg-label-secondary d-flex align-items-center justify-content-center mx-auto" style="width: 140px; height: 190px;">
                                    <i class="bx bx-book text-muted" style="font-size: 4rem;"></i>
                                </div>
                            @endif
                        </div>
                        <h5 class="mb-1 text-dark">{{ $buku->judul }}</h5>
                        <p class="text-muted mb-2">{{ $buku->pengarang ?: 'Pengarang tidak dicantumkan' }}</p>
                        
                        <div class="d-flex justify-content-center gap-2 mb-3">
                            <span class="badge bg-label-primary fs-6">{{ $buku->kategori->nama_kategori ?? 'Umum' }}</span>
                            <span class="badge bg-label-dark fs-6 font-monospace">{{ $buku->kode_induk_buku }}</span>
                        </div>

                        <!-- Ringkasan Stok Eksemplar -->
                        <div class="alert alert-primary p-3 rounded mb-3 text-start">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="small">Total Eksemplar Fisik:</span>
                                <strong class="fs-6">{{ $buku->total_eksemplar }} Buku</strong>
                            </div>
                            <div class="d-flex justify-content-between mb-1">
                                <span class="small">Stok Siap Dipinjam:</span>
                                <strong class="fs-6 text-success">{{ $buku->stok }} Tersedia</strong>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span class="small">Sedang Dipinjam:</span>
                                <strong class="fs-6 text-warning">{{ $buku->total_eksemplar - $buku->stok }} Buku</strong>
                            </div>
                        </div>

                        <ul class="list-group list-group-flush text-start small">
                            <li class="list-group-item d-flex justify-content-between">
                                <span class="text-muted">Penerbit:</span>
                                <strong>{{ $buku->penerbit ?: '-' }}</strong>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span class="text-muted">Tahun Terbit:</span>
                                <strong>{{ $buku->tahun ?: '-' }}</strong>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span class="text-muted">ISBN:</span>
                                <code>{{ $buku->isbn ?: '-' }}</code>
                            </li>
                        </ul>

                        @if($buku->deskripsi)
                        <div class="mt-3 text-start">
                            <small class="text-muted fw-bold d-block mb-1">Sinopsis / Keterangan:</small>
                            <p class="small text-muted mb-0">{{ $buku->deskripsi }}</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Panel Kelola Eksemplar Fisik (Copies) -->
            <div class="col-md-8 mb-4">
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center py-3">
                        <div>
                            <h5 class="mb-0 fw-bold text-primary"><i class="bx bx-copy me-2"></i>Daftar Eksemplar Fisik ({{ $buku->eksemplar->count() }} Fisik)</h5>
                            <small class="text-muted">Setiap eksemplar memiliki stiker barcode unik yang dapat di-scan secara presisi.</small>
                        </div>
                        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambahEksemplar">
                            <i class="bx bx-plus me-1"></i> Tambah Eksemplar
                        </button>
                    </div>

                    <div class="table-responsive text-nowrap">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th width="40">No</th>
                                    <th>Kode Barcode Eksemplar</th>
                                    <th>Lokasi Rak / Laci</th>
                                    <th>Kondisi Fisik</th>
                                    <th>Status Ketersediaan</th>
                                    <th width="120" class="text-center">Aksi & Label</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($buku->eksemplar as $index => $eks)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <span class="badge bg-label-dark font-monospace fs-6 me-2">{{ $eks->kode_eksemplar }}</span>
                                        </div>
                                        <small class="text-muted d-block">{{ $eks->no_inventaris ?: 'No Inv: -' }}</small>
                                    </td>
                                    <td>
                                        @if($eks->laci)
                                            <strong class="d-block text-dark">{{ $eks->laci->lemari->kode_lemari ?? '-' }}</strong>
                                            <small class="text-muted">{{ $eks->laci->kode_laci }} ({{ $eks->laci->deskripsi ?: 'Rak' }})</small>
                                        @else
                                            <span class="text-muted small">Belum diatur</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($eks->kondisi === 'baik')
                                            <span class="badge bg-label-success">Baik</span>
                                        @elseif($eks->kondisi === 'rusak')
                                            <span class="badge bg-label-warning">Rusak</span>
                                        @else
                                            <span class="badge bg-label-danger">Hilang</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($eks->status === 'tersedia')
                                            <span class="badge bg-success"><i class="bx bx-check me-1"></i> Tersedia</span>
                                        @elseif($eks->status === 'dipinjam')
                                            <span class="badge bg-warning"><i class="bx bx-time me-1"></i> Dipinjam</span>
                                        @elseif($eks->status === 'rusak')
                                            <span class="badge bg-danger"><i class="bx bx-error me-1"></i> Rusak</span>
                                        @else
                                            <span class="badge bg-secondary">Hilang</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="d-inline-flex gap-1">
                                            <!-- Cetak Barcode Satuan -->
                                            <a href="{{ route('buku.cetak_barcode_single', $eks->id) }}" target="_blank" class="btn btn-icon btn-sm btn-outline-info" title="Cetak Barcode Eksemplar Ini">
                                                <i class="bx bx-barcode"></i>
                                            </a>

                                            <!-- Edit Eksemplar Modal Button -->
                                            <button type="button" class="btn btn-icon btn-sm btn-outline-warning" data-bs-toggle="modal" data-bs-target="#modalEditEksemplar{{ $eks->id }}" title="Edit Eksemplar">
                                                <i class="bx bx-edit-alt"></i>
                                            </button>

                                            <!-- Hapus Eksemplar -->
                                            <form action="{{ route('buku.destroy_eksemplar', $eks->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" class="btn btn-icon btn-sm btn-outline-danger btn-delete-confirm" data-name="eksemplar fisik {{ $eks->kode_eksemplar }}" title="Hapus Eksemplar">
                                                    <i class="bx bx-trash"></i>
                                                </button>
                                            </form>
                                        </div>

                                        <!-- Modal Edit Eksemplar -->
                                        <div class="modal fade text-start" id="modalEditEksemplar{{ $eks->id }}" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered">
                                                <div class="modal-content">
                                                    <form action="{{ route('buku.update_eksemplar', $eks->id) }}" method="POST">
                                                        @csrf
                                                        @method('PUT')
                                                        <div class="modal-header">
                                                            <h5 class="modal-title"><i class="bx bx-edit me-1 text-warning"></i> Edit Eksemplar: {{ $eks->kode_eksemplar }}</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div class="mb-3">
                                                                <label class="form-label">Kode Barcode Eksemplar <span class="text-danger">*</span></label>
                                                                <input type="text" name="kode_eksemplar" class="form-control font-monospace" value="{{ old('kode_eksemplar', $eks->kode_eksemplar) }}" required>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label">Nomor Inventaris</label>
                                                                <input type="text" name="no_inventaris" class="form-control" value="{{ old('no_inventaris', $eks->no_inventaris) }}">
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label">Lokasi Rak / Laci</label>
                                                                <select name="id_laci" class="form-select">
                                                                    <option value="">-- Pilih Lokasi Rak --</option>
                                                                    @foreach($lemariList as $lem)
                                                                        <optgroup label="{{ $lem->kode_lemari }} - {{ $lem->nama_lokasi }}">
                                                                            @foreach($lem->laci as $lac)
                                                                                <option value="{{ $lac->id }}" {{ (old('id_laci', $eks->id_laci) == $lac->id) ? 'selected' : '' }}>
                                                                                    {{ $lac->kode_laci }} ({{ $lac->deskripsi ?: 'Rak' }})
                                                                                </option>
                                                                            @endforeach
                                                                        </optgroup>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                            <div class="row">
                                                                <div class="col-md-6 mb-3">
                                                                    <label class="form-label">Kondisi Fisik <span class="text-danger">*</span></label>
                                                                    <select name="kondisi" class="form-select" required>
                                                                        <option value="baik" {{ $eks->kondisi === 'baik' ? 'selected' : '' }}>Baik</option>
                                                                        <option value="rusak" {{ $eks->kondisi === 'rusak' ? 'selected' : '' }}>Rusak</option>
                                                                        <option value="hilang" {{ $eks->kondisi === 'hilang' ? 'selected' : '' }}>Hilang</option>
                                                                    </select>
                                                                </div>
                                                                <div class="col-md-6 mb-3">
                                                                    <label class="form-label">Status Ketersediaan <span class="text-danger">*</span></label>
                                                                    <select name="status" class="form-select" required>
                                                                        <option value="tersedia" {{ $eks->status === 'tersedia' ? 'selected' : '' }}>Tersedia</option>
                                                                        <option value="dipinjam" {{ $eks->status === 'dipinjam' ? 'selected' : '' }}>Dipinjam</option>
                                                                        <option value="rusak" {{ $eks->status === 'rusak' ? 'selected' : '' }}>Rusak</option>
                                                                        <option value="hilang" {{ $eks->status === 'hilang' ? 'selected' : '' }}>Hilang</option>
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
                                    <td colspan="6" class="text-center py-3 text-muted">Belum ada eksemplar fisik untuk buku ini.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Riwayat Peminjaman Seluruh Eksemplar Buku Ini -->
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center py-3">
                        <h6 class="mb-0 fw-bold"><i class="bx bx-history me-2 text-info"></i>Riwayat Sirkulasi Peminjaman Buku Ini</h6>
                    </div>
                    <div class="table-responsive text-nowrap">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Eksemplar</th>
                                    <th>Peminjam</th>
                                    <th>Tgl Pinjam</th>
                                    <th>Jatuh Tempo</th>
                                    <th>Tgl Kembali</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $hasLoans = false; @endphp
                                @foreach($buku->eksemplar as $e)
                                    @foreach($e->peminjaman as $p)
                                        @php $hasLoans = true; @endphp
                                        <tr>
                                            <td><span class="badge bg-label-dark font-monospace">{{ $e->kode_eksemplar }}</span></td>
                                            <td>{{ $p->anggota->nama ?? '-' }}</td>
                                            <td>{{ $p->tgl_pinjam->format('d/m/Y') }}</td>
                                            <td>{{ $p->tgl_jatuh_tempo->format('d/m/Y') }}</td>
                                            <td>{{ $p->tgl_kembali ? $p->tgl_kembali->format('d/m/Y') : '-' }}</td>
                                            <td>
                                                @if($p->status === 'Dipinjam')
                                                    <span class="badge bg-label-warning">Dipinjam</span>
                                                @elseif($p->status === 'Selesai')
                                                    <span class="badge bg-label-success">Selesai</span>
                                                @else
                                                    <span class="badge bg-label-danger">Terlambat</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                @endforeach
                                @if(!$hasLoans)
                                    <tr>
                                        <td colspan="6" class="text-center py-3 text-muted">Belum ada riwayat peminjaman untuk eksemplar buku ini.</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah 1 Eksemplar Baru -->
<div class="modal fade" id="modalTambahEksemplar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('buku.store_eksemplar', $buku->id) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bx bx-plus-circle me-1 text-primary"></i> Tambah Eksemplar Fisik Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Kode Barcode Eksemplar <span class="text-danger">*</span></label>
                        <input type="text" name="kode_eksemplar" class="form-control font-monospace" value="{{ old('kode_eksemplar', $nextCopyCode) }}" required>
                        <small class="text-muted">Kode barcode unik yang akan dicetak pada stiker fisik buku.</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nomor Inventaris</label>
                        <input type="text" name="no_inventaris" class="form-control" placeholder="Contoh: INV/TI/2026/005" value="{{ old('no_inventaris') }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Lokasi Rak / Laci</label>
                        <select name="id_laci" class="form-select">
                            <option value="">-- Pilih Lokasi Rak --</option>
                            @foreach($lemariList as $lem)
                                <optgroup label="{{ $lem->kode_lemari }} - {{ $lem->nama_lokasi }}">
                                    @foreach($lem->laci as $lac)
                                        <option value="{{ $lac->id }}" {{ old('id_laci') == $lac->id ? 'selected' : '' }}>
                                            {{ $lac->kode_laci }} ({{ $lac->deskripsi ?: 'Rak' }})
                                        </option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Kondisi Fisik <span class="text-danger">*</span></label>
                            <select name="kondisi" class="form-select" required>
                                <option value="baik" selected>Baik</option>
                                <option value="rusak">Rusak</option>
                                <option value="hilang">Hilang</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Status Ketersediaan <span class="text-danger">*</span></label>
                            <select name="status" class="form-select" required>
                                <option value="tersedia" selected>Tersedia</option>
                                <option value="dipinjam">Dipinjam</option>
                                <option value="rusak">Rusak</option>
                                <option value="hilang">Hilang</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary"><i class="bx bx-check me-1"></i> Simpan Eksemplar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit Bibliografi Judul Buku -->
<div class="modal fade" id="modalEditBuku" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <form action="{{ route('buku.update', $buku->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bx bx-edit me-1 text-warning"></i> Edit Judul Buku: {{ $buku->judul }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Kode Induk Katalog <span class="text-danger">*</span></label>
                            <input type="text" name="kode_induk_buku" class="form-control font-monospace" value="{{ old('kode_induk_buku', $buku->kode_induk_buku) }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">ISBN</label>
                            <input type="text" name="isbn" class="form-control" value="{{ old('isbn', $buku->isbn) }}">
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label">Judul Buku <span class="text-danger">*</span></label>
                            <input type="text" name="judul" class="form-control" value="{{ old('judul', $buku->judul) }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Pengarang</label>
                            <input type="text" name="pengarang" class="form-control" value="{{ old('pengarang', $buku->pengarang) }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Penerbit</label>
                            <input type="text" name="penerbit" class="form-control" value="{{ old('penerbit', $buku->penerbit) }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tahun Terbit</label>
                            <input type="number" name="tahun" class="form-control" value="{{ old('tahun', $buku->tahun) }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Kategori <span class="text-danger">*</span></label>
                            <select name="id_kategori" class="form-select" required>
                                @foreach(\App\Models\Kategori::all() as $kat)
                                    <option value="{{ $kat->id }}" {{ (old('id_kategori', $buku->id_kategori) == $kat->id) ? 'selected' : '' }}>
                                        {{ $kat->nama_kategori }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Upload Cover Baru</label>
                            <input type="file" name="cover" class="form-control" accept="image/*">
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label">Deskripsi / Sinopsis</label>
                            <textarea name="deskripsi" class="form-control" rows="3">{{ old('deskripsi', $buku->deskripsi) }}</textarea>
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
@endsection
