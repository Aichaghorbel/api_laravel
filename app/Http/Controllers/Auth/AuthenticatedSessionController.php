<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse; // <-- important
use Illuminate\Support\Facades\Auth;

class AuthenticatedSessionController extends Controller
{
    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): JsonResponse
    {
        // 🔐 Vérifier email + password (stateless)
        if (!Auth::once($request->only('email', 'password'))) {
            return response()->json([
                'error' => 'Invalid credentials'
            ], 401);
        }

        // 👤 récupérer user
        $user = Auth::user();

        // 🚫 Vérifier si l'utilisateur est suspendu
        if ($user->status === 'suspendu') {
            $suspension = \App\Models\Suspension::where('user_id', $user->id)
                ->orderBy('id', 'desc')
                ->first();
                
            $reason = $suspension ? $suspension->reason : 'Violation des conditions.';
            
            // Aucune session n'a été créée grâce à Auth::once()


            return response()->json([
                'error' => 'Compte suspendu',
                'reason' => $reason
            ], 403);
        }

        // 🔑 créer token
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token
        ]);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): JsonResponse
    {
        // Pour Sanctum, la déconnexion se fait généralement côté client en supprimant le token, 
        // ou ici en révoquant le token actuel si nécessaire.


        // Pas besoin de session en API


        return response()->json([], 204); // No Content
    }
}