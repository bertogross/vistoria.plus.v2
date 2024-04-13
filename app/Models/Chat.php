<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Chat extends Model
{
    public $timestamps = true;

    protected $table = 'chats';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'sender_id',
        'recipient_id',
        'message',
        'is_read'
    ];

    /**
     * Relationship to the User model for the sender
     */
    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /**
     * Relationship to the User model for the recipient
     */
    public function recipient()
    {
        return $this->belongsTo(User::class, 'recipient_id');
    }

    /**
     * Generate the private channel name between two users
     */
    public static function generateChannelName($userId1, $userId2)
    {
        $ids = [$userId1, $userId2];

        // To avoid ambiguity, you might want to sort the user IDs so that the lower ID always comes first. This ensures that both users listen and broadcast to the same channel name regardless of who initiates the chat.
        sort($ids);
        return 'chat-channel.' . implode('_', $ids);
    }
}
