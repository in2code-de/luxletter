<?php

declare(strict_types=1);

use In2code\Luxletter\Widget\DataProvider\ClickRateDataProvider;
use In2code\Luxletter\Widget\DataProvider\LastNewslettersClickRateDataProvider;
use In2code\Luxletter\Widget\DataProvider\LastNewslettersOpenRateDataProvider;
use In2code\Luxletter\Widget\DataProvider\NewsletterDataProvider;
use In2code\Luxletter\Widget\DataProvider\OpenRateDataProvider;
use In2code\Luxletter\Widget\DataProvider\ReceiverDataProvider;
use In2code\Luxletter\Widget\DataProvider\UnsubscribeRateDataProvider;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Reference;
use TYPO3\CMS\Dashboard\Dashboard;
use TYPO3\CMS\Dashboard\Widgets\BarChartWidget;
use TYPO3\CMS\Dashboard\Widgets\DoughnutChartWidget;
use TYPO3\CMS\Dashboard\Widgets\NumberWithIconWidget;

return function (ContainerConfigurator $configurator, ContainerBuilder $containerBuilder) {
    $services = $configurator->services();

    if ($containerBuilder->hasDefinition(Dashboard::class)) {
//        $services->set('widgets.dashboard.widget.exampleWidget')
//            ->class(DoughnutChartWidget::class)
//            ->arg('$dataProvider', new Reference(OpenRateDataProvider::class))
//            ->arg('$buttonProvider', new Reference(\TYPO3\CMS\Dashboard\Widgets\Provider\SysLogButtonProvider::class))
//            ->arg('$view', new Reference('dashboard.views.widget'))
//            ->arg('$options', ['template' => 'Widget/ExampleWidget'])
//            ->tag('dashboard.widget', [
//                'identifier' => 'widgets-exampleWidget',
//                'groupNames' => 'systemInfo',
//                'title' => 'LLL:EXT:ext_key/Resources/Private/Language/locallang.xlf:widgets.dashboard.widget.exampleWidget.title',
//                'description' => 'LLL:EXT:ext_key/Resources/Private/Language/locallang.xlf:widgets.dashboard.widget.exampleWidget.description',
//                'iconIdentifier' => 'content-widget-list',
//                'height' => 'medium',
//                'width' => 'medium'
//            ]);
//
//        $services->set('dashboard.widgets.ClickRateWidget')
//            ->class(DoughnutChartWidget::class)
//            ->arg('$view', new Reference('dashboard.views.widget'))
//            ->arg('$dataProvider', new Reference(ClickRateDataProvider::class))
//            ->tag('dashboard.widget', [
//                'identifier' => 'luxletterClickRate',
//                'groupNames' => 'luxletter',
//                'title' => 'LLL:EXT:luxletter/Resources/Private/Language/locallang_db.xlf:module.dashboard.widget.clickrate.title',
//                'description' => 'LLL:EXT:luxletter/Resources/Private/Language/locallang_db.xlf:module.dashboard.widget.clickrate.description',
//                'iconIdentifier' => 'extension-luxletter',
//                'height' => 'medium',
//                'width' => 'small',
//            ]);
//
//        $services->set('dashboard.widgets.UnsubscribeRateWidget')
//            ->class(DoughnutChartWidget::class)
//            ->arg('$view', new Reference('dashboard.views.widget'))
//            ->arg('$dataProvider', new Reference(UnsubscribeRateDataProvider::class))
//            ->tag('dashboard.widget', [
//                'identifier' => 'luxletterUnsubscribeRate',
//                'groupNames' => 'luxletter',
//                'title' => 'LLL:EXT:luxletter/Resources/Private/Language/locallang_db.xlf:module.dashboard.widget.unsubscriberate.title',
//                'description' => 'LLL:EXT:luxletter/Resources/Private/Language/locallang_db.xlf:module.dashboard.widget.unsubscriberate.description',
//                'iconIdentifier' => 'extension-luxletter',
//                'height' => 'medium',
//                'width' => 'small',
//            ]);
//
//        $services->set('dashboard.widgets.ReceiverWidget')
//            ->class(NumberWithIconWidget::class)
//            ->arg('$view', new Reference('dashboard.views.widget'))
//            ->arg('$dataProvider', new Reference(ReceiverDataProvider::class))
//            ->arg('$options', [
//                'title' => 'LLL:EXT:luxletter/Resources/Private/Language/locallang_db.xlf:module.dashboard.widget.receiver.title',
//                'subtitle' => 'LLL:EXT:luxletter/Resources/Private/Language/locallang_db.xlf:module.dashboard.widget.receiver.description',
//                'icon' => 'luxletter-widget-receiver',
//            ])
//            ->tag('dashboard.widget', [
//                'identifier' => 'luxletterReceiver',
//                'groupNames' => 'luxletter',
//                'title' => 'LLL:EXT:luxletter/Resources/Private/Language/locallang_db.xlf:module.dashboard.widget.receiver.title',
//                'description' => 'LLL:EXT:luxletter/Resources/Private/Language/locallang_db.xlf:module.dashboard.widget.receiver.description',
//                'iconIdentifier' => 'luxletter-widget-receiver',
//            ]);
//
//        $services->set('dashboard.widgets.NewsletterWidget')
//            ->class(NumberWithIconWidget::class)
//            ->arg('$view', new Reference('dashboard.views.widget'))
//            ->arg('$dataProvider', new Reference(NewsletterDataProvider::class))
//            ->arg('$options', [
//                'title' => 'LLL:EXT:luxletter/Resources/Private/Language/locallang_db.xlf:module.dashboard.widget.newsletter.title',
//                'subtitle' => 'LLL:EXT:luxletter/Resources/Private/Language/locallang_db.xlf:module.dashboard.widget.newsletter.description',
//                'icon' => 'luxletter-widget-receiver',
//            ])
//            ->tag('dashboard.widget', [
//                'identifier' => 'luxletterReceiver',
//                'groupNames' => 'luxletter',
//                'title' => 'LLL:EXT:luxletter/Resources/Private/Language/locallang_db.xlf:module.dashboard.widget.newsletter.title',
//                'description' => 'LLL:EXT:luxletter/Resources/Private/Language/locallang_db.xlf:module.dashboard.widget.newsletter.description',
//                'iconIdentifier' => 'luxletter-widget-receiver',
//            ]);
//
//        $services->set('dashboard.widgets.LastNewslettersOpenRateWidget')
//            ->class(BarChartWidget::class)
//            ->arg('$view', new Reference('dashboard.views.widget'))
//            ->arg('$dataProvider', new Reference(LastNewslettersOpenRateDataProvider::class))
//            ->tag('dashboard.widget', [
//                'identifier' => 'luxletterLastNewslettersOpenRate',
//                'groupNames' => 'luxletter',
//                'title' => 'LLL:EXT:luxletter/Resources/Private/Language/locallang_db.xlf:module.dashboard.widget.lastnewslettersopenrate.title',
//                'description' => 'LLL:EXT:luxletter/Resources/Private/Language/locallang_db.xlf:module.dashboard.widget.lastnewslettersopenrate.description',
//                'iconIdentifier' => 'extension-luxletter',
//                'height' => 'medium',
//                'width' => 'medium',
//            ]);
//
//        $services->set('dashboard.widgets.LastNewslettersClickRateWidget')
//            ->class(BarChartWidget::class)
//            ->arg('$view', new Reference('dashboard.views.widget'))
//            ->arg('$dataProvider', new Reference(LastNewslettersClickRateDataProvider::class))
//            ->tag('dashboard.widget', [
//                'identifier' => 'luxletterLastNewslettersClickRate',
//                'groupNames' => 'luxletter',
//                'title' => 'LLL:EXT:luxletter/Resources/Private/Language/locallang_db.xlf:module.dashboard.widget.lastnewslettersclickrate.title',
//                'description' => 'LLL:EXT:luxletter/Resources/Private/Language/locallang_db.xlf:module.dashboard.widget.lastnewslettersclickrate.description',
//                'iconIdentifier' => 'extension-luxletter',
//                'height' => 'medium',
//                'width' => 'medium',
//            ]);
    }
};
