<?php
declare(strict_types = 1);
namespace In2code\Luxletter\ViewHelpers\Statistic;

use In2code\Luxletter\Domain\Model\Newsletter;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;
use TYPO3\CMS\Extbase\Reflection\Exception\PropertyNotAccessibleException;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Class GetOpenersViewHelper
 * to get a commaseparated list of openers from newsletters
 * @noinspection PhpUnused
 */
class GetListOfPropertiesFromNewslettersViewHelper extends AbstractViewHelper
{

    /**
     * @return void
     */
    public function initializeArguments(): void
    {
        parent::initializeArguments();
        $this->registerArgument('newsletters', QueryResultInterface::class, 'Newsletter', true);
        $this->registerArgument('property', 'string', 'Any concatinated property from a newsletter', true);
        $this->registerArgument('limit', 'int', 'Show X values', false, 6);
    }

    /**
     * @return string
     * @throws PropertyNotAccessibleException
     */
    public function render(): string
    {
        $values = [];
        foreach ($this->arguments['newsletters'] as $newsletter) {
            /** @var Newsletter $newsletter */
            $values[] = ObjectAccess::getProperty($newsletter, $this->arguments['property']);
        }
        return implode(',', $values);
    }
}
