<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CourtCase extends Model
{
    protected $table = 'court_cases';
    protected $guarded = [];        // or list explicit fillables if you prefer
    public $timestamps = true;      // if your table has created_at/updated_at
}
