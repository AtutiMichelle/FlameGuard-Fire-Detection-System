<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SensorData;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SensorDataController extends Controller
{
    private $mlApiUrl;

    public function __construct()
    {
        $this->mlApiUrl = env('ML_API_URL', 'http://localhost:5000');
    }

    public function store(Request $request): JsonResponse
    {
        // Validate incoming ESP32 data
        $validated = $request->validate([
            'device_id' => 'required|string|max:255',
            'mq2' => 'required|numeric',
            'temp' => 'required|numeric',
            'humidity' => 'required|numeric'
        ]);

        try {
            // Send to Flask ML API for prediction
            $mlResults = $this->callMLAPI($validated);
            
            // Create sensor data record
            $sensorData = SensorData::create([
                'device_id' => $validated['device_id'],
                'sensor_type' => 'composite', // Combined sensors
                'raw_data' => [
                    'mq2' => $validated['mq2'],
                    'temp' => $validated['temp'],
                    'humidity' => $validated['humidity']
                ],
                'ml_results' => $mlResults,
                'status' => 'completed',
                'processed_at' => now()
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Sensor data processed successfully',
                'data_id' => $sensorData->id,
                'ml_insights' => $mlResults,
                'fire_detected' => $mlResults['fire_detected'],
                'confidence' => $mlResults['confidence']
            ], 201);

        } catch (\Exception $e) {
            Log::error('FlameGuard sensor data processing failed: ' . $e->getMessage());
            
            return response()->json([
                'status' => 'error',
                'message' => 'Data processing failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Call Flask ML API for prediction
     */
    private function callMLAPI(array $sensorData): array
    {
        $response = Http::timeout(10)
            ->retry(3, 100)
            ->post($this->mlApiUrl . '/predict', [
                'mq2' => $sensorData['mq2'],
                'temp' => $sensorData['temp'],
                'humidity' => $sensorData['humidity'],
                'device_id' => $sensorData['device_id']
            ]);

        if ($response->successful()) {
            return $response->json();
        }

        throw new \Exception('ML API request failed: ' . $response->body());
    }

    public function show($id): JsonResponse
    {
        $sensorData = SensorData::findOrFail($id);
        
        return response()->json([
            'status' => 'success',
            'data' => $sensorData
        ]);
    }
}