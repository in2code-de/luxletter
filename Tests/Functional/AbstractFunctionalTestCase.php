<?php

namespace In2code\Luxletter\Tests\Functional;

use In2code\Luxletter\Command\FakeRequestTrait;
use TYPO3\CMS\Core\Configuration\SiteWriter;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

abstract class AbstractFunctionalTestCase extends FunctionalTestCase
{
    use FakeRequestTrait;

    protected array $testExtensionsToLoad = [
        'in2code/luxletter',
    ];

    /**
     * Run repositories and services in the same backend context as the luxletter commands
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->fakeRequest();
    }

    /**
     * Site "main" on https://www.example.org/ with root page 1 and unsubscribe page 2
     *
     * @return void
     */
    protected function writeSiteConfiguration(): void
    {
        $this->get(SiteWriter::class)->write('main', [
            'rootPageId' => 1,
            'base' => 'https://www.example.org/',
            'luxletterUnsubscribePid' => 2,
            'languages' => [
                [
                    'languageId' => 0,
                    'title' => 'English',
                    'locale' => 'en_US.UTF-8',
                    'base' => '/',
                ],
            ],
        ]);
    }

    /**
     * @param string $table
     * @param array $columns
     * @return array list of rows with the values of the given columns, ordered by uid
     */
    protected function getRows(string $table, array $columns): array
    {
        return $this->get(ConnectionPool::class)
            ->getConnectionForTable($table)
            ->select($columns, $table, [], [], ['uid' => 'ASC'])
            ->fetchAllNumeric();
    }

    /**
     * @param string $table
     * @param string $column
     * @param string $orderBy
     * @return array values of the given column
     */
    protected function getColumn(string $table, string $column, string $orderBy = 'uid'): array
    {
        return $this->get(ConnectionPool::class)
            ->getConnectionForTable($table)
            ->select([$column], $table, [], [], [$orderBy => 'ASC'])
            ->fetchFirstColumn();
    }
}
