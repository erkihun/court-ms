<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('respondent_responses', function (Blueprint $table) {
            $table->string('review_status')->default('awaiting_review')->after('pdf_path');
            $table->text('review_note')->nullable()->after('review_status');
            $table->foreignId('reviewed_by_user_id')->nullable()->after('review_note')
                ->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable()->after('reviewed_by_user_id');
        });
    }

    public function down(): void
    {
        Schema::table('respondent_responses', function (Blueprint $table) {
            $table->dropConstrainedForeignId('reviewed_by_user_id');
            $table->dropColumn(['review_status', 'review_note', 'reviewed_at']);
        });
    }
};
