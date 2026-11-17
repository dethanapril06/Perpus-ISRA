<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengaturan', function (Blueprint $table) {
            $table->id();
            $table->decimal('nominal_per_hari', 12, 2)->default(1000);
            $table->integer('durasi_pinjam_default')->default(7); // hari
            $table->integer('maks_buku_pinjam')->default(3); // maksimal buku per anggota
            $table->decimal('maks_denda', 12, 2)->nullable(); // opsional batas maksimal
            $table->string('nama_perpustakaan')->default('Perpustakaan ISRA');
            $table->text('alamat_perpustakaan')->nullable();
            $table->string('no_telp_perpustakaan')->nullable();
            $table->timestamps();
        });

        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_user')->nullable()->constrained('users')->onDelete('set null');
            $table->string('aktivitas'); // login, logout, create, update, delete, dll
            $table->string('modul'); // Peminjaman, Pengembalian, Anggota, Buku, dll
            $table->text('detail')->nullable();
            $table->string('ip_address')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('pengaturan');
    }
};
