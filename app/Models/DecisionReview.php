<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DecisionReview extends Model
{
    use HasFactory;

    protected $fillable = [
        'decision_id',
        'case_number',
        'reviewer_id',
        'review_note',
        'outcome',
    ];

    public function decision()
    {
        return $this->belongsTo(Decision::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }
}
