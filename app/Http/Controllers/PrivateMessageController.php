<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PrivateMessage;
use App\Models\User;

class PrivateMessageController extends Controller
{
    // Récupérer la conversation avec un utilisateur
    public function index(Request $request, $userId)
    {
          if (!is_numeric($userId)) {
        return response()->json(['error' => 'Identifiant invalide'], 400);
    }
        $me = $request->user();

        $messages = PrivateMessage::with(['sender', 'receiver'])
            ->where(function ($q) use ($me, $userId) {
                $q->where('sender_id', $me->id)->where('receiver_id', $userId);
            })
            ->orWhere(function ($q) use ($me, $userId) {
                $q->where('sender_id', $userId)->where('receiver_id', $me->id);
            })
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($msg) use ($me) {
                return [
                    'id'         => $msg->id,
                    'body'       => $msg->body,
                    'mine'       => $msg->sender_id === $me->id,
                    'sender'     => $msg->sender?->pseudo ?? $msg->sender?->name,
                    'created_at' => $msg->created_at,
                ];
            });

        // Marquer comme lus
        PrivateMessage::where('sender_id', $userId)
            ->where('receiver_id', $me->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json($messages);
    }

    // Envoyer un message
    public function store(Request $request, $userId)
    {  if (!is_numeric($userId)) {
        return response()->json(['error' => 'Identifiant invalide'], 400);
    }
        $request->validate([
            'body' => 'required|string|max:2000'
        ]);

        $me = $request->user();

        // Vérifier que le destinataire existe
        $receiver = User::findOrFail($userId);

        $msg = PrivateMessage::create([
            'sender_id'   => $me->id,
            'receiver_id' => $receiver->id,
            'body'        => $request->body,
        ]);

        return response()->json([
            'id'         => $msg->id,
            'body'       => $msg->body,
            'mine'       => true,
            'sender'     => $me->pseudo ?? $me->name,
            'created_at' => $msg->created_at,
        ], 201);
    }

    // Nombre de messages non lus
    public function unreadCount(Request $request)
    {
        $count = PrivateMessage::where('receiver_id', $request->user()->id)
            ->where('is_read', false)
            ->count();

        return response()->json(['unread' => $count]);
    }
}