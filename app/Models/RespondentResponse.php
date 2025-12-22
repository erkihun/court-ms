<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RespondentResponse extends Model
{
    protected $table = 'respondent_responses';

    protected $fillable = [
        'respondent_id',
        'case_number',
        'title',
        'description',
        'pdf_path',
        'review_status',
        'review_note',
        'reviewed_by_user_id',
        'reviewed_at',
    ];

    public function respondent(): BelongsTo
    {
        return $this->belongsTo(Respondent::class);
    }
}
