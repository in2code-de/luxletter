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

use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Type\Bitmask\Permission;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\ViewHelpers\Be\AbstractBackendViewHelper;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;

/**
 * ViewHelper which returns page title in backend modules.
 *
 * .. note::
 *    This ViewHelper is experimental!
 *
 * Examples
 * ========
 *
 * Default::
 *
 *    <luxletter:backend.pageTitle />
 *
 * Page title
 */
class PageTitleViewHelper extends AbstractBackendViewHelper
{

    /**
     * This ViewHelper renders no HTML
     *
     * @var bool
     */
    protected $escapeOutput = true;

    /**
     * Output page title
     *
     * @return string the page title
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
            $pageRecord = BackendUtility::readPageAccess(
                $id,
                $GLOBALS['BE_USER']->getPagePermsClause(Permission::PAGE_SHOW)
            );
            if ($pageRecord['title']) {
                return $pageRecord['title'];
            }
        }

        return '';
    }
}
