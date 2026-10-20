<?php

namespace Wexample\SymfonyAnalyticsDs\Traits;

use Wexample\SymfonyAnalyticsDs\WexampleSymfonyAnalyticsDsBundle;
use Wexample\SymfonyHelpers\Traits\BundleClassTrait;

trait SymfonyAnalyticsDsBundleClassTrait
{
    use BundleClassTrait;

    public static function getBundleClassName(): string
    {
        return WexampleSymfonyAnalyticsDsBundle::class;
    }
}
