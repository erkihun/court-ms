<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LetterTemplate extends Model
{
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
