<?php

namespace App\Http\Controllers;
use Illuminate\Validation\Rule;

use Illuminate\Http\Request;
use App\Models\Chat;

class ChatController extends Controller
{
    // 🔹 Lister tous les messages (ou les derniers n)
    public function index()
    {
        $chats = Chat::with('user')
            ->latest()
            ->take(50) // par exemple, les 50 derniers
            ->get();

        return response()->json($chats);
    }

    // 🔹 Envoyer un message
    public function store(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        $chat = Chat::create([
            'message' => $request->message,
            'user_id' => $request->user()->id,
        ]);

        return response()->json([
            'message' => 'Message envoyé',
            'chat' => $chat
        ], 201);
    }

    // 🔹 Supprimer un message (seul propriétaire ou admin)
    public function destroy(Request $request, $id)
    {
        $chat = Chat::findOrFail($id);

        if ($chat->user_id !== $request->user()->id && $request->user()->role !== 'admin') {
            return response()->json(['error' => 'Non autorisé'], 403);
        }

        $chat->delete();

        return response()->json(['message' => 'Message supprimé']);
    }
}