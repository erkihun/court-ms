<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Backfill existing duplicate references so unique index creation does not fail.
        $duplicates = DB::table('letters')
            ->select('reference_number', DB::raw('COUNT(*) as total'))
            ->whereNotNull('reference_number')
            ->where('reference_number', '!=', '')
            ->groupBy('reference_number')
            ->having('total', '>', 1)
            ->get();

        foreach ($duplicates as $dup) {
            $rows = DB::table('letters')
                ->where('reference_number', $dup->reference_number)
                ->orderBy('id')
                ->get(['id', 'reference_number']);

            $seen = 0;
            foreach ($rows as $row) {
                $seen++;
                if ($seen === 1) {
                    continue; // Keep the first occurrence unchanged.
                }

                $base = (string) $row->reference_number;
                $suffix = '-DUP' . $seen;
                $candidate = substr($base, 0, 50 - strlen($suffix)) . $suffix;
                $inc = 1;

                while (
                    DB::table('letters')
                        ->where('reference_number', $candidate)
                        ->where('id', '!=', $row->id)
                        ->exists()
                ) {
                    $suffix = '-DUP' . $seen . '-' . $inc;
                    $candidate = substr($base, 0, 50 - strlen($suffix)) . $suffix;
                    $inc++;
                }

                DB::table('letters')
                    ->where('id', $row->id)
                    ->update(['reference_number' => $candidate]);
            }
        }

        Schema::table('letters', function (Blueprint $table) {
            $table->unique('reference_number', 'letters_reference_number_unique');
        });
    }

    public function down(): void
    {
        Schema::table('letters', function (Blueprint $table) {
            $table->dropUnique('letters_reference_number_unique');
        });
    }
};
