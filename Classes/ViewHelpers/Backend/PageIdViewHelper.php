<?php

namespace In2code\Luxletter\ViewHelpers\Backend;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\ViewHelpers\Be\AbstractBackendViewHelper;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;

/**
 * ViewHelper which returns page id in backend modules.
 *
 * .. note::
 *    This ViewHelper is experimental!
 *
 * Examples
 * ========
 *
 * Default::
 *
 *    <luxletter:backend.pageId />
 *
 * Page id
 */
class PageIdViewHelper extends AbstractBackendViewHelper
{

    /**
     * This ViewHelper renders no HTML
     *
     * @var bool
     */
    protected $escapeOutput = true;

    /**
     * Output page id
     *
     * @return string the page id
     */
    public function render(): string
    {
        return static::renderStatic(
            [],
            $this->buildRenderChildrenClosure(),
            $this->renderingContext
        );
    }

    /**
     * @param array $arguments
     * @param \Closure $renderChildrenClosure
     * @param RenderingContextInterface $renderingContext
     *
     * @return string
     */
    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ): string {
        $id = (int)GeneralUtility::_GP('id');
        if ($id > 0) {
            return '' . $id;
        }

        return '';
    }
}
