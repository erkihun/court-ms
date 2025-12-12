<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\CourtCase;
use App\Models\User;

class Decision extends Model
{
    use HasFactory;

    /**
     * Mass assignable attributes.
     */
    protected $fillable = [
        'court_case_id',
        'case_number',
        'case_file_number',
        'applicant_full_name',
        'respondent_full_name',
        'case_filed_date',
        'decision_date',
        'panel_judges',
        'panel_decision',
        'judges_comments',
        'reviewing_admin_user_id',
        'reviewing_admin_user_name',
        'reviewing_admin_user_names',
        'name',
        'description',
        'decision_content',
        'status',
    ];

    /**
     * Attribute casts.
     */
    protected $casts = [
        'case_filed_date' => 'date',
        'decision_date' => 'date',
        'panel_judges' => 'array',
        'reviewing_admin_user_names' => 'array',
    ];

    public function courtCase(): BelongsTo
    {
        return $this->belongsTo(CourtCase::class, 'court_case_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewing_admin_user_id');
    }

    public function reviews()
    {
        return $this->hasMany(DecisionReview::class);
    }
}
