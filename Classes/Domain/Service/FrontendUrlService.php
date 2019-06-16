<?php
declare(strict_types=1);
namespace In2code\Luxletter\Domain\Service;

use In2code\Luxletter\Utility\FrontendUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

/**
 * Class FrontendUrlService
 * Todo: Currently only the current backend domain is used for the FE process. This should be reworked with a frontend
 * context that it's possible to get different domains if there are more page branches in TYPO3
 */
class FrontendUrlService
{

    /**
     * @param string $parameter
     * @return string
     */
    public function getTypolinkFromParameter(string $parameter): string
    {
        $contentObject = GeneralUtility::makeInstance(ContentObjectRenderer::class);
        $url = FrontendUtility::getCurrentUri() . ltrim($contentObject->typoLink_URL(['parameter' => $parameter]), '/');
        return $url;
    }
}
