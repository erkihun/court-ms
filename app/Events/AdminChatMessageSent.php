<?php

namespace App\Events;

use App\Models\AdminChatMessage;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AdminChatMessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public AdminChatMessage $message;

    public function __construct(AdminChatMessage $message)
    {
        $this->message = $message->load('sender');
    }

    public function broadcastOn(): Channel
    {
        return new Channel('admin-chat');
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->message->id,
            'message' => $this->message->message,
            'sender_name' => $this->message->sender?->name,
            'sender_id' => $this->message->sender_user_id,
            'recipient_id' => $this->message->recipient_user_id,
            'created_at' => $this->message->created_at?->toIsoString(),
        ];
    }

    public function broadcastAs(): string
    {
        return 'AdminChatMessageSent';
    }
}
