<?php

return [
    'luxletter' => [
        'title' =>
            'LLL:EXT:luxletter/Resources/Private/Language/locallang_db.xlf:module.dashboard.preset.luxletter.title',
        'description' =>
            'LLL:EXT:luxletter/Resources/Private/Language/locallang_db.xlf:module.dashboard.preset.luxletter.description',
        'iconIdentifier' => 'extension-luxletter',
        'defaultWidgets' => [
            'luxletterReceiver',
            'luxletterOpenRate',
            'luxletterClickRate',
            'luxletterUnsubscribeRate',
            'luxletterNewsletter',
            'luxletterLastNewslettersOpenRate',
            'luxletterLastNewslettersClickRate',
        ],
        'showInWizard' => true,
    ],
];
