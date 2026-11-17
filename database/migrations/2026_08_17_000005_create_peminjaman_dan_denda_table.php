<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('peminjaman', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_kunjungan')->nullable()->constrained('kunjungan')->onDelete('set null');
            $table->foreignId('id_anggota')->constrained('anggota')->onDelete('cascade');
            $table->foreignId('id_eksemplar')->constrained('eksemplar_buku')->onDelete('cascade');
            $table->foreignId('id_user')->constrained('users')->onDelete('cascade');
            $table->date('tgl_pinjam');
            $table->date('tgl_jatuh_tempo');
            $table->date('tgl_kembali')->nullable();
            $table->enum('status', ['Dipinjam', 'Selesai', 'Terlambat'])->default('Dipinjam');
            $table->enum('kondisi_kembali', ['baik', 'rusak', 'hilang'])->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();
        });

        Schema::create('denda', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_peminjaman')->constrained('peminjaman')->onDelete('cascade');
            $table->decimal('nominal', 12, 2)->default(0);
            $table->enum('status_bayar', ['Belum Lunas', 'Lunas'])->default('Belum Lunas');
            $table->timestamp('tgl_bayar')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('denda');
        Schema::dropIfExists('peminjaman');
    }
};
