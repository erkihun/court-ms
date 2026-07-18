<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class CaseEvidence extends Model
{
    protected $table = 'case_evidences';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['size' => 'integer'];
    }

    public function courtCase(): BelongsTo
    {
        return $this->belongsTo(CourtCase::class, 'case_id');
    }
}
