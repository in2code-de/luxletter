<?php

declare(strict_types=1);
namespace In2code\Luxletter\ViewHelpers\Format;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Class PercentViewHelper
 * @noinspection PhpUnused
 */
class PercentViewHelper extends AbstractViewHelper
{
    /**
     * @return void
     */
    public function initializeArguments(): void
    {
        parent::initializeArguments();
        $this->registerArgument('number', 'float', 'Any number', true);
        $this->registerArgument('decimals', 'int', 'The number of digits after the decimal point', false, 1);
        $this->registerArgument('decPoint', 'string', 'Decimal point', false, ',');
        $this->registerArgument('postfix', 'string', 'Any postfix like a percent sign', false, ' %');
    }

    /**
     * @return string
     */
    public function render(): string
    {
        $value = $this->arguments['number'] * 100;
        $value = number_format($value, $this->arguments['decimals'], $this->arguments['decPoint'], '.');
        return $value . $this->arguments['postfix'];
    }
}
