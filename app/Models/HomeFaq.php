<?php

namespace App\Models;

use App\Models\Concerns\HasAmharicText;
use Illuminate\Database\Eloquent\Model;

class HomeFaq extends Model
{
    use HasAmharicText;

    protected $fillable = ['sort_order', 'is_active', 'question', 'answer'];

    protected $casts = ['is_active' => 'boolean'];

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }
}
