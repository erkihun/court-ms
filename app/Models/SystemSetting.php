<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use OwenIt\Auditing\Auditable;

class SystemSetting extends Model implements AuditableContract
{
    use Auditable;

    protected $table = 'system_settings';

    protected $fillable = [
        'app_name',
        'short_name',
        'logo_path',
        'banner_path',
        'favicon_path',
        'seal_path',
        'contact_email',
        'contact_phone',
        'about',
        'maintenance_mode',
    ];

    protected $casts = [
        'maintenance_mode' => 'boolean',
    ];
}
