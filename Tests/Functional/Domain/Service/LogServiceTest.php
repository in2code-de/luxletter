<?php

namespace In2code\Luxletter\Tests\Functional\Domain\Service;

use In2code\Luxletter\Domain\Model\Log;
use In2code\Luxletter\Domain\Service\LogService;
use In2code\Luxletter\Tests\Functional\AbstractFunctionalTestCase;
use In2code\Luxletter\Tests\Unit\Fixtures\DomainObjectTrait;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(LogService::class)]
class LogServiceTest extends AbstractFunctionalTestCase
{
    use DomainObjectTrait;

    protected LogService $subject;

    public function setUp(): void
    {
        parent::setUp();
        $this->subject = $this->get(LogService::class);
    }

    /**
     * @return void
     */
    public function testLogNewsletterOpeningIsLoggedOnlyOncePerNewsletterAndUser(): void
    {
        $this->subject->logNewsletterOpening(1, 5);
        $this->subject->logNewsletterOpening(1, 5);
        $this->subject->logNewsletterOpening(2, 5);

        self::assertSame(
            [
                [1, 5, Log::STATUS_NEWSLETTEROPENING, '[]'],
                [2, 5, Log::STATUS_NEWSLETTEROPENING, '[]'],
            ],
            $this->getLogRows()
        );
    }

    /**
     * A click on a link is also an opening of the newsletter
     *
     * @return void
     */
    public function testLogLinkOpeningLogsClickAndOpeningOnlyOnce(): void
    {
        $link = ['newsletter' => 1, 'user' => 5, 'target' => 'https://www.in2code.de'];
        $this->subject->logLinkOpening($link);
        $this->subject->logLinkOpening($link);

        self::assertSame(
            [
                [1, 5, Log::STATUS_NEWSLETTEROPENING, '[]'],
                [1, 5, Log::STATUS_LINKOPENING, '{"target":"https:\/\/www.in2code.de"}'],
                [1, 5, Log::STATUS_LINKOPENING, '{"target":"https:\/\/www.in2code.de"}'],
            ],
            $this->getLogRows()
        );
    }

    /**
     * @return void
     */
    public function testLogLinkOpeningIgnoresLinksWithoutUserOrNewsletter(): void
    {
        $this->subject->logLinkOpening(['newsletter' => 1, 'user' => 0, 'target' => 'https://www.in2code.de']);
        $this->subject->logLinkOpening(['user' => 5, 'target' => 'https://www.in2code.de']);

        self::assertSame([], $this->getLogRows());
    }

    /**
     * @return void
     */
    public function testLogDispatchFailureAndUnsubscribe(): void
    {
        $newsletter = $this->getNewsletter(1);
        $user = $this->getUser(5);
        $this->subject->logNewsletterDispatch($newsletter, $user);
        $this->subject->logNewsletterDispatchFailure($newsletter, $user, 'Connection refused');
        $this->subject->logUnsubscribe($newsletter, $user);

        self::assertSame(
            [
                [1, 5, Log::STATUS_DISPATCH, '[]'],
                [1, 5, Log::STATUS_DISPATCH_FAILURE, '{"exception":"Connection refused"}'],
                [1, 5, Log::STATUS_UNSUBSCRIBE, '[]'],
            ],
            $this->getLogRows()
        );
    }

    /**
     * @return array list of [newsletter, user, status, properties]
     */
    protected function getLogRows(): array
    {
        return $this->getRows(Log::TABLE_NAME, ['newsletter', 'user', 'status', 'properties']);
    }
}
