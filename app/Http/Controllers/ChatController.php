<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Events\MessageSent;

class ChatController extends Controller
{
    public function index()
    {
        return view('chat.index'); // Ensure you have this view created under resources/views/chat/index.blade.php
    }


    public function sendMessage(Request $request)
    {
        // Validate the request data
        $validatedData = $request->validate([
            'message' => 'required|string|max:255',
        ]);

        // Normally, you would also store the message in the database here

        // Broadcast the message using an event
        event(new MessageSent($request->user(), $validatedData['message']));

        return response()->json(['status' => 'Message sent successfully']);
    }


}
