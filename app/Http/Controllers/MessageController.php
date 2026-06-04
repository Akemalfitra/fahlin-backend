<?php

namespace App\Http\Controllers;

use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MessageController extends Controller
{
    // --- FUNGSI UNTUK API FLUTTER ---
    public function getMessages($userId)
    {
        try {
            $messages = Message::where('user_id', $userId)->orderBy('created_at', 'asc')->get();
            return response()->json(['status' => 'success', 'data' => $messages], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|integer',
            'message' => 'required|string',
            'sender' => 'required|in:user,admin',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
        }

        $message = Message::create([
            'user_id' => $request->user_id,
            'message' => $request->message,
            'sender' => $request->sender,
        ]);

        return response()->json(['status' => 'success', 'data' => $message], 201);
    }

    // --- FUNGSI UNTUK WEB ADMIN (YANG TADI ERROR) ---

    // Menampilkan daftar user yang pernah chat
    public function adminIndex()
    {
        // Fungsi ini yang tadinya "Undefined"
        $chats = Message::select('user_id')
                        ->groupBy('user_id')
                        ->get();
        return view('admin.messages.index', compact('chats'));
    }

    // Menampilkan detail chat
    public function adminShow($userId)
    {
        $messages = Message::where('user_id', $userId)->orderBy('created_at', 'asc')->get();
        return view('admin.messages.show', compact('messages', 'userId'));
    }

    // Balas pesan dari Web
    public function adminStore(Request $request)
    {
        $request->validate([
            'user_id' => 'required',
            'message' => 'required',
        ]);

        Message::create([
            'user_id' => $request->user_id,
            'message' => $request->message,
            'sender' => 'admin',
        ]);

        return back()->with('success', 'Balasan terkirim!');
    }
}