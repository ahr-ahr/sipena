<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('anggaran', function (Blueprint $table) {
            $table->id();
            $table->string('kode_anggaran', 30)->unique();
            $table->uuid('uuid')->unique();
            $table->foreignId('laporan_id')->unique()->constrained('laporan');
            $table->foreignId('dibuat_oleh')->constrained('users');

            $table->decimal('total_biaya', 15, 2)->nullable();

            $table->enum('status', [
                'draft',
                'diajukan_ke_tu',
                'ditolak_tu',
                'diterima_tu',
                'ditolak_kepsek',
                'disetujui_kepsek'
            ])->default('draft');

            $table->text('catatan')->nullable();
            $table->timestamps();
            $table->index('status');
        });

        Schema::create('anggaran_detail', function (Blueprint $table) {
            $table->id();
            $table->foreignId('anggaran_id')->constrained('anggaran')->cascadeOnDelete();
            $table->string('nama_item', 100);
            $table->integer('qty');
            $table->decimal('harga_satuan', 15, 2);
            $table->decimal('subtotal', 15, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('anggaran_detail');
        Schema::dropIfExists('anggaran');
    }
};
