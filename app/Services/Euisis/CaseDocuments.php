<?php

declare(strict_types=1);

namespace App\Services\Euisis;

use App\Services\SecureUploadService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/** A PDF that came from EUISIS, kept as a case's evidence document (as an applicant's evidence PDF). */
final class CaseDocuments
{
    public function store(int $caseId, UploadedFile $file, string $title, string $source): void
    {
        $stored = app(SecureUploadService::class)->store($file, 'evidences', 'private', [
            'related_type' => 'court_case',
            'related_id' => $caseId,
        ]);

        $insert = ['case_id' => $caseId, 'type' => 'document', 'created_at' => now(), 'updated_at' => now()];
        if (Schema::hasColumn('case_evidences', 'file_path')) {
            $insert['file_path'] = $stored;
        } elseif (Schema::hasColumn('case_evidences', 'path')) {
            $insert['path'] = $stored;
        }
        if (Schema::hasColumn('case_evidences', 'title')) {
            $insert['title'] = mb_substr($title, 0, 255);
        }
        if (Schema::hasColumn('case_evidences', 'description')) {
            $insert['description'] = $source;
        }
        if (Schema::hasColumn('case_evidences', 'mime')) {
            $insert['mime'] = $file->getClientMimeType() ?: 'application/pdf';
        }
        if (Schema::hasColumn('case_evidences', 'size')) {
            $insert['size'] = $file->getSize();
        }

        DB::table('case_evidences')->insert($insert);
    }
}
