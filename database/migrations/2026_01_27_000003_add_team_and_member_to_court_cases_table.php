<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('court_cases')) {
            return;
        }

        Schema::table('court_cases', function (Blueprint $table) {
            if (!Schema::hasColumn('court_cases', 'assigned_team_id')) {
                $table->foreignId('assigned_team_id')->nullable()
                    ->constrained('teams')->nullOnDelete()->after('assigned_user_id');
            }
            if (!Schema::hasColumn('court_cases', 'assigned_member_user_id')) {
                $table->foreignId('assigned_member_user_id')->nullable()
                    ->constrained('users')->nullOnDelete()->after('assigned_team_id');
            }
        });
    }

    public function down()
    {
        if (!Schema::hasTable('court_cases')) {
            return;
        }

        Schema::table('court_cases', function (Blueprint $table) {
            if (Schema::hasColumn('court_cases', 'assigned_member_user_id')) {
                $table->dropForeign(['assigned_member_user_id']);
                $table->dropColumn('assigned_member_user_id');
            }
            if (Schema::hasColumn('court_cases', 'assigned_team_id')) {
                $table->dropForeign(['assigned_team_id']);
                $table->dropColumn('assigned_team_id');
            }
        });
    }
};
