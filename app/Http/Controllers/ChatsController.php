<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Chat;
use App\Events\ChatMessages;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class ChatsController extends Controller
{
    public function index()
    {

        $currentUser = auth()->user();

        $users = getUsers();
        $users = Chat::getUsersInteractFromChat();

        return view('chat.index', compact('currentUser', 'users'));
    }

    /**
     * Store a new chat message in the database and broadcast it to the recipient
     */
    public function store(Request $request)
    {
        // Define validation rules
        $rules = [
            'message' => 'required|string|max:500',
            'recipient_id' => 'required|integer',
        ];

        // Perform validation manually to customize the response
        $validator = \Validator::make($request->all(), $rules);

        // Check if validation fails
        if ($validator->fails()) {
            // Check specifically for message length error
            if ($validator->errors()->has('message') && strlen($request->message) > 500) {
                return response()->json(['error' => 'Message cannot exceed 500 characters.'], 422);
            }

            return response()->json(['error' => $validator->errors()], 422);
        }

        $currentUserId = auth()->id();

        $message = $request->message;
        $recipientId = $request->recipient_id;

        if ($currentUserId === $recipientId) {
            return response()->json(['success' => false]);
        }

        try {
            $user = auth()->user();

            $messageEncrypted = Crypt::encryptString($message);

            //\Log::info("Attempting to save chat message", ['user_id' => $currentUserId, 'recipient_id' => $recipientId]);
            $chatMessage = new Chat();
            $chatMessage->sender_id = $user->id;
            $chatMessage->recipient_id = $recipientId;
            $chatMessage->message = $messageEncrypted;
            $chatMessage->save();
            //\Log::debug($chatMessage);
            //\Log::info("Message saved, preparing to broadcast", ['chat_message_id' => $chatMessage->id]);

            // Broadcast it
            try {
                event(new ChatMessages($user, $chatMessage, $recipientId));

                //\Log::info("Message broadcasted successfully", ['chat_message_id' => $chatMessage->id]);
            } catch (\Exception $e) {
                \Log::error("Broadcast error: " . $e->getMessage());

                return response()->json(['error' => 'Broadcast error'], 500);
            }

            return response()->json(['success' => true, 'message' => $message, 'sender_name' => $user->name]);
        } catch (\Exception $e) {
            \Log::error('Error saving chat message: ' . $e->getMessage());

            return response()->json(['error' => 'An error occurred while saving the message'], 500);
        }
    }

    /**
     * Retrieves chat messages between the current user and the specified recipient.
     *
     * Fetches paginated chat messages involving the current user and the recipient,
     * ordered by creation time in descending order. Transforms each message to include
     * sender information before returning them in a JSON response.
     *
     * @param  int  $recipientId  The recipient's user ID.
     */
    public function retrieve(Request $request)
    {
        $recipientId = $request->input('recipient_id');
        $page = $request->input('page', 1);

        if (!$recipientId) {
            \Log::error("Recipient ID is required");

            return response()->json(['error' => 'Recipient ID is required'], 400);
        }

        if (!User::find($recipientId)) {
            \Log::error("User ID ".$recipientId." not exist");

            return response()->json(['error' => 'User not exist'], 400);
        }

        $currentUserId = auth()->id();

        try {
            $messages = Chat::with('sender')
                            ->where(function ($query) use ($currentUserId, $recipientId) {
                                $query->where('sender_id', $currentUserId)
                                    ->where('recipient_id', $recipientId);
                            })
                            ->orWhere(function ($query) use ($currentUserId, $recipientId) {
                                $query->where('sender_id', $recipientId)
                                    ->where('recipient_id', $currentUserId)
                                    ->update(['is_read' => true]);// When users is the same, consider the message readed
                            })
                            ->orderBy('created_at', 'desc')
                            ->paginate(env('APP_PAGINATION'), ['*'], 'page', $page);

            if ($messages->isEmpty()) {
                return response()->json(['message' => []], 200);
            }

            $transformedMessages = $messages->getCollection()->transform(function ($chat) {
                $message = $chat->message ?? null;

                if($message){
                    $id = $chat->id ?? null;

                    try {
                        $messageDecripted = Crypt::decryptString($message);
                    } catch (\Exception $e) {
                        \Log::error("Decryption error: " . $e->getMessage());

                        $messageDecripted = "Esta mensagem não pôde ser descriptografada.";
                    }
                    return [
                        'id' => $id,
                        'message' => $messageDecripted,
                        'sender_id' => $chat->sender_id,
                        'sender_name' => $chat->sender->name,
                        'sender_avatar' => checkUserAvatar($chat->sender->avatar),
                        'is_read' => $chat->is_read,
                        'timestamp' => $chat->created_at
                    ];
                }
                return response()->json(['message' => []], 200);
            })->filter();

            if ($transformedMessages->isEmpty()) {
                return response()->json(['message' => []], 200);
            }

            return response()->json([
                'messages' => $transformedMessages,
                'current_page' => $messages->currentPage(),
                'last_page' => $messages->lastPage()
            ]);

        } catch (\Exception $e) {
            \Log::error("Error fetching messages: " . $e->getMessage());

            return response()->json(['error' => 'An error occurred while fetching messages'], 500);
        }
    }

    /**
     * Change status messages when user joined to the channel
     */
    public function markAsRead(Request $request)
    {
        $currentUserId = auth()->id();

        $request->validate([
            'sender_id' => 'required|integer',
            'recipient_id' => 'required|integer',
        ]);

        $senderId = $request->input('sender_id');
        $recipientId = $request->input('recipient_id');

        if ($currentUserId === $senderId) {
            return response()->json(['success' => false]);
        }

        try {
            $chatMessages = Chat::where('sender_id', $senderId)
                                ->where('recipient_id', $recipientId)
                                ->get();

            foreach ($chatMessages as $chatMessage) {
                $chatMessage->is_read = true; // true means read
                $chatMessage->save();
            }

            return response()->json(['success' => true, 'message' => 'Messages marked as read', 'sender_id' => $senderId, 'recipient_id' => $recipientId]);
        } catch (\Exception $e) {
            \Log::error("Error updating message status: {$e->getMessage()}", [
                'sender_id' => $senderId,
                'recipient_id' => $recipientId
            ]);

            return response()->json(['error' => 'An error occurred while updating message status'], 500);
        }
    }

}
