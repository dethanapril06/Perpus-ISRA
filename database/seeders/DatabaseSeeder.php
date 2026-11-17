<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Pengaturan;
use App\Models\Kategori;
use App\Models\Lemari;
use App\Models\Laci;
use App\Models\Buku;
use App\Models\EksemplarBuku;
use App\Models\Anggota;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Users
        User::create([
            'name' => 'Sinyo April Dethan',
            'username' => 'admin',
            'email' => 'admin@perpus.local',
            'password' => Hash::make('password'),
            'no_telp' => '081234567890',
            'level' => 'admin',
            'status' => 'aktif',
        ]);

        User::create([
            'name' => 'Petugas Perpustakaan',
            'username' => 'petugas',
            'email' => 'petugas@perpus.local',
            'password' => Hash::make('password'),
            'no_telp' => '081298765432',
            'level' => 'petugas',
            'status' => 'aktif',
        ]);

        // 2. Pengaturan
        Pengaturan::create([
            'nominal_per_hari' => 1000,
            'durasi_pinjam_default' => 7,
            'maks_buku_pinjam' => 3,
            'maks_denda' => 50000,
            'nama_perpustakaan' => 'Perpustakaan SMA Kr Indonesia Sejahtera',
            'alamat_perpustakaan' => 'Kuli Aisele, Rote Ndao, NTT',
            'no_telp_perpustakaan' => '(0380) 881234',
        ]);

        // 3. Lemari
        $lemariA = Lemari::create([
            'kode_lemari' => 'LMR-01',
            'nama_lokasi' => 'Lemari A (Lantai 1 - Sayap Kiri)',
            'deskripsi' => 'Koleksi Buku Teknologi & Sains',
        ]);

        $lemariB = Lemari::create([
            'kode_lemari' => 'LMR-02',
            'nama_lokasi' => 'Lemari B (Lantai 1 - Sayap Kanan)',
            'deskripsi' => 'Koleksi Sastra, Novel & Bahasa',
        ]);

        $lemariC = Lemari::create([
            'kode_lemari' => 'LMR-03',
            'nama_lokasi' => 'Lemari C (Lantai 2)',
            'deskripsi' => 'Koleksi Sejarah, Sosial & Umum',
        ]);

        // 4. Laci
        $laciA1 = Laci::create([
            'kode_laci' => 'LAC-A1',
            'id_lemari' => $lemariA->id,
            'deskripsi' => 'Rak 1: Web Development & Pemrograman',
        ]);

        $laciA2 = Laci::create([
            'kode_laci' => 'LAC-A2',
            'id_lemari' => $lemariA->id,
            'deskripsi' => 'Rak 2: Artificial Intelligence & Data Science',
        ]);

        $laciB1 = Laci::create([
            'kode_laci' => 'LAC-B1',
            'id_lemari' => $lemariB->id,
            'deskripsi' => 'Rak 1: Novel Klasik Indonesia & Dunia',
        ]);

        $laciB2 = Laci::create([
            'kode_laci' => 'LAC-B2',
            'id_lemari' => $lemariB->id,
            'deskripsi' => 'Rak 2: Puisi, Drama & Antologi',
        ]);

        // 5. Kategori
        $katKomputer = Kategori::create([
            'kode_kategori' => 'KAT-001',
            'nama_kategori' => 'Ilmu Komputer & TI',
            'deskripsi' => 'Buku-buku seputar pemrograman, basis data, jaringan, dan AI',
        ]);

        $katSastra = Kategori::create([
            'kode_kategori' => 'KAT-002',
            'nama_kategori' => 'Sastra & Fiksi',
            'deskripsi' => 'Koleksi novel, cerpen, fiksi ilmiah, dan sastra klasik',
        ]);

        $katSains = Kategori::create([
            'kode_kategori' => 'KAT-003',
            'nama_kategori' => 'Sains & Matematika',
            'deskripsi' => 'Fisika, Biologi, Kimia, dan Matematika murni/terapan',
        ]);

        $katSejarah = Kategori::create([
            'kode_kategori' => 'KAT-004',
            'nama_kategori' => 'Sejarah & Sosial',
            'deskripsi' => 'Buku sejarah nusantara, peradaban dunia, dan sosiologi',
        ]);

        // 6. Buku & Eksemplar Fisik
        $buku1 = Buku::create([
            'kode_induk_buku' => 'BK-2026-0001',
            'judul' => 'Mastering Laravel 12: Modern Web Development',
            'pengarang' => 'Taylor Otwell & Tim',
            'penerbit' => 'Informatika Press',
            'tahun' => 2025,
            'isbn' => '978-602-001-112-3',
            'id_kategori' => $katKomputer->id,
            'deskripsi' => 'Buku panduan lengkap arsitektur dan pengembangan aplikasi modern dengan Laravel 12.',
        ]);
        for ($i = 1; $i <= 4; $i++) {
            EksemplarBuku::create([
                'id_buku' => $buku1->id,
                'kode_eksemplar' => 'BK-2026-0001.' . str_pad($i, 2, '0', STR_PAD_LEFT),
                'no_inventaris' => 'INV/TI/2026/' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'id_laci' => $laciA1->id,
                'kondisi' => 'baik',
                'status' => 'tersedia',
            ]);
        }

        $buku2 = Buku::create([
            'kode_induk_buku' => 'BK-2026-0002',
            'judul' => 'Clean Code: Panduan Menulis Kode yang Rapi',
            'pengarang' => 'Robert C. Martin',
            'penerbit' => 'Pearson Publishing',
            'tahun' => 2023,
            'isbn' => '978-013-235-088-4',
            'id_kategori' => $katKomputer->id,
            'deskripsi' => 'Prinsip, pola, dan praktik rekayasa perangkat lunak yang bersih.',
        ]);
        for ($i = 1; $i <= 3; $i++) {
            EksemplarBuku::create([
                'id_buku' => $buku2->id,
                'kode_eksemplar' => 'BK-2026-0002.' . str_pad($i, 2, '0', STR_PAD_LEFT),
                'no_inventaris' => 'INV/TI/2026/' . str_pad($i + 4, 3, '0', STR_PAD_LEFT),
                'id_laci' => $laciA1->id,
                'kondisi' => 'baik',
                'status' => 'tersedia',
            ]);
        }

        $buku3 = Buku::create([
            'kode_induk_buku' => 'BK-2026-0003',
            'judul' => 'Laskar Pelangi',
            'pengarang' => 'Andrea Hirata',
            'penerbit' => 'Bentang Pustaka',
            'tahun' => 2022,
            'isbn' => '978-979-306-279-2',
            'id_kategori' => $katSastra->id,
            'deskripsi' => 'Novel inspiratif tentang perjuangan sepuluh anak di Belitung.',
        ]);
        for ($i = 1; $i <= 3; $i++) {
            EksemplarBuku::create([
                'id_buku' => $buku3->id,
                'kode_eksemplar' => 'BK-2026-0003.' . str_pad($i, 2, '0', STR_PAD_LEFT),
                'no_inventaris' => 'INV/SAS/2026/' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'id_laci' => $laciB1->id,
                'kondisi' => 'baik',
                'status' => 'tersedia',
            ]);
        }

        $buku4 = Buku::create([
            'kode_induk_buku' => 'BK-2026-0004',
            'judul' => 'Bumi Manusia',
            'pengarang' => 'Pramoedya Ananta Toer',
            'penerbit' => 'Hasta Mitra / Lentera Dipantara',
            'tahun' => 2021,
            'isbn' => '978-979-973-123-1',
            'id_kategori' => $katSastra->id,
            'deskripsi' => 'Karya sastra legendaris Tetralogi Buru babak pertama.',
        ]);
        for ($i = 1; $i <= 4; $i++) {
            EksemplarBuku::create([
                'id_buku' => $buku4->id,
                'kode_eksemplar' => 'BK-2026-0004.' . str_pad($i, 2, '0', STR_PAD_LEFT),
                'no_inventaris' => 'INV/SAS/2026/' . str_pad($i + 3, 3, '0', STR_PAD_LEFT),
                'id_laci' => $laciB1->id,
                'kondisi' => 'baik',
                'status' => 'tersedia',
            ]);
        }

        // 7. Anggota
        Anggota::create([
            'no_anggota' => 'ANG-2026-001',
            'nama' => 'Ahmad Rian Fauzi',
            'identitas' => '202401001',
            'kelas_jabatan' => 'XII IPA 1 / Siswa',
            'no_hp' => '081234567801',
            'alamat' => 'Jl. Merdeka No. 12, Kupang',
            'tgl_daftar' => '2026-01-10',
            'status' => 'aktif',
        ]);

        Anggota::create([
            'no_anggota' => 'ANG-2026-002',
            'nama' => 'Siti Nurhaliza',
            'identitas' => '202401002',
            'kelas_jabatan' => 'XI IPS 2 / Siswa',
            'no_hp' => '081234567802',
            'alamat' => 'Jl. Ahmad Yani No. 5, Kupang',
            'tgl_daftar' => '2026-01-15',
            'status' => 'aktif',
        ]);

        Anggota::create([
            'no_anggota' => 'ANG-2026-003',
            'nama' => 'Bambang Sudarmono, S.Pd',
            'identitas' => '198505122010011002',
            'kelas_jabatan' => 'Guru Matematika / Staf',
            'no_hp' => '081234567803',
            'alamat' => 'Perum Graha Indah Blok C-4',
            'tgl_daftar' => '2026-01-05',
            'status' => 'aktif',
        ]);
    }
}
