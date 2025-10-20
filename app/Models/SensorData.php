<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SensorData extends Model
{
   use HasFactory;

    protected $fillable = [
        'device_id',
        'sensor_type', 
        'raw_data',
        'ml_results',
        'status',
        'processed_at'
    ];

    protected $casts = [
        'raw_data' => 'array',
        'ml_results' => 'array',
        'processed_at' => 'datetime'
    ];
}
