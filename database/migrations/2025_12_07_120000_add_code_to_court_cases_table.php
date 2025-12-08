<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('court_cases', function (Blueprint $table) {
            if (!Schema::hasColumn('court_cases', 'code')) {
                $table->string('code', 8)->nullable()->unique()->after('case_number');
            }
        });

        if (!Schema::hasColumn('court_cases', 'code')) {
            return;
        }

        $used = DB::table('court_cases')
            ->whereNotNull('code')
            ->pluck('code')
            ->all();

        $existingIds = DB::table('court_cases')
            ->whereNull('code')
            ->pluck('id')
            ->all();

        foreach ($existingIds as $id) {
            $code = $this->uniqueCode($used);
            DB::table('court_cases')->where('id', $id)->update(['code' => $code]);
            $used[] = $code;
        }
    }

    public function down(): void
    {
        Schema::table('court_cases', function (Blueprint $table) {
            if (Schema::hasColumn('court_cases', 'code')) {
                $table->dropUnique(['code']);
                $table->dropColumn('code');
            }
        });
    }

    private function uniqueCode(array $used): string
    {
        do {
            $code = str_pad((string) random_int(0, 99999), 5, '0', STR_PAD_LEFT);
        } while (in_array($code, $used, true));

        return $code;
    }
};
