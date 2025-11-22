<?php

namespace App\Filament\Admin\Widgets;

use App\Models\SensorData;
use Filament\Widgets\ChartWidget;

class FireConfidenceChart extends ChartWidget
{
    protected ?string $heading = 'Fire Severity (Confidence)';
    protected static bool $isDiscovered = false;

    // Use scatter chart
    protected function getType(): string
    {
        return 'scatter';
    }

    protected function getData(): array
    {
        // Get latest 20 fire alerts
        $records = SensorData::where('ml_results->fire_detected', true)
            ->latest()
            ->take(20)
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Confidence',
                    'data' => $records->map(fn($r) => [
                        'x' => $r->created_at->timestamp, // numeric timestamp for x-axis
                        'y' => (float) ($r->ml_results['confidence'] ?? 0), // numeric confidence
                    ])->toArray(),
                    'backgroundColor' => '#FFAA00',
                ],
            ],
        ];
    }

    // Optional: format x-axis labels as time
    protected function getOptions(): array
    {
        return [
            'scales' => [
                'x' => [
                    'type' => 'time',
                    'time' => [
                        'unit' => 'minute',
                        'tooltipFormat' => 'H:mm',
                    ],
                ],
                'y' => [
                    'min' => 0,
                    'max' => 1,
                ],
            ],
        ];
    }
}
