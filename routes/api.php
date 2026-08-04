<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\api\SongController;
use App\Http\Controllers\api\PlaylistController;
use App\Http\Controllers\api\PlaylistSongController;
use App\Http\Controllers\api\FavoriteSongController;
use App\Http\Controllers\AuthController;

Route::prefix('v1')->group(function () {

    // Auth routes
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/refresh', [AuthController::class, 'refresh']);
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:api');

    // Public routes
    Route::post('/songs/metadata', [SongController::class, 'getMetadata']);
    Route::post('/songs/search-musicbrainz', [SongController::class, 'searchMusicBrainz']);
    Route::get('/songs/search', [SongController::class, 'search']);

    // Deezer
    Route::get('/songs/search-deezer', [SongController::class, 'searchDeezer']);
    Route::get('/songs/search-deezer-isrc', [SongController::class, 'searchDeezerByISRC']);
    Route::get('/songs/deezer-album/{albumId}', [SongController::class, 'getDeezerAlbum']);

    // Song.link (cross-platform)
    Route::post('/songs/songlink', [SongController::class, 'getSongLink']);
    Route::get('/songs/songlink-isrc', [SongController::class, 'getSongLinkByISRC']);

    // Lyrics (LrcLib)
    Route::get('/songs/lyrics', [SongController::class, 'getLyrics']);
    Route::get('/songs/lyrics/search', [SongController::class, 'searchLyrics']);

    // Protected routes (JWT)
    Route::middleware('auth:api')->group(function () {

        // Songs
        Route::get('/songs', [SongController::class, 'index']);
        Route::get('/songs/search', [SongController::class, 'search']);
        Route::post('/songs', [SongController::class, 'store']);
        Route::get('/songs/{id}', [SongController::class, 'show']);
        Route::get('/songs/{id}/stream', [SongController::class, 'stream']);
        Route::put('/songs/{id}', [SongController::class, 'update']);
        Route::delete('/songs/{id}', [SongController::class, 'destroy']);

        // Playlists
        Route::get('/playlists', [PlaylistController::class, 'index']);
        Route::post('/playlists', [PlaylistController::class, 'store']);
        Route::get('/playlists/{id}', [PlaylistController::class, 'show']);
        Route::put('/playlists/{id}', [PlaylistController::class, 'update']);
        Route::delete('/playlists/{id}', [PlaylistController::class, 'destroy']);

        // Playlist Songs
        Route::post('/playlists/{playlistId}/songs', [PlaylistSongController::class, 'attach']);
        Route::patch('/playlists/{playlistId}/songs/reorder', [PlaylistSongController::class, 'reorder']);
        Route::delete('/playlists/{playlistId}/songs/{songId}', [PlaylistSongController::class, 'detach']);

        // Favorites
        Route::get('/favorites', [FavoriteSongController::class, 'index']);
        Route::post('/favorites', [FavoriteSongController::class, 'add']);
        Route::delete('/favorites', [FavoriteSongController::class, 'remove']);
    });
});
