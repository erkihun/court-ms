<?php

namespace App\Models;

use App\Models\CourtCase;
use App\Models\CaseHearing;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BenchNote extends Model
{
    protected $fillable = [
        'case_id',
        'hearing_id',
        'user_id',
        'title',
        'note',
        'judge_one_id',
        'judge_two_id',
        'judge_three_id',
    ];

    public function case(): BelongsTo
    {
        return $this->belongsTo(CourtCase::class, 'case_id');
    }

    public function hearing(): BelongsTo
    {
        return $this->belongsTo(CaseHearing::class, 'hearing_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function judgeOne(): BelongsTo
    {
        return $this->belongsTo(User::class, 'judge_one_id');
    }

    public function judgeTwo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'judge_two_id');
    }

    public function judgeThree(): BelongsTo
    {
        return $this->belongsTo(User::class, 'judge_three_id');
    }
}
