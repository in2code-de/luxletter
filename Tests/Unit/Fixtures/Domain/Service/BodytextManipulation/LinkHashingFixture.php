<?php

namespace In2code\Luxletter\Tests\Unit\Fixtures\Domain\Service\BodytextManipulation;

use In2code\Luxletter\Domain\Model\Newsletter;
use In2code\Luxletter\Domain\Model\User;
use In2code\Luxletter\Domain\Service\BodytextManipulation\LinkHashing;

class LinkHashingFixture extends LinkHashing
{
    public function __construct(Newsletter $newsletter, User $user)
    {
        $this->newsletter = $newsletter;
        $this->user = $user;
    }
}
