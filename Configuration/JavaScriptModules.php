<?php

return [
    'dependencies' => [
        'core',
    ],
    'imports' => [
        '@in2code/luxletter/' => [
            'path' => 'EXT:luxletter/Resources/Public/JavaScript/Luxletter/',
        ],
        '@in2code/luxletter/vendor/chartjs.js' => 'EXT:luxletter/Resources/Public/JavaScript/Vendor/Chart.min.js',
        '@in2code/luxletter/vendor/choices.js' => 'EXT:luxletter/Resources/Public/JavaScript/Vendor/Choices.min.js',
    ],
];
