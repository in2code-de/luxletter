<?php

use In2code\Lux\Exception\FileNotFoundException;

if (empty($webRoot = getenv('TYPO3_PATH_WEB'))) {
    putenv('TYPO3_PATH_WEB=' . $webRoot = realpath(__DIR__ . '/../../.Build/public') . '/');
} else {
    $webRoot = rtrim($webRoot, '/') . '/';
}
$buildRoot = realpath($webRoot . '/..');
$autoload = $buildRoot . '/vendor/autoload.php';
if (!file_exists($autoload)) {
    throw new FileNotFoundException('Can not find autoload path', 1684150120);
}

$bootstrapLoaded = false;
$bootstrapOldschool = $webRoot . 'typo3/sysext/core/Build/UnitTestsBootstrap.php';
$bootstrapVendor = $buildRoot . '/vendor/typo3/testing-framework/Resources/Core/Build/UnitTestsBootstrap.php';
if (file_exists($bootstrapOldschool)) {
    require($bootstrapOldschool);
    $bootstrapLoaded = true;
} elseif (file_exists($bootstrapVendor)) {
    require($bootstrapVendor);
    $bootstrapLoaded = true;
}
if ($bootstrapLoaded === false) {
    throw new FileNotFoundException(
        'Can not find unit test bootstrap file. Did you do a composer update?',
        1684150123
    );
}
