<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** An EUISIS position mirrored from the EUISIS partner API. */
class EuisisPosition extends Model
{
    protected $table = 'euisis_positions';

    protected $primaryKey = 'uuid';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $guarded = [];

    protected $casts = ['synced_at' => 'datetime', 'last_sequence' => 'integer', 'is_active' => 'boolean'];

    public function displayTitle(): string
    {
        return app()->getLocale() === 'am' && filled($this->title_am) ? (string) $this->title_am : (string) $this->title_en;
    }
}
