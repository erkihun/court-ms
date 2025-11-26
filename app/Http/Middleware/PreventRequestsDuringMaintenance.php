<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance as Middleware;

class PreventRequestsDuringMaintenance extends Middleware
{
    /**
     * URIs that should be reachable while in maintenance mode.
     * Add webhook endpoints etc. if needed.
     */
    protected $except = [
        //
    ];
}
