<?php

namespace Wexample\SymfonyAnalyticsDs\Controller\Pages;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Wexample\SymfonyAnalytics\Enum\AnalyticsDimension;
use Wexample\SymfonyAnalytics\Service\AnalyticsAccessService;
use Wexample\SymfonyAnalytics\Service\ChartPoint\AnalyticsCountriesChartPointProvider;
use Wexample\SymfonyAnalytics\Service\ChartPoint\AnalyticsTrafficChartPointProvider;
use Wexample\SymfonyAnalyticsDs\Traits\SymfonyAnalyticsDsBundleClassTrait;
use Wexample\SymfonyLoader\Controller\AbstractPagesController;

/**
 * The audience of the site over a period: its figures, its course, where it
 * comes from, where it is. Every part reads the api for the period the
 * period-nav of the page says, and reads it again when it changes. The
 * holders of symfony-analytics' `access_role`.
 */
#[Route(path: '/analytics/', name: 'analytics_')]
final class AnalyticsController extends AbstractPagesController
{
    use SymfonyAnalyticsDsBundleClassTrait;

    final public const string ROUTE_INDEX = 'analytics_index';

    #[Route(path: '', name: 'index')]
    public function index(AnalyticsAccessService $access): Response
    {
        $access->denyUnlessGranted();

        return $this->renderPage('index', [
            'traffic_chart' => AnalyticsTrafficChartPointProvider::getChartName(),
            'countries_chart' => AnalyticsCountriesChartPointProvider::getChartName(),
            'dimensions' => array_map(static fn (AnalyticsDimension $dimension): string => $dimension->value, AnalyticsDimension::cases()),
        ]);
    }
}
