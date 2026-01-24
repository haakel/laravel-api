<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\api\DataController;
use App\Http\Controllers\api\CategoryController;
use App\Http\Controllers\api\SongController;
use App\Http\Controllers\api\PlaylistController;
use App\Http\Controllers\api\PlaylistSongController;
use App\Http\Controllers\AuthController;

// این Route اول قرار داره
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Route مربوط به data کامنت شده
// Route::get('/data', [DataController::class]);
// پیشوند v1
// Route::prefix('v1')->group(function () {
//     Route::post('/categories', [CategoryController::class, 'store']);
//     Route::put('/categories/{id}', [CategoryController::class, 'update']);
//     Route::delete('/categories/{id}', [CategoryController::class, 'destroy']);
    
//     // این Route‌ها برای songs
//     Route::get('/songs', [SongController::class, 'index']);
//     Route::post('/datasong', [SongController::class, 'GetDataSong']);
//     Route::post('/songs', [SongController::class, 'store']);
//     Route::delete('/songs', [SongController::class, 'destroysong']);
//     Route::put('/songs', [SongController::class, 'editsong']);

// });

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/refresh', [AuthController::class, 'refresh']);
Route::post('/logout', [AuthController::class, 'logout']);


Route::prefix('v1')->group(function () {
    // public
    Route::post('/datasong', [SongController::class, 'GetDataSong']);

    // protected (JWT)
    Route::middleware('auth:api')->group(function () {
        Route::get('/songs', [SongController::class, 'index']);
        Route::post('/song', [SongController::class, 'store']);
        Route::delete('/song/{id}', [SongController::class, 'destroysong']);
        Route::patch('/song', [SongController::class, 'editsong']);
        Route::get('/song/{id}', [SongController::class, 'show']);

    // playlists
    Route::get('/playlists', [PlaylistController::class, 'index']);
    Route::post('/playlist', [PlaylistController::class, 'store']);
    Route::get('/playlist/{id}', [PlaylistController::class, 'show']);
    Route::patch('/playlist/{id}', [PlaylistController::class, 'update']);
    Route::delete('/playlist/{id}', [PlaylistController::class, 'destroy']);

    // playlist songs
    Route::post('/playlist/{playlistId}/songs', [PlaylistSongController::class, 'attach']);
    Route::patch('/playlist/{playlistId}/songs/reorder', [PlaylistSongController::class, 'reorder']);
    Route::delete('/playlist/{playlistId}/songs/{songId}', [PlaylistSongController::class, 'detach']);
    });

    Route::get('/favorites', [FavoriteSongController::class, 'index']);
    Route::post('/favorites/add', [FavoriteSongController::class, 'add']);
    Route::post('/favorites/remove', [FavoriteSongController::class, 'remove']);

});