<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lemari', function (Blueprint $table) {
            $table->id();
            $table->string('kode_lemari')->unique();
            $table->string('nama_lokasi');
            $table->text('deskripsi')->nullable();
            $table->timestamps();
        });

        Schema::create('laci', function (Blueprint $table) {
            $table->id();
            $table->string('kode_laci')->unique();
            $table->foreignId('id_lemari')->constrained('lemari')->onDelete('cascade');
            $table->text('deskripsi')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('laci');
        Schema::dropIfExists('lemari');
    }
};
