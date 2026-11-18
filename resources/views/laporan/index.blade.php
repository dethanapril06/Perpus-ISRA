@extends('layouts.app')

@section('title', 'Laporan Perpustakaan')

@section('content')
<div class="row">
    <div class="col-12">
        <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Laporan & Rekap /</span> Laporan Operasional Perpustakaan</h4>

        <!-- Tabs Navigation -->
        <div class="nav-align-top mb-4">
            <ul class="nav nav-tabs nav-fill" role="tablist">
                <li class="nav-item">
                    <a class="nav-link {{ $tab === 'peminjaman' ? 'active' : '' }}" href="{{ route('laporan.index', ['tab' => 'peminjaman']) }}">
                        <i class="tf-icons bx bx-book-bookmark me-1"></i> 1. Peminjaman
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $tab === 'pengembalian' ? 'active' : '' }}" href="{{ route('laporan.index', ['tab' => 'pengembalian']) }}">
                        <i class="tf-icons bx bx-book-content me-1"></i> 2. Pengembalian
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $tab === 'denda' ? 'active' : '' }}" href="{{ route('laporan.index', ['tab' => 'denda']) }}">
                        <i class="tf-icons bx bx-money me-1"></i> 3. Denda
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $tab === 'kunjungan' ? 'active' : '' }}" href="{{ route('laporan.index', ['tab' => 'kunjungan']) }}">
                        <i class="tf-icons bx bx-walk me-1"></i> 4. Kunjungan
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $tab === 'buku' ? 'active' : '' }}" href="{{ route('laporan.index', ['tab' => 'buku']) }}">
                        <i class="tf-icons bx bx-book me-1"></i> 5. Stok Buku
                    </a>
                </li>
            </ul>

            <div class="card border-top-0 rounded-top-0">
                <div class="card-body">
                    <!-- Filter Box -->
                    <form action="{{ route('laporan.index') }}" method="GET" class="row g-3 align-items-end mb-4">
                        <input type="hidden" name="tab" value="{{ $tab }}">
                        
                        @if($tab !== 'buku')
                        <div class="col-md-3">
                            <label class="form-label">Dari Tanggal</label>
                            <input type="date" name="tgl_mulai" class="form-control" value="{{ $tglMulai }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Sampai Tanggal</label>
                            <input type="date" name="tgl_selesai" class="form-control" value="{{ $tglSelesai }}">
                        </div>
                        @endif

                        @if($tab === 'peminjaman')
                        <div class="col-md-3">
                            <label class="form-label">Status Peminjaman</label>
                            <select name="status_pinjam" class="form-select">
                                <option value="">-- Semua Status --</option>
                                <option value="Dipinjam" {{ request('status_pinjam') === 'Dipinjam' ? 'selected' : '' }}>Dipinjam</option>
                                <option value="Selesai" {{ request('status_pinjam') === 'Selesai' ? 'selected' : '' }}>Selesai</option>
                            </select>
                        </div>
                        @elseif($tab === 'pengembalian')
                        <div class="col-md-3">
                            <label class="form-label">Kondisi Buku</label>
                            <select name="kondisi_kembali" class="form-select">
                                <option value="">-- Semua Kondisi --</option>
                                <option value="baik" {{ request('kondisi_kembali') === 'baik' ? 'selected' : '' }}>Baik</option>
                                <option value="rusak" {{ request('kondisi_kembali') === 'rusak' ? 'selected' : '' }}>Rusak</option>
                                <option value="hilang" {{ request('kondisi_kembali') === 'hilang' ? 'selected' : '' }}>Hilang</option>
                            </select>
                        </div>
                        @elseif($tab === 'denda')
                        <div class="col-md-3">
                            <label class="form-label">Status Pembayaran</label>
                            <select name="status_bayar" class="form-select">
                                <option value="">-- Semua Status --</option>
                                <option value="Belum Lunas" {{ request('status_bayar') === 'Belum Lunas' ? 'selected' : '' }}>Belum Lunas</option>
                                <option value="Lunas" {{ request('status_bayar') === 'Lunas' ? 'selected' : '' }}>Lunas</option>
                            </select>
                        </div>
                        @elseif($tab === 'kunjungan')
                        <div class="col-md-3">
                            <label class="form-label">Status Kunjungan</label>
                            <select name="status_kunjungan" class="form-select">
                                <option value="">-- Semua Status --</option>
                                <option value="Berlangsung" {{ request('status_kunjungan') === 'Berlangsung' ? 'selected' : '' }}>Berlangsung</option>
                                <option value="Selesai" {{ request('status_kunjungan') === 'Selesai' ? 'selected' : '' }}>Selesai</option>
                            </select>
                        </div>
                        @elseif($tab === 'buku')
                        <div class="col-md-4">
                            <label class="form-label">Kategori Buku</label>
                            <select name="id_kategori" class="form-select">
                                <option value="">-- Semua Kategori --</option>
                                @foreach($kategoriList as $k)
                                    <option value="{{ $k->id }}" {{ request('id_kategori') == $k->id ? 'selected' : '' }}>{{ $k->nama_kategori }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Status Ketersediaan</label>
                            <select name="status_buku" class="form-select">
                                <option value="">-- Semua Status --</option>
                                <option value="tersedia" {{ request('status_buku') === 'tersedia' ? 'selected' : '' }}>Tersedia</option>
                                <option value="dipinjam" {{ request('status_buku') === 'dipinjam' ? 'selected' : '' }}>Dipinjam</option>
                                <option value="rusak" {{ request('status_buku') === 'rusak' ? 'selected' : '' }}>Rusak</option>
                                <option value="hilang" {{ request('status_buku') === 'hilang' ? 'selected' : '' }}>Hilang</option>
                            </select>
                        </div>
                        @endif

                        @php
                            $exportParams = [
                                'jenis' => $tab,
                                'tgl_mulai' => $tglMulai,
                                'tgl_selesai' => $tglSelesai,
                                'status' => request('status_pinjam') ?: (request('status_kunjungan') ?: request('status_buku')),
                                'kondisi' => request('kondisi_kembali'),
                                'status_bayar' => request('status_bayar'),
                                'id_kategori' => request('id_kategori'),
                            ];
                        @endphp
                        <div class="col-md-3 d-flex gap-2 flex-wrap flex-xl-nowrap">
                            <button type="submit" class="btn btn-primary"><i class="bx bx-filter-alt"></i></button>
                            <a href="{{ route('laporan.cetak', $exportParams) }}" target="_blank" class="btn btn-outline-danger" title="Cetak / Ekspor PDF">
                                <i class="bx bx-printer"></i>
                            </a>
                            <a href="{{ route('laporan.export_excel', $exportParams) }}" class="btn btn-outline-success" title="Ekspor ke Excel (.xlsx)">
                                <i class="bx bx-spreadsheet"></i>
                            </a>
                        </div>
                    </form>

                    <!-- TABEL KONTEN -->
                    @if($tab === 'peminjaman')
                    <div class="table-responsive text-nowrap">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>No</th>
                                    <th>Peminjam</th>
                                    <th>Buku</th>
                                    <th>Tgl Pinjam</th>
                                    <th>Jatuh Tempo</th>
                                    <th>Tgl Kembali</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($dataPeminjaman as $index => $p)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $p->anggota->nama ?? '-' }} ({{ $p->anggota->no_anggota ?? '-' }})</td>
                                    <td>{{ $p->buku->judul ?? '-' }}</td>
                                    <td>{{ $p->tgl_pinjam->format('d/m/Y') }}</td>
                                    <td>{{ $p->tgl_jatuh_tempo->format('d/m/Y') }}</td>
                                    <td>{{ $p->tgl_kembali ? $p->tgl_kembali->format('d/m/Y') : '-' }}</td>
                                    <td><span class="badge {{ $p->status === 'Selesai' ? 'bg-success' : 'bg-warning' }}">{{ $p->status }}</span></td>
                                </tr>
                                @empty
                                <tr><td colspan="7" class="text-center py-3 text-muted">Tidak ada data peminjaman pada periode ini.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @elseif($tab === 'pengembalian')
                    <div class="table-responsive text-nowrap">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>No</th>
                                    <th>Peminjam</th>
                                    <th>Buku</th>
                                    <th>Tgl Pinjam</th>
                                    <th>Tgl Kembali</th>
                                    <th>Kondisi Buku</th>
                                    <th>Denda</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($dataPengembalian as $index => $p)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $p->anggota->nama ?? '-' }}</td>
                                    <td>{{ $p->buku->judul ?? '-' }}</td>
                                    <td>{{ $p->tgl_pinjam->format('d/m/Y') }}</td>
                                    <td>{{ $p->tgl_kembali ? $p->tgl_kembali->format('d/m/Y') : '-' }}</td>
                                    <td><span class="badge bg-label-info text-capitalize">{{ $p->kondisi_kembali }}</span></td>
                                    <td>
                                        @if($p->denda && $p->denda->nominal > 0)
                                            <strong class="text-danger">Rp {{ number_format($p->denda->nominal, 0, ',', '.') }}</strong>
                                        @else
                                            <span class="text-success">Rp 0</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="7" class="text-center py-3 text-muted">Tidak ada data pengembalian pada periode ini.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @elseif($tab === 'denda')
                    <div class="table-responsive text-nowrap">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>No</th>
                                    <th>Anggota</th>
                                    <th>Buku</th>
                                    <th>Tanggal Tercatat</th>
                                    <th>Nominal Denda</th>
                                    <th>Status Pembayaran</th>
                                    <th>Tgl Pembayaran</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($dataDenda as $index => $d)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $d->peminjaman->anggota->nama ?? '-' }}</td>
                                    <td>{{ $d->peminjaman->buku->judul ?? '-' }}</td>
                                    <td>{{ $d->created_at->format('d/m/Y') }}</td>
                                    <td><strong>Rp {{ number_format($d->nominal, 0, ',', '.') }}</strong></td>
                                    <td><span class="badge {{ $d->status_bayar === 'Lunas' ? 'bg-success' : 'bg-danger' }}">{{ $d->status_bayar }}</span></td>
                                    <td>{{ $d->tgl_bayar ? $d->tgl_bayar->format('d/m/Y H:i') : '-' }}</td>
                                </tr>
                                @empty
                                <tr><td colspan="7" class="text-center py-3 text-muted">Tidak ada data transaksi denda pada periode ini.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @elseif($tab === 'kunjungan')
                    <div class="table-responsive text-nowrap">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>No</th>
                                    <th>Anggota</th>
                                    <th>Tanggal</th>
                                    <th>Waktu Masuk</th>
                                    <th>Waktu Keluar</th>
                                    <th>Buku Dibaca</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($dataKunjungan as $index => $k)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $k->anggota->nama ?? '-' }}</td>
                                    <td>{{ $k->tanggal->format('d/m/Y') }}</td>
                                    <td>{{ $k->waktu_masuk }}</td>
                                    <td>{{ $k->waktu_keluar ?: '-' }}</td>
                                    <td>{{ $k->detailBuku->count() }} Buku</td>
                                    <td><span class="badge {{ $k->status === 'Selesai' ? 'bg-success' : 'bg-warning' }}">{{ $k->status }}</span></td>
                                </tr>
                                @empty
                                <tr><td colspan="7" class="text-center py-3 text-muted">Tidak ada data kunjungan pada periode ini.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @elseif($tab === 'buku')
                    <div class="table-responsive text-nowrap">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>No</th>
                                    <th>Kode Barcode Eksemplar</th>
                                    <th>Judul Buku</th>
                                    <th>Kategori</th>
                                    <th>Lokasi Rak / Laci</th>
                                    <th>Kondisi Fisik</th>
                                    <th>Status Ketersediaan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($dataEksemplar as $index => $eks)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td><span class="badge bg-label-dark font-monospace">{{ $eks->kode_eksemplar }}</span></td>
                                    <td><strong>{{ $eks->buku->judul ?? '-' }}</strong></td>
                                    <td>{{ $eks->buku->kategori->nama_kategori ?? 'Umum' }}</td>
                                    <td>{{ $eks->laci ? ($eks->laci->lemari->kode_lemari . ' - ' . $eks->laci->kode_laci) : '-' }}</td>
                                    <td><span class="badge bg-label-info text-capitalize">{{ $eks->kondisi }}</span></td>
                                    <td><span class="badge {{ $eks->status === 'tersedia' ? 'bg-success' : 'bg-warning' }} text-capitalize">{{ $eks->status }}</span></td>
                                </tr>
                                @empty
                                <tr><td colspan="7" class="text-center py-3 text-muted">Tidak ada data eksemplar buku yang sesuai filter.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @endif

                </div>
            </div>
        </div>
    </div>
</div>
@endsection
