<?php

namespace App\Http\Controllers;
use Illuminate\Validation\Rule;

use Illuminate\Http\Request;
use App\Models\Suspension;
use App\Models\User;
use App\Models\Notification;
class SuspensionController extends Controller
{
    // 🔹 Lister toutes les suspensions
    public function index()
    {
        $suspensions = Suspension::with(['user', 'moderateur'])->get();

        return response()->json([
            'count' => $suspensions->count(),
            'suspensions' => $suspensions
        ]);
    }

    // 🔹 Suspendre un utilisateur (ADMIN ou MODERATEUR seulement)
    public function store(Request $request)
{
    $userAuth = $request->user();

    if (!in_array($userAuth->role, ['admin', 'moderateur'])) {
        return response()->json(['error' => 'Accès refusé'], 403);
    }

    $request->validate([
        'user_id' => 'required|exists:users,id',
        'reason' => 'required|string|max:255'
    ]);

    $userToSuspend = User::findOrFail($request->user_id);

    if ($userToSuspend->role === 'admin') {
        return response()->json(['error' => 'Impossible de suspendre admin'], 403);
    }

    $suspension = Suspension::create([
        'user_id' => $userToSuspend->id,
        'moderateur_id' => $userAuth->id,
        'reason' => $request->reason,
        'dateSusp' => now(),
    ]);

    // 🔥 MAJ statut
    $userToSuspend->update(['status' => 'suspendu']);

    // 🔥 NOTIFICATION AUTO
    Notification::create([
        'user_id' => $userToSuspend->id,
        'message' => 'Votre compte a été suspendu pour : ' . $request->reason
    ]);

    return response()->json([
        'message' => 'Utilisateur suspendu',
        'suspension' => $suspension
    ]);
}

    // 🔹 Annuler une suspension (ADMIN ou MODERATEUR)
    public function destroy(Request $request, $id)
    {
        $userAuth = $request->user();

        // 🔒 Vérifier rôle
        if (!in_array($userAuth->role, ['admin', 'moderateur'])) {
            return response()->json([
                'error' => 'Accès refusé'
            ], 403);
        }

        $suspension = Suspension::findOrFail($id);

        $user = $suspension->user;

        // 🔹 réactiver utilisateur
        $user->update([
            'status' => 'actif'
        ]);

        $suspension->delete();

        return response()->json([
            'message' => 'Suspension annulée'
        ]);
    }
}