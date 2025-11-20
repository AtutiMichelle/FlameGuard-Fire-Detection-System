<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\SensorData;

class SensorDataUpdated
{
    use Dispatchable, InteractsWithSockets, SerializesModels, SerializesModels;

    public function __construct(public SensorData $data) {}

    public function broadcastOn()
    {
        return new Channel('sensor-channel');
    }

    public function broadcastWith()
    {
        return [
            'temp' => $this->data->raw_data['temp'] ?? null,
            'mq2' => $this->data->raw_data['mq2'] ?? null,
            'humidity' => $this->data->raw_data['humidity'] ?? null,
            'timestamp' => $this->data->created_at->toDateTimeString(),
        ];
    }
}
