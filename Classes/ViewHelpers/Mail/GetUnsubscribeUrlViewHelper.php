<?php

declare(strict_types=1);
namespace In2code\Luxletter\ViewHelpers\Mail;

use In2code\Luxletter\Domain\Model\Newsletter;
use In2code\Luxletter\Domain\Model\User;
use In2code\Luxletter\Domain\Service\UnsubscribeUrlService;
use In2code\Luxletter\Exception\MisconfigurationException;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class GetUnsubscribeUrlViewHelper extends AbstractViewHelper
{
    public function initializeArguments(): void
    {
        parent::initializeArguments();
        $this->registerArgument('newsletter', Newsletter::class, 'Newsletter object');
        $this->registerArgument('user', User::class, 'User object');
        $this->registerArgument('site', Site::class, 'Site object', true);
        $this->registerArgument('language', 'int', 'Language identifier', false, 0);
        $this->registerArgument('additionalArguments', 'array', 'Further link arguments', false, []);
    }

    /**
     * @return string
     * @throws MisconfigurationException
     */
    public function render(): string
    {
        $unsubscribeUrlService = GeneralUtility::makeInstance(
            UnsubscribeUrlService::class,
            $this->arguments['newsletter'],
            $this->arguments['user'],
            $this->arguments['site'],
            $this->arguments['language'],
            $this->arguments['additionalArguments']
        );
        return $unsubscribeUrlService->get();
    }
}
