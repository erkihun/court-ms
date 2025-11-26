<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('case_evidences', function (Blueprint $table) {
            if (!Schema::hasColumn('case_evidences', 'path')) {
                $table->string('path')->nullable()->after('description');
            }
            if (!Schema::hasColumn('case_evidences', 'mime')) {
                $table->string('mime', 120)->nullable()->after('path');
            }
            if (!Schema::hasColumn('case_evidences', 'size')) {
                $table->unsignedBigInteger('size')->nullable()->after('mime');
            }
        });
    }

    public function down(): void
    {
        Schema::table('case_evidences', function (Blueprint $table) {
            if (Schema::hasColumn('case_evidences', 'path')) {
                $table->dropColumn('path');
            }
            if (Schema::hasColumn('case_evidences', 'mime')) {
                $table->dropColumn('mime');
            }
            if (Schema::hasColumn('case_evidences', 'size')) {
                $table->dropColumn('size');
            }
        });
    }
};
