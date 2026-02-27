<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('laporan', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('kode_laporan', 30)->unique();
            $table->string('judul', 150)->nullable();
            $table->text('deskripsi')->nullable();

            $table->foreignId('kategori_id')->constrained('kategori_laporan');
            $table->foreignId('pelapor_id')->constrained('users');

            $table->foreignId('mapel_id')
            ->nullable()
            ->constrained('mapel')
            ->nullOnDelete();

            $table->enum('status', [
                'menunggu',
                'diproses',
                'ditolak',
                'disetujui',
                'selesai',
            ])->default('menunggu');

            $table->timestamps();

$table->index(
    ['kategori_id', 'pelapor_id', 'status', 'created_at'],
    'idx_laporan_filter'
);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('laporan');
    }
};
