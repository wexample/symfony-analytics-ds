<?php

namespace Wexample\SymfonyAnalyticsDs\Controller\Pages;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Wexample\SymfonyAnalytics\Enum\AnalyticsAudience;
use Wexample\SymfonyAnalytics\Enum\AnalyticsDimension;
use Wexample\SymfonyAnalytics\Service\AnalyticsAccessService;
use Wexample\SymfonyAnalytics\Service\AnalyticsStatsService;
use Wexample\SymfonyAnalytics\Service\AnalyticsStorageService;
use Wexample\SymfonyAnalytics\Service\ChartPoint\AnalyticsCompositionChartPointProvider;
use Wexample\SymfonyAnalytics\Service\ChartPoint\AnalyticsCountriesChartPointProvider;
use Wexample\SymfonyAnalytics\Service\ChartPoint\AnalyticsTrafficChartPointProvider;
use Wexample\SymfonyAnalyticsDs\Traits\SymfonyAnalyticsDsBundleClassTrait;
use Wexample\SymfonyLoader\Controller\AbstractPagesController;

/**
 * The audience of the site over a period: its figures, its course, where it
 * comes from, where it is, of the people, the robots or both, and what it
 * weighs in the database. Every part reads the api for the period the
 * period-nav of the page says, and reads it again when it changes. The
 * holders of symfony-analytics' `access_role`.
 */
#[Route(path: '/analytics/', name: 'analytics_')]
final class AnalyticsController extends AbstractPagesController
{
    use SymfonyAnalyticsDsBundleClassTrait;

    final public const string ROUTE_INDEX = 'analytics_index';

    #[Route(path: '', name: 'index')]
    public function index(
        AnalyticsAccessService $access,
        AnalyticsStatsService $stats,
        AnalyticsStorageService $storage,
    ): Response {
        $access->denyUnlessGranted();

        $sizes = $storage->getSizes();

        return $this->renderPage('index', [
            'composition_chart' => AnalyticsCompositionChartPointProvider::getChartName(),
            'traffic_chart' => AnalyticsTrafficChartPointProvider::getChartName(),
            'countries_chart' => AnalyticsCountriesChartPointProvider::getChartName(),
            'dimensions' => array_map(static fn (AnalyticsDimension $dimension): string => $dimension->value, AnalyticsDimension::cases()),
            'storage_size' => null === $sizes ? null : array_sum($sizes),
            'retention' => [
                'human' => $stats->getRetentionMonths(AnalyticsAudience::HUMAN),
                'suspect' => $stats->getRetentionMonths(AnalyticsAudience::SUSPECT),
                'declared' => $stats->getRetentionMonths(AnalyticsAudience::DECLARED),
            ],
        ]);
    }
}
