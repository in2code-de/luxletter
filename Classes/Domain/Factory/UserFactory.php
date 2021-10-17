<?php
declare(strict_types = 1);
namespace In2code\Luxletter\Domain\Factory;

use In2code\Luxletter\Domain\Model\User;
use In2code\Luxletter\Signal\SignalTrait;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\Exception;
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
     * @throws Exception
     */
    public function getDummyUser(): User
    {
        $user = GeneralUtility::makeInstance(User::class);
        foreach (self::$dummyProperties as $key => $value) {
            ObjectAccess::setProperty($user, $key, $value);
        }
        $this->signalDispatch(__CLASS__, __FUNCTION__, [$user]);
        return $user;
    }
}
