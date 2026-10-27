<?php

// The route list the React screens read (resources/js/ziggy.js, `php artisan ziggy:generate resources/js/ziggy.js`).
// Development tooling stays out of it so the committed file is the same on every machine.
return [
    'except' => ['debugbar.*', 'ignition.*', 'telescope*', 'horizon.*', 'sanctum.*', 'livewire.*', 'storage.local'],
];
