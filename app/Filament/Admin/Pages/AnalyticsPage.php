<?php

namespace App\Filament\Admin\Pages;

use Filament\Pages\Page;
use App\Filament\Admin\Widgets\AnalyticsStatsOverview;
use App\Filament\Admin\Widgets\FireFrequencyChart;
use App\Filament\Admin\Widgets\CombinedSensorChart;
use App\Filament\Admin\Widgets\FireAlertsChart;
use App\Filament\Admin\Widgets\FireConfidenceChart;
use UnitEnum;

class AnalyticsPage extends Page
{
    protected static ?string $title = 'Analytics';
    protected static UnitEnum|string|null $navigationGroup = '📊 Data & Reports';
    protected static ?int $navigationSort = 31;

    protected string $view = 'filament.admin.pages.analytics-page';

    protected function getHeaderWidgets(): array
    {
       return [
        AnalyticsStatsOverview::class,
        FireFrequencyChart::class,
        CombinedSensorChart::class,
        FireAlertsChart::class,
        FireConfidenceChart::class,
    ];
    }

    public static function getHeaderWidgetColumns(): int|array|null
{
    return 2; // two widgets per row
}

}
