<?php

namespace App\Filament\Admin\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\SensorData;

class GasChartWidget extends ChartWidget
{
   protected ?string $heading = '💨 Gas / Smoke Level';
    protected static ?int $sort = 3;
    protected ?string $pollingInterval = '10s';

    protected $listeners = ['echo:sensor-channel,SensorDataUpdated' => '$refresh'];


    protected function getData(): array
    {
        $records = SensorData::latest()->take(20)->get()->reverse();

        $labels = array_values($records->map(fn($data) => $data->created_at->format('H:i:s'))->toArray());
        $gasLevels = array_values($records->pluck('raw_data.mq2')->map(fn($g) => (float)$g)->toArray());

        return [
            'datasets' => [
                [
                    'label' => 'Gas Concentration (PPM)',
                    'data' => $gasLevels,
                    'borderColor' => '#f59e0b',
                    'backgroundColor' => 'rgba(245,158,11,0.2)',
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
