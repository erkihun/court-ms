<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use OwenIt\Auditing\Auditable;

class Letter extends Model implements AuditableContract
{
    use HasFactory, Auditable;

    protected $fillable = [
        'letter_template_id',
        'user_id',
        'recipient_name',
        'recipient_title',
        'recipient_company',
        'subject',
        'reference_number',
        'case_number',
        'body',
        'cc',
        'approved_by_name',
        'approved_by_title',
        'approval_status',
    ];

    public function template(): BelongsTo
    {
        return $this->belongsTo(LetterTemplate::class, 'letter_template_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
