<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('performance_evaluation_criteria', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('name_am')->nullable();
            $table->string('category')->default('general');
            $table->unsignedTinyInteger('weight')->default(10);
            $table->text('description')->nullable();
            $table->boolean('active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        $now = now();
        DB::table('performance_evaluation_criteria')->insert([
            ['name' => 'Case Management',         'name_am' => 'የጉዳይ አስተዳደር',        'category' => 'efficiency', 'weight' => 20, 'description' => 'Timely handling and resolution of assigned cases.',         'active' => 1, 'sort_order' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Quality of Work',         'name_am' => 'የሥራ ጥራት',             'category' => 'quality',    'weight' => 20, 'description' => 'Accuracy, thoroughness and quality of decisions made.',   'active' => 1, 'sort_order' => 2, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Attendance & Punctuality','name_am' => 'መገኘት እና ሰዓት አከባበር',  'category' => 'conduct',    'weight' => 15, 'description' => 'Regularity of attendance and adherence to schedule.',    'active' => 1, 'sort_order' => 3, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Professionalism',         'name_am' => 'ሙያዊ ሥነ-ምግባር',        'category' => 'conduct',    'weight' => 15, 'description' => 'Professional conduct and ethical behavior.',             'active' => 1, 'sort_order' => 4, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Teamwork & Collaboration','name_am' => 'ቡድን ሥራ',              'category' => 'conduct',    'weight' => 15, 'description' => 'Ability to work effectively within the team.',           'active' => 1, 'sort_order' => 5, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Technical Knowledge',     'name_am' => 'ቴክኒካዊ እውቀት',         'category' => 'quality',    'weight' => 15, 'description' => 'Knowledge of laws, procedures and court regulations.',   'active' => 1, 'sort_order' => 6, 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('performance_evaluation_criteria');
    }
};
