<?php

declare(strict_types=1);
namespace In2code\Luxletter\Domain\Factory;

use TYPO3\CMS\Core\View\ViewFactoryData;
use TYPO3\CMS\Core\View\ViewFactoryInterface;
use TYPO3\CMS\Core\View\ViewInterface;
use TYPO3\CMS\Fluid\View\FluidViewAdapter;

/**
 * Class ViewFactory
 * to create standalone fluid views from a template file or from a template source string
 */
class ViewFactory
{
    public function __construct(private readonly ViewFactoryInterface $viewFactory)
    {
    }

    /**
     * @param string $templateSource
     * @param array $templatePaths with keys "templateRootPaths", "partialRootPaths" and "layoutRootPaths"
     * @return ViewInterface
     */
    public function createFromTemplateSource(string $templateSource, array $templatePaths = []): ViewInterface
    {
        $view = $this->create($templatePaths);
        // ViewFactoryData does not support template sources, so the rendering context has to be used
        if ($view instanceof FluidViewAdapter) {
            $view->getRenderingContext()->getTemplatePaths()->setTemplateSource($templateSource);
        }
        return $view;
    }

    /**
     * @param string $templatePathAndFilename absolute path to the template file
     * @param array $templatePaths with keys "templateRootPaths", "partialRootPaths" and "layoutRootPaths"
     * @return ViewInterface
     */
    public function createFromTemplatePathAndFilename(
        string $templatePathAndFilename,
        array $templatePaths = []
    ): ViewInterface {
        return $this->create($templatePaths, $templatePathAndFilename);
    }

    protected function create(array $templatePaths, ?string $templatePathAndFilename = null): ViewInterface
    {
        return $this->viewFactory->create(new ViewFactoryData(
            templateRootPaths: $templatePaths['templateRootPaths'] ?? null,
            partialRootPaths: $templatePaths['partialRootPaths'] ?? null,
            layoutRootPaths: $templatePaths['layoutRootPaths'] ?? null,
            templatePathAndFilename: $templatePathAndFilename,
        ));
    }
}
