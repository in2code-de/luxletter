<?php

namespace In2code\Luxletter\Tests\Unit\Controller;

use In2code\Luxletter\Controller\FrontendController;
use In2code\Luxletter\Domain\Model\Newsletter;
use In2code\Luxletter\Domain\Model\User;
use In2code\Luxletter\Domain\Model\Usergroup;
use In2code\Luxletter\Exception\ArgumentMissingException;
use In2code\Luxletter\Exception\AuthenticationFailedException;
use In2code\Luxletter\Exception\DisallowedArgumentException;
use In2code\Luxletter\Exception\MissingRelationException;
use In2code\Luxletter\Tests\Unit\Fixtures\DomainObjectTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use TYPO3\TestingFramework\Core\AccessibleObjectInterface;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

#[CoversClass(FrontendController::class)]
class FrontendControllerTest extends UnitTestCase
{
    use DomainObjectTrait;

    protected AccessibleObjectInterface&FrontendController $subject;

    public function setUp(): void
    {
        parent::setUp();
        $accessibleClassName = $this->buildAccessibleProxy(FrontendController::class);
        $this->subject = (new \ReflectionClass($accessibleClassName))->newInstanceWithoutConstructor();
    }

    /**
     * @return void
     */
    public function testCheckArgumentsForUnsubscribeThrowsExceptionWithoutUser(): void
    {
        $this->expectException(ArgumentMissingException::class);
        $this->expectExceptionCode(1562050511);
        $this->subject->_call('checkArgumentsForUnsubscribe', null, new Newsletter(), 'hash');
    }

    /**
     * @return void
     */
    public function testCheckArgumentsForUnsubscribeThrowsExceptionWithoutNewsletter(): void
    {
        $this->expectException(ArgumentMissingException::class);
        $this->expectExceptionCode(1562267031);
        $this->subject->_call('checkArgumentsForUnsubscribe', $this->getUserStub('hash'), null, 'hash');
    }

    /**
     * @return void
     */
    public function testCheckArgumentsForUnsubscribeThrowsExceptionWithoutHash(): void
    {
        $this->expectException(ArgumentMissingException::class);
        $this->expectExceptionCode(1562050533);
        $this->subject->_call('checkArgumentsForUnsubscribe', $this->getUserStub('hash'), new Newsletter(), '');
    }

    /**
     * @return void
     */
    public function testCheckArgumentsForUnsubscribeThrowsExceptionWithWrongHash(): void
    {
        $this->expectException(AuthenticationFailedException::class);
        $this->expectExceptionCode(1562069583);
        $this->subject->_call('checkArgumentsForUnsubscribe', $this->getUserStub('correct'), new Newsletter(), 'wrong');
    }

    /**
     * @return void
     */
    public function testCheckArgumentsForUnsubscribePassesWithCorrectHash(): void
    {
        $user = $this->getUserStub('correct');
        $this->subject->_call('checkArgumentsForUnsubscribe', $user, new Newsletter(), 'correct');
        $this->addToAssertionCount(1);
    }

    /**
     * @return void
     */
    public function testCheckForUsergroupsPassesIfUserIsInOneOfTheReceiverGroups(): void
    {
        $receiverGroup1 = $this->getUsergroup(1);
        $receiverGroup2 = $this->getUsergroup(2);
        $newsletter = $this->getNewsletterWithReceivers($receiverGroup1, $receiverGroup2);
        $user = new User();
        $user->addUsergroup($this->getUsergroup(3));
        $user->addUsergroup($receiverGroup2);

        $this->subject->_call('checkForUsergroups', $user, $newsletter);
        $this->addToAssertionCount(1);
    }

    /**
     * @return void
     */
    public function testCheckForUsergroupsThrowsExceptionIfUserIsNotInAnyReceiverGroup(): void
    {
        $newsletter = $this->getNewsletterWithReceivers($this->getUsergroup(1), $this->getUsergroup(2));
        $user = new User();
        $user->addUsergroup($this->getUsergroup(3));

        $this->expectException(MissingRelationException::class);
        $this->expectExceptionCode(1562066292);
        $this->subject->_call('checkForUsergroups', $user, $newsletter);
    }

    /**
     * @return void
     */
    public function testCheckForAllowedUsergroupsPassesWithAllowedUsergroups(): void
    {
        $this->subject->_call('checkForAllowedUsergroups', [1, 3], [1, 2, 3]);
        $this->subject->_call('checkForAllowedUsergroups', [], [1, 2, 3]);
        $this->addToAssertionCount(1);
    }

    /**
     * @return void
     */
    public function testCheckForAllowedUsergroupsThrowsExceptionWithDisallowedUsergroup(): void
    {
        $this->expectException(DisallowedArgumentException::class);
        $this->expectExceptionCode(1693909396);
        $this->subject->_call('checkForAllowedUsergroups', [1, 4], [1, 2, 3]);
    }

    protected function getUserStub(string $unsubscribeHash): User
    {
        $user = self::createStub(User::class);
        $user->method('getUnsubscribeHash')->willReturn($unsubscribeHash);
        return $user;
    }

    protected function getNewsletterWithReceivers(Usergroup ...$usergroups): Newsletter
    {
        $receivers = new ObjectStorage();
        foreach ($usergroups as $usergroup) {
            $receivers->attach($usergroup);
        }
        $newsletter = new Newsletter();
        $newsletter->setReceivers($receivers);
        return $newsletter;
    }
}
