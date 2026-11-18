<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Anggota;
use App\Models\Kategori;
use App\Models\Lemari;
use App\Models\Laci;
use App\Models\Buku;
use App\Models\EksemplarBuku;
use App\Models\Kunjungan;
use App\Models\DetailKunjunganBuku;
use App\Models\Peminjaman;
use App\Models\Denda;
use App\Models\Pengaturan;
use Carbon\Carbon;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PerpustakaanTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $petugas;
    protected Pengaturan $pengaturan;
    protected Anggota $anggota;
    protected Buku $buku;
    protected EksemplarBuku $eksemplar1;
    protected EksemplarBuku $eksemplar2;
    protected Kategori $kategori;
    protected Lemari $lemari;
    protected Laci $laci;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name' => 'Admin Test',
            'username' => 'admin_test',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'level' => 'admin',
            'status' => 'aktif',
        ]);

        $this->petugas = User::create([
            'name' => 'Petugas Test',
            'username' => 'petugas_test',
            'email' => 'petugas@test.com',
            'password' => bcrypt('password'),
            'level' => 'petugas',
            'status' => 'aktif',
        ]);

        $this->pengaturan = Pengaturan::create([
            'nominal_per_hari' => 1000,
            'durasi_pinjam_default' => 7,
            'maks_buku_pinjam' => 3,
            'maks_denda' => 50000,
            'nama_perpustakaan' => 'Perpustakaan ISRA Test',
        ]);

        $this->lemari = Lemari::create([
            'kode_lemari' => 'LMR-T1',
            'nama_lokasi' => 'Lemari Uji Coba',
        ]);

        $this->laci = Laci::create([
            'kode_laci' => 'LAC-T1',
            'id_lemari' => $this->lemari->id,
            'deskripsi' => 'Laci Uji Coba',
        ]);

        $this->kategori = Kategori::create([
            'kode_kategori' => 'KAT-T1',
            'nama_kategori' => 'Kategori Uji Coba',
        ]);

        $this->buku = Buku::create([
            'kode_induk_buku' => 'BK-TEST-001',
            'judul' => 'Buku Uji Coba Laravel',
            'id_kategori' => $this->kategori->id,
        ]);

        $this->eksemplar1 = EksemplarBuku::create([
            'id_buku' => $this->buku->id,
            'kode_eksemplar' => 'BK-TEST-001.01',
            'id_laci' => $this->laci->id,
            'kondisi' => 'baik',
            'status' => 'tersedia',
        ]);

        $this->eksemplar2 = EksemplarBuku::create([
            'id_buku' => $this->buku->id,
            'kode_eksemplar' => 'BK-TEST-001.02',
            'id_laci' => $this->laci->id,
            'kondisi' => 'baik',
            'status' => 'tersedia',
        ]);

        $this->anggota = Anggota::create([
            'no_anggota' => 'ANG-TEST-001',
            'nama' => 'Anggota Uji Coba',
            'identitas' => '12345678',
            'kelas_jabatan' => 'Siswa',
            'tgl_daftar' => '2026-01-01',
            'status' => 'aktif',
        ]);
    }

    public function test_login_successful_with_active_user()
    {
        $response = $this->post(route('login.post'), [
            'username' => 'admin_test',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($this->admin);
    }

    public function test_login_rejected_for_inactive_user()
    {
        $this->admin->update(['status' => 'nonaktif']);

        $response = $this->post(route('login.post'), [
            'username' => 'admin_test',
            'password' => 'password',
        ]);

        $response->assertSessionHas('error');
        $this->assertGuest();
    }

    public function test_auto_generate_copies_when_creating_book()
    {
        $response = $this->actingAs($this->admin)->post(route('buku.store'), [
            'kode_induk_buku' => 'BK-AUTO-999',
            'judul' => 'Buku Multi Eksemplar',
            'id_kategori' => $this->kategori->id,
            'id_laci' => $this->laci->id,
            'jumlah_eksemplar' => 4,
        ]);

        $response->assertRedirect(route('buku.index'));
        $bukuBaru = Buku::where('kode_induk_buku', 'BK-AUTO-999')->first();
        $this->assertNotNull($bukuBaru);
        $this->assertEquals(4, $bukuBaru->total_eksemplar);
        $this->assertEquals(4, $bukuBaru->stok);

        $this->assertDatabaseHas('eksemplar_buku', [
            'id_buku' => $bukuBaru->id,
            'kode_eksemplar' => 'BK-AUTO-999.01',
            'status' => 'tersedia',
        ]);
        $this->assertDatabaseHas('eksemplar_buku', [
            'id_buku' => $bukuBaru->id,
            'kode_eksemplar' => 'BK-AUTO-999.04',
            'status' => 'tersedia',
        ]);
    }

    public function test_category_cannot_be_deleted_if_has_books()
    {
        $response = $this->actingAs($this->admin)->delete(route('kategori.destroy', $this->kategori->id));

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('kategori', ['id' => $this->kategori->id]);
    }

    public function test_visit_check_in_and_scan_reading_copy()
    {
        // 1. Check in visit
        $response = $this->actingAs($this->admin)->post(route('kunjungan.store'), [
            'kode_anggota' => $this->anggota->no_anggota,
        ]);

        $kunjungan = Kunjungan::first();
        $this->assertNotNull($kunjungan);
        $this->assertEquals('Berlangsung', $kunjungan->status);
        $response->assertRedirect(route('kunjungan.show', $kunjungan->id));

        // 2. Scan reading copy
        $this->actingAs($this->admin)->post(route('kunjungan.scan_buku', $kunjungan->id), [
            'kode_buku' => $this->eksemplar1->kode_eksemplar,
        ]);

        $this->assertDatabaseHas('detail_kunjungan_buku', [
            'id_kunjungan' => $kunjungan->id,
            'id_eksemplar' => $this->eksemplar1->id,
            'status' => 'Dibaca di Tempat',
        ]);
    }

    public function test_visit_convert_reading_copy_to_loan_jalur_b()
    {
        $kunjungan = Kunjungan::create([
            'id_anggota' => $this->anggota->id,
            'tanggal' => Carbon::today(),
            'waktu_masuk' => '10:00:00',
            'status' => 'Berlangsung',
        ]);

        $detail = DetailKunjunganBuku::create([
            'id_kunjungan' => $kunjungan->id,
            'id_eksemplar' => $this->eksemplar1->id,
            'waktu_scan' => '10:05:00',
            'status' => 'Dibaca di Tempat',
        ]);

        $response = $this->actingAs($this->admin)->post(route('kunjungan.konversi_pinjam', $detail->id));

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('peminjaman', [
            'id_kunjungan' => $kunjungan->id,
            'id_anggota' => $this->anggota->id,
            'id_eksemplar' => $this->eksemplar1->id,
            'status' => 'Dipinjam',
        ]);

        $this->assertEquals('Dijadikan Peminjaman', $detail->fresh()->status);
        $this->assertEquals('dipinjam', $this->eksemplar1->fresh()->status);
        // Eksemplar 2 remains available
        $this->assertEquals('tersedia', $this->eksemplar2->fresh()->status);
        $this->assertEquals(1, $this->buku->fresh()->stok);
    }

    public function test_direct_loan_specific_copy_jalur_a_with_validations()
    {
        // 1. Direct loan of copy 1
        $response = $this->actingAs($this->admin)->post(route('peminjaman.store'), [
            'kode_anggota' => $this->anggota->no_anggota,
            'kode_buku' => $this->eksemplar1->kode_eksemplar,
            'tgl_pinjam' => Carbon::today()->format('Y-m-d'),
            'durasi_hari' => 7,
        ]);

        $response->assertRedirect(route('peminjaman.index'));
        $this->assertDatabaseHas('peminjaman', [
            'id_anggota' => $this->anggota->id,
            'id_eksemplar' => $this->eksemplar1->id,
            'status' => 'Dipinjam',
        ]);

        $this->assertEquals('dipinjam', $this->eksemplar1->fresh()->status);
        $this->assertEquals('tersedia', $this->eksemplar2->fresh()->status);
        $this->assertEquals(1, $this->buku->fresh()->stok);
    }

    public function test_return_specific_copy_with_automatic_overdue_fine()
    {
        // Create loan that was due 5 days ago for copy 1
        $peminjaman = Peminjaman::create([
            'id_anggota' => $this->anggota->id,
            'id_eksemplar' => $this->eksemplar1->id,
            'id_user' => $this->admin->id,
            'tgl_pinjam' => Carbon::today()->subDays(12),
            'tgl_jatuh_tempo' => Carbon::today()->subDays(5),
            'status' => 'Dipinjam',
        ]);

        $this->eksemplar1->update(['status' => 'dipinjam']);

        // Process Return today (5 days late * 1000 = 5000 fine)
        $response = $this->actingAs($this->admin)->post(route('pengembalian.store'), [
            'id_peminjaman' => $peminjaman->id,
            'tgl_kembali' => Carbon::today()->format('Y-m-d'),
            'kondisi_kembali' => 'baik',
        ]);

        $response->assertRedirect(route('pengembalian.index'));
        $this->assertEquals('Selesai', $peminjaman->fresh()->status);
        $this->assertEquals('tersedia', $this->eksemplar1->fresh()->status);

        // Check fine record
        $this->assertDatabaseHas('denda', [
            'id_peminjaman' => $peminjaman->id,
            'nominal' => 5000,
            'status_bayar' => 'Belum Lunas',
        ]);

        // Pay fine
        $denda = Denda::where('id_peminjaman', $peminjaman->id)->first();
        $this->actingAs($this->admin)->put(route('denda.bayar', $denda->id));
        $this->assertEquals('Lunas', $denda->fresh()->status_bayar);
    }

    public function test_landing_page_renders_successfully()
    {
        $response = $this->get(route('landing.index'));

        $response->assertStatus(200);
        $response->assertSee('Perpustakaan ISRA Test');
        $response->assertSee('Buku Uji Coba Laravel');
    }

    public function test_online_member_self_registration_succeeds()
    {
        $response = $this->post(route('landing.register.post'), [
            'nama' => 'Calon Anggota Mandiri',
            'jenis_identitas' => 'NIS',
            'identitas' => '99887766',
            'kelas_jabatan' => 'X MIPA 2',
            'no_hp' => '081299998888',
            'alamat' => 'Jl. Pelajar No. 10',
        ]);

        $anggota = Anggota::where('identitas', '99887766')->first();
        $this->assertNotNull($anggota);
        $this->assertEquals('Calon Anggota Mandiri', $anggota->nama);
        $this->assertStringStartsWith('ANG-', $anggota->no_anggota);
        $this->assertEquals('aktif', $anggota->status);

        $response->assertRedirect(route('landing.bukti_register', $anggota->id));
    }

    public function test_laporan_export_excel_works_for_all_report_types()
    {
        $kunjungan = Kunjungan::create([
            'id_anggota' => $this->anggota->id,
            'tanggal' => Carbon::today()->format('Y-m-d'),
            'waktu_masuk' => '08:00:00',
            'status' => 'Selesai',
        ]);

        DetailKunjunganBuku::create([
            'id_kunjungan' => $kunjungan->id,
            'id_eksemplar' => $this->eksemplar1->id,
            'waktu_scan' => Carbon::now(),
        ]);

        $peminjaman = Peminjaman::create([
            'id_kunjungan' => $kunjungan->id,
            'id_anggota' => $this->anggota->id,
            'id_eksemplar' => $this->eksemplar1->id,
            'id_user' => $this->admin->id,
            'tgl_pinjam' => Carbon::today()->subDays(10),
            'tgl_jatuh_tempo' => Carbon::today()->subDays(3),
            'tgl_kembali' => Carbon::today(),
            'status' => 'Selesai',
            'kondisi_kembali' => 'baik',
        ]);

        Denda::create([
            'id_peminjaman' => $peminjaman->id,
            'jumlah_hari_terlambat' => 3,
            'nominal' => 3000,
            'status_bayar' => 'Belum Lunas',
        ]);

        $indexResponse = $this->actingAs($this->admin)->get(route('laporan.index', ['tab' => 'peminjaman']));
        $indexResponse->assertStatus(200);
        $indexResponse->assertSee('laporan/export-excel');

        $tglMulai = Carbon::today()->startOfMonth()->format('Y-m-d');
        $tglSelesai = Carbon::today()->format('Y-m-d');

        foreach (['peminjaman', 'pengembalian', 'denda', 'kunjungan', 'buku'] as $jenis) {
            $response = $this->actingAs($this->admin)->get(route('laporan.export_excel', [
                'jenis' => $jenis,
                'tgl_mulai' => $tglMulai,
                'tgl_selesai' => $tglSelesai,
            ]));

            $response->assertStatus(200);
            $this->assertStringContainsString(
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                $response->headers->get('content-type')
            );
            $this->assertStringContainsString('.xlsx', $response->headers->get('content-disposition'));

            $content = $response->streamedContent();
            $tmpFile = tempnam(sys_get_temp_dir(), 'xlsx_test_');
            file_put_contents($tmpFile, $content);

            try {
                $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($tmpFile);
                $sheet = $spreadsheet->getActiveSheet();
                $this->assertSame('Perpustakaan ISRA Test', $sheet->getCell('A1')->getValue());
                $this->assertNotEmpty($sheet->getCell('A6')->getValue());
                $this->assertNotEmpty($sheet->getCell('B7')->getValue());
            } finally {
                @unlink($tmpFile);
            }
        }
    }

    public function test_buku_import_template_includes_guide_sheet_and_is_readable()
    {
        $templatePath = public_path('templates/template_import_buku.xlsx');
        @unlink($templatePath);

        $response = $this->actingAs($this->admin)->get(route('buku.template_excel'));
        $response->assertStatus(200);
        $this->assertFileExists($templatePath);

        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($templatePath);
        $this->assertSame(2, $spreadsheet->getSheetCount());
        $this->assertSame('Template Buku & Eksemplar', $spreadsheet->getSheet(0)->getTitle());
        $this->assertSame('Petunjuk Pengisian', $spreadsheet->getSheet(1)->getTitle());
        $this->assertStringContainsString('PETUNJUK PENGISIAN', (string) $spreadsheet->getSheet(1)->getCell('A1')->getValue());

        // Ensure XlsxHelper::read() still reads Sheet 1 headers & sample rows cleanly
        $rows = \App\Helpers\XlsxHelper::read($templatePath);
        $this->assertGreaterThan(1, count($rows));
        $this->assertSame('kode_induk_buku', $rows[0][0]);
        $this->assertSame('judul', $rows[0][2]);
        $this->assertContains('kode_lemari', $rows[0]);
    }

    public function test_buku_import_auto_creates_lemari_and_laci_when_provided()
    {
        $templatePath = public_path('templates/template_import_buku.xlsx');
        @unlink($templatePath);

        $tmpXlsx = tempnam(sys_get_temp_dir(), 'import_lemari_') . '.xlsx';
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $headers = ['kode_induk_buku', 'kode_eksemplar', 'judul', 'pengarang', 'kategori', 'kode_lemari', 'kode_laci', 'kondisi', 'status'];
        $row = ['BK-IMP-01', 'BK-IMP-01.01', 'Buku Lemari Baru', 'Penulis A', 'Fiksi', 'LMR-BARU', 'LAC-BARU', 'baik', 'tersedia'];
        foreach ($headers as $i => $h) {
            $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i + 1);
            $sheet->setCellValue("{$col}1", $h);
            $sheet->setCellValue("{$col}2", $row[$i]);
        }
        (new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet))->save($tmpXlsx);

        try {
            $uploaded = new \Illuminate\Http\UploadedFile($tmpXlsx, 'import.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);
            $response = $this->actingAs($this->admin)->post(route('buku.import_excel'), [
                'file_excel' => $uploaded,
                'mode_duplikat' => 'skip',
            ]);

            $response->assertRedirect();
            $this->assertDatabaseHas('lemari', ['kode_lemari' => 'LMR-BARU']);
            $this->assertDatabaseHas('laci', ['kode_laci' => 'LAC-BARU']);
            $eksemplar = EksemplarBuku::where('kode_eksemplar', 'BK-IMP-01.01')->first();
            $this->assertNotNull($eksemplar);
            $this->assertSame('LMR-BARU', $eksemplar->laci->lemari->kode_lemari);
        } finally {
            @unlink($tmpXlsx);
        }
    }

    public function test_buku_can_be_deleted_when_no_active_loans()
    {
        $response = $this->actingAs($this->admin)->delete(route('buku.destroy', $this->buku->id));

        $response->assertRedirect(route('buku.index'));
        $this->assertDatabaseMissing('buku', ['id' => $this->buku->id]);
    }
}
