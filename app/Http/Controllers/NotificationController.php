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
        // 🔹 DEBUG : afficher l'id de l'utilisateur connecté
        $user = $request->user();
        \Log::info('Utilisateur connecté: '.$user->id);

        $notifications = Notification::where('user_id', $user->id)
            ->latest()
            ->get();

        return response()->json([
            'user_id' => $user->id,
            'count' => $notifications->count(),
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