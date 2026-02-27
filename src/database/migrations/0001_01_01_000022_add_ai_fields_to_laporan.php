<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('laporan', function (Blueprint $table) {
            $table->string('ai_label', 20)->nullable()->after('status');
            $table->float('ai_score')->nullable()->after('ai_label');
        });
    }

    public function down(): void
    {
        Schema::table('laporan', function (Blueprint $table) {
            $table->dropColumn(['ai_label', 'ai_score']);
        });
    }
};
