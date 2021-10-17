<?php
declare(strict_types = 1);
namespace In2code\Luxletter\Widget\DataProvider;

use Doctrine\DBAL\DBALException;
use In2code\Luxletter\Domain\Model\Newsletter;
use In2code\Luxletter\Domain\Repository\NewsletterRepository;
use In2code\Luxletter\Utility\LocalizationUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Dashboard\WidgetApi;
use TYPO3\CMS\Dashboard\Widgets\ChartDataProviderInterface;
use TYPO3\CMS\Extbase\Object\Exception;

/**
 * Class LastNewslettersClickRateDataProvider
 * @noinspection PhpUnused
 */
class LastNewslettersClickRateDataProvider implements ChartDataProviderInterface
{
    /**
     * @return array
     * @throws Exception
     * @throws DBALException
     */
    public function getChartData(): array
    {
        return [
            'labels' => $this->getData()['titles'],
            'datasets' => [
                [
                    'label' => $this->getWidgetLabel('lastnewslettersclickrate.label'),
                    'backgroundColor' => [
                        WidgetApi::getDefaultChartColors()[0],
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
        $newsletterRepository = GeneralUtility::makeInstance(NewsletterRepository::class);
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
        $label = LocalizationUtility::translate(
            'LLL:EXT:luxletter/Resources/Private/Language/locallang_db.xlf:module.dashboard.widget.' . $key
        );
        if (empty($label)) {
            $label = $key;
        }
        return $label;
    }
}
