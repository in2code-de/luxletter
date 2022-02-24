<?php

use In2code\Luxletter\Controller\NewsletterController;

return [
    '/luxletter/testMail' => [
        'path' => '/luxletter/testMail',
        'target' => NewsletterController::class . '::testMailAjax',
    ],
    '/luxletter/previewSources' => [
        'path' => '/luxletter/previewSources',
        'target' => NewsletterController::class . '::previewSourcesAjax',
    ],
    '/luxletter/wizardUserPreview' => [
        'path' => '/luxletter/wizardUserPreview',
        'target' => NewsletterController::class . '::wizardUserPreviewAjax',
    ],
    '/luxletter/receiverdetail' => [
        'path' => '/luxletter/receiverdetail',
        'target' => NewsletterController::class . '::receiverDetailAjax',
    ],
];
