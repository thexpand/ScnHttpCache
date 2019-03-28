<?php

use Thexpand\Zf2HttpCache\Service\EsiViewHelperFactory;

return [
    'view_helpers' => [
        'factories' => [
            'esi' => EsiViewHelperFactory::class,
        ],
        'shared'    => [
            'esi' => false,
        ],
    ],
];
