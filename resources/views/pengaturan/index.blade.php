@extends('layouts.app')

@section('title', 'Pengaturan Perpustakaan & Denda')

@section('content')
<div class="row">
    <div class="col-12">
        <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Pengaturan /</span> Konfigurasi Sistem & Tarif Denda</h4>

        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bx bx-cog me-2 text-primary"></i>Parameter Operasional & Denda</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('pengaturan.update') }}" method="POST">
                    @csrf
                    @method('PUT')

                    <h6 class="fw-bold text-primary mb-3"><i class="bx bx-money me-1"></i> 1. Pengaturan Denda & Peminjaman</h6>
                    <div class="row mb-4">
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Nominal Denda Per Hari (Rp) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" name="nominal_per_hari" class="form-control" value="{{ old('nominal_per_hari', $pengaturan->nominal_per_hari) }}" min="0" step="500" required>
                            </div>
                            <small class="text-muted">Dikenakan setiap 1 hari keterlambatan pengembalian buku.</small>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Durasi Peminjaman Default (Hari) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" name="durasi_pinjam_default" class="form-control" value="{{ old('durasi_pinjam_default', $pengaturan->durasi_pinjam_default) }}" min="1" required>
                                <span class="input-group-text">Hari</span>
                            </div>
                            <small class="text-muted">Masa pinjam standar dari tanggal transaksi pinjam.</small>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Maksimal Buku Dipinjam Per Anggota <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" name="maks_buku_pinjam" class="form-control" value="{{ old('maks_buku_pinjam', $pengaturan->maks_buku_pinjam) }}" min="1" required>
                                <span class="input-group-text">Buku</span>
                            </div>
                            <small class="text-muted">Batas kuota peminjaman aktif per anggota.</small>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Batas Maksimal Nominal Denda (Opsional)</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" name="maks_denda" class="form-control" value="{{ old('maks_denda', $pengaturan->maks_denda) }}" min="0" step="1000" placeholder="Kosongkan jika tidak dibatasi">
                            </div>
                            <small class="text-muted">Batas plafon atas maksimal denda per transaksi.</small>
                        </div>
                    </div>

                    <hr class="my-4">

                    <h6 class="fw-bold text-primary mb-3"><i class="bx bx-building me-1"></i> 2. Informasi Perpustakaan & Header Struk / Kartu</h6>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Nama Perpustakaan / Instansi <span class="text-danger">*</span></label>
                            <input type="text" name="nama_perpustakaan" class="form-control" value="{{ old('nama_perpustakaan', $pengaturan->nama_perpustakaan) }}" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">No. Telepon / Kontak</label>
                            <input type="text" name="no_telp_perpustakaan" class="form-control" value="{{ old('no_telp_perpustakaan', $pengaturan->no_telp_perpustakaan) }}">
                        </div>

                        <div class="col-12 mb-3">
                            <label class="form-label fw-bold">Alamat Perpustakaan</label>
                            <textarea name="alamat_perpustakaan" class="form-control" rows="2">{{ old('alamat_perpustakaan', $pengaturan->alamat_perpustakaan) }}</textarea>
                        </div>
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary"><i class="bx bx-save me-1"></i> Simpan Pengaturan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
