<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;

use App\Http\Controllers\UserController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CommentaireController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ReactionController;
use App\Http\Controllers\SuspensionController;
use App\Http\Controllers\StatsController;
use App\Http\Controllers\PrivateMessageController; // ✅ AJOUT MANQUANT


//
// =======================
// 🔓 AUTH (PUBLIC)
// =======================
Route::post('/register', [RegisteredUserController::class, 'store']);
Route::post('/login', [AuthenticatedSessionController::class, 'store']);



// =======================
// 🌍 PUBLIC (INDEX - NO LOGIN)
// =======================

Route::get('/posts', [PostController::class, 'index']);
Route::get('/posts/{id}', [PostController::class, 'show']);

Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/categories/{id}', [CategoryController::class, 'show']);

// ✅ TEST DATABASE
Route::get('/test-db', function () {
    try {
        return \DB::select('SELECT 1');
    } catch (\Exception $e) {
        return $e->getMessage();
    }
});

// ✅ CLEAR CACHE
Route::get('/clear-config', function () {
    try {
        \Artisan::call('config:clear');
        \Artisan::call('cache:clear');
        return "✅ Cache vidé";
    } catch (\Exception $e) {
        return $e->getMessage();
    }
});

// ✅ FORCE MIGRATE
Route::get('/force-migrate', function () {
    try {
        \Artisan::call('migrate', ['--force' => true]);
        return "✅ Migration exécutée";
    } catch (\Exception $e) {
        return $e->getMessage();
    }
});



//
// =======================
// 🔐 PROTECTED (LOGIN REQUIRED)
// =======================
Route::middleware(['auth:sanctum', 'check.status'])->group(function () {

    //
    // 🔓 AUTH ACTIONS
    //
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy']);

    //
    // 👤 PROFILE
    //
    Route::get('/profile', [UserController::class, 'profile']);
    Route::put('/profile', [UserController::class, 'update']);

    //
    // 🧑 USERS (ADMIN)
    //
    Route::get('/users', [UserController::class, 'index']);
    Route::post('/users', [UserController::class, 'store']);
    Route::put('/users/{id}', [UserController::class, 'updateAdmin']);
    Route::delete('/users/{id}', [UserController::class, 'destroy']);
    Route::put('/users/{id}/role', [UserController::class, 'changeRole']);
    Route::put('/users/{id}/suspend', [UserController::class, 'suspend']);
    Route::get('/moderateurs', [UserController::class, 'listModerateurs']);

    //
    // 🟠 POSTS (WRITE ONLY)
    //
    Route::post('/posts', [PostController::class, 'store']);
    Route::put('/posts/{id}', [PostController::class, 'update']);
    Route::delete('/posts/{id}', [PostController::class, 'destroy']);
    Route::post('/posts/{id}/toggle-lock', [PostController::class, 'toggleLock']);
    Route::post('/posts/{id}/toggle-hide', [PostController::class, 'toggleHide']);

    Route::delete('/users/{userId}/posts/{postId}', [PostController::class, 'deleteByUser']);

    //
    // 💬 COMMENTS (WRITE ONLY)
    //
    Route::post('/posts/{post_id}/comments', [CommentaireController::class, 'store']);
    Route::put('/comments/{id}', [CommentaireController::class, 'update']);
    Route::delete('/comments/{id}', [CommentaireController::class, 'destroy']);

    //
    // ❤️ REACTIONS (WRITE ONLY)
    //
    Route::post('/posts/{post_id}/reactions', [ReactionController::class, 'store']);
    Route::put('/reactions/{id}', [ReactionController::class, 'update']);
    Route::delete('/reactions/{id}', [ReactionController::class, 'destroy']);

    //
    // 🏷️ CATEGORIES (ADMIN ONLY WRITE)
    //
    Route::post('/categories', [CategoryController::class, 'store']);
    Route::put('/categories/{id}', [CategoryController::class, 'update']);
    Route::delete('/categories/{id}', [CategoryController::class, 'destroy']);

    //
    // 💬 CHAT
    //
    Route::get('/chats', [ChatController::class, 'index']);
    Route::post('/chats', [ChatController::class, 'store']);
    Route::delete('/chats/{id}', [ChatController::class, 'destroy']);

    //
    // 🔔 NOTIFICATIONS
    //
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications', [NotificationController::class, 'store']);
    Route::delete('/notifications/{id}', [NotificationController::class, 'destroy']);

    //
    // 🚫 SUSPENSIONS
    //
    Route::get('/suspensions', [SuspensionController::class, 'index']);
    Route::post('/suspensions', [SuspensionController::class, 'store']);
    Route::delete('/suspensions/{id}', [SuspensionController::class, 'destroy']);

    //
    // 📊 STATISTIQUES (ADMIN ONLY)
    //
    Route::get('/stats', [StatsController::class, 'dashboard']);               // tout-en-un
    Route::get('/stats/dashboard-full', [StatsController::class, 'dashboardFull']); // consolidé


    Route::get('/me/history', [UserController::class, 'history']);



    // Dans les routes protégées par auth:sanctum
    Route::get('/users/search', [UserController::class, 'search']);
    
    Route::get('/messages/unread/count', [PrivateMessageController::class, 'unreadCount']);
    Route::get('/messages/{userId}', [PrivateMessageController::class, 'index']);
    Route::post('/messages/{userId}', [PrivateMessageController::class, 'store']);
});