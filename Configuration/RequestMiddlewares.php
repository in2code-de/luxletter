<?php

use In2code\Luxletter\Middleware\LuxletterLink;

return [
    'frontend' => [
        'luxletter-luxletterlink' => [
            'target' => LuxletterLink::class,
            'after' => [
                'typo3/cms-frontend/tsfe',
            ],
        ],
    ],
];
