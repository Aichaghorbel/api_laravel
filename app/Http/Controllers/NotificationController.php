<?php

namespace App\Http\Controllers;
use Illuminate\Validation\Rule;

use Illuminate\Http\Request;
use App\Models\Notification;

class NotificationController extends Controller
{
    // 🔹 Lister toutes les notifications d'un utilisateur
   public function index(Request $request)
{
    $user = $request->user();

    $notifications = Notification::where('user_id', $user->id)
        ->latest()
        ->get()
        ->map(function ($n) {
            return [
                'id' => $n->id,
                'type' => $n->type,
                'from_user_name' => $n->from_user,   // ✅ PSEUDO
                'post_title' => $n->post_title,       // ✅ TITRE
            ];
        });

    return response()->json([
        'notifications' => $notifications
    ]);
}

    
    

    // 🔹 Supprimer une notification
    public function destroy(Request $request, $id)
    {
        $notification = Notification::findOrFail($id);

        if ($notification->user_id !== $request->user()->id && $request->user()->role !== 'admin') {
            return response()->json(['error' => 'Non autorisé'], 403);
        }

        $notification->delete();

        return response()->json(['message' => 'Notification supprimée']);
    }
}