<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemSetting extends Model
{
    protected $table = 'system_settings';

    protected $fillable = [
        'app_name',
        'short_name',
        'logo_path',
        'banner_path',
        'favicon_path',
        'contact_email',
        'contact_phone',
        'about',
        'maintenance_mode',
    ];

    protected $casts = [
        'maintenance_mode' => 'boolean',
    ];
}
