<?php
namespace App\Events;

use App\Models\User;
use App\Models\Chat;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Support\Facades\Crypt;

class ChatMessages implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $user;
    public $message;
    public $recipientId;

    /**
     * Create a new event instance.
     *
     * @param User $user Sender of the message
     * @param Chat $message Chat message instance
     * @param int $recipientId Recipient user ID
     */
    public function __construct(User $user, Chat $message, $recipientId)
    {
        $this->user = $user;
        $this->message = $message;
        $this->recipientId = $recipientId;
    }

    /**
     * Get the channels the event should broadcast on.
     * Broadcasts on both the sender's and recipient's private channels.
     */
    public function broadcastOn()
    {
        $channelName = Chat::generateChannelName($this->user->id, $this->recipientId);
        return new PrivateChannel($channelName);
    }

    /**
     * The Broadcast Name
     */
    public function broadcastAs()
    {
        return 'chat.messages';
    }

    /**
     * Data to broadcast with the event.
     */
    public function broadcastWith()
    {
        $message = $this->message->message;
        $messageDecripted = Crypt::decryptString($message);

        return [
            'message_id' => $this->message->id,
            'message' => $messageDecripted,
            'is_read' => $this->message->is_read,
            'sender_name' => $this->user->name,
            'sender_id' => $this->user->id,
            'recipient_id' => $this->message->recipient_id,
            'timestamp' => $this->message->created_at->toDateTimeString(),
        ];
    }


}
