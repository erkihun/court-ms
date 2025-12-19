<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            // SQLite cannot run ALTER ... CHANGE COLUMN; the original enum is stored as TEXT.
            return;
        }

        DB::statement(
            "ALTER TABLE admin_notification_reads 
             CHANGE COLUMN `type` `type` ENUM('message','case','hearing','respondent_view') NOT NULL"
        );
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        DB::statement(
            "ALTER TABLE admin_notification_reads 
             CHANGE COLUMN `type` `type` ENUM('message','case','hearing') NOT NULL"
        );
    }
};
