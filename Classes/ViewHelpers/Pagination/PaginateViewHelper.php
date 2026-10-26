<?php

declare(strict_types=1);
namespace In2code\Luxletter\ViewHelpers\Pagination;

use In2code\Luxletter\Exception\NotPaginatableException;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Pagination\ArrayPaginator;
use TYPO3\CMS\Core\Pagination\PaginationInterface;
use TYPO3\CMS\Core\Pagination\PaginatorInterface;
use TYPO3\CMS\Core\Pagination\SimplePagination;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\RequestInterface;
use TYPO3\CMS\Extbase\Pagination\QueryResultPaginator;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;
use TYPO3\CMS\Extbase\Service\ExtensionService;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * PaginateViewHelper
 */
class PaginateViewHelper extends AbstractViewHelper
{
    /**
     * @var bool
     */
    protected $escapeOutput = false;

    /**
     * @return void
     */
    public function initializeArguments(): void
    {
        parent::initializeArguments();
        $this->registerArgument('objects', 'mixed', 'array or queryresult', true);
        $this->registerArgument('as', 'string', 'new variable name', true);
        $this->registerArgument('itemsPerPage', 'int', 'items per page', false, 10);
        $this->registerArgument('name', 'string', 'unique identification - will take "as" as fallback', false, '');
    }

    /**
     * @return string
     * @throws NotPaginatableException
     */
    public function render(): string
    {
        if ($this->arguments['objects'] === null) {
            return (string)$this->renderChildren();
        }
        $templateVariableContainer = $this->renderingContext->getVariableProvider();
        $templateVariableContainer->add($this->arguments['as'], [
            'pagination' => $this->getPagination(),
            'paginator' => $this->getPaginator(),
            'name' => $this->getName(),
        ]);
        $output = (string)$this->renderChildren();
        $templateVariableContainer->remove($this->arguments['as']);
        return $output;
    }

    /**
     * @return PaginationInterface
     * @throws NotPaginatableException
     */
    protected function getPagination(): PaginationInterface
    {
        return GeneralUtility::makeInstance(SimplePagination::class, $this->getPaginator());
    }

    /**
     * @return PaginatorInterface
     * @throws NotPaginatableException
     */
    protected function getPaginator(): PaginatorInterface
    {
        if (is_array($this->arguments['objects'])) {
            $paginatorClass = ArrayPaginator::class;
        } elseif (is_a($this->arguments['objects'], QueryResultInterface::class)) {
            $paginatorClass = QueryResultPaginator::class;
        } else {
            throw new NotPaginatableException('Given object is not supported for pagination', 1634132847);
        }
        return GeneralUtility::makeInstance(
            $paginatorClass,
            $this->arguments['objects'],
            $this->getPageNumber(),
            $this->arguments['itemsPerPage']
        );
    }

    /**
     * @return int
     */
    protected function getPageNumber(): int
    {
        /** @var RequestInterface $request */
        $request = $this->renderingContext->getAttribute(ServerRequestInterface::class);
        $extensionService = GeneralUtility::makeInstance(ExtensionService::class);
        $pluginNamespace = $extensionService->getPluginNamespace(
            $request->getControllerExtensionName(),
            $request->getPluginName()
        );
        $variables = $request->getParsedBody()[$pluginNamespace] ?? $request->getQueryParams()[$pluginNamespace] ?? [];
        if (!empty($variables[$this->getName()]['currentPage'])) {
            return (int)$variables[$this->getName()]['currentPage'];
        }
        return 1;
    }

    /**
     * @return string
     */
    protected function getName(): string
    {
        return $this->arguments['name'] ?: $this->arguments['as'];
    }
}
