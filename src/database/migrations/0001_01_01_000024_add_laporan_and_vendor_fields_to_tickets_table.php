<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {

            // relasi ke laporan
            $table->foreignId('laporan_id')
                ->nullable()
                ->after('id')
                ->constrained('laporan')
                ->cascadeOnDelete();

            // teknisi eksternal
            $table->string('external_vendor')
                ->nullable()
                ->after('assigned_to');

            $table->text('external_notes')
                ->nullable()
                ->after('external_vendor');

            // waktu pengerjaan
            $table->timestamp('started_at')
                ->nullable()
                ->after('status');

            $table->timestamp('resolved_at')
                ->nullable()
                ->after('started_at');
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {

            $table->dropForeign(['laporan_id']);
            $table->dropColumn([
                'laporan_id',
                'external_vendor',
                'external_notes',
                'started_at',
                'resolved_at',
            ]);
        });
    }
};
