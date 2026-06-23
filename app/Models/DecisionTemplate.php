<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use OwenIt\Auditing\Auditable;

class DecisionTemplate extends Model implements AuditableContract
{
    use Auditable;

    protected $fillable = [
        'title',
        'category',
        'is_default',
        'placeholders',
        'body',
        'header_image_path',
        'footer_image_path',
    ];

    protected $casts = [
        'placeholders' => 'array',
        'is_default' => 'boolean',
    ];

    /**
     * The default template used when generating a decision's final output
     * without an explicitly chosen template.
     */
    public static function default(): ?self
    {
        return static::where('is_default', true)->first()
            ?? static::orderBy('id')->first();
    }
}
