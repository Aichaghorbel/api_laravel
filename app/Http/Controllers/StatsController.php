<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Categorie;
use App\Models\Post;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class StatsController extends Controller
{
    // ─────────────────────────────────────────────────────────
    // Helper : vérifie que l'utilisateur connecté est admin
    // ─────────────────────────────────────────────────────────
    private function isAdmin(Request $request): bool
    {
        return $request->user() && $request->user()->role === 'admin';
    }



    // ─────────────────────────────────────────────────────────
    // 📊 GET /stats/dashboard-full (TOUT EN UN POUR ACCÉLÉRER)
    // ─────────────────────────────────────────────────────────
    public function dashboardFull(Request $request)
    {
        if (!$this->isAdmin($request)) {
            return response()->json(['error' => 'Accès refusé (Admin uniquement)'], 403);
        }

        // On utilise le cache pour éviter de recalculer tout à chaque refresh (ex: 5 minutes)
        return \Illuminate\Support\Facades\Cache::remember('admin_dashboard_stats', 300, function () {
            // 1. Les comptes globaux
            $summary = [
                'users'       => User::where('role', 'utilisateur')->count(),
                'categories'  => Categorie::count(),
                'posts'       => Post::count(),
                'moderateurs' => User::where('role', 'moderateur')->count(),
            ];

            // 2. Données du graphique utilisateurs
            $usersChart = $this->getUsersChartData();

            // 3. Données du graphique posts
            $postsChart = $this->getPostsChartData();

            // 4. Répartition par catégorie
            $categoriesPercent = $this->getCategoriesPercentData();

            // 5. Liste des 5 derniers modérateurs
            $recentModerateurs = User::where('role', 'moderateur')
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get(['id', 'name', 'pseudo', 'email', 'status', 'role']);

            return [
                'summary'           => $summary,
                'usersChart'        => $usersChart,
                'postsChart'        => $postsChart,
                'categoriesPercent' => $categoriesPercent,
                'moderateurs'       => $recentModerateurs
            ];
        });
    }

    // Helper methods for internal reuse
    private function getUsersChartData()
    {
        $startOfWeek = Carbon::now()->startOfWeek();
        $usersThisWeek = User::where('role', 'utilisateur')
            ->whereBetween('created_at', [$startOfWeek, Carbon::now()->endOfWeek()])
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
            ->groupBy('date')->get()->keyBy('date');

        $semaineData = [];
        $joursSemaine = ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'];
        for ($i = 0; $i < 7; $i++) {
            $dateString = (clone $startOfWeek)->addDays($i)->format('Y-m-d');
            $semaineData[] = ['name' => $joursSemaine[$i], 'inscrits' => isset($usersThisWeek[$dateString]) ? $usersThisWeek[$dateString]->count : 0];
        }

        $usersThisYear = User::where('role', 'utilisateur')
            ->whereYear('created_at', Carbon::now()->year)
            ->select(DB::raw('MONTH(created_at) as month'), DB::raw('count(*) as count'))
            ->groupBy('month')->get()->keyBy('month');

        $moisData = [];
        $nomsMois = ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin', 'Juil', 'Août', 'Sep', 'Oct', 'Nov', 'Déc'];
        for ($i = 1; $i <= 12; $i++) {
            $moisData[] = ['name' => $nomsMois[$i - 1], 'inscrits' => isset($usersThisYear[$i]) ? $usersThisYear[$i]->count : 0];
        }

        return ['semaine' => $semaineData, 'mois' => $moisData];
    }

    private function getPostsChartData()
    {
        $startOfWeek = Carbon::now()->startOfWeek();
        $postsThisWeek = Post::whereBetween('created_at', [$startOfWeek, Carbon::now()->endOfWeek()])
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
            ->groupBy('date')->get()->keyBy('date');

        $semaineData = [];
        $joursSemaine = ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'];
        for ($i = 0; $i < 7; $i++) {
            $dateString = (clone $startOfWeek)->addDays($i)->format('Y-m-d');
            $semaineData[] = ['name' => $joursSemaine[$i], 'posts' => isset($postsThisWeek[$dateString]) ? $postsThisWeek[$dateString]->count : 0];
        }

        $postsThisYear = Post::whereYear('created_at', Carbon::now()->year)
            ->select(DB::raw('MONTH(created_at) as month'), DB::raw('count(*) as count'))
            ->groupBy('month')->get()->keyBy('month');

        $moisData = [];
        $nomsMois = ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin', 'Juil', 'Août', 'Sep', 'Oct', 'Nov', 'Déc'];
        for ($i = 1; $i <= 12; $i++) {
            $moisData[] = ['name' => $nomsMois[$i - 1], 'posts' => isset($postsThisYear[$i]) ? $postsThisYear[$i]->count : 0];
        }

        return ['semaine' => $semaineData, 'mois' => $moisData];
    }

    private function getCategoriesPercentData()
    {
        $totalPosts = Post::count();
        if ($totalPosts === 0) return [];

        $categories = Categorie::withCount('posts')->get();
        $data = [];
        $colors = ['bg-slate-800', 'bg-amber-500', 'bg-blue-500', 'bg-emerald-500', 'bg-purple-500', 'bg-rose-500'];

        foreach ($categories as $index => $cat) {
            $data[] = [
                'name'    => $cat->titre,
                'percent' => round(($cat->posts_count / $totalPosts) * 100),
                'color'   => $colors[$index % count($colors)]
            ];
        }
        usort($data, fn($a, $b) => $b['percent'] <=> $a['percent']);
        return $data;
    }

    // ─────────────────────────────────────────────────────────
    // 📊 GET /stats  (all-in-one pour le dashboard)
    // ─────────────────────────────────────────────────────────
    public function dashboard(Request $request)
    {
        if (!$this->isAdmin($request)) {
            return response()->json(['error' => 'Accès refusé (Admin uniquement)'], 403);
        }

        return response()->json([
            'users'       => User::where('role', 'utilisateur')->count(),
            'categories'  => Categorie::count(),
            'posts'       => Post::count(),
            'moderateurs' => User::where('role', 'moderateur')->count(),
        ]);
    }


}
