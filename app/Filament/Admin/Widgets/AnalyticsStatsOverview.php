<?php

namespace App\Filament\Admin\Widgets;

use App\Models\SensorData;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AnalyticsStatsOverview extends BaseWidget
{
    protected static bool $isDiscovered = false;

    protected function getStats(): array
    {
        $last = SensorData::latest()->first();

        return [
            
            Stat::make('Total Fire Alerts', SensorData::where('ml_results->fire_detected', true)->count())
                ->description('Detected by ML Model')
                ->color('danger'),
        ];
    }
}
