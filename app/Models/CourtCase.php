<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use App\Models\Applicant;
use App\Models\CaseType;
use App\Models\User;

class CourtCase extends Model
{
    protected $table = 'court_cases';
    protected $guarded = [];        // or list explicit fillables if you prefer
    public $timestamps = true;      // if your table has created_at/updated_at
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'first_hearing_date' => 'date',
        'filing_date' => 'date',
    ];

    public function judge(): BelongsTo
    {
        return $this->belongsTo(User::class, 'judge_id');
    }

    public function caseType(): BelongsTo
    {
        return $this->belongsTo(CaseType::class, 'case_type_id');
    }

    public function applicant(): BelongsTo
    {
        return $this->belongsTo(Applicant::class, 'applicant_id');
    }
}
