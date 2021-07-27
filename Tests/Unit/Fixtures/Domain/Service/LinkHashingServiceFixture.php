<?php
namespace In2code\Luxletter\Tests\Unit\Fixtures\Domain\Service;

use In2code\Luxletter\Domain\Model\Newsletter;
use In2code\Luxletter\Domain\Model\User;
use In2code\Luxletter\Domain\Service\LinkHashingService;

/**
 * Class RedirectUriServiceFixture
 */
class LinkHashingServiceFixture extends LinkHashingService
{
    /**
     * LinkHashingServiceFixture constructor.
     * @param Newsletter $newsletter
     * @param User $user
     */
    public function __construct(Newsletter $newsletter, User $user)
    {
        $this->newsletter = $newsletter;
        $this->user = $user;
    }
}
