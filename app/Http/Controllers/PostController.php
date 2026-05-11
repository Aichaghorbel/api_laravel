<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;
use App\Models\Notification;
use Illuminate\Support\Facades\Storage;

class PostController extends Controller
{
    // ✅ Helper centralisé pour construire l'URL image
    private function getImageUrl(?string $imagePath): ?string
    {
        if (!$imagePath) return null;

        // Si déjà une URL complète, retourner telle quelle
        if (str_starts_with($imagePath, 'http')) {
            return $imagePath;
        }

        // Construire l'URL avec le bon port
        return url('storage/' . $imagePath);
    }

    public function index(Request $request)
    {
        $query = Post::with(['user', 'categorie', 'commentaires.user', 'reactions']);

        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        $user = $request->user();
        if (!$user || !in_array($user->role, ['admin', 'moderateur'])) {
            $query->where('is_hidden', false);
        }

        $posts = $query->latest()->get();

        $posts->transform(function ($post) {
            $post->image = $this->getImageUrl($post->image);
            return $post;
        });

        return response()->json($posts);
    }

    public function show($id)
    {
        $post = Post::with(['user', 'categorie', 'commentaires.user', 'reactions'])
            ->findOrFail($id);

        $post->image = $this->getImageUrl($post->image);

        return response()->json($post);
    }

    public function store(Request $request)
    {
        $request->validate([
            'titre'        => 'required|string|max:255',
            'contenu'      => 'required|string',
            'categorie_id' => 'required|exists:categories,id',
            'image'        => 'nullable|image|max:2048'
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('posts', 'public');
        }

        $post = Post::create([
            'titre'        => $request->titre,
            'contenu'      => $request->contenu,
            'categorie_id' => $request->categorie_id,
            'user_id'      => $request->user()->id,
            'image'        => $imagePath
        ]);

        return response()->json([
            'message' => 'Post créé avec succès',
            'post'    => array_merge($post->toArray(), [
                'image' => $this->getImageUrl($post->image)
            ])
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $post = Post::findOrFail($id);

        if (
            $post->user_id !== $request->user()->id &&
            !in_array($request->user()->role, ['admin', 'moderateur'])
        ) {
            return response()->json(['error' => 'Non autorisé'], 403);
        }

        $request->validate([
            'titre'        => 'sometimes|required|string|max:255',
            'contenu'      => 'sometimes|required|string',
            'categorie_id' => 'sometimes|required|exists:categories,id',
            'image'        => 'nullable|image|max:2048'
        ]);

        if ($request->hasFile('image')) {
            // Supprimer l'ancienne image
            if ($post->image && !str_starts_with($post->image, 'http')) {
                Storage::disk('public')->delete($post->image);
            } elseif ($post->image) {
                $oldPath = str_replace(url('storage/'), '', $post->image);
                Storage::disk('public')->delete($oldPath);
            }
            $post->image = $request->file('image')->store('posts', 'public');
        }

        $post->update($request->only(['titre', 'contenu', 'categorie_id']));

        if ($request->hasFile('image')) {
            $post->save();
        }

        return response()->json([
            'message' => 'Post mis à jour',
            'post'    => array_merge($post->toArray(), [
                'image' => $this->getImageUrl($post->image)
            ])
        ]);
    }

    public function destroy(Request $request, $id)
    {
        $post = Post::findOrFail($id);

        if (
            $post->user_id !== $request->user()->id &&
            !in_array($request->user()->role, ['admin', 'moderateur'])
        ) {
            return response()->json(['error' => 'Non autorisé'], 403);
        }

        if ($post->image && !str_starts_with($post->image, 'http')) {
            Storage::disk('public')->delete($post->image);
        }

        $post->delete();

        return response()->json(['message' => 'Post supprimé']);
    }

    public function toggleLock(Request $request, $id)
    {
        $post = Post::findOrFail($id);

        if (!in_array($request->user()->role, ['admin', 'moderateur'])) {
            return response()->json(['error' => 'Non autorisé'], 403);
        }

        $post->is_locked = !$post->is_locked;
        $post->save();

        return response()->json([
            'message'   => $post->is_locked ? 'Sujet fermé' : 'Sujet ouvert',
            'is_locked' => $post->is_locked
        ]);
    }

    public function toggleHide(Request $request, $id)
    {
        $post = Post::findOrFail($id);

        if (!in_array($request->user()->role, ['admin', 'moderateur'])) {
            return response()->json(['error' => 'Non autorisé'], 403);
        }

        $post->is_hidden = !$post->is_hidden;
        $post->save();

        return response()->json([
            'message'   => $post->is_hidden ? 'Post masqué' : 'Post affiché',
            'is_hidden' => $post->is_hidden
        ]);
    }

    public function deleteByUser(Request $request, $userId, $postId)
    {
        if (!in_array($request->user()->role, ['admin', 'moderateur'])) {
            return response()->json(['error' => 'Accès refusé'], 403);
        }

        $post = Post::where('id', $postId)
            ->where('user_id', $userId)
            ->firstOrFail();

        Notification::create([
            'user_id' => $userId,
            'message' => 'Votre post a été supprimé par un admin/modérateur'
        ]);

        if ($post->image && !str_starts_with($post->image, 'http')) {
            Storage::disk('public')->delete($post->image);
        }

        $post->delete();

        return response()->json(['message' => 'Post supprimé']);
    }
}