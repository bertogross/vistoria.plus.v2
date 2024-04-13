<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Chat;
use App\Events\ChatMessages;
use Illuminate\Support\Facades\Crypt;

class ChatController extends Controller
{
    public function index()
    {

        $currentUser = auth()->user();

        $users = getUsers();

        return view('chat.index', compact('currentUser', 'users'));
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
    public function show(Request $request)
    {
        $recipientId = $request->input('recipient_id');

        if (!$recipientId) {
            \Log::error("Recipient ID is required");
            return;
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
                            ->paginate(env('APP_PAGINATION'));

            $messages->getCollection()->transform(function ($chat) {
                $message = $chat->message ?? null;

                if($message){
                    $id = $chat->id ?? null;

                    try {
                        $messageDecripted = Crypt::decryptString($message);
                    } catch (\Exception $e) {
                        \Log::error("Decryption error: " . $e->getMessage());

                        $messageDecripted = "Message cannot be decrypted.";
                    }
                    return [
                        'message' => $messageDecripted,
                        'sender_name' => $chat->sender->name,
                        'sender_id' => $chat->sender_id,
                        'timestamp' => $chat->created_at,
                        'is_read' => $chat->is_read,
                        'id' => $id
                    ];
                }

            });

            return response()->json($messages);

        } catch (\Exception $e) {
            \Log::error("Error fetching messages: " . $e->getMessage());

            return response()->json(['error' => 'An error occurred while fetching messages'], 500);
        }
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

        $messageContent = $request->message;
        $recipientId = $request->recipient_id;

        if ($currentUserId === $recipientId) {
            return response()->json(['success' => false]);
        }

        try {
            $user = auth()->user();

            $messageContentEncrypted = Crypt::encryptString($messageContent);

            $chatMessage  = new Chat();
            $chatMessage ->sender_id = $user->id;
            $chatMessage ->recipient_id = $recipientId;
            $chatMessage ->message = $messageContentEncrypted;
            $chatMessage ->save();

            // Broadcast it
            event(new ChatMessages($user, $chatMessage, $recipientId));

            return response()->json(['success' => true, 'message' => $messageContent, 'sender_name' => $user->name]);
        } catch (\Exception $e) {
            \Log::error('Error saving chat message: ' . $e->getMessage());

            return response()->json(['error' => 'An error occurred while saving the message'], 500);
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

    /**
     * Get all users except the current
     */
    public function getUsers(Request $request)
    {
        $currentUserId = auth()->id();

        // Retrieve all users except the current user
        $users = User::where('id', '!=', $currentUserId)
                     ->get(['id', 'name']);

        // Convert the users to an array suitable for the select options
        // where the user's ID is the key, and the user's name is the value
        $options = $users->pluck('name', 'id')->toArray();

        // Get the selected user ID from the request, if any
        $selectedOption = $request->input('sender_id');

        return view('dashboard', compact('options', 'selectedOption'));
    }
}
