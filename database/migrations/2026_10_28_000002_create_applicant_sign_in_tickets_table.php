<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
| One-time sign-in tickets EUISIS asks for when an employee opens the court
| portal from the EUISIS employee portal (docs/euisis-integration.md, Flow 4).
| Only the SHA-256 hash of a ticket is stored; a ticket lives two minutes and
| works once.
*/
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('applicant_sign_in_tickets')) {
            return;
        }

        Schema::create('applicant_sign_in_tickets', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('applicant_id')->constrained('applicants')->cascadeOnDelete();
            $table->foreignId('integration_client_id')->nullable()->constrained('integration_clients')->nullOnDelete();
            $table->char('token_hash', 64)->unique();
            $table->string('target', 120);
            $table->timestamp('expires_at');
            $table->timestamp('used_at')->nullable();
            $table->string('used_ip', 45)->nullable();
            $table->timestamps();
            $table->index(['applicant_id', 'expires_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('applicant_sign_in_tickets');
    }
};
