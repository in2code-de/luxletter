<?php
$GLOBALS['SiteConfiguration']['site']['columns']['luxletterUnsubscribePid'] = [
    'label' => 'Unsubscribe PID',
    'description' => 'IMPORTANT: "Entry Point" in tab "General" must not contain a simple "/" but a full domain name like "https://domain.org/" to allow Luxletter to create url from CLI context!',
    'config' => [
        'type' => 'input',
        'eval' => 'required',
    ],
];

$GLOBALS['SiteConfiguration']['site']['types']['0']['showitem'] .= ', --div--;Luxletter, luxletterUnsubscribePid';
