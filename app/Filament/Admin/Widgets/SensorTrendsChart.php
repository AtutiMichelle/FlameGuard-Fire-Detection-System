<?php

namespace App\Filament\Admin\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\SensorData;
use Illuminate\Support\Facades\DB;

class SensorTrendsChart extends ChartWidget
{
    protected ?string $heading = 'Sensor Trends (Last 24 Hours)';

    protected int | string | array $columnSpan = 'full'; // Takes full width
    
    // Remove these static properties - they're already defined in the parent class
    // protected static ?string $pollingInterval = '30s';
    // protected static ?string $maxHeight = '300px';

    protected function getData(): array
    {
        // Get data grouped by hour for the last 24 hours
        $data = SensorData::where('created_at', '>=', now()->subDay())
            ->select(
                DB::raw('DATE_FORMAT(created_at, "%H:00") as hour'),
                DB::raw('AVG(JSON_UNQUOTE(JSON_EXTRACT(raw_data, "$.temp"))) as avg_temp'),
                DB::raw('AVG(JSON_UNQUOTE(JSON_EXTRACT(raw_data, "$.mq2"))) as avg_gas'),
                DB::raw('AVG(JSON_UNQUOTE(JSON_EXTRACT(raw_data, "$.humidity"))) as avg_humidity')
            )
            ->groupBy('hour')
            ->orderBy('hour')
            ->get();

        // If no data, return empty arrays
        if ($data->isEmpty()) {
            return [
                'datasets' => [
                    [
                        'label' => 'Temperature (°C)',
                        'data' => [],
                        'borderColor' => '#ef4444',
                    ],
                    [
                        'label' => 'Gas Level (PPM)',
                        'data' => [],
                        'borderColor' => '#f59e0b',
                    ],
                    [
                        'label' => 'Humidity (%)',
                        'data' => [],
                        'borderColor' => '#3b82f6',
                    ],
                ],
                'labels' => [],
            ];
        }

        return [
            'datasets' => [
                [
                    'label' => 'Temperature (°C)',
                    'data' => $data->pluck('avg_temp')->map(fn($val) => floatval($val))->toArray(),
                    'borderColor' => '#ef4444',
                    'backgroundColor' => 'rgba(239, 68, 68, 0.1)',
                    'tension' => 0.4,
                ],
                [
                    'label' => 'Gas Level (PPM)',
                    'data' => $data->pluck('avg_gas')->map(fn($val) => floatval($val))->toArray(),
                    'borderColor' => '#f59e0b',
                    'backgroundColor' => 'rgba(245, 158, 11, 0.1)',
                    'tension' => 0.4,
                ],
                [
                    'label' => 'Humidity (%)',
                    'data' => $data->pluck('avg_humidity')->map(fn($val) => floatval($val))->toArray(),
                    'borderColor' => '#3b82f6',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'tension' => 0.4,
                ],
            ],
            'labels' => $data->pluck('hour')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'top',
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => false,
                ],
            ],
        ];
    }
    
    // Add polling interval as a method instead of property
    // public static function getPollingInterval(): ?string
    // {
    //     return '30s';
    // }
}