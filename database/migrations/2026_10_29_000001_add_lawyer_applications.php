<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Lawyers apply to be registered (/lawyer/register, EUISIS ADR 0013 #13 as
 * amended on 2026-10-29): when they applied, and the registry's refusal with
 * its reason. Verification stays `lawyer_verified_at`. Additive only; down()
 * removes exactly these columns.
 */
return new class extends Migration
{
    private const COLUMNS = ['lawyer_applied_at', 'lawyer_refused_at', 'lawyer_refusal_reason'];

    public function up(): void
    {
        Schema::table('applicants', function (Blueprint $table): void {
            if (! Schema::hasColumn('applicants', 'lawyer_applied_at')) {
                $table->timestamp('lawyer_applied_at')->nullable()->index();
            }
            if (! Schema::hasColumn('applicants', 'lawyer_refused_at')) {
                $table->timestamp('lawyer_refused_at')->nullable();
            }
            if (! Schema::hasColumn('applicants', 'lawyer_refusal_reason')) {
                $table->text('lawyer_refusal_reason')->nullable();
            }
        });
    }

    public function down(): void
    {
        foreach (self::COLUMNS as $column) {
            if (Schema::hasColumn('applicants', $column)) {
                Schema::table('applicants', function (Blueprint $table) use ($column): void {
                    if ($column === 'lawyer_applied_at') {
                        $table->dropIndex(['lawyer_applied_at']);
                    }
                    $table->dropColumn($column);
                });
            }
        }
    }
};
