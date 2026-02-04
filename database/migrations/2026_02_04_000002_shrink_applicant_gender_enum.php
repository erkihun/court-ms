<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Ensure no invalid values remain before narrowing enum.
        DB::table('applicants')
            ->whereNull('gender')
            ->orWhere('gender', '')
            ->orWhereNotIn('gender', ['male', 'female'])
            ->update(['gender' => 'male']);

        Schema::table('applicants', function (Blueprint $table) {
            $table->enum('gender', ['male', 'female'])->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('applicants', function (Blueprint $table) {
            $table->enum('gender', ['male', 'female', 'other'])->nullable(false)->change();
        });
    }
};
