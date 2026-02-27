<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('failed_jobs', function (Blueprint $table) {
            $table->string('uuid')->nullable()->after('id');
        });

        DB::table('failed_jobs')->get()->each(function ($row) {
            DB::table('failed_jobs')
                ->where('id', $row->id)
                ->update([
                    'uuid' => (string) Str::uuid(),
                ]);
        });

        Schema::table('failed_jobs', function (Blueprint $table) {
            $table->string('uuid')->nullable(false)->change();
            $table->unique('uuid');
        });
    }

    public function down(): void
    {
        Schema::table('failed_jobs', function (Blueprint $table) {
            $table->dropUnique(['uuid']);
            $table->dropColumn('uuid');
        });
    }
};
