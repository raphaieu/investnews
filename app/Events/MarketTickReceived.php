<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MarketTickReceived implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public array $tick;
    public string $symbol;
    public string $timestamp;

    public function __construct(array $tick, string $symbol)
    {
        $this->tick = $tick;
        $this->symbol = $symbol;
        $this->timestamp = now()->toISOString();
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('market-ticks'),
            new Channel('market-ticks.' . $this->symbol),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'symbol'    => $this->symbol,
            'tick'      => $this->tick,
            'timestamp' => $this->timestamp,
        ];
    }

    public function broadcastAs(): string
    {
        return 'market.tick';
    }
}
