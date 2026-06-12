<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('performance_evaluation_categories', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->string('name_am')->nullable();
            $table->boolean('active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        $now = now();
        DB::table('performance_evaluation_categories')->insert([
            ['slug' => 'efficiency', 'name' => 'Efficiency', 'name_am' => 'ቅልጥፍና', 'active' => true, 'sort_order' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['slug' => 'quality', 'name' => 'Quality', 'name_am' => 'ጥራት', 'active' => true, 'sort_order' => 2, 'created_at' => $now, 'updated_at' => $now],
            ['slug' => 'conduct', 'name' => 'Conduct', 'name_am' => 'ሥነ ምግባር', 'active' => true, 'sort_order' => 3, 'created_at' => $now, 'updated_at' => $now],
            ['slug' => 'general', 'name' => 'General', 'name_am' => 'አጠቃላይ', 'active' => true, 'sort_order' => 4, 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('performance_evaluation_categories');
    }
};
