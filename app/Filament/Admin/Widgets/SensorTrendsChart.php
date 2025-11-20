<?php

namespace App\Filament\Admin\Widgets;

use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Http;

class SensorTrendsChart extends Widget
{
    protected string $view = 'filament.admin.widgets.sensor-trends-chart';
    protected ?string $heading = '🔥 Realtime Sensor Data';
    protected int|string|array $columnSpan = 'full';
    protected ?string $pollingInterval = '1s'; // poll every second

    public function getViewData(): array
    {
        $firebaseUrl = rtrim(env('FIREBASE_DATABASE_URL'), '/') . '/sensor_data/esp32_01/history.json';

        try {
            $response = Http::get($firebaseUrl);
            $history = $response->json() ?? [];
        } catch (\Throwable $e) {
            $history = [];
        }

        $labels = [];
        $temps = [];
        $gases = [];
        $humidities = [];

        foreach ($history as $entry) {
            $timestamp = $entry['timestamp'] ?? now()->format('H:i:s');
            $labels[] = date('H:i:s', strtotime($timestamp));
            $temps[] = $entry['temp'] ?? null;
            $gases[] = $entry['mq2'] ?? null;
            $humidities[] = $entry['humidity'] ?? null;
        }

        if (count($labels) > 20) {
            $labels = array_slice($labels, -20);
            $temps = array_slice($temps, -20);
            $gases = array_slice($gases, -20);
            $humidities = array_slice($humidities, -20);
        }

        return [
            'labels' => $labels,
            'temps' => $temps,
            'gases' => $gases,
            'humidities' => $humidities,
        ];
    }
}


//     protected ?string $heading = 'Sensor Trends (Last 24 Hours)';

//     protected int | string | array $columnSpan = 'full'; // Takes full width
    
//     // Polling interval (in seconds)
//     public function getPollingInterval(): ?string
// {
//     return '15s';
// }


    
//     protected function getFirebaseDatabase()
//     {
//         $factory = (new Factory)->withServiceAccount(storage_path('app/serviceAccountKey.json'));
//         return $factory->createDatabase();
//     }
    
//         protected function getData(): array
// {
//     $database = $this->getFirebaseDatabase();
//     $ref = $database->getReference('sensor_data'); // root node
//     $snapshot = $ref->getValue(); // fetch all devices

//     $labels = [];
//     $temps = [];
//     $gases = [];
//     $humidities = [];

//     if ($snapshot) {
//         // Loop through each device
//         foreach ($snapshot as $deviceId => $deviceData) {
//             if (isset($deviceData['latest'])) {
//                 $latest = $deviceData['latest'];

//                 // Format timestamp for the label
//                 $labels[] = date('H:i', strtotime($latest['timestamp']));

//                 $temps[] = floatval($latest['temp']);
//                 $gases[] = floatval($latest['mq2']);
//                 $humidities[] = floatval($latest['humidity']);
//             }
//         }
//     }

//     // Return empty arrays if no data
//     if (empty($labels)) {
//         return [
//             'datasets' => [
//                 ['label' => 'Temperature (°C)', 'data' => [], 'borderColor' => '#ef4444'],
//                 ['label' => 'Gas Level (PPM)', 'data' => [], 'borderColor' => '#f59e0b'],
//                 ['label' => 'Humidity (%)', 'data' => [], 'borderColor' => '#3b82f6'],
//             ],
//             'labels' => [],
//         ];
//     }

//     return [
//         'datasets' => [
//             [
//                 'label' => 'Temperature (°C)',
//                 'data' => $temps,
//                 'borderColor' => '#ef4444',
//                 'backgroundColor' => 'rgba(239,68,68,0.1)',
//                 'tension' => 0.4,
//             ],
//             [
//                 'label' => 'Gas Level (PPM)',
//                 'data' => $gases,
//                 'borderColor' => '#f59e0b',
//                 'backgroundColor' => 'rgba(245,158,11,0.1)',
//                 'tension' => 0.4,
//             ],
//             [
//                 'label' => 'Humidity (%)',
//                 'data' => $humidities,
//                 'borderColor' => '#3b82f6',
//                 'backgroundColor' => 'rgba(59,130,246,0.1)',
//                 'tension' => 0.4,
//             ],
//         ],
//         'labels' => $labels,
//     ];
// }
//     protected function getType(): string
//     {
//         return 'line';
//     }

//     protected function getOptions(): array
//     {
//         return [
//             'plugins' => [
//                 'legend' => [
//                     'display' => true,
//                     'position' => 'top',
//                 ],
//             ],
//             'scales' => [
//                 'y' => [
//                     'beginAtZero' => false,
//                 ],
//             ],
//         ];
//     }
    
//     // Add polling interval as a method instead of property
//     // public static function getPollingInterval(): ?string
//     // {
//     //     return '30s';
//     // }
//