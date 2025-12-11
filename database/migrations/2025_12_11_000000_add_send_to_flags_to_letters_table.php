<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('letters', function (Blueprint $table) {
            if (!Schema::hasColumn('letters', 'send_to_applicant')) {
                $table->boolean('send_to_applicant')->default(true)->after('cc');
            }
            if (!Schema::hasColumn('letters', 'send_to_respondent')) {
                $table->boolean('send_to_respondent')->default(true)->after('send_to_applicant');
            }
        });
    }

    public function down(): void
    {
        Schema::table('letters', function (Blueprint $table) {
            if (Schema::hasColumn('letters', 'send_to_respondent')) {
                $table->dropColumn('send_to_respondent');
            }
            if (Schema::hasColumn('letters', 'send_to_applicant')) {
                $table->dropColumn('send_to_applicant');
            }
        });
    }
};
