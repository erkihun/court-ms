<?php
// database/migrations/2025_11_05_000001_add_review_fields_to_court_cases_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('court_cases', function (Blueprint $t) {
            $t->string('review_status', 30)->default('awaiting_review')->index();
            $t->text('review_note')->nullable();
            $t->foreignId('reviewed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamp('reviewed_at')->nullable();
        });

        // Optional: mark all existing rows as accepted so current cases stay visible
        DB::table('court_cases')->whereNull('review_status')->orWhere('review_status', '=', 'awaiting_review')
            ->update(['review_status' => 'accepted']);
    }

    public function down(): void
    {
        Schema::table('court_cases', function (Blueprint $t) {
            $t->dropColumn(['review_status', 'review_note', 'reviewed_by_user_id', 'reviewed_at']);
        });
    }
};
