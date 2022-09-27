<?php

declare(strict_types=1);
namespace In2code\Luxletter\Domain\Model;

use TYPO3\CMS\Extbase\Domain\Model\FrontendUserGroup;

/**
 * Class Usergroup
 */
class Usergroup extends FrontendUserGroup
{
    const TABLE_NAME = 'fe_groups';

    /**
     * @var bool
     */
    protected $luxletterReceiver = false;

    /**
     * @return bool
     */
    public function isLuxletterReceiver(): bool
    {
        return $this->luxletterReceiver;
    }

    /**
     * @param bool $luxletterReceiver
     * @return Usergroup
     */
    public function setLuxletterReceiver(bool $luxletterReceiver): self
    {
        $this->luxletterReceiver = $luxletterReceiver;
        return $this;
    }
}
