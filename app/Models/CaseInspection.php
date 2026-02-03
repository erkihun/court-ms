<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CaseInspection extends Model
{
    protected $table = 'case_inspections';

    protected $fillable = [
        'court_case_id',
        'inspection_date',
        'summary',
        'details',
        'inspected_by_user_id',
        'created_by_user_id',
        'updated_by_user_id',
    ];

    protected $casts = [
        'inspection_date' => 'date',
    ];

    public function case(): BelongsTo
    {
        return $this->belongsTo(CourtCase::class, 'court_case_id');
    }

    public function inspectedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'inspected_by_user_id');
    }
}
