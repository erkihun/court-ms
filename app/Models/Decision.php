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
        'approved_at',
        'approved_by',
    ];

    /**
     * Attribute casts.
     */
    protected $casts = [
        'case_filed_date' => 'date',
        'decision_date' => 'date',
        'panel_judges' => 'array',
        'reviewing_admin_user_names' => 'array',
        'approved_at' => 'datetime',
    ];

    public function courtCase(): BelongsTo
    {
        return $this->belongsTo(CourtCase::class, 'court_case_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewing_admin_user_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function reviews()
    {
        return $this->hasMany(DecisionReview::class);
    }

    public function isApproved(): bool
    {
        return $this->approved_at !== null;
    }

    public function isPublished(): bool
    {
        return $this->status === 'published';
    }

    /**
     * Parties (applicant/respondent) may download the decision only once it is
     * both approved (sealed) and published.
     */
    public function isDownloadableByParties(): bool
    {
        return $this->isApproved() && $this->isPublished();
    }
}
