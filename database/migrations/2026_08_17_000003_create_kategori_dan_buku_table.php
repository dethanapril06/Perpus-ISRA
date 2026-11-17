<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kategori', function (Blueprint $table) {
            $table->id();
            $table->string('kode_kategori')->unique();
            $table->string('nama_kategori');
            $table->text('deskripsi')->nullable();
            $table->timestamps();
        });

        // 1. Data Bibliografi / Judul Buku
        Schema::create('buku', function (Blueprint $table) {
            $table->id();
            $table->string('kode_induk_buku')->unique(); // Kode katalog induk (contoh: BK-2026-0001)
            $table->string('judul');
            $table->string('pengarang')->nullable();
            $table->string('penerbit')->nullable();
            $table->integer('tahun')->nullable();
            $table->string('isbn')->nullable();
            $table->foreignId('id_kategori')->nullable()->constrained('kategori')->onDelete('restrict');
            $table->string('cover')->nullable();
            $table->text('deskripsi')->nullable();
            $table->timestamps();
        });

        // 2. Data Eksemplar Fisik (Copies / Item Barcode Unik)
        Schema::create('eksemplar_buku', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_buku')->constrained('buku')->onDelete('cascade');
            $table->string('kode_eksemplar')->unique(); // Barcode unik per fisik (contoh: BK-2026-0001.01)
            $table->string('no_inventaris')->nullable();
            $table->foreignId('id_laci')->nullable()->constrained('laci')->onDelete('set null');
            $table->enum('kondisi', ['baik', 'rusak', 'hilang'])->default('baik');
            $table->enum('status', ['tersedia', 'dipinjam', 'rusak', 'hilang'])->default('tersedia');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('eksemplar_buku');
        Schema::dropIfExists('buku');
        Schema::dropIfExists('kategori');
    }
};
