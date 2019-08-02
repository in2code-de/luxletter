<?php
declare(strict_types=1);
namespace In2code\Luxletter\Domain\Factory;

use In2code\Luxletter\Domain\Model\User;
use In2code\Luxletter\Signal\SignalTrait;
use In2code\Luxletter\Utility\ObjectUtility;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;
use TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotException;
use TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotReturnException;

/**
 * Class UserFactory to get some dummy values for test newsletters
 */
class UserFactory
{
    use SignalTrait;

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
        'company' => 'Muster GmbH'
    ];

    /**
     * @return User
     * @throws InvalidSlotException
     * @throws InvalidSlotReturnException
     */
    public function getDummyUser(): User
    {
        $user = ObjectUtility::getObjectManager()->get(User::class);
        foreach (self::$dummyProperties as $key => $value) {
            ObjectAccess::setProperty($user, $key, $value);
        }
        $this->signalDispatch(__CLASS__, __FUNCTION__, [$user]);
        return $user;
    }
}
