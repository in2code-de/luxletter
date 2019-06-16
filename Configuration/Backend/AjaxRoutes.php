<?php
return [
    '/luxletter/wizardUserPreview' => [
        'path' => '/luxletter/wizardUserPreview',
        'target' => \In2code\Luxletter\Controller\NewsletterController::class . '::wizardUserPreviewAjax',
    ],
    '/luxletter/wizardNewsletterPreview' => [
        'path' => '/luxletter/wizardNewsletterPreview',
        'target' => \In2code\Luxletter\Controller\NewsletterController::class . '::wizardNewsletterPreviewAjax',
    ]
];
