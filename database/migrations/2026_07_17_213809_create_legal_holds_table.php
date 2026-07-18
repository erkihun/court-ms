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
        Schema::create('legal_holds', function (Blueprint $table) {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->string('holdable_type', 100);
            $table->unsignedBigInteger('holdable_id');
            $table->text('reason');
            $table->unsignedBigInteger('placed_by_user_id')->nullable()->index();
            $table->timestamp('placed_at');
            $table->unsignedBigInteger('released_by_user_id')->nullable()->index();
            $table->timestamp('released_at')->nullable();
            $table->text('release_reason')->nullable();
            $table->timestamps();

            $table->index(['holdable_type', 'holdable_id', 'released_at'], 'legal_holds_active_lookup');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('legal_holds');
    }
};
