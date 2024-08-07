<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Chat extends Model
{
    protected $connection = 'vpOnboard';

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
        //return $this->belongsTo(User::class, 'sender_id')->connection('vpOnboard');
    }

    /**
     * Relationship to the User model for the recipient
     */
    public function recipient()
    {
        return $this->belongsTo(User::class, 'recipient_id');
        //return $this->belongsTo(User::class, 'recipient_id')->connection('vpOnboard');
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

    /**
     * List all distinct users with whom the current user has interacted in chats.
     * This includes both users they have sent messages to and users they have received messages from.
     * Returns user details from the User model.
     */
    public static function getUsersInteractFromChat()
    {
        $currentUserId = auth()->id();

        // Fetch all chat records where the current user is either sender or recipient
        $chatUserIds = Chat::where('sender_id', $currentUserId)
            ->orWhere('recipient_id', $currentUserId)
            ->get()
            ->flatMap(function ($chat) use ($currentUserId) {
                // Return both sender and recipient IDs, removing any duplicates within each chat
                return [$chat->sender_id, $chat->recipient_id];
            })
            ->unique()  // Remove duplicate user IDs from the list
            ->reject(function ($id) use ($currentUserId) {
                // Exclude the current user's ID if you don't want them in the results
                return $id === $currentUserId;
            });

        // Fetch and return user models for all IDs collected from chats
        return User::whereIn('id', $chatUserIds)->get();
    }








}
