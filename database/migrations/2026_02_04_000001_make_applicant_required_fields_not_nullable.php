<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Backfill existing NULL/invalid values before enforcing NOT NULL.
        DB::table('applicants')
            ->whereNull('middle_name')
            ->update(['middle_name' => '']);

        DB::table('applicants')
            ->whereNull('address')
            ->update(['address' => '']);

        DB::table('applicants')
            ->whereNull('gender')
            ->orWhere('gender', '')
            ->orWhereNotIn('gender', ['male', 'female', 'other'])
            ->update(['gender' => 'other']);

        Schema::table('applicants', function (Blueprint $table) {
            $table->string('middle_name')->nullable(false)->change();
            $table->enum('gender', ['male', 'female', 'other'])->nullable(false)->change();
            $table->text('address')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('applicants', function (Blueprint $table) {
            $table->string('middle_name')->nullable()->change();
            $table->enum('gender', ['male', 'female', 'other'])->nullable()->change();
            $table->text('address')->nullable()->change();
        });
    }
};
