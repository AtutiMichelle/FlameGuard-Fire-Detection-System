<?php

namespace App\Filament\Admin\Widgets;

use App\Models\SensorData;
use Filament\Widgets\LineChartWidget;

class FireAlertsChart extends LineChartWidget
{
    protected ?string $heading = 'Fire Alerts Over Time';

    protected static bool $isDiscovered = false;


    protected function getData(): array
    {
        $alerts = SensorData::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->whereJsonContains('ml_results->fire_detected', true)
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return [
            'labels' => $alerts->pluck('date')->toArray(),
            'datasets' => [
                [
                    'label' => 'Fire Alerts',
                    'data' => $alerts->pluck('count')->toArray(),
                    'borderColor' => '#ff3d00',
                    'backgroundColor' => 'rgba(255,61,0,0.2)',
                ],
            ],
        ];
    }
}
