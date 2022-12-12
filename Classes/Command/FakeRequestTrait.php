<?php

declare(strict_types=1);
namespace In2code\Luxletter\Command;

use TYPO3\CMS\Core\Core\SystemEnvironmentBuilder;
use TYPO3\CMS\Core\Http\ServerRequest;

/**
 * For whatever reason it seems not possible to create an instance of an extbase repository in a symfony command
 * in TYPO3 12. So now we have to fake a request.
 */
trait FakeRequestTrait
{
    protected function fakeRequest()
    {
        if (!isset($GLOBALS['TYPO3_REQUEST'])) {
            $request = (new ServerRequest())
                ->withAttribute('applicationType', SystemEnvironmentBuilder::REQUESTTYPE_BE);
            $GLOBALS['TYPO3_REQUEST'] = $request;
        }
    }
}
