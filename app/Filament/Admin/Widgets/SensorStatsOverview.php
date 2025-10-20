<?php

namespace App\Filament\Admin\Widgets;

use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\SensorData;

class SensorStatsOverview extends StatsOverviewWidget
{
      
    // protected static ?string $pollingInterval = '10s';
    
    protected function getStats(): array
    {
        // Get the latest sensor data
        $latestData = SensorData::latest()->first();
        
        if (!$latestData) {
            return [
                Stat::make('No Data', 'No sensor data available')
                    ->description('Waiting for ESP32 connection')
                    ->color('gray')
                    ->icon('heroicon-o-exclamation-circle'),
            ];
        }

        // Extract values from the latest record
        $temperature = $latestData->raw_data['temp'] ?? 0;
        $gasLevel = $latestData->raw_data['mq2'] ?? 0;
        $humidity = $latestData->raw_data['humidity'] ?? 0;
        
        // Get ML prediction results
        // $mlResults = $latestData->ml_results ?? [];
        // $fireDetected = $mlResults['fire_detected'] ?? false;
        // $confidence = $mlResults['confidence'] ?? 0;
        // $riskLevel = $mlResults['risk_level'] ?? 'unknown';

        return [
            Stat::make('Temperature', number_format($temperature, 1) . '°C')
                ->description('Current reading')
                ->color($this->getTemperatureColor($temperature))
                ->icon('heroicon-o-fire')
                ->chart($this->getTemperatureHistory())
                ->descriptionIcon($this->getTemperatureTrend($temperature)),

            Stat::make('Gas Level', number_format($gasLevel, 2) . ' PPM')
                ->description('MQ2 Sensor')
                ->color($this->getGasLevelColor($gasLevel))
                ->icon('heroicon-o-beaker')
                ->descriptionIcon($gasLevel > 5 ? 'heroicon-o-arrow-trending-up' : 'heroicon-o-arrow-trending-down'),

            Stat::make('Humidity', number_format($humidity, 1) . '%')
                ->description('Environment')
                ->color($this->getHumidityColor($humidity))
                ->icon('heroicon-o-cloud')
                ->descriptionIcon('heroicon-o-document-chart-bar'),

            // Stat::make('Fire Risk', ucfirst($riskLevel))
            //     ->description($fireDetected ? 'Fire Detected!' : 'All Clear')
            //     ->color($this->getRiskColor($riskLevel, $fireDetected))
            //     ->icon($this->getRiskIcon($riskLevel, $fireDetected))
            //     ->descriptionIcon($fireDetected ? 'heroicon-o-exclamation-triangle' : 'heroicon-o-check-circle'),
                
            // Stat::make('Confidence', number_format($confidence * 100, 1) . '%')
            //     ->description('ML Prediction')
            //     ->color($this->getConfidenceColor($confidence))
            //     ->icon('heroicon-o-cpu-chip')
            //     ->descriptionIcon($confidence > 0.7 ? 'heroicon-o-shield-check' : 'heroicon-o-shield-exclamation'),
        ];
    }

    // Helper methods for dynamic colors and icons
    private function getTemperatureColor(float $temperature): string
    {
        return match(true) {
            $temperature > 35 => 'danger',
            $temperature > 28 => 'warning',  
            $temperature > 15 => 'success',
            default => 'gray',
        };
    }

    private function getGasLevelColor(float $gasLevel): string
    {
        return match(true) {
            $gasLevel > 8 => 'danger',
            $gasLevel > 5 => 'warning',
            $gasLevel > 2 => 'success',
            default => 'gray',
        };
    }

    private function getHumidityColor(float $humidity): string
    {
        return match(true) {
            $humidity > 80 => 'warning',
            $humidity > 30 => 'success',
            default => 'gray',
        };
    }

    // private function getRiskColor(string $riskLevel, bool $fireDetected): string
    // {
    //     if ($fireDetected) {
    //         return 'danger';
    //     }
        
    //     return match($riskLevel) {
    //         'high' => 'danger',
    //         'medium' => 'warning',
    //         'low' => 'success',
    //         default => 'gray',
    //     };
    // }

    // private function getRiskIcon(string $riskLevel, bool $fireDetected): string
    // {
    //     if ($fireDetected) {
    //         return 'heroicon-o-exclamation-triangle';
    //     }
        
    //     return match($riskLevel) {
    //         'high' => 'heroicon-o-shield-exclamation',
    //         'medium' => 'heroicon-o-clock',
    //         'low' => 'heroicon-o-shield-check',
    //         default => 'heroicon-o-question-mark-circle',
    //     };
    // }

    // private function getConfidenceColor(float $confidence): string
    // {
    //     return match(true) {
    //         $confidence > 0.8 => 'success',
    //         $confidence > 0.6 => 'warning',
    //         default => 'gray',
    //     };
    // }

    private function getTemperatureHistory(): array
    {
        $temperatures = SensorData::latest()
            ->limit(7)
            ->get()
            ->pluck('raw_data.temp')
            ->filter()
            ->values()
            ->toArray();

        if (count($temperatures) < 2) {
            $latestData = SensorData::latest()->first();
            $currentTemp = $latestData->raw_data['temp'] ?? 25;
            return array_fill(0, 7, $currentTemp);
        }

        return $temperatures;
    }

    private function getTemperatureTrend(float $currentTemp): string
    {
        $previousData = SensorData::latest()->skip(1)->first();
        
        if (!$previousData) {
            return 'heroicon-o-minus';
        }

        $previousTemp = $previousData->raw_data['temp'] ?? 0;
        
        if ($currentTemp > $previousTemp) {
            return 'heroicon-o-arrow-trending-up';
        } elseif ($currentTemp < $previousTemp) {
            return 'heroicon-o-arrow-trending-down';
        }
        
        return 'heroicon-o-minus';
    }
}
