<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('jabatan', function (Blueprint $table) {
            $table->id();
            $table->string('nama_jabatan', 50);
        });

        Schema::create('user_jabatan', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('jabatan_id')->constrained('jabatan')->cascadeOnDelete();

            $table->primary(['user_id', 'jabatan_id']);
            $table->index('user_id');
            $table->index('jabatan_id');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_jabatan');
        Schema::dropIfExists('jabatan');
    }
};
