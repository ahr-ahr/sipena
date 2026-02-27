<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('app_installer', function (Blueprint $table) {
            $table->id();
            $table->timestamp('installed_at')->useCurrent();
            $table->string('installed_by', 100)->nullable();
            $table->enum('environment', ['local', 'development', 'production'])->default('development');
            $table->string('db_version', 20)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('license_cache', function (Blueprint $table) {
            $table->id();
            $table->string('license_key', 100)->nullable();
            $table->enum('status', ['active', 'expired', 'invalid', 'revoked']);
            $table->date('expires_at')->nullable();
            $table->timestamp('last_checked_at')->nullable();
            $table->date('grace_until')->nullable();
            $table->string('server_fingerprint')->nullable();

            $table->index('status');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('license_cache');
        Schema::dropIfExists('app_installer');
    }
};
