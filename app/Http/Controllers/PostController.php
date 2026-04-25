<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;
use App\Models\Notification;
use Illuminate\Support\Facades\Storage;

class PostController extends Controller
{
    // 🔹 Liste tous les posts avec leurs relations
    public function index(Request $request)
    {
        $query = Post::with(['user', 'categorie', 'commentaires.user', 'reactions']);

        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // 🔒 Masquer les posts cachés pour les non admins
        $user = $request->user();
        if (!$user || !in_array($user->role, ['admin', 'moderateur'])) {
            $query->where('is_hidden', false);
        }

        $posts = $query->latest()->get();

        // ✅ AJOUT : transformer le chemin image en URL publique
        $posts->transform(function ($post) {
            if ($post->image) {
                $post->image = asset('storage/' . $post->image);
            }
            return $post;
        });

        return response()->json($posts);
    }

    // 🔹 Afficher un post
    public function show($id)
    {
        $post = Post::with(['user', 'categorie', 'commentaires.user', 'reactions'])
            ->findOrFail($id);

        // ✅ AJOUT
        if ($post->image) {
            $post->image = asset('storage/' . $post->image);
        }

        return response()->json($post);
    }

    // 🔹 Créer un post
    public function store(Request $request)
    {
        $request->validate([
            'titre' => 'required|string|max:255',
            'contenu' => 'required|string',
            'categorie_id' => 'required|exists:categories,id',
            'image' => 'nullable|image|max:2048'
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('posts', 'public');
        }

        $post = Post::create([
            'titre' => $request->titre,
            'contenu' => $request->contenu,
            'categorie_id' => $request->categorie_id,
            'user_id' => $request->user()->id,
            'image' => $imagePath
        ]);

        // ✅ AJOUT
        $post->image = $imagePath ? asset('storage/' . $imagePath) : null;

        return response()->json([
            'message' => 'Post créé avec succès',
            'post' => $post
        ], 201);
    }

    // 🔹 Mettre à jour un post
    public function update(Request $request, $id)
    {
        $post = Post::findOrFail($id);

        if ($post->user_id !== $request->user()->id &&
            !in_array($request->user()->role, ['admin', 'moderateur'])) {
            return response()->json(['error' => 'Non autorisé'], 403);
        }

        $request->validate([
            'titre' => 'sometimes|required|string|max:255',
            'contenu' => 'sometimes|required|string',
            'categorie_id' => 'sometimes|required|exists:categories,id',
            'image' => 'nullable|image|max:2048'
        ]);

        if ($request->hasFile('image')) {
            if ($post->image) {
                Storage::disk('public')->delete($post->image);
            }
            $post->image = $request->file('image')->store('posts', 'public');
        }

        $post->update($request->only(['titre', 'contenu', 'categorie_id', 'image']));

        // ✅ AJOUT
        if ($post->image) {
            $post->image = asset('storage/' . $post->image);
        }

        return response()->json([
            'message' => 'Post mis à jour',
            'post' => $post
        ]);
    }

    // 🔹 Supprimer un post
    public function destroy(Request $request, $id)
    {
        $post = Post::findOrFail($id);

        if ($post->user_id !== $request->user()->id &&
            !in_array($request->user()->role, ['admin', 'moderateur'])) {
            return response()->json(['error' => 'Non autorisé'], 403);
        }

        if ($post->image) {
            Storage::disk('public')->delete($post->image);
        }

        $post->delete();

        return response()->json(['message' => 'Post supprimé']);
    }

    // 🔹 Suppression par admin/modérateur
    public function deleteByUser(Request $request, $userId, $postId)
    {
        if (!in_array($request->user()->role, ['admin', 'moderateur'])) {
            return response()->json(['error' => 'Accès refusé'], 403);
        }

        $post = Post::where('id', $postId)->where('user_id', $userId)->firstOrFail();

        Notification::create([
            'user_id' => $userId,
            'message' => 'Votre post a été supprimé par un admin/modérateur'
        ]);

        if ($post->image) {
            Storage::disk('public')->delete($post->image);
        }

        $post->delete();

        return response()->json(['message' => 'Post supprimé']);
    }
}