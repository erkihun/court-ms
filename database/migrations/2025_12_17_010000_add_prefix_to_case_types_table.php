<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Table already has a 'prefix' column in this environment; no action required.
        // If you ever need to add a different prefix column, adjust this migration accordingly.
    }

    public function down(): void
    {
        // Nothing to rollback since we didn't add a column here.
    }
};
