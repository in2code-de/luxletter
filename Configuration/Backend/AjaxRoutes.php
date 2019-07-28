<?php
return [
    '/luxletter/wizardUserPreview' => [
        'path' => '/luxletter/wizardUserPreview',
        'target' => \In2code\Luxletter\Controller\NewsletterController::class . '::wizardUserPreviewAjax',
    ],
    '/luxletter/testMail' => [
        'path' => '/luxletter/testMail',
        'target' => \In2code\Luxletter\Controller\NewsletterController::class . '::testMailAjax',
    ],
    '/luxletter/receiverdetail' => [
        'path' => '/luxletter/receiverdetail',
        'target' => \In2code\Luxletter\Controller\NewsletterController::class . '::receiverDetailAjax',
    ]
];
