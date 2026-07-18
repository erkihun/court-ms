<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('system_audits', function (Blueprint $table) {
            $table->string('request_id', 128)->nullable()->index()->after('id');
            $table->string('outcome', 32)->default('success')->index()->after('action');
            $table->unsignedSmallInteger('response_status')->nullable()->after('method');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('system_audits', function (Blueprint $table) {
            $table->dropIndex(['request_id']);
            $table->dropIndex(['outcome']);
            $table->dropColumn(['request_id', 'outcome', 'response_status']);
        });
    }
};
