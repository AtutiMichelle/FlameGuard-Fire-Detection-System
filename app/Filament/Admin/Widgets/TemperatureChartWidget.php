<?php

namespace App\Filament\Admin\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\SensorData;

class TemperatureChartWidget extends ChartWidget
{
    protected ?string $heading = '🌡️ Temperature Trend';
    protected static ?int $sort = 2;
    protected ?string $pollingInterval = '5s'; // auto refresh every 5 seconds

    protected $listeners = ['echo:sensor-channel,SensorDataUpdated' => '$refresh'];


    protected function getData(): array
{
    $records = SensorData::latest()->take(20)->get()->reverse();

    $labels = array_values($records->map(fn($data) => $data->created_at->format('H:i:s'))->toArray());
    $temperatures = array_values($records->pluck('raw_data.temp')->map(fn($t) => (float)$t)->toArray());

    return [
        'datasets' => [
            [
                'label' => 'Temperature (°C)',
                'data' => $temperatures,
                'borderColor' => '#ef4444',
                'backgroundColor' => 'rgba(239,68,68,0.2)',
                'fill' => true,
                'tension' => 0.3,
            ],
        ],
        'labels' => $labels,
    ];
}


    protected function getType(): string
    {
        return 'line';
    }
}
