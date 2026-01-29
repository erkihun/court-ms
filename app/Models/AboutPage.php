<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class AboutPage extends Model
{
    protected $table = 'about_pages';

    protected $fillable = [
        'title',
        'slug',
        'body',
        'is_published',
        'created_by_user_id',
        'updated_by_user_id',
    ];

    protected $casts = [
        'is_published' => 'boolean',
    ];

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by_user_id');
    }
}
