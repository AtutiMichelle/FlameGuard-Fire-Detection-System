<?php

namespace App\Filament\Admin\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\SensorData;

class CombinedSensorChart extends ChartWidget
{
    protected ?string $heading = 'Combined Sensor Chart';

    protected static bool $isDiscovered = false;


  protected function getData(): array
{
    // Get last 10 composite sensor readings
    $records = SensorData::where('sensor_type', 'composite')
        ->latest()
        ->take(10)
        ->get();

    return [
        'labels' => $records->pluck('created_at')->map(fn($d) => $d->format('H:i'))->toArray(),
        'datasets' => [
            [
                'label' => 'Temperature (°C)',
                'data' => $records->pluck('raw_data')->map(fn($r) => $r['temp'] ?? 0)->toArray(),
                'borderColor' => '#FF5733',
                'backgroundColor' => 'rgba(255,87,51,0.2)',
            ],
            [
                'label' => 'Gas (MQ2)',
                'data' => $records->pluck('raw_data')->map(fn($r) => $r['mq2'] ?? 0)->toArray(),
                'borderColor' => '#33C3FF',
                'backgroundColor' => 'rgba(51,195,255,0.2)',
            ],
            [
                'label' => 'Humidity (%)',
                'data' => $records->pluck('raw_data')->map(fn($r) => $r['humidity'] ?? 0)->toArray(),
                'borderColor' => '#33FF57',
                'backgroundColor' => 'rgba(51,255,87,0.2)',
            ],
        ],
    ];
}

    protected function getType(): string
    {
        return 'line';
    }
}
