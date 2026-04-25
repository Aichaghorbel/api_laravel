<?php

namespace App\Http\Controllers;
use Illuminate\Validation\Rule;
use App\Models\Notification;

use Illuminate\Http\Request;
use App\Models\Reaction;
use App\Models\Post;

class ReactionController extends Controller
{
    // 🔹 Lister les réactions d’un post
    public function index($post_id)
    {
        $post = Post::findOrFail($post_id);

        $reactions = $post->reactions()->with('user')->get();

        return response()->json([
            'post_id' => $post_id,
            'count' => $reactions->count(),
            'reactions' => $reactions
        ]);
    }

    // 🔹 Ajouter ou mettre à jour une réaction

public function store(Request $request, $post_id)
{
    $request->validate([
        'type' => 'required|in:like,dislike'
    ]);

    $user = $request->user();
    $post = Post::findOrFail($post_id);

    $reaction = Reaction::updateOrCreate(
        ['user_id' => $user->id, 'post_id' => $post_id],
        ['type' => $request->type]
    );

    // 🔔 Notification
    if ($post->user_id != $user->id) {
        Notification::create([
            'user_id' => $post->user_id,
            'from_user' => $user->pseudo,
            'post_title' => $post->titre,
            'type' => 'like',
            'message' => 'a aimé votre post'
        ]);
    }

    return response()->json([
        'message' => 'Réaction ajoutée',
        'reaction' => $reaction
    ]);
}
    // 🔹 Modifier une réaction existante (méthode séparée)
    public function update(Request $request, $id)
    {
        $request->validate([
            'type' => 'required|in:like,dislike'
        ]);

        $reaction = Reaction::findOrFail($id);

        // Vérifier que l'utilisateur est propriétaire ou admin
        if ($reaction->user_id !== $request->user()->id && $request->user()->role !== 'admin') {
            return response()->json(['error' => 'Non autorisé'], 403);
        }

        $reaction->update(['type' => $request->type]);

        return response()->json([
            'message' => 'Réaction modifiée',
            'reaction' => $reaction
        ]);
    }

    // 🔹 Supprimer une réaction (propriétaire ou admin)
    public function destroy(Request $request, $id)
    {
        $reaction = Reaction::findOrFail($id);

        if ($reaction->user_id !== $request->user()->id && $request->user()->role !== 'admin') {
            return response()->json(['error' => 'Non autorisé'], 403);
        }

        $reaction->delete();

        return response()->json(['message' => 'Réaction supprimée']);
    }
}