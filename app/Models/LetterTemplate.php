<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use OwenIt\Auditing\Auditable;

class LetterTemplate extends Model implements AuditableContract
{
    use Auditable;

    protected $fillable = [
        'title',
        'category',
        'subject_prefix',
        'reference_sequence',
        'placeholders',
        'body',
        'header_image_path',
        'footer_image_path',
    ];

    protected $casts = [
        'placeholders' => 'array',
        'reference_sequence' => 'integer',
    ];
}
