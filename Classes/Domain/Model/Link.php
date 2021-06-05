<?php
declare(strict_types=1);
namespace In2code\Luxletter\Domain\Model;

use In2code\Luxletter\Domain\Service\SiteService;
use In2code\Luxletter\Exception\MisconfigurationException;
use In2code\Luxletter\Utility\StringUtility;
use TYPO3\CMS\Core\Exception\SiteNotFoundException;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

/**
 * Class Link
 */
class Link extends AbstractEntity
{
    const TABLE_NAME = 'tx_luxletter_domain_model_link';

    /**
     * @var \In2code\Luxletter\Domain\Model\Newsletter
     */
    protected $newsletter = null;

    /**
     * @var \In2code\Luxletter\Domain\Model\User
     */
    protected $user = null;

    /**
     * @var string
     */
    protected $hash = '';

    /**
     * @var string
     */
    protected $target = '';

    /**
     * @return Newsletter
     */
    public function getNewsletter(): ?Newsletter
    {
        return $this->newsletter;
    }

    /**
     * @param Newsletter $newsletter
     * @return Link
     */
    public function setNewsletter(Newsletter $newsletter): self
    {
        $this->newsletter = $newsletter;
        return $this;
    }

    /**
     * @return User
     */
    public function getUser(): ?User
    {
        return $this->user;
    }

    /**
     * @param User $user
     * @return Link
     */
    public function setUser(User $user): self
    {
        $this->user = $user;
        return $this;
    }

    /**
     * @return string
     */
    public function getHash(): string
    {
        return $this->hash;
    }

    /**
     * @param string $hash
     * @return Link
     */
    public function setHash(string $hash): self
    {
        $this->hash = $hash;
        return $this;
    }

    /**
     * Get hashed uri
     *
     * @return string
     * @throws MisconfigurationException
     * @throws SiteNotFoundException
     */
    public function getUriFromHash(): string
    {
        $site = $this->getNewsletter()->getConfiguration()->getSiteConfiguration();
        /** @var SiteService $siteService */
        $siteService = GeneralUtility::makeInstance(SiteService::class);
        return $siteService->getFrontendUrlFromParameter(['luxletterlink' => $this->getHash()], $site);
    }

    /**
     * @return string
     */
    public function getTarget(): string
    {
        return $this->target;
    }

    /**
     * @param string $target
     * @return Link
     */
    public function setTarget(string $target): self
    {
        $this->target = $target;
        $this->setHashFromTarget($target);
        return $this;
    }

    /**
     * @param string $target
     * @return void
     */
    private function setHashFromTarget(string $target): void
    {
        if ($this->getHash() === '' && $this->getNewsletter() !== null && $this->getUser() !== null) {
            $this->setHash($this->getHashFromTarget($target));
        }
    }

    /**
     * @param string $target
     * @return string
     */
    private function getHashFromTarget(string $target): string
    {
        $parts = [
            $target,
            $this->getUser()->getUid(),
            $this->getNewsletter()->getUid()
        ];
        return StringUtility::getHashFromArguments($parts);
    }
}
