<?php

declare(strict_types=1);
namespace In2code\Luxletter\Domain\Factory;

use DateTime;
use In2code\Luxletter\Domain\Model\User;
use In2code\Luxletter\Events\DummyUserEvent;
use Psr\EventDispatcher\EventDispatcherInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;

/**
 * Class UserFactory to get some dummy values for test newsletters
 */
class UserFactory
{
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @var array
     */
    protected static $dummyProperties = [
        'title' => 'Prof. Dr.',
        'firstName' => 'Max',
        'lastName' => 'Muster',
        'email' => 'max.muster@mail.org',
        'username' => 'mmuster',
        'name' => 'Max Muster',
        'middleName' => 'Markus',
        'address' => 'Teststr. 123',
        'city' => 'Rosenheim',
        'company' => 'Muster GmbH',
    ];

    /**
     * @return User
     */
    public function getDummyUser(): User
    {
        $user = GeneralUtility::makeInstance(User::class);
        foreach (self::$dummyProperties as $key => $value) {
            ObjectAccess::setProperty($user, $key, $value);
        }
        ObjectAccess::setProperty($user, 'crdate', new DateTime());

        $dummyUserEvent = GeneralUtility::makeInstance(DummyUserEvent::class, $user);
        $this->eventDispatcher->dispatch($dummyUserEvent);
        return $dummyUserEvent->getUser();
    }
}
