<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PlaylistSong\AttachSongRequest;
use App\Http\Requests\PlaylistSong\ReorderSongsRequest;
use App\Http\Traits\ApiResponse;
use App\Models\Playlist;
use App\Services\PlaylistService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PlaylistSongController extends Controller
{
    use ApiResponse;

    public function __construct(protected PlaylistService $service) {}

    public function attach(AttachSongRequest $request, int $playlistId): JsonResponse
    {
        $playlist = Playlist::find($playlistId);

        if (!$playlist) {
            return $this->errorResponse('Playlist not found', 404);
        }

        $this->authorize('manageSongs', $playlist);

        if ($playlist->songs()->where('song_id', $request->song_id)->exists()) {
            return $this->errorResponse('This song is already in the playlist', 409);
        }

        $this->service->attachSong($playlist, $request->song_id);

        return $this->successResponse(null, 'Song added to playlist');
    }

    public function detach(int $playlistId, int $songId): JsonResponse
    {
        $playlist = Playlist::find($playlistId);

        if (!$playlist) {
            return $this->errorResponse('Playlist not found', 404);
        }

        $this->authorize('manageSongs', $playlist);

        $this->service->detachSong($playlist, $songId);

        return $this->successResponse(null, 'Song removed from playlist');
    }

    public function reorder(ReorderSongsRequest $request, int $playlistId): JsonResponse
    {
        $playlist = Playlist::find($playlistId);

        if (!$playlist) {
            return $this->errorResponse('Playlist not found', 404);
        }

        $this->authorize('manageSongs', $playlist);

        $this->service->reorderSongs($playlist, $request->song_ids);

        return $this->successResponse(null, 'Playlist reordered successfully');
    }
}
