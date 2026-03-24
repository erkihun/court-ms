<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('applicant_response_replies')) {
            return;
        }

        Schema::table('applicant_response_replies', function (Blueprint $table) {
            if (!Schema::hasColumn('applicant_response_replies', 'review_status')) {
                $table->string('review_status', 32)->default('awaiting_review')->after('pdf_path');
            }
            if (!Schema::hasColumn('applicant_response_replies', 'review_note')) {
                $table->text('review_note')->nullable()->after('review_status');
            }
            if (!Schema::hasColumn('applicant_response_replies', 'reviewed_by_user_id')) {
                $table->foreignId('reviewed_by_user_id')->nullable()->after('review_note')->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('applicant_response_replies', 'reviewed_at')) {
                $table->timestamp('reviewed_at')->nullable()->after('reviewed_by_user_id');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('applicant_response_replies')) {
            return;
        }

        Schema::table('applicant_response_replies', function (Blueprint $table) {
            if (Schema::hasColumn('applicant_response_replies', 'reviewed_by_user_id')) {
                $table->dropConstrainedForeignId('reviewed_by_user_id');
            }

            $dropColumns = [];
            foreach (['review_status', 'review_note', 'reviewed_at'] as $column) {
                if (Schema::hasColumn('applicant_response_replies', $column)) {
                    $dropColumns[] = $column;
                }
            }
            if (!empty($dropColumns)) {
                $table->dropColumn($dropColumns);
            }
        });
    }
};
