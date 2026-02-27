<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('laporan_attachments', function (Blueprint $table) {
            $table->id();

            // public identifier (aman buat frontend)
            $table->uuid('uuid')->unique();

            // relasi ke laporan
            $table->foreignId('laporan_id')
                ->constrained('laporan')
                ->cascadeOnDelete();

            // storage info (MinIO / S3 / Local)
            $table->string('file_path');                 // path di bucket
            $table->string('file_name')->nullable();     // nama asli
            $table->string('mime_type', 50);
            $table->unsignedBigInteger('file_size');     // bytes

            $table->timestamps();

            // index buat performa
            $table->index('laporan_id');
            $table->index('mime_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('laporan_attachments');
    }
};
