@extends('layouts.app')

@section('title', 'Katalog Buku & Eksemplar')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
            <h4 class="fw-bold py-3 mb-0"><span class="text-muted fw-light">Master Data /</span> Katalog Buku & Eksemplar</h4>
            <div class="d-flex gap-2 flex-wrap">
                <button type="button" class="btn btn-outline-success" data-bs-toggle="modal" data-bs-target="#modalImportBuku">
                    <i class="bx bx-upload me-1"></i> Impor Excel (.xlsx)
                </button>
                <a href="{{ route('buku.cetak_semua_barcode', ['id_kategori' => request('id_kategori')]) }}" target="_blank" class="btn btn-outline-primary">
                    <i class="bx bx-barcode me-1"></i> Cetak Label Seluruh Eksemplar
                </a>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambahBuku">
                    <i class="bx bx-plus me-1"></i> Tambah Buku Baru
                </button>
            </div>
        </div>

        <!-- Filter -->
        <div class="card mb-4">
            <div class="card-body">
                <form action="{{ route('buku.index') }}" method="GET" class="row g-3">
                    <div class="col-md-7">
                        <div class="input-group">
                            <span class="input-group-text"><i class="bx bx-search"></i></span>
                            <input type="text" name="search" class="form-control" placeholder="Cari Kode Induk, Kode Eksemplar, Judul, Pengarang, ISBN..." value="{{ request('search') }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <select name="id_kategori" class="form-select">
                            <option value="">-- Semua Kategori --</option>
                            @foreach($kategoriList as $k)
                                <option value="{{ $k->id }}" {{ request('id_kategori') == $k->id ? 'selected' : '' }}>{{ $k->nama_kategori }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 d-flex gap-2">
                        <button type="submit" class="btn btn-primary w-100"><i class="bx bx-filter-alt"></i> Filter</button>
                        @if(request()->hasAny(['search', 'id_kategori']))
                            <a href="{{ route('buku.index') }}" class="btn btn-outline-secondary"><i class="bx bx-reset"></i></a>
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
                            <th width="50">No</th>
                            <th>Cover & Kode Induk</th>
                            <th>Judul & Pengarang</th>
                            <th>Kategori</th>
                            <th>Total Eksemplar</th>
                            <th>Stok Tersedia</th>
                            <th width="160" class="text-center">Kelola & Barcode</th>
                        </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
                        @forelse($buku as $index => $b)
                        <tr>
                            <td>{{ $buku->firstItem() + $index }}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    @if($b->cover)
                                        <img src="{{ asset('storage/' . $b->cover) }}" alt="{{ $b->judul }}" class="rounded me-2 shadow-xs" style="width: 42px; height: 56px; object-fit: cover;">
                                    @else
                                        <div class="rounded bg-label-secondary d-flex align-items-center justify-content-center me-2" style="width: 42px; height: 56px;">
                                            <i class="bx bx-book text-muted fs-4"></i>
                                        </div>
                                    @endif
                                    <div>
                                        <span class="badge bg-label-dark font-monospace">{{ $b->kode_induk_buku }}</span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <a href="{{ route('buku.show', $b->id) }}" class="fw-bold text-primary d-block text-wrap" style="max-width: 280px;">
                                    {{ $b->judul }}
                                </a>
                                <small class="text-muted d-block">{{ $b->pengarang ?: 'Pengarang: -' }} • {{ $b->tahun ?: '-' }}</small>
                            </td>
                            <td>
                                <span class="badge bg-label-primary">{{ $b->kategori->nama_kategori ?? 'Umum' }}</span>
                            </td>
                            <td>
                                <span class="badge bg-label-secondary fs-6">
                                    <i class="bx bx-copy me-1"></i> {{ $b->total_eksemplar }} Fisik
                                </span>
                            </td>
                            <td>
                                @if($b->stok > 0)
                                    <span class="badge bg-label-success fs-6 fw-bold">
                                        <i class="bx bx-check me-1"></i> {{ $b->stok }} Tersedia
                                    </span>
                                @else
                                    <span class="badge bg-label-danger fs-6 fw-bold">
                                        <i class="bx bx-x me-1"></i> Habis Dipinjam
                                    </span>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="d-inline-flex gap-1">
                                    <!-- Cetak Semua Barcode Eksemplar -->
                                    <a href="{{ route('buku.cetak_barcode', $b->id) }}" target="_blank" class="btn btn-icon btn-sm btn-outline-info" title="Cetak Semua Barcode Eksemplar">
                                        <i class="bx bx-barcode"></i>
                                    </a>

                                    <!-- Detail & Kelola Eksemplar -->
                                    <a href="{{ route('buku.show', $b->id) }}" class="btn btn-sm btn-outline-primary" title="Kelola Eksemplar Fisik">
                                        <i class="bx bx-layer me-1"></i> Eksemplar
                                    </a>

                                    <!-- Hapus Judul Buku -->
                                    <form action="{{ route('buku.destroy', $b->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" class="btn btn-icon btn-sm btn-outline-danger btn-delete-confirm" data-name="buku '{{ $b->judul }}' beserta seluruh eksemplarnya" title="Hapus Judul Buku">
                                            <i class="bx bx-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">
                                <i class="bx bx-book-open fs-3 d-block mb-1"></i>
                                Belum ada data buku dalam katalog.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer d-flex justify-content-between align-items-center py-2">
                <span class="text-muted small">Menampilkan {{ $buku->firstItem() ?? 0 }} - {{ $buku->lastItem() ?? 0 }} dari {{ $buku->total() }} judul buku</span>
                <div>{{ $buku->links('pagination::bootstrap-5') }}</div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah Buku Baru & Auto-generate Eksemplar -->
<div class="modal fade" id="modalTambahBuku" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <form action="{{ route('buku.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bx bx-plus-circle me-1 text-primary"></i> Tambah Judul Buku & Eksemplar Fisik</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Kode Induk Katalog <span class="text-danger">*</span></label>
                            <input type="text" name="kode_induk_buku" class="form-control font-monospace" value="{{ old('kode_induk_buku', $nextBookCode) }}" required>
                            <small class="text-muted">Kode induk buku (eksemplar akan bernomor .01, .02, dst).</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">ISBN</label>
                            <input type="text" name="isbn" class="form-control" placeholder="Contoh: 978-602-001-112-3" value="{{ old('isbn') }}">
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label">Judul Buku <span class="text-danger">*</span></label>
                            <input type="text" name="judul" class="form-control" placeholder="Masukkan judul lengkap buku" value="{{ old('judul') }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Pengarang / Penulis</label>
                            <input type="text" name="pengarang" class="form-control" placeholder="Nama penulis / pengarang" value="{{ old('pengarang') }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Penerbit</label>
                            <input type="text" name="penerbit" class="form-control" placeholder="Nama penerbit buku" value="{{ old('penerbit') }}">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Tahun Terbit</label>
                            <input type="number" name="tahun" class="form-control" placeholder="{{ date('Y') }}" value="{{ old('tahun', date('Y')) }}" min="1900" max="{{ date('Y') + 1 }}">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Kategori Buku <span class="text-danger">*</span></label>
                            <select name="id_kategori" class="form-select" required>
                                <option value="">-- Pilih Kategori --</option>
                                @foreach($kategoriList as $k)
                                    <option value="{{ $k->id }}" {{ old('id_kategori') == $k->id ? 'selected' : '' }}>{{ $k->nama_kategori }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Lokasi Rak / Laci Default</label>
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

                        <!-- AUTO-GENERATE EKSEMPLAR FISIK -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold text-primary">Jumlah Eksemplar Fisik yang Dibuat <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-primary text-white"><i class="bx bx-copy"></i></span>
                                <input type="number" name="jumlah_eksemplar" class="form-control fw-bold" value="{{ old('jumlah_eksemplar', 4) }}" min="1" max="50" required>
                                <span class="input-group-text">Buku Fisik</span>
                            </div>
                            <small class="text-muted">Sistem akan otomatis men-generate barcode unik untuk masing-masing eksemplar fisik.</small>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Cover Buku (Opsional)</label>
                            <input type="file" name="cover" class="form-control" accept="image/*">
                        </div>

                        <div class="col-12 mb-3">
                            <label class="form-label">Deskripsi / Sinopsis Buku (Opsional)</label>
                            <textarea name="deskripsi" class="form-control" rows="2" placeholder="Sinopsis atau ringkasan isi buku">{{ old('deskripsi') }}</textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary"><i class="bx bx-check me-1"></i> Simpan & Buat Eksemplar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Impor Data Buku & Eksemplar dari Excel -->
<div class="modal fade" id="modalImportBuku" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('buku.import_excel') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header bg-label-success">
                    <h5 class="modal-title fw-bold text-success"><i class="bx bx-file me-1"></i> Impor Buku & Eksemplar (Excel .xlsx)</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Banner Unduh Template -->
                    <div class="card bg-label-primary p-3 mb-3 border-0">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <h6 class="fw-bold mb-1 text-primary"><i class="bx bx-spreadsheet me-1"></i> Template Excel Per-Eksemplar</h6>
                                <small class="text-muted d-block">1 baris Excel = 1 eksemplar fisik buku perpustakaan.</small>
                            </div>
                            <a href="{{ route('buku.template_excel') }}" class="btn btn-sm btn-primary">
                                <i class="bx bx-download me-1"></i> Unduh (.xlsx)
                            </a>
                        </div>
                    </div>

                    <!-- Petunjuk Pengisian -->
                    <div class="alert alert-secondary py-2 px-3 small mb-3">
                        <div class="fw-semibold text-dark mb-1"><i class="bx bx-info-circle me-1 text-primary"></i> Aturan Pengisian Eksemplar:</div>
                        <ul class="mb-0 ps-3">
                            <li>Setiap baris mewakili <strong>1 fisik buku</strong> (1 eksemplar).</li>
                            <li>Jika memiliki 3 eksemplar dari buku yang sama, tuliskan 3 baris dengan <strong>Judul</strong> yang sama.</li>
                            <li><strong>Kode Induk</strong> &amp; <strong>Kode Eksemplar</strong> boleh dikosongkan (sistem otomatis membuatkan nomor urut).</li>
                            <li>Kategori buku baru akan otomatis dibuat jika belum terdaftar.</li>
                        </ul>
                    </div>

                    <!-- Pilih File -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">Pilih File Spreadsheet Excel (.xlsx) <span class="text-danger">*</span></label>
                        <input type="file" name="file_excel" class="form-control" accept=".xlsx" required>
                        <small class="text-muted">Hanya file berekstensi <strong>.xlsx</strong> (Maksimal 10 MB).</small>
                    </div>

                    <!-- Pilihan Penanganan Duplikasi Barcode Eksemplar -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">Jika Kode Eksemplar / Barcode Sudah Ada:</label>
                        <div class="form-check mb-1">
                            <input class="form-check-input" type="radio" name="mode_duplikat" id="modeBukuSkip" value="skip" checked>
                            <label class="form-check-label" for="modeBukuSkip">
                                <strong>Lewati (Skip)</strong> - Eksemplar yang sudah ada tidak akan diubah/ditimpa.
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="mode_duplikat" id="modeBukuUpdate" value="update">
                            <label class="form-check-label" for="modeBukuUpdate">
                                <strong>Perbarui (Update)</strong> - Data lokasi laci, kondisi, dan status eksemplar lama akan diperbarui.
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success"><i class="bx bx-upload me-1"></i> Mulai Impor Data</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
