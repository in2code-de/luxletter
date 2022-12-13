<?php

declare(strict_types=1);

namespace In2code\Luxletter\Backend\Buttons;

use TYPO3\CMS\Backend\Template\Components\Buttons\AbstractButton;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Request;
use TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder;

class NavigationGroupButton extends AbstractButton
{
    protected array $configuration = [
        'actionname' => 'any label',
    ];

    protected string $currentAction;

    protected Request $request;
    protected UriBuilder $uriBuilder;
    protected IconFactory $iconFactory;

    public function __construct(Request $request, string $currentAction, array $configuration)
    {
        $this->request = $request;
        $this->currentAction = $currentAction;
        $this->configuration = $configuration;
        $this->uriBuilder = GeneralUtility::makeInstance(UriBuilder::class);
        $this->uriBuilder->setRequest($this->request);
        $this->iconFactory = GeneralUtility::makeInstance(IconFactory::class);
    }

    public function render()
    {
        $content = $this->prepend();
        $content .= '<div class="btn-group" role="group">';
        foreach ($this->configuration as $action => $label) {
            $url = $this->uriBuilder->uriFor($action);
            $class = 'btn-default';
            if ($this->currentAction === $action) {
                $class = 'btn-primary';
            }
            $content .= '<a href="' . $url . '" class="btn ' . $class . '">' . $label . '</a>';
        }
        $content .= '</div>';
        $content = $this->append($content);
        return $content;
    }

    protected function prepend(): string
    {
        $icon = $this->iconFactory->getIcon('extension-lux');
        return $icon->render();
    }

    protected function append(string $content): string
    {
        if (ExtensionManagementUtility::isLoaded('luxenterprise')) {
            return $content;
        }

        $icon = $this->iconFactory->getIcon('extension-luxletter-star', Icon::SIZE_SMALL);
        $content .= '<div style="padding-top: 5px;">';
        $content .= '<a href="https://www.in2code.de/produkte/lux-typo3-marketing-automation/?utm_campaign=LUX+%2B+LUXletter+Community+Version&utm_id=llcv&utm_source=typo3&utm_medium=browser&utm_content=LUXletter" class="lux_poweredby" style="color:black; font-weight: bold;" target="_blank" rel="noopener">';
        $content .= $icon->render();
        $content .= 'Go enterprise</a></div>';
        return $content;
    }

    public function __toString()
    {
        return $this->render();
    }

    public function isValid()
    {
        return true;
    }
}
