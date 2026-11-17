<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kunjungan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_anggota')->constrained('anggota')->onDelete('cascade');
            $table->date('tanggal');
            $table->time('waktu_masuk');
            $table->time('waktu_keluar')->nullable();
            $table->enum('status', ['Berlangsung', 'Selesai'])->default('Berlangsung');
            $table->timestamps();
        });

        Schema::create('detail_kunjungan_buku', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_kunjungan')->constrained('kunjungan')->onDelete('cascade');
            $table->foreignId('id_eksemplar')->constrained('eksemplar_buku')->onDelete('cascade');
            $table->time('waktu_scan');
            $table->enum('status', ['Dibaca di Tempat', 'Dijadikan Peminjaman'])->default('Dibaca di Tempat');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detail_kunjungan_buku');
        Schema::dropIfExists('kunjungan');
    }
};
