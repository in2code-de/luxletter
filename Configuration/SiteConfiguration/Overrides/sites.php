<?php

$configuration = [
    'label' => 'Unsubscribe PID',
    'description' => 'IMPORTANT: "Entry Point" in tab "General" must not contain a simple "/" but a full domain name like "https://domain.org/" to allow Luxletter to create url from CLI context!',
    'config' => [
        'type' => 'number',
        'required' => true,
    ],
];

// Todo: Can be removed if TYPO3 11 support is dropped
if (\In2code\Luxletter\Utility\ConfigurationUtility::isTypo3Version12() === false) {
    $configuration['config']['type'] = 'input';
    $configuration['config']['eval'] = 'required,int';
}

$GLOBALS['SiteConfiguration']['site']['columns']['luxletterUnsubscribePid'] = $configuration;

$GLOBALS['SiteConfiguration']['site']['types']['0']['showitem'] .= ', --div--;Luxletter, luxletterUnsubscribePid';
