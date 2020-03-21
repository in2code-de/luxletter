<?php
declare(strict_types=1);
namespace In2code\Luxletter\Widget;

use Doctrine\DBAL\DBALException;
use In2code\Lux\Utility\LocalizationUtility;
use In2code\Luxletter\Domain\Model\Newsletter;
use In2code\Luxletter\Domain\Repository\LogRepository;
use In2code\Luxletter\Domain\Repository\NewsletterRepository;
use In2code\Luxletter\Utility\ObjectUtility;
use TYPO3\CMS\Dashboard\Widgets\AbstractBarChartWidget;
use TYPO3\CMS\Extbase\Object\Exception;

/**
 * Class LastNewslettersClickRateWidget
 */
class LastNewslettersClickRateWidget extends AbstractBarChartWidget
{
    protected $title = 'LLL:EXT:luxletter/Resources/Private/Language/locallang_db.xlf:'
        . 'module.dashboard.widget.lastnewslettersclickrate.title';
    protected $description = 'LLL:EXT:luxletter/Resources/Private/Language/locallang_db.xlf:'
        . 'module.dashboard.widget.lastnewslettersclickrate.description';
    protected $iconIdentifier = 'extension-luxletter';
    protected $height = 4;
    protected $width = 4;

    /**
     * @var LogRepository
     */
    protected $logRepository = null;

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
                    'label' => $this->getWidgetLabel('lastnewslettersclickrate.label'),
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
     *  [
     *      'amounts' => [
     *          200,
     *          66
     *      ],
     *      'titles' => [
     *          'Openers',
     *          'NonOpeners'
     *      ]
     *  ]
     *
     * @return array
     * @throws Exception
     * @throws DBALException
     */
    protected function getData(): array
    {
        $newsletterRepository = ObjectUtility::getObjectManager()->get(NewsletterRepository::class);
        $newsletters = $newsletterRepository->findAll()->getQuery()->setLimit(10)->execute();
        $data = [
            'amounts' => [],
            'titles' => []
        ];
        /** @var Newsletter $newsletter */
        foreach ($newsletters as $newsletter) {
            $data['amounts'][] = $newsletter->getClickers();
            $data['titles'][] = $newsletter->getTitle();
        }
        return $data;
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
}
