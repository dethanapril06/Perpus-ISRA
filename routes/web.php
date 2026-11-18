<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\LemariController;
use App\Http\Controllers\LaciController;
use App\Http\Controllers\KategoriController;
use App\Http\Controllers\AnggotaController;
use App\Http\Controllers\BukuController;
use App\Http\Controllers\KunjunganController;
use App\Http\Controllers\PeminjamanController;
use App\Http\Controllers\PengembalianController;
use App\Http\Controllers\DendaController;
use App\Http\Controllers\PengaturanController;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\LaporanController;

use App\Http\Controllers\LandingController;

// 1. Beranda Publik & Katalog Buku (OPAC)
Route::get('/', [LandingController::class, 'index'])->name('landing.index');
Route::get('/daftar-anggota', [LandingController::class, 'showRegister'])->name('landing.register');
Route::post('/daftar-anggota', [LandingController::class, 'storeRegister'])->name('landing.register.post');
Route::get('/bukti-pendaftaran/{id}', [LandingController::class, 'buktiRegister'])->name('landing.bukti_register');

// 2. Guest Routes (Authentication)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
});

// Authenticated Routes (Admin & Petugas)
Route::middleware('auth')->group(function () {
    // Logout
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Profile & Password
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::put('/profile/update', [ProfileController::class, 'updateProfile'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');

    // 1. Modul Kunjungan
    Route::prefix('kunjungan')->name('kunjungan.')->group(function () {
        Route::get('/', [KunjunganController::class, 'index'])->name('index');
        Route::post('/', [KunjunganController::class, 'store'])->name('store');
        Route::get('/{kunjungan}', [KunjunganController::class, 'show'])->name('show');
        Route::post('/{kunjungan}/scan-buku', [KunjunganController::class, 'scanBuku'])->name('scan_buku');
        Route::delete('/detail/{detail}', [KunjunganController::class, 'hapusBuku'])->name('hapus_buku');
        Route::post('/{kunjungan}/selesai', [KunjunganController::class, 'selesai'])->name('selesai');
        Route::post('/detail/{detail}/konversi-pinjam', [KunjunganController::class, 'konversiPinjam'])->name('konversi_pinjam');
    });

    // 2. Modul Peminjaman
    Route::prefix('peminjaman')->name('peminjaman.')->group(function () {
        Route::get('/', [PeminjamanController::class, 'index'])->name('index');
        Route::get('/create', [PeminjamanController::class, 'create'])->name('create');
        Route::post('/', [PeminjamanController::class, 'store'])->name('store');
        Route::get('/{peminjaman}/cetak-struk', [PeminjamanController::class, 'cetakStruk'])->name('cetak_struk');
    });

    // 3. Modul Pengembalian
    Route::prefix('pengembalian')->name('pengembalian.')->group(function () {
        Route::get('/', [PengembalianController::class, 'index'])->name('index');
        Route::get('/check-loan', [PengembalianController::class, 'checkActiveLoan'])->name('check_loan');
        Route::post('/store', [PengembalianController::class, 'store'])->name('store');
    });

    // 4. Modul Denda
    Route::prefix('denda')->name('denda.')->group(function () {
        Route::get('/', [DendaController::class, 'index'])->name('index');
        Route::put('/{denda}/bayar', [DendaController::class, 'bayar'])->name('bayar');
        Route::get('/{denda}/cetak-kuitansi', [DendaController::class, 'cetakKuitansi'])->name('cetak_kuitansi');
    });

    // 5. Master Anggota
    Route::prefix('anggota')->name('anggota.')->group(function () {
        Route::get('/lookup', [AnggotaController::class, 'lookup'])->name('lookup');
        Route::get('/template-excel', [AnggotaController::class, 'downloadTemplateExcel'])->name('template_excel');
        Route::post('/import-excel', [AnggotaController::class, 'importExcel'])->name('import_excel');
        Route::get('/pairing-rfid', [AnggotaController::class, 'pairingRfid'])->name('pairing_rfid');
        Route::get('/search-pairing', [AnggotaController::class, 'searchForPairing'])->name('search_pairing');
        Route::post('/save-rfid', [AnggotaController::class, 'saveRfid'])->name('save_rfid');
        Route::post('/remove-rfid', [AnggotaController::class, 'removeRfid'])->name('remove_rfid');
    });
    Route::resource('anggota', AnggotaController::class);

    // 6. Master Buku & Eksemplar
    Route::prefix('buku')->name('buku.')->group(function () {
        Route::get('/lookup', [BukuController::class, 'lookup'])->name('lookup');
        Route::get('/template-excel', [BukuController::class, 'downloadTemplateExcel'])->name('template_excel');
        Route::post('/import-excel', [BukuController::class, 'importExcel'])->name('import_excel');
        Route::get('/cetak-semua-barcode', [BukuController::class, 'cetakSemuaBarcode'])->name('cetak_semua_barcode');
        Route::get('/{buku}/cetak-barcode', [BukuController::class, 'cetakBarcode'])->name('cetak_barcode');
        Route::post('/{buku}/eksemplar', [BukuController::class, 'storeEksemplar'])->name('store_eksemplar');
        Route::put('/eksemplar/{eksemplar}', [BukuController::class, 'updateEksemplar'])->name('update_eksemplar');
        Route::delete('/eksemplar/{eksemplar}', [BukuController::class, 'destroyEksemplar'])->name('destroy_eksemplar');
        Route::get('/eksemplar/{eksemplar}/cetak-barcode', [BukuController::class, 'cetakBarcodeSingle'])->name('cetak_barcode_single');
    });
    Route::resource('buku', BukuController::class);

    // 7. Master Kategori
    Route::resource('kategori', KategoriController::class)->except(['create', 'edit', 'show']);

    // 8. Master Lemari & Laci
    Route::resource('lemari', LemariController::class)->except(['create', 'edit', 'show']);
    Route::get('/laci/by-lemari/{idLemari}', [LaciController::class, 'getByLemari'])->name('laci.by_lemari');
    Route::resource('laci', LaciController::class)->except(['create', 'edit', 'show']);

    // 9. Laporan
    Route::get('/laporan', [LaporanController::class, 'index'])->name('laporan.index');
    Route::get('/laporan/cetak', [LaporanController::class, 'cetak'])->name('laporan.cetak');
    Route::get('/laporan/export-excel', [LaporanController::class, 'exportExcel'])->name('laporan.export_excel');

    // 10. Audit Activity Log
    Route::get('/activity-log', [ActivityLogController::class, 'index'])->name('activity-log.index');

    // 11. Admin Only Routes (Pengguna & Pengaturan Perpustakaan)
    Route::middleware('admin')->group(function () {
        Route::resource('users', UserController::class)->except(['create', 'edit', 'show']);
        Route::get('/pengaturan', [PengaturanController::class, 'index'])->name('pengaturan.index');
        Route::put('/pengaturan', [PengaturanController::class, 'update'])->name('pengaturan.update');
    });
});
