<?php

namespace In2code\Luxletter\Tests\Unit\Fixtures;

use In2code\Luxletter\Domain\Model\Newsletter;
use In2code\Luxletter\Domain\Model\User;
use In2code\Luxletter\Domain\Model\Usergroup;

trait DomainObjectTrait
{
    protected function getNewsletter(int $identifier): Newsletter
    {
        $newsletter = new Newsletter();
        $newsletter->_setProperty('uid', $identifier);
        return $newsletter;
    }

    protected function getUser(int $identifier): User
    {
        $user = new User();
        $user->_setProperty('uid', $identifier);
        return $user;
    }

    protected function getUsergroup(int $identifier): Usergroup
    {
        $usergroup = new Usergroup();
        $usergroup->_setProperty('uid', $identifier);
        return $usergroup;
    }
}
