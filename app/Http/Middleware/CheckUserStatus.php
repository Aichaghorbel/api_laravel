<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckUserStatus
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        // 🔒 Vérifier si utilisateur suspendu
        if ($user && $user->status === 'suspendu') {
            return response()->json([
                'error' => 'Votre compte est suspendu'
            ], 403);
        }

        return $next($request);
    }
}