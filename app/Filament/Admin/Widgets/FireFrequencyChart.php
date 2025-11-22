<?php

namespace App\Filament\Admin\Widgets;

use App\Models\SensorData;
use Filament\Widgets\ChartWidget;

class FireFrequencyChart extends ChartWidget
{
    protected ?string $heading = 'Fire Frequency Chart';

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
                'label' => 'Fire Frequency',
                'data' => $alerts->pluck('count')->toArray(),
                'borderColor' => '#FF0000',
                'backgroundColor' => 'rgba(255,0,0,0.2)',
            ],
        ],
    ];
}


    protected function getType(): string
    {
        return 'line';
    }
}
