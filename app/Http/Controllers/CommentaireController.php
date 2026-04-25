<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Commentaire;
use Illuminate\Validation\Rule;
use App\Models\Post;
use App\Models\Notification;


class CommentaireController extends Controller
{
    // 🔹 Liste des commentaires d’un post
    public function index($post_id)
    {
        $commentaires = Commentaire::with('user')
            ->where('post_id', $post_id)
            ->latest()
            ->get();

        return response()->json($commentaires);
    }

    public function store(Request $request, $post_id)
{
    $request->validate([
        'contenu' => 'required|string'
    ]);

    $user = $request->user();
    $post = Post::findOrFail($post_id);

    if ($post->is_locked) {
        return response()->json(['error' => 'Ce sujet est fermé aux commentaires'], 403);
    }

    $commentaire = Commentaire::create([
        'contenu' => $request->contenu,
        'user_id' => $user->id,
        'post_id' => $post_id
    ]);

    // 🔔 Notification
    if ($post->user_id != $user->id) {
        Notification::create([
            'user_id' => $post->user_id,
            'from_user' => $user->pseudo,
            'post_title' => $post->titre,
            'type' => 'comment',
            'message' => 'a commenté votre post'
        ]);
    }

    return response()->json([
        'message' => 'Commentaire ajouté',
        'commentaire' => $commentaire
    ]);
}

    // 🔹 Modifier commentaire
    public function update(Request $request, $id)
    {
        $commentaire = Commentaire::findOrFail($id);

        // 🔒 sécurité : seul propriétaire
        if ($commentaire->user_id !== $request->user()->id) {
            return response()->json(['error' => 'Non autorisé'], 403);
        }

        $request->validate([
            'contenu' => 'required|string'
        ]);

        $commentaire->update([
            'contenu' => $request->contenu
        ]);

        return response()->json([
            'message' => 'Commentaire modifié',
            'commentaire' => $commentaire
        ]);
    }

    // 🔹 Supprimer commentaire
    public function destroy(Request $request, $id)
    {
        $commentaire = Commentaire::findOrFail($id);

        // 🔒 sécurité : propriétaire, admin ou moderateur
        if (
            $commentaire->user_id !== $request->user()->id &&
            !in_array($request->user()->role, ['admin', 'moderateur'])
        ) {
            return response()->json(['error' => 'Non autorisé'], 403);
        }

        $commentaire->delete();

        return response()->json([
            'message' => 'Commentaire supprimé'
        ]);
    }
}