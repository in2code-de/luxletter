<?php
declare(strict_types=1);
namespace In2code\Luxletter\Widget;

use Doctrine\DBAL\DBALException;
use In2code\Lux\Utility\LocalizationUtility;
use In2code\Luxletter\Domain\Repository\LogRepository;
use In2code\Luxletter\Utility\ObjectUtility;
use TYPO3\CMS\Dashboard\Widgets\AbstractDoughnutChartWidget;
use TYPO3\CMS\Extbase\Object\Exception;

/**
 * Class ClickRateWidget
 */
class ClickRateWidget extends AbstractDoughnutChartWidget
{
    protected $title =
        'LLL:EXT:luxletter/Resources/Private/Language/locallang_db.xlf:module.dashboard.widget.clickrate.title';
    protected $description =
        'LLL:EXT:luxletter/Resources/Private/Language/locallang_db.xlf:module.dashboard.widget.clickrate.description';
    protected $iconIdentifier = 'extension-luxletter';
    protected $height = 4;
    protected $width = 2;

    /**
     * @var LogRepository
     */
    protected $logRepository = null;

    /**
     * @var array
     */
    protected $chartOptions = [
        'maintainAspectRatio' => false,
        'legend' => [
            'display' => true,
            'position' => 'right'
        ],
        'cutoutPercentage' => 40
    ];

    /**
     * @return void
     * @throws DBALException
     * @throws Exception
     */
    protected function prepareChartData(): void
    {
        $this->chartData = [
            'labels' => $this->getData()['titles'],
            'datasets' => [
                [
                    'label' => $this->getWidgetLabel('clickrate.label'),
                    'backgroundColor' => [
                        $this->chartColors[0],
                        '#dddddd'
                    ],
                    'border' => 0,
                    'data' => $this->getData()['amounts']
                ]
            ]
        ];
    }

    /**
     * @return string
     * @throws DBALException
     * @throws Exception
     */
    public function getTitle(): string
    {
        $this->initialize();
        return parent::getTitle() . ' ' . $this->getClickRate();
    }

    /**
     *  [
     *      'amounts' => [
     *          200,
     *          66
     *      ],
     *      'titles' => [
     *          'Clicker',
     *          'NonClicker'
     *      ]
     *  ]
     *
     * @return array
     * @throws Exception
     * @throws DBALException
     */
    protected function getData(): array
    {
        return [
            'amounts' => [
                $this->logRepository->getOverallClicks(),
                ($this->logRepository->getOverallOpenings() - $this->logRepository->getOverallClicks())
            ],
            'titles' => [
                $this->getWidgetLabel('clickrate.label.0'),
                $this->getWidgetLabel('clickrate.label.1')
            ]
        ];
    }

    /**
     * @return string
     * @throws DBALException
     */
    protected function getClickRate(): string
    {
        return number_format($this->logRepository->getOverallClickRate() * 100, 1, ',', '.') . '%';
    }

    /**
     * @param string $key e.g. "browser.label"
     * @return string
     */
    protected function getWidgetLabel(string $key): string
    {
        $label = LocalizationUtility::getLanguageService()->sL(
            'LLL:EXT:luxletter/Resources/Private/Language/locallang_db.xlf:module.dashboard.widget.' . $key
        );
        if (empty($label)) {
            $label = $key;
        }
        return $label;
    }

    /**
     * @return void
     * @throws Exception
     */
    protected function initialize()
    {
        $this->logRepository = ObjectUtility::getObjectManager()->get(LogRepository::class);
    }
}
