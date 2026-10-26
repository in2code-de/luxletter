<?php

namespace In2code\Luxletter\Tests\Functional\Domain\Factory;

use In2code\Luxletter\Domain\Factory\ViewFactory;
use In2code\Luxletter\Tests\Functional\AbstractFunctionalTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use TYPO3\CMS\Core\Utility\GeneralUtility;

#[CoversClass(ViewFactory::class)]
class ViewFactoryTest extends AbstractFunctionalTestCase
{
    /**
     * NewsletterController and NewsletterUrl get the factory via makeInstance(), so it must be a public service
     *
     * @return void
     */
    public function testMakeInstanceReturnsViewFactoryWithDependencies(): void
    {
        self::assertInstanceOf(ViewFactory::class, GeneralUtility::makeInstance(ViewFactory::class));
    }

    /**
     * @return void
     */
    public function testCreateFromTemplateSourceRendersGivenSource(): void
    {
        $view = $this->get(ViewFactory::class)->createFromTemplateSource('Hello {name}');
        $view->assign('name', 'World');

        self::assertSame('Hello World', $view->render());
    }

    /**
     * @return void
     */
    public function testCreateFromTemplatePathAndFilenameRendersGivenFile(): void
    {
        $view = $this->get(ViewFactory::class)->createFromTemplatePathAndFilename(
            __DIR__ . '/Fixtures/Template.html'
        );
        $view->assign('name', 'World');

        self::assertSame('Hello World from file', trim($view->render()));
    }
}
