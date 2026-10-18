<?php

declare(strict_types=1);

/*
| Routes exposed to the frontend's route() helper. Internal tooling never
| needs a client-side URL, so it is left out of the list sent to browsers.
*/
return [
    'except' => [
        '_debugbar.*',
        'debugbar.*',
        'pulse',
        'pulse.*',
        'livewire.*',
        'default-livewire.*',
        'sanctum.*',
        'storage.*',
        'ignition.*',
        'i18n.messages',
    ],
];
