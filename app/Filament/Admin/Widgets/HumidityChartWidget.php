<?php

namespace App\Filament\Admin\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\SensorData;

class HumidityChartWidget extends ChartWidget
{
    protected ?string $heading = '💧 Humidity Levels';
    protected static ?int $sort = 4;
    protected  ?string $pollingInterval = '10s';

    protected $listeners = ['echo:sensor-channel,SensorDataUpdated' => '$refresh'];


    protected function getData(): array
    {
        $records = SensorData::latest()->take(20)->get()->reverse();

        // $labels = $records->map(fn($data) => $data->created_at->format('H:i:s'))->toArray();
        $labels = array_values($records->map(fn($data) => $data->created_at->format('H:i:s'))->toArray());

        $humidity = array_values($records->pluck('raw_data.humidity')->map(fn($h) => (float)$h)->toArray());

        return [
            'datasets' => [
                [
                    'label' => 'Humidity (%)',
                    'data' => $humidity,
                    'borderColor' => '#3b82f6',
                    'backgroundColor' => 'rgba(59,130,246,0.2)',
                    'fill' => true,
                    'tension' => 0.3,
                ],
            ],
            'labels' => $labels,
        ];
    }    protected function getType(): string
    {
        return 'line';
    }
}
