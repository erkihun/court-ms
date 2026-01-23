<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AdminChatMessagesRead implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $senderUserId;
    public int $readerUserId;
    public string $readAt;

    public function __construct(int $senderUserId, int $readerUserId, string $readAt)
    {
        $this->senderUserId = $senderUserId;
        $this->readerUserId = $readerUserId;
        $this->readAt = $readAt;
    }

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel('admin-chat.' . $this->senderUserId);
    }

    public function broadcastWith(): array
    {
        return [
            'sender_id' => $this->senderUserId,
            'reader_id' => $this->readerUserId,
            'read_at' => $this->readAt,
        ];
    }

    public function broadcastAs(): string
    {
        return 'AdminChatMessagesRead';
    }
}
