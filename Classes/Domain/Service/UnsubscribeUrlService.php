<?php

declare(strict_types=1);

namespace In2code\Luxletter\Domain\Service;

use In2code\Luxletter\Domain\Model\Newsletter;
use In2code\Luxletter\Domain\Model\User;
use In2code\Luxletter\Events\UnsubscribeUrlEvent;
use In2code\Luxletter\Exception\MisconfigurationException;
use Psr\EventDispatcher\EventDispatcherInterface;
use Throwable;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class UnsubscribeUrlService
{
    protected ?Newsletter $newsletter;
    protected ?User $user;
    protected Site $site;
    protected int $language;
    protected SiteService $siteService;
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(?Newsletter $newsletter, ?User $user, Site $site, int $language)
    {
        $this->newsletter = $newsletter;
        $this->user = $user;
        $this->site = $site;
        $this->language = $language;
        $this->siteService = GeneralUtility::makeInstance(SiteService::class);
        $this->eventDispatcher = GeneralUtility::makeInstance(EventDispatcherInterface::class);
    }

    /**
     * @return string
     * @throws MisconfigurationException
     */
    public function get(): string
    {
        // In case of a preview or of a test mail
        $url = '#';

        if ($this->newsletter !== null && $this->user !== null) {
            $url = $this->getNewsletterUrl();
        }

        /** @var UnsubscribeUrlEvent $event */
        $event = $this->eventDispatcher->dispatch(
            new UnsubscribeUrlEvent(
                $url,
                $this->newsletter,
                $this->user,
                $this->site,
                $this->language
            )
        );

        return $event->getUrl();
    }

    /**
     * @return string
     * @throws MisconfigurationException
     */
    protected function getNewsletterUrl(): string
    {
        try {
            return $this->siteService->getPageUrlFromParameter(
                $this->getPidUnsubscribe(),
                [
                    'tx_luxletter_fe' => [
                        'user' => $this->user->getUid(),
                        'newsletter' => $this->newsletter->getUid(),
                        'hash' => $this->user->getUnsubscribeHash(),
                    ],
                    '_language' => $this->language,
                ]
            );
        } catch (Throwable $exception) {
            throw new MisconfigurationException(
                'Could not build a valid URL to unsubscribe page to pid' . $this->getPidUnsubscribe()
                . ' in site "' . $this->site->getIdentifier() . '".'
                . ' Message: ' . $exception->getMessage() . '(' . $exception->getCode() . ')',
                1646380245
            );
        }
    }

    /**
     * @return int
     * @throws MisconfigurationException
     */
    protected function getPidUnsubscribe(): int
    {
        $unsubscribePid = (int)($this->site->getConfiguration()['luxletterUnsubscribePid'] ?? 0);
        if ($unsubscribePid === 0) {
            throw new MisconfigurationException(
                'No unsubscribe page identifier found in site configuration',
                1682077001
            );
        }
        return $unsubscribePid;
    }
}
