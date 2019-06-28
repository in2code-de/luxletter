<?php
return [
    'frontend' => [
        'luxletter-luxletterlink' => [
            'target' => \In2code\Luxletter\Middleware\LuxletterLink::class,
            'after' => [
                'typo3/cms-frontend/timetracker'
            ]
        ]
    ]
];
