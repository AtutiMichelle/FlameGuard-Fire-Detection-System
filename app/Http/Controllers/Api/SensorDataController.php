<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SensorData;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use App\Events\SensorDataUpdated;

class SensorDataController extends Controller
{
    /**
     * Store sensor data + ML results sent from Flask
     */
    public function store(Request $request): JsonResponse
    {
    
    // Validate incoming data from Flask
    $validated = $request->validate([
        'device_id' => 'required|string|max:255',
        'mq2' => 'required|numeric',
        'temp' => 'required|numeric',
        'humidity' => 'required|numeric',
        'fire_detected' => 'required|boolean',
        'confidence' => 'required|numeric'
    ]);

    try {
        // Create sensor data record in MySQL
        $sensorData = SensorData::create([
            'device_id' => $validated['device_id'],
            'sensor_type' => 'composite', // combined sensors
            'raw_data' => [
                'mq2' => $validated['mq2'],
                'temp' => $validated['temp'],
                'humidity' => $validated['humidity']
            ],
            'ml_results' => [
                'fire_detected' => $validated['fire_detected'],
                'confidence' => $validated['confidence']
            ],
            'status' => 'logged',
            'processed_at' => now()
        ]);

        // ✅ Trigger broadcast event for real-time dashboard update
        event(new \App\Events\SensorDataUpdated($sensorData));

        return response()->json([
            'status' => 'success',
            'message' => 'Sensor data logged successfully',
            'data_id' => $sensorData->id,
            'ml_results' => $sensorData->ml_results
        ], 201);

    } catch (\Exception $e) {
        Log::error('FlameGuard sensor data logging failed: ' . $e->getMessage());

        return response()->json([
            'status' => 'error',
            'message' => 'Data logging failed: ' . $e->getMessage()
        ], 500);
    }
}
    

    /**
     * Show historical sensor data by ID
     */
    public function show($id): JsonResponse
    {
        $sensorData = SensorData::findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data' => $sensorData
        ]);
    }
}

