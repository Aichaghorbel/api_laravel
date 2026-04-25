<?php

namespace App\Http\Controllers;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use App\Models\Categorie;

class CategoryController extends Controller
{
    // 🔹 Liste des catégories
    public function index()
    {
        return response()->json(Categorie::all());
    }

    // 🔹 Ajouter une catégorie
    public function store(Request $request)
    {
        $request->validate([
            'titre' => 'required|unique:categories,titre',
            'description' => 'nullable|string',
            'icon' => 'nullable|string',   // ✅
            'color' => 'nullable|string'   // ✅
        ]);

        $categorie = Categorie::create([
            'titre' => $request->titre,
            'description' => $request->description,
            'icon' => $request->icon,     // ✅
            'color' => $request->color    // ✅
        ]);

        return response()->json([
            'message' => 'Catégorie créée',
            'categorie' => $categorie
        ]);
    }

    // 🔹 Afficher une catégorie
    public function show($id)
    {
        return response()->json(Categorie::findOrFail($id));
    }

    // 🔹 Modifier (ADMIN)
    public function update(Request $request, $id)
    {
        $user = $request->user();
        if ($user->role !== 'admin') {
            return response()->json(['error' => 'Accès refusé'], 403);
        }

        $categorie = Categorie::findOrFail($id);

        $request->validate([
            'titre' => [
                'required',
                Rule::unique('categories')->ignore($categorie->id),
            ],
            'description' => 'nullable|string',
            'icon' => 'nullable|string',   // ✅
            'color' => 'nullable|string'   // ✅
        ]);

        $categorie->update([
            'titre' => $request->titre,
            'description' => $request->description,
            'icon' => $request->icon,     // ✅
            'color' => $request->color    // ✅
        ]);

        return response()->json([
            'message' => 'Catégorie mise à jour',
            'categorie' => $categorie
        ]);
    }

    // 🔹 Supprimer (ADMIN)
    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        if ($user->role !== 'admin') {
            return response()->json(['error' => 'Accès refusé'], 403);
        }

        Categorie::findOrFail($id)->delete();

        return response()->json([
            'message' => 'Catégorie supprimée'
        ]);
    }
}