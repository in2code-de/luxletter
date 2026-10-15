<?php

namespace In2code\Luxletter\Tests\Functional\Mail;

use In2code\Luxletter\Domain\Model\Link;
use In2code\Luxletter\Domain\Model\Log;
use In2code\Luxletter\Domain\Model\Queue;
use In2code\Luxletter\Mail\ProgressQueue;
use In2code\Luxletter\Tests\Functional\AbstractFunctionalTestCase;
use In2code\Luxletter\Tests\Functional\Fixtures\Mail\MemoryTransport;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Mime\Email;

/**
 * Sends the queued newsletters of the fixture through the whole stack (parsing, link hashing, unsubscribe header,
 * mail, queue and log) with a mail transport that keeps the mails in memory
 */
#[CoversClass(ProgressQueue::class)]
class ProgressQueueTest extends AbstractFunctionalTestCase
{
    /**
     * Relies on the defaults of ext_conf_template.txt, which rewrite links and add the unsubscribe header
     */
    protected array $configurationToUseInTestInstance = [
        'MAIL_LUXLETTER' => [
            'transport' => MemoryTransport::class,
        ],
    ];

    public function setUp(): void
    {
        parent::setUp();
        $this->importCSVDataSet(__DIR__ . '/../Fixtures/Receivers.csv');
        $this->importCSVDataSet(__DIR__ . '/Fixtures/ProgressQueue.csv');
        $this->writeSiteConfiguration();
        MemoryTransport::$messages = [];
    }

    /**
     * @return void
     */
    public function testProgressSendsPersonalizedMailsWithTrackingLinksAndUnsubscribeHeader(): void
    {
        $progressed = $this->progress(1);

        self::assertSame(2, $progressed);
        self::assertCount(2, MemoryTransport::$messages);
        $mail = $this->getMailTo('alice@example.org');
        self::assertSame('Hello Alice', $mail->getSubject());
        self::assertSame('newsletter@example.org', $mail->getFrom()[0]->getAddress());
        self::assertSame('reply@example.org', $mail->getReplyTo()[0]->getAddress());
        self::assertStringContainsString('Dear Alice', $mail->getHtmlBody());
        self::assertStringContainsString('https://www.example.org/?luxletterlink=', $mail->getHtmlBody());
        self::assertStringNotContainsString('https://www.in2code.de/target', $mail->getHtmlBody());
        self::assertStringContainsString(
            'https://www.example.org/unsubscribe',
            $mail->getHeaders()->get('List-Unsubscribe')->getBodyAsString()
        );
    }

    /**
     * @return void
     */
    public function testProgressMarksQueueAsSentAndLogsDispatchAndLinks(): void
    {
        $this->progress(1);

        self::assertSame([1, 1, 0], $this->getColumn(Queue::TABLE_NAME, 'sent'));
        self::assertSame([Log::STATUS_DISPATCH, Log::STATUS_DISPATCH], $this->getColumn(Log::TABLE_NAME, 'status'));
        self::assertSame(
            ['https://www.in2code.de/target', 'https://www.in2code.de/target'],
            $this->getColumn(Link::TABLE_NAME, 'target')
        );
    }

    /**
     * A newsletter whose configuration points to a not existing site can not be sent
     *
     * @return void
     */
    public function testProgressIncreasesFailuresAndLogsFailureIfMailCanNotBeSent(): void
    {
        $this->progress(2);

        self::assertSame([], MemoryTransport::$messages);
        self::assertSame([0, 0, 1], $this->getColumn(Queue::TABLE_NAME, 'failures'));
        self::assertSame([0, 0, 0], $this->getColumn(Queue::TABLE_NAME, 'sent'));
        self::assertSame([Log::STATUS_DISPATCH_FAILURE], $this->getColumn(Log::TABLE_NAME, 'status'));
    }

    /**
     * @return void
     */
    public function testProgressDoesNotSendAlreadySentMailsAgain(): void
    {
        $this->progress(1);
        $progressed = $this->progress(1);

        self::assertSame(0, $progressed);
        self::assertCount(2, MemoryTransport::$messages);
    }

    protected function getMailTo(string $email): Email
    {
        foreach (MemoryTransport::$messages as $message) {
            if ($message->getTo()[0]->getAddress() === $email) {
                return $message;
            }
        }
        self::fail('No mail sent to ' . $email);
    }

    protected function progress(int $newsletterIdentifier): int
    {
        return (new ProgressQueue(new NullOutput()))->progress(10, $newsletterIdentifier);
    }
}
